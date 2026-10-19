<?php

namespace App\Http\Controllers\Citizen;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDemandeFinancementRequest;
use App\Models\DemandeFinancement;
use App\Models\Paiement;
use App\Models\Projet;
use App\Models\Role;
use App\Services\FinancementException;
use App\Services\FinancementService;
use App\Services\Payments\SimulationResult;
use App\Services\Payments\SimulatedPaymentProvider;
use Illuminate\Http\Request;

/**
 * Citizen side of the funding flow.
 *
 * The payment here is a LOCAL simulation: no external gateway, no HTTP call,
 * no bank, no card details and no real money. Every step below re-checks the
 * role, the ownership of the request and its status before doing anything.
 */
class DemandeFinancementController extends Controller
{
    public function __construct(
        private readonly FinancementService $financements,
        private readonly SimulatedPaymentProvider $simulation,
    ) {
    }

    /* ── Historique ──────────────────────────────────────────────────────── */

    /**
     * "Mes demandes de financement", newest first.
     */
    public function index(Request $request)
    {
        abort_unless($this->estCitoyen($request), 403, 'Accès refusé : vous n\'avez pas le rôle requis.');

        $demandes = DemandeFinancement::query()
            ->where('user_id', $request->user()->id)
            ->with(['projet', 'paiements'])
            ->latest()
            ->paginate(10);

        return view('citizen.demandes.index', compact('demandes'));
    }

    /* ── Étapes 1 et 2 : sélection puis validation ───────────────────────── */

    /**
     * Funding form for one project.
     *
     * The project is passed as ?projet_id= in the query string (not as a route
     * segment) so the URL stays /citizen/demandes/create and never exposes a
     * numeric ID in a path that a route binder would fail to resolve when the
     * parameter is absent.
     */
    public function create(Request $request)
    {
        abort_unless($this->estCitoyen($request), 403, 'Accès refusé : vous n\'avez pas le rôle requis.');

        $projet = Projet::query()->findOrFail($request->integer('projet_id'));
        $projet->load('financements');

        return view('citizen.demandes.create', [
            'projet'     => $projet,
            'minimum'    => StoreDemandeFinancementRequest::MONTANT_MINIMUM,
            'eligible'   => $projet->estEligibleAuFinancementCitoyen(),
            'dejaSoumis' => $this->aDejaDemandeEnCours($projet),
        ]);
    }

    /**
     * Store the request. The citizen is the authenticated user, always.
     */
    public function store(StoreDemandeFinancementRequest $request)
    {
        $projet = Projet::query()->findOrFail($request->integer('projet_id'));

        $demande = $this->financements->soumettre(
            $projet,
            $request->user(),
            (float) $request->input('montant'),
        );

        return redirect()
            ->route('citizen.demandes.show', $demande)
            ->with('success', 'Votre demande de financement a été envoyée. Elle sera examinée par le manager.');
    }

    /* ── Détail ──────────────────────────────────────────────────────────── */

    /**
     * Detail of one of MY requests. Ownership is enforced server side.
     */
    public function show(Request $request, DemandeFinancement $demande)
    {
        $this->verifierProprietaire($request, $demande);

        $demande->load(['projet', 'decideur', 'paiements']);

        return view('citizen.demandes.show', compact('demande'));
    }

    /* ── Étape 3 : récapitulatif ─────────────────────────────────────────── */

    /**
     * Recap of what is about to be simulated, with the mandatory warning and
     * the confirm / cancel buttons. Nothing has been recorded yet.
     */
    public function recap(Request $request, DemandeFinancement $demande)
    {
        $this->verifierProprietaire($request, $demande);

        if (! $demande->estPayable()) {
            return redirect()
                ->route('citizen.demandes.show', $demande)
                ->with('error', $this->messageNonPayable($demande));
        }

        $demande->load('projet');

        return view('citizen.demandes.recap', [
            'demande'   => $demande,
            'scenarios' => $this->simulation->permetChoisirScenario() ? SimulationResult::SCENARIOS : [],
            'libelles'  => SimulationResult::LIBELLES,
        ]);
    }

    /* ── Étapes 4 et 5 : confirmation puis résultat ──────────────────────── */

    /**
     * Run the LOCAL simulation and record its outcome.
     *
     * The amount is taken from the database, never from this request. A repeat
     * submission reuses the pending attempt instead of counting twice.
     */
    public function confirmer(Request $request, DemandeFinancement $demande)
    {
        $this->verifierProprietaire($request, $demande);

        $scenario = $this->scenarioAutorise($request);

        try {
            $paiement = $this->financements->initierPaiement($demande);

            $resultat = $this->simulation->simuler($paiement, $scenario);

            $paiement = $this->financements->appliquerSimulation($paiement, $resultat);
        } catch (FinancementException $e) {
            return redirect()
                ->route('citizen.demandes.show', $demande)
                ->with('error', $e->getMessage());
        }

        return redirect()
            ->route('citizen.demandes.paiement.resultat', ['paiement' => $paiement])
            ->with('simulation_reussie', $paiement->estConfirme());
    }

    /**
     * Cancel before anything is simulated. The request keeps its status and no
     * amount is ever moved.
     */
    public function annuler(Request $request, DemandeFinancement $demande)
    {
        $this->verifierProprietaire($request, $demande);

        return redirect()
            ->route('citizen.demandes.show', $demande)
            ->with('success', 'La simulation a été annulée. Aucun montant n\'a été comptabilisé.');
    }

    /**
     * Outcome screen for one attempt, plus its receipt.
     */
    public function resultat(Request $request, Paiement $paiement)
    {
        $this->verifierPaiementProprietaire($request, $paiement);

        $paiement->load(['demande.projet']);

        return view('citizen.demandes.resultat', [
            'paiement'    => $paiement,
            'demande'     => $paiement->demande,
            'reussie'     => $paiement->estConfirme(),
        ]);
    }

    /**
     * The receipt, always stamped as a simulation.
     */
    public function recu(Request $request, Paiement $paiement)
    {
        $this->verifierPaiementProprietaire($request, $paiement);

        $paiement->load(['demande.projet', 'citoyen']);

        return view('citizen.demandes.recu', compact('paiement'));
    }

    /* ── Contrôles d'accès ───────────────────────────────────────────────── */

    /**
     * The `role:citoyen` middleware already guards every route below; this is a
     * second, independent check on the resolved user.
     *
     * It deliberately uses hasAnyRole() rather than Role::sessionMatches():
     * the latter reads the `user.role` session key written at login and stays
     * false for a user resolved by the guard, which would make the check
     * useless rather than protective.
     */
    private function estCitoyen(Request $request): bool
    {
        $user = $request->user();

        return $user instanceof \App\Models\User && $user->hasAnyRole([Role::CITOYEN]);
    }

    private function verifierProprietaire(Request $request, DemandeFinancement $demande): void
    {
        abort_unless($this->estCitoyen($request), 403, 'Accès refusé : vous n\'avez pas le rôle requis.');

        if (! $request->user() || (int) $demande->user_id !== (int) $request->user()->id) {
            abort(403, 'Vous ne pouvez consulter que vos propres demandes de financement.');
        }
    }

    private function verifierPaiementProprietaire(Request $request, Paiement $paiement): void
    {
        abort_unless($this->estCitoyen($request), 403, 'Accès refusé : vous n\'avez pas le rôle requis.');

        if (! $request->user() || (int) $paiement->user_id !== (int) $request->user()->id) {
            abort(403, 'Vous ne pouvez consulter que vos propres opérations de paiement.');
        }
    }

    /**
     * Only a scenario the server actually allows may reach the provider, and
     * only when the development demo switch is on.
     */
    private function scenarioAutorise(Request $request): ?string
    {
        if (! $this->simulation->permetChoisirScenario()) {
            return null;
        }

        $scenario = (string) $request->input('scenario', '');

        return in_array($scenario, SimulationResult::SCENARIOS, true) ? $scenario : null;
    }

    private function messageNonPayable(DemandeFinancement $demande): string
    {
        if ($demande->estPayee()) {
            return 'Cette demande a déjà été payée.';
        }

        if ($demande->estRefusee()) {
            return 'Une demande refusée ne peut pas être payée.';
        }

        if ($demande->estEnAttente()) {
            return 'Cette demande n\'a pas encore été acceptée par le manager.';
        }

        return 'Cette demande n\'est pas payable pour le moment.';
    }

    private function aDejaDemandeEnCours(Projet $projet): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        return DemandeFinancement::query()
            ->where('projet_id', $projet->id)
            ->where('user_id', $user->id)
            ->whereIn('statut', [DemandeFinancement::EN_ATTENTE, DemandeFinancement::ACCEPTEE])
            ->exists();
    }
}
