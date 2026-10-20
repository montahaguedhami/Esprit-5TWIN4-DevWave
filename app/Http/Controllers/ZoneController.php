<?php

namespace App\Http\Controllers;

use App\Enums\InfrastructureEtat;
use App\Enums\ZoneStatut;
use App\Http\Requests\StoreZoneRequest;
use App\Http\Requests\UpdateZoneRequest;
use App\Models\User;
use App\Models\Zone;
use App\Services\WaterQualityService;
use App\Services\ZoneStatsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ZoneController extends Controller
{
    public function index(Request $request, ZoneStatsService $statsService): View
    {
        $q = trim((string) $request->query('q', ''));
        $allowedSorts = ['nom', 'infrastructures_count', 'created_at'];
        $rawSort = (string) $request->query('sort');
        $sort = in_array($rawSort, $allowedSorts, true) ? $rawSort : 'nom';

        $rawDirection = strtolower((string) $request->query('direction'));
        $direction = in_array($rawDirection, ['asc', 'desc'], true) ? $rawDirection : 'asc';

        $zones = Zone::query()
            ->with(['responsable'])
            ->withCount([
                'infrastructures',
                'infrastructures as en_service_count' => fn ($q) => $q->where('etat', InfrastructureEtat::EnService->value),
            ])
            ->when($q !== '', fn ($query) => $query->search($q))
            ->orderBy($sort, $direction)
            ->paginate(10)
            ->withQueryString();

        $statsGlobales = $statsService->statsGlobales();

        return view('manager.zones.index', [
            'zones' => $zones,
            'statsGlobales' => $statsGlobales,
            'filters' => [
                'q' => $q,
                'sort' => $sort,
                'direction' => $direction,
            ],
        ]);
    }

    public function create(): View
    {
        return view('manager.zones.create', [
            'users' => User::orderBy('name')->get(['id', 'name', 'email']),
            'statuts' => ZoneStatut::cases(),
        ]);
    }

    public function store(StoreZoneRequest $request): RedirectResponse
    {
        $zone = Zone::create($request->validated());

        return redirect()->route($this->routeName($request, 'zones.show'), $zone)->with('success', 'Zone créée avec succès.');
    }

    public function show(Request $request, Zone $zone, ZoneStatsService $statsService, WaterQualityService $waterQualityService): View
    {
        $rawEtat = $request->query('etat');
        $etat = is_string($rawEtat) && $rawEtat !== '' ? InfrastructureEtat::tryFrom($rawEtat) : null;

        $zone->load([
            'responsable',
            'infrastructures' => fn ($query) => $query
                ->when($etat !== null, fn ($q) => $q->dEtat($etat))
                ->orderBy('nom'),
            'infrastructures.latestRiskPrediction',
            'incidents' => fn ($q) => $q->whereNotIn('statut', ['resolu', 'cloture'])->latest('date_signalement'),
            'pointsMesure.derniereMesure',
            'projets' => fn ($q) => $q->orderByDesc('created_at'),
        ]);

        foreach ($zone->pointsMesure as $point) {
            $point->assessment = $point->derniereMesure
                ? $waterQualityService->assess($point->derniereMesure->only([
                    'ph',
                    'turbidite',
                    'chlore_residuel',
                    'plomb',
                    'nitrates',
                ]))
                : null;
        }

        $stats = $statsService->statsZone($zone);
        $riskInfrastructures = $zone->infrastructures
            ->filter(fn ($infrastructure) => $infrastructure->latestRiskPrediction !== null)
            ->sortByDesc(fn ($infrastructure) => $infrastructure->latestRiskPrediction->probability)
            ->values();
        $riskAverage = $riskInfrastructures->isNotEmpty()
            ? (int) round($riskInfrastructures->avg(fn ($infrastructure) => $infrastructure->latestRiskPrediction->probability))
            : null;

        return view('manager.zones.show', [
            'zone' => $zone,
            'stats' => $stats,
            'selectedEtat' => $etat?->value ?? '',
            'etats' => InfrastructureEtat::cases(),
            'riskTop5' => $riskInfrastructures->take(5),
            'riskAverage' => $riskAverage,
        ]);
    }

    public function edit(Zone $zone): View
    {
        return view('manager.zones.edit', [
            'zone' => $zone,
            'users' => User::orderBy('name')->get(['id', 'name', 'email']),
            'statuts' => ZoneStatut::cases(),
        ]);
    }

    public function update(UpdateZoneRequest $request, Zone $zone): RedirectResponse
    {
        $zone->update($request->validated());

        return redirect()->route($this->routeName($request, 'zones.show'), $zone)->with('success', 'Zone mise à jour avec succès.');
    }

    public function destroy(Request $request, Zone $zone): RedirectResponse
    {
        // Seules les infrastructures NON supprimées logiquement bloquent la suppression
        if ($zone->infrastructures()->exists()) {
            return redirect()->route($this->routeName($request, 'zones.index'))
                ->with('error', 'Cette zone contient des infrastructures actives. Réaffectez-les ou supprimez-les avant de supprimer la zone.');
        }

        $zone->delete();

        return redirect()->route($this->routeName($request, 'zones.index'))->with('success', 'Zone supprimée avec succès.');
    }

    private function routeName(Request $request, string $name): string
    {
        return ($request->routeIs('admin.*') ? 'admin.' : 'manager.') . $name;
    }
}
