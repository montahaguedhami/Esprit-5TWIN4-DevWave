<?php

namespace App\Http\Controllers;

use App\Models\Infrastructure;
use App\Models\Zone;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InfrastructureController extends Controller
{
    public function index(): View
    {
        $infrastructures = Infrastructure::with('zone')->orderBy('nom')->paginate(10);

        return view('manager.infrastructures.index', compact('infrastructures'));
    }

    public function create(Request $request): View|RedirectResponse
    {
        if (! Zone::exists()) {
            return redirect()->route('manager.zones.create')
                ->with('error', 'Créez d’abord une zone avant d’ajouter une infrastructure.');
        }

        return view('manager.infrastructures.create', [
            'zones' => Zone::orderBy('nom')->get(),
            'selectedZoneId' => $request->query('zone_id'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $infrastructure = Infrastructure::create($request->validate([
            'nom' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'max:255'],
            'localisation' => ['required', 'string', 'max:255'],
            'etat' => ['required', 'string', 'max:255'],
            'date_installation' => ['required', 'date'],
            'zone_id' => ['required', 'integer', 'exists:zones,id'],
        ]));

        return redirect()->route('manager.infrastructures.show', $infrastructure)
            ->with('success', 'Infrastructure créée avec succès.');
    }

    public function show(Infrastructure $infrastructure): View
    {
        $infrastructure->load('zone');

        return view('manager.infrastructures.show', compact('infrastructure'));
    }

    public function edit(Infrastructure $infrastructure): View
    {
        return view('manager.infrastructures.edit', [
            'infrastructure' => $infrastructure,
            'zones' => Zone::orderBy('nom')->get(),
        ]);
    }

    public function update(Request $request, Infrastructure $infrastructure): RedirectResponse
    {
        $infrastructure->update($request->validate([
            'nom' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'max:255'],
            'localisation' => ['required', 'string', 'max:255'],
            'etat' => ['required', 'string', 'max:255'],
            'date_installation' => ['required', 'date'],
            'zone_id' => ['required', 'integer', 'exists:zones,id'],
        ]));

        return redirect()->route('manager.infrastructures.show', $infrastructure)
            ->with('success', 'Infrastructure mise à jour avec succès.');
    }

    public function destroy(Infrastructure $infrastructure): RedirectResponse
    {
        $infrastructure->delete();

        return redirect()->route('manager.infrastructures.index')
            ->with('success', 'Infrastructure supprimée avec succès.');
    }
}
