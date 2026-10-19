<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Services\BudgetReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Export du rapport financier de la page Budget.
 *
 * Mêmes données que l'écran : BudgetReportService est partagé, donc le
 * document ne peut pas contredire ce que le gestionnaire voit.
 */
class BudgetExportController extends Controller
{
    /**
     * Rapport complet en CSV (Excel FR : séparateur `;`, décimale `,`, BOM UTF-8).
     */
    public function exportCsv(BudgetReportService $service): StreamedResponse
    {
        $stats   = $service->stats();
        $date    = now()->format('d/m/Y à H:i');
        $filename = 'rapport-budget-' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($service, $stats, $date): void {
            $handle = fopen('php://output', 'w');

            // BOM UTF-8 : ouverture correcte dans Excel sans curseur d'accents.
            fwrite($handle, "\xEF\xBB\xBF");

            $money = fn (float $v): string => number_format($v, 2, ',', '');
            $pct   = fn (float $v): string => number_format($v, 1, ',', '');

            $this->ligne($handle, ['AquaSecure — Rapport financier et budgétaire']);
            $this->ligne($handle, ['Généré le', $date]);
            $this->ligne($handle, ['Émetteur', 'Espace gestionnaire — ' . (auth()->user()->name ?? 'Gestionnaire')]);
            $this->ligne($handle, ['Source', config('app.url') . '/manager/budget']);
            $this->ligne($handle);

            // ── 1. Synthèse ──────────────────────────────────────────────
            $this->ligne($handle, ['SYNTHÈSE']);
            $this->ligne($handle, ['Indicateur', 'Valeur']);
            $this->ligne($handle, ['Budget total des projets (DT)', $money((float) $stats['totalBudget'])]);
            $this->ligne($handle, ['Total financé (DT)', $money((float) $stats['totalFinance'])]);
            $this->ligne($handle, ['Budget restant (DT)', $money((float) $stats['budgetRestant'])]);
            $this->ligne($handle, ['Taux de financement (%)', $pct((float) $stats['pourcentageUtilise'])]);
            $this->ligne($handle, ['Nombre de projets', (string) $stats['projets']->count()]);
            $this->ligne($handle, ['Nombre de financements', (string) $stats['financements']->count()]);
            $this->ligne($handle, ['Sources de financement distinctes', (string) $stats['sourcesUniques']]);
            $this->ligne($handle);

            // ── 2. Détail des financements (même contenu que le tableau) ─
            $this->ligne($handle, ['FINANCEMENTS (' . $stats['financements']->count() . ')']);
            $this->ligne($handle, [
                'Date', 'Projet', 'Source de financement',
                'Montant (DT)', 'Budget projet (DT)', '% Financé du projet',
            ]);

            foreach ($stats['financements'] as $financement) {
                $projet = $financement->projet;

                $this->ligne($handle, [
                    $financement->date_financement?->format('d/m/Y') ?? '',
                    $projet?->nom ?? 'Projet supprimé',
                    $financement->source,
                    $money((float) $financement->montant),
                    $projet ? $money((float) $projet->budget) : '',
                    $projet ? $pct((float) $projet->pourcentage_finance) : '',
                ]);
            }

            $this->ligne($handle, [
                'TOTAL', '', '', $money((float) $stats['totalFinance']), '', '',
            ]);
            $this->ligne($handle);

            // ── 3. Synthèse par projet ───────────────────────────────────
            $parProjet = $service->syntheseParProjet($stats);

            $this->ligne($handle, ['SITUATION PAR PROJET (' . $parProjet->count() . ')']);
            $this->ligne($handle, [
                'Projet', 'Statut', 'Budget (DT)', 'Financé (DT)',
                'Restant (DT)', '% Financé', 'Nb financements',
            ]);

            foreach ($parProjet as $ligne) {
                $this->ligne($handle, [
                    $ligne['nom'],
                    $ligne['statut'],
                    $money($ligne['budget']),
                    $money($ligne['finance']),
                    $money($ligne['restant']),
                    $pct($ligne['pourcentage']),
                    (string) $ligne['nbFinancements'],
                ]);
            }

            $this->ligne($handle, [
                'TOTAL', '', $money((float) $stats['totalBudget']),
                $money((float) $stats['totalFinance']),
                $money((float) $stats['budgetRestant']),
                $pct((float) $stats['pourcentageUtilise']), '',
            ]);
            $this->ligne($handle);

            // ── 4. Répartition par source ────────────────────────────────
            $parSource = $service->syntheseParSource($stats);
            $total     = (float) $stats['totalFinance'];

            $this->ligne($handle, ['RÉPARTITION PAR SOURCE DE FINANCEMENT (' . $parSource->count() . ')']);
            $this->ligne($handle, ['Source', 'Nb financements', 'Montant (DT)', '% du total financé']);

            foreach ($parSource as $ligne) {
                $this->ligne($handle, [
                    $ligne['source'],
                    (string) $ligne['nombre'],
                    $money($ligne['montant']),
                    $pct($total > 0 ? $ligne['montant'] / $total * 100 : 0),
                ]);
            }

            $this->ligne($handle, ['TOTAL', (string) $stats['financements']->count(), $money($total), $pct(100)]);

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * Rapport complet en PDF (A4 paysage, généré localement par dompdf).
     */
    public function exportPdf(BudgetReportService $service): Response
    {
        $stats = $service->stats();

        $pdf = Pdf::loadView('manager.exports.budget-pdf', [
            'stats'       => $stats,
            'parProjet'   => $service->syntheseParProjet($stats),
            'parSource'   => $service->syntheseParSource($stats),
            'emetteur'    => auth()->user()->name ?? 'Gestionnaire',
            'generatedAt' => now()->format('d/m/Y à H:i'),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('rapport-budget-' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Écrit une ligne CSV ; une ligne vide sépare les sections.
     *
     * @param  array<int, string>  $champs
     */
    private function ligne($handle, array $champs = []): void
    {
        if ($champs === []) {
            fwrite($handle, "\n");

            return;
        }

        fputcsv($handle, $champs, ';');
    }
}
