<?php

namespace App\Services\ExternalData;

use App\Models\PointMesure;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DataGovTnProvider implements ExternalDataProviderInterface
{
    public function getProviderSlug(): string
    {
        return 'data_gov_tn';
    }

    public function getProviderName(): string
    {
        return 'Portail National des Données Ouvertes (data.gov.tn)';
    }

    public function fetchObservations(array $options = []): array
    {
        $datasetId = env('DATA_GOV_TN_DATASET_ID');
        if (! $datasetId) {
            return [];
        }

        try {
            $url = "https://data.gov.tn/api/explore/v2.1/catalog/datasets/{$datasetId}/records";
            $response = Http::timeout(10)->get($url, ['limit' => 50]);

            if (! $response->successful()) {
                Log::warning('DataGovTnProvider: API request returned unsuccessful status: ' . $response->status());
                return [];
            }

            $data = $response->json();
            $results = $data['results'] ?? [];
            $observations = [];

            foreach ($results as $item) {
                $code = $item['station_code'] ?? $item['code'] ?? null;
                if (! $code) {
                    continue;
                }

                $obsDate = isset($item['date']) ? \Carbon\Carbon::parse($item['date']) : now();
                $externalId = 'datagov-' . $code . '-' . $obsDate->format('Y-m-d');

                $observations[] = new NormalisedObservation(
                    externalId:      $externalId,
                    provider:        'data_gov_tn',
                    stationCode:     $code,
                    stationName:     $item['station_name'] ?? $item['nom'] ?? ('Station ' . $code),
                    latitude:        (float) ($item['latitude'] ?? 36.8),
                    longitude:       (float) ($item['longitude'] ?? 10.1),
                    observationDate: $obsDate,
                    ph:              isset($item['ph']) ? (float) $item['ph'] : null,
                    turbidite:       isset($item['turbidite']) ? (float) $item['turbidite'] : null,
                    nitrates:        isset($item['nitrates']) ? (float) $item['nitrates'] : null,
                    chloreResiduel:  isset($item['chlore_residuel']) ? (float) $item['chlore_residuel'] : null,
                    plomb:           isset($item['plomb']) ? (float) $item['plomb'] : null,
                    qualityFlag:     $item['quality_flag'] ?? null,
                    rawMeta:         $item,
                );
            }

            return $observations;
        } catch (\Throwable $e) {
            Log::warning('DataGovTnProvider: failed to fetch records: ' . $e->getMessage());
            return [];
        }
    }

    public function resolveOrCreatePoint(NormalisedObservation $observation): ?PointMesure
    {
        return PointMesure::firstOrCreate(
            ['code' => $observation->stationCode],
            [
                'nom'       => $observation->stationName,
                'statut'    => 'actif',
                'latitude'  => $observation->latitude,
                'longitude' => $observation->longitude,
            ]
        );
    }
}
