<?php

namespace App\Services\Chatbot\Tools;

use App\Models\Projet;
use App\Services\Chatbot\ToolExecutor;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Retourne les informations budgétaires autorisées d'un projet.
 *
 * Réutilise impérativement les accessors du modèle Projet (budget_disponible,
 * total_finance, montant_reserve) qui sont la source de vérité métier.
 * Ne recalcule jamais le budget avec une nouvelle formule.
 */
class GetProjectFundingStatusTool implements ToolExecutor
{
    public function name(): string
    {
        return 'get_project_funding_status';
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
        $projet = Projet::findOrFail((int) $validatedArguments['projet_id']);

        $budget = (float) $projet->budget;
        $totalFinance = (float) $projet->total_finance;
        $montantReserve = (float) $projet->montant_reserve;
        $budgetDisponible = (float) $projet->budget_disponible;

        return [
            'projet_id' => (int) $projet->id,
            'nom' => $projet->nom,
            'budget' => $budget,
            'total_finance' => $totalFinance,
            'montant_reserve' => $montantReserve,
            'budget_disponible' => $budgetDisponible,
            'pourcentage_finance' => $budget > 0 ? round(($totalFinance / $budget) * 100, 2) : 0.0,
            'statut' => $projet->statut,
            'eligible_au_financement_citoyen' => $projet->estEligibleAuFinancementCitoyen(),
            'source' => 'App\\Models\\Projet accessors (budget_disponible, total_finance, montant_reserve)',
        ];
    }
}
