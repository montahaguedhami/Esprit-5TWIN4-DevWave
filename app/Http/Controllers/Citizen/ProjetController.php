<?php

namespace App\Http\Controllers\Citizen;

use App\Http\Controllers\Controller;
use App\Models\Projet;
use Illuminate\Http\Request;

class ProjetController extends Controller
{
    /**
     * Display a listing of the resource (citizen view — read only).
     */
    public function index(Request $request)
    {
        if (! \App\Models\Role::sessionMatches(\App\Models\Role::CITOYEN)) {
            return redirect()->route('auth.login');
        }

        $query = Projet::with('financements');

        if ($request->filled('search')) {
            $query->where('nom', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        $projets = $query->latest()->paginate(12);

        return view('citizen.projets.index', compact('projets'));
    }

    /**
     * Display the specified resource (citizen view — read only).
     */
    public function show(Projet $projet)
    {
        if (! \App\Models\Role::sessionMatches(\App\Models\Role::CITOYEN)) {
            return redirect()->route('auth.login');
        }

        $projet->load('financements');

        return view('citizen.projets.show', compact('projet'));
    }
}
