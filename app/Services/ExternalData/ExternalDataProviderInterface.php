<?php

namespace App\Services\ExternalData;

use App\Models\PointMesure;

/**
 * Contract every external data provider must fulfil.
 *
 * Providers convert their raw API/CSV responses into NormalisedObservation
 * objects which ExternalSyncService imports into mesures_qualites.
 */
interface ExternalDataProviderInterface
{
    /**
     * Unique slug identifying the provider (e.g. 'simulation', 'csv').
     */
    public function getProviderSlug(): string;

    /**
     * Human readable provider name, shown in logs and the UI.
     */
    public function getProviderName(): string;

    /**
     * Fetch the observations currently available from this provider.
     *
     * @return list<NormalisedObservation>
     */
    public function fetchObservations(array $options = []): array;

    /**
     * Resolve the measurement point an observation belongs to, creating it
     * when missing. Returns null when the observation must be skipped.
     */
    public function resolveOrCreatePoint(NormalisedObservation $observation): ?PointMesure;
}
