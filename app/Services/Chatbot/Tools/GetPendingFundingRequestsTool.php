<?php

namespace App\Services\Chatbot\Tools;

use App\Models\DemandeFinancement;
use App\Services\Chatbot\ToolExecutor;

/**
 * Retourne les demandes en attente de décision (réservé au manager).
 * Ne retourne que les informations nécessaires au traitement.
 */
class GetPendingFundingRequestsTool implements ToolExecutor
{
    public function name(): string
    {
        return 'get_pending_funding_requests';
    }

    public function isAuthorized(mixed $user): bool
    {
        return $user !== null && method_exists($user, 'hasAnyRole') && $user->hasAnyRole([\App\Models\Role::MANAGER]);
    }

    public function validateArguments(array $arguments): array
    {
        return [];
    }

    public function execute(mixed $user, array $validatedArguments): array
    {
        $demandes = DemandeFinancement::query()
            ->where('statut', DemandeFinancement::EN_ATTENTE)
            ->with(['projet', 'citoyen'])
            ->latest()
            ->get();

        return [
            'demandes_en_attente' => $demandes->map(fn (DemandeFinancement $d): array => [
                'id' => (int) $d->id,
                'projet_id' => (int) $d->projet_id,
                'projet_nom' => $d->projet?->nom,
                'citoyen_id' => (int) $d->user_id,
                'citoyen_nom' => $d->citoyen?->name,
                'montant' => (float) $d->montant,
                'devise' => $d->devise,
                'created_at' => $d->created_at?->toDateTimeString(),
            ])->values()->all(),
            'total' => count($demandes),
        ];
    }
}
