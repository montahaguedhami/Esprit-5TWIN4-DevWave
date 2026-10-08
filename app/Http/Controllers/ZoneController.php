<?php

namespace App\Http\Controllers;

use App\Models\Zone;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ZoneController extends Controller
{
    public function index(): View
    {
        $zones = Zone::withCount('infrastructures')->orderBy('nom')->paginate(10);

        return view('manager.zones.index', compact('zones'));
    }

    public function create(): View
    {
        return view('manager.zones.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $zone = Zone::create($request->validate([
            'nom' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'localisation' => ['required', 'string', 'max:255'],
        ]));

        return redirect()->route('manager.zones.show', $zone)->with('success', 'Zone créée avec succès.');
    }

    public function show(Zone $zone): View
    {
        $zone->load(['infrastructures' => fn ($query) => $query->orderBy('nom')]);

        return view('manager.zones.show', compact('zone'));
    }

    public function edit(Zone $zone): View
    {
        return view('manager.zones.edit', compact('zone'));
    }

    public function update(Request $request, Zone $zone): RedirectResponse
    {
        $zone->update($request->validate([
            'nom' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'localisation' => ['required', 'string', 'max:255'],
        ]));

        return redirect()->route('manager.zones.show', $zone)->with('success', 'Zone mise à jour avec succès.');
    }

    public function destroy(Zone $zone): RedirectResponse
    {
        if ($zone->infrastructures()->exists()) {
            return redirect()->route('manager.zones.index')
                ->with('error', 'Cette zone contient des infrastructures. Réaffectez-les ou supprimez-les avant de supprimer la zone.');
        }

        $zone->delete();

        return redirect()->route('manager.zones.index')->with('success', 'Zone supprimée avec succès.');
    }
}
