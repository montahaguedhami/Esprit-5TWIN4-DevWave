<?php

namespace App\Http\Controllers\Citizen;

use App\Http\Controllers\Controller;
use App\Models\Financement;
use Illuminate\Http\Request;

/**
 * Portefeuille citoyen : « Mon portefeuille et mon impact ».
 *
 * Lecture seule, strictement limitée aux financements enregistrés au nom de
 * l'utilisateur connecté. Aucune écriture, aucun appel à FinancementService,
 * aucun impact sur la logique de paiement ou d'approbation existante.
 */
class PortefeuilleController extends Controller
{
    /**
     * Tableau de bord du portefeuille + historique des contributions.
     *
     * Les indicateurs sont calculés sur TOUTES les contributions du
     * citoyen (agrégations SQL), jamais sur la page paginée affichée.
     */
    public function index(Request $request)
    {
        abort_unless($this->estCitoyen($request), 403, 'Accès réservé aux citoyens.');

        // Le propriétaire est toujours l'utilisateur authentifié :
        // aucun identifiant n'est lu depuis l'URL ni la query string.
        $userId = (int) $request->user()->id;

        // 1) Total de mes contributions — somme des financements réellement
        //    enregistrés. Seule la confirmation citoyen crée un Financement :
        //    une demande en attente, refusée, ou même acceptée mais non
        //    confirmée (simple réservation), n'en crée aucun.
        $totalContributions = (float) Financement::query()
            ->where('user_id', $userId)
            ->sum('montant');

        // 2) Projets soutenus — projets DISTINCTS (plusieurs contributions
        //    au même projet ne comptent qu'une seule fois).
        $projetsSoutenus = (int) Financement::query()
            ->where('user_id', $userId)
            ->distinct()
            ->count('projet_id');

        // 3) Projets terminés soutenus — parmi les projets distincts
        //    soutenus, ceux dont le statut réel est « termine ».
        $projetsTerminesSoutenus = (int) Financement::query()
            ->where('user_id', $userId)
            ->whereHas('projet', fn ($q) => $q->where('statut', 'termine'))
            ->distinct()
            ->count('projet_id');

        // Historique paginé, ordonné de la contribution la plus récente à la
        // plus ancienne. `with('projet')` évite le N+1.
        $contributions = Financement::query()
            ->where('user_id', $userId)
            ->with('projet')
            ->orderByDesc('date_financement')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('citizen.portefeuille', [
            'totalContributions'      => $totalContributions,
            'projetsSoutenus'         => $projetsSoutenus,
            'projetsTerminesSoutenus' => $projetsTerminesSoutenus,
            'contributions'           => $contributions,
        ]);
    }

    /**
     * Garde défensive : le middleware `role:citoyen` fait déjà le travail,
     * mais on vérifie avec le mécanisme réel des rôles (hasAnyRole).
     */
    private function estCitoyen(Request $request): bool
    {
        $user = $request->user();

        if (! $user) {
            return false;
        }

        if (! method_exists($user, 'hasAnyRole')) {
            return false;
        }

        return (bool) $user->hasAnyRole(['citoyen']);
    }
}
