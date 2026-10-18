<?php

namespace App\Http\Controllers\Technician;

use App\Http\Controllers\Controller;
use App\Models\Zone;
use App\Models\PointMesure;
use App\Services\TechnicianScopeService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ZoneController extends Controller
{
    /**
     * Liste des zones d'intervention du technicien.
     */
    public function index(Request $request): View
    {
        $technicien = TechnicianScopeService::currentTechnicien();
        $query = TechnicianScopeService::zonesQuery($technicien);

        if ($search = $request->input('q')) {
            $query->search($search);
        }

        $zones = $query->with('responsable')->orderBy('nom')->paginate(12)->withQueryString();

        return view('technician.zones.index', [
            'technicien' => $technicien,
            'zones' => $zones,
            'search' => $search,
        ]);
    }

    /**
     * Fiche technique terrain d'une zone (sans les données financières/budgets).
     */
    public function show(Zone $zone): View
    {
        $technicien = TechnicianScopeService::currentTechnicien();
        $zone->load('responsable');

        // Récupérer les infrastructures de cette zone assignées à ce technicien
        $infrastructures = $zone->infrastructures()
            ->whereHas('interventions', function ($q) use ($technicien): void {
                $q->where('technicien_id', $technicien->id);
            })
            ->with(['interventions' => function ($q) use ($technicien): void {
                $q->where('technicien_id', $technicien->id)
                    ->whereIn('statut', ['Planifiée', 'En cours']);
            }])
            ->get();

        // Interventions en cours ou planifiées dans la zone pour ce technicien
        $interventions = \App\Models\Intervention::query()
            ->where('technicien_id', $technicien->id)
            ->whereHas('infrastructure', function ($q) use ($zone): void {
                $q->where('zone_id', $zone->id);
            })
            ->whereIn('statut', ['Planifiée', 'En cours'])
            ->orderBy('date')
            ->get();

        $qualityPoints = PointMesure::query()
            ->where('statut', 'actif')
            ->where(function ($query) use ($zone): void {
                $query->where('zone_id', $zone->id)
                    ->orWhere(function ($fallback) use ($zone): void {
                        $fallback->whereNull('zone_id')->where('zone', $zone->nom);
                    })
                    ->orWhereHas('infrastructure', fn ($infrastructure) => $infrastructure->where('zone_id', $zone->id));
            })
            ->with(['infrastructure', 'derniereMesure', 'mesuresQualites' => fn ($query) => $query->latest('date_mesure')->limit(5)])
            ->orderBy('nom')
            ->get();

        return view('technician.zones.show', [
            'technicien' => $technicien,
            'zone' => $zone,
            'infrastructures' => $infrastructures,
            'interventions' => $interventions,
            'qualityPoints' => $qualityPoints,
        ]);
    }
}
