<?php

namespace App\Services\Chatbot;

use App\Models\User;
use App\Services\Chatbot\GroqClient;
use App\Services\Chatbot\QuestionClassifier;
use App\Services\Chatbot\DirectToolResponder;
use App\Services\Chatbot\Tools\GetFundingDashboardSummaryTool;
use App\Services\Chatbot\Tools\GetFundingRulesTool;
use App\Services\Chatbot\Tools\GetManagerProjectBudgetReportTool;
use App\Services\Chatbot\Tools\GetMyFundingPortfolioTool;
use App\Services\Chatbot\Tools\GetMyFundingRequestsTool;
use App\Services\Chatbot\Tools\GetMyPaymentsTool;
use App\Services\Chatbot\Tools\GetMyProjectContributionsTool;
use App\Services\Chatbot\Tools\GetPendingFundingRequestsTool;
use App\Services\Chatbot\Tools\GetProjectDetailsTool;
use App\Services\Chatbot\Tools\GetProjectFundingStatusTool;
use App\Services\Chatbot\Tools\GetProjectRecommendationsTool;
use App\Services\Chatbot\Tools\SearchDocumentationTool;
use App\Services\Chatbot\Tools\SearchProjectsTool;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Orchestrateur du chatbot IA (fournisseur : Groq Cloud).
 *
 * Architecture 3 niveaux :
 *   Niveau 1 — TOOL_DIRECT  (0 token LLM) : exécution directe d'un outil métier
 *   Niveau 2 — RAG_DIRECT   (0 token LLM) : recherche documentaire full-text
 *   Niveau 3 — LLM Groq     (appel LLM)   : questions complexes, multi-outils
 *
 * Aucune logique d'autorisation n'est déléguée au modèle : Laravel décide.
 */
class ChatbotOrchestrator
{
    public const MAX_TOOL_CALLS     = 3;
    public const MAX_MESSAGE_LENGTH = 2000;

    private GroqClient           $groq;
    private ToolRegistry         $registry;
    private DocumentSearchService $documentSearch;
    private QuestionClassifier   $classifier;
    private DirectToolResponder  $directResponder;
    private array                $executors = [];
    private string               $systemInstruction;

    public function __construct(
        GroqClient $groq,
        ToolRegistry $registry,
        DocumentSearchService $documentSearch,
        QuestionClassifier $classifier,
        DirectToolResponder $directResponder,
        SearchProjectsTool $searchProjects,
        GetProjectDetailsTool $getProjectDetails,
        GetProjectFundingStatusTool $getProjectFundingStatus,
        GetFundingRulesTool $getFundingRules,
        SearchDocumentationTool $searchDocumentation,
        GetProjectRecommendationsTool $getProjectRecommendations,
        GetMyFundingRequestsTool $getMyFundingRequests,
        GetMyPaymentsTool $getMyPayments,
        GetMyFundingPortfolioTool $getMyFundingPortfolio,
        GetMyProjectContributionsTool $getMyProjectContributions,
        GetFundingDashboardSummaryTool $getFundingDashboardSummary,
        GetPendingFundingRequestsTool $getPendingFundingRequests,
        GetManagerProjectBudgetReportTool $getManagerProjectBudgetReport,
    ) {
        $this->groq            = $groq;
        $this->registry        = $registry;
        $this->documentSearch  = $documentSearch;
        $this->classifier      = $classifier;
        $this->directResponder = $directResponder;

        $this->executors = [
            'search_projects'                   => $searchProjects,
            'get_project_details'               => $getProjectDetails,
            'get_project_funding_status'        => $getProjectFundingStatus,
            'get_funding_rules'                 => $getFundingRules,
            'search_documentation'              => $searchDocumentation,
            'get_project_recommendations'       => $getProjectRecommendations,
            'get_my_funding_requests'           => $getMyFundingRequests,
            'get_my_payments'                   => $getMyPayments,
            'get_my_funding_portfolio'          => $getMyFundingPortfolio,
            'get_my_project_contributions'      => $getMyProjectContributions,
            'get_funding_dashboard_summary'     => $getFundingDashboardSummary,
            'get_pending_funding_requests'      => $getPendingFundingRequests,
            'get_manager_project_budget_report' => $getManagerProjectBudgetReport,
        ];

        $this->systemInstruction = $this->buildSystemInstruction();
    }

    /**
     * Traite une question et retourne la réponse avec métadonnées.
     * NE JAMAIS laisser d'exception s'échapper : retourne toujours un tableau valide.
     *
     * @return array{
     *   response: string,
     *   sources: list<array>,
     *   tool_calls: list<array>,
     *   error: string|null,
     *   groq_available: bool
     * }
     */
    public function handle(array $input): array
    {
        try {
            return $this->handleInternal($input);
        } catch (\Throwable $e) {
            Log::error('ChatbotOrchestrator : exception non attrapée.', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'response'        => 'Je n\'ai pas pu traiter votre demande. Veuillez reformuler votre question.',
                'sources'         => [],
                'tool_calls'      => [],
                'error'           => 'erreur_interne',
                'groq_available'  => false,
            ];
        }
    }

    private function handleInternal(array $input): array
    {
        $user    = $input['user'] ?? null;
        $message = $input['message'] ?? '';
        $history = $input['history'] ?? [];

        // ── Validation ─────────────────────────────────────────────────────
        try {
            Validator::make(['message' => $message], [
                'message' => ['required', 'string', 'min:1', 'max:' . self::MAX_MESSAGE_LENGTH],
            ])->validateWithBag('chatbot');
        } catch (ValidationException $e) {
            return $this->errorResponse(
                'Votre question est trop longue ou vide. Veuillez la reformuler.',
                $e->validator->errors()->first('message')
            );
        }

        // ── Authentification ────────────────────────────────────────────────
        if (! $user instanceof User || ! method_exists($user, 'hasAnyRole')) {
            return $this->errorResponse('Vous devez être connecté pour utiliser l\'assistant.', 'non_authentifie');
        }

        // ── Vérification Groq ───────────────────────────────────────────────
        if (! $this->groq->isConfigured()) {
            return $this->errorResponse($this->unavailableMessage(), 'groq_non_configure');
        }

        $role = $user->getPrimaryRoleAttribute();

        // ════════════════════════════════════════════════════════════════════
        // NIVEAU 1 — TOOL_DIRECT (0 token LLM)
        // ════════════════════════════════════════════════════════════════════
        try {
            $classification = $this->classifier->classify($message, $role);
        } catch (\Throwable $e) {
            Log::warning('Chatbot : erreur QuestionClassifier.', ['error' => $e->getMessage()]);
            $classification = ['strategy' => QuestionClassifier::LLM, 'tool' => null, 'tool_args' => [], 'confidence' => 0];
        }

        if ($classification['strategy'] === QuestionClassifier::TOOL_DIRECT) {
            $toolName = $classification['tool'] ?? '';
            $toolArgs = $classification['tool_args'] ?? [];

            Log::info('Chatbot : TOOL_DIRECT', ['tool' => $toolName, 'user_id' => $user->id]);

            $response = $this->directResponder->respond($user, $toolName, $toolArgs);

            if ($response !== null) {
                return $this->successResponse($response, [], [['tool' => $toolName, 'success' => true]]);
            }

            // Fallback LLM si DirectToolResponder échoue
            Log::info('Chatbot : TOOL_DIRECT fallback LLM', ['tool' => $toolName]);
        }

        // ════════════════════════════════════════════════════════════════════
        // NIVEAU 2 — RAG_DIRECT (0 token LLM)
        // ════════════════════════════════════════════════════════════════════
        if ($classification['strategy'] === QuestionClassifier::RAG_DIRECT) {
            Log::info('Chatbot : RAG_DIRECT', ['user_id' => $user->id]);

            try {
                $chunks = $this->documentSearch->search($message, (int) config('chatbot.max_rag_chunks', 5));
            } catch (\Throwable $e) {
                Log::warning('Chatbot : erreur recherche documentaire.', ['error' => $e->getMessage()]);
                $chunks = [];
            }

            if (!empty($chunks)) {
                $response = $this->formatRagResponse($message, $chunks);
                $sources  = array_map(fn(array $c): array => [
                    'document_title' => $c['document_title'] ?? '',
                    'section'        => $c['section'] ?? null,
                ], $chunks);

                return $this->successResponse($response, $sources, []);
            }

            // Aucun chunk trouvé → fallback LLM
            Log::info('Chatbot : RAG_DIRECT vide, fallback LLM');
        }

        // ════════════════════════════════════════════════════════════════════
        // NIVEAU 3 — LLM Groq (questions complexes / contextuelles)
        // ════════════════════════════════════════════════════════════════════
        return $this->handleWithLlm($user, $role, $message, $history);
    }

    // ── Niveau 3 : LLM Groq ──────────────────────────────────────────────────

    private function handleWithLlm(User $user, string $role, string $message, array $history): array
    {
        try {
            Log::info('Chatbot : LLM', ['user_id' => $user->id]);

            // Outils accessibles à ce rôle
            $accessibleTools = $this->registry->forRole($role);
            $toolDefinitions = array_values(array_map(fn(array $t): array => [
                'name'        => $t['name'],
                'description' => $t['description'],
                'parameters'  => $t['parameters'],
            ], $accessibleTools));

            // RAG contextuel (léger)
            $ragContext = '';
            if (config('chatbot.rag_enabled', true)) {
                try {
                    $chunks = $this->documentSearch->search($message, 3);
                    if (!empty($chunks)) {
                        $ragContext = "\n\n[Documentation pertinente]\n";
                        foreach ($chunks as $chunk) {
                            $ragContext .= "--- [{$chunk['document_title']}] ---\n{$chunk['content']}\n";
                        }
                    }
                } catch (\Throwable $e) {
                    Log::warning('Chatbot : erreur contexte RAG.', ['error' => $e->getMessage()]);
                }
            }

            // Construction des messages
            $messages       = [];
            $recentHistory  = array_slice($history, -10);

            foreach ($recentHistory as $turn) {
                $r = $turn['role'] ?? 'user';
                $c = $turn['content'] ?? '';
                if (!empty($c) && in_array($r, ['user', 'assistant'])) {
                    $messages[] = ['role' => $r, 'content' => $c];
                }
            }

            $userContent = $message . ($ragContext !== '' ? $ragContext : '');
            $messages[]  = ['role' => 'user', 'content' => $userContent];

            // Premier appel Groq
            try {
                $result          = $this->groq->generate($this->systemInstruction, $messages, $toolDefinitions);
                $toolCalls       = [];
                $sources         = [];
                $finalText       = $result['text'] ?? '';
                $toolCallResults = [];

                // Exécution des outils proposés par Groq
                foreach ($result['tool_calls'] as $toolCall) {
                    if (count($toolCallResults) >= self::MAX_TOOL_CALLS) {
                        break;
                    }

                    $toolName = $toolCall['name'] ?? '';
                    $args     = $toolCall['args'] ?? [];

                    if (! $this->registry->exists($toolName)) {
                        Log::warning('Chatbot LLM : outil inconnu ignoré.', ['tool' => $toolName]);
                        continue;
                    }

                    $toolDef = $this->registry->get($toolName);
                    if ($toolDef === null || ! in_array($role, $toolDef['roles'], true)) {
                        Log::warning('Chatbot LLM : outil refusé RBAC.', ['tool' => $toolName, 'role' => $role]);
                        $toolCallResults[] = ['tool' => $toolName, 'error' => 'Permission refusée.'];
                        continue;
                    }

                    $executor = $this->executors[$toolName] ?? null;
                    if ($executor === null || ! $executor->isAuthorized($user)) {
                        $toolCallResults[] = ['tool' => $toolName, 'error' => 'Accès refusé.'];
                        continue;
                    }

                    try {
                        $validatedArgs = $executor->validateArguments($args);
                        $toolResult    = $executor->execute($user, $validatedArgs);

                        if ($toolName === 'search_documentation' && isset($toolResult['results'])) {
                            $sources = array_values($toolResult['results']);
                        }

                        $toolCalls[]       = ['tool' => $toolName, 'success' => true];
                        $toolCallResults[] = ['tool' => $toolName, 'result' => $toolResult];

                    } catch (ValidationException $e) {
                        $toolCallResults[] = ['tool' => $toolName, 'error' => 'Arguments invalides : ' . $e->validator->errors()->first()];
                    } catch (\Throwable $e) {
                        Log::error('Chatbot LLM : erreur outil.', ['tool' => $toolName, 'error' => $e->getMessage()]);
                        $toolCallResults[] = ['tool' => $toolName, 'error' => 'Erreur lors de l\'exécution.'];
                    }
                }

                // Second appel Groq si des outils ont été exécutés
                if ($toolCallResults !== []) {
                    $followUp   = $messages;
                    $followUp[] = ['role' => 'assistant', 'content' => $finalText ?? ''];
                    $followUp[] = ['role' => 'user', 'content' => $this->buildToolResultsContent($message, $toolCallResults, $role)];

                    try {
                        $followUpResult = $this->groq->generate($this->systemInstruction, $followUp, []);
                        $finalText      = $followUpResult['text'] ?? $finalText;
                    } catch (\Throwable $fe) {
                        Log::warning('Chatbot LLM : second appel échoué.', ['error' => $fe->getMessage()]);
                    }
                }

                if (empty(trim($finalText ?? ''))) {
                    $finalText = 'Je n\'ai pas pu générer une réponse. Veuillez reformuler votre question.';
                }

                return $this->successResponse($finalText, $sources, $toolCalls);

            } catch (\RuntimeException $e) {
                Log::error('Chatbot LLM : erreur Groq.', ['error' => $e->getMessage(), 'user_id' => $user->id]);

                $isConfigError = str_contains($e->getMessage(), 'configuré') || str_contains($e->getMessage(), 'absente');
                $isToolError   = str_contains($e->getMessage(), 'tool call validation') || str_contains($e->getMessage(), 'not in request.tools');

                // Fallback sans tools si Groq a appelé un outil inconnu
                if ($isToolError) {
                    try {
                        $fallback = $this->groq->generate($this->systemInstruction, $messages, []);
                        $text     = $fallback['text'] ?? '';
                        if (!empty(trim($text))) {
                            return $this->successResponse($text, [], []);
                        }
                    } catch (\Throwable $fe) {
                        Log::warning('Chatbot LLM : fallback sans tools échoué.', ['error' => $fe->getMessage()]);
                    }
                    return $this->errorResponse('Je n\'ai pas pu traiter votre demande. Veuillez reformuler.', 'groq_erreur');
                }

                return [
                    'response'        => $isConfigError ? $this->unavailableMessage() : $e->getMessage(),
                    'sources'         => [],
                    'tool_calls'      => [],
                    'error'           => 'groq_erreur',
                    'groq_available'  => ! $isConfigError,
                ];
            }
        } catch (\Throwable $e) {
            Log::error('Chatbot LLM : exception inattendue.', ['error' => $e->getMessage()]);

            return [
                'response'        => 'Je n\'ai pas pu générer une réponse. Veuillez reformuler votre question.',
                'sources'         => [],
                'tool_calls'      => [],
                'error'           => 'groq_erreur',
                'groq_available'  => false,
            ];
        }
    }

    // ── Formatters ──────────────────────────────────────────────────────────

    /**
     * Formate une réponse RAG à partir des chunks documentaires.
     * Déduplique les sections et nettoie les titres H1 redondants.
     */
    private function formatRagResponse(string $question, array $chunks): string
    {
        if (empty($chunks)) {
            return "Je n'ai pas trouvé d'information documentaire sur ce sujet. "
                . "Vous pouvez consulter directement la section Projets et Financements.";
        }

        $parts   = [];
        $seen    = [];
        $sources = [];

        foreach ($chunks as $chunk) {
            $content  = trim($chunk['content'] ?? '');
            if (empty($content)) continue;

            // Déduplication par les 80 premiers caractères normalisés
            $key = mb_substr(preg_replace('/\s+/', ' ', $content), 0, 80);
            if (isset($seen[$key])) continue;
            $seen[$key] = true;

            // Supprimer le titre H1 principal du document (ex: "# Foire aux questions")
            $cleaned = $this->cleanChunkContent($content);
            if (!empty($cleaned)) {
                $parts[] = $cleaned;
            }

            $docTitle = $chunk['document_title'] ?? '';
            if ($docTitle && !in_array($docTitle, $sources)) {
                $sources[] = $docTitle;
            }
        }

        if (empty($parts)) {
            return "Je n'ai pas trouvé d'information documentaire sur ce sujet.";
        }

        $body = implode("\n\n", $parts);

        if (!empty($sources)) {
            $body .= "\n\n---\n*Source : " . implode(', ', $sources) . "*";
        }

        return $body;
    }

    /**
     * Supprime le titre H1 redondant en début de chunk et normalise le contenu.
     */
    private function cleanChunkContent(string $content): string
    {
        $lines        = explode("\n", $content);
        $result       = [];
        $h1Skipped    = false;

        foreach ($lines as $line) {
            // Sauter uniquement le premier H1 (titre du document)
            if (!$h1Skipped && preg_match('/^# /', ltrim($line))) {
                $h1Skipped = true;
                continue;
            }
            $result[] = $line;
        }

        return trim(implode("\n", $result));
    }

    private function buildToolResultsContent(string $message, array $toolResults, string $role): string
    {
        $parts = [
            "Question : {$message}",
            "Rôle : {$role}",
            "Résultats des outils :",
        ];

        foreach ($toolResults as $r) {
            $parts[] = "- Outil : {$r['tool']}";
            $parts[] = isset($r['error'])
                ? "  Erreur : {$r['error']}"
                : "  Résultat : " . json_encode($r['result'], JSON_UNESCAPED_UNICODE);
        }

        $parts[] = "\nFormule une réponse claire et concise en français. N'invente aucune donnée.";

        return implode("\n", $parts);
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    private function successResponse(string $response, array $sources, array $toolCalls): array
    {
        return [
            'response'        => $response,
            'sources'         => $sources,
            'tool_calls'      => $toolCalls,
            'error'           => null,
            'groq_available'  => true,
        ];
    }

    private function errorResponse(string $message, string $error): array
    {
        return [
            'response'        => $message,
            'sources'         => [],
            'tool_calls'      => [],
            'error'           => $error,
            'groq_available'  => $this->groq->isConfigured(),
        ];
    }

    private function unavailableMessage(): string
    {
        return "L'assistant IA est actuellement indisponible. Vérifiez que GROK_API_KEY est configurée dans votre fichier .env.";
    }

    private function buildSystemInstruction(): string
    {
        return <<<SYSTEM
Tu es l'assistant officiel d'AquaSecure pour le module Projets et Financements.

Tu réponds en français aux questions sur les projets, le financement participatif, les budgets, les demandes, les paiements simulés et les contributions.

Tu utilises les outils métier autorisés pour obtenir des données à jour. Tu ne devines jamais les montants, statuts ou dates.

Tu respectes strictement les autorisations du backend. Un citoyen ne voit pas les données manager.

Tu n'as aucune capacité de modification.

Si une information n'est pas disponible, tu l'indiques clairement.

Tu restes dans le périmètre Projets et Financements uniquement.
SYSTEM;
    }
}
