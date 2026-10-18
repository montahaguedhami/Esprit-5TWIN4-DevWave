<?php

namespace App\Http\Controllers\Citizen;

use App\Enums\InfrastructureEtat;
use App\Enums\ZoneStatut;
use App\Http\Controllers\Controller;
use App\Models\Zone;
use App\Models\PointMesure;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ZoneController extends Controller
{
    public function index(Request $request): View
    {
        $search = substr(trim((string) $request->query('q', '')), 0, 100);

        $zones = Zone::query()
            ->where('statut', ZoneStatut::Active->value)
            ->withCount([
                'infrastructures',
                'infrastructures as infrastructures_en_service_count' => fn ($query) => $query
                    ->where('etat', InfrastructureEtat::EnService->value),
            ])
            ->when($search !== '', fn ($query) => $query->search($search))
            ->orderBy('nom')
            ->paginate(12)
            ->withQueryString();

        return view('citizen.zones.index', compact('zones', 'search'));
    }

    public function show(Zone $zone): View
    {
        abort_unless($zone->statut === ZoneStatut::Active, 404);

        $zone->loadCount([
            'infrastructures',
            'infrastructures as infrastructures_en_service_count' => fn ($query) => $query
                ->where('etat', InfrastructureEtat::EnService->value),
        ]);
        $infrastructures = $zone->infrastructures()
            ->orderBy('nom')
            ->get(['id', 'nom', 'type', 'localisation', 'etat', 'zone_id']);
        $openIncidentsCount = $zone->incidents()
            ->whereNotIn('statut', ['resolu', 'cloture'])
            ->count();

        $qualityPoints = PointMesure::query()
            ->where('statut', 'actif')
            ->where(function ($query) use ($zone): void {
                $query->where('zone_id', $zone->id)
                    ->orWhere(function ($fallback) use ($zone): void {
                        $fallback->whereNull('zone_id')->where('zone', $zone->nom);
                    })
                    ->orWhereHas('infrastructure', fn ($infrastructure) => $infrastructure->where('zone_id', $zone->id));
            })
            ->with(['infrastructure', 'mesuresQualites' => fn ($query) => $query
                ->where('is_verified', true)
                ->where(function ($q): void {
                    $q->whereNull('source_donnee')
                        ->orWhereNotIn('source_donnee', ['simule', 'simulation']);
                })
                ->where(function ($q): void {
                    $q->whereNull('source_type')->orWhere('source_type', '!=', 'simulated');
                })
                ->latest('date_mesure')])
            ->orderBy('nom')
            ->get()
            ->map(function (PointMesure $point): array {
                $latest = $point->mesuresQualites->first();
                return [
                    'point' => $point,
                    'measurement' => $latest,
                    'is_stale' => $latest?->date_mesure?->lt(now()->subDays(30)) ?? false,
                ];
            });

        return view('citizen.zones.show', compact('zone', 'infrastructures', 'openIncidentsCount', 'qualityPoints'));
    }
}
