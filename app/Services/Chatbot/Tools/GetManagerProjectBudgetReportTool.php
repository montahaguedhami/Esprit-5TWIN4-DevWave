<?php

namespace App\Services\Chatbot\Tools;

use App\Models\Projet;
use App\Services\BudgetReportService;
use App\Services\Chatbot\ToolExecutor;

/**
 * Retourne les statistiques budgétaires auxquelles le manager a accès.
 *
 * Réutilise BudgetReportService comme source de vérité (mêmes données que
 * l'écran manager/budget et les exports PDF/CSV). Ne duplique pas les calculs.
 */
class GetManagerProjectBudgetReportTool implements ToolExecutor
{
    public function __construct(
        private readonly BudgetReportService $budgetReport,
    ) {
    }

    public function name(): string
    {
        return 'get_manager_project_budget_report';
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
        $stats    = $this->budgetReport->stats();
        $synthese = $this->budgetReport->syntheseParProjet($stats);

        $projets = $synthese->map(fn (array $ligne): array => [
            'projet_id'      => (int)   $ligne['projet']->id,
            'nom'            =>         $ligne['nom'],
            'statut'         =>         $ligne['statut'],
            'budget'         => (float) $ligne['budget'],
            'finance'        => (float) $ligne['finance'],
            'restant'        => (float) $ligne['restant'],
            'pourcentage'    => (float) $ligne['pourcentage'],
            'nb_financements'=> (int)   $ligne['nbFinancements'],
        ])->values()->all();

        return [
            'total_budget'        => (float) $stats['totalBudget'],
            'total_finance'       => (float) $stats['totalFinance'],
            'budget_restant'      => (float) $stats['budgetRestant'],
            'pourcentage_utilise' => (float) $stats['pourcentageUtilise'],
            'sources_uniques'     => (int)   $stats['sourcesUniques'],
            'projets'             => $projets,
            'source'              => 'App\\Services\\BudgetReportService',
        ];
    }
}
