<?php

namespace App\Services\Ai;

use Illuminate\Http\Client\ConnectionException;
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

        $model = (string) config('services.gemini.model', 'gemini-3.8-flash');
        $baseUrl = rtrim((string) config('services.gemini.base_url'), '/');
        $payload = [
            'system_instruction' => ['parts' => [['text' => $systemInstruction]]],
            'contents' => [['role' => 'user', 'parts' => [['text' => $userPrompt]]]],
            'generationConfig' => [
                'temperature' => $temperature,
                'responseMimeType' => 'application/json',
                'responseSchema' => $responseSchema,
            ],
        ];
        $sendRequest = function (string $requestModel) use ($baseUrl, $payload) {
            return Http::withHeaders(['x-goog-api-key' => (string) config('services.gemini.api_key')])
                ->acceptJson()
                ->timeout((int) config('services.gemini.timeout', 30))
                ->post($baseUrl . '/models/' . rawurlencode($requestModel) . ':generateContent', $payload);
        };

        try {
            $response = $sendRequest($model);

            $fallbackModel = (string) config('services.gemini.fallback_model', '');
            if ($response->status() === 503 && filled($fallbackModel) && $fallbackModel !== $model) {
                Log::warning('Gemini: modèle principal surchargé, essai du modèle de secours', [
                    'model' => $model,
                    'fallback_model' => $fallbackModel,
                ]);
                $model = $fallbackModel;
                $response = $sendRequest($model);
            }
        } catch (ConnectionException $e) {
            Log::warning('Gemini: connexion impossible', ['error' => $e->getMessage()]);
            throw new AiUnavailableException('Le service IA est momentanément injoignable.');
        }

        if ($response->failed()) {
            // Never log the API key; only status and Google's error message.
            Log::warning('Gemini: réponse en erreur', [
                'model' => $model,
                'status' => $response->status(),
                'error' => $response->json('error.message'),
            ]);

            $message = match ($response->status()) {
                429 => 'Le quota de l’assistant IA est atteint. Réessayez dans quelques instants.',
                503 => 'Les modèles Gemini sont temporairement surchargés. Réessayez dans quelques instants.',
                400, 401, 403 => 'Clé API Gemini invalide ou non autorisée (vérifiez GEMINI_API_KEY).',
                404 => 'Modèle Gemini introuvable (vérifiez GEMINI_MODEL).',
                default => 'Le service IA n’a pas pu traiter la demande.',
            };

            throw new AiUnavailableException($message);
        }

        $text = collect($response->json('candidates.0.content.parts', []))
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
