<?php

namespace App\Services\Chatbot\Tools;

use App\Services\Chatbot\DocumentSearchService;
use App\Services\Chatbot\ToolExecutor;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Recherche les passages documentaires pertinents (RAG).
 * Retourne les références des documents réellement récupérés.
 */
class SearchDocumentationTool implements ToolExecutor
{
    public function __construct(
        private readonly DocumentSearchService $search,
    ) {
    }

    public function name(): string
    {
        return 'search_documentation';
    }

    public function isAuthorized(mixed $user): bool
    {
        return $user !== null;
    }

    public function validateArguments(array $arguments): array
    {
        $validator = Validator::make($arguments, [
            'query' => ['required', 'string', 'min:3', 'max:500'],
            'limit' => ['sometimes', 'integer', 'min:1', 'max:10'],
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }

    public function execute(mixed $user, array $validatedArguments): array
    {
        $limit = (int) ($validatedArguments['limit'] ?? 5);
        $results = $this->search->search($validatedArguments['query'], $limit);

        return [
            'query' => $validatedArguments['query'],
            'results' => array_values($results),
            'count' => count($results),
            'source' => 'docs/chatbot/ (recherche textuelle, non vectorielle)',
        ];
    }
}
