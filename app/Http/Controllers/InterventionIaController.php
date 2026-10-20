<?php

namespace App\Http\Controllers;

use App\Models\Infrastructure;
use App\Services\TechnicianRecommendationService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InterventionIaController extends Controller
{
    /**
     * Recommandation IA du technicien pour le formulaire d'intervention (appel AJAX).
     */
    public function recommander(Request $request, TechnicianRecommendationService $service): JsonResponse
    {
        $validated = $request->validate([
            'date' => ['required', 'date'],
            'description' => ['nullable', 'string', 'max:1000'],
            'infrastructure_id' => ['nullable', 'integer', 'exists:infrastructures,id'],
            'intervention_id' => ['nullable', 'integer', 'exists:interventions,id'],
        ], [
            'date.required' => 'Choisissez d\'abord la date de l\'intervention.',
            'date.date' => 'La date de l\'intervention est invalide.',
        ]);

        $infrastructure = isset($validated['infrastructure_id'])
            ? Infrastructure::with('zone')->find($validated['infrastructure_id'])
            : null;

        return response()->json($service->recommander(
            $infrastructure,
            Carbon::parse($validated['date'])->startOfDay(),
            (string) ($validated['description'] ?? ''),
            isset($validated['intervention_id']) ? (int) $validated['intervention_id'] : null,
        ));
    }
}
