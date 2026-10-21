<?php

namespace Tests\Feature;

use App\Models\Favori;
use App\Models\Projet;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\InteractsWithRbac;
use Tests\TestCase;

/**
 * Bookmarks — the citizen's explicit interest signal.
 *
 * Isolation is the point of this suite: a citizen must never be able to read
 * or edit someone else's favourites, whatever the URL or payload says.
 */
class FavorisCitoyenTest extends TestCase
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

    private function citoyen(): User
    {
        return $this->createUserWithRole(Role::CITOYEN);
    }

    private function projetDisponible(array $attrs = []): Projet
    {
        return Projet::factory()->create(array_merge(['statut' => 'en_cours', 'budget' => 500000], $attrs));
    }

    /* ── 1. Ajout / retrait ──────────────────────────────────────────────── */

    public function test_un_citoyen_authentifie_peut_ajouter_un_favori(): void
    {
        $citoyen = $this->citoyen();
        $projet  = $this->projetDisponible();

        $response = $this->actingAs($citoyen)
            ->post(route('citizen.projets.favori.store', $projet));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('favoris', [
            'user_id'   => $citoyen->id,
            'projet_id' => $projet->id,
        ]);
    }

    public function test_un_citoyen_peut_retirer_son_favori(): void
    {
        $citoyen = $this->citoyen();
        $projet  = $this->projetDisponible();

        Favori::create(['user_id' => $citoyen->id, 'projet_id' => $projet->id]);

        $this->actingAs($citoyen)
            ->delete(route('citizen.projets.favori.destroy', $projet))
            ->assertRedirect();

        $this->assertDatabaseMissing('favoris', [
            'user_id'   => $citoyen->id,
            'projet_id' => $projet->id,
        ]);
    }

    public function test_le_mecanisme_bascule_ajoute_puis_retire(): void
    {
        $citoyen = $this->citoyen();
        $projet  = $this->projetDisponible();

        // 1st toggle: added.
        $this->actingAs($citoyen)
            ->patchJson(route('citizen.projets.favori.basculer', $projet))
            ->assertOk()
            ->assertJsonPath('favori', true);

        $this->assertDatabaseHas('favoris', ['user_id' => $citoyen->id, 'projet_id' => $projet->id]);

        // 2nd toggle: removed.
        $this->actingAs($citoyen)
            ->patchJson(route('citizen.projets.favori.basculer', $projet))
            ->assertOk()
            ->assertJsonPath('favori', false);

        $this->assertDatabaseMissing('favoris', ['user_id' => $citoyen->id, 'projet_id' => $projet->id]);
    }

    /* ── 2. Unicité ──────────────────────────────────────────────────────── */

    public function test_une_paire_utilisateur_projet_ne_peut_pas_etre_dupliquee(): void
    {
        $citoyen = $this->citoyen();
        $projet  = $this->projetDisponible();

        Favori::create(['user_id' => $citoyen->id, 'projet_id' => $projet->id]);

        // The controller is idempotent: calling it again must not duplicate.
        $this->actingAs($citoyen)
            ->post(route('citizen.projets.favori.store', $projet))
            ->assertRedirect();

        $this->assertSame(1, Favori::where('user_id', $citoyen->id)->where('projet_id', $projet->id)->count());
    }

    public function test_une_paire_dupliquee_est_rejetee_par_la_base_lui_meme(): void
    {
        // The guarantee lives in the schema, not only in the controller:
        // bypassing the controller still cannot duplicate the row.
        $citoyen = $this->citoyen();
        $projet  = $this->projetDisponible();

        Favori::create(['user_id' => $citoyen->id, 'projet_id' => $projet->id]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        DB::table('favoris')->insert([
            'user_id'    => $citoyen->id,
            'projet_id'  => $projet->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /* ── 3. Isolation ────────────────────────────────────────────────────── */

    public function test_un_citoyen_ne_peut_pas_modifier_les_favoris_d_un_autre(): void
    {
        $victime = $this->citoyen();
        $intrus  = $this->citoyen();
        $projet  = $this->projetDisponible();

        Favori::create(['user_id' => $victime->id, 'projet_id' => $projet->id]);

        // Removing as someone else must NOT delete the victim's row.
        $this->actingAs($intrus)
            ->delete(route('citizen.projets.favori.destroy', $projet))
            ->assertRedirect();

        $this->assertDatabaseHas('favoris', ['user_id' => $victime->id, 'projet_id' => $projet->id]);
    }

    public function test_le_liste_des_favoris_ne_montre_que_ses_propres_projets(): void
    {
        $victime = $this->citoyen();
        $intrus  = $this->citoyen();

        // Explicit, distinct names: ProjetFactory picks at random from a pool
        // of fifteen, so two projects can share a name and make an assertion
        // on the rendered text ambiguous.
        $mien    = $this->projetDisponible(['nom' => 'Projet visible par l intrus seulement']);
        $pasMien = $this->projetDisponible(['nom' => 'Projet prive de la victime']);

        Favori::create(['user_id' => $victime->id, 'projet_id' => $pasMien->id]);
        Favori::create(['user_id' => $intrus->id, 'projet_id' => $mien->id]);

        $response = $this->actingAs($victime)->get(route('citizen.favoris'));

        $response->assertOk();
        $response->assertSee('Projet prive de la victime', false);
        $response->assertDontSee('Projet visible par l intrus seulement', false);
        $this->assertSame(1, $response->viewData('favoris')->total());
    }

    public function test_aucun_identifiant_citoyen_n_est_accepte_depuis_l_url(): void
    {
        $victime = $this->citoyen();
        $intrus  = $this->citoyen();
        $projet  = $this->projetDisponible();

        Favori::create(['user_id' => $victime->id, 'projet_id' => $projet->id]);

        // Forging an owner in the query string must change nothing: the owner
        // always comes from the session.
        $this->actingAs($intrus)
            ->post(route('citizen.projets.favori.destroy', $projet) . '?user_id=' . $victime->id . '&id=' . $victime->id)
            ->assertRedirect();

        $this->assertDatabaseHas('favoris', ['user_id' => $victime->id, 'projet_id' => $projet->id]);
    }

    public function test_un_visiteur_ne_peut_pas_gerer_les_favoris(): void
    {
        $projet = $this->projetDisponible();

        $this->post(route('citizen.projets.favori.store', $projet))->assertRedirect(route('auth.login'));
        $this->delete(route('citizen.projets.favori.destroy', $projet))->assertRedirect(route('auth.login'));
        $this->get(route('citizen.favoris'))->assertRedirect(route('auth.login'));
    }

    public function test_un_employe_non_citoyen_ne_peut_pas_gerer_les_favoris(): void
    {
        $projet = $this->projetDisponible();

        $this->actingAs($this->createUserWithRole(Role::TECHNICIEN))
            ->post(route('citizen.projets.favori.store', $projet))
            ->assertForbidden();

        $this->actingAs($this->createUserWithRole(Role::MANAGER))
            ->get(route('citizen.favoris'))
            ->assertForbidden();
    }

    /* ── 4. Validité ─────────────────────────────────────────────────────── */

    public function test_un_projet_inexistant_est_refuse(): void
    {
        $citoyen = $this->citoyen();

        $this->actingAs($citoyen)
            ->post(route('citizen.projets.favori.store', 999999))
            ->assertNotFound();
    }

    public function test_un_projet_non_accessible_ne_peut_pas_etre_favori(): void
    {
        $citoyen = $this->citoyen();
        // A cancelled project is not part of what the citizen can browse.
        $projet  = Projet::factory()->create(['statut' => 'annule']);

        $this->actingAs($citoyen)
            ->post(route('citizen.projets.favori.store', $projet))
            ->assertStatus(422);
    }

    /* ── 5. Pagination ───────────────────────────────────────────────────── */

    public function test_la_liste_des_favoris_est_paginee(): void
    {
        $citoyen = $this->citoyen();

        foreach (Projet::factory()->count(15)->create(['statut' => 'en_cours']) as $projet) {
            Favori::create(['user_id' => $citoyen->id, 'projet_id' => $projet->id]);
        }

        $response = $this->actingAs($citoyen)->get(route('citizen.favoris'));

        $response->assertOk();
        $this->assertSame(12, $response->viewData('favoris')->count());
        $this->assertSame(15, $response->viewData('favoris')->total());
        $this->assertTrue($response->viewData('favoris')->hasPages());
    }

    public function test_l_etat_vide_est_affiche_sans_aucun_favori(): void
    {
        $response = $this->actingAs($this->citoyen())->get(route('citizen.favoris'));

        $response->assertOk();
        // assertSee() escapes by default, but this sentence is written as
        // literal Blade markup, so the page really contains the apostrophe.
        $response->assertSee("Aucun favori pour l'instant", false);
    }
}
