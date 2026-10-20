<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class AiExplanationService
{
    public function explain(int $infrastructureId, array $factors, string $fallback): string
    {
        return Cache::remember(
            "failure-risk:explanation:{$infrastructureId}",
            now()->addHours((int) config('services.failure_risk.cache_hours', 24)),
            function () use ($infrastructureId, $factors, $fallback): string {
                $apiKey = (string) config('services.failure_risk.api_key', '');
                if ($apiKey === '') {
                    return $fallback;
                }

                $result = RateLimiter::attempt(
                    "failure-risk:explanation:{$infrastructureId}",
                    (int) config('services.failure_risk.max_requests_per_minute', 1),
                    fn () => $this->requestExplanation($apiKey, $factors, $fallback, $infrastructureId),
                    60,
                );

                return is_string($result) ? $result : $fallback;
            },
        );
    }

    private function requestExplanation(string $apiKey, array $factors, string $fallback, int $infrastructureId): string
    {
        $structuredFactors = array_map(
            static fn (array $factor): array => [
                'key' => $factor['key'],
                'value' => $factor['value'],
                'weight' => $factor['weight'],
                'contribution' => $factor['contribution'],
                'explanation' => $factor['explanation'],
            ],
            $factors,
        );

        try {
            $response = Http::acceptJson()
                ->withToken($apiKey)
                ->timeout((int) config('services.failure_risk.timeout_seconds', 5))
                ->post((string) config('services.failure_risk.endpoint'), [
                    'model' => (string) config('services.failure_risk.model'),
                    'temperature' => 0.2,
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'Réécris les facteurs fournis en exactement deux phrases claires en français. Ne déduis aucune donnée absente, ne modifie pas les probabilités, ne recommande pas de décision différente. Réponds uniquement avec ces deux phrases.',
                        ],
                        [
                            'role' => 'user',
                            'content' => json_encode($structuredFactors, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
                        ],
                    ],
                ])
                ->throw();

            $explanation = trim((string) $response->json('choices.0.message.content', ''));
            if ($explanation === '' || preg_match_all('/[.!?](?:\s|$)/u', $explanation) !== 2) {
                Log::warning('La réponse du service d’explication du risque est invalide.', [
                    'infrastructure_id' => $infrastructureId,
                ]);

                return $fallback;
            }

            return $explanation;
        } catch (ConnectionException|RequestException $exception) {
            Log::warning('Le service d’explication du risque est indisponible.', [
                'infrastructure_id' => $infrastructureId,
                'error' => $exception->getMessage(),
            ]);

            return $fallback;
        }
    }
}
