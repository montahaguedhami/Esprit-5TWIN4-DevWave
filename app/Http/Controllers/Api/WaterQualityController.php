<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MesureQualite;
use App\Models\PointMesure;
use App\Services\WaterQualityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WaterQualityController extends Controller
{
    public function __construct(private readonly WaterQualityService $waterQualityService) {}

    public function points(): JsonResponse
    {
        return response()->json([
            'data' => PointMesure::query()
                ->where('statut', 'actif')
                ->orderBy('nom')
                ->get(),
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $query = MesureQualite::with('pointMesure')->latest('date_mesure');

        if ($request->filled('point_mesure_id')) {
            $query->where('point_mesure_id', $request->integer('point_mesure_id'));
        }

        return response()->json([
            'data' => $query->paginate($request->integer('per_page', 15)),
        ]);
    }

    public function show(MesureQualite $mesure): JsonResponse
    {
        return response()->json(['data' => $mesure->load('pointMesure')]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'reference' => ['required', 'string', 'max:50', 'unique:mesures_qualites,reference'],
            'point_mesure_id' => ['required', 'integer', 'exists:point_mesures,id'],
            'date_mesure' => ['required', 'date'],
            'ph' => ['required', 'numeric', 'between:0,14'],
            'turbidite' => ['required', 'numeric', 'min:0'],
            'chlore_residuel' => ['required', 'numeric', 'min:0'],
            'plomb' => ['required', 'numeric', 'min:0'],
            'nitrates' => ['required', 'numeric', 'min:0'],
            'is_verified' => ['sometimes', 'boolean'],
            'verifier' => ['nullable', 'string', 'max:255'],
        ]);

        $assessment = $this->waterQualityService->assess($validated);
        $mesure = MesureQualite::create($validated + [
            'is_verified' => $validated['is_verified'] ?? false,
            'status' => $assessment['status'],
            'overall_compliance' => $assessment['overall_compliance'],
        ]);

        return response()->json([
            'message' => 'La mesure a été enregistrée.',
            'data' => $mesure->load('pointMesure'),
            'assessment' => $assessment,
        ], 201);
    }
}
