<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Client minimal pour le serveur Ollama local (même configuration que RagAnalysisService : config/ollama.php).
 * Ne lève jamais d'exception : en cas d'indisponibilité il renvoie null et l'appelant bascule sur son calcul de secours.
 */
class OllamaClient
{
    public function active(): bool
    {
        return (bool) config('ollama.enabled', true);
    }

    /**
     * Demande une réponse JSON au modèle et la décode.
     *
     * @return array<string, mixed>|null
     */
    public function json(string $prompt, ?int $timeout = null): ?array
    {
        if (! $this->active()) {
            return null;
        }

        try {
            $reponse = Http::timeout($timeout ?? (int) config('ollama.timeout', 60))
                ->post(rtrim((string) config('ollama.url'), '/').'/api/generate', [
                    'model' => config('ollama.model'),
                    'prompt' => $prompt,
                    'format' => 'json',
                    'stream' => false,
                    'options' => ['temperature' => (float) config('ollama.temperature', 0.1)],
                ])
                ->throw();

            $donnees = json_decode((string) $reponse->json('response'), true);

            return is_array($donnees) ? $donnees : null;
        } catch (\Throwable $e) {
            Log::info('OllamaClient : modèle indisponible, calcul de secours utilisé', ['erreur' => $e->getMessage()]);

            return null;
        }
    }
}
