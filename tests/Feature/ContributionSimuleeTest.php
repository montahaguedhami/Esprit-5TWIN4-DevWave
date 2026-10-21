<?php

namespace Tests\Feature;

use App\Models\DemandeFinancement;
use App\Models\Financement;
use App\Models\Paiement;
use App\Models\Projet;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithRbac;
use Tests\TestCase;

/**
 * End to end behaviour of a citizen contribution.
 *
 * Nothing in this file contacts a network: the outcome comes from the local
 * SimulatedPaymentProvider, and the totals are recomputed from the recorded
 * Financement rows rather than trusted from anywhere else.
 */
class ContributionSimuleeTest extends TestCase
{
    use RefreshDatabase;
    use InteractsWithRbac;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->seedRbac();

        // Turn the demo scenario picker on, as it is in a local .env.
        config()->set('financement.scenario_choix', true);
    }

    /* ── Helpers ──────────────────────────────────────────────────────────── */

    private function projetEligible(float $budget = 20000): Projet
    {
        return Projet::factory()->create([
            'statut' => 'en_cours',
            'budget' => $budget,
        ]);
    }

    /**
     * A request the manager already approved, ready to be contributed to.
     */
    private function demandeAcceptee(Projet $projet, $citoyen, float $montant = 1000): DemandeFinancement
    {
        $manager = $this->createUserWithRole(Role::MANAGER);

        $demande = DemandeFinancement::create([
            'projet_id' => $projet->id,
            'user_id'   => $citoyen->id,
            'montant'   => $montant,
            'devise'    => 'TND',
            'statut'    => DemandeFinancement::EN_ATTENTE,
        ]);

        return app(\App\Services\FinancementService::class)->accepter($demande, $manager);
    }

    /* ── Soumission ───────────────────────────────────────────────────────── */

    public function test_un_visiteur_ne_peut_pas_soumettre_une_demande(): void
    {
        $projet = $this->projetEligible();

        $this->post('/citizen/demandes', [
            'projet_id' => $projet->id,
            'monant'    => 1000,
            'montant'   => 1000,
        ])->assertRedirect();

        $this->assertDatabaseCount('demandes_financement', 0);
    }

    public function test_un_citoyen_peut_soumettre_une_demande_valide(): void
    {
        $citoyen = $this->createUserWithRole(Role::CITOYEN);
        $projet  = $this->projetEligible();

        $this->actingAs($citoyen)->post('/citizen/demandes', [
            'projet_id' => $projet->id,
            'montant'   => 1000,
        ])->assertRedirect();

        $this->assertDatabaseHas('demandes_financement', [
            'projet_id' => $projet->id,
            'user_id'   => $citoyen->id,
            'montant'   => 1000,
            'statut'    => 'en_attente',
        ]);
    }

    public function test_un_montant_inferieur_au_minimum_est_rejete(): void
    {
        $citoyen = $this->createUserWithRole(Role::CITOYEN);
        $projet  = $this->projetEligible();

        $this->actingAs($citoyen)
            ->from('/citizen/demandes/create?projet_id='.$projet->id)
            ->post('/citizen/demandes', [
                'projet_id' => $projet->id,
                'montant'   => 999.99,
            ])
            ->assertSessionHasErrors('montant');

        $this->assertDatabaseCount('demandes_financement', 0);
    }

    public function test_un_montant_non_numerique_est_rejete(): void
    {
        $citoyen = $this->createUserWithRole(Role::CITOYEN);
        $projet  = $this->projetEligible();

        $this->actingAs($citoyen)->post('/citizen/demandes', [
            'projet_id' => $projet->id,
            'montant'   => 'mille',
        ])->assertSessionHasErrors('montant');
    }

    public function test_un_montant_superieur_au_budget_disponible_est_rejete(): void
    {
        $citoyen = $this->createUserWithRole(Role::CITOYEN);
        $projet  = $this->projetEligible(5000);

        $this->actingAs($citoyen)->post('/citizen/demandes', [
            'projet_id' => $projet->id,
            'montant'   => 6000,
        ])->assertSessionHasErrors('montant');

        $this->assertDatabaseCount('demandes_financement', 0);
    }

    public function test_un_projet_ferme_n_accepte_pas_de_contribution(): void
    {
        $citoyen = $this->createUserWithRole(Role::CITOYEN);
        $projet  = Projet::factory()->create(['statut' => 'termine', 'budget' => 50000]);

        $this->actingAs($citoyen)->post('/citizen/demandes', [
            'projet_id' => $projet->id,
            'montant'   => 1000,
        ])->assertSessionHasErrors('projet_id');

        $this->assertDatabaseCount('demandes_financement', 0);
    }

    public function test_le_citoyen_est_pris_dans_la_session_et_dans_le_payload(): void
    {
        $victime = $this->createUserWithRole(Role::CITOYEN);
        $attaquant = $this->createUserWithRole(Role::CITOYEN);
        $projet = $this->projetEligible();

        $this->actingAs($attaquant)->post('/citizen/demandes', [
            'projet_id' => $projet->id,
            'montant'   => 1000,
            'user_id'   => $victime->id,
        ])->assertRedirect();

        // The payload value must be ignored: the owner is the session user.
        $this->assertDatabaseHas('demandes_financement', [
            'projet_id' => $projet->id,
            'user_id'   => $attaquant->id,
        ]);
    }

    /* ── Une demande en attente ne déplace jamais d'argent ────────────────── */

    public function test_une_demande_en_attente_n_augmente_pas_le_total_financé(): void
    {
        $citoyen = $this->createUserWithRole(Role::CITOYEN);
        $projet  = $this->projetEligible(20000);

        $this->actingAs($citoyen)->post('/citizen/demandes', [
            'projet_id' => $projet->id,
            'montant'   => 5000,
        ]);

        $projet->refresh();

        $this->assertSame(0.0, (float) $projet->total_finance);
        $this->assertSame(20000.0, (float) $projet->budget_restant);
    }

    /* ── Contrôle d'accès ─────────────────────────────────────────────────── */

    public function test_un_citoyen_ne_peut_pas_consulter_la_demande_d_un_autre(): void
    {
        $proprietaire = $this->createUserWithRole(Role::CITOYEN);
        $autre        = $this->createUserWithRole(Role::CITOYEN);
        $projet       = $this->projetEligible();

        $demande = DemandeFinancement::create([
            'projet_id' => $projet->id,
            'user_id'   => $proprietaire->id,
            'montant'   => 1000,
            'devise'    => 'TND',
            'statut'    => DemandeFinancement::EN_ATTENTE,
        ]);

        $this->actingAs($autre)->get('/citizen/demandes/'.$demande->id)->assertForbidden();

        // And neither the recap nor the confirm endpoint.
        $this->actingAs($autre)->get('/citizen/demandes/'.$demande->id.'/recapitulatif')->assertForbidden();
        $this->actingAs($autre)->post('/citizen/demandes/'.$demande->id.'/confirmer')->assertForbidden();

        $this->assertDatabaseCount('financements', 0);
    }

    public function test_un_citoyen_ne_peut_pas_accepter_sa_propre_demande(): void
    {
        $citoyen = $this->createUserWithRole(Role::CITOYEN);
        $projet  = $this->projetEligible();

        $demande = DemandeFinancement::create([
            'projet_id' => $projet->id,
            'user_id'   => $citoyen->id,
            'montant'   => 1000,
            'devise'    => 'TND',
            'statut'    => DemandeFinancement::EN_ATTENTE,
        ]);

        // The role middleware refuses with a 403 before the route is reached:
        // an authenticated citizen is never silently bounced to the login page.
        $this->actingAs($citoyen)
            ->post('/manager/demandes/'.$demande->id.'/accepter')
            ->assertForbidden();

        $this->assertSame('en_attente', $demande->refresh()->statut);
    }

    /* ── Simulations ──────────────────────────────────────────────────────── */

    public function test_une_simulation_reussie_enregistre_le_financement_une_seule_fois(): void
    {
        $citoyen = $this->createUserWithRole(Role::CITOYEN);
        $projet  = $this->projetEligible(20000);
        $demande = $this->demandeAcceptee($projet, $citoyen, 1000);

        $this->actingAs($citoyen)->post('/citizen/demandes/'.$demande->id.'/confirmer', [
            'scenario' => 'succes',
        ]);

        $this->assertDatabaseHas('demandes_financement', [
            'id'     => $demande->id,
            'statut' => 'payee',
        ]);

        $this->assertDatabaseHas('paiements', [
            'demande_id' => $demande->id,
            'user_id'    => $citoyen->id,
            'projet_id'  => $projet->id,
            'montant'    => 1000,
            'statut'     => 'confirme',
        ]);

        // Exactly one funding, tied to the right project and citizen.
        $this->assertSame(1, Financement::count());

        $financement = Financement::first();
        $this->assertSame($projet->id, $financement->projet_id);
        $this->assertSame($citoyen->id, $financement->user_id);

        // And the total moved by exactly that amount.
        $this->assertSame(1000.0, (float) $projet->refresh()->total_finance);
    }

    public function test_une_simulation_reussie_maj_coherence_des_totaux(): void
    {
        $citoyen = $this->createUserWithRole(Role::CITOYEN);
        $projet  = $this->projetEligible(20000);
        $demande = $this->demandeAcceptee($projet, $citoyen, 5000);

        $this->assertSame(20000.0, (float) $projet->refresh()->budget_restant);

        $this->actingAs($citoyen)->post('/citizen/demandes/'.$demande->id.'/confirmer', [
            'scenario' => 'succes',
        ]);

        $projet->refresh();

        $this->assertSame(5000.0, (float) $projet->total_finance);
        $this->assertSame(15000.0, (float) $projet->budget_restant);
        $this->assertEqualsWithDelta(25.0, (float) $projet->pourcentage_finance, 0.001);
    }

    public function test_une_simulation_echouee_ne_modifie_pas_le_total_financé(): void
    {
        $citoyen = $this->createUserWithRole(Role::CITOYEN);
        $projet  = $this->projetEligible(20000);
        $demande = $this->demandeAcceptee($projet, $citoyen, 1000);

        $this->actingAs($citoyen)->post('/citizen/demandes/'.$demande->id.'/confirmer', [
            'scenario' => 'refus',
        ]);

        $this->assertSame(0, Financement::count());
        $this->assertSame(0.0, (float) $projet->refresh()->total_finance);

        $this->assertDatabaseHas('paiements', [
            'demande_id' => $demande->id,
            'statut'     => 'echoue',
        ]);

        // The request stays approved so the citizen may try again.
        $this->assertSame('acceptee', $demande->refresh()->statut);
    }

    public function test_une_annulation_ne_modifie_pas_le_total_financé(): void
    {
        $citoyen = $this->createUserWithRole(Role::CITOYEN);
        $projet  = $this->projetEligible(20000);
        $demande = $this->demandeAcceptee($projet, $citoyen, 1000);

        $this->actingAs($citoyen)->post('/citizen/demandes/'.$demande->id.'/confirmer', [
            'scenario' => 'annule',
        ]);

        $this->assertSame(0, Financement::count());
        $this->assertSame(0.0, (float) $projet->refresh()->total_finance);

        $this->assertDatabaseHas('paiements', [
            'demande_id' => $demande->id,
            'statut'     => 'annule',
        ]);
    }

    public function test_une_erreur_technique_ne_laisse_aucune_donnee_incoherente(): void
    {
        $citoyen = $this->createUserWithRole(Role::CITOYEN);
        $projet  = $this->projetEligible(20000);
        $demande = $this->demandeAcceptee($projet, $citoyen, 1000);

        $this->actingAs($citoyen)->post('/citizen/demandes/'.$demande->id.'/confirmer', [
            'scenario' => 'erreur',
        ]);

        $this->assertSame(0, Financement::count());
        $this->assertSame(0.0, (float) $projet->refresh()->total_finance);

        $paiement = Paiement::firstWhere('demande_id', $demande->id);
        $this->assertNotNull($paiement);
        $this->assertSame('echoue', $paiement->statut);
        $this->assertNotNull($paiement->motif, 'Un scénario d\'erreur doit laisser un motif traçable.');

        // The approved request is untouched, so a retry stays possible.
        $this->assertSame('acceptee', $demande->refresh()->statut);

        // No funding row was half-created.
        $this->assertSame(0, Financement::count());
    }

    public function test_une_requete_repetee_ne_comptabilise_pas_deux_fois(): void
    {
        $citoyen = $this->createUserWithRole(Role::CITOYEN);
        $projet  = $this->projetEligible(20000);
        $demande = $this->demandeAcceptee($projet, $citoyen, 1000);

        $this->actingAs($citoyen)->post('/citizen/demandes/'.$demande->id.'/confirmer', [
            'scenario' => 'succes',
        ]);

        // A double click, a refresh with resubmission, or a replayed POST.
        $this->actingAs($citoyen)->post('/citizen/demandes/'.$demande->id.'/confirmer', [
            'scenario' => 'succes',
        ]);

        $this->assertSame(1, Financement::count(), 'Une contribution ne doit jamais être comptée deux fois.');
        $this->assertSame(1, Paiement::count());
        $this->assertSame(1000.0, (float) $projet->refresh()->total_finance);
    }

    public function test_le_montant_vient_de_la_base_et_jamais_du_navigateur(): void
    {
        $citoyen = $this->createUserWithRole(Role::CITOYEN);
        $projet  = $this->projetEligible(20000);
        $demande = $this->demandeAcceptee($projet, $citoyen, 1000);

        // A forged payload trying to inflate the contribution.
        $this->actingAs($citoyen)->post('/citizen/demandes/'.$demande->id.'/confirmer', [
            'scenario' => 'succes',
            'montant'  => 19000,
            'statut'   => 'payee',
        ]);

        $this->assertSame(1, Financement::count());
        $this->assertSame(1000.0, (float) Financement::first()->montant);
        $this->assertSame(1000.0, (float) $projet->refresh()->total_finance);
    }

    public function test_un_scenario_non_autorise_est_ignore_hors_mode_demo(): void
    {
        config()->set('financement.scenario_choix', false);
        $this->app->forgetInstance(\App\Services\Payments\SimulatedPaymentProvider::class);

        $citoyen = $this->createUserWithRole(Role::CITOYEN);
        $projet  = $this->projetEligible(20000);
        $demande = $this->demandeAcceptee($projet, $citoyen, 1000);

        // The citizen asks for a failure; the server must not oblige.
        $this->actingAs($citoyen)->post('/citizen/demandes/'.$demande->id.'/confirmer', [
            'scenario' => 'refus',
        ]);

        $this->assertSame(1, Financement::count());
        $this->assertSame('payee', $demande->refresh()->statut);
    }

    public function test_l_annulation_explicite_ne_comptabilise_rien(): void
    {
        $citoyen = $this->createUserWithRole(Role::CITOYEN);
        $projet  = $this->projetEligible(20000);
        $demande = $this->demandeAcceptee($projet, $citoyen, 1000);

        $this->actingAs($citoyen)
            ->post('/citizen/demandes/'.$demande->id.'/annuler')
            ->assertRedirect();

        // Nothing is recorded, nothing moves, and the request stays usable.
        $this->assertSame(0, Paiement::count());
        $this->assertSame(0, Financement::count());
        $this->assertSame(0.0, (float) $projet->refresh()->total_finance);
        $this->assertSame('acceptee', $demande->refresh()->statut);
    }

    public function test_l_annulation_explicite_est_interdite_a_un_autre_citoyen(): void
    {
        $proprietaire = $this->createUserWithRole(Role::CITOYEN);
        $autre        = $this->createUserWithRole(Role::CITOYEN);
        $projet       = $this->projetEligible(20000);
        $demande      = $this->demandeAcceptee($projet, $proprietaire, 1000);

        $this->actingAs($autre)
            ->post('/citizen/demandes/'.$demande->id.'/annuler')
            ->assertForbidden();
    }

    /**
     * Regression guard: the list view used to call a route that no longer
     * exists (`citizen.demandes.payer`), which returned a 500 and silently
     * broke the whole contribution journey for accepted requests.
     */
    public function test_la_liste_affiche_le_lien_contribuer_sans_route_morte(): void
    {
        $citoyen = $this->createUserWithRole(Role::CITOYEN);
        $projet  = $this->projetEligible(20000);
        $demande = $this->demandeAcceptee($projet, $citoyen, 1000);

        $response = $this->actingAs($citoyen)->get('/citizen/demandes');

        $response->assertOk()
            ->assertSee('Contribuer')
            ->assertSee(route('citizen.demandes.recap', $demande), false);

        // A refused request must NOT offer to be paid.
        $refusee = DemandeFinancement::create([
            'projet_id' => $projet->id,
            'user_id'   => $citoyen->id,
            'montant'   => 1000,
            'devise'    => 'TND',
            'statut'    => DemandeFinancement::REFUSEE,
        ]);

        $this->actingAs($citoyen)
            ->get('/citizen/demandes')
            ->assertOk()
            ->assertDontSee(route('citizen.demandes.recap', $refusee), false);
    }

    /**
     * Every citizen-facing page must render for each status without hitting
     * a missing route or an undefined variable.
     */
    public function test_toutes_les_vues_citoyen_se_rendent_pour_chaque_statut(): void
    {
        $citoyen = $this->createUserWithRole(Role::CITOYEN);
        $projet  = $this->projetEligible(20000);

        foreach (DemandeFinancement::STATUTS as $statut) {
            $demande = DemandeFinancement::create([
                'projet_id' => $projet->id,
                'user_id'   => $citoyen->id,
                'montant'   => 1000,
                'devise'    => 'TND',
                'statut'    => $statut,
            ]);

            $this->actingAs($citoyen)
                ->get('/citizen/demandes/'.$demande->id)
                ->assertOk();

            $this->actingAs($citoyen)
                ->get('/citizen/demandes/'.$demande->id.'/recapitulatif')
                ->assertStatus(in_array($statut, ['acceptee'], true) ? 200 : 302);
        }

        $this->actingAs($citoyen)->get('/citizen/demandes')->assertOk();
        $this->actingAs($citoyen)->get('/citizen/demandes/create?projet_id='.$projet->id)->assertOk();
    }

    /* ── Reçu et résultat ─────────────────────────────────────────────────── */

    public function test_le_recu_affiche_la_mention_de_simulation_et_protège_le_propriétaire(): void
    {
        $proprietaire = $this->createUserWithRole(Role::CITOYEN, ['name' => 'Yassine Hamdi']);
        $autre        = $this->createUserWithRole(Role::CITOYEN);
        $projet       = $this->projetEligible(20000);
        $demande      = $this->demandeAcceptee($projet, $proprietaire, 1000);

        $this->actingAs($proprietaire)->post('/citizen/demandes/'.$demande->id.'/confirmer', [
            'scenario' => 'succes',
        ]);

        $paiement = Paiement::firstWhere('demande_id', $demande->id);

        $this->actingAs($proprietaire)
            ->get('/citizen/paiements/'.$paiement->id.'/recu')
            ->assertOk()
            ->assertSee('Simulation — aucune transaction financière réelle')
            ->assertSee($paiement->reference_interne)
            ->assertSee('1 000,00');

        // IDOR: another citizen gets a 403 on both the receipt and the result.
        $this->actingAs($autre)->get('/citizen/paiements/'.$paiement->id.'/recu')->assertForbidden();
        $this->actingAs($autre)->get('/citizen/paiements/'.$paiement->id)->assertForbidden();
    }

    public function test_le_recu_n_est_pas_accessible_a_un_visiteur(): void
    {
        $citoyen = $this->createUserWithRole(Role::CITOYEN);
        $projet  = $this->projetEligible(20000);
        $demande = $this->demandeAcceptee($projet, $citoyen, 1000);

        $this->actingAs($citoyen)->post('/citizen/demandes/'.$demande->id.'/confirmer', [
            'scenario' => 'succes',
        ]);

        $paiement = Paiement::firstWhere('demande_id', $demande->id);

        // Drop the guards so the next request really is anonymous.
        $this->app['auth']->forgetGuards();

        $this->get('/citizen/paiements/'.$paiement->id.'/recu')->assertRedirect();
    }

    /* ── Configuration ────────────────────────────────────────────────────── */

    public function test_un_driver_de_paiement_inconnu_est_refuse(): void
    {
        config()->set('financement.driver', 'stripe');
        $this->app->forgetInstance(\App\Services\Payments\SimulatedPaymentProvider::class);

        $this->expectException(\RuntimeException::class);

        $this->app->make(\App\Services\Payments\SimulatedPaymentProvider::class);
    }
}
