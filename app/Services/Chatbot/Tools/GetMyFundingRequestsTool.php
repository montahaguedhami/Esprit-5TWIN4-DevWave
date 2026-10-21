<?php

namespace App\Services\Chatbot\Tools;

use App\Models\DemandeFinancement;
use App\Services\Chatbot\ToolExecutor;

/**
 * Retourne uniquement les demandes de financement de l'utilisateur connecté.
 * Le propriétaire provient de l'utilisateur authentifié, jamais des arguments.
 */
class GetMyFundingRequestsTool implements ToolExecutor
{
    public function name(): string
    {
        return 'get_my_funding_requests';
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

        $demandes = DemandeFinancement::query()
            ->where('user_id', $userId)
            ->with('projet')
            ->latest()
            ->get();

        return [
            'user_id' => $userId,
            'demandes' => $demandes->map(fn (DemandeFinancement $d): array => [
                'id' => (int) $d->id,
                'user_id' => (int) $d->user_id,
                'projet_id' => (int) $d->projet_id,
                'projet_nom' => $d->projet?->nom,
                'montant' => (float) $d->montant,
                'devise' => $d->devise,
                'statut' => $d->statut,
                'libelle_statut' => $d->libelleStatut(),
                'motif_refus' => $d->motif_refus,
                'created_at' => $d->created_at?->toDateTimeString(),
                'decided_at' => $d->decided_at?->toDateTimeString(),
            ])->values()->all(),
            'total' => count($demandes),
        ];
    }
}
