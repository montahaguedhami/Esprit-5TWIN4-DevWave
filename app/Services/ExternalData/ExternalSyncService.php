<?php

namespace App\Services\ExternalData;

use App\Models\MesureQualite;
use App\Models\PointMesure;
use App\Models\ProviderSyncLog;
use App\Services\WaterQualityService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Routes observations coming from external providers into mesures_qualites.
 *
 * For every provider run this service:
 *   1. Calls the provider and receives NormalisedObservation objects.
 *   2. Skips observations whose external_id is already stored (idempotent sync).
 *   3. Resolves/creates the measurement point and stores the observation.
 *   4. Assesses the stored values and persists status / compliance.
 *   5. Records the outcome in a ProviderSyncLog for the history panel.
 */
class ExternalSyncService
{
    /**
     * Providers attempted when the caller does not restrict the list.
     * Only classes that exist are executed (e.g. data_gov_tn may be absent).
     *
     * @var list<string>
     */
    private const DEFAULT_PROVIDERS = ['simulation', 'data_gov_tn', 'csv'];

    /**
     * Run a single provider and return its aggregated result.
     *
     * @return array{provider: string, imported: int, skipped: int, failed: int, error: ?string}
     */
    public function runProvider(
        ExternalDataProviderInterface $provider,
        bool $fillSimulated = true,
        ?string $label = null,
    ): array {
        $label         ??= $provider->getProviderName();
        $started       = now();
        $observations = [];

        $log = new ProviderSyncLog();
        $log->provider     = $provider->getProviderSlug();
        $log->dataset_id   = $provider->getProviderSlug();
        $log->status       = 'running';
        $log->started_at  = $started;
        $log->save();

        $imported   = 0;
        $skipped    = 0;
        $failed     = 0;
        $errorMessage = null;
        $rowErrors = [];

        try {
            $observations = $provider->fetchObservations(['fill_simulated' => $fillSimulated]);
        } catch (\Throwable $e) {
            $failed++;
            $errorMessage = $e->getMessage();
        }

        foreach ($observations as $observation) {
            if (! $observation->isUsable()) {
                $skipped++;

                continue;
            }

            if ($this->alreadyImported($observation)) {
                // CSVs may add columns over time. Backfill missing values on an
                // existing row without replacing measurements already stored.
                if ($observation->provider === 'csv' && $this->backfillCsvMeasurement($observation)) {
                    $imported++;
                    continue;
                }

                $skipped++;

                continue;
            }

            try {
                $this->import($observation, $provider);
            } catch (\Throwable $e) {
                $failed++;
                $rowError = [
                    'external_id' => $observation->externalId,
                    'station_code' => $observation->stationCode,
                    'message' => $e->getMessage(),
                ];
                Log::warning('External data row import failed', [
                    'provider' => $provider->getProviderSlug(),
                    ...$rowError,
                ]);
                if (count($rowErrors) < 5) {
                    $rowErrors[] = $rowError;
                }

                continue;
            }

            $imported++;
        }

        $log->records_fetched = count($observations);
        $log->records_imported = $imported;
        $log->records_skipped = $skipped;
        $log->records_failed = $failed;
        if ($errorMessage === null && $failed > 0) {
            $errorMessage = "{$failed} ligne(s) en échec. " . implode(' | ', array_map(
                fn (array $rowError) => "{$rowError['station_code']}: {$rowError['message']}",
                $rowErrors,
            ));
        }
        $log->error_message = $errorMessage;
        $log->finish(
            $failed > 0 && $imported > 0 ? 'partial' : ($errorMessage !== null ? 'failed' : 'success'),
            $errorMessage,
        );
        $log->save();

        return [
            'provider' => $label,
            'imported' => $imported,
            'skipped'  => $skipped,
            'failed'   => $failed,
            'error'    => $errorMessage,
            'errors'   => $rowErrors,
        ];
    }

    /**
     * Run every applicable provider and aggregate the per-provider results.
     *
     * @param  list<string>  $providers  Provider slugs to run (empty = all known providers).
     * @return array<int, array{provider: string, imported: int, skipped: int, failed: int, error: ?string}>
     */
    public function runAll(array $providers = [], bool $fillSimulated = true): array
    {
        $results = [];

        foreach ($this->resolveProviderClasses(
            $providers === [] ? self::DEFAULT_PROVIDERS : $providers,
        ) as $provider) {
            $results[] = $this->runProvider($provider, $fillSimulated);
        }

        return $results;
    }

    /**
     * Build the summary shown by the data-collection status endpoint.
     *
     * @return array<string, array{enabled: bool, last_run_at: ?string, last_status: ?string}>
     */
    public function syncStatusSummary(): array
    {
        $summary = [];

        foreach ($this->resolveProviderClasses(self::DEFAULT_PROVIDERS) as $slug => $providerClass) {
            $lastRun = ProviderSyncLog::where('provider', $slug)
                ->orderByDesc('started_at')
                ->first();

            $summary[$slug] = [
                'enabled'     => true,
                'last_run_at' => $lastRun?->started_at?->toIso8601String(),
                'last_status' => $lastRun?->status,
            ];
        }

        return $summary;
    }

    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Import one normalised observation, or throw when it cannot be stored.
     */
    private function import(
        NormalisedObservation $observation,
        ExternalDataProviderInterface $provider,
    ): MesureQualite {
        $point = $this->resolvePoint($observation, $provider);

        $values = [
            'ph'              => $observation->ph,
            'turbidite'       => $observation->turbidite,
            'chlore_residuel' => $observation->chloreResiduel,
            'plomb'           => $observation->plomb,
            'nitrates'        => $observation->nitrates,
        ];

        $assessment = app(WaterQualityService::class)->assess($values);

        $ref = $observation->rawMeta['custom_reference'] ?? $this->buildReference($observation);
        if (MesureQualite::where('reference', $ref)->exists()) {
            $ref = $ref . '-' . bin2hex(random_bytes(2));
        }

        $source = $observation->sourceDonnee();
        if (in_array(strtolower($source), ['simule', 'simulé', 'simulation'], true)) {
            $source = 'manuel';
        }

        $mesure = MesureQualite::create([
            'reference'             => $ref,
            'point_mesure_id'       => $point?->id,
            'date_mesure'           => $observation->observationDate,
            'ph'                    => $observation->ph,
            'turbidite'             => $observation->turbidite,
            'chlore_residuel'       => $observation->chloreResiduel,
            'plomb'                 => $observation->plomb,
            'nitrates'              => $observation->nitrates,
            'source_donnee'         => $source,
            'source_type'           => 'real',
            'provider'              => $observation->provider,
            'external_id'           => $observation->externalId,
            'observation_date'      => $observation->observationDate,
            'external_quality_flag' => $observation->qualityFlag,
            'overall_compliance'    => $assessment['overall_compliance'],
            'status'                => $assessment['status'],
        ]);

        return $mesure;
    }

    private function isSimulatedObservation(NormalisedObservation $observation): bool
    {
        return false;
    }

    private function backfillCsvMeasurement(NormalisedObservation $observation): bool
    {
        $mesure = MesureQualite::where('provider', $observation->provider)
            ->where('external_id', $observation->externalId)
            ->first();

        if (! $mesure) {
            return false;
        }

        $availableValues = [
            'ph' => $observation->ph,
            'turbidite' => $observation->turbidite,
            'chlore_residuel' => $observation->chloreResiduel,
            'plomb' => $observation->plomb,
            'nitrates' => $observation->nitrates,
        ];
        $updates = [];

        foreach ($availableValues as $field => $value) {
            if ($mesure->{$field} === null && $value !== null) {
                $updates[$field] = $value;
            }
        }

        if ($this->isSimulatedObservation($observation) && $mesure->source_type !== 'simulated') {
            $updates['source_type'] = 'simulated';
        }

        if ($updates === []) {
            return false;
        }

        $assessment = app(WaterQualityService::class)->assess(array_merge(
            $mesure->only(array_keys($availableValues)),
            $updates,
        ));
        $mesure->fill($updates + [
            'overall_compliance' => $assessment['overall_compliance'],
            'status' => $assessment['status'],
        ])->save();

        return true;
    }

    /**
     * Resolve (or create) the measurement point the observation belongs to.
     */
    private function resolvePoint(
        NormalisedObservation $observation,
        ExternalDataProviderInterface $provider,
    ): ?PointMesure
    {
        return $provider->resolveOrCreatePoint($observation);
    }

    /**
     * True when this external_id has already been imported.
     */
    private function alreadyImported(NormalisedObservation $observation): bool
    {
        return MesureQualite::where('provider', $observation->provider)
            ->where('external_id', $observation->externalId)
            ->exists();
    }

    /**
     * Build a unique 50-character reference for the imported measurement.
     */
    private function buildReference(NormalisedObservation $observation): string
    {
        return substr('EXT-' . md5($observation->externalId), 0, 50);
    }

    /**
     * @return array<string, class-string<ExternalDataProviderInterface>>
     */
    private function resolveProviderClasses(array $slugs): array
    {
        $providers = [];

        foreach ($slugs as $slug) {
            $class = 'App\\Services\\ExternalData\\' . Str::studly($slug) . 'Provider';

            if (class_exists($class)) {
                $providers[$slug] = $class;
            }
        }

        return $providers;
    }
}
