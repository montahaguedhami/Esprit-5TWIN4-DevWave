<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProjetRequest;
use App\Http\Requests\UpdateProjetRequest;
use App\Models\Projet;
use Illuminate\Http\Request;

class ProjetController extends Controller
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

        $query = Projet::with('financements');

        // Recherche par nom
        if ($request->filled('search')) {
            $query->where('nom', 'like', '%' . $request->search . '%');
        }

        // Filtre par statut
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        // Pagination
        $projets = $query->latest()->paginate(15);

        // Statistiques
        $stats = [
            'total' => Projet::count(),
            'en_cours' => Projet::where('statut', 'en_cours')->count(),
            'termine' => Projet::where('statut', 'termine')->count(),
            'planifie' => Projet::where('statut', 'planifie')->count(),
        ];

        return view('manager.projets.index', compact('projets', 'stats'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Vérification session manager
        if (!session('user') || !in_array(session('user.role'), ['manager', 'admin'])) {
            return redirect()->route('auth.login');
        }

        return view('manager.projets.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProjetRequest $request)
    {
        $projet = Projet::create($request->validated());

        return redirect()
            ->route('manager.projets.show', $projet)
            ->with('success', 'Le projet a été créé avec succès.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Projet $projet)
    {
        // Vérification session manager
        if (!session('user') || !in_array(session('user.role'), ['manager', 'admin'])) {
            return redirect()->route('auth.login');
        }

        // Charger les financements avec eager loading
        $projet->load('financements');

        return view('manager.projets.show', compact('projet'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Projet $projet)
    {
        // Vérification session manager
        if (!session('user') || !in_array(session('user.role'), ['manager', 'admin'])) {
            return redirect()->route('auth.login');
        }

        return view('manager.projets.edit', compact('projet'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProjetRequest $request, Projet $projet)
    {
        $projet->update($request->validated());

        return redirect()
            ->route('manager.projets.show', $projet)
            ->with('success', 'Le projet a été modifié avec succès.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Projet $projet)
    {
        // Vérification session manager
        if (!session('user') || !in_array(session('user.role'), ['manager', 'admin'])) {
            return redirect()->route('auth.login');
        }

        $nom = $projet->nom;
        $projet->delete(); // Les financements seront supprimés en cascade

        return redirect()
            ->route('manager.projets.index')
            ->with('success', "Le projet \"$nom\" a été supprimé avec succès.");
    }

    /**
     * Display projects on a map.
     */
    public function map()
    {
        // Vérification session manager
        if (!session('user') || !in_array(session('user.role'), ['manager', 'admin'])) {
            return redirect()->route('auth.login');
        }

        // Récupérer uniquement les projets géolocalisés
        $projets = Projet::whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->with('financements')
            ->get();

        return view('manager.projets.map', compact('projets'));
    }
}
