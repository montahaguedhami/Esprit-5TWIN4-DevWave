<?php

namespace App\Http\Controllers\Technician;

use App\Http\Controllers\Controller;
use App\Models\Intervention;
use App\Services\TechnicianScopeService;
use Illuminate\Http\Request;

class TourneeController extends Controller
{
    /**
     * Carte et feuille de route de la tournée du jour du technicien (Mobile-First).
     */
    public function index(Request $request)
    {
        $technicien = TechnicianScopeService::currentTechnicien();
        abort_unless($technicien, 403, 'Compte technicien introuvable.');

        // Récupérer les interventions du jour ou en cours/planifiées
        $interventions = Intervention::query()
            ->where('technicien_id', $technicien->id)
            ->whereIn('statut', ['Planifiée', 'En cours'])
            ->with(['infrastructure.zone'])
            ->orderByRaw("CASE WHEN statut = 'En cours' THEN 1 WHEN statut = 'Planifiée' THEN 2 ELSE 3 END")
            ->orderBy('date')
            ->get();

        // Récupérer aussi celles récemment terminées aujourd'hui pour l'historique de tournée
        $completedToday = Intervention::query()
            ->where('technicien_id', $technicien->id)
            ->where('statut', 'Terminée')
            ->whereDate('updated_at', now()->toDateString())
            ->with(['infrastructure.zone'])
            ->get();

        // Préparer les données pour la carte Leaflet
        $mapPoints = [];
        $order = 1;

        foreach ($interventions as $item) {
            $infra = $item->infrastructure;
            $lat = $infra?->latitude ?? $infra?->zone?->latitude;
            $lng = $infra?->longitude ?? $infra?->zone?->longitude;

            if ($lat && $lng) {
                $mapPoints[] = [
                    'order' => $order++,
                    'intervention_id' => $item->id,
                    'statut' => $item->statut,
                    'date' => $item->date?->format('d/m/Y'),
                    'description' => $item->description,
                    'infra_id' => $infra?->id,
                    'infra_nom' => $infra?->nom ?? 'Ouvrage non spécifié',
                    'infra_type' => $infra?->type?->label() ?? 'Équipement',
                    'infra_etat' => $infra?->etat?->label() ?? 'Inconnu',
                    'zone' => $infra?->zone?->nom ?? 'Zone non définie',
                    'lat' => (float) $lat,
                    'lng' => (float) $lng,
                    'url_fiche' => $infra ? route('technician.infrastructures.show', $infra) : null,
                    'url_intervention' => route('technician.interventions.show', $item),
                    'url_rapport' => route('technician.interventions.report', $item),
                ];
            }
        }

        $stats = [
            'total' => $interventions->count() + $completedToday->count(),
            'en_cours' => $interventions->where('statut', 'En cours')->count(),
            'planifiees' => $interventions->where('statut', 'Planifiée')->count(),
            'terminees' => $completedToday->count(),
        ];

        return view('technician.tournee', compact(
            'interventions',
            'completedToday',
            'technicien',
            'mapPoints',
            'stats'
        ));
    }
}
