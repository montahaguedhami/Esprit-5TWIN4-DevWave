<?php

namespace App\Services\Chatbot;

use App\Models\User;
use App\Services\Chatbot\Tools\GetFundingDashboardSummaryTool;
use App\Services\Chatbot\Tools\GetFundingRulesTool;
use App\Services\Chatbot\Tools\GetManagerProjectBudgetReportTool;
use App\Services\Chatbot\Tools\GetMyFundingPortfolioTool;
use App\Services\Chatbot\Tools\GetMyFundingRequestsTool;
use App\Services\Chatbot\Tools\GetMyPaymentsTool;
use App\Services\Chatbot\Tools\GetMyProjectContributionsTool;
use App\Services\Chatbot\Tools\GetPendingFundingRequestsTool;
use App\Services\Chatbot\Tools\SearchProjectsTool;

/**
 * Répond directement aux questions simples sans appel LLM.
 *
 * Exécute l'outil identifié par le QuestionClassifier, puis formate
 * la réponse en Markdown lisible. Zéro token Groq consommé.
 */
class DirectToolResponder
{
    public function __construct(
        private readonly SearchProjectsTool $searchProjects,
        private readonly GetMyFundingRequestsTool $getMyFundingRequests,
        private readonly GetMyPaymentsTool $getMyPayments,
        private readonly GetMyFundingPortfolioTool $getMyFundingPortfolio,
        private readonly GetMyProjectContributionsTool $getMyProjectContributions,
        private readonly GetFundingRulesTool $getFundingRules,
        private readonly GetFundingDashboardSummaryTool $getFundingDashboardSummary,
        private readonly GetPendingFundingRequestsTool $getPendingFundingRequests,
        private readonly GetManagerProjectBudgetReportTool $getManagerProjectBudgetReport,
    ) {}

    /**
     * Exécute l'outil et retourne une réponse Markdown formatée.
     *
     * @param  User   $user
     * @param  string $toolName  Nom de l'outil (depuis QuestionClassifier)
     * @param  array  $toolArgs  Arguments pré-calculés
     * @return string|null       null si l'outil est inconnu ou non autorisé
     */
    public function respond(User $user, string $toolName, array $toolArgs): ?string
    {
        $executor = $this->getExecutor($toolName);
        if ($executor === null) return null;
        if (! $executor->isAuthorized($user)) return null;

        try {
            $validatedArgs = $executor->validateArguments($toolArgs);
            $data          = $executor->execute($user, $validatedArgs);
        } catch (\Throwable $e) {
            return null; // Laisser l'orchestrateur fallback vers LLM
        }

        return match ($toolName) {
            'search_projects'                   => $this->formatProjects($data),
            'get_my_funding_requests'           => $this->formatMyRequests($data),
            'get_my_payments'                   => $this->formatMyPayments($data),
            'get_my_funding_portfolio'          => $this->formatMyPortfolio($data),
            'get_my_project_contributions'      => $this->formatMyContributions($data),
            'get_funding_rules'                 => $this->formatFundingRules($data),
            'get_funding_dashboard_summary'     => $this->formatDashboard($data),
            'get_pending_funding_requests'      => $this->formatPendingRequests($data),
            'get_manager_project_budget_report' => $this->formatBudgetReport($data),
            default => null,
        };
    }

    // ── Formatters ────────────────────────────────────────────────────────

    private function formatProjects(array $data): string
    {
        $projets = $data['projets'] ?? [];
        $total   = $data['total'] ?? 0;

        if ($total === 0) {
            return "Aucun projet ne correspond à votre recherche pour le moment.";
        }

        $lines = ["Voici **{$total} projet(s)** trouvé(s) :\n"];

        foreach ($projets as $i => $p) {
            $num     = $i + 1;
            $statut  = $this->labelStatut($p['statut'] ?? '');
            $budget  = number_format($p['budget'] ?? 0, 0, ',', ' ');
            $dispo   = number_format($p['budget_disponible'] ?? 0, 0, ',', ' ');
            $prog    = $p['progression'] ?? 0;
            $zones   = implode(', ', $p['zones'] ?? []) ?: '—';
            $eligible = ($p['eligible'] ?? false) ? '✅ Oui' : '❌ Non';

            // Utiliser paragraphes séparés plutôt que listes imbriquées
            $lines[] = "**{$num}. {$p['nom']}** (ID : {$p['id']})";
            $lines[] = "Statut : {$statut} — Avancement : {$prog} % | Budget total : {$budget} TND | Disponible : {$dispo} TND";
            $lines[] = "Zones : {$zones} | Éligible financement citoyen : {$eligible}";
            $lines[] = "";
        }

        return implode("\n", $lines);
    }

    private function formatMyRequests(array $data): string
    {
        $demandes = $data['demandes'] ?? [];
        $total    = $data['total'] ?? 0;

        if ($total === 0) {
            return "Vous n'avez aucune demande de financement pour le moment.\n\n"
                . "Pour en créer une, consultez la section **Projets** et cliquez sur « Financer ce projet ».";
        }

        $lines = ["Voici vos **{$total} demande(s)** de financement :\n"];

        foreach ($demandes as $i => $d) {
            $num     = $i + 1;
            $montant = number_format($d['montant'] ?? 0, 0, ',', ' ');
            $statut  = $d['libelle_statut'] ?? $d['statut'] ?? '—';
            $date    = isset($d['created_at']) ? substr($d['created_at'], 0, 10) : '—';
            $projet  = $d['projet_nom'] ?? "Projet #{$d['projet_id']}";

            $lines[] = "**{$num}. {$projet}**";
            $lines[] = "- Montant demandé : **{$montant} TND**";
            $lines[] = "- Statut : **{$statut}**";
            $lines[] = "- Date de soumission : {$date}";

            if (!empty($d['motif_refus'])) {
                $lines[] = "- Motif de refus : {$d['motif_refus']}";
            }

            $lines[] = "";
        }

        return implode("\n", $lines);
    }

    private function formatMyPayments(array $data): string
    {
        $paiements = $data['paiements'] ?? [];
        $total     = $data['total'] ?? 0;

        if ($total === 0) {
            return "Vous n'avez aucun paiement simulé enregistré pour le moment.";
        }

        $lines = [
            "Voici vos **{$total} paiement(s) simulé(s)** :\n",
            "> ℹ️ Les paiements sont simulés à des fins académiques. Aucun argent réel n'est transféré.\n",
        ];

        foreach ($paiements as $i => $p) {
            $num     = $i + 1;
            $montant = number_format($p['montant'] ?? 0, 0, ',', ' ');
            $statut  = $p['libelle_statut'] ?? $p['statut'] ?? '—';
            $projet  = $p['projet_nom'] ?? "Projet #{$p['projet_id']}";
            $ref     = $p['reference_interne'] ?? '—';
            $date    = isset($p['created_at']) ? substr($p['created_at'], 0, 10) : '—';

            $lines[] = "**{$num}. {$projet}**";
            $lines[] = "- Montant : **{$montant} TND**";
            $lines[] = "- Statut : **{$statut}**";
            $lines[] = "- Référence : `{$ref}`";
            $lines[] = "- Date : {$date}";
            $lines[] = "";
        }

        return implode("\n", $lines);
    }

    private function formatMyPortfolio(array $data): string
    {
        $total    = number_format($data['total_contributions'] ?? 0, 0, ',', ' ');
        $projets  = $data['projets_soutenus'] ?? 0;
        $termines = $data['projets_termines_soutenus'] ?? 0;

        return <<<MD
## 📊 Votre portefeuille de contributions

| Indicateur | Valeur |
|:---|:---:|
| Total contribué | **{$total} TND** |
| Projets soutenus | **{$projets}** |
| Projets terminés soutenus | **{$termines}** |

> Chaque contribution participative aide à sécuriser le financement des infrastructures d'eau en Tunisie.
MD;
    }

    private function formatMyContributions(array $data): string
    {
        $contributions = $data['contributions'] ?? [];
        $total         = $data['total'] ?? 0;

        if ($total === 0) {
            return "Vous n'avez effectué aucune contribution pour le moment.\n\n"
                . "Consultez les projets éligibles pour commencer à contribuer.";
        }

        $lines = ["Voici vos **{$total} contribution(s)** :\n"];

        foreach ($contributions as $i => $c) {
            $num     = $i + 1;
            $montant = number_format($c['montant'] ?? 0, 0, ',', ' ');
            $projet  = $c['projet_nom'] ?? "Projet #{$c['projet_id']}";
            $date    = $c['date_financement'] ?? '—';

            $lines[] = "**{$num}. {$projet}**";
            $lines[] = "- Montant versé : **{$montant} TND**";
            $lines[] = "- Date : {$date}";
            $lines[] = "";
        }

        $totalMontant = number_format(
            array_sum(array_column($contributions, 'montant')), 0, ',', ' '
        );
        $lines[] = "---\n**Total contribué : {$totalMontant} TND**";

        return implode("\n", $lines);
    }

    private function formatFundingRules(array $data): string
    {
        $min      = number_format($data['montant_minimum_tnd'] ?? 0, 0, ',', ' ');
        $eligible = implode(', ', $data['statuts_projet_eligible'] ?? []);

        $workflow = '';
        foreach ($data['workflow'] ?? [] as $etape => $desc) {
            $workflow .= "- {$desc}\n";
        }

        return <<<MD
## 📋 Règles du financement participatif

**Montant minimum :** {$min} TND

**Projets éligibles :** statut `{$eligible}` avec budget disponible positif

### Workflow en 5 étapes

{$workflow}
### Points importants

- Les paiements sont **simulés** (aucun argent réel transféré)
- Un projet refusé nécessite un **motif obligatoire**
- Une demande déjà payée ne peut **pas être payée une seconde fois**
- Le montant provient toujours de la **base de données**, jamais du navigateur
MD;
    }

    private function formatDashboard(array $data): string
    {
        $total    = $data['total_demandes'] ?? 0;
        $attente  = $data['demandes_en_attente'] ?? 0;
        $acceptee = $data['demandes_acceptees'] ?? 0;
        $refusee  = $data['demandes_refusees'] ?? 0;
        $payee    = $data['demandes_payees'] ?? 0;
        $finance  = number_format($data['total_finance_tnd'] ?? 0, 0, ',', ' ');

        return <<<MD
## 📊 Tableau de bord — Financements

| Indicateur | Valeur |
|:---|:---:|
| Total demandes | **{$total}** |
| En attente | **{$attente}** |
| Acceptées | **{$acceptee}** |
| Refusées | **{$refusee}** |
| Payées | **{$payee}** |
| **Total financé** | **{$finance} TND** |
MD;
    }

    private function formatPendingRequests(array $data): string
    {
        $demandes = $data['demandes_en_attente'] ?? [];
        $total    = $data['total'] ?? 0;

        if ($total === 0) {
            return "✅ Aucune demande en attente de décision pour le moment.";
        }

        $lines = ["Il y a **{$total} demande(s)** en attente de votre décision :\n"];

        foreach ($demandes as $i => $d) {
            $num     = $i + 1;
            $montant = number_format($d['montant'] ?? 0, 0, ',', ' ');
            $projet  = $d['projet_nom'] ?? "Projet #{$d['projet_id']}";
            $citoyen = $d['citoyen_nom'] ?? "Citoyen #{$d['citoyen_id']}";
            $date    = isset($d['created_at']) ? substr($d['created_at'], 0, 10) : '—';

            $lines[] = "**{$num}. {$projet}**";
            $lines[] = "- Citoyen : {$citoyen}";
            $lines[] = "- Montant demandé : **{$montant} TND**";
            $lines[] = "- Soumise le : {$date}";
            $lines[] = "";
        }

        $lines[] = "> Rendez-vous dans **Demandes de financement** pour accepter ou refuser.";

        return implode("\n", $lines);
    }

    private function formatBudgetReport(array $data): string
    {
        $totalBudget  = number_format($data['total_budget'] ?? 0, 0, ',', ' ');
        $totalFinance = number_format($data['total_finance'] ?? 0, 0, ',', ' ');
        $restant      = number_format($data['budget_restant'] ?? 0, 0, ',', ' ');
        $pct          = number_format($data['pourcentage_utilise'] ?? 0, 1);
        $projets      = $data['projets'] ?? [];

        $rows = '';
        foreach ($projets as $p) {
            $budget  = number_format($p['budget'] ?? 0, 0, ',', ' ');
            $finance = number_format($p['finance'] ?? 0, 0, ',', ' ');
            $rest    = number_format($p['restant'] ?? 0, 0, ',', ' ');
            $pctP    = number_format($p['pourcentage'] ?? 0, 1);
            $statut  = $this->labelStatut($p['statut'] ?? '');
            $rows   .= "| {$p['nom']} | {$statut} | {$budget} | {$finance} | {$rest} | {$pctP} % |\n";
        }

        return <<<MD
## 📈 Rapport budgétaire des projets

| Indicateur global | Valeur |
|:---|:---:|
| Budget total | **{$totalBudget} TND** |
| Total financé | **{$totalFinance} TND** |
| Budget restant | **{$restant} TND** |
| Taux d'utilisation | **{$pct} %** |

### Détail par projet

| Projet | Statut | Budget | Financé | Restant | % |
|:---|:---:|:---:|:---:|:---:|:---:|
{$rows}
MD;
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    private function getExecutor(string $toolName): ?object
    {
        return match ($toolName) {
            'search_projects'                   => $this->searchProjects,
            'get_my_funding_requests'           => $this->getMyFundingRequests,
            'get_my_payments'                   => $this->getMyPayments,
            'get_my_funding_portfolio'          => $this->getMyFundingPortfolio,
            'get_my_project_contributions'      => $this->getMyProjectContributions,
            'get_funding_rules'                 => $this->getFundingRules,
            'get_funding_dashboard_summary'     => $this->getFundingDashboardSummary,
            'get_pending_funding_requests'      => $this->getPendingFundingRequests,
            'get_manager_project_budget_report' => $this->getManagerProjectBudgetReport,
            default => null,
        };
    }

    private function labelStatut(string $statut): string
    {
        return match ($statut) {
            'en_cours'  => 'En cours',
            'planifie'  => 'Planifié',
            'termine'   => 'Terminé',
            'suspendu'  => 'Suspendu',
            'annule'    => 'Annulé',
            default     => ucfirst($statut),
        };
    }
}
