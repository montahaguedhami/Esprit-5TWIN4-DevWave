<?php

namespace App\Services;

use App\Models\Financement;
use App\Models\Projet;
use Illuminate\Support\Collection;

/**
 * Indicateurs budgétaires de /manager/budget.
 *
 * Unique source de vérité : la page web ET l'export PDF/CSV lisent ce même
 * service, sinon le document pourrait un jour contredire l'écran.
 *
 * Toutes les valeurs sont recalculées depuis la base à chaque appel — aucun
 * montant n'est stocké, donc pas de risque de dérive.
 */
class BudgetReportService
{
    /**
     * Les 7 variables consommées par la vue manager.budget.
     *
     * @return array{
     *     projets: Collection<int, Projet>,
     *     financements: Collection<int, Financement>,
     *     totalBudget: float,
     *     totalFinance: float,
     *     budgetRestant: float,
     *     pourcentageUtilise: float,
     *     sourcesUniques: int
     * }
     */
    public function stats(): array
    {
        $projets            = Projet::with('financements')->get();
        $financements       = Financement::with('projet')->latest()->get();
        $totalBudget        = (float) $projets->sum('budget');
        $totalFinance       = (float) $financements->sum('montant');
        $budgetRestant      = $totalBudget - $totalFinance;
        $pourcentageUtilise = $totalBudget > 0 ? ($totalFinance / $totalBudget * 100) : 0.0;
        $sourcesUniques     = $financements->pluck('source')->unique()->count();

        return compact(
            'projets',
            'financements',
            'totalBudget',
            'totalFinance',
            'budgetRestant',
            'pourcentageUtilise',
            'sourcesUniques'
        );
    }

    /**
     * Synthèse ligne par ligne : un projet = une ligne, tous les projets,
     * y compris ceux sans financement.
     *
     * Réutilise `$stats['projets']` déjà chargé : aucune requête supplémentaire,
     * donc pas de N+1 sur les grands volumes.
     *
     * @param  array<string, mixed>  $stats
     * @return Collection<int, array<string, mixed>>
     */
    public function syntheseParProjet(array $stats): Collection
    {
        return $stats['projets']
            ->map(function (Projet $projet): array {
                $finance = (float) $projet->financements->sum('montant');
                $budget  = (float) $projet->budget;

                return [
                    'projet'         => $projet,
                    'nom'            => $projet->nom,
                    'statut'         => $projet->statut,
                    'budget'         => $budget,
                    'finance'        => $finance,
                    'restant'        => $budget - $finance,
                    'pourcentage'    => $budget > 0 ? ($finance / $budget) * 100 : 0.0,
                    'nbFinancements' => $projet->financements->count(),
                ];
            })
            ->sortByDesc('budget')
            ->values();
    }

    /**
     * Répartition par source de financement, ordre décroissant par montant.
     *
     * @param  array<string, mixed>  $stats
     * @return Collection<int, array{source: string, montant: float, nombre: int}>
     */
    public function syntheseParSource(array $stats): Collection
    {
        return $stats['financements']
            ->groupBy('source')
            ->map(fn (Collection $lignes): array => [
                'montant' => (float) $lignes->sum('montant'),
                'nombre'  => $lignes->count(),
            ])
            ->sortByDesc(fn (array $ligne): float => $ligne['montant'])
            ->map(fn (array $ligne, string $source): array => ['source' => $source] + $ligne)
            ->values();
    }
}
