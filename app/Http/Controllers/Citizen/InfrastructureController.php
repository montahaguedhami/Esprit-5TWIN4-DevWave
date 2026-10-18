<?php

namespace App\Http\Controllers\Citizen;

use App\Enums\InfrastructureEtat;
use App\Enums\InfrastructureType;
use App\Enums\ZoneStatut;
use App\Http\Controllers\Controller;
use App\Models\Infrastructure;
use App\Models\Zone;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InfrastructureController extends Controller
{
    public function index(Request $request): View
    {
        $search = substr(trim((string) $request->query('q', '')), 0, 100);
        $typeValue = $request->query('type');
        $type = is_string($typeValue) && $typeValue !== '' ? InfrastructureType::tryFrom($typeValue) : null;
        $etatValue = $request->query('etat');
        $etat = is_string($etatValue) && $etatValue !== '' ? InfrastructureEtat::tryFrom($etatValue) : null;
        $zoneValue = $request->query('zone_id');
        $zoneId = is_string($zoneValue) && ctype_digit($zoneValue) ? (int) $zoneValue : null;

        $infrastructures = Infrastructure::query()
            ->whereHas('zone', fn ($query) => $query->where('statut', ZoneStatut::Active->value))
            ->with(['zone:id,nom,localisation'])
            ->when($search !== '', fn ($query) => $query->search($search))
            ->when($type !== null, fn ($query) => $query->deType($type))
            ->when($etat !== null, fn ($query) => $query->deEtat($etat))
            ->when($zoneId !== null, fn ($query) => $query->where('zone_id', $zoneId))
            ->orderBy('nom')
            ->paginate(12)
            ->withQueryString();

        $zones = Zone::query()
            ->where('statut', ZoneStatut::Active->value)
            ->orderBy('nom')
            ->get(['id', 'nom']);

        return view('citizen.infrastructures.index', [
            'infrastructures' => $infrastructures,
            'zones' => $zones,
            'types' => InfrastructureType::cases(),
            'etats' => InfrastructureEtat::cases(),
            'filters' => [
                'q' => $search,
                'type' => $type?->value ?? '',
                'etat' => $etat?->value ?? '',
                'zone_id' => $zoneId ? (string) $zoneId : '',
            ],
        ]);
    }

    public function show(Infrastructure $infrastructure): View
    {
        abort_unless(
            $infrastructure->zone()->where('statut', ZoneStatut::Active->value)->exists(),
            404
        );

        $infrastructure->load('zone:id,nom,localisation');

        return view('citizen.infrastructures.show', compact('infrastructure'));
    }
}
