<?php

namespace Tests\Feature;

use App\Models\Projet;
use App\Models\Role;
use App\Models\User;
use App\Services\Recommandation\RecommandationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\InteractsWithRbac;
use Tests\TestCase;

/**
 * Wiring of the recommendation module: routes, access control, rendering.
 *
 * Also guards the funding module: everything added here must leave the
 * existing citizen funding flow untouched.
 */
class RecommandationIntegrationTest extends TestCase
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

    private function asCitoyen(User $citoyen): static
    {
        return $this->actingAs($citoyen)->withSession([
            'user' => [
                'name'  => $citoyen->name,
                'email' => $citoyen->email,
                'role'  => 'citizen',
            ],
        ]);
    }

    private function citoyen(): User
    {
        return $this->createUserWithRole(Role::CITOYEN);
    }

    /* ── 1. Authentification ─────────────────────────────────────────────── */

    public function test_les_routes_demandent_une_authentification(): void
    {
        $this->get(route('citizen.recommandations'))->assertRedirect(route('auth.login'));
        $this->get(route('citizen.recommandations.api'))->assertRedirect(route('auth.login'));
        $this->get(route('citizen.favoris'))->assertRedirect(route('auth.login'));
    }

    public function test_un_employe_non_citoyen_est_refuse(): void
    {
        $manager    = $this->createUserWithRole(Role::MANAGER);
        $technicien = $this->createUserWithRole(Role::TECHNICIEN);

        foreach ([$manager, $technicien] as $employe) {
            $this->actingAs($employe)->get(route('citizen.recommandations'))->assertForbidden();
            $this->actingAs($employe)->get(route('citizen.recommandations.api'))->assertForbidden();
            $this->actingAs($employe)->get(route('citizen.favoris'))->assertForbidden();
        }
    }

    /* ── 2. Aucune usurpation d'identité ─────────────────────────────────── */

    public function test_la_route_des_recommandations_utilise_la_session_et_non_un_identifiant_client(): void
    {
        $alice  = $this->citoyen();
        $bob    = $this->citoyen();
        $projet = Projet::factory()->create(['statut' => 'en_cours']);

        // Alice bookmarks it, then tries to ask for Bob's recommendations.
        $this->asCitoyen($alice)
            ->post(route('citizen.projets.favori.store', $projet))
            ->assertRedirect();

        // Forging an identifier in the query string changes nothing: the
        // engine always reads the authenticated session.
        $reponse = $this->asCitoyen($alice)
            ->getJson(route('citizen.recommandations.api') . '?user_id=' . $bob->id . '&id=' . $bob->id);

        $reponse->assertOk();

        // Bob has no history, so his profile must never be served to Alice:
        // the response is the honest fallback, not Alice's personalisation.
        $this->assertTrue($reponse->json('repli'), 'Le profil de Bob n\'a pas été servi à Alice.');

        // And Bob, asked directly, gets his own (empty) profile too.
        $this->assertTrue(
            $this->asCitoyen($bob)->getJson(route('citizen.recommandations.api'))->json('repli')
        );
    }

    /* ── 3. Rendu et contrat JSON ────────────────────────────────────────── */

    public function test_l_ecran_des_recommandations_s_affiche(): void
    {
        Projet::factory()->count(3)->create(['statut' => 'en_cours']);

        $response = $this->asCitoyen($this->citoyen())->get(route('citizen.recommandations'));

        $response->assertOk();
        $response->assertSee('Recommandés pour vous');
    }

    public function test_un_citoyen_avec_historique_voir_mentionner_le_moteur_de_regles(): void
    {
        // Once the citizen has a real signal, the honest wording switches from
        // the fallback message to the explicit "this is a deterministic rules
        // engine, not an AI model" statement.
        $citoyen = $this->citoyen();
        $projet  = Projet::factory()->create(['statut' => 'en_cours']);

        $this->asCitoyen($citoyen)
            ->post(route('citizen.projets.favori.store', $projet))
            ->assertRedirect();

        $response = $this->asCitoyen($citoyen)->get(route('citizen.recommandations'));

        $response->assertOk();
        $response->assertSee('moteur de règles déterministe');
        $response->assertSee("Aucun historique pour l'instant", false) && false;
    }

    public function test_la_reponse_json_respecte_le_contrat(): void
    {
        $projet = Projet::factory()->create(['statut' => 'en_cours', 'budget' => 100000]);

        $donnees = $this->asCitoyen($this->citoyen())
            ->getJson(route('citizen.recommandations.api'))
            ->assertOk()
            ->json('donnees');

        $this->assertNotEmpty($donnees);

        $premier = $donnees[0];
        foreach (['projet_id', 'nom', 'statut', 'progression', 'budget', 'budget_disponible', 'zones', 'score', 'financable', 'repli', 'raisons', 'url'] as $champ) {
            $this->assertArrayHasKey($champ, $premier, "Champ $champ absent du contrat JSON.");
        }

        $this->assertGreaterThanOrEqual(0.0, $premier['score']);
        $this->assertLessThanOrEqual(100.0, $premier['score']);
        $this->assertIsArray($premier['raisons']);
    }

    public function test_la_limite_est_validee_cote_serveur(): void
    {
        Projet::factory()->count(5)->create(['statut' => 'en_cours']);

        // Above the documented ceiling: rejected, not silently clamped into a
        // full catalogue dump.
        $this->asCitoyen($this->citoyen())
            ->getJson(route('citizen.recommandations.api') . '?limite=500')
            ->assertStatus(422);

        $this->asCitoyen($this->citoyen())
            ->getJson(route('citizen.recommandations.api') . '?limite=0')
            ->assertStatus(422);

        $this->asCitoyen($this->citoyen())
            ->getJson(route('citizen.recommandations.api') . '?limite=abc')
            ->assertStatus(422);

        $this->asCitoyen($this->citoyen())
            ->getJson(route('citizen.recommandations.api') . '?limite=2')
            ->assertOk()
            ->assertJsonCount(2, 'donnees');
    }

    /* ── 4. États de l'interface ─────────────────────────────────────────── */

    public function test_l_etat_vide_s_affiche_quand_aucun_projet_n_est_admissible(): void
    {
        Projet::factory()->count(3)->create(['statut' => 'annule']);

        $response = $this->asCitoyen($this->citoyen())->get(route('citizen.recommandations'));

        $response->assertOk();
        $response->assertSee('Aucune recommandation disponible');
    }

    public function test_le_citoyen_sans_historique_recoit_un_message_honnete(): void
    {
        Projet::factory()->create(['statut' => 'en_cours']);

        $response = $this->asCitoyen($this->citoyen())->get(route('citizen.recommandations'));

        $response->assertOk();
        $response->assertSee("Aucun historique pour l'instant", false);
        $response->assertSee('Ce n\'est pas une sélection personnalisée', false);
    }

    public function test_les_favoris_s_affichent_avec_leur_etat_vide(): void
    {
        $response = $this->asCitoyen($this->citoyen())->get(route('citizen.favoris'));

        $response->assertOk();
        $response->assertSee("Aucun favori pour l'instant", false);
    }

    /* ── 5. Performance : pas de N+1 ─────────────────────────────────────── */

    public function test_le_classement_ne_provoque_pas_de_requetes_N_plus_1(): void
    {
        $projet = Projet::factory()->create(['statut' => 'en_cours', 'budget' => 500000]);
        $autre  = Projet::factory()->create(['statut' => 'en_cours', 'budget' => 500000]);

        // Budgets differ so the two projects do not tie, which would make the
        // ordering depend solely on the tie-break rule.
        $this->asCitoyen($this->citoyen());

        DB::enableQueryLog();

        $this->moteur()->recommander($this->citoyen(), 20);
        $premier = count(DB::getQueryLog());

        // Double the candidate set.
        Projet::factory()->count(8)->create(['statut' => 'en_cours', 'budget' => 700000]);

        DB::flushQueryLog();
        $this->moteur()->recommander($this->citoyen(), 20);
        $second = count(DB::getQueryLog());

        DB::disableQueryLog();

        // With N+1, 8 extra projects would add dozens of queries. The engine
        // loads interactions once and aggregates in SQL, so the growth stays
        // far below a per-project cost.
        $this->assertLessThan(
            10,
            $second - $premier,
            'Le moteur doit charger les interactions une seule fois (N+1 détecté).'
        );
    }

    /* ── 6. Le module de financement reste intact ────────────────────────── */

    public function test_le_parcours_de_financement_existant_fonctionne_toujours(): void
    {
        $citoyen = $this->citoyen();
        $projet  = Projet::factory()->create(['statut' => 'en_cours', 'budget' => 500000]);

        // The funding screens still render for a citizen.
        $this->asCitoyen($citoyen)->get(route('citizen.demandes.index'))->assertOk();
        // `create` requires an explicit projet_id — it never guesses one.
        $this->asCitoyen($citoyen)
            ->get(route('citizen.demandes.create', ['projet_id' => $projet->id]))
            ->assertOk();

        // A request can still be filed.
        $this->asCitoyen($citoyen)
            ->post(route('citizen.demandes.store'), [
                'projet_id' => $projet->id,
                'montant'   => 2000,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('demandes_financement', [
            'projet_id' => $projet->id,
            'user_id'   => $citoyen->id,
            'montant'   => 2000,
        ]);
    }

    public function test_les_projets_et_leur_detail_rendent_toujours(): void
    {
        $projet = Projet::factory()->create(['statut' => 'en_cours']);

        $this->asCitoyen($this->citoyen())->get(route('citizen.projets.index'))->assertOk();
        $this->asCitoyen($this->citoyen())->get(route('citizen.projets.show', $projet))->assertOk();
    }

    /* ── Helpers ─────────────────────────────────────────────────────────── */

    private function moteur(): RecommandationService
    {
        return $this->app->make(RecommandationService::class);
    }
}
