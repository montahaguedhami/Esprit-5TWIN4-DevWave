<?php

namespace App\Http\Controllers\Manager;

use App\Data\PlaceholderData;
use App\Http\Controllers\Controller;
use App\Models\PointMesure;
use App\Services\WaterQualityService;
use Illuminate\View\View;

class MapController extends Controller
{
    public function __invoke(WaterQualityService $waterQualityService): View
    {
        if (! session('user')) {
            session(['user' => ['name' => 'Ines Mansouri', 'email' => 'gestionnaire@aquasecure.tn', 'role' => 'manager', 'role_key' => 'manager']]);
        }

        $zones = PlaceholderData::mapZones();
        $pipelines = PlaceholderData::mapPipelines();
        $stats = PlaceholderData::stats();
        $user = session('user', ['name' => 'Gestionnaire', 'role' => 'manager']);
        $measurementPoints = PointMesure::with('derniereMesure')
            ->where('statut', 'actif')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get()
            ->map(function (PointMesure $point) use ($waterQualityService): array {
                $measurement = $point->derniereMesure;
                $assessment = $measurement
                    ? $waterQualityService->assess($measurement->only([
                        'ph',
                        'turbidite',
                        'chlore_residuel',
                        'plomb',
                        'nitrates',
                    ]))
                    : null;

                return [
                    'id' => $point->id,
                    'name' => $point->nom,
                    'code' => $point->code,
                    'zone' => $point->zone,
                    'address' => $point->adresse,
                    'lat' => (float) $point->latitude,
                    'lng' => (float) $point->longitude,
                    'type' => $point->type,
                    'status' => match ($assessment['status'] ?? null) {
                        'Non-Compliant' => 'critical',
                        'Alert' => 'alert',
                        default => 'normal',
                    },
                    'measurement_status' => $assessment['status'] ?? 'No measurement',
                    'overall_compliance' => $assessment['overall_compliance'] ?? null,
                    'measurement' => $measurement ? [
                        'reference' => $measurement->reference,
                        'date' => $measurement->date_mesure->format('Y-m-d H:i'),
                        'ph' => (float) $measurement->ph,
                        'turbidite' => (float) $measurement->turbidite,
                        'chlore_residuel' => (float) $measurement->chlore_residuel,
                        'plomb' => (float) $measurement->plomb,
                        'nitrates' => (float) $measurement->nitrates,
                        'is_verified' => $measurement->is_verified,
                        'verifier' => $measurement->verifier,
                    ] : null,
                ];
            });

        return view('manager.map', compact('zones', 'pipelines', 'stats', 'user', 'measurementPoints'));
    }
}
