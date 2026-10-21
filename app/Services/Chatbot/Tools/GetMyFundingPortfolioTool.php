<?php

namespace App\Services\Chatbot\Tools;

use App\Models\Financement;
use App\Services\Chatbot\ToolExecutor;

/**
 * Retourne le portefeuille personnel du citoyen connecté.
 * Réutilise le modèle Financement (total des contributions, projets concernés).
 */
class GetMyFundingPortfolioTool implements ToolExecutor
{
    public function name(): string
    {
        return 'get_my_funding_portfolio';
    }

    public function isAuthorized(mixed $user): bool
    {
        return $user !== null && method_exists($user, 'hasAnyRole') && $user->hasAnyRole([\App\Models\Role::CITOYEN]);
    }

    public function validateArguments(array $arguments): array
    {
        return [];
    }

    public function execute(mixed $user, array $validatedArguments): array
    {
        $userId = (int) $user->id;

        $totalContributions = (float) Financement::query()
            ->where('user_id', $userId)
            ->sum('montant');

        $projetsSoutenus = (int) Financement::query()
            ->where('user_id', $userId)
            ->distinct()
            ->count('projet_id');

        $projetsTermines = (int) Financement::query()
            ->where('user_id', $userId)
            ->whereHas('projet', fn ($q) => $q->where('statut', 'termine'))
            ->distinct()
            ->count('projet_id');

        return [
            'user_id' => $userId,
            'total_contributions' => $totalContributions,
            'projets_soutenus' => $projetsSoutenus,
            'projets_termines_soutenus' => $projetsTermines,
            'devise' => 'TND',
        ];
    }
}
