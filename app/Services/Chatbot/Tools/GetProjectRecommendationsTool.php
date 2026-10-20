<?php

namespace App\Services\Chatbot\Tools;

use App\Models\User;
use App\Services\Recommandation\RecommandationService;
use App\Services\Chatbot\ToolExecutor;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Recommandations de projets via le moteur V1 à règles (RecommandationService).
 * Facultatif : uniquement si le moteur V1 existe et est fonctionnel.
 */
class GetProjectRecommendationsTool implements ToolExecutor
{
    public function __construct(
        private readonly RecommandationService $recommandationService,
    ) {
    }

    public function name(): string
    {
        return 'get_project_recommendations';
    }

    public function isAuthorized(mixed $user): bool
    {
        return $user !== null && method_exists($user, 'hasAnyRole') && $user->hasAnyRole([\App\Models\Role::CITOYEN]);
    }

    public function validateArguments(array $arguments): array
    {
        $validator = Validator::make($arguments, [
            'limit' => ['sometimes', 'integer', 'min:1', 'max:20'],
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }

    public function execute(mixed $user, array $validatedArguments): array
    {
        if (! $user instanceof User) {
            return ['recommandations' => [], 'total' => 0, 'repli' => false];
        }

        $limit = (int) ($validatedArguments['limit'] ?? 6);
        $resultat = $this->recommandationService->recommander($user, $limit);

        return [
            'recommandations' => $resultat['items']->map(fn ($r): array => [
                'projet_id' => (int) $r->projetId,
                'nom' => $r->nom,
                'score' => (float) $r->score,
                'financable' => (bool) $r->financable,
                'raisons' => $r->raisons,
                'repli' => (bool) $r->repli,
                'statut' => $r->projet->statut,
                'budget' => (float) $r->projet->budget,
                'budget_disponible' => (float) $r->projet->budget_disponible,
                'zones' => $r->projet->zones->pluck('nom')->filter()->values()->all(),
            ])->values()->all(),
            'total' => (int) $resultat['candidats'],
            'repli' => (bool) $resultat['repli'],
            'note' => 'Moteur V1 déterministe à règles (pas de machine learning).',
        ];
    }
}
