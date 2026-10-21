<?php

namespace App\Services\Chatbot\Tools;

use App\Models\DemandeFinancement;
use App\Models\Financement;
use App\Services\Chatbot\ToolExecutor;

/**
 * Retourne les statistiques globales de financement réservées au manager.
 * Réutilise les calculs des services métier existants.
 */
class GetFundingDashboardSummaryTool implements ToolExecutor
{
    public function name(): string
    {
        return 'get_funding_dashboard_summary';
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
        $totalDemandes = DemandeFinancement::query()->count();
        $pending = DemandeFinancement::query()->where('statut', DemandeFinancement::EN_ATTENTE)->count();
        $acceptees = DemandeFinancement::query()->where('statut', DemandeFinancement::ACCEPTEE)->count();
        $refusees = DemandeFinancement::query()->where('statut', DemandeFinancement::REFUSEE)->count();
        $payees = DemandeFinancement::query()->where('statut', DemandeFinancement::PAYEE)->count();

        $totalFinance = (float) Financement::query()->sum('montant');

        return [
            'total_demandes' => (int) $totalDemandes,
            'demandes_en_attente' => (int) $pending,
            'demandes_acceptees' => (int) $acceptees,
            'demandes_refusees' => (int) $refusees,
            'demandes_payees' => (int) $payees,
            'total_finance_tnd' => $totalFinance,
            'devise' => 'TND',
            'source' => 'demandes_financement + financements (agrégats SQL)',
        ];
    }
}
