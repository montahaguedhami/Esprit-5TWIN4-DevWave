<?php

namespace App\Http\Controllers;

use App\Models\Incident;
use App\Services\Ai\AiUnavailableException;
use App\Services\Ai\IncidentAiAssistantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Read-only AI endpoints: they return suggestions as JSON and never modify an incident or an action.
 */
class IncidentAiController extends Controller
{
    public function __construct(private readonly IncidentAiAssistantService $assistant)
    {
    }

    /** Citizen: advice for an incident being written (before submission). */
    public function citizenPreview(Request $request): JsonResponse
    {
        $data = $request->validate([
            'type' => ['required', Rule::in(IncidentAiAssistantService::TYPES)],
            'description' => ['required', 'string', 'min:10', 'max:5000'],
            'titre' => ['nullable', 'string', 'max:255'],
            'gravite' => ['nullable', Rule::in(IncidentAiAssistantService::PRIORITIES)],
        ], [
            'type.required' => 'Choisissez d’abord le type d’incident.',
            'description.required' => 'Décrivez d’abord le problème.',
            'description.min' => 'Décrivez le problème en quelques mots (10 caractères minimum).',
        ]);

        return $this->respond(fn () => $this->assistant->citizenAdvice($data));
    }

    /** Citizen: advice for one of their own incidents. */
    public function citizenAdvice(Request $request, Incident $incident): JsonResponse
    {
        abort_unless((int) $incident->user_id === (int) $request->attributes->get('incident_user_id'), 404);

        return $this->respond(fn () => $this->assistant->citizenAdvice([
            'type' => $incident->type,
            'description' => $incident->description,
            'titre' => $incident->titre,
            'gravite' => $incident->gravite,
        ], $request->boolean('refresh')));
    }

    /** Manager: technical analysis and corrective action proposals. */
    public function managerAnalysis(Request $request, Incident $incident): JsonResponse
    {
        return $this->respond(fn () => $this->assistant->managerAnalysis($incident, $request->boolean('refresh')));
    }

    private function respond(callable $callback): JsonResponse
    {
        try {
            return response()->json(['data' => $callback()]);
        } catch (AiUnavailableException $e) {
            return response()->json(['message' => $e->getMessage()], 503);
        }
    }
}
