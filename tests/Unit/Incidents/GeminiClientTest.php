<?php

namespace Tests\Unit\Incidents;

use App\Services\Ai\AiUnavailableException;
use App\Services\Ai\GeminiClient;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class GeminiClientTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Log::spy();
        config([
            'services.gemini.api_key' => 'test-key',
            'services.gemini.model' => 'gemini-test',
            'services.gemini.base_url' => 'https://generativelanguage.googleapis.com/v1beta',
        ]);
    }

    private function generate(): array
    {
        return (new GeminiClient())->generateJson('system', 'prompt', ['type' => 'OBJECT']);
    }

    private function assertFailsWith(string $expectedMessage): void
    {
        try {
            $this->generate();
            $this->fail('AiUnavailableException attendue.');
        } catch (AiUnavailableException $e) {
            $this->assertStringContainsString($expectedMessage, $e->getMessage());
        }
    }

    public function test_is_configured_depends_on_api_key(): void
    {
        $this->assertTrue((new GeminiClient())->isConfigured());

        config(['services.gemini.api_key' => null]);
        $this->assertFalse((new GeminiClient())->isConfigured());
    }

    public function test_missing_key_throws_without_calling_google(): void
    {
        Http::fake();
        config(['services.gemini.api_key' => '']);

        $this->assertFailsWith('GEMINI_API_KEY');
        Http::assertNothingSent();
    }

    public function test_successful_call_returns_decoded_json_and_ignores_thought_parts(): void
    {
        Http::fake(['*' => Http::response([
            'candidates' => [['content' => ['parts' => [
                ['text' => 'réflexion interne', 'thought' => true],
                ['text' => '{"conseils":'],
                ['text' => '["Rester à distance"]}'],
            ]]]],
        ])]);

        $this->assertSame(['conseils' => ['Rester à distance']], $this->generate());
    }

    public function test_request_contains_key_header_model_instructions_and_schema(): void
    {
        Http::fake(['*' => Http::response(['candidates' => [['content' => ['parts' => [['text' => '{}']]]]]])]);

        $this->generate();

        Http::assertSent(function (HttpRequest $request) {
            return $request->url() === 'https://generativelanguage.googleapis.com/v1beta/models/gemini-test:generateContent'
                && $request->hasHeader('x-goog-api-key', 'test-key')
                && $request['system_instruction']['parts'][0]['text'] === 'system'
                && $request['contents'][0]['parts'][0]['text'] === 'prompt'
                && $request['generationConfig']['responseMimeType'] === 'application/json'
                && $request['generationConfig']['responseSchema'] === ['type' => 'OBJECT'];
        });
    }

    public function test_invalid_json_response_throws(): void
    {
        Http::fake(['*' => Http::response(['candidates' => [['content' => ['parts' => [['text' => 'pas du json']]]]]])]);

        $this->assertFailsWith('inexploitable');
    }

    public function test_empty_candidates_throws(): void
    {
        Http::fake(['*' => Http::response(['promptFeedback' => ['blockReason' => 'SAFETY']])]);

        $this->assertFailsWith('inexploitable');
    }

    public function test_quota_error_has_specific_message(): void
    {
        Http::fake(['*' => Http::response(['error' => ['message' => 'quota']], 429)]);

        $this->assertFailsWith('quota');
    }

    public function test_unknown_model_has_specific_message(): void
    {
        Http::fake(['*' => Http::response(['error' => ['message' => 'not found']], 404)]);

        $this->assertFailsWith('GEMINI_MODEL');
    }

    public function test_invalid_key_has_specific_message(): void
    {
        Http::fake(['*' => Http::response(['error' => ['message' => 'API key not valid']], 400)]);

        $this->assertFailsWith('Clé API Gemini invalide');
    }

    public function test_server_error_has_generic_message(): void
    {
        Http::fake(['*' => Http::response([], 500)]);

        $this->assertFailsWith('n’a pas pu traiter');
    }

    public function test_connection_failure_throws(): void
    {
        Http::fake(['*' => Http::failedConnection()]);

        $this->assertFailsWith('injoignable');
    }

    public function test_api_key_is_never_logged(): void
    {
        Http::fake(['*' => Http::response(['error' => ['message' => 'boom']], 500)]);

        try {
            $this->generate();
        } catch (AiUnavailableException) {
        }

        Log::shouldHaveReceived('warning')->withArgs(fn ($message, $context) => ! str_contains(json_encode($context), 'test-key'));
    }
}
