<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMesureQualiteRequest;
use App\Http\Requests\UpdateMesureQualiteRequest;
use App\Models\MesureQualite;
use App\Models\PointMesure;
use App\Services\WaterQualityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class WaterQualityController extends Controller
{
    public function __construct(private readonly WaterQualityService $waterQualityService) {}

    public function index(): View
    {
        if (! session('user')) {
            session(['user' => ['name' => 'Dr. Selim Dridi', 'email' => 'quality@aquasecure.tn', 'role' => 'manager', 'role_key' => 'quality']]);
        }

        $records = MesureQualite::with('pointMesure')
            ->latest('date_mesure')
            ->get()
            ->map(fn (MesureQualite $mesure) => $this->recordForView($mesure));
        $latestMeasurement = MesureQualite::with('pointMesure')->latest('date_mesure')->first();
        $latestAssessment = $latestMeasurement
            ? $this->waterQualityService->assess($latestMeasurement->only([
                'ph',
                'turbidite',
                'chlore_residuel',
                'plomb',
                'nitrates',
            ]))
            : null;
        $averageCompliance = $records->isNotEmpty()
            ? round($records->avg('overall_compliance'), 2)
            : null;
        $activePointsCount = PointMesure::where('statut', 'actif')->count();
        $chartCutoff = now()->subDays(30)->format('Y-m-d H:i');
        $chartRecords = $records->reverse()
            ->filter(fn (array $record) => $record['date'] >= $chartCutoff)
            ->values();
        $points = PointMesure::with('derniereMesure')
            ->where('statut', 'actif')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get()
            ->map(fn (PointMesure $point) => $this->pointForMap($point));
        $user = session('user', ['name' => 'Dr. Selim Dridi', 'role' => 'manager']);

        return view('manager.quality', compact(
            'records',
            'latestMeasurement',
            'latestAssessment',
            'averageCompliance',
            'activePointsCount',
            'chartRecords',
            'points',
            'user'
        ));
    }

    public function create(): View
    {
        abort_unless(PointMesure::where('statut', 'actif')->exists(), 404, 'Créez d’abord un point de mesure actif.');

        return view('manager.quality-measures.create', [
            'points' => PointMesure::where('statut', 'actif')->orderBy('nom')->get(),
        ]);
    }

    public function store(StoreMesureQualiteRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $assessment = $this->waterQualityService->assess($validated);
        MesureQualite::create($validated + [
            'status' => $assessment['status'],
            'overall_compliance' => $assessment['overall_compliance'],
        ]);

        return redirect()->route('manager.quality')->with('success', 'La mesure a été enregistrée.');
    }

    public function show(MesureQualite $mesure): View
    {
        $mesure->load('pointMesure');

        return view('manager.quality-measures.show', compact('mesure'));
    }

    public function edit(MesureQualite $mesure): View
    {
        return view('manager.quality-measures.edit', [
            'mesure' => $mesure,
            'points' => PointMesure::orderBy('nom')->get(),
        ]);
    }

    public function update(UpdateMesureQualiteRequest $request, MesureQualite $mesure): RedirectResponse
    {
        $validated = $request->validated();
        $assessment = $this->waterQualityService->assess($validated);
        $mesure->update($validated + [
            'status' => $assessment['status'],
            'overall_compliance' => $assessment['overall_compliance'],
        ]);

        return redirect()->route('manager.quality')->with('success', 'La mesure a été mise à jour.');
    }

    public function destroy(MesureQualite $mesure): RedirectResponse
    {
        $mesure->delete();

        return redirect()->route('manager.quality')->with('success', 'La mesure a été supprimée.');
    }

    /**
     * @return array<string, mixed>
     */
    private function recordForView(MesureQualite $mesure): array
    {
        $assessment = $this->waterQualityService->assess($mesure->only([
            'ph',
            'turbidite',
            'chlore_residuel',
            'plomb',
            'nitrates',
        ]));

        return [
            'id' => $mesure->reference,
            'measurement_id' => $mesure->id,
            'sampling_point' => $mesure->pointMesure?->nom ?? 'Point supprimé',
            'zone' => $mesure->pointMesure?->zone ?? '—',
            'date' => $mesure->date_mesure->format('Y-m-d H:i'),
            'pH' => (float) $mesure->ph,
            'turbidity' => (float) $mesure->turbidite,
            'residual_chlorine' => (float) $mesure->chlore_residuel,
            'lead_pb' => (float) $mesure->plomb,
            'nitrates' => (float) $mesure->nitrates,
            'is_verified' => $mesure->is_verified,
            'verifier' => $mesure->verifier,
            'overall_compliance' => $assessment['overall_compliance'],
            'status' => $assessment['status'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function pointForMap(PointMesure $point): array
    {
        $measurement = $point->derniereMesure;
        $assessment = $measurement
            ? $this->waterQualityService->assess($measurement->only([
                'ph',
                'turbidite',
                'chlore_residuel',
                'plomb',
                'nitrates',
            ]))
            : null;

        return [
            'id' => $point->id,
            'name' => $point->nom,
            'code' => $point->code,
            'zone' => $point->zone,
            'address' => $point->adresse,
            'lat' => (float) $point->latitude,
            'lng' => (float) $point->longitude,
            'type' => $point->type,
            'status' => match ($assessment['status'] ?? null) {
                'Non-Compliant' => 'critical',
                'Alert' => 'alert',
                default => 'normal',
            },
            'measurement_status' => $assessment['status'] ?? 'No measurement',
            'overall_compliance' => $assessment['overall_compliance'] ?? null,
            'measurement' => $measurement ? [
                'reference' => $measurement->reference,
                'date' => $measurement->date_mesure->format('Y-m-d H:i'),
                'ph' => (float) $measurement->ph,
                'turbidite' => (float) $measurement->turbidite,
                'chlore_residuel' => (float) $measurement->chlore_residuel,
                'plomb' => (float) $measurement->plomb,
                'nitrates' => (float) $measurement->nitrates,
                'is_verified' => $measurement->is_verified,
                'verifier' => $measurement->verifier,
            ] : null,
        ];
    }
}
