<?php

namespace Tests\Unit;

use App\Services\Chatbot\GeminiClient;
use Tests\TestCase;

/**
 * Tests du client Gemini sans appel externe réel.
 */
class ChatbotGeminiClientTest extends TestCase
{
    public function test_constructeur_lecture_config(): void
    {
        // Le client se construit sans lever d'exception, même sans clé
        $client = new GeminiClient();
        $this->assertInstanceOf(GeminiClient::class, $client);
    }

    public function test_isConfigured_retourne_false_sans_cle(): void
    {
        // Quand la clé est absente, le client n'est pas configuré
        config()->set('chatbot.gemini.api_key', '');
        $client = new GeminiClient();
        $this->assertFalse($client->isConfigured());
    }

    public function test_isConfigured_retourne_true_avec_cle(): void
    {
        config()->set('chatbot.gemini.api_key', 'AIzaTestKey123456789');
        $client = new GeminiClient();
        $this->assertTrue($client->isConfigured());
    }

    public function test_configuration_a_lecture_seule_depuis_env(): void
    {
        // Vérifier que la config lit depuis env() sans coder en dur
        $config = config('chatbot.gemini');
        $this->assertArrayHasKey('api_key', $config);
        $this->assertArrayHasKey('model', $config);
        $this->assertArrayHasKey('timeout', $config);
    }

    public function test_modele_est_flash(): void
    {
        $config = config('chatbot.gemini');
        // Le modèle doit être un modèle Flash compatible
        $this->assertStringContainsString('flash', (string) $config['model']);
    }
}
