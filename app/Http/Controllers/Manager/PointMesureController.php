<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePointMesureRequest;
use App\Http\Requests\UpdatePointMesureRequest;
use App\Models\PointMesure;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PointMesureController extends Controller
{
    public function index(): View
    {
        $points = PointMesure::with('derniereMesure')->orderBy('nom')->paginate(15);

        return view('manager.points-mesure.index', compact('points'));
    }

    public function create(): View
    {
        return view('manager.points-mesure.create');
    }

    public function store(StorePointMesureRequest $request): RedirectResponse
    {
        $point = PointMesure::create($request->validated());

        return redirect()->route('manager.points-mesure.show', $point)->with('success', 'Le point de mesure a été créé.');
    }

    public function show(PointMesure $point_mesure): View
    {
        $point_mesure->load(['mesuresQualites' => fn ($query) => $query->latest('date_mesure')]);

        return view('manager.points-mesure.show', ['point' => $point_mesure]);
    }

    public function edit(PointMesure $point_mesure): View
    {
        return view('manager.points-mesure.edit', ['point' => $point_mesure]);
    }

    public function update(UpdatePointMesureRequest $request, PointMesure $point_mesure): RedirectResponse
    {
        $point_mesure->update($request->validated());

        return redirect()->route('manager.points-mesure.show', $point_mesure)->with('success', 'Le point de mesure a été mis à jour.');
    }

    public function destroy(PointMesure $point_mesure): RedirectResponse
    {
        if ($point_mesure->mesuresQualites()->exists()) {
            $point_mesure->update(['statut' => 'inactif']);

            return redirect()->route('manager.points-mesure.index')
                ->with('success', 'Le point possède un historique de mesures : il a été désactivé.');
        }

        $point_mesure->delete();

        return redirect()->route('manager.points-mesure.index')->with('success', 'Le point de mesure a été supprimé.');
    }
}
