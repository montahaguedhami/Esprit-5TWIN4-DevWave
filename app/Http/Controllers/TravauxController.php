<?php

namespace App\Http\Controllers;

use App\Models\Intervention;

/**
 * Front Office : informe les citoyens des travaux de maintenance sur le réseau.
 */
class TravauxController extends Controller
{
    public function index()
    {
        $enCours = Intervention::with('technicien')
            ->where('statut', 'En cours')
            ->orderBy('date')
            ->get();

        $aVenir = Intervention::with('technicien')
            ->where('statut', 'Planifiée')
            ->whereDate('date', '>=', today())
            ->orderBy('date')
            ->get();

        $terminees = Intervention::with('technicien')
            ->where('statut', 'Terminée')
            ->latest('date')
            ->take(6)
            ->get();

        $termineesCeMois = Intervention::where('statut', 'Terminée')
            ->whereBetween('date', [now()->startOfMonth(), now()->endOfMonth()])
            ->count();

        return view('citizen.travaux.index', compact('enCours', 'aVenir', 'terminees', 'termineesCeMois'));
    }

    public function show(Intervention $intervention)
    {
        // Les interventions annulées ne sont pas publiées côté citoyen
        abort_if($intervention->statut === 'Annulée', 404);

        $intervention->load('technicien');

        return view('citizen.travaux.show', compact('intervention'));
    }
}
