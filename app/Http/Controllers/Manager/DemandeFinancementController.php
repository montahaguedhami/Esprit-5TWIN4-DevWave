<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Http\Requests\RefuseDemandeFinancementRequest;
use App\Models\DemandeFinancement;
use App\Models\Projet;
use App\Models\Role;
use App\Services\FinancementException;
use App\Services\FinancementService;
use Illuminate\Http\Request;

/**
 * Manager side: review citizen funding requests and rule on them.
 */
class DemandeFinancementController extends Controller
{
    public function __construct(
        private readonly FinancementService $financements,
    ) {
    }

    /**
     * The `role:manager` middleware already guards every route below; this is a
     * second, independent check on the resolved user.
     *
     * It uses hasAnyRole() rather than Role::sessionMatches(), which reads the
     * `user.role` session key written at login and stays false for a user
     * resolved by the guard.
     */
    private function estManager(Request $request): bool
    {
        $user = $request->user();

        return $user instanceof \App\Models\User
            && $user->hasAnyRole([Role::MANAGER, Role::ADMINISTRATEUR]);
    }

    /**
     * List every request, filterable by status and by project.
     */
    public function index(Request $request)
    {
        abort_unless($this->estManager($request), 403, 'Accès refusé : vous n\'avez pas le rôle requis.');

        $filtreStatut = (string) $request->query('statut', '');

        if (! in_array($filtreStatut, DemandeFinancement::STATUTS, true)) {
            $filtreStatut = '';
        }

        $query = DemandeFinancement::query()->with(['projet', 'citoyen']);

        if ($filtreStatut !== '') {
            $query->where('statut', $filtreStatut);
        }

        if ($request->filled('projet_id')) {
            $query->where('projet_id', $request->integer('projet_id'));
        }

        $demandes = $query->latest()->paginate(15)->withQueryString();

        $projets = Projet::query()->orderBy('nom')->get();

        $compteurs = DemandeFinancement::query()
            ->selectRaw('statut, COUNT(*) as total')
            ->groupBy('statut')
            ->pluck('total', 'statut');

        return view('manager.demandes.index', compact('demandes', 'projets', 'filtreStatut', 'compteurs'));
    }

    /**
     * Full detail of a request.
     */
    public function show(Request $request, DemandeFinancement $demande)
    {
        abort_unless($this->estManager($request), 403, 'Accès refusé : vous n\'avez pas le rôle requis.');

        $demande->load(['projet', 'citoyen', 'decideur', 'paiements']);

        return view('manager.demandes.show', compact('demande'));
    }

    /**
     * Approve a pending request. Refuses cleanly if it was already ruled on.
     */
    public function accepter(Request $request, DemandeFinancement $demande)
    {
        abort_unless($this->estManager($request), 403, 'Accès refusé : vous n\'avez pas le rôle requis.');

        try {
            $this->financements->accepter($demande, $request->user());
        } catch (FinancementException $e) {
            return redirect()
                ->route('manager.demandes.show', $demande)
                ->with('error', $e->getMessage());
        }

        return redirect()
            ->route('manager.demandes.show', $demande)
            ->with('success', 'La demande de financement a été acceptée. Le citoyen peut maintenant procéder au paiement.');
    }

    /**
     * Refuse a pending request, with a mandatory reason.
     */
    public function refuser(RefuseDemandeFinancementRequest $request, DemandeFinancement $demande)
    {
        abort_unless($this->estManager($request), 403, 'Accès refusé : vous n\'avez pas le rôle requis.');

        try {
            $this->financements->refuser(
                $demande,
                $request->user(),
                (string) $request->input('motif_refus'),
            );
        } catch (FinancementException $e) {
            return redirect()
                ->route('manager.demandes.show', $demande)
                ->with('error', $e->getMessage());
        }

        return redirect()
            ->route('manager.demandes.show', $demande)
            ->with('success', 'La demande de financement a été refusée.');
    }
}
