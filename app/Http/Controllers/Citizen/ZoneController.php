<?php

namespace App\Http\Controllers\Citizen;

use App\Enums\InfrastructureEtat;
use App\Enums\ZoneStatut;
use App\Http\Controllers\Controller;
use App\Models\Zone;
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

        return view('citizen.zones.show', compact('zone', 'infrastructures', 'openIncidentsCount'));
    }
}
