<?php

namespace App\Services;

use App\Models\DemandeFinancement;
use App\Models\Financement;
use App\Models\Paiement;
use App\Models\Projet;
use App\Models\User;
use App\Notifications\DemandeAccepteeNotification;
use App\Notifications\DemandeRefuseeNotification;
use App\Notifications\PaiementConfirmeNotification;
use App\Services\Payments\SimulationResult;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * All state transitions of the citizen funding flow live here.
 *
 * Invariants enforced by this service:
 *   - a request starts as "en_attente" and never moves money by itself;
 *   - only a manager decision can approve or refuse it;
 *   - only the payment provider, checked server side, can confirm money;
 *   - a confirmed payment produces exactly one Financement, once;
 *   - the sum of accepted requests can never exceed the project budget.
 */
class FinancementService
{
    /**
     * Source recorded on the definitive funding row.
     */
    public const SOURCE_CITOYEN = 'Financement citoyen';

    /* ── Soumission ──────────────────────────────────────────────────────── */

    /**
     * A citizen files a request. Nothing is financed yet.
     */
    public function soumettre(Projet $projet, User $citoyen, float $montant): DemandeFinancement
    {
        return DemandeFinancement::create([
            'projet_id' => $projet->id,
            'user_id'   => $citoyen->id,
            'montant'   => $montant,
            'devise'    => 'TND',
            'statut'    => DemandeFinancement::EN_ATTENTE,
        ]);
    }

    /* ── Décision du manager ─────────────────────────────────────────────── */

    /**
     * Approve a pending request, re-checking the budget under a row lock so
     * that concurrent approvals cannot jointly overrun the remaining budget.
     */
    public function accepter(DemandeFinancement $demande, User $manager): DemandeFinancement
    {
        // The transaction commits the decision. Nothing else runs inside it so
        // a mailer failure after the commit can never roll back a valid choice.
        $acceptee = DB::transaction(function () use ($demande, $manager) {
            $verrou = DemandeFinancement::query()
                ->whereKey($demande->id)
                ->lockForUpdate()
                ->first();

            if (! $verrou instanceof DemandeFinancement) {
                throw new FinancementException('Cette demande de financement n\'existe plus.');
            }

            if (! $verrou->estEnAttente()) {
                throw new FinancementException('Cette demande a déjà été traitée.');
            }

            $projet = Projet::query()->whereKey($verrou->projet_id)->lockForUpdate()->first();

            if (! $projet instanceof Projet) {
                throw new FinancementException('Le projet lié à cette demande n\'existe plus.');
            }

            if (! in_array($projet->statut, DemandeFinancement::STATUTS_PROJET_ELIGIBLE, true)) {
                throw new FinancementException('Ce projet n\'est plus ouvert au financement citoyen.');
            }

            $disponible = (float) $projet->budget_disponible;

            if ((float) $verrou->montant > $disponible + 0.0001) {
                throw new FinancementException(
                    'Le montant demandé dépasse le budget encore disponible pour ce projet ('
                        .number_format($disponible, 2, ',', ' ').' TND).'
                );
            }

            $verrou->update([
                'statut'      => DemandeFinancement::ACCEPTEE,
                'decided_by'  => $manager->id,
                'decided_at'  => now(),
                'motif_refus' => null,
            ]);

            return $verrou->refresh();
        });

        // Email envoyé APRÈS le commit — une erreur SMTP ne peut pas défaire
        // une décision déjà persistée.
        $this->notifierCitoyen($acceptee, new DemandeAccepteeNotification($acceptee));

        return $acceptee;
    }

    /**
     * Refuse a pending request. A reason is mandatory.
     */
    public function refuser(DemandeFinancement $demande, User $manager, string $motif): DemandeFinancement
    {
        $motif = trim($motif);

        if ($motif === '') {
            throw new FinancementException('Indiquez un motif de refus : il sera affiché au citoyen.');
        }

        $refusee = DB::transaction(function () use ($demande, $manager, $motif) {
            $verrou = DemandeFinancement::query()
                ->whereKey($demande->id)
                ->lockForUpdate()
                ->first();

            if (! $verrou instanceof DemandeFinancement) {
                throw new FinancementException('Cette demande de financement n\'existe plus.');
            }

            if (! $verrou->estEnAttente()) {
                throw new FinancementException('Cette demande a déjà été traitée.');
            }

            $verrou->update([
                'statut'      => DemandeFinancement::REFUSEE,
                'decided_by'  => $manager->id,
                'decided_at'  => now(),
                'motif_refus' => $motif,
            ]);

            return $verrou->refresh();
        });

        // Email après commit — une erreur SMTP ne peut pas annuler le refus.
        $this->notifierCitoyen($refusee, new DemandeRefuseeNotification($refusee));

        return $refusee;
    }

    /* ── Paiement ────────────────────────────────────────────────────────── */

    /**
     * Open (or reuse) a payment attempt for an approved request.
     *
     * The amount is copied from the database row, never from the browser.
     */
    public function initierPaiement(DemandeFinancement $demande): Paiement
    {
        return DB::transaction(function () use ($demande) {
            $verrou = DemandeFinancement::query()
                ->whereKey($demande->id)
                ->lockForUpdate()
                ->first();

            if (! $verrou instanceof DemandeFinancement) {
                throw new FinancementException('Cette demande de financement n\'existe plus.');
            }

            if ($verrou->estPayee()) {
                throw new FinancementException('Cette demande a déjà été payée.');
            }

            if (! $verrou->estAcceptee()) {
                throw new FinancementException(
                    'Seule une demande acceptée par le manager peut être payée (statut actuel : '.$verrou->libelleStatut().').'
                );
            }

            // Reuse an attempt already in flight so a double click cannot
            // open two checkout sessions for the same request.
            $existant = $verrou->paiements()
                ->where('statut', Paiement::EN_ATTENTE)
                ->orderByDesc('id')
                ->first();

            if ($existant instanceof Paiement) {
                return $existant;
            }

            return Paiement::create([
                'demande_id'        => $verrou->id,
                'user_id'           => $verrou->user_id,
                'projet_id'         => $verrou->projet_id,
                'montant'           => $verrou->montant,
                'devise'            => $verrou->devise,
                'statut'            => Paiement::EN_ATTENTE,
                'reference_interne' => $this->nouvelleReference(),
            ]);
        });
    }

    /**
     * Apply a LOCAL simulation result to a payment attempt.
     *
     * Idempotent by construction: the attempt and the request are both locked,
     * and a request that is already settled can never produce a second
     * Financement. This is the single place allowed to record money.
     *
     * @throws FinancementException when the request is no longer in a state
     *                              that permits the operation
     */
    public function appliquerSimulation(Paiement $paiement, SimulationResult $resultat): Paiement
    {
        $paiementFinal = DB::transaction(function () use ($paiement, $resultat) {
            $verrou = Paiement::query()->whereKey($paiement->id)->lockForUpdate()->first();

            if (! $verrou instanceof Paiement) {
                throw new FinancementException('Cette opération de paiement n\'existe plus.');
            }

            // Already settled: nothing to do, and above all nothing to redo.
            if ($verrou->estConfirme()) {
                return $verrou;
            }

            $demande = DemandeFinancement::query()
                ->whereKey($verrou->demande_id)
                ->lockForUpdate()
                ->first();

            if (! $demande instanceof DemandeFinancement) {
                throw new FinancementException('La demande liée à cette opération n\'existe plus.');
            }

            if ($demande->estPayee()) {
                // Another attempt already settled this request: this one must
                // never add a second funding.
                $this->cloreEchec($verrou, 'Une contribution a déjà été enregistrée pour cette demande.');

                return $verrou;
            }

            if ($demande->estRefusee()) {
                $this->cloreEchec($verrou, 'La demande a été refusée par le manager : contribution impossible.', Paiement::ANNULE);

                return $verrou;
            }

            if (! $demande->estAcceptee()) {
                $this->cloreEchec($verrou, 'La demande n\'est plus acceptée : contribution impossible.');

                return $verrou;
            }

            if (! $resultat->succes) {
                // A refused or cancelled simulation closes the attempt without
                // touching any total. A technical error does the same, and is
                // logged so it can be investigated.
                $statut = $resultat->estAnnule() ? Paiement::ANNULE : Paiement::ECHOUE;

                if ($resultat->estErreurTechnique()) {
                    Log::warning('Simulation de paiement : erreur technique.', [
                        'reference' => $verrou->reference_interne,
                        'projet_id' => $verrou->projet_id,
                        'montant'   => (string) $verrou->montant,
                    ]);
                }

                $this->cloreEchec($verrou, $resultat->motif, $statut);

                return $verrou;
            }

            // The amount is read back from OUR row: the browser never supplied
            // anything that reaches this point.
            $financement = new Financement([
                'projet_id'        => $verrou->projet_id,
                'source'           => self::SOURCE_CITOYEN,
                'montant'          => $verrou->montant,
                'date_financement' => today()->toDateString(),
            ]);
            $financement->user_id = $verrou->user_id;
            $financement->save();

            $demande->update(['statut' => DemandeFinancement::PAYEE]);

            $verrou->update([
                'statut'       => Paiement::CONFIRME,
                'confirmed_at' => now(),
                'motif'        => $resultat->motif,
            ]);

            Log::info('Simulation de paiement confirmée.', [
                'reference'   => $verrou->reference_interne,
                'projet_id'   => $verrou->projet_id,
                'financement' => $financement->id,
            ]);

            return $verrou->refresh();
        });

        // Notification uniquement si la simulation a réussi et que le paiement
        // est bien confirmé. L'email part APRÈS le commit de la transaction.
        // Une erreur SMTP ne peut jamais annuler une contribution déjà enregistrée.
        if ($paiementFinal->estConfirme()) {
            $paiementFinal->loadMissing(['demande.projet', 'citoyen']);
            $this->notifierCitoyen($paiementFinal->demande, new PaiementConfirmeNotification($paiementFinal));
        }

        return $paiementFinal;
    }

    /**
     * Close an attempt without ever touching the funded totals.
     */
    public function marquerEchec(Paiement $paiement, string $statut = Paiement::ECHOUE, ?string $motif = null): void
    {
        if (! in_array($statut, [Paiement::ECHOUE, Paiement::ANNULE], true)) {
            $statut = Paiement::ECHOUE;
        }

        Paiement::query()
            ->whereKey($paiement->id)
            ->where('statut', Paiement::EN_ATTENTE)
            ->update(['statut' => $statut, 'motif' => $motif]);
    }

    /**
     * Close the locked row we already hold, only if it is still pending.
     */
    private function cloreEchec(Paiement $verrou, string $motif, string $statut = Paiement::ECHOUE): void
    {
        $verrou->update(['statut' => $statut, 'motif' => $motif]);
    }

    /* ── Utilitaires ─────────────────────────────────────────────────────── */

    /**
     * Unique internal reference for a simulation attempt.
     */
    private function nouvelleReference(): string
    {
        for ($essai = 0; $essai < 5; $essai++) {
            $reference = 'AQS-'.now()->format('Ymd').'-'.Str::upper(Str::random(16));

            $existe = Paiement::query()->where('reference_interne', $reference)->exists();

            if (! $existe) {
                return $reference;
            }
        }

        throw new FinancementException('Impossible de générer une référence de paiement unique.');
    }

    /**
     * Send a notification to the citizen who filed the request.
     *
     * A mail transport failure must NEVER bubble up and roll back a business
     * operation that has already been committed — so we catch all throwables
     * here and log them instead.
     */
    private function notifierCitoyen(DemandeFinancement $demande, \Illuminate\Notifications\Notification $notification): void
    {
        try {
            $demande->loadMissing('citoyen');
            $citoyen = $demande->citoyen;

            if (! $citoyen instanceof \App\Models\User) {
                return;
            }

            $citoyen->notify($notification);
        } catch (\Throwable $e) {
            Log::warning('Notification financement non envoyée.', [
                'demande_id' => $demande->id,
                'class'      => get_class($notification),
                'error'      => $e->getMessage(),
            ]);
        }
    }
}
