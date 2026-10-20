<?php

namespace App\Services\Chatbot;

/**
 * Classifier de questions du chatbot — 0 token LLM consommé.
 *
 * Stratégies (du moins coûteux au plus coûteux) :
 *   TOOL_DIRECT  → Outil identifié sans ambiguïté, exécution PHP directe (0 token LLM)
 *   RAG_DIRECT   → Documentation : recherche full-text MySQL, réponse PHP (0 token LLM)
 *   LLM          → Question complexe ou contextuelle → appel Groq
 *
 * RÈGLE : Les patterns TOOL_DIRECT sont vérifiés avant RAG_DIRECT.
 * Un pattern RAG ne doit pas être un sous-ensemble d'un pattern TOOL.
 */
class QuestionClassifier
{
    public const TOOL_DIRECT = 'TOOL_DIRECT';
    public const RAG_DIRECT  = 'RAG_DIRECT';
    public const LLM         = 'LLM';

    public function classify(string $message, string $role): array
    {
        $lower = mb_strtolower(trim($message));

        // ══════════════════════════════════════════════════════════════════
        // NIVEAU 1 — TOOL_DIRECT
        // Patterns stricts : ne pas mettre de mots-clés ambigus avec RAG
        // ══════════════════════════════════════════════════════════════════

        // get_my_funding_requests
        if ($this->matches($lower, [
            'mes demandes', 'mes demande de financement',
            'statut de ma demande', 'statut demande', 'mes requêtes',
            'voir mes demandes', 'liste mes demandes',
        ])) {
            return $this->toolResult('get_my_funding_requests', [], 0.95);
        }

        // get_my_payments (uniquement données personnelles, pas l'explication)
        if ($this->matches($lower, [
            'mes paiements', 'mon paiement simulé', 'historique paiement',
            'mes transactions', 'voir mes paiements', 'liste mes paiements',
        ])) {
            return $this->toolResult('get_my_payments', [], 0.95);
        }

        // get_my_funding_portfolio
        if ($this->matches($lower, [
            'mon portefeuille', 'total contribué', 'combien ai-je contribué',
            'résumé contributions', 'bilan contributions',
        ])) {
            return $this->toolResult('get_my_funding_portfolio', [], 0.95);
        }

        // get_my_project_contributions
        if ($this->matches($lower, [
            'mes contributions', 'ma contribution', 'projets que j\'ai financé',
            'projets financés par moi', 'consulte mes contributions',
            'voir mes contributions', 'mes versements',
        ])) {
            return $this->toolResult('get_my_project_contributions', [], 0.95);
        }

        // get_funding_rules — STRICT : montant minimum, règles, conditions
        if ($this->matches($lower, [
            'montant minimum', 'montant minimal', 'montant minimum de contribution',
            'règles du financement', 'règles financement',
            'conditions d\'éligibilité', 'conditions éligibilité',
            'statuts possibles', 'workflow financement',
        ])) {
            return $this->toolResult('get_funding_rules', [], 0.90);
        }

        // search_projects avec filtres précis
        if ($this->matches($lower, ['projets en cours', 'en cours de réalisation', 'montre-moi les projets en cours'])) {
            return $this->toolResult('search_projects', ['statut' => 'en_cours', 'limit' => 20], 0.92);
        }

        if ($this->matches($lower, ['projets planifiés', 'projets en planification', 'projets à venir', 'projets planifié'])) {
            return $this->toolResult('search_projects', ['statut' => 'planifie', 'limit' => 20], 0.92);
        }

        if ($this->matches($lower, ['projets terminés', 'projets finis', 'projets complétés', 'projets achevés'])) {
            return $this->toolResult('search_projects', ['statut' => 'termine', 'limit' => 20], 0.90);
        }

        // search_projects — projets finançables/éligibles
        if ($this->matches($lower, [
            'puis-je financer', 'quels projets financer', 'projets à financer',
            'projets disponibles pour financement', 'projets éligibles au financement',
            'projets ouverts au financement',
        ])) {
            return $this->toolResult('search_projects', ['eligible_only' => true, 'limit' => 20], 0.92);
        }

        // search_projects — liste générale (pas de filtre complexe)
        if ($this->matches($lower, [
            'tous les projets', 'liste des projets', 'liste projets',
            'affiche les projets', 'montre les projets', 'voir tous les projets',
        ])) {
            return $this->toolResult('search_projects', ['limit' => 20], 0.88);
        }

        // ── Outils MANAGER uniquement ──────────────────────────────────────
        if ($role === 'manager') {

            // get_funding_dashboard_summary
            if ($this->matches($lower, [
                'tableau de bord', 'résumé financement', 'statistiques globales',
                'total demandes', 'vue d\'ensemble', 'dashboard', 'bilan global',
                'nombre de demandes', 'combien de demandes',
                'résume la situation', 'situation des financements',
            ])) {
                return $this->toolResult('get_funding_dashboard_summary', [], 0.93);
            }

            // get_pending_funding_requests
            if ($this->matches($lower, [
                'demandes en attente', 'demandes à traiter',
                'à approuver', 'à valider', 'en attente de décision',
                'demandes non traitées', 'demandes pending',
                'quelles demandes sont en attente', 'quelles demandes',
            ])) {
                return $this->toolResult('get_pending_funding_requests', [], 0.93);
            }

            // get_manager_project_budget_report — STRICT
            if ($this->matches($lower, [
                'rapport budgétaire', 'rapport budget',
                'budget par projet', 'taux de financement par projet',
                'rapport financier', 'synthèse budgétaire',
                'consulte les budgets', 'budgets des projets',
                'nécessitent encore des financements', 'projets nécessitent',
            ])) {
                return $this->toolResult('get_manager_project_budget_report', [], 0.93);
            }
        }

        // ══════════════════════════════════════════════════════════════════
        // NIVEAU 2 — RAG_DIRECT
        // Questions procédurales, explicatives, "comment", "qu'est-ce que"
        // ══════════════════════════════════════════════════════════════════
        if ($this->matches($lower, [
            // Comment faire / Comment financer un projet
            'comment financer', 'comment faire une demande', 'comment contribuer',
            'comment soumettre', 'comment créer une demande', 'comment un projet',
            'procédure financement', 'étapes financement', 'démarche financement',
            // Comment ça fonctionne
            'comment fonctionne', 'comment ça marche', 'comment ça fonctionne',
            // Guides
            'guide', 'tutoriel', 'aide', 'documentation',
            // Paiements simulés (explication)
            'paiement simulé', 'paiements simulés', 'c\'est quoi le paiement',
            'qu\'est-ce qu\'un paiement simulé', 'simulation paiement',
            'comment fonctionne le paiement', 'comment fonctionne la simulation',
            // Reçus
            'comment obtenir un reçu', 'comment imprimer', 'comment télécharger',
            // Autres actions
            'annuler une demande', 'modifier une demande',
            // Présentation générale
            'qu\'est-ce que aquasecure', 'présentation', 'à quoi sert',
            // Manager : règles et situation
            'quelles sont les règles', 'règles de décision', 'explique les règles',
            'conditions de financement', 'résume la situation', 'situation des financements',
            // Montant minimum de contribution (formulation longue)
            'montant minimum de contribution', 'quel est le montant',
        ])) {
            return ['strategy' => self::RAG_DIRECT, 'confidence' => 0.88];
        }

        // ══════════════════════════════════════════════════════════════════
        // NIVEAU 3 — LLM (par défaut)
        // ══════════════════════════════════════════════════════════════════
        return ['strategy' => self::LLM, 'confidence' => 1.0];
    }

    private function matches(string $lower, array $keywords): bool
    {
        foreach ($keywords as $kw) {
            if (mb_strpos($lower, $kw) !== false) {
                return true;
            }
        }
        return false;
    }

    private function toolResult(string $tool, array $args, float $confidence): array
    {
        return [
            'strategy'   => self::TOOL_DIRECT,
            'tool'       => $tool,
            'tool_args'  => $args,
            'confidence' => $confidence,
        ];
    }
}
