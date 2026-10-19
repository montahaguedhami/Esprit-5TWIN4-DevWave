<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMesureQualiteRequest;
use App\Http\Requests\UpdateMesureQualiteRequest;
use App\Models\AuditLog;
use App\Models\MesureQualite;
use App\Models\PointMesure;
use App\Services\WaterQualityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class WaterQualityController extends Controller
{
    public function __construct(private readonly WaterQualityService $waterQualityService) {}

    public function index(Request $request): View
    {
        $query = MesureQualite::with('pointMesure.infrastructure');

        // Search by keyword (reference, point name, point code, or zone)
        if ($search = trim((string) $request->input('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                  ->orWhereHas('pointMesure', function ($sub) use ($search) {
                      $sub->where('nom', 'like', "%{$search}%")
                          ->orWhere('code', 'like', "%{$search}%")
                          ->orWhere('zone', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by point de mesure
        if ($pointId = $request->input('point_mesure_id')) {
            $query->where('point_mesure_id', (int) $pointId);
        }

        // Filter by status (Compliant, Alert, Non-Compliant)
        if ($status = $request->input('status')) {
            if (in_array($status, ['Compliant', 'Alert', 'Non-Compliant'], true)) {
                $query->where('status', $status);
            }
        }

        // Filter by verification status
        if ($request->has('is_verified') && $request->input('is_verified') !== '' && $request->input('is_verified') !== null) {
            $query->where('is_verified', (bool) $request->input('is_verified'));
        }

        // Filter by source de données
        if ($source = $request->input('source_donnee')) {
            if (in_array($source, MesureQualite::SOURCES, true)) {
                $query->where('source_donnee', $source);
            }
        }

        // Filter by date range
        if ($dateFrom = $request->input('date_from')) {
            $query->where('date_mesure', '>=', $dateFrom . ' 00:00:00');
        }
        if ($dateTo = $request->input('date_to')) {
            $query->where('date_mesure', '<=', $dateTo . ' 23:59:59');
        }

        // Sorting
        $sort = $request->input('sort', 'latest');
        match ($sort) {
            'oldest'          => $query->oldest('date_mesure'),
            'compliance_asc'  => $query->orderBy('overall_compliance'),
            'compliance_desc' => $query->orderByDesc('overall_compliance'),
            default           => $query->latest('date_mesure'),
        };

        // Paginated records for the table
        $paginator = $query->paginate(25)->withQueryString();

        $records = $paginator->getCollection()
            ->map(fn (MesureQualite $mesure) => $this->recordForView($mesure));

        $latestMeasurement = MesureQualite::with('pointMesure.infrastructure')->latest('date_mesure')->first();
        $latestAssessment  = $latestMeasurement
            ? $this->waterQualityService->assess($latestMeasurement->only([
                'ph', 'turbidite', 'chlore_residuel', 'plomb', 'nitrates',
            ]))
            : null;

        // Average and chart span all records, not just the current page
        $averageCompliance = MesureQualite::avg('overall_compliance') !== null
            ? round((float) MesureQualite::avg('overall_compliance'), 2)
            : null;
        $activePointsCount = PointMesure::where('statut', 'actif')->count();
        $recentMeasurementsCount = MesureQualite::where('date_mesure', '>=', now()->subDays(7))->count();
        $activeAlertsCount = MesureQualite::where('date_mesure', '>=', now()->subDays(30))
            ->whereIn('status', ['Alert', 'Non-Compliant'])
            ->count();
        $realMeasurementsCount = MesureQualite::where('date_mesure', '>=', now()->subDays(30))
            ->where('source_type', 'real')
            ->count();
        $recentMeasurementsBaseCount = MesureQualite::where('date_mesure', '>=', now()->subDays(30))->count();
        $realDataCoverage = $recentMeasurementsBaseCount > 0
            ? round(($realMeasurementsCount / $recentMeasurementsBaseCount) * 100, 1)
            : 0.0;

        $chartCutoff  = now()->subDays(30)->format('Y-m-d H:i');
        $chartRecords = MesureQualite::with('pointMesure.infrastructure')
            ->where('date_mesure', '>=', $chartCutoff)
            ->oldest('date_mesure')
            ->get()
            ->map(fn (MesureQualite $mesure) => $this->recordForView($mesure))
            ->values();

        $points = PointMesure::with('derniereMesure')
            ->where('statut', 'actif')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get()
            ->map(fn (PointMesure $point) => $this->pointForMap($point));
        $user = session('user', ['name' => 'Dr. Selim Dridi', 'role' => 'manager']);

        $allPointsForFilter = PointMesure::with('infrastructure')
            ->orderBy('nom')
            ->get(['id', 'nom', 'code', 'infrastructure_id']);

        return view('manager.quality', compact(
            'records',
            'paginator',
            'latestMeasurement',
            'latestAssessment',
            'averageCompliance',
            'activePointsCount',
            'recentMeasurementsCount',
            'activeAlertsCount',
            'realDataCoverage',
            'chartRecords',
            'points',
            'allPointsForFilter',
            'user'
        ));
    }

    public function create(): View
    {
        abort_unless(PointMesure::where('statut', 'actif')->exists(), 404, 'Créez d\'abord un point de mesure actif.');

        return view('manager.quality-measures.create', [
            'points' => PointMesure::with('infrastructure')->where('statut', 'actif')->orderBy('nom')->get(),
        ]);
    }

    public function store(StoreMesureQualiteRequest $request): RedirectResponse
    {
        $this->authorize('create', MesureQualite::class);

        $validated  = $request->validated();
        $assessment = $this->waterQualityService->assess($validated);

        $mesure = DB::transaction(function () use ($validated, $assessment) {
            $mesure = MesureQualite::create($validated + [
                'status'             => $assessment['status'],
                'overall_compliance' => $assessment['overall_compliance'],
            ]);

            AuditLog::record('measurement_created', 'MesureQualite', $mesure->id, [
                'reference' => $mesure->reference,
                'point_id'  => $mesure->point_mesure_id,
                'status'    => $mesure->status,
            ], auth()->user());

            return $mesure;
        });

        return redirect()->route('manager.quality')->with('success', 'La mesure a été enregistrée avec succès.');
    }

    public function show(MesureQualite $mesure): View
    {
        $mesure->load('pointMesure.infrastructure');

        return view('manager.quality-measures.show', compact('mesure'));
    }

    public function edit(MesureQualite $mesure): View
    {
        return view('manager.quality-measures.edit', [
            'mesure' => $mesure,
            'points' => PointMesure::with('infrastructure')->orderBy('nom')->get(),
        ]);
    }

    public function update(UpdateMesureQualiteRequest $request, MesureQualite $mesure): RedirectResponse
    {
        $this->authorize('update', $mesure);

        $validated  = $request->validated();
        $assessment = $this->waterQualityService->assess($validated);

        DB::transaction(function () use ($validated, $assessment, $mesure) {
            $mesure->update($validated + [
                'status'             => $assessment['status'],
                'overall_compliance' => $assessment['overall_compliance'],
            ]);

            AuditLog::record('measurement_updated', 'MesureQualite', $mesure->id, [
                'reference' => $mesure->reference,
                'status'    => $mesure->status,
            ], auth()->user());
        });

        return redirect()->route('manager.quality')->with('success', 'La mesure a été mise à jour avec succès.');
    }

    public function destroy(MesureQualite $mesure): RedirectResponse
    {
        $this->authorize('delete', $mesure);

        DB::transaction(function () use ($mesure) {
            $ref = $mesure->reference;
            $mesure->delete();

            AuditLog::record('measurement_deleted', 'MesureQualite', null, [
                'reference' => $ref,
            ], auth()->user());
        });

        return redirect()->route('manager.quality')->with('success', 'La mesure a été supprimée avec succès.');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'mesure_ids' => ['required', 'array', 'min:1', 'max:25'],
            'mesure_ids.*' => ['required', 'integer', 'distinct', 'exists:mesures_qualites,id'],
        ]);

        $mesures = MesureQualite::whereIn('id', $validated['mesure_ids'])->get();
        foreach ($mesures as $mesure) {
            $this->authorize('delete', $mesure);
        }

        DB::transaction(function () use ($mesures) {
            foreach ($mesures as $mesure) {
                $reference = $mesure->reference;
                $id = $mesure->id;
                $mesure->delete();

                AuditLog::record('measurement_deleted', 'MesureQualite', null, [
                    'measurement_id' => $id,
                    'reference' => $reference,
                    'bulk' => true,
                ], auth()->user());
            }
        });

        return redirect()->route('manager.quality')
            ->with('success', count($validated['mesure_ids']) . ' mesure(s) supprimée(s) avec succès.');
    }

    public function validateMeasurement(MesureQualite $mesure): RedirectResponse
    {
        $this->authorize('validate', $mesure);

        /** @var \App\Models\User $user */
        $user = auth()->user()->load('roles');

        $verifierName = $user->name . ' (' . $user->primary_role . ')';

        DB::transaction(function () use ($mesure, $verifierName, $user) {
            $mesure->update([
                'is_verified' => true,
                'verifier'    => $verifierName,
            ]);

            AuditLog::record('measurement_validated', 'MesureQualite', $mesure->id, [
                'reference'    => $mesure->reference,
                'validated_by' => $user->email,
            ], $user);
        });

        return redirect()->back()->with('success', "La mesure {$mesure->reference} a été validée avec succès.");
    }

    /**
     * Map a MesureQualite to a view array.
     *
     * Uses persisted status/overall_compliance from the database rather than
     * re-running assess() on every page load.
     *
     * @return array<string, mixed>
     */
    private function recordForView(MesureQualite $mesure): array
    {
        return [
            'id'                 => $mesure->reference,
            'measurement_id'     => $mesure->id,
            'point_mesure_id'    => $mesure->point_mesure_id,
            'sampling_point'     => $mesure->pointMesure?->nom ?? 'Point supprimé',
            'infrastructure'     => $mesure->pointMesure?->infrastructure?->nom,
            'zone'               => $mesure->pointMesure?->zone ?? '—',
            'date'               => $mesure->date_mesure?->format('Y-m-d H:i') ?? '-',
            'pH'                 => (float) ($mesure->ph ?? 0),
            'turbidity'          => (float) ($mesure->turbidite ?? 0),
            'residual_chlorine'  => (float) ($mesure->chlore_residuel ?? 0),
            'lead_pb'            => (float) ($mesure->plomb ?? 0),
            'nitrates'           => (float) ($mesure->nitrates ?? 0),
            'source_donnee'      => $mesure->source_donnee ?? 'manuel',
            'is_verified'        => $mesure->is_verified,
            'verifier'           => $mesure->verifier,
            'overall_compliance' => (float) $mesure->overall_compliance,
            'status'             => $mesure->status,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function pointForMap(PointMesure $point): array
    {
        $measurement = $point->derniereMesure;
        $assessment  = $measurement
            ? $this->waterQualityService->assess($measurement->only([
                'ph', 'turbidite', 'chlore_residuel', 'plomb', 'nitrates',
            ]))
            : null;

        return [
            'id'                 => $point->id,
            'name'               => $point->nom,
            'code'               => $point->code,
            'zone'               => $point->zone,
            'address'            => $point->adresse,
            'lat'                => (float) $point->latitude,
            'lng'                => (float) $point->longitude,
            'type'               => $point->type,
            'status'             => match ($assessment['status'] ?? null) {
                'Non-Compliant' => 'critical',
                'Alert'         => 'alert',
                default         => 'normal',
            },
            'measurement_status' => $assessment['status'] ?? 'No measurement',
            'overall_compliance' => $assessment['overall_compliance'] ?? null,
            'measurement'        => $measurement ? [
                'reference'       => $measurement->reference,
                'date'            => $measurement->date_mesure?->format('Y-m-d H:i') ?? '-',
                'ph'              => (float) ($measurement->ph ?? 0),
                'turbidite'       => (float) ($measurement->turbidite ?? 0),
                'chlore_residuel' => (float) ($measurement->chlore_residuel ?? 0),
                'plomb'           => (float) ($measurement->plomb ?? 0),
                'nitrates'        => (float) ($measurement->nitrates ?? 0),
                'is_verified'     => $measurement->is_verified,
                'verifier'        => $measurement->verifier,
            ] : null,
        ];
    }
}
