<?php

namespace App\Http\Controllers;

use App\Enums\InfrastructureEtat;
use App\Enums\InfrastructureType;
use App\Enums\NiveauCriticite;
use App\Http\Requests\StoreInfrastructureRequest;
use App\Http\Requests\UpdateInfrastructureEtatRequest;
use App\Http\Requests\UpdateInfrastructureRequest;
use App\Models\Infrastructure;
use App\Models\InfrastructureAlerte;
use App\Models\Zone;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InfrastructureController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));
        $rawZoneId = $request->query('zone_id');
        $zoneId = is_numeric($rawZoneId) && (int) $rawZoneId > 0 ? (int) $rawZoneId : null;

        $rawType = $request->query('type');
        $type = is_string($rawType) && $rawType !== '' ? InfrastructureType::tryFrom($rawType) : null;

        $rawEtat = $request->query('etat');
        $etat = is_string($rawEtat) && $rawEtat !== '' ? InfrastructureEtat::tryFrom($rawEtat) : null;

        $santeFiltre = $request->query('sante'); // 'critique' | 'retard'

        $allowedSorts = ['nom', 'type', 'etat', 'date_installation', 'zone', 'niveau_criticite', 'capacite'];
        $rawSort = (string) $request->query('sort');
        $sort = in_array($rawSort, $allowedSorts, true) ? $rawSort : 'nom';

        $rawDirection = strtolower((string) $request->query('direction'));
        $direction = in_array($rawDirection, ['asc', 'desc'], true) ? $rawDirection : 'asc';

        $query = Infrastructure::query()
            ->with('zone')
            ->withCount([
                'interventions as interventions_12_mois_count' => fn ($q) => $q->where('date', '>=', now()->subYear()),
            ])
            ->when($q !== '', fn ($query) => $query->search($q))
            ->when($zoneId !== null, fn ($query) => $query->deZone($zoneId))
            ->when($type !== null, fn ($query) => $query->deType($type))
            ->when($etat !== null, fn ($query) => $query->dEtat($etat))
            ->when($santeFiltre === 'retard', fn ($query) => $query->enRetardMaintenance())
            ->when($santeFiltre === 'critique', fn ($query) => $query->santeCritique());

        if ($sort === 'zone') {
            $query->orderBy(
                Zone::select('nom')->whereColumn('zones.id', 'infrastructures.zone_id'),
                $direction
            );
        } else {
            $query->orderBy($sort, $direction);
        }

        $infrastructures = $query->paginate(10)->withQueryString();

        $alertes = InfrastructureAlerte::with('infrastructure.zone')
            ->where('resolue', false)
            ->latest()
            ->take(6)
            ->get();

        return view($request->routeIs('admin.*') ? 'admin.inventory.infrastructures.index' : 'manager.infrastructures.index', [
            'infrastructures' => $infrastructures,
            'zones' => Zone::orderBy('nom')->get(['id', 'nom']),
            'types' => InfrastructureType::cases(),
            'etats' => InfrastructureEtat::cases(),
            'alertes' => $alertes,
            'filters' => [
                'q' => $q,
                'zone_id' => $zoneId ? (string) $zoneId : '',
                'type' => $type?->value ?? '',
                'etat' => $etat?->value ?? '',
                'sante' => $santeFiltre ?? '',
                'sort' => $sort,
                'direction' => $direction,
            ],
        ]);
    }

    public function create(Request $request): View|RedirectResponse
    {
        if (! Zone::exists()) {
            return redirect()->route($this->routeName($request, 'zones.create'))
                ->with('error', 'Créez d’abord une zone avant d’ajouter une infrastructure.');
        }

        return view('manager.infrastructures.create', [
            'zones' => Zone::orderBy('nom')->get(),
            'selectedZoneId' => $request->query('zone_id'),
            'types' => InfrastructureType::cases(),
            'etats' => InfrastructureEtat::cases(),
            'niveauxCriticite' => NiveauCriticite::cases(),
            'unitesCapacite' => ['m3', 'm3/h', 'l/s'],
        ]);
    }

    public function store(StoreInfrastructureRequest $request): RedirectResponse
    {
        $infrastructure = new Infrastructure($request->validated());
        $infrastructure->etat_changement_commentaire = $request->input('commentaire_etat');
        $infrastructure->save();

        return redirect()->route($this->routeName($request, 'infrastructures.show'), $infrastructure)
            ->with('success', 'Infrastructure créée avec succès.');
    }

    public function show(Request $request, Infrastructure $infrastructure): View
    {
        $infrastructure->load([
            'zone',
            'historiques' => fn ($q) => $q->orderByDesc('created_at')->orderByDesc('id'),
            'alertes' => fn ($q) => $q->where('resolue', false),
            'interventions.technicien',
            'incidents' => fn ($q) => $q->whereNotIn('statut', ['resolu', 'cloture'])->latest('date_signalement'),
        ]);

        $sante = $infrastructure->sante;
        $coutTotalMaintenance = (float) $infrastructure->interventions->sum('cout');

        return view($request->routeIs('admin.*') ? 'admin.inventory.infrastructures.show' : 'manager.infrastructures.show', [
            'infrastructure' => $infrastructure,
            'sante' => $sante,
            'coutTotalMaintenance' => $coutTotalMaintenance,
            'etats' => InfrastructureEtat::cases(),
        ]);
    }

    public function edit(Infrastructure $infrastructure): View
    {
        return view('manager.infrastructures.edit', [
            'infrastructure' => $infrastructure,
            'zones' => Zone::orderBy('nom')->get(),
            'types' => InfrastructureType::cases(),
            'etats' => InfrastructureEtat::cases(),
            'niveauxCriticite' => NiveauCriticite::cases(),
            'unitesCapacite' => ['m3', 'm3/h', 'l/s'],
        ]);
    }

    public function update(UpdateInfrastructureRequest $request, Infrastructure $infrastructure): RedirectResponse
    {
        $infrastructure->fill($request->validated());
        $infrastructure->etat_changement_commentaire = $request->input('commentaire_etat');
        $infrastructure->save();

        return redirect()->route($this->routeName($request, 'infrastructures.show'), $infrastructure)
            ->with('success', 'Infrastructure mise à jour avec succès.');
    }

    public function updateEtat(UpdateInfrastructureEtatRequest $request, Infrastructure $infrastructure): RedirectResponse
    {
        $infrastructure->etat = $request->validated('etat');
        $infrastructure->etat_changement_commentaire = $request->validated('commentaire') ?: 'Changement d’état rapide';
        $infrastructure->save();

        return back()->with('success', 'État de l’infrastructure mis à jour avec succès.');
    }

    public function resoudreAlerte(InfrastructureAlerte $alerte): RedirectResponse
    {
        $alerte->update(['resolue' => true]);

        return back()->with('success', 'Alerte de maintenance marquée comme résolue.');
    }

    public function destroy(Request $request, Infrastructure $infrastructure): RedirectResponse
    {
        $infrastructure->delete();

        return redirect()->route($this->routeName($request, 'infrastructures.index'))
            ->with('success', 'Infrastructure supprimée avec succès.');
    }

    private function routeName(Request $request, string $name): string
    {
        return ($request->routeIs('admin.*') ? 'admin.' : 'manager.') . $name;
    }
}
