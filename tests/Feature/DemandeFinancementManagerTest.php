<?php

namespace Tests\Feature;

use App\Models\DemandeFinancement;
use App\Models\Projet;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithRbac;
use Tests\TestCase;

/**
 * Manager side of the funding flow: reviewing and ruling on citizen requests.
 */
class DemandeFinancementManagerTest extends TestCase
{
    use RefreshDatabase;
    use InteractsWithRbac;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->seedRbac();
        config()->set('financement.scenario_choix', true);
    }

    private function demande(Projet $projet, $citoyen, string $statut = 'en_attente', float $montant = 1000): DemandeFinancement
    {
        return DemandeFinancement::create([
            'projet_id' => $projet->id,
            'user_id'   => $citoyen->id,
            'montant'   => $montant,
            'devise'    => 'TND',
            'statut'    => $statut,
        ]);
    }

    private function projet(float $budget = 20000): Projet
    {
        return Projet::factory()->create(['statut' => 'en_cours', 'budget' => $budget]);
    }

    /* ── Visibilité ───────────────────────────────────────────────────────── */

    public function test_un_citoyen_ne_peut_pas_accéder_a_l_espace_manager(): void
    {
        $citoyen = $this->createUserWithRole(Role::CITOYEN);

        // Authenticated but without the role: refused with a 403.
        $this->actingAs($citoyen)->get('/manager/demandes')->assertForbidden();
    }

    public function test_le_manager_voit_la_liste_et_peut_filtrer_par_statut(): void
    {
        $manager = $this->createUserWithRole(Role::MANAGER);
        $citoyen = $this->createUserWithRole(Role::CITOYEN);
        $projet  = $this->projet();

        $this->demande($projet, $citoyen, 'en_attente');
        $this->demande($projet, $citoyen, 'refusee');

        $this->actingAs($manager)
            ->get('/manager/demandes?statut=en_attente')
            ->assertOk()
            ->assertSee('En attente');

        $this->actingAs($manager)
            ->get('/manager/demandes?statut=refusee')
            ->assertOk()
            ->assertSee('Refusée');
    }

    /* ── Détail ───────────────────────────────────────────────────────────── */

    public function test_le_manager_consulte_le_detail_d_une_demande(): void
    {
        $manager = $this->createUserWithRole(Role::MANAGER);
        $citoyen = $this->createUserWithRole(Role::CITOYEN);
        $projet  = $this->projet();
        $demande = $this->demande($projet, $citoyen, 'en_attente', 5000);

        $this->actingAs($manager)
            ->get('/manager/demandes/'.$demande->id)
            ->assertOk();

        // Un citoyen reste refusé sur le détail manager.
        $this->actingAs($citoyen)
            ->get('/manager/demandes/'.$demande->id)
            ->assertForbidden();
    }

    /* ── Acceptation ──────────────────────────────────────────────────────── */

    public function test_le_manager_peut_accepter_une_demande_en_attente(): void
    {
        $manager = $this->createUserWithRole(Role::MANAGER);
        $citoyen = $this->createUserWithRole(Role::CITOYEN);
        $projet  = $this->projet();
        $demande = $this->demande($projet, $citoyen, 'en_attente', 4000);

        $this->actingAs($manager)
            ->post('/manager/demandes/'.$demande->id.'/accepter')
            ->assertRedirect();

        $demande->refresh();

        $this->assertSame('acceptee', $demande->statut);
        $this->assertSame($manager->id, $demande->decided_by);
        $this->assertNotNull($demande->decided_at);
    }

    public function test_accepter_ne_comptabilise_aucun_financement(): void
    {
        $manager = $this->createUserWithRole(Role::MANAGER);
        $citoyen = $this->createUserWithRole(Role::CITOYEN);
        $projet  = $this->projet();
        $demande = $this->demande($projet, $citoyen, 'en_attente', 4000);

        $this->actingAs($manager)->post('/manager/demandes/'.$demande->id.'/accepter');

        $this->assertSame(0, \App\Models\Financement::count());
        $this->assertSame(0.0, (float) $projet->refresh()->total_finance);
    }

    public function test_une_demande_deja_traitee_ne_peut_plus_etre_acceptee(): void
    {
        $manager = $this->createUserWithRole(Role::MANAGER);
        $citoyen = $this->createUserWithRole(Role::CITOYEN);
        $projet  = $this->projet();

        $demande = $this->demande($projet, $citoyen, 'refusee');

        $this->actingAs($manager)
            ->post('/manager/demandes/'.$demande->id.'/accepter')
            ->assertSessionHas('error');

        $this->assertSame('refusee', $demande->refresh()->statut);
    }

    public function test_une_montant_superieur_au_budget_restant_ne_peut_etre_accepte(): void
    {
        $manager = $this->createUserWithRole(Role::MANAGER);
        $citoyen = $this->createUserWithRole(Role::CITOYEN);
        $projet  = $this->projet(5000);

        $demande = $this->demande($projet, $citoyen, 'en_attente', 6000);

        $this->actingAs($manager)
            ->post('/manager/demandes/'.$demande->id.'/accepter')
            ->assertSessionHas('error');

        $this->assertSame('en_attente', $demande->refresh()->statut);
    }

    public function test_une_reservee_empeche_de_depasser_le_budget_par_plusieurs_acceptations(): void
    {
        $manager = $this->createUserWithRole(Role::MANAGER);
        $citoyen = $this->createUserWithRole(Role::CITOYEN);
        $projet  = $this->projet(5000);

        // Two requests of 4 000 each: only one may be approved.
        $premiere  = $this->demande($projet, $citoyen, 'en_attente', 4000);
        $deuxieme  = $this->demande($projet, $citoyen, 'en_attente', 4000);

        $this->actingAs($manager)->post('/manager/demandes/'.$premiere->id.'/accepter');
        $this->actingAs($manager)->post('/manager/demandes/'.$deuxieme->id.'/accepter');

        $this->assertSame('acceptee', $premiere->refresh()->statut);
        $this->assertSame('en_attente', $deuxieme->refresh()->statut, 'La seconde demande dépasse le budget réservé.');

        // The reservation is reflected in what is still open for funding.
        $this->assertSame(1000.0, (float) $projet->refresh()->budget_disponible);
    }

    /* ── Refus ────────────────────────────────────────────────────────────── */

    public function test_le_manager_peut_refuser_une_demande_avec_un_motif(): void
    {
        $manager = $this->createUserWithRole(Role::MANAGER);
        $citoyen = $this->createUserWithRole(Role::CITOYEN);
        $projet  = $this->projet();
        $demande = $this->demande($projet, $citoyen);

        $this->actingAs($manager)
            ->post('/manager/demandes/'.$demande->id.'/refuser', [
                'motif_refus' => 'Budget déjà saturé sur ce lot.',
            ])
            ->assertRedirect();

        $demande->refresh();

        $this->assertSame('refusee', $demande->statut);
        $this->assertSame('Budget déjà saturé sur ce lot.', $demande->motif_refus);
        $this->assertSame($manager->id, $demande->decided_by);
    }

    public function test_un_refus_sans_motif_est_rejete(): void
    {
        $manager = $this->createUserWithRole(Role::MANAGER);
        $citoyen = $this->createUserWithRole(Role::CITOYEN);
        $projet  = $this->projet();
        $demande = $this->demande($projet, $citoyen);

        $this->actingAs($manager)
            ->post('/manager/demandes/'.$demande->id.'/refuser', ['motif_refus' => ''])
            ->assertSessionHasErrors('motif_refus');

        $this->assertSame('en_attente', $demande->refresh()->statut);
    }

    public function test_le_motif_de_refus_est_visible_par_le_citoyen(): void
    {
        $manager = $this->createUserWithRole(Role::MANAGER);
        $citoyen = $this->createUserWithRole(Role::CITOYEN);
        $projet  = $this->projet();
        $demande = $this->demande($projet, $citoyen);

        $this->actingAs($manager)->post('/manager/demandes/'.$demande->id.'/refuser', [
            'motif_refus' => 'Le projet est déjà financé à 100 %.',
        ]);

        $this->actingAs($citoyen)
            ->get('/citizen/demandes/'.$demande->id)
            ->assertOk()
            ->assertSee('Refusée')
            ->assertSee('Le projet est déjà financé à 100 %.');
    }

    /* ── Paiement ─────────────────────────────────────────────────────────── */

    public function test_une_demande_refusee_ne_peut_pas_etre_payee(): void
    {
        $citoyen = $this->createUserWithRole(Role::CITOYEN);
        $projet  = $this->projet();
        $demande = $this->demande($projet, $citoyen, 'refusee');

        $this->actingAs($citoyen)
            ->get('/citizen/demandes/'.$demande->id.'/recapitulatif')
            ->assertRedirect();

        $this->actingAs($citoyen)
            ->post('/citizen/demandes/'.$demande->id.'/confirmer')
            ->assertRedirect();

        $this->assertSame(0, \App\Models\Paiement::count());
        $this->assertSame(0, \App\Models\Financement::count());
    }

    public function test_une_demande_encore_en_attente_ne_peut_pas_etre_payee(): void
    {
        $citoyen = $this->createUserWithRole(Role::CITOYEN);
        $projet  = $this->projet();
        $demande = $this->demande($projet, $citoyen, 'en_attente');

        $this->actingAs($citoyen)
            ->post('/citizen/demandes/'.$demande->id.'/confirmer')
            ->assertRedirect();

        $this->assertSame(0, \App\Models\Paiement::count());
        $this->assertSame(0, \App\Models\Financement::count());
    }

    /* ── Flux complet ─────────────────────────────────────────────────────── */

    public function test_flux_complet_depuis_la_demande_jusqu_au_reçu(): void
    {
        $manager = $this->createUserWithRole(Role::MANAGER);
        $citoyen = $this->createUserWithRole(Role::CITOYEN);
        $projet  = $this->projet(20000);

        // 1. The citizen files a request.
        $this->actingAs($citoyen)->post('/citizen/demandes', [
            'projet_id' => $projet->id,
            'montant'   => 5000,
        ]);
        $demande = DemandeFinancement::firstOrFail();
        $this->assertSame('en_attente', $demande->statut);

        // 2. The manager approves it.
        $this->actingAs($manager)->post('/manager/demandes/'.$demande->id.'/accepter');
        $this->assertSame('acceptee', $demande->refresh()->statut);

        // 3. The citizen reviews the recap, which shows the mandatory warning.
        $this->actingAs($citoyen)
            ->get('/citizen/demandes/'.$demande->id.'/recapitulatif')
            ->assertOk()
            ->assertSee('Paiement simulé à des fins académiques')
            ->assertSee('5 000,00');

        // 4. He confirms and the simulation succeeds.
        $this->actingAs($citoyen)->post('/citizen/demandes/'.$demande->id.'/confirmer', [
            'scenario' => 'succes',
        ]);
        $this->assertSame('payee', $demande->refresh()->statut);

        // 5. The funding is recorded once, on the right project.
        $this->assertSame(1, \App\Models\Financement::count());
        $this->assertSame(5000.0, (float) $projet->refresh()->total_finance);

        // 6. The receipt is reachable and stamped as a simulation.
        $paiement = \App\Models\Paiement::firstOrFail();
        $this->actingAs($citoyen)
            ->get('/citizen/paiements/'.$paiement->id.'/recu')
            ->assertOk()
            ->assertSee('Simulation — aucune transaction financière réelle');
    }
}
