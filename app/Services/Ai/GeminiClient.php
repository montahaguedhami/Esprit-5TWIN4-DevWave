<?php

namespace App\Services\Ai;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Minimal client for the Google Gemini API (Google AI Studio key).
 * Returns the decoded JSON object produced by the model (structured output).
 */
class GeminiClient
{
    public function isConfigured(): bool
    {
        return filled(config('services.gemini.api_key'));
    }

    /**
     * @param  array<string, mixed>  $responseSchema  OpenAPI-subset schema enforced by Gemini
     * @return array<string, mixed>
     *
     * @throws AiUnavailableException
     */
    public function generateJson(string $systemInstruction, string $userPrompt, array $responseSchema, float $temperature = 0.3): array
    {
        if (! $this->isConfigured()) {
            throw new AiUnavailableException('L’assistant IA n’est pas configuré (clé GEMINI_API_KEY manquante).');
        }

        $payload = [
            'system_instruction' => ['parts' => [['text' => $systemInstruction]]],
            'contents' => [['role' => 'user', 'parts' => [['text' => $userPrompt]]]],
            'generationConfig' => [
                'temperature' => $temperature,
                'responseMimeType' => 'application/json',
                'responseSchema' => $responseSchema,
            ],
        ];

        // Modèle principal puis modèle de secours (GEMINI_FALLBACK_MODEL) si le premier est indisponible
        $modeles = array_values(array_unique(array_filter([
            (string) config('services.gemini.model', 'gemini-3.8-flash'),
            (string) config('services.gemini.fallback_model'),
        ])));

        foreach ($modeles as $rang => $model) {
            $dernier = $rang === count($modeles) - 1;

            try {
                $response = $this->appeler($model, $payload);
            } catch (AiUnavailableException $e) {
                if ($dernier) {
                    throw $e;
                }

                continue;
            }

            if ($response->successful()) {
                return $this->decoder($response);
            }

            // Never log the API key; only status and Google's error message.
            Log::warning('Gemini: réponse en erreur', [
                'model' => $model,
                'status' => $response->status(),
                'error' => $response->json('error.message'),
            ]);

            // Clé refusée : le modèle de secours échouerait de la même façon
            $temporaire = ! in_array($response->status(), [400, 401, 403], true);
            if ($temporaire && ! $dernier) {
                continue;
            }

            throw new AiUnavailableException(match ($response->status()) {
                429 => 'Le quota de l’assistant IA est atteint. Réessayez dans quelques instants.',
                400, 401, 403 => 'Clé API Gemini invalide ou non autorisée (vérifiez GEMINI_API_KEY).',
                404 => 'Modèle Gemini introuvable (vérifiez GEMINI_MODEL et GEMINI_FALLBACK_MODEL).',
                503 => 'Les modèles Gemini sont temporairement surchargés. Réessayez dans quelques instants.',
                default => 'Le service IA n’a pas pu traiter la demande.',
            });
        }

        throw new AiUnavailableException('Le service IA n’a pas pu traiter la demande.');
    }

    /**
     * @param  array<string, mixed>  $payload
     *
     * @throws AiUnavailableException
     */
    private function appeler(string $model, array $payload): Response
    {
        $url = rtrim((string) config('services.gemini.base_url'), '/') . '/models/' . rawurlencode($model) . ':generateContent';

        try {
            return Http::withHeaders(['x-goog-api-key' => (string) config('services.gemini.api_key')])
                ->acceptJson()
                ->timeout((int) config('services.gemini.timeout', 30))
                ->post($url, $payload);
        } catch (ConnectionException $e) {
            Log::warning('Gemini: connexion impossible', ['model' => $model, 'error' => $e->getMessage()]);
            throw new AiUnavailableException('Le service IA est momentanément injoignable.');
        }
    }

    /**
     * @return array<string, mixed>
     *
     * @throws AiUnavailableException
     */
    private function decoder(Response $response): array
    {
        $text =collect($response->json('candidates.0.content.parts', []))
            ->reject(fn ($part) => ! empty($part['thought']))
            ->pluck('text')
            ->filter()
            ->implode('');

        $decoded = json_decode($text, true);

        if (! is_array($decoded)) {
            Log::warning('Gemini: réponse non JSON', [
                'finish_reason' => $response->json('candidates.0.finishReason'),
                'block_reason' => $response->json('promptFeedback.blockReason'),
            ]);
            throw new AiUnavailableException('La réponse de l’assistant IA est inexploitable. Réessayez.');
        }

        return $decoded;
    }
}
