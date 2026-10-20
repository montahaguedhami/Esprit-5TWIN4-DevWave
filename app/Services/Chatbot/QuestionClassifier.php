<?php

namespace App\Services\Chatbot;

/**
 * Classifier de questions du chatbot — 0 token LLM consommé.
 *
 * Stratégies :
 *   TOOL_DIRECT  → Outil identifié, exécution PHP directe (0 token LLM)
 *   RAG_DIRECT   → Documentation MySQL full-text (0 token LLM)
 *   LLM          → Question complexe/contextuelle → appel Groq
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
        // NIVEAU 1 — TOOL_DIRECT (citoyen + manager)
        // ══════════════════════════════════════════════════════════════════

        // get_my_funding_requests
        if ($this->matches($lower, [
            'mes demandes', 'mes demande de financement',
            'statut de ma demande', 'statut demande', 'mes requêtes',
            'voir mes demandes', 'liste mes demandes',
        ])) {
            return $this->toolResult('get_my_funding_requests', [], 0.95);
        }

        // get_my_payments
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

        // get_funding_rules
        if ($this->matches($lower, [
            'montant minimum', 'montant minimal', 'montant minimum de contribution',
            'règles du financement', 'règles financement',
            'conditions d\'éligibilité', 'conditions éligibilité',
            'statuts possibles', 'workflow financement',
        ])) {
            return $this->toolResult('get_funding_rules', [], 0.90);
        }

        // search_projects — filtres statut
        if ($this->matches($lower, [
            'projets en cours', 'en cours de réalisation',
            'montre-moi les projets en cours',
        ])) {
            return $this->toolResult('search_projects', ['statut' => 'en_cours', 'limit' => 20], 0.92);
        }

        if ($this->matches($lower, [
            'projets planifiés', 'projets en planification',
            'projets à venir', 'projets planifié',
        ])) {
            return $this->toolResult('search_projects', ['statut' => 'planifie', 'limit' => 20], 0.92);
        }

        if ($this->matches($lower, [
            'projets terminés', 'projets finis',
            'projets complétés', 'projets achevés',
        ])) {
            return $this->toolResult('search_projects', ['statut' => 'termine', 'limit' => 20], 0.90);
        }

        // search_projects — éligibles au financement
        if ($this->matches($lower, [
            'puis-je financer', 'quels projets financer', 'projets à financer',
            'projets disponibles pour financement', 'projets éligibles au financement',
            'projets ouverts au financement', 'projets éligibles',
            'quels projets sont éligibles', 'projets qui peuvent être financés',
        ])) {
            return $this->toolResult('search_projects', ['eligible_only' => true, 'limit' => 20], 0.92);
        }

        // search_projects — liste générale
        if ($this->matches($lower, [
            'tous les projets', 'liste des projets', 'liste projets',
            'affiche les projets', 'montre les projets', 'voir tous les projets',
            'les projets', 'voir les projets', 'afficher les projets',
            'projets disponibles', 'projets existants', 'projets du système',
            'donne moi liste des projets', 'donne-moi la liste des projets',
        ])) {
            return $this->toolResult('search_projects', ['limit' => 20], 0.88);
        }

        // Mot seul "projets"
        if (in_array(trim($lower), ['projets', 'projet'])) {
            return $this->toolResult('search_projects', ['limit' => 20], 0.85);
        }

        // ── MANAGER uniquement ─────────────────────────────────────────────
        if ($role === 'manager') {

            // get_funding_dashboard_summary
            if ($this->matches($lower, [
                'tableau de bord', 'résumé financement', 'statistiques globales',
                'total demandes', 'vue d\'ensemble', 'dashboard', 'bilan global',
                'nombre de demandes', 'combien de demandes',
                'résume la situation', 'situation des financements',
                'résumez la situation', 'résumez les financements',
                'résumé global', 'situation globale', 'bilan financements',
                'résumez la situation globale', 'résumer la situation',
                'résumez la situation globale des financements',
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

            // get_manager_project_budget_report
            if ($this->matches($lower, [
                'rapport budgétaire', 'rapport budget',
                'budget par projet', 'taux de financement par projet',
                'rapport financier', 'synthèse budgétaire',
                'consulte les budgets', 'budgets des projets',
                'nécessitent encore des financements', 'projets nécessitent',
                'financements urgents', 'projets urgents',
            ])) {
                return $this->toolResult('get_manager_project_budget_report', [], 0.93);
            }
        }

        // ══════════════════════════════════════════════════════════════════
        // NIVEAU 2 — RAG_DIRECT
        // ══════════════════════════════════════════════════════════════════
        if ($this->matches($lower, [
            // Comment faire
            'comment financer', 'comment faire une demande', 'comment contribuer',
            'comment soumettre', 'comment créer une demande', 'comment un projet',
            'procédure financement', 'étapes financement', 'démarche financement',
            // Comment ça fonctionne
            'comment fonctionne', 'comment ça marche',
            // Guides
            'guide', 'tutoriel', 'aide', 'documentation',
            // Paiements simulés
            'paiement simulé', 'paiements simulés', 'c\'est quoi le paiement',
            'qu\'est-ce qu\'un paiement simulé', 'simulation paiement',
            'comment fonctionne le paiement', 'comment fonctionne la simulation',
            // Reçus
            'comment obtenir un reçu', 'comment imprimer', 'comment télécharger',
            // Autres procédures
            'annuler une demande', 'modifier une demande',
            // Présentation
            'qu\'est-ce que aquasecure', 'présentation', 'à quoi sert',
            // Règles générales
            'quelles sont les règles', 'règles de décision', 'explique les règles',
            'conditions de financement', 'procédures manager',
            // Montant (formulation longue)
            'quel est le montant',
        ])) {
            return ['strategy' => self::RAG_DIRECT, 'confidence' => 0.88];
        }

        // ══════════════════════════════════════════════════════════════════
        // NIVEAU 3 — LLM
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
