<?php

namespace App\Http\Controllers\Citizen;

use App\Http\Controllers\Controller;
use App\Models\Projet;
use Illuminate\Http\Request;

class ProjetController extends Controller
{
    /**
     * Display a listing of the resource (citizen view - read only).
     */
    public function index(Request $request)
    {
        // Vérification session citizen
        if (!session('user') || session('user.role') !== 'citizen') {
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
        $projets = $query->latest()->paginate(12);

        return view('citizen.projets.index', compact('projets'));
    }

    /**
     * Display the specified resource (citizen view - read only).
     */
    public function show(Projet $projet)
    {
        // Vérification session citizen
        if (!session('user') || session('user.role') !== 'citizen') {
            return redirect()->route('auth.login');
        }

        // Charger les financements
        $projet->load('financements');

        return view('citizen.projets.show', compact('projet'));
    }
}
