<?php

namespace Tests\Unit;

use App\Services\Chatbot\QuestionClassifier;
use Tests\TestCase;

/**
 * Tests du classifier de questions du chatbot.
 *
 * Logique pure (aucune base, aucun appel LLM) : on vérifie que chaque
 * formulation est routée vers la bonne stratégie (TOOL_DIRECT / RAG_DIRECT / LLM)
 * et qu'un outil manager n'est jamais proposé à un citoyen.
 */
class QuestionClassifierTest extends TestCase
{
    private QuestionClassifier $classifier;

    protected function setUp(): void
    {
        parent::setUp();
        $this->classifier = new QuestionClassifier();
    }

    // ── Niveau 1 : TOOL_DIRECT ────────────────────────────────────────────

    public function test_questions_donnees_citoyen_sont_tool_direct(): void
    {
        $cas = [
            'Quels projets puis-je financer ?'      => 'search_projects',
            'Projets disponibles pour financement'  => 'search_projects',
            'Consulte mes contributions'            => 'get_my_project_contributions',
            'Quel est le montant minimum ?'         => 'get_funding_rules',
            'Statut de ma demande'                  => 'get_my_funding_requests',
            'Mes paiements simulés'                 => 'get_my_payments',
            'Mon portefeuille'                      => 'get_my_funding_portfolio',
        ];

        foreach ($cas as $message => $outilAttendu) {
            $result = $this->classifier->classify($message, 'citoyen');

            $this->assertSame(
                QuestionClassifier::TOOL_DIRECT,
                $result['strategy'],
                "« {$message} » aurait dû être routé TOOL_DIRECT."
            );
            $this->assertSame(
                $outilAttendu,
                $result['tool'],
                "« {$message} » aurait dû viser l'outil {$outilAttendu}."
            );
        }
    }

    public function test_statut_filtre_les_projets_par_statut(): void
    {
        $result = $this->classifier->classify('Projets en cours', 'citoyen');

        $this->assertSame(QuestionClassifier::TOOL_DIRECT, $result['strategy']);
        $this->assertSame('search_projects', $result['tool']);
        $this->assertSame('en_cours', $result['tool_args']['statut']);
    }

    public function test_outils_manager_sont_classe_pour_manager(): void
    {
        $cas = [
            'Tableau de bord des financements'   => 'get_funding_dashboard_summary',
            'Demandes en attente'                => 'get_pending_funding_requests',
            'Rapport budgétaire'                 => 'get_manager_project_budget_report',
        ];

        foreach ($cas as $message => $outilAttendu) {
            $result = $this->classifier->classify($message, 'manager');

            $this->assertSame(QuestionClassifier::TOOL_DIRECT, $result['strategy']);
            $this->assertSame($outilAttendu, $result['tool']);
        }
    }

    public function test_outils_manager_ne_sont_pas_proposes_au_citoyen(): void
    {
        // Même formulation, mais le rôle citoyen ne doit jamais mapper un outil manager.
        $messagesManager = [
            'Tableau de bord des financements',
            'Demandes en attente',
            'Rapport budgétaire',
        ];

        foreach ($messagesManager as $message) {
            $result = $this->classifier->classify($message, 'citoyen');

            $this->assertNotSame(
                QuestionClassifier::TOOL_DIRECT,
                $result['strategy'],
                "Un citoyen ne doit pas déclencher un outil manager pour « {$message} »."
            );
            $this->assertStringNotContainsString('manager', $result['tool'] ?? '');
            $this->assertStringNotContainsString('dashboard', $result['tool'] ?? '');
        }
    }

    // ── Niveau 2 : RAG_DIRECT ─────────────────────────────────────────────

    public function test_questions_procedure_sont_rag_direct(): void
    {
        $cas = [
            'Comment financer un projet ?',
            'Comment fonctionne le paiement simulé ?',
            'Comment créer une demande de financement ?',
            'Quelles sont les règles de financement ?',
            'Qu\'est-ce qu\'un paiement simulé ?',
            'Comment obtenir un reçu ?',
        ];

        foreach ($cas as $message) {
            $result = $this->classifier->classify($message, 'citoyen');

            $this->assertSame(
                QuestionClassifier::RAG_DIRECT,
                $result['strategy'],
                "« {$message} » aurait dû être routé RAG_DIRECT."
            );
        }
    }

    // ── Niveau 3 : LLM ────────────────────────────────────────────────────

    public function test_questions_complexes_versent_en_llm(): void
    {
        $cas = [
            'Pourquoi le ciel est-il bleu ?',                  // hors périmètre
            'Peux-tu m\'expliquer le sens de la vie ?',        // question libre
            'Bonsoir',                                          // salutation ouverte
            'Raconte-moi une histoire de projets imaginaires',   // question libre
        ];

        foreach ($cas as $message) {
            $result = $this->classifier->classify($message, 'citoyen');

            $this->assertSame(
                QuestionClassifier::LLM,
                $result['strategy'],
                "« {$message} » aurait dû tomber en LLM."
            );
        }
    }

    // ── Casse / normalisation ─────────────────────────────────────────────

    public function test_classification_insensible_a_la_casse_et_aux_espaces(): void
    {
        $result = $this->classifier->classify('  QUELS PROJETS PUIS-JE FINANCER ?  ', 'citoyen');

        $this->assertSame(QuestionClassifier::TOOL_DIRECT, $result['strategy']);
        $this->assertSame('search_projects', $result['tool']);
    }

    public function test_confidence_est_un_float_compris_entre_zero_et_un(): void
    {
        $messages = [
            'Quels projets puis-je financer ?',
            'Comment financer un projet ?',
            'Question totalement libre sans mot-clé.',
        ];

        foreach ($messages as $message) {
            $result = $this->classifier->classify($message, 'citoyen');
            $this->assertIsFloat($result['confidence']);
            $this->assertGreaterThanOrEqual(0.0, $result['confidence']);
            $this->assertLessThanOrEqual(1.0, $result['confidence']);
        }
    }
}
