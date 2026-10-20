<?php

namespace App\Services\Chatbot\Tools;

use App\Models\Projet;
use App\Services\Chatbot\ToolExecutor;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Recherche les projets accessibles (lecture seule).
 * Accessible aux citoyens et managers selon les règles d'accès réelles.
 */
class SearchProjectsTool implements ToolExecutor
{
    public function name(): string
    {
        return 'search_projects';
    }

    public function isAuthorized(mixed $user): bool
    {
        return $user !== null;
    }

    public function validateArguments(array $arguments): array
    {
        $validator = Validator::make($arguments, [
            'query' => ['sometimes', 'string', 'max:200'],
            'statut' => ['sometimes', 'string', 'max:50'],
            'eligible_only' => ['sometimes', 'boolean'],
            'limit' => ['sometimes', 'integer', 'min:1', 'max:20'],
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }

    public function execute(mixed $user, array $validatedArguments): array
    {
        $query = Projet::query();

        if (!empty($validatedArguments['query'])) {
            $query->where('nom', 'like', '%' . $validatedArguments['query'] . '%');
        }

        if (!empty($validatedArguments['statut'])) {
            $query->where('statut', $validatedArguments['statut']);
        }

        if (!empty($validatedArguments['eligible_only']) && $validatedArguments['eligible_only'] === true) {
            $query->whereIn('statut', ['planifie', 'en_cours']);
        }

        $limit = (int) ($validatedArguments['limit'] ?? 10);
        $projets = $query->latest()->limit($limit)->get();

        return [
            'projets' => $projets->map(fn (Projet $p): array => [
                'id' => (int) $p->id,
                'nom' => $p->nom,
                'description' => $p->description,
                'statut' => $p->statut,
                'budget' => (float) $p->budget,
                'progression' => (int) $p->progression,
                'budget_disponible' => (float) $p->budget_disponible,
                'eligible' => $p->estEligibleAuFinancementCitoyen(),
                'zones' => $p->zones->pluck('nom')->filter()->values()->all(),
            ])->values()->all(),
            'total' => count($projets),
        ];
    }
}
