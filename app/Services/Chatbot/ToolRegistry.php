<?php

namespace App\Services\Chatbot;

use App\Models\Role;

/**
 * Registre des outils métier autorisés pour le chatbot.
 *
 * Chaque outil est défini par :
 *   - un nom fixe (utilisé pour la validation, jamais pris tel quel depuis le LLM)
 *   - une description lisible par le modèle
 *   - un schéma de paramètres JSON (conforme Function Calling Gemini)
 *   - les rôles autorisés à exécuter l'outil
 *
 * Tous les outils de cette version sont en lecture seule.
 * Aucun outil d'écriture (création, modification, suppression) n'est enregistré.
 */
class ToolRegistry
{
    /**
     * Rôle citoyen (utilisé pour les matrices d'accès).
     */
    public const ROLE_CITOYEN = Role::CITOYEN;

    /**
     * Rôle manager (utilisé pour les matrices d'accès).
     */
    public const ROLE_MANAGER = Role::MANAGER;

    /**
     * @return array<string, array{name: string, description: string, parameters: array<string, mixed>, roles: list<string>}>
     */
    public function all(): array
    {
        return [
            'search_projects' => $this->searchProjects(),
            'get_project_details' => $this->getProjectDetails(),
            'get_project_funding_status' => $this->getProjectFundingStatus(),
            'get_funding_rules' => $this->getFundingRules(),
            'search_documentation' => $this->searchDocumentation(),
            'get_project_recommendations' => $this->getProjectRecommendations(),
            'get_my_funding_requests' => $this->getMyFundingRequests(),
            'get_my_payments' => $this->getMyPayments(),
            'get_my_funding_portfolio' => $this->getMyFundingPortfolio(),
            'get_my_project_contributions' => $this->getMyProjectContributions(),
            'get_funding_dashboard_summary' => $this->getFundingDashboardSummary(),
            'get_pending_funding_requests' => $this->getPendingFundingRequests(),
            'get_manager_project_budget_report' => $this->getManagerProjectBudgetReport(),
        ];
    }

    /**
     * Retourne les outils accessibles à un rôle donné.
     */
    public function forRole(string $role): array
    {
        $all = $this->all();

        return array_filter($all, fn (array $tool): bool => in_array($role, $tool['roles'], true));
    }

    /**
     * Vérifie qu'un nom d'outil existe dans le registre.
     */
    public function exists(string $name): bool
    {
        return isset($this->all()[$name]);
    }

    /**
     * Retourne la définition d'un outil, ou null s'il n'existe pas.
     */
    public function get(string $name): ?array
    {
        return $this->all()[$name] ?? null;
    }

    /* ── Définitions des outils ─────────────────────────────────────────── */

    private function searchProjects(): array
    {
        return [
            'name' => 'search_projects',
            'description' => 'Recherche les projets ouverts au financement, avec filtres optionnels (texte, statut, éligibilité).',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'query' => ['type' => 'string', 'description' => 'Texte de recherche sur le nom du projet.'],
                    'statut' => ['type' => 'string', 'description' => 'Filtre par statut du projet (ex: en_cours, planifie).'],
                    'eligible_only' => ['type' => 'boolean', 'description' => 'Si vrai, ne retourne que les projets éligibles au financement citoyen.'],
                    'limit' => ['type' => 'integer', 'description' => 'Nombre maximal de résultats (max 20).'],
                ],
            ],
            'roles' => [self::ROLE_CITOYEN, self::ROLE_MANAGER],
        ];
    }

    private function getProjectDetails(): array
    {
        return [
            'name' => 'get_project_details',
            'description' => 'Retourne les informations autorisées d\'un projet précis (nom, description, budget, statut, progression, zones).',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'projet_id' => ['type' => 'integer', 'description' => 'Identifiant du projet.'],
                ],
                'required' => ['projet_id'],
            ],
            'roles' => [self::ROLE_CITOYEN, self::ROLE_MANAGER],
        ];
    }

    private function getProjectFundingStatus(): array
    {
        return [
            'name' => 'get_project_funding_status',
            'description' => 'Retourne les informations budgétaires autorisées d\'un projet : budget, contributions cumulées, réservations, solde disponible, état du financement.',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'projet_id' => ['type' => 'integer', 'description' => 'Identifiant du projet.'],
                ],
                'required' => ['projet_id'],
            ],
            'roles' => [self::ROLE_CITOYEN, self::ROLE_MANAGER],
        ];
    }

    private function getFundingRules(): array
    {
        return [
            'name' => 'get_funding_rules',
            'description' => 'Retourne les règles métier vérifiées : montant minimum, conditions d\'éligibilité, statuts, fonctionnement des demandes et du paiement simulé.',
            'parameters' => [
                'type' => 'object',
                'properties' => [],
            ],
            'roles' => [self::ROLE_CITOYEN, self::ROLE_MANAGER],
        ];
    }

    private function searchDocumentation(): array
    {
        return [
            'name' => 'search_documentation',
            'description' => 'Recherche les passages documentaires pertinents sur les procédures et règles du module Projets et Financements.',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'query' => ['type' => 'string', 'description' => 'Texte de recherche documentaire.'],
                    'limit' => ['type' => 'integer', 'description' => 'Nombre maximal de passages (max 10).'],
                ],
            ],
            'roles' => [self::ROLE_CITOYEN, self::ROLE_MANAGER],
        ];
    }

    private function getProjectRecommendations(): array
    {
        return [
            'name' => 'get_project_recommendations',
            'description' => 'Retourne les recommandations de projets personnalisées pour le citoyen connecté (moteur V1 à règles).',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'limit' => ['type' => 'integer', 'description' => 'Nombre maximal de recommandations (max 20).'],
                ],
            ],
            'roles' => [self::ROLE_CITOYEN],
        ];
    }

    private function getMyFundingRequests(): array
    {
        return [
            'name' => 'get_my_funding_requests',
            'description' => 'Retourne uniquement les demandes de financement de l\'utilisateur connecté (projet, montant, statut, date).',
            'parameters' => [
                'type' => 'object',
                'properties' => [],
            ],
            'roles' => [self::ROLE_CITOYEN],
        ];
    }

    private function getMyPayments(): array
    {
        return [
            'name' => 'get_my_payments',
            'description' => 'Retourne uniquement les paiements simulés de l\'utilisateur connecté (montant, statut, date, référence).',
            'parameters' => [
                'type' => 'object',
                'properties' => [],
            ],
            'roles' => [self::ROLE_CITOYEN],
        ];
    }

    private function getMyFundingPortfolio(): array
    {
        return [
            'name' => 'get_my_funding_portfolio',
            'description' => 'Retourne le portefeuille personnel du citoyen connecté : total des contributions, nombre de projets concernés.',
            'parameters' => [
                'type' => 'object',
                'properties' => [],
            ],
            'roles' => [self::ROLE_CITOYEN],
        ];
    }

    private function getMyProjectContributions(): array
    {
        return [
            'name' => 'get_my_project_contributions',
            'description' => 'Retourne les contributions du citoyen connecté avec les projets associés.',
            'parameters' => [
                'type' => 'object',
                'properties' => [],
            ],
            'roles' => [self::ROLE_CITOYEN],
        ];
    }

    private function getFundingDashboardSummary(): array
    {
        return [
            'name' => 'get_funding_dashboard_summary',
            'description' => 'Retourne les statistiques globales de financement (nombre de demandes, montants globaux) réservées au manager.',
            'parameters' => [
                'type' => 'object',
                'properties' => [],
            ],
            'roles' => [self::ROLE_MANAGER],
        ];
    }

    private function getPendingFundingRequests(): array
    {
        return [
            'name' => 'get_pending_funding_requests',
            'description' => 'Retourne les demandes de financement en attente de décision (réservé au manager).',
            'parameters' => [
                'type' => 'object',
                'properties' => [],
            ],
            'roles' => [self::ROLE_MANAGER],
        ];
    }

    private function getManagerProjectBudgetReport(): array
    {
        return [
            'name' => 'get_manager_project_budget_report',
            'description' => 'Retourne le rapport budgétaire des projets (budget, financé, restant par projet) réservé au manager.',
            'parameters' => [
                'type' => 'object',
                'properties' => [],
            ],
            'roles' => [self::ROLE_MANAGER],
        ];
    }
}
