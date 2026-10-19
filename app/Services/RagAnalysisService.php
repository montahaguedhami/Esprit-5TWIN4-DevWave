<?php

namespace App\Services;

use App\Models\MesureQualite;
use App\Models\PointMesure;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * RAG (Retrieval-Augmented Generation) analysis service.
 *
 * Architecture:
 *   1. Retrieval   — fetches relevant validated measurements from the DB.
 *   2. Context     — builds a structured prompt context with parameter values,
 *                    units, source type, thresholds, and regulatory references.
 *   3. Generation  — sends context to Ollama; falls back to deterministic
 *                    rules-based analysis if Ollama is unavailable.
 *
 * Rules for what the LLM may/may not do:
 *   - May: summarise, identify patterns, suggest investigation directions.
 *   - May NOT: invent parameter values, claim compliance certifications,
 *              fabricate regulatory thresholds, present simulations as real data.
 *
 * The deterministic fallback (available without Ollama) always runs first
 * and its output is included in both LLM and fallback responses.
 *
 * All thresholds come from config/water_quality.php — never from the LLM.
 */
class RagAnalysisService
{
    /** Ollama API base URL from env. */
    private string $ollamaUrl;

    /** Ollama model to use. */
    private string $ollamaModel;

    /** Whether LLM enrichment is enabled. */
    private bool $ollamaEnabled;

    /** Request timeout. */
    private int $ollamaTimeout;

    /** Maximum context rows sent to LLM. */
    private int $maxContextRows;

    /** Temperature. */
    private float $temperature;

    /** Regulatory references bundled with every prompt context. */
    private array $regulatoryRefs = [
        'NT 09.14' => 'Norme tunisienne NT 09.14 — Qualité de l\'eau destinée à la consommation humaine (INNORPI)',
        'WHO 2022' => 'OMS — Directives de qualité pour l\'eau de boisson, 4e édition (2022)',
        'NT 106.02'=> 'Norme tunisienne NT 106.02 — Eaux résiduaires (INNORPI)',
    ];

    public function __construct()
    {
        $this->ollamaUrl       = rtrim((string) config('ollama.url', env('OLLAMA_URL', 'http://localhost:11434')), '/');
        $this->ollamaModel     = (string) config('ollama.model', env('OLLAMA_MODEL', 'llama3.2'));
        $this->ollamaEnabled   = (bool) config('ollama.enabled', true);
        $this->ollamaTimeout   = (int) config('ollama.timeout', 60);
        $this->maxContextRows  = (int) config('ollama.max_context_rows', 10);
        $this->temperature     = (float) config('ollama.temperature', 0.1);
    }

    /**
     * Analyse a measurement point over a time window.
     *
     * @param  PointMesure  $point
     * @param  int          $days     Look-back window in days
     * @return array{
     *   summary: string,
     *   forecast: array<string, array{predicted: float|null, trend: string}>,
     *   parameters: array,
     *   anomalies: array,
     *   confidence: string,
     *   data_quality_note: string,
     *   references: array,
     *   recommendations: array,
     *   method: string,
     *   simulated_data_warning: bool,
     * }
     */
    public function analysePoint(PointMesure $point, int $days = 30, bool $allHistory = false): array
    {
        $measurements = $this->retrieveMeasurements($point, $days, $allHistory);
        $context      = $this->buildContext($point, $measurements, $days, $allHistory);

        // Always compute deterministic analysis first
        $deterministic = $this->deterministicAnalysis($measurements, $context);

        // Attempt LLM enrichment only if configured and enough data may be useful.
        $llmEnrichment = null;
        if ($this->ollamaEnabled) {
            try {
                $llmEnrichment = $this->queryOllama($context, $deterministic);
            } catch (\Throwable $e) {
                Log::info('RagAnalysisService: Ollama unavailable, using deterministic fallback', [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $this->buildResponse($deterministic, $llmEnrichment, $context);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Retrieval
    // ──────────────────────────────────────────────────────────────────────────

    private function retrieveMeasurements(PointMesure $point, int $days, bool $allHistory = false): Collection
    {
        $query = MesureQualite::where('point_mesure_id', $point->id);

        if (! $allHistory) {
            $query->where('date_mesure', '>=', now()->subDays($days));
        }

        return $query->orderBy('date_mesure')->get();
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Context building
    // ──────────────────────────────────────────────────────────────────────────

    private function buildContext(PointMesure $point, Collection $measurements, int $days, bool $allHistory = false): array
    {
        $realCount = $measurements->filter(fn ($m) => ! $m->isSimulated())->count();
        $simCount  = $measurements->filter(fn ($m) => $m->isSimulated())->count();
        $firstDate = $measurements->first()?->date_mesure;
        $lastDate = $measurements->last()?->date_mesure;

        $missingParameters = [];
        foreach (['ph', 'turbidite', 'chlore_residuel', 'plomb', 'nitrates'] as $parameter) {
            if ($measurements->isEmpty() || $measurements->every(fn ($m) => $m->{$parameter} === null)) {
                $missingParameters[] = $parameter;
            }
        }

        $daysCovered = $firstDate && $lastDate
            ? max(1, $firstDate->diffInDays($lastDate) + 1)
            : 0;

        $temporalCoverage = $days > 0
            ? round(min(100, ($daysCovered / max(1, $days)) * 100), 1)
            : 0.0;

        $thresholds = [
            'ph'              => ['min' => config('water_quality.ph_min', 6.5),    'max' => config('water_quality.ph_max', 8.5),     'unit' => 'sans dimension'],
            'turbidite'       => ['alert' => config('water_quality.turbidite_alert', 1.0), 'max' => config('water_quality.turbidite_max', 2.0), 'unit' => 'NTU'],
            'chlore_residuel' => ['min' => config('water_quality.chlore_min', 0.2), 'max' => config('water_quality.chlore_max', 2.0),  'unit' => 'mg/L'],
            'plomb'           => ['max' => config('water_quality.plomb_max', 15.0), 'unit' => 'µg/L'],
            'nitrates'        => ['max' => config('water_quality.nitrates_max'),     'unit' => 'mg/L'],
        ];

        return [
            'point'         => ['code' => $point->code, 'nom' => $point->nom, 'zone' => $point->zone, 'type' => $point->type],
            'window_days'   => $days,
            'all_history'   => $allHistory,
            'total_obs'     => $measurements->count(),
            'real_obs'            => $realCount,
            'simulated_obs'       => $simCount,
            'has_simulated'       => $simCount > 0,
            'missing_parameters'  => $missingParameters,
            'temporal_coverage'   => $temporalCoverage,
            'first_date'          => $firstDate?->toDateTimeString(),
            'last_date'           => $lastDate?->toDateTimeString(),
            'thresholds'          => $thresholds,
            'measurements'  => $measurements->map(fn ($m) => [
                'date'            => $m->date_mesure->toDateTimeString(),
                'source_type'     => $m->source_type ?? ($m->source_donnee === 'simule' ? 'simulated' : 'real'),
                'source_donnee'   => $m->source_donnee,
                'ph'              => $m->ph,
                'turbidite'       => $m->turbidite,
                'chlore_residuel' => $m->chlore_residuel,
                'plomb'           => $m->plomb,
                'nitrates'        => $m->nitrates,
                'status'          => $m->status,
                'is_verified'     => $m->is_verified,
            ])->toArray(),
            'references'    => $this->regulatoryRefs,
        ];
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Forecasting (deterministic trend extrapolation)
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Extrapolate the evolution of each measured parameter over the analysis
     * window. Purely deterministic: ordinary least squares on the retrieved
     * measurements, clearly labelled as an estimate.
     *
     * @return array<string, array{predicted: float|null, trend: string}>
     */
    private function predictFuture(Collection $measurements, int $days): array
    {
        $horizon = max(1, $days);
        $forecast = [];

        foreach (['ph', 'turbidite', 'chlore_residuel', 'plomb', 'nitrates'] as $param) {
            $points = [];

            foreach ($measurements as $m) {
                $value = $m->{$param};
                if ($value === null) {
                    continue;
                }
                $points[] = [
                    'day'    => $m->date_mesure->diffInDays(now()->startOfDay()),
                    'value'  => (float) $value,
                ];
            }

            if ($points === []) {
                $forecast[$param] = ['predicted' => null, 'trend' => 'stable'];

                continue;
            }

            [$intercept, $slope] = $this->leastSquaresTrend($points);
            $predicted = max(0, $intercept + ($slope * $horizon));

            $forecast[$param] = [
                'predicted' => round($predicted, 3),
                'trend'     => $this->describeTrend($slope),
            ];
        }

        return $forecast;
    }

    /**
     * Ordinary least squares fit; returns [intercept, slope].
     *
     * @param  list<array{day: int, value: float}>  $points
     * @return array{0: float, 1: float}
     */
    private function leastSquaresTrend(array $points): array
    {
        $count = count($points);
        if ($count < 2) {
            $first = $points[0]['value'] ?? 0.0;

            return [$first, 0.0];
        }

        $sumX = 0.0;
        $sumY = 0.0;
        $sumXY = 0.0;
        $sumXX = 0.0;

        foreach ($points as $point) {
            $sumX += $point['day'];
            $sumY += $point['value'];
            $sumXY += $point['day'] * $point['value'];
            $sumXX += $point['day'] ** 2;
        }

        $denominator = $count * $sumXX - $sumX * $sumX;
        if (abs($denominator) < 1e-9) {
            return [$sumY / $count, 0.0];
        }

        return [
            ($sumXX * $sumY - $sumX * $sumXY) / $denominator,
            ($count * $sumXY - $sumX * $sumY) / $denominator,
        ];
    }

    private function describeTrend(float $slope): string
    {
        return match (true) {
            $slope > 0.01 => 'hausse',
            $slope < -0.01 => 'baisse',
            default => 'stable',
        };
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Deterministic analysis (always runs — no external dependency)
    // ──────────────────────────────────────────────────────────────────────────

    private function deterministicAnalysis(Collection $measurements, array $context): array
    {
        if ($measurements->isEmpty()) {
            return [
                'summary'     => 'Aucune mesure disponible sur la période sélectionnée.',
                'forecast'    => [],
                'parameters'  => [],
                'anomalies'   => [],
                'confidence'  => 'nulle',
                'data_note'   => 'Aucune donnée — impossible d\'évaluer la qualité.',
            ];
        }

        $params = ['ph', 'turbidite', 'chlore_residuel', 'plomb', 'nitrates'];
        $paramStats = [];

        foreach ($params as $param) {
            $values = $measurements->pluck($param)->filter(fn ($v) => $v !== null)->map(fn ($v) => (float) $v);
            if ($values->isEmpty()) {
                continue;
            }
            $paramStats[$param] = [
                'min'    => round($values->min(), 3),
                'max'    => round($values->max(), 3),
                'avg'    => round($values->avg(), 3),
                'count'  => $values->count(),
            ];
        }

        // Detect anomalies via thresholds (deterministic — not from LLM)
        $anomalies = $this->detectAnomalies($paramStats, $context['thresholds']);

        $nonCompliantCount = $measurements->where('status', 'Non-Compliant')->count();
        $alertCount        = $measurements->where('status', 'Alert')->count();
        $totalReal         = $measurements->filter(fn ($m) => ! $m->isSimulated())->count();

        $forecast = $this->predictFuture($measurements, (int) $context['window_days']);

        $confidence = match (true) {
            $measurements->count() === 0 => 'faible',
            $measurements->count() < 3   => 'faible',
            $measurements->count() < 10  => 'modérée',
            default                      => 'élevée',
        };

        $summary = $this->buildDeterministicSummary(
            $measurements->count(), $totalReal, $nonCompliantCount, $alertCount, $anomalies
        );

        $dataNote = "Analyse basée sur {$measurements->count()} mesure(s) enregistrée(s).";

        return [
            'summary'     => $summary,
            'forecast'    => $forecast,
            'parameters'  => $paramStats,
            'anomalies'   => $anomalies,
            'confidence'  => $confidence,
            'data_note'   => $dataNote,
        ];
    }

    private function detectAnomalies(array $paramStats, array $thresholds): array
    {
        $anomalies = [];

        if (isset($paramStats['ph'])) {
            $s = $paramStats['ph'];
            if ($s['min'] < $thresholds['ph']['min'] || $s['max'] > $thresholds['ph']['max']) {
                $anomalies[] = [
                    'param'     => 'ph',
                    'severity'  => 'high',
                    'detail'    => "pH hors plage [{$thresholds['ph']['min']}–{$thresholds['ph']['max']}] : min={$s['min']}, max={$s['max']}",
                    'reference' => 'NT 09.14, WHO 2022',
                ];
            }
        }

        if (isset($paramStats['turbidite'])) {
            $s = $paramStats['turbidite'];
            $maxNtu = $thresholds['turbidite']['max'];
            $alertNtu = $thresholds['turbidite']['alert'];
            if ($s['max'] > $maxNtu) {
                $anomalies[] = ['param' => 'turbidite', 'severity' => 'high',
                    'detail' => "Turbidité max {$s['max']} NTU dépasse le seuil de non-conformité {$maxNtu} NTU",
                    'reference' => 'NT 09.14'];
            } elseif ($s['max'] > $alertNtu) {
                $anomalies[] = ['param' => 'turbidite', 'severity' => 'medium',
                    'detail' => "Turbidité max {$s['max']} NTU dépasse le seuil d'alerte {$alertNtu} NTU",
                    'reference' => 'NT 09.14'];
            }
        }

        if (isset($paramStats['chlore_residuel'])) {
            $s = $paramStats['chlore_residuel'];
            $min = $thresholds['chlore_residuel']['min'];
            $max = $thresholds['chlore_residuel']['max'];
            if ($s['min'] < $min) {
                $anomalies[] = ['param' => 'chlore_residuel', 'severity' => 'high',
                    'detail' => "Chlore résiduel minimum {$s['min']} mg/L sous le seuil minimal {$min} mg/L",
                    'reference' => 'NT 09.14'];
            }
            if ($s['max'] > $max) {
                $anomalies[] = ['param' => 'chlore_residuel', 'severity' => 'medium',
                    'detail' => "Chlore résiduel maximum {$s['max']} mg/L dépasse le plafond {$max} mg/L",
                    'reference' => 'NT 09.14'];
            }
        }

        if (isset($paramStats['plomb'])) {
            $s = $paramStats['plomb'];
            $maxPb = $thresholds['plomb']['max'];
            if ($s['max'] > $maxPb) {
                $anomalies[] = ['param' => 'plomb', 'severity' => 'high',
                    'detail' => "Concentration en plomb max {$s['max']} µg/L dépasse le seuil d'action {$maxPb} µg/L — action immédiate requise",
                    'reference' => 'NT 09.14, WHO 2022'];
            }
        }

        if (isset($paramStats['nitrates']) && $thresholds['nitrates']['max'] !== null) {
            $s = $paramStats['nitrates'];
            $maxNo3 = $thresholds['nitrates']['max'];
            if ($s['max'] > $maxNo3) {
                $anomalies[] = ['param' => 'nitrates', 'severity' => 'high',
                    'detail' => "Nitrates max {$s['max']} mg/L dépasse le seuil configuré {$maxNo3} mg/L",
                    'reference' => 'NT 09.14'];
            }
        }

        return $anomalies;
    }

    private function buildDeterministicSummary(
        int $total, int $real, int $nonCompliant, int $alert, array $anomalies
    ): string {
        $parts = [];
        $parts[] = "{$total} mesure(s) analysée(s).";

        if ($nonCompliant > 0) {
            $parts[] = "{$nonCompliant} mesure(s) non conforme(s) détectée(s).";
        }
        if ($alert > 0) {
            $parts[] = "{$alert} mesure(s) en état d'alerte.";
        }
        if (empty($anomalies)) {
            $parts[] = "Aucune anomalie détectée sur les seuils configurés.";
        } else {
            $parts[] = count($anomalies) . " anomalie(s) dépassant les seuils réglementaires de référence.";
        }

        return implode(' ', $parts);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Ollama integration
    // ──────────────────────────────────────────────────────────────────────────

    private function queryOllama(array $context, array $deterministic): ?array
    {
        $prompt = $this->buildPrompt($context, $deterministic);

        if (! $this->ollamaEnabled) {
            throw new \RuntimeException('Ollama enrichment disabled by configuration');
        }

        $response = Http::timeout($this->ollamaTimeout)
            ->retry(config('ollama.retries', 1), 2000)
            ->post("{$this->ollamaUrl}/api/generate", [
                'model'  => $this->ollamaModel,
                'prompt' => $prompt,
                'format' => 'json',
                'stream' => false,
                'options'=> ['temperature' => $this->temperature], // low temp = factual, less invention
            ]);

        if (!$response->successful()) {
            throw new \RuntimeException("Ollama HTTP {$response->status()}");
        }

        $responseText = $response->json('response');
        if (empty($responseText)) {
            throw new \RuntimeException('Ollama returned empty response');
        }

        $parsed = json_decode($responseText, true);
        if (!is_array($parsed)) {
            // LLM produced non-JSON — use raw text as summary enrichment
            return ['llm_summary' => substr(strip_tags($responseText), 0, 500)];
        }

        return $parsed;
    }

    private function buildPrompt(array $context, array $deterministic): string
    {
        $point = $context['point'];
        $refs  = implode('; ', array_keys($context['references']));
        $anomalyCount = count($deterministic['anomalies']);
        $missing = implode(', ', $context['missing_parameters'] ?? []) ?: 'aucun';
        $coverage = $context['temporal_coverage'] ?? 0;

        $measurementSummary = '';
        foreach (array_slice($context['measurements'], -$this->maxContextRows) as $m) {
            $measurementSummary .= "{$m['date']}: pH={$m['ph']}, turbidité={$m['turbidite']} NTU, chlore={$m['chlore_residuel']} mg/L, plomb={$m['plomb']} µg/L, nitrates={$m['nitrates']} mg/L, statut={$m['status']}\n";
        }

        return <<<PROMPT
Tu es un assistant d'analyse de la qualité de l'eau. Tu dois analyser les données ci-dessous et retourner un objet JSON structuré.

RÈGLES ABSOLUES :
1. Ne jamais inventer de valeurs, de seuils ou de mesures.
2. Ne jamais affirmer qu'une eau est "potable" ou "conforme" — utiliser uniquement les statuts calculés fournis.
3. Proposer des actions d'investigation ou de surveillance ciblées selon les écarts constatés.
4. Les seuils réglementaires viennent de : {$refs}.
5. Tes recommandations sont des suggestions d'investigation, jamais des certifications.
6. Ne jamais estimer ou remplacer une valeur manquante par une valeur par defaut.

POINT DE MESURE :
- Code: {$point['code']}, Nom: {$point['nom']}, Zone: {$point['zone']}, Type: {$point['type']}
- Couverture temporelle: {$coverage}%
- Parametres manquants: {$missing}

ANALYSE DÉTERMINISTE PRÉ-CALCULÉE :
{$deterministic['summary']}
Anomalies détectées : {$anomalyCount}
Niveau de confiance : {$deterministic['confidence']}
Note sur la qualité des données : {$deterministic['data_note']}

MESURES (10 dernières) :
{$measurementSummary}

Retourne UNIQUEMENT ce JSON (sans texte avant ni après) :
{
  "recommendations": ["...", "..."],
  "interpretation": "explication en 2-3 phrases des tendances observées",
  "limitations": "limites de cette analyse liées à la disponibilité ou la qualité des données"
}
PROMPT;
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Final response assembly
    // ──────────────────────────────────────────────────────────────────────────

    private function buildResponse(array $deterministic, ?array $llm, array $context): array
    {
        $recommendations = $llm['recommendations'] ?? $this->defaultRecommendations($deterministic['anomalies']);
        $interpretation  = $llm['interpretation']  ?? null;
        $limitations     = $llm['limitations']     ?? $deterministic['data_note'];
        $missingParameters = $context['missing_parameters'] ?? [];
        $realPercentage = $context['total_obs'] > 0
            ? round(($context['real_obs'] / $context['total_obs']) * 100, 1)
            : 0.0;
        $executiveSummary = trim($deterministic['summary'] . ' Couverture reelle: ' . $realPercentage . '%. '
            . 'Parametres manquants: ' . ($missingParameters === [] ? 'aucun' : implode(', ', $missingParameters)) . '.');
        $triage = $this->triage($deterministic, $context);

        return [
            'summary'                => $deterministic['summary'],
            'forecast'               => $deterministic['forecast'] ?? [],
            'executive_summary'      => $executiveSummary,
            'triage'                 => $triage,
            'parameters'             => $deterministic['parameters'],
            'anomalies'              => $deterministic['anomalies'],
            'confidence'             => $deterministic['confidence'],
            'data_quality_note'      => $deterministic['data_note'],
            'simulated_data_warning' => false,
            'real_observations'      => $context['real_obs'],
            'simulated_observations' => 0,
            'real_data_percentage'   => 100,
            'missing_parameters'     => $missingParameters,
            'temporal_coverage'      => $context['temporal_coverage'] ?? 0.0,
            'first_date'             => $context['first_date'] ?? null,
            'last_date'              => $context['last_date'] ?? null,
            'analyzed_measurements'  => $context['total_obs'],
            'latest_measurements'    => collect($context['measurements'] ?? [])->sortByDesc('date')->take(12)->values()->all(),
            'references'             => $context['references'],
            'recommendations'        => $recommendations,
            'interpretation'         => $interpretation,
            'limitations'            => $limitations,
            'method'                 => $llm !== null ? 'rag+ollama' : 'deterministic_only',
        ];
    }

    private function triage(array $deterministic, array $context): array
    {
        $anomalies = $deterministic['anomalies'] ?? [];
        $totalObs = $context['total_obs'] ?? 0;
        $realObs = $context['real_obs'] ?? 0;
        $simulatedWarning = false;
        $missing = $context['missing_parameters'] ?? [];

        $criticalParams = [];
        $alertParams = [];
        foreach ($anomalies as $anomaly) {
            $param = $anomaly['param'] ?? null;
            $severity = $anomaly['severity'] ?? 'medium';
            if ($severity === 'high') {
                $alertParams[] = $param;
                if (in_array($param, ['plomb', 'nitrates'], true)) {
                    $criticalParams[] = $param;
                }
            } else {
                $alertParams[] = $param;
            }
        }

        // A lack of data or partial measurements keeps the case at alert level
        if ($totalObs === 0) {
            $level = 'alerte';
        } elseif ($criticalParams !== [] || count(array_filter($anomalies, fn ($a) => ($a['severity'] ?? '') === 'high')) >= 2) {
            $level = 'critique';
        } elseif ($anomalies !== [] || $missing !== [] || $context['temporal_coverage'] < 30) {
            $level = 'alerte';
        } else {
            $level = 'info';
        }

        [$priority, $deadlineHours, $technicianRequired] = match ($level) {
            'critique' => [1, 24, true],
            'alerte' => [2, 72, $anomalies !== [] || $missing !== []],
            default => [3, 168, false],
        };

        $status = match ($level) {
            'critique' => 'requires_immediate_action',
            'alerte' => 'requires_action',
            default => 'monitoring',
        };

        $paramLabels = [
            'ph' => 'pH',
            'turbidite' => 'turbidite',
            'chlore_residuel' => 'chlore residuel',
            'plomb' => 'plomb',
            'nitrates' => 'nitrates',
        ];

        if ($level === 'critique') {
            $description = 'Etat critique : un ou plusieurs parametres depassent les seuils de reference ou plusieurs anomalies simultanees sont detectees.';
        } elseif ($level === 'alerte') {
            $description = 'Etat vigilance : des anomalies, des donnees partielles ou une faible couverture temporelle ont ete detectees.';
        } else {
            $description = 'Etat stable : les parametres disponibles respectent les seuils configures sur la periode analysee.';
        }

        if ($criticalParams !== []) {
            $possibleCause = 'Contamination possible ou dysfonctionnement lie a : ' . implode(', ', array_map(fn ($p) => $paramLabels[$p] ?? $p, $criticalParams)) . '.';
        } elseif ($alertParams !== []) {
            $possibleCause = 'Deviation possible liee a : ' . implode(', ', array_map(fn ($p) => $paramLabels[$p] ?? $p, array_unique($alertParams))) . '.';
        } else {
            $possibleCause = 'Aucune cause defavorable majeure n\'est identifiable a partir des donnees disponibles.';
        }

        if ($missing !== []) {
            $possibleCause .= ' Parametres non disponibles : ' . implode(', ', $missing) . '.';
        }

        if ($level === 'critique') {
            $recommendedAction = 'Prelever une confirmation, limiter l\'usage si necessaire, notifier les responsables et depêcher un technicien dans le delai indique.';
        } elseif ($level === 'alerte') {
            $recommendedAction = 'Renforcer la surveillance, verifier le parametre concerne, planifier une intervention corrective sous 72 heures.';
        } else {
            $recommendedAction = 'Maintenir la frequence de surveillance actuelle et archiver les resultats.';
        }

        return [
            'status' => $status,
            'severity' => $level,
            'severity_label' => match ($level) {
                'critique' => 'Critique',
                'alerte' => 'Alerte',
                default => 'Information',
            },
            'priority' => $priority,
            'deadline_hours' => $deadlineHours,
            'technician_required' => $technicianRequired,
            'description' => $description,
            'possible_cause' => trim($possibleCause),
            'recommended_action' => $recommendedAction,
            'simulated_data_warning' => false,
        ];
    }

    private function defaultRecommendations(array $anomalies): array
    {
        if (empty($anomalies)) {
            return ['Maintenir la fréquence de surveillance actuelle.'];
        }

        $recs = [];
        foreach ($anomalies as $a) {
            $recs[] = match ($a['param']) {
                'ph'              => 'Vérifier l\'équilibre calcique et le processus de chloration. Prélèvement complémentaire recommandé.',
                'turbidite'       => 'Inspecter les filtres et la station de traitement. Augmenter la fréquence de surveillance.',
                'chlore_residuel' => 'Réviser le dosage de chloration. Contrôle de réseaux de distribution.',
                'plomb'           => 'Identification urgente de la source de contamination (canalisations, raccords). Notifier les autorités sanitaires.',
                'nitrates'        => 'Recherche de sources de contamination agricole ou industrielle en amont.',
                default           => "Contrôle renforcé du paramètre {$a['param']}.",
            };
        }
        return array_unique($recs);
    }
}
