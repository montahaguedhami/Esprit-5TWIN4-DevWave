<?php

namespace App\Http\Controllers\Citizen;

use App\Http\Controllers\Controller;
use App\Models\Favori;
use App\Models\Projet;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Bookmarks — the citizen's EXPLICIT interest signal.
 *
 * Everything below is scoped to the authenticated user. A citizen never
 * supplies their own identifier: the owner always comes from the session, so
 * there is no way to read or edit someone else's favourites by tampering with
 * the URL or the payload.
 */
class FavoriController extends Controller
{
    /**
     * Paginated list of the connected citizen's favourites.
     */
    public function index(Request $request)
    {
        abort_unless($this->estCitoyen($request), 403, 'Accès réservé aux citoyens.');

        $favoris = $request->user()
            ->projetsFavoris()
            // One query for the whole page instead of one per card.
            ->with('zones:id,nom')
            ->paginate(12)
            ->withQueryString();

        return view('citizen.favoris.index', compact('favoris'));
    }

    /**
     * Save a project. Repeat calls are harmless: the unique index on
     * (user_id, projet_id) makes the second insert fail, so the row is
     * looked up instead of being duplicated.
     *
     * The response is returned in the shape the caller asked for (JSON for an
     * AJAX toggle, redirect for a classic form), so the same action serves
     * both the card button and the detail page.
     */
    public function store(Request $request, Projet $projet)
    {
        abort_unless($this->estCitoyen($request), 403, 'Accès réservé aux citoyens.');

        $this->verifierProjetAccessible($projet);

        // The owner is the authenticated user. `projet_id` comes from the
        // route; nothing else is read from the request.
        Favori::firstOrCreate([
            'user_id'   => $request->user()->id,
            'projet_id' => $projet->id,
        ]);

        return $this->reponse($request, $projet, true, 'Projet ajouté à vos favoris.');
    }

    /**
     * Remove a project from the favourites.
     *
     * Deleting someone else's row is impossible: the WHERE clause carries both
     * the projet AND the authenticated owner, so a foreign id simply matches
     * nothing.
     */
    public function destroy(Request $request, Projet $projet)
    {
        abort_unless($this->estCitoyen($request), 403, 'Accès réservé aux citoyens.');

        Favori::query()
            ->where('user_id', $request->user()->id)
            ->where('projet_id', $projet->id)
            ->delete();

        return $this->reponse($request, $projet, false, 'Projet retiré de vos favoris.');
    }

    /**
     * Toggle, used by the card button which does not know the current state.
     *
     * The state is decided inside a transaction, so two simultaneous requests
     * cannot both flip the same row in the same direction.
     */
    public function basculer(Request $request, Projet $projet)
    {
        abort_unless($this->estCitoyen($request), 403, 'Accès réservé aux citoyens.');

        $this->verifierProjetAccessible($projet);

        $userId = $request->user()->id;

        $estFavori = DB::transaction(function () use ($userId, $projet): bool {
            $existant = Favori::query()
                ->where('user_id', $userId)
                ->where('projet_id', $projet->id)
                ->lockForUpdate()
                ->first();

            if ($existant instanceof Favori) {
                $existant->delete();

                return false;
            }

            // firstOrCreate re-checks the unique index if a concurrent request
            // inserted the row between our SELECT and this INSERT.
            Favori::firstOrCreate([
                'user_id'   => $userId,
                'projet_id' => $projet->id,
            ]);

            return true;
        });

        return $this->reponse(
            $request,
            $projet,
            $estFavori,
            $estFavori ? 'Projet ajouté à vos favoris.' : 'Projet retiré de vos favoris.'
        );
    }

    /* ── Helpers ──────────────────────────────────────────────────────────── */

    private function estCitoyen(Request $request): bool
    {
        $user = $request->user();

        if (! $user instanceof \App\Models\User) {
            return false;
        }

        return $user->hasAnyRole([Role::CITOYEN]);
    }

    /**
     * A project the citizen may bookmark must still be part of what they can
     * actually see on the projects screen.
     */
    private function verifierProjetAccessible(Projet $projet): void
    {
        abort_if(
            ! in_array($projet->statut, \App\Services\Recommandation\RecommandationService::STATUTS_DECOUVERTES, true),
            422,
            'Ce projet n\'est plus accessible.'
        );
    }

    /**
     * Answer in the format the caller used, without ever leaking a foreign
     * identifier.
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    private function reponse(Request $request, Projet $projet, bool $estFavori, string $message)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'succes'     => true,
                'favori'     => $estFavori,
                'projet_id'  => $projet->id,
                'message'    => $message,
                // The count shown on the card is an aggregate only; it never
                // reveals who else saved the project.
                'total'      => $projet->favoris()->count(),
            ]);
        }

        return back()->with('success', $message);
    }
}
