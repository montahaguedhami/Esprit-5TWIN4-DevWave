<?php

namespace App\Services\Chatbot\Tools;

use App\Models\Paiement;
use App\Services\Chatbot\ToolExecutor;

/**
 * Retourne uniquement les paiements de l'utilisateur connecté.
 * Explique clairement que les paiements sont simulés.
 */
class GetMyPaymentsTool implements ToolExecutor
{
    public function name(): string
    {
        return 'get_my_payments';
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

        $paiements = Paiement::query()
            ->where('user_id', $userId)
            ->with('demande.projet')
            ->latest()
            ->get();

        return [
            'user_id' => $userId,
            'paiements' => $paiements->map(fn (Paiement $p): array => [
                'id' => (int) $p->id,
                'demande_id' => (int) $p->demande_id,
                'projet_id' => (int) $p->projet_id,
                'projet_nom' => $p->projet?->nom,
                'montant' => (float) $p->montant,
                'devise' => $p->devise,
                'statut' => $p->statut,
                'libelle_statut' => $p->libelleStatut(),
                'motif' => $p->motif,
                'reference_interne' => $p->reference_interne,
                'confirmed_at' => $p->confirmed_at?->toDateTimeString(),
                'created_at' => $p->created_at?->toDateTimeString(),
            ])->values()->all(),
            'total' => count($paiements),
            'note' => 'Les paiements sont simulés à des fins académiques. Aucun argent réel n\'est transféré.',
        ];
    }
}
