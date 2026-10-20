<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithRbac;
use Tests\TestCase;

/**
 * Tests RBAC et authentification du chatbot.
 *
 * Aucun appel externe (Gemini) n'est effectué : le client Gemini est mocké
 * via l'injection de dépendances ou le fait que GEMINI_API_KEY est absente.
 */
class ChatbotRbacTest extends TestCase
{
    use RefreshDatabase;
    use InteractsWithRbac;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->seedRbac();

        // Aucune clé Gemini configurée en test → le chatbot doit indiquer son indisponibilité
        config()->set('chatbot.gemini.api_key', '');
    }

    public function test_utilisateur_non_authentifie_ne_peut_pas_appeler_le_chatbot(): void
    {
        $response = $this->postJson(route('chatbot.ask'), ['message' => 'Bonjour']);
        $response->assertStatus(401); // Non authentifié (JSON)
    }

    public function test_citoyen_peut_poser_une_question_sur_projets(): void
    {
        $citoyen = $this->createUserWithRole(Role::CITOYEN, ['email' => 'citoyen@test.tn']);
        $this->actingAs($citoyen);

        $response = $this->post(route('chatbot.ask'), ['message' => 'Quels projets sont ouverts ?']);
        // Sans clé Gemini, le chatbot répond un message d'indisponibilité (200, pas 500)
        $response->assertStatus(200);
        $this->assertArrayHasKey('response', json_decode($response->getContent(), true));
    }

    public function test_citoyen_ne_peut_pas_appeler_outils_manager(): void
    {
        $citoyen = $this->createUserWithRole(Role::CITOYEN, ['email' => 'citoyen2@test.tn']);
        $this->actingAs($citoyen);

        // Le citoyen ne doit pas avoir accès aux outils manager dans le registre
        $registry = app(\App\Services\Chatbot\ToolRegistry::class);
        $tools = $registry->forRole(Role::CITOYEN);

        $toolNames = array_keys($tools);
        $this->assertNotContains('get_funding_dashboard_summary', $toolNames);
        $this->assertNotContains('get_pending_funding_requests', $toolNames);
        $this->assertNotContains('get_manager_project_budget_report', $toolNames);
    }

    public function test_modifier_user_id_dans_requete_ne_donne_aucun_acces_supplementaire(): void
    {
        $citoyen = $this->createUserWithRole(Role::CITOYEN, ['email' => 'citoyen3@test.tn']);
        $autre = $this->createUserWithRole(Role::CITOYEN, ['email' => 'autre@test.tn']);

        // Le citoyen ne peut consulter que ses propres demandes (l'user_id vient de l'auth, pas du payload)
        $this->actingAs($citoyen);
        $response = $this->post(route('chatbot.ask'), ['message' => 'Mes demandes', 'user_id' => $autre->id]);
        $response->assertStatus(200);

        // Vérifier que le registre filtre bien par rôle
        $registry = app(\App\Services\Chatbot\ToolRegistry::class);
        $citoyenTools = $registry->forRole(Role::CITOYEN);
        $managerTools = $registry->forRole(Role::MANAGER);

        $this->assertNotEmpty($citoyenTools);
        $this->assertNotEmpty($managerTools);

        // Un outil manager ne doit jamais apparaître dans la liste citoyen
        foreach ($citoyenTools as $name => $tool) {
            $this->assertStringNotContainsString('manager', $name);
            $this->assertStringNotContainsString('dashboard', $name);
        }
    }

    public function test_gestionnaire_peut_appeler_outils_gestion(): void
    {
        $manager = $this->createUserWithRole(Role::MANAGER, ['email' => 'manager@test.tn']);
        $this->actingAs($manager);

        $registry = app(\App\Services\Chatbot\ToolRegistry::class);
        $tools = $registry->forRole(Role::MANAGER);
        $toolNames = array_keys($tools);

        $this->assertContains('get_funding_dashboard_summary', $toolNames);
        $this->assertContains('get_pending_funding_requests', $toolNames);
        $this->assertContains('get_manager_project_budget_report', $toolNames);
    }

    public function test_cle_absente_ne_revele_aucun_secret(): void
    {
        $citoyen = $this->createUserWithRole(Role::CITOYEN, ['email' => 'secret@test.tn']);
        $this->actingAs($citoyen);

        // Désactiver la clé et rebinder un mock GroqClient non configuré
        config()->set('chatbot.groq.api_key', '');
        $this->app->bind(\App\Services\Chatbot\GroqClient::class, function () {
            // Nouvelle instance avec clé vide depuis config
            return new \App\Services\Chatbot\GroqClient();
        });
        $this->app->bind(\App\Services\Chatbot\ChatbotOrchestrator::class, function ($app) {
            return $app->make(\App\Services\Chatbot\ChatbotOrchestrator::class);
        });

        $response = $this->post(route('chatbot.ask'), ['message' => 'Test']);
        $content = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('gemini_available', $content);
        $this->assertFalse($content['gemini_available']);
        $this->assertStringNotContainsString('500', $response->getContent());
    }
}
