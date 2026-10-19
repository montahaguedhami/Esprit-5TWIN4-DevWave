<?php

namespace App\Http\Controllers\Citizen;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Services\Recommandation\Recommandation;
use App\Services\Recommandation\RecommandationService;
use Illuminate\Http\Request;

/**
 * Recommendations for the connected citizen.
 *
 * The ranking is computed SERVER SIDE, from the authenticated session only.
 * The citizen is never asked for an identifier, so there is no way to fetch
 * someone else's profile or someone else's recommendations.
 *
 * V1 note: the ranking comes from a deterministic rules engine
 * (RecommandationService), not from a machine-learning model. A future V2 can
 * replace the service behind this controller without touching its routes.
 */
class RecommandationController extends Controller
{
    public function __construct(
        private readonly RecommandationService $moteur,
    ) {
    }

    /**
     * The HTML screen: "Recommandés pour vous".
     */
    public function index(Request $request)
    {
        abort_unless($this->estCitoyen($request), 403, 'Accès réservé aux citoyens.');

        $resultat = $this->moteur->recommander($request->user(), 6);

        return view('citizen.recommandations.index', [
            'recommandations' => $resultat['items'],
            'repli'           => $resultat['repli'],
            'nbCandidats'     => $resultat['candidats'],
        ]);
    }

    /**
     * The JSON endpoint, for the section embedded in the projects screen.
     *
     * `limite` is validated and BOUNDED: a caller cannot ask for the whole
     * catalogue and turn the endpoint into a load tool.
     */
    public function api(Request $request)
    {
        abort_unless($this->estCitoyen($request), 403, 'Accès réservé aux citoyens.');

        $valide = $request->validate([
            'limite' => ['sometimes', 'integer', 'min:1', 'max:20'],
        ]);

        $resultat = $this->moteur->recommander(
            $request->user(),
            (int) ($valide['limite'] ?? 6)
        );

        return response()->json([
            'repli'       => $resultat['repli'],
            'candidats'   => $resultat['candidats'],
            'message'     => $resultat['repli']
                ? 'Aucun historique pour l\'instant : voici une sélection de projets à découvrir.'
                : null,
            'donnees'     => $resultat['items']->map(fn (Recommandation $r): array => [
                'projet_id'     => $r->projetId,
                'nom'           => $r->nom,
                'statut'        => $r->projet->statut,
                'progression'   => (int) $r->projet->progression,
                'budget'        => (float) $r->projet->budget,
                'budget_disponible' => (float) $r->projet->budget_disponible,
                'zones'         => $r->projet->zones->pluck('nom')->filter()->values(),
                'score'         => $r->score,
                'financable'    => $r->financable,
                'repli'         => $r->repli,
                'raisons'       => $r->raisons,
                'url'           => route('citizen.projets.show', $r->projet),
            ])->values(),
        ]);
    }

    private function estCitoyen(Request $request): bool
    {
        $user = $request->user();

        if (! $user instanceof \App\Models\User) {
            return false;
        }

        return $user->hasAnyRole([Role::CITOYEN]);
    }
}
