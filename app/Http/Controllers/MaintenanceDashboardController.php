<?php

namespace App\Http\Controllers;

use App\Services\MaintenanceStatsService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;

class MaintenanceDashboardController extends Controller
{
    public function __construct(private MaintenanceStatsService $stats)
    {
    }

    /**
     * Tableau de bord des équipes de maintenance (page « Équipes »).
     */
    public function index()
    {
        return view('manager.maintenance.dashboard', [
            'indicateurs' => $this->stats->indicateurs(),
            'couts' => $this->stats->coutsParMois(),
            'parStatut' => $this->stats->parStatut(),
            'charge' => $this->stats->chargeTechniciens(),
            'pannes' => $this->stats->infrastructuresAPannes(),
            'enRetard' => $this->stats->enRetard()->take(8),
        ]);
    }

    /**
     * Rapport mensuel de maintenance au format PDF (?mois=AAAA-MM, mois courant par défaut).
     */
    public function rapportMensuel(Request $request)
    {
        $validated = $request->validate([
            'mois' => ['nullable', 'date_format:Y-m'],
        ], [
            'mois.date_format' => 'Le mois doit être au format AAAA-MM.',
        ]);

        $mois = isset($validated['mois'])
            ? Carbon::createFromFormat('!Y-m', $validated['mois'])
            : today()->startOfMonth();

        $pdf = Pdf::loadView('manager.maintenance.rapport-pdf', $this->stats->rapportMensuel($mois))
            ->setPaper('a4', 'landscape');

        return $pdf->download('rapport-maintenance-'.$mois->format('Y-m').'.pdf');
    }
}
