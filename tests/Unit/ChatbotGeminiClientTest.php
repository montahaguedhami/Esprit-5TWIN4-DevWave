<?php

namespace Tests\Unit;

use App\Services\Chatbot\GroqClient;
use Tests\TestCase;

/**
 * Tests du client Groq (fournisseur LLM actuel du chatbot).
 *
 * Aucun appel réseau réel — on vérifie uniquement la configuration
 * et la logique d'instanciation.
 */
class ChatbotGeminiClientTest extends TestCase
{
    public function test_constructeur_lecture_config(): void
    {
        // Le client se construit sans lever d'exception, même sans clé
        $client = new GroqClient();
        $this->assertInstanceOf(GroqClient::class, $client);
    }

    public function test_isConfigured_retourne_false_sans_cle(): void
    {
        config()->set('chatbot.groq.api_key', '');
        $client = new GroqClient();
        $this->assertFalse($client->isConfigured());
    }

    public function test_isConfigured_retourne_true_avec_cle(): void
    {
        config()->set('chatbot.groq.api_key', 'gsk_TestKey123456789');
        $client = new GroqClient();
        $this->assertTrue($client->isConfigured());
    }

    public function test_configuration_a_lecture_seule_depuis_env(): void
    {
        // Vérifier que la config Groq est correctement structurée
        $config = config('chatbot.groq');
        $this->assertIsArray($config);
        $this->assertArrayHasKey('api_key', $config);
        $this->assertArrayHasKey('model', $config);
        $this->assertArrayHasKey('timeout', $config);
        $this->assertArrayHasKey('base_url', $config);
    }

    public function test_modele_est_configure(): void
    {
        $config = config('chatbot.groq');
        // Le modèle doit être défini et non vide
        $this->assertNotEmpty($config['model']);
        $this->assertIsString($config['model']);
    }
}
