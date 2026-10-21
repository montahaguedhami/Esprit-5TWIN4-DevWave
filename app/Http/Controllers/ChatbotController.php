<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChatbotRequest;
use App\Models\User;
use App\Services\Chatbot\ChatbotOrchestrator;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class ChatbotController extends Controller
{
    public function __construct(
        private readonly ChatbotOrchestrator $orchestrator,
    ) {
    }

    /**
     * Endpoint de conversation du chatbot.
     *
     * L'authentification et l'autorisation sont gérées par le middleware `role:citoyen,manager,administrateur`
     * et par l'orchestrateur qui vérifie le rôle à chaque exécution d'outil.
     */
    public function ask(ChatbotRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        // Rate limiting par utilisateur (10 req/min par défaut)
        $key = 'chatbot:' . $user->id;
        if (! RateLimiter::attempt($key, 20, function () {}, (int) config('chatbot.rate_limit', 20))) {
            return response()->json([
                'response' => null,
                'error' => 'trop_de_requetes',
                'message' => 'Trop de requêtes. Veuillez patienter avant de poser une nouvelle question.',
            ], 429);
        }

        try {
            $result = $this->orchestrator->handle([
                'user'    => $user,
                'message' => $request->validated('message'),
                'history' => $request->input('history', []),
            ]);

            return response()->json([
                'response'       => $result['response'],
                'sources'        => $result['sources']      ?? [],
                'tool_calls'     => $result['tool_calls']   ?? [],
                'gemini_available' => $result['groq_available'] ?? $result['gemini_available'] ?? true,
                'groq_available'   => $result['groq_available'] ?? false,
            ], 200);
        } catch (\Throwable $e) {
            Log::error('ChatbotController : erreur inattendue.', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'response' => 'Une erreur interne est survenue. Veuillez réessayer plus tard.',
                'sources' => [],
                'tool_calls' => [],
                'error' => 'erreur_interne',
                'groq_available' => false,
            ], 200);
        }
    }

    /**
     * Vérifie si le chatbot est disponible (clé API configurée).
     */
    public function status(): JsonResponse
    {
        $groq = new \App\Services\Chatbot\GroqClient();

        return response()->json([
            'enabled'      => config('chatbot.enabled', true),
            'groq_available' => $groq->isConfigured(),
        ]);
    }
}
