<?php

namespace App\Services\Chatbot;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Client dédié à l'API Google Gemini Flash.
 *
 * Responsabilités :
 *   - Authentification auprès de Gemini (clé API depuis config/chatbot.php)
 *   - Construction des requêtes (messages, outils, configuration)
 *   - Lecture et validation des réponses
 *   - Gestion du Function Calling (désérialisation des appels d'outils)
 *   - Délais d'attente, erreurs HTTP, quotas, réponses invalides
 *   - Réponses de sécurité du fournisseur
 *
 * Aucune logique métier Laravel n'est placée ici : ce client est un adaptateur
 * HTTP vers l'API Gemini. L'orchestrateur décide quels outils sont autorisés.
 */
class GeminiClient
{
    private string $apiKey;
    private string $model;
    private int $timeout;
    private string $baseUrl = 'https://generativelanguage.googleapis.com/v1beta';

    public function __construct()
    {
        $config = config('chatbot.gemini', []);
        $this->apiKey = (string) ($config['api_key'] ?? '');
        $this->model = (string) ($config['model'] ?? 'gemini-1.5-flash-latest');
        $this->timeout = (int) ($config['timeout'] ?? 30);
    }

    /**
     * Indique si le client est utilisable (clé configurée).
     */
    public function isConfigured(): bool
    {
        return !empty((string) config('chatbot.gemini.api_key', ''));
    }

    /**
     * Envoie une conversation à Gemini Flash avec la liste des outils autorisés.
     *
     * @param  array<string, mixed>  $systemInstruction  instruction système (texte)
     * @param  array<int, array<string, mixed>>  $messages  historique des messages
     * @param  array<int, array<string, mixed>>  $tools  outils déclarés au modèle
     * @param  float|null  $temperature
     * @return array{text?: string|null, tool_calls: list<array{name: string, args: array<string, mixed>}>, raw: array<string, mixed>}
     *
     * @throws \RuntimeException en cas d'erreur de configuration, quota, réseau ou réponse invalide
     */
    public function generate(
        array $systemInstruction,
        array $messages,
        array $tools = [],
        ?float $temperature = null
    ): array {
        if (! $this->isConfigured()) {
            throw new RuntimeException('Client Gemini non configuré : clé API absente.');
        }

        $contents = $this->buildContents($messages);

        $payload = [
            'contents' => $contents,
            'systemInstruction' => $systemInstruction,
            'generationConfig' => array_filter([
                'temperature' => $temperature ?? 0.3,
                'maxOutputTokens' => 2048,
            ]),
        ];

        if ($tools !== []) {
            $payload['tools'] = [
                [
                    'functionDeclarations' => array_map(
                        fn (array $tool): array => $this->normalizeToolParameters($tool),
                        $tools
                    ),
                ],
            ];
            $payload['toolConfig'] = ['functionCallingConfig' => ['mode' => 'AUTO']];
        }

        try {
            $response = Http::timeout($this->timeout)
                ->withOptions([
                    'curl' => [
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_IPRESOLVE    => CURL_IPRESOLVE_V4,
                        CURLOPT_SSL_VERIFYPEER => false,
                        CURLOPT_SSL_VERIFYHOST => false,
                    ],
                    'verify' => false,
                    'version' => 1.1,
                ])
                ->post("{$this->baseUrl}/models/{$this->model}:generateContent?key={$this->apiKey}", $payload);
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::warning('Gemini API : timeout ou problème réseau.', ['error' => $e->getMessage()]);
            throw new RuntimeException('Le service IA est inaccessible (problème réseau ou timeout). Veuillez réessayer.');
        }

        if ($response->failed()) {
            $status = $response->status();
            $body = $response->body();

            // Erreurs de quota (429)
            if ($status === 429) {
                Log::warning('Gemini API : quota dépassé.', ['status' => $status, 'body' => $body]);
                throw new RuntimeException('Le service IA est temporairement surchargé. Veuillez réessayer plus tard.');
            }

            // Surcharge temporaire (503)
            if ($status === 503) {
                Log::warning('Gemini API : service temporairement indisponible.', ['status' => $status]);
                throw new RuntimeException('Le service IA est temporairement indisponible. Veuillez réessayer dans quelques instants.');
            }

            // Erreur de configuration (400/401/403)
            if (in_array($status, [400, 401, 403], true)) {
                Log::warning('Gemini API : erreur de configuration ou d\'authentification.', [
                    'status' => $status,
                    'body' => $this->sanitizeBody($body),
                ]);
                throw new RuntimeException('Le service IA n\'est pas correctement configuré.');
            }

            Log::error('Gemini API : erreur HTTP.', ['status' => $status, 'body' => $this->sanitizeBody($body)]);
            throw new RuntimeException('Erreur lors de l\'appel au service IA.');
        }

        $data = $response->json();

        if (! is_array($data)) {
            throw new RuntimeException('Réponse invalide du service IA.');
        }

        // Réponse de sécurité (bloquage par le fournisseur)
        if (isset($data['promptFeedback']) && ! empty($data['promptFeedback']['blockReason'])) {
            Log::info('Gemini API : réponse bloquée par le filtre de sécurité.', [
                'reason' => $data['promptFeedback']['blockReason'] ?? null,
            ]);
            throw new RuntimeException('Votre demande ne peut pas être traitée pour des raisons de sécurité.');
        }

        $candidates = $data['candidates'] ?? [];
        $text = null;
        $toolCalls = [];

        foreach ($candidates as $candidate) {
            if (! is_array($candidate)) {
                continue;
            }

            // Vérifie la fin de raison (sécurité, arrêt, etc.)
            $finishReason = $candidate['finishReason'] ?? null;
            if ($finishReason === 'SAFETY') {
                Log::info('Gemini API : réponse bloquée par le filtre de sécurité (candidate).', [
                    'reason' => $finishReason,
                ]);
                continue;
            }

            $content = $candidate['content'] ?? null;
            if (! is_array($content)) {
                continue;
            }

            $parts = $content['parts'] ?? [];
            if (! is_array($parts)) {
                continue;
            }

            foreach ($parts as $part) {
                if (! is_array($part)) {
                    continue;
                }

                if (isset($part['text']) && is_string($part['text'])) {
                    $text = $part['text'];
                }

                if (isset($part['functionCall']) && is_array($part['functionCall'])) {
                    $fc = $part['functionCall'];
                    $toolCalls[] = [
                        'name' => $fc['name'] ?? '',
                        'args' => $fc['args'] ?? [],
                    ];
                }
            }
        }

        return [
            'text' => $text,
            'tool_calls' => $toolCalls,
            'raw' => $data,
        ];
    }

    /**
     * Normalise les paramètres d'un outil pour la compatibilité Gemini.
     * - Les `properties` vides doivent être un objet JSON {} pas un tableau []
     * - Supprime les champs non supportés par Gemini (additionalProperties, minimum, maximum)
     */
    private function normalizeToolParameters(array $tool): array
    {
        if (isset($tool['parameters'])) {
            $params = $tool['parameters'];

            // properties vide : [] en PHP → {} en JSON (Gemini attend un objet)
            if (isset($params['properties']) && $params['properties'] === []) {
                $params['properties'] = new \stdClass();
            }

            // Nettoyer chaque propriété des champs non supportés par Gemini
            if (isset($params['properties']) && is_array($params['properties'])) {
                foreach ($params['properties'] as $propName => $propDef) {
                    unset(
                        $params['properties'][$propName]['minimum'],
                        $params['properties'][$propName]['maximum'],
                        $params['properties'][$propName]['additionalProperties']
                    );
                }
            }

            // Supprimer additionalProperties au niveau racine
            unset($params['additionalProperties']);

            $tool['parameters'] = $params;
        }

        return $tool;
    }

    /**
     * Construit le tableau `contents` attendu par l'API Gemini.
     */
    private function buildContents(array $messages): array
    {
        $contents = [];

        foreach ($messages as $message) {
            $role = $message['role'] ?? 'user';
            $text = $message['content'] ?? '';

            // Gemini utilise 'user' et 'model' (pas 'assistant')
            $geminiRole = ($role === 'assistant') ? 'model' : $role;

            $contents[] = [
                'role' => $geminiRole,
                'parts' => [
                    ['text' => $text],
                ],
            ];
        }

        return $contents;
    }

    /**
     * Masque les éventuels secrets dans le corps d'une réponse d'erreur
     * avant journalisation.
     */
    private function sanitizeBody(string $body): string
    {
        $sanitized = preg_replace('/"key"\s*:\s*"[^"]*"/', '"key":"[REDACTED]"', $body);
        $sanitized = preg_replace('/AIza[A-Za-z0-9_\-]+/', '[REDACTED_API_KEY]', $sanitized);

        return $sanitized;
    }
}
