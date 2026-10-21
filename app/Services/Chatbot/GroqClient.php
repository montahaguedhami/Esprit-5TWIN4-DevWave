<?php

namespace App\Services\Chatbot;

use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Client dédié à l'API Groq Cloud (compatible OpenAI).
 *
 * Responsabilités :
 *   - Authentification auprès de Groq (clé API depuis config/chatbot.php)
 *   - Construction des requêtes (messages, outils, configuration)
 *   - Lecture et validation des réponses
 *   - Gestion du Tool Calling (format OpenAI)
 *   - Délais d'attente, erreurs HTTP, quotas, réponses invalides
 *
 * L'API Groq est 100% compatible OpenAI → format messages/tools/tool_calls identique.
 */
class GroqClient
{
    private string $apiKey;
    private string $model;
    private int $timeout;
    private string $baseUrl;

    public function __construct()
    {
        $config = config('chatbot.groq', []);
        $this->apiKey  = (string) ($config['api_key'] ?? '');
        $this->model   = (string) ($config['model'] ?? 'qwen/qwen3.8-27b');
        $this->timeout = (int) ($config['timeout'] ?? 30);
        $this->baseUrl = (string) ($config['base_url'] ?? 'https://api.groq.com/openai/v1');
    }

    /**
     * Indique si le client est utilisable (clé configurée).
     */
    public function isConfigured(): bool
    {
        return !empty((string) config('chatbot.groq.api_key', ''));
    }

    /**
     * Envoie une conversation à Groq avec la liste des outils autorisés.
     *
     * @param  string  $systemPrompt  instruction système
     * @param  array   $messages      historique [{role, content}]
     * @param  array   $tools         outils déclarés (format OpenAI function calling)
     * @param  float|null $temperature
     * @return array{text: string|null, tool_calls: list<array{name: string, args: array}>, raw: array}
     *
     * @throws RuntimeException en cas d'erreur réseau, quota ou configuration
     */
    public function generate(
        string $systemPrompt,
        array $messages,
        array $tools = [],
        ?float $temperature = null
    ): array {
        if (! $this->isConfigured()) {
            throw new RuntimeException('Client Groq non configuré : clé API absente.');
        }

        // Construction des messages au format OpenAI
        $openaiMessages = [];

        if (!empty($systemPrompt)) {
            $openaiMessages[] = ['role' => 'system', 'content' => $systemPrompt];
        }

        foreach ($messages as $msg) {
            $openaiMessages[] = [
                'role'    => $msg['role'] ?? 'user',
                'content' => $msg['content'] ?? '',
            ];
        }

        $payload = [
            'model'       => $this->model,
            'messages'    => $openaiMessages,
            'temperature' => $temperature ?? 0.3,
            'max_tokens'  => 2048,
        ];

        // Ajout des outils si présents (format OpenAI function calling)
        if (!empty($tools)) {
            $payload['tools'] = array_map(fn(array $tool): array => [
                'type'     => 'function',
                'function' => [
                    'name'        => $tool['name'],
                    'description' => $tool['description'],
                    'parameters'  => $this->normalizeParameters($tool['parameters'] ?? []),
                ],
            ], $tools);
            $payload['tool_choice'] = 'auto';
        }

        $response = $this->callApi('/chat/completions', $payload);

        $choice = $response['choices'][0] ?? [];
        $message = $choice['message'] ?? [];

        $text      = isset($message['content']) && $message['content'] !== null
                        ? (string) $message['content']
                        : null;
        $toolCalls = [];

        // Désérialiser les appels d'outils (format OpenAI)
        foreach ($message['tool_calls'] ?? [] as $tc) {
            $fn   = $tc['function'] ?? [];
            $args = $fn['arguments'] ?? '{}';

            // arguments est une string JSON dans l'API OpenAI
            if (is_string($args)) {
                $args = json_decode($args, true) ?? [];
            }

            $toolCalls[] = [
                'name' => $fn['name'] ?? '',
                'args' => $args,
            ];
        }

        return [
            'text'       => $text,
            'tool_calls' => $toolCalls,
            'raw'        => $response,
        ];
    }

    /**
     * Envoie une requête HTTP POST vers l'API Groq.
     *
     * @throws RuntimeException
     */
    private function callApi(string $endpoint, array $payload): array
    {
        $url = rtrim($this->baseUrl, '/') . $endpoint;

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->apiKey,
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => $this->timeout,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_IPRESOLVE      => CURL_IPRESOLVE_V4,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ]);

        $body  = curl_exec($ch);
        $errno = curl_errno($ch);
        $error = curl_error($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Erreur réseau / timeout
        if ($errno !== 0) {
            Log::warning('Groq API : erreur réseau.', ['errno' => $errno, 'error' => $error]);
            throw new RuntimeException('Le service IA est inaccessible (problème réseau). Veuillez réessayer.');
        }

        $data = json_decode($body, true);

        if ($status === 429) {
            Log::warning('Groq API : quota dépassé.');
            throw new RuntimeException('Le service IA est temporairement surchargé (limite de requêtes). Attendez 1 minute et réessayez.');
        }

        if ($status === 503) {
            Log::warning('Groq API : service indisponible.');
            throw new RuntimeException('Le service IA est temporairement indisponible. Veuillez réessayer dans quelques instants.');
        }

        if ($status >= 400) {
            $msg = $data['error']['message'] ?? 'Erreur inconnue';
            Log::error('Groq API : erreur HTTP.', ['status' => $status, 'message' => $msg]);
            throw new RuntimeException('Erreur du service IA : ' . $msg);
        }

        return $data;
    }

    /**
     * Normalise les paramètres d'un outil pour l'API OpenAI/Groq.
     * - properties vide [] → objet stdClass {}
     */
    private function normalizeParameters(array $params): array
    {
        if (isset($params['properties']) && $params['properties'] === []) {
            $params['properties'] = new \stdClass();
        }

        // Supprimer champs non supportés
        unset($params['additionalProperties']);

        return $params;
    }
}
