<?php

namespace App\Http\Controllers\Technician;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\MesureQualite;
use App\Models\PointMesure;
use App\Models\Zone;
use App\Services\TechnicianScopeService;
use App\Services\WaterQualityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class WaterQualityController extends Controller
{
    public function __construct(private readonly WaterQualityService $waterQualityService) {}

    public function create(Zone $zone): View
    {
        $this->authorizeZone($zone);
        $points = $this->pointsForZone($zone)->get();
        abort_unless($points->isNotEmpty(), 404, 'Aucun point de mesure actif dans cette zone.');

        return view('technician.quality.create', compact('zone', 'points'));
    }

    public function store(Request $request, Zone $zone): RedirectResponse
    {
        $this->authorizeZone($zone);
        $validated = $request->validate([
            'point_mesure_id' => ['required', 'integer', 'exists:point_mesures,id'],
            'date_mesure' => ['required', 'date', 'before_or_equal:now'],
            'ph' => ['required', 'numeric', 'between:0,14'],
            'turbidite' => ['required', 'numeric', 'min:0', 'max:1000'],
            'chlore_residuel' => ['required', 'numeric', 'min:0', 'max:100'],
            'plomb' => ['required', 'numeric', 'min:0', 'max:10000'],
            'nitrates' => ['required', 'numeric', 'min:0', 'max:1000'],
        ]);

        abort_unless($this->pointsForZone($zone)->whereKey($validated['point_mesure_id'])->exists(), 403);

        $assessment = $this->waterQualityService->assess($validated);
        $measurement = MesureQualite::create($validated + [
            'reference' => 'MES-' . Str::upper(Str::random(10)),
            'source_donnee' => 'manuel',
            'is_verified' => false,
            'status' => $assessment['status'],
            'overall_compliance' => $assessment['overall_compliance'],
        ]);
        AuditLog::record('measurement_created', 'MesureQualite', $measurement->id, [
            'reference' => $measurement->reference,
            'point_id' => $measurement->point_mesure_id,
            'status' => $measurement->status,
            'submitted_by_technician' => true,
        ]);

        return redirect()->route('technician.zones.show', $zone)
            ->with('success', 'Mesure enregistrée. Elle sera visible publiquement après validation par un gestionnaire.');
    }

    private function authorizeZone(Zone $zone): void
    {
        $technicien = TechnicianScopeService::currentTechnicien();
        abort_unless($technicien, 403, 'Compte technicien introuvable.');
        abort_unless(TechnicianScopeService::zonesQuery($technicien)->whereKey($zone->id)->exists(), 403, 'Cette zone ne fait pas partie de votre périmètre.');
    }

    private function pointsForZone(Zone $zone)
    {
        return PointMesure::query()->with('infrastructure')->where('statut', 'actif')->where(function ($query) use ($zone): void {
            $query->where('zone_id', $zone->id)
                ->orWhere(function ($fallback) use ($zone): void {
                    $fallback->whereNull('zone_id')->where('zone', $zone->nom);
                })
                ->orWhereHas('infrastructure', fn ($infrastructure) => $infrastructure->where('zone_id', $zone->id));
        })->orderBy('nom');
    }
}
