<?php

namespace App\Services\Chatbot\Tools;

use App\Models\Financement;
use App\Services\Chatbot\ToolExecutor;

/**
 * Retourne les contributions du citoyen connecté avec les projets associés.
 * Ne retourne jamais les contributions d'autres citoyens.
 */
class GetMyProjectContributionsTool implements ToolExecutor
{
    public function name(): string
    {
        return 'get_my_project_contributions';
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

        $contributions = Financement::query()
            ->where('user_id', $userId)
            ->with('projet')
            ->orderByDesc('date_financement')
            ->orderByDesc('id')
            ->get();

        return [
            'user_id' => $userId,
            'contributions' => $contributions->map(fn (Financement $f): array => [
                'id' => (int) $f->id,
                'projet_id' => (int) $f->projet_id,
                'projet_nom' => $f->projet?->nom,
                'montant' => (float) $f->montant,
                'date_financement' => $f->date_financement?->toDateString(),
                'source' => $f->source,
            ])->values()->all(),
            'total' => count($contributions),
        ];
    }
}
