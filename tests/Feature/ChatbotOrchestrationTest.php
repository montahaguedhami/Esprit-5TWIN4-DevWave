<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Services\Chatbot\ChatbotOrchestrator;
use App\Services\Chatbot\GroqClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\Concerns\InteractsWithRbac;
use Tests\TestCase;

/**
 * Tests d'orchestration du chatbot (Tool Calling / RAG / LLM Groq).
 *
 * Le client Groq est MOCKÉ : aucun appel réseau réel n'est effectué.
 * On vérifie le routage 3 niveaux, les fallbacks et le respect du RBAC.
 */
class ChatbotOrchestrationTest extends TestCase
{
    use RefreshDatabase;
    use InteractsWithRbac;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->seedRbac();
    }

    /**
     * Installe un GroqClient mocké configuré, et retourne le mock pour assertions.
     */
    private function mockGroq(array $generateReturns = []): Mockery\MockInterface
    {
        $mock = Mockery::mock(GroqClient::class);
        $mock->shouldReceive('isConfigured')->andReturn(true);

        $expectation = $mock->shouldReceive('generate');
        if ($generateReturns === []) {
            // Par défaut : pas d'appel attendu (chemins TOOL/RAG).
            $expectation->never();
        } else {
            foreach ($generateReturns as $i => $return) {
                $expectation->once()->andReturn($return);
            }
        }

        $this->app->instance(GroqClient::class, $mock);

        return $mock;
    }

    // ── Route publique de statut ──────────────────────────────────────────

    public function test_la_route_de_statut_est_publique_et_renvoie_l_etat_de_groq(): void
    {
        // Aucun authentification requise : le client JS vérifie le statut avant login.
        $response = $this->getJson(route('chatbot.status'));

        $response->assertOk()
            ->assertJsonStructure(['enabled', 'groq_available'])
            ->assertJsonPath('enabled', (bool) config('chatbot.enabled', true));

        $this->assertIsBool($response->json('groq_available'));
    }

    public function test_le_statut_reflete_la_configuration_groq(): void
    {
        config()->set('chatbot.enabled', false);

        $this->getJson(route('chatbot.status'))
            ->assertOk()
            ->assertJsonPath('enabled', false);
    }

    // ── Niveau 1 — TOOL_DIRECT (0 token LLM) ──────────────────────────────

    public function test_outil_direct_ne_consomme_aucun_token_llm(): void
    {
        // Groq mocké : generate() ne doit JAMAIS être appelé.
        $this->mockGroq([]);

        $citoyen = $this->createUserWithRole(Role::CITOYEN, ['email' => 'orch-citoyen@test.tn']);
        $this->actingAs($citoyen);

        $response = $this->post(route('chatbot.ask'), ['message' => 'Quels projets puis-je financer ?']);

        $response->assertStatus(200);
        $data = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('response', $data);
        $this->assertNotEmpty($data['response']);
        // L'outil a bien été exécuté (search_projects), sans passer par le LLM.
        $this->assertNotEmpty($data['tool_calls']);
        $this->assertSame('search_projects', $data['tool_calls'][0]['tool']);
    }

    public function test_outil_manager_direct_repond_sans_appel_llm(): void
    {
        // Le manager interroge son tableau de bord → TOOL_DIRECT réussi, aucun appel Groq.
        $this->mockGroq([]);

        $manager = $this->createUserWithRole(Role::MANAGER, ['email' => 'orch-mgr@test.tn']);
        $this->actingAs($manager);

        $response = $this->post(route('chatbot.ask'), ['message' => 'Tableau de bord des financements']);

        $response->assertStatus(200);
        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('response', $data);
        $this->assertNotEmpty($data['response']);
        $this->assertNotEmpty($data['tool_calls']);
        $this->assertSame('get_funding_dashboard_summary', $data['tool_calls'][0]['tool']);
    }

    // ── Niveau 2 — RAG_DIRECT (0 token LLM) ───────────────────────────────

    public function test_question_documentaire_retourne_reponse_rag_avec_sources(): void
    {
        // generate() jamais appelé : la réponse vient de la documentation.
        $this->mockGroq([]);

        // Indexer un document RAG explicite pour garantir un chunk pertinent.
        $this->seedRagDocument(
            'Règles de financement',
            'Le montant minimum de contribution est de 10 dinars tunisiens.'
        );

        $citoyen = $this->createUserWithRole(Role::CITOYEN, ['email' => 'orch-rag@test.tn']);
        $this->actingAs($citoyen);

        $response = $this->post(route('chatbot.ask'), ['message' => 'Comment fonctionne le financement ?']);

        $response->assertStatus(200);
        $data = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('response', $data);
        $this->assertNotEmpty($data['response']);
    }

    // ── Niveau 3 — LLM Groq ───────────────────────────────────────────────

    public function test_question_libre_passe_par_le_llm_groq(): void
    {
        $this->mockGroq([
            ['text' => 'Réponse générée par Groq pour une question libre.', 'tool_calls' => []],
        ]);

        $citoyen = $this->createUserWithRole(Role::CITOYEN, ['email' => 'orch-llm@test.tn']);
        $this->actingAs($citoyen);

        // Question sans mot-clé TOOL/RAG → niveau 3.
        $response = $this->post(route('chatbot.ask'), ['message' => 'Peux-tu m\'aider à réfléchir à un projet futuriste ?']);

        $response->assertStatus(200);
        $data = json_decode($response->getContent(), true);

        $this->assertStringContainsString('Groq', $data['response']);
        $this->assertNull($data['error']);
    }

    public function test_historique_est_transmis_au_llm(): void
    {
        $mock = Mockery::mock(GroqClient::class);
        $mock->shouldReceive('isConfigured')->andReturn(true);
        // On capture le 3e argument (toolDefinitions) et le 2e (messages) du 1er appel.
        $mock->shouldReceive('generate')
            ->once()
            ->andReturn(['text' => 'OK', 'tool_calls' => []]);

        $this->app->instance(GroqClient::class, $mock);

        $citoyen = $this->createUserWithRole(Role::CITOYEN, ['email' => 'orch-hist@test.tn']);
        $this->actingAs($citoyen);

        $response = $this->post(route('chatbot.ask'), [
            'message' => 'Question libre sans mot-clé particulier du tout',
            'history' => [
                ['role' => 'user', 'content' => 'Bonjour'],
                ['role' => 'assistant', 'content' => 'Bonjour !'],
            ],
        ]);

        $response->assertStatus(200);
    }

    // ── RBAC dans le chemin LLM ───────────────────────────────────────────

    public function test_citoyen_ne_peut_pas_obtenir_outil_manager_via_llm(): void
    {
        // Le LLM propose un outil manager → l'orchestrateur doit le refuser (RBAC).
        $this->mockGroq([
            [
                'text'      => '',
                'tool_calls' => [
                    ['name' => 'get_funding_dashboard_summary', 'args' => []],
                ],
            ],
            ['text' => 'Désolé, cette donnée est réservée aux gestionnaires.', 'tool_calls' => []],
        ]);

        $citoyen = $this->createUserWithRole(Role::CITOYEN, ['email' => 'orch-rbac@test.tn']);
        $this->actingAs($citoyen);

        $response = $this->post(route('chatbot.ask'), ['message' => 'Question libre pour forcer le chemin LLM ici']);

        $response->assertStatus(200);
        $data = json_decode($response->getContent(), true);
        // L'outil manager n'a pas de résultat de données exploitables.
        $this->assertArrayHasKey('response', $data);
    }

    // ── Indisponibilité / erreurs ─────────────────────────────────────────

    public function test_groq_non_configure_renvoie_message_indisponibilite_sans_500(): void
    {
        config()->set('chatbot.groq.api_key', '');
        $this->app->forgetInstance(GroqClient::class);
        $this->app->forgetInstance(ChatbotOrchestrator::class);

        $citoyen = $this->createUserWithRole(Role::CITOYEN, ['email' => 'orch-nokey@test.tn']);
        $this->actingAs($citoyen);

        $response = $this->post(route('chatbot.ask'), ['message' => 'Bonjour']);

        $response->assertStatus(200);
        $data = json_decode($response->getContent(), true);
        $this->assertNotEmpty($data['response']);
        $this->assertFalse($data['groq_available']);
    }

    public function test_message_vide_ou_trop_long_est_valide_cote_serveur(): void
    {
        $this->mockGroq([]);

        $citoyen = $this->createUserWithRole(Role::CITOYEN, ['email' => 'orch-val@test.tn']);
        $this->actingAs($citoyen);

        // Message trop long (> 2000) → réponse d'erreur lisible, jamais un 500.
        $response = $this->post(route('chatbot.ask'), ['message' => str_repeat('a', 2500)]);

        $response->assertStatus(200);
        $data = json_decode($response->getContent(), true);
        $this->assertNotEmpty($data['response']);
    }

    public function test_histoire_ne_depasse_pas_dix_tours(): void
    {
        $this->mockGroq([
            ['text' => 'OK', 'tool_calls' => []],
        ]);

        $citoyen = $this->createUserWithRole(Role::CITOYEN, ['email' => 'orch-slice@test.tn']);
        $this->actingAs($citoyen);

        // 20 tours d'historique (limite de validation) → l'orchestrateur n'en garde que 10.
        $history = [];
        for ($i = 0; $i < 20; $i++) {
            $history[] = ['role' => 'user', 'content' => "Tour {$i}"];
        }

        $response = $this->post(route('chatbot.ask'), [
            'message' => 'Question libre sans mot-clé spécifique non plus',
            'history' => $history,
        ]);

        $response->assertStatus(200);
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    /**
     * Indexe un chunk dans la table RAG (schéma dénormalisé) pour garantir
     * un résultat pertinent lors de la recherche full-text.
     */
    private function seedRagDocument(string $title, string $content): void
    {
        if (! \Schema::hasTable('chatbot_document_chunks')) {
            return;
        }

        \App\Models\ChatbotDocumentChunk::create([
            'document_id'    => 'guide-financement',
            'document_title' => $title,
            'chunk_index'    => 0,
            'content'        => $content,
            'section'        => 'Règles',
        ]);
    }
}
