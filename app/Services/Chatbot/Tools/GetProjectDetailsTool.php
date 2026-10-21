<?php

namespace App\Services\Chatbot\Tools;

use App\Models\Projet;
use App\Services\Chatbot\ToolExecutor;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Retourne les informations autorisées d'un projet précis.
 */
class GetProjectDetailsTool implements ToolExecutor
{
    public function name(): string
    {
        return 'get_project_details';
    }

    public function isAuthorized(mixed $user): bool
    {
        return $user !== null;
    }

    public function validateArguments(array $arguments): array
    {
        $validator = Validator::make($arguments, [
            'projet_id' => ['required', 'integer', 'exists:projets,id'],
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }

    public function execute(mixed $user, array $validatedArguments): array
    {
        $projet = Projet::with('zones')->findOrFail((int) $validatedArguments['projet_id']);

        return [
            'id' => (int) $projet->id,
            'nom' => $projet->nom,
            'description' => $projet->description,
            'date_debut' => $projet->date_debut?->toDateString(),
            'date_fin' => $projet->date_fin?->toDateString(),
            'budget' => (float) $projet->budget,
            'statut' => $projet->statut,
            'progression' => (int) $projet->progression,
            'adresse' => $projet->adresse,
            'budget_disponible' => (float) $projet->budget_disponible,
            'total_finance' => (float) $projet->total_finance,
            'montant_reserve' => (float) $projet->montant_reserve,
            'eligible_au_financement_citoyen' => $projet->estEligibleAuFinancementCitoyen(),
            'zones' => $projet->zones->pluck('nom')->filter()->values()->all(),
            'has_geolocation' => $projet->hasGeolocation(),
        ];
    }
}
