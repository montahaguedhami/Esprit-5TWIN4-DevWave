<?php

namespace Tests\Feature;

use App\Models\DemandeFinancement;
use App\Models\Financement;
use App\Models\Paiement;
use App\Models\Projet;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithRbac;
use Tests\TestCase;

/**
 * « Mon portefeuille et mon impact ».
 *
 * These tests only read rows that already exist: nothing here creates a
 * contribution through the payment flow, and nothing here is allowed to
 * touch another citizen's data.
 */
class PortefeuilleCitoyenTest extends TestCase
{
    use RefreshDatabase;
    use InteractsWithRbac;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->seedRbac();
    }

    /* ── Helpers ─────────────────────────────────────────────────────────── */

    /**
     * Record a funding explicitly owned by a citizen.
     *
     * `user_id` is deliberately not mass assignable on the model, which is
     * exactly why it is assigned here instead of passed to create().
     */
    private function enregistrerContribution(Projet $projet, User $citoyen, float $montant): Financement
    {
        $financement = new Financement([
            'projet_id'        => $projet->id,
            'source'           => 'Contribution citoyenne (simulation)',
            'montant'          => $montant,
            'date_financement' => '2026-03-15',
        ]);

        $financement->user_id = $citoyen->id;
        $financement->save();

        return $financement;
    }

    private function projet(string $statut = 'en_cours'): Projet
    {
        return Projet::factory()->create(['statut' => $statut]);
    }

    /** Read the variables handed to the Blade view. */
    private function data($response): array
    {
        return $response->original->gatherData();
    }

    /* ── 1. Portefeuille vide ────────────────────────────────────────────── */

    public function test_un_citoyen_sans_contribution_voit_un_portefeuille_vide_avec_des_statistiques_a_zero(): void
    {
        $citoyen = $this->createUserWithRole(Role::CITOYEN);

        $response = $this->actingAs($citoyen)->get('/citizen/portefeuille');

        $response->assertOk();
        $response->assertViewIs('citizen.portefeuille');

        $data = $this->data($response);
        $this->assertSame(0.0, (float) $data['totalContributions']);
        $this->assertSame(0, $data['projetsSoutenus']);
        $this->assertSame(0, $data['projetsTerminesSoutenus']);
        $this->assertSame(0, $data['contributions']->total());

        // Empty state visible.
        $response->assertSee('Votre portefeuille est vide');
        $response->assertSee('0,00 TND');
    }

    /* ── 2. Total correct ────────────────────────────────────────────────── */

    public function test_le_total_est_la_somme_de_mes_contributions(): void
    {
        $citoyen = $this->createUserWithRole(Role::CITOYEN);
        $p1 = $this->projet();
        $p2 = $this->projet();

        $this->enregistrerContribution($p1, $citoyen, 1500.00);
        $this->enregistrerContribution($p2, $citoyen, 250.50);

        $response = $this->actingAs($citoyen)->get('/citizen/portefeuille');

        $response->assertOk();

        $data = $this->data($response);
        $this->assertEquals(1750.50, $data['totalContributions']);
        $this->assertSame(2, $data['projetsSoutenus']);
        $this->assertSame(2, $data['contributions']->total());

        $response->assertSee('1 750,50 TND');
        $response->assertDontSee('Votre portefeuille est vide');
    }

    /* ── 3. Un projet = un projet soutenu ────────────────────────────────── */

    public function test_plusieurs_contributions_au_memes_projet_ne_comptent_que_pour_un_seul_projet_soutenu(): void
    {
        $citoyen = $this->createUserWithRole(Role::CITOYEN);
        $projet  = $this->projet();

        $this->enregistrerContribution($projet, $citoyen, 100.00);
        $this->enregistrerContribution($projet, $citoyen, 100.00);
        $this->enregistrerContribution($projet, $citoyen, 100.00);

        $data = $this->data($this->actingAs($citoyen)->get('/citizen/portefeuille'));

        // Three rows recorded, but a single distinct project.
        $this->assertSame(3, $data['contributions']->total());
        $this->assertEquals(300.00, $data['totalContributions']);
        $this->assertSame(1, $data['projetsSoutenus']);
        $this->assertSame(0, $data['projetsTerminesSoutenus']);
    }

    /* ── 4. Projet terminé compté une fois ───────────────────────────────── */

    public function test_un_projet_termine_soutenu_est_compte_une_seule_fois_dans_les_projets_termines(): void
    {
        $citoyen      = $this->createUserWithRole(Role::CITOYEN);
        $termine      = $this->projet('termine');
        $enCours      = $this->projet('en_cours');

        // Two contributions to the finished project, one to the running one.
        $this->enregistrerContribution($termine, $citoyen, 500.00);
        $this->enregistrerContribution($termine, $citoyen, 500.00);
        $this->enregistrerContribution($enCours, $citoyen, 500.00);

        $data = $this->data($this->actingAs($citoyen)->get('/citizen/portefeuille'));

        $this->assertSame(2, $data['projetsSoutenus']);
        $this->assertSame(1, $data['projetsTerminesSoutenus']);
        $this->assertEquals(1500.00, $data['totalContributions']);
    }

    public function test_les_autres_statuts_de_projet_ne_sont_pas_comptes_comme_termines(): void
    {
        $citoyen = $this->createUserWithRole(Role::CITOYEN);

        foreach (['planifie', 'en_cours', 'suspendu', 'annule'] as $statut) {
            $this->enregistrerContribution($this->projet($statut), $citoyen, 100.00);
        }

        $data = $this->data($this->actingAs($citoyen)->get('/citizen/portefeuille'));

        $this->assertSame(4, $data['projetsSoutenus']);
        $this->assertSame(0, $data['projetsTerminesSoutenus']);
    }

    /* ── 5. Les demandes ne sont pas des contributions ───────────────────── */

    public function test_les_demandes_en_attente_refusees_ou_annulees_ne_sont_pas_comptabilisees(): void
    {
        $citoyen = $this->createUserWithRole(Role::CITOYEN);
        $projet  = $this->projet();

        // A request is only a proposal: it never creates a Financement row.
        // `acceptee` is included on purpose — an approved request only
        // *reserves* the amount, the Financement appears at confirmation.
        foreach ([
            DemandeFinancement::EN_ATTENTE,
            DemandeFinancement::REFUSEE,
            DemandeFinancement::ACCEPTEE,
        ] as $statut) {
            DemandeFinancement::create([
                'projet_id' => $projet->id,
                'user_id'   => $citoyen->id,
                'montant'   => 1000,
                'devise'    => 'TND',
                'statut'    => $statut,
            ]);
        }

        // Cancelled and failed attempts stay on `paiements` and never reach
        // `financements`, so they must not appear in the wallet either.
        foreach (DemandeFinancement::query()->pluck('id') as $index => $demandeId) {
            \App\Models\Paiement::create([
                'demande_id'         => $demandeId,
                'user_id'            => $citoyen->id,
                'projet_id'          => $projet->id,
                'montant'            => 1000,
                'devise'             => 'TND',
                'statut'             => $index % 2 === 0 ? Paiement::ANNULE : Paiement::ECHOUE,
                'reference_interne'  => 'SIM-TEST-' . $index,
                'motif'              => 'Scénario de test : aucun montant comptabilisé.',
            ]);
        }

        $this->assertSame(0, Financement::count());

        $response = $this->actingAs($citoyen)->get('/citizen/portefeuille');
        $response->assertOk();

        $data = $this->data($response);
        $this->assertSame(0.0, (float) $data['totalContributions']);
        $this->assertSame(0, $data['projetsSoutenus']);
        $this->assertSame(0, $data['projetsTerminesSoutenus']);
        $this->assertSame(0, $data['contributions']->total());
        $response->assertSee('Votre portefeuille est vide');
    }

    /* ── 6. Aucune donnée d'autrui ───────────────────────────────────────── */

    public function test_les_financements_des_autres_citoyens_et_du_gestionnaire_ne_sont_jamais_inclus(): void
    {
        $citoyen     = $this->createUserWithRole(Role::CITOYEN);
        $autreCitoyen = $this->createUserWithRole(Role::CITOYEN);
        $manager     = $this->createUserWithRole(Role::MANAGER);
        $projet      = $this->projet();

        // Another citizen's contribution.
        $this->enregistrerContribution($projet, $autreCitoyen, 9000.00);

        // A manager funding, with no citizen attached (user_id NULL).
        $managerFunding = new Financement([
            'projet_id'        => $projet->id,
            'source'           => 'Budget de l\'État Tunisien',
            'montant'          => 5000.00,
            'date_financement' => '2026-03-15',
        ]);
        $managerFunding->user_id = $manager->id;
        $managerFunding->save();

        // A legacy manager funding with a NULL owner, as created before user_id existed.
        Financement::withoutTouching(function () use ($projet) {
            \DB::table('financements')->insert([
                'projet_id'        => $projet->id,
                'source'           => 'Banque Mondiale',
                'montant'          => 7500.00,
                'date_financement' => '2026-03-15',
                'user_id'          => null,
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);
        });

        $this->enregistrerContribution($projet, $citoyen, 120.00);

        $response = $this->actingAs($citoyen)->get('/citizen/portefeuille');
        $response->assertOk();

        $data = $this->data($response);

        // Only the citizen's own contribution is visible or counted.
        $this->assertEquals(120.00, $data['totalContributions']);
        $this->assertSame(1, $data['projetsSoutenus']);
        $this->assertSame(1, $data['contributions']->total());

        foreach ($data['contributions'] as $contribution) {
            $this->assertSame($citoyen->id, $contribution->user_id);
        }

        $response->assertDontSee('Budget de l\'État Tunisien');
        $response->assertDontSee('Banque Mondiale');
    }

    public function test_un_identifiant_utilisateur_transmis_dans_l_url_est_ignore(): void
    {
        $citoyen      = $this->createUserWithRole(Role::CITOYEN);
        $autreCitoyen = $this->createUserWithRole(Role::CITOYEN);
        $projet       = $this->projet();

        $this->enregistrerContribution($projet, $autreCitoyen, 4200.00);
        $this->enregistrerContribution($projet, $citoyen, 80.00);

        $response = $this->actingAs($citoyen)
            ->get('/citizen/portefeuille?user_id=' . $autreCitoyen->id . '&id=' . $autreCitoyen->id);

        $response->assertOk();

        $data = $this->data($response);
        $this->assertEquals(80.00, $data['totalContributions']);
        $this->assertSame(1, $data['contributions']->total());

        foreach ($data['contributions'] as $contribution) {
            $this->assertSame($citoyen->id, $contribution->user_id);
        }
    }

    /* ── 7. Accès ────────────────────────────────────────────────────────── */

    public function test_un_visiteur_ne_peut_pas_acceder_au_portefeuille(): void
    {
        $this->get('/citizen/portefeuille')->assertRedirect(route('auth.login'));
    }

    public function test_un_utilisateur_sans_role_citoyen_ne_peut_pas_acceder_au_portefeuille(): void
    {
        $manager  = $this->createUserWithRole(Role::MANAGER);
        $technicien = $this->createUserWithRole(Role::TECHNICIEN);

        $this->actingAs($manager)->get('/citizen/portefeuille')->assertForbidden();
        $this->actingAs($technicien)->get('/citizen/portefeuille')->assertForbidden();
    }

    public function test_un_compte_desactive_ne_peut_pas_acceder_au_portefeuille(): void
    {
        $citoyen = $this->createUserWithRole(Role::CITOYEN, ['is_active' => false]);

        $this->actingAs($citoyen)->get('/citizen/portefeuille')->assertRedirect();
    }

    /* ── Intégration / accès depuis l'espace citoyen ─────────────────────── */

    public function test_la_route_du_portefeuille_est_accessible_depuis_le_menu_citoyen(): void
    {
        $this->assertSame(url('/citizen/portefeuille'), route('citizen.portefeuille'));

        $citoyen = $this->createUserWithRole(Role::CITOYEN);

        // Checked on a *different* front-office page: the link lives in the
        // shared layout, so it must be present outside the wallet itself.
        $response = $this->actingAs($citoyen)->get('/citizen/demandes');

        $response->assertOk();
        $response->assertSee('/citizen/portefeuille', false);
        $response->assertSee('Mon portefeuille');
    }

    public function test_le_lien_vers_le_projet_est_present_dans_l_historique(): void
    {
        $citoyen = $this->createUserWithRole(Role::CITOYEN);
        $projet  = $this->projet('en_cours');

        $this->enregistrerContribution($projet, $citoyen, 300.00);

        $response = $this->actingAs($citoyen)->get('/citizen/portefeuille');

        $response->assertOk();
        $response->assertSee($projet->nom);
        $response->assertSee(route('citizen.projets.show', $projet), false);
        $response->assertSee('300,00 TND');
        $response->assertSee('En cours');
    }

    public function test_l_etat_affiche_respecte_la_pagination_sans_recalculer_les_statistiques(): void
    {
        $citoyen = $this->createUserWithRole(Role::CITOYEN);
        $projet  = $this->projet();

        for ($i = 0; $i < 15; $i++) {
            $this->enregistrerContribution($projet, $citoyen, 10.00);
        }

        $response = $this->actingAs($citoyen)->get('/citizen/portefeuille');

        $response->assertOk();

        $data = $this->data($response);

        // Only one page of rows is rendered…
        $this->assertSame(10, $data['contributions']->count());
        // …but the totals still reflect all 15 contributions.
        $this->assertSame(15, $data['contributions']->total());
        $this->assertEquals(150.00, $data['totalContributions']);
        $this->assertSame(1, $data['projetsSoutenus']);
    }
}
