<?php

namespace App\Http\Controllers;

use App\Http\Requests\InterventionRequest;
use App\Models\Intervention;
use App\Models\Technicien;
use Illuminate\Http\Request;

class InterventionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $interventions = Intervention::query()
            ->with(['technicien', 'infrastructure.zone'])
            ->when($request->filled('technicien_id'), fn ($query) => $query->where('technicien_id', $request->technicien_id))
            ->when($request->filled('statut'), fn ($query) => $query->where('statut', $request->statut))
            ->latest('date')
            ->paginate(10)
            ->withQueryString();

        $techniciens = Technicien::orderBy('nom')->get(['id', 'nom']);

        return view('manager.interventions.index', compact('interventions', 'techniciens'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        // Permet de pré-sélectionner le technicien ou l'infrastructure depuis sa fiche
        $intervention = new Intervention([
            'technicien_id' => $request->query('technicien_id'),
            'infrastructure_id' => $request->query('infrastructure_id'),
        ]);
        $techniciens = Technicien::orderBy('nom')->get(['id', 'nom', 'specialite']);
        $infrastructures = \App\Models\Infrastructure::with('zone')->orderBy('nom')->get(['id', 'nom', 'zone_id']);

        return view('manager.interventions.create', compact('intervention', 'techniciens', 'infrastructures'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(InterventionRequest $request)
    {
        $intervention = Intervention::create($request->validated());

        return redirect()
            ->route('manager.interventions.show', $intervention)
            ->with('success', 'L\'intervention a été créée.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Intervention $intervention)
    {
        $intervention->load(['technicien', 'infrastructure.zone']);

        return view('manager.interventions.show', compact('intervention'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Intervention $intervention)
    {
        $techniciens = Technicien::orderBy('nom')->get(['id', 'nom', 'specialite']);
        $infrastructures = \App\Models\Infrastructure::with('zone')->orderBy('nom')->get(['id', 'nom', 'zone_id']);

        return view('manager.interventions.edit', compact('intervention', 'techniciens', 'infrastructures'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(InterventionRequest $request, Intervention $intervention)
    {
        $intervention->update($request->validated());

        return redirect()
            ->route('manager.interventions.show', $intervention)
            ->with('success', 'L\'intervention a été modifiée.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Intervention $intervention)
    {
        $intervention->delete();

        return redirect()
            ->route('manager.interventions.index')
            ->with('success', 'L\'intervention a été supprimée.');
    }
}
