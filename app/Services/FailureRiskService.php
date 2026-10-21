<?php

namespace App\Services;

use App\Enums\InfrastructureEtat;
use App\Models\Infrastructure;
use App\Models\InfrastructureAlerte;
use App\Models\InfrastructureRiskPrediction;
use Carbon\CarbonInterface;

class FailureRiskService
{
    private const TOTAL_FEATURES = 7;

    /**
     * Estimate the probability that an infrastructure will fail within 30 days.
     *
     * @return array{
     *     probability: int,
     *     level: string,
     *     factors: list<array{key: string, name: string, value: float, weight: float, contribution: float, explanation: string}>,
     *     recommendation: string,
     *     confidence: int
     * }
     */
    public function predict(Infrastructure $infrastructure): array
    {
        $config = app(InfrastructureRiskSettingsService::class)->configuration();
        $weights = $config['weights'];
        $normalizers = $config['normalizers'];
        $features = [];

        $this->addStateFeature($features, $infrastructure, $config, $weights);
        $this->addAgeFeature($features, $infrastructure, $config, $weights, $normalizers);
        $this->addMaintenanceFeature($features, $infrastructure, $config, $weights, $normalizers);
        $this->addInterventionFeature($features, $infrastructure, $config, $weights, $normalizers);
        $this->addIncidentFeature($features, $infrastructure, $config, $weights, $normalizers);
        $this->addCriticalityFeature($features, $infrastructure, $config, $weights);
        $this->addFlappingFeature($features, $infrastructure, $config, $weights, $normalizers);

        $logit = (float) $config['bias'];
        foreach ($features as $feature) {
            $logit += $feature['weight'] * $feature['value'];
        }

        $probability = (int) round($this->sigmoid($logit) * 100);
        $factors = [];

        foreach ($features as $feature) {
            $probabilityWithoutFeature = $this->sigmoid($logit - ($feature['weight'] * $feature['value'])) * 100;
            $contribution = round($probability - $probabilityWithoutFeature, 2);
            $factors[] = [
                ...$feature,
                'contribution' => $contribution,
            ];
        }

        usort($factors, static fn (array $a, array $b): int => abs($b['contribution']) <=> abs($a['contribution']));

        $level = $this->levelFor($probability, $config['thresholds']);

        return [
            'probability' => min(100, max(0, $probability)),
            'level' => $level,
            'factors' => $factors,
            'recommendation' => $this->recommendationFor($factors, $level, $config['recommendation'], $infrastructure->id),
            'confidence' => (int) round((count($features) / self::TOTAL_FEATURES) * 100),
        ];
    }

    /**
     * Calculate and save an idempotent snapshot, retaining earlier predictions as history.
     *
     * @return array{
     *     probability: int,
     *     level: string,
     *     factors: list<array{key: string, name: string, value: float, weight: float, contribution: float, explanation: string}>,
     *     recommendation: string,
     *     confidence: int
     * }
     */
    public function recompute(Infrastructure $infrastructure, ?CarbonInterface $computedAt = null): array
    {
        $computedAt ??= now();
        $prediction = $this->predict($infrastructure);
        $previous = InfrastructureRiskPrediction::query()
            ->where('infrastructure_id', $infrastructure->id)
            ->where('computed_at', '<', $computedAt)
            ->orderByDesc('computed_at')
            ->first();

        InfrastructureRiskPrediction::query()->updateOrCreate(
            [
                'infrastructure_id' => $infrastructure->id,
                'computed_at' => $computedAt,
            ],
            [
                'probability' => $prediction['probability'],
                'level' => $prediction['level'],
                'factors' => $prediction['factors'],
            ],
        );

        $this->syncRiskAlert($infrastructure, $prediction['level'], $prediction['probability'], $previous?->level);

        return $prediction;
    }

    private function addStateFeature(array &$features, Infrastructure $infrastructure, array $config, array $weights): void
    {
        $state = $infrastructure->etat instanceof InfrastructureEtat
            ? $infrastructure->etat->value
            : $infrastructure->etat;

        if ($state === null || ! array_key_exists($state, $config['etat'])) {
            return;
        }

        $value = (float) $config['etat'][$state];
        $this->addFeature($features, 'etat', 'État actuel', $value, $weights['etat'], "État actuel : {$state}");
    }

    private function addAgeFeature(array &$features, Infrastructure $infrastructure, array $config, array $weights, array $normalizers): void
    {
        $lifespan = (int) $infrastructure->duree_vie_annees;
        if (! $infrastructure->date_installation || $lifespan <= 0) {
            return;
        }

        $ageDays = max(0, $infrastructure->date_installation->diffInDays(now()));
        $daysPerYear = (float) $normalizers['days_per_year'];
        $value = min(1.0, $ageDays / ($lifespan * $daysPerYear));
        $ageYears = (int) floor($ageDays / $daysPerYear);
        $this->addFeature(
            $features,
            'age_ratio',
            'Durée de vie consommée',
            $value,
            $weights['age_ratio'],
            "{$ageYears} an(s) sur {$lifespan} de durée de vie",
        );
    }

    private function addMaintenanceFeature(array &$features, Infrastructure $infrastructure, array $config, array $weights, array $normalizers): void
    {
        if (! $infrastructure->date_prochaine_maintenance) {
            return;
        }

        $daysOverdue = max(0, (int) $infrastructure->date_prochaine_maintenance->copy()->startOfDay()->diffInDays(now()->startOfDay(), false));
        $value = min(1.0, $daysOverdue / (float) $normalizers['maintenance_delay_days']);
        $explanation = $daysOverdue > 0
            ? "Maintenance en retard de {$daysOverdue} jour(s)"
            : 'Maintenance planifiée à temps';
        $this->addFeature($features, 'maintenance_delay', 'Retard de maintenance', $value, $weights['maintenance_delay'], $explanation);
    }

    private function addInterventionFeature(array &$features, Infrastructure $infrastructure, array $config, array $weights, array $normalizers): void
    {
        if (! $infrastructure->exists) {
            return;
        }

        $months = (int) $config['periods']['interventions_months'];
        $count = $infrastructure->interventions()
            ->whereBetween('date', [now()->subMonths($months), now()])
            ->count();

        if ($count === 0) {
            return;
        }

        $value = min(1.0, $count / (float) $normalizers['interventions_12_months']);
        $this->addFeature($features, 'interventions', 'Interventions récentes', $value, $weights['interventions'], "{$count} intervention(s) en {$months} mois");
    }

    private function addIncidentFeature(array &$features, Infrastructure $infrastructure, array $config, array $weights, array $normalizers): void
    {
        if (! $infrastructure->exists) {
            return;
        }

        $months = (int) $config['periods']['incidents_months'];
        $count = $infrastructure->incidents()
            ->whereBetween('date_signalement', [now()->subMonths($months), now()])
            ->count();

        if ($count === 0) {
            return;
        }

        $value = min(1.0, $count / (float) $normalizers['incidents_12_months']);
        $this->addFeature($features, 'incidents', 'Incidents récents', $value, $weights['incidents'], "{$count} incident(s) en {$months} mois");
    }

    private function addCriticalityFeature(array &$features, Infrastructure $infrastructure, array $config, array $weights): void
    {
        $criticality = $infrastructure->niveau_criticite;
        $criticality = $criticality instanceof \BackedEnum
            ? $criticality->value
            : $criticality;
        $criticality = is_string($criticality) ? mb_strtolower($criticality) : null;

        if ($criticality === null || ! array_key_exists($criticality, $config['criticality'])) {
            return;
        }

        $value = (float) $config['criticality'][$criticality];
        $this->addFeature($features, 'criticite', 'Criticité', $value, $weights['criticite'], 'Niveau de criticité : ' . ucfirst($criticality));
    }

    private function addFlappingFeature(array &$features, Infrastructure $infrastructure, array $config, array $weights, array $normalizers): void
    {
        if (! $infrastructure->exists) {
            return;
        }

        $days = (int) $config['periods']['flapping_days'];
        $count = $infrastructure->historiques()
            ->whereNotNull('ancien_etat')
            ->whereColumn('ancien_etat', '!=', 'nouvel_etat')
            ->where('created_at', '>=', now()->subDays($days))
            ->reorder()
            ->count();
        $value = min(1.0, $count / (float) $normalizers['flapping_90_days']);
        $this->addFeature(
            $features,
            'flapping',
            'Changements récents d’état',
            $value,
            $weights['flapping'],
            "{$count} changement(s) d’état en {$days} jours",
        );
    }

    private function addFeature(array &$features, string $key, string $name, float $value, float $weight, string $explanation): void
    {
        $features[] = [
            'key' => $key,
            'name' => $name,
            'value' => min(1.0, max(0.0, $value)),
            'weight' => $weight,
            'explanation' => $explanation,
        ];
    }

    private function levelFor(int $probability, array $thresholds): string
    {
        return match (true) {
            $probability >= (int) $thresholds['critique'] => 'Critique',
            $probability >= (int) $thresholds['eleve'] => 'Élevé',
            $probability >= (int) $thresholds['moyen'] => 'Moyen',
            default => 'Faible',
        };
    }

    public function recommendationFor(array $factors, string $level, ?array $config = null, ?int $infrastructureId = null): string
    {
        $config ??= app(InfrastructureRiskSettingsService::class)->configuration()['recommendation'];

        $localFallback = $this->buildLocalRecommendation($factors, $level, $config);

        if ($infrastructureId !== null) {
            return app(RecommendationAiService::class)->recommend(
                $infrastructureId,
                $factors,
                $level,
                $localFallback
            );
        }

        return $localFallback;
    }

    private function buildLocalRecommendation(array $factors, string $level, array $config): string
    {
        $maxFactors = (int) $config['max_factors'];
        $topFactors = array_values(array_filter(
            array_slice($factors, 0, $maxFactors),
            static fn (array $factor): bool => $factor['contribution'] > 0,
        ));

        if ($topFactors === []) {
            return $level === 'Faible'
                ? 'Maintenir le suivi préventif habituel.'
                : 'Surveiller les indicateurs et vérifier les données de maintenance.';
        }

        $days = (int) $config['days'];
        $reasons = implode(' et ', array_map(
            static fn (array $factor): string => mb_strtolower($factor['name']),
            $topFactors,
        ));

        return "Planifier une action préventive sous {$days} jours en priorité sur {$reasons}.";
    }

    private function syncRiskAlert(Infrastructure $infrastructure, string $level, int $probability, ?string $previousLevel): void
    {
        $alertType = 'risque_panne';
        $highLevels = ['Élevé', 'Critique'];

        if (in_array($level, $highLevels, true)) {
            $wasHigh = in_array($previousLevel, $highLevels, true);
            $alertExists = InfrastructureAlerte::query()
                ->where('infrastructure_id', $infrastructure->id)
                ->where('type', $alertType)
                ->where('resolue', false)
                ->exists();

            if (! $wasHigh && ! $alertExists) {
                InfrastructureAlerte::create([
                    'infrastructure_id' => $infrastructure->id,
                    'type' => $alertType,
                    'message' => "Risque de panne {$level} estimé à {$probability} % dans les 30 prochains jours.",
                    'resolue' => false,
                ]);
            }

            return;
        }

        InfrastructureAlerte::query()
            ->where('infrastructure_id', $infrastructure->id)
            ->where('type', $alertType)
            ->where('resolue', false)
            ->update(['resolue' => true]);
    }

    private function sigmoid(float $value): float
    {
        return 1 / (1 + exp(-$value));
    }
}
