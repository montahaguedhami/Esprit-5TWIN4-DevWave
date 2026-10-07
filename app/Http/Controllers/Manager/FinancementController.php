<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFinancementRequest;
use App\Http\Requests\UpdateFinancementRequest;
use App\Models\Financement;
use App\Models\Projet;
use Illuminate\Http\Request;

class FinancementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Vérification session manager
        if (!session('user') || !in_array(session('user.role'), ['manager', 'admin'])) {
            return redirect()->route('auth.login');
        }

        $query = Financement::with('projet');

        // Filtre par projet
        if ($request->filled('projet_id')) {
            $query->where('projet_id', $request->projet_id);
        }

        // Pagination
        $financements = $query->latest()->paginate(15);

        // Projets pour le filtre
        $projets = Projet::orderBy('nom')->get();

        return view('manager.financements.index', compact('financements', 'projets'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        // Vérification session manager
        if (!session('user') || !in_array(session('user.role'), ['manager', 'admin'])) {
            return redirect()->route('auth.login');
        }

        // Récupérer tous les projets pour le select
        $projets = Projet::orderBy('nom')->get();

        // Pré-sélection si projet_id fourni dans l'URL
        $projetId = $request->query('projet_id');

        return view('manager.financements.create', compact('projets', 'projetId'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreFinancementRequest $request)
    {
        $financement = Financement::create($request->validated());

        return redirect()
            ->route('manager.financements.show', $financement)
            ->with('success', 'Le financement a été créé avec succès.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Financement $financement)
    {
        // Vérification session manager
        if (!session('user') || !in_array(session('user.role'), ['manager', 'admin'])) {
            return redirect()->route('auth.login');
        }

        // Charger le projet associé
        $financement->load('projet');

        return view('manager.financements.show', compact('financement'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Financement $financement)
    {
        // Vérification session manager
        if (!session('user') || !in_array(session('user.role'), ['manager', 'admin'])) {
            return redirect()->route('auth.login');
        }

        // Récupérer tous les projets pour le select
        $projets = Projet::orderBy('nom')->get();

        return view('manager.financements.edit', compact('financement', 'projets'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateFinancementRequest $request, Financement $financement)
    {
        $financement->update($request->validated());

        return redirect()
            ->route('manager.financements.show', $financement)
            ->with('success', 'Le financement a été modifié avec succès.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Financement $financement)
    {
        // Vérification session manager
        if (!session('user') || !in_array(session('user.role'), ['manager', 'admin'])) {
            return redirect()->route('auth.login');
        }

        $source = $financement->source;
        $financement->delete();

        return redirect()
            ->route('manager.financements.index')
            ->with('success', "Le financement \"$source\" a été supprimé avec succès.");
    }
}
