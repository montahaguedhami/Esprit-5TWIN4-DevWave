<?php

namespace App\Services\ExternalData;

use App\Models\MesureQualite;
use App\Models\PointMesure;

/**
 * Generates realistic synthetic water-quality measurements for active Tunisian
 * measurement points that have no recent data.
 *
 * ⚠️  All data produced by this provider is tagged source_type='simulated' and
 *     source_donnee='simule'. It must NEVER be presented as a real measurement.
 *
 * The simulation uses parameter ranges representative of Tunisian drinking-water
 * networks (SONEDE annual reports) but the individual values are random and carry
 * no scientific validity. They exist solely to populate dashboards during
 * development or when no real data has been imported yet.
 *
 * The provider only generates observations for points that have had no measurement
 * in the last `$staleDays` days, so it never overwrites fresh real data.
 */
class TunisianSimulationProvider implements ExternalDataProviderInterface
{
    /** Do not generate simulated data for points that have recent measurements. */
    private int $staleDays = 30;

    public function getProviderSlug(): string
    {
        return 'simulation';
    }

    public function getProviderName(): string
    {
        return 'Simulation synthétique (données de démonstration — non réelles)';
    }

    public function fetchObservations(array $options = []): array
    {
        if (($options['fill_simulated'] ?? true) === false) {
            return [];
        }

        $staleCutoff = now()->subDays($this->staleDays);

        // Only target points that have no measurement more recent than $staleDays ago
        $stalePoints = PointMesure::where('statut', 'actif')
            ->whereDoesntHave('mesuresQualites', function ($q) use ($staleCutoff) {
                $q->where('date_mesure', '>=', $staleCutoff);
            })
            ->get();

        $observations = [];
        foreach ($stalePoints as $point) {
            $observations[] = $this->generateObservation($point);
        }

        return $observations;
    }

    public function resolveOrCreatePoint(NormalisedObservation $obs): ?PointMesure
    {
        // For simulation, the point already exists by definition (we only simulate for existing points)
        return PointMesure::where('code', $obs->stationCode)->first();
    }

    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Produce a simulated observation for one point.
     *
     * Parameter ranges are representative (not normative) for Tunisian
     * drinking-water networks based on publicly available SONEDE annual reports.
     * Actual compliance is assessed by WaterQualityService, not here.
     */
    private function generateObservation(PointMesure $point): NormalisedObservation
    {
        // Deterministic seed per point+date so the same call always produces the
        // same value — prevents multiple identical rows if the job re-runs.
        $dateKey  = now()->format('Y-m-d');
        $seed     = crc32($point->code . $dateKey);
        mt_srand($seed);

        // Ranges tuned to produce a mix of compliant/alert/non-compliant outcomes
        $ph              = $this->randFloat(6.8, 8.2);
        $turbidite       = $this->randFloat(0.1, 2.5);
        $chloreResiduel  = $this->randFloat(0.1, 1.8);
        $plomb           = $this->randFloat(0.5, 20.0);
        $nitrates        = $this->randFloat(5.0, 45.0);

        // Stable external_id: provider + point.code + date so it never duplicates
        $externalId = 'sim-' . $point->code . '-' . $dateKey;

        return new NormalisedObservation(
            externalId:      $externalId,
            provider:        'simulation',
            stationCode:     $point->code,
            stationName:     $point->nom,
            latitude:        (float) ($point->latitude ?? 36.8),
            longitude:       (float) ($point->longitude ?? 10.1),
            observationDate: now()->startOfDay(),
            ph:              $ph,
            turbidite:       $turbidite,
            nitrates:        $nitrates,
            chloreResiduel:  $chloreResiduel,
            plomb:           $plomb,
            qualityFlag:     null,
            rawMeta:         ['note' => 'Synthetic data — not a real measurement'],
        );
    }

    private function randFloat(float $min, float $max): float
    {
        return round($min + (mt_rand() / mt_getrandmax()) * ($max - $min), 2);
    }
}
