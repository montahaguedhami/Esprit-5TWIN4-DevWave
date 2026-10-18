<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class RecommendationAiService
{
    public function recommend(int $infrastructureId, array $factors, string $level, string $fallback): string
    {
        return Cache::remember(
            "recommendation-ai:{$infrastructureId}:{$level}",
            now()->addHours((int) config('services.recommendation_ai.cache_hours', 24)),
            function () use ($infrastructureId, $factors, $level, $fallback): string {
                $apiKey = (string) config('services.recommendation_ai.api_key', '');
                if ($apiKey === '') {
                    return $fallback;
                }

                $result = RateLimiter::attempt(
                    "recommendation-ai:{$infrastructureId}",
                    (int) config('services.recommendation_ai.max_requests_per_minute', 1),
                    fn () => $this->requestRecommendation($apiKey, $factors, $level, $fallback, $infrastructureId),
                    60,
                );

                return is_string($result) ? $result : $fallback;
            },
        );
    }

    private function requestRecommendation(string $apiKey, array $factors, string $level, string $fallback, int $infrastructureId): string
    {
        $topFactors = array_values(array_filter(
            array_slice($factors, 0, 3),
            static fn (array $factor): bool => $factor['contribution'] > 0,
        ));

        $structured = [
            'level' => $level,
            'top_factors' => array_map(static fn (array $f): array => [
                'name' => $f['name'],
                'contribution' => $f['contribution'],
                'explanation' => $f['explanation'],
            ], $topFactors),
        ];

        try {
            $response = Http::acceptJson()
                ->withToken($apiKey)
                ->timeout((int) config('services.recommendation_ai.timeout_seconds', 8))
                ->post((string) config('services.recommendation_ai.endpoint'), [
                    'model' => (string) config('services.recommendation_ai.model'),
                    'temperature' => 0.3,
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'Tu es un expert en gestion de réseaux d’eau potable. À partir du niveau de risque et des facteurs techniques fournis, génère une recommandation d’action préventive personnalisée en français, en une seule phrase claire et opérationnelle. Ne modifies pas le score de risque, ne recommande pas de décision d’arrêt ou de remplacement d’ouvrage, ne déduis pas de données absentes. Réponds uniquement avec cette phrase.',
                        ],
                        [
                            'role' => 'user',
                            'content' => json_encode($structured, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
                        ],
                    ],
                ])
                ->throw();

            $recommendation = trim((string) $response->json('choices.0.message.content', ''));
            if ($recommendation === '') {
                Log::warning('La réponse du service de recommandation IA est vide.', [
                    'infrastructure_id' => $infrastructureId,
                ]);

                return $fallback;
            }

            return $recommendation;
        } catch (ConnectionException|RequestException $exception) {
            Log::warning('Le service de recommandation IA est indisponible.', [
                'infrastructure_id' => $infrastructureId,
                'error' => $exception->getMessage(),
            ]);

            return $fallback;
        }
    }
}
