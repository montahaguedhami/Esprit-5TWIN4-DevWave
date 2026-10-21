<?php

namespace Tests\Feature;

use App\Models\Projet;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithRbac;
use Tests\TestCase;

/**
 * Tests du CRUD gestionnaire des projets (module Projets et Financements).
 *
 * Les contrôleurs manager vérifient `Role::sessionMatches()` qui lit la clé de
 * SESSION `user.role` écrite à la connexion : `actingAs()` seul ne suffit pas,
 * il faut aussi peupler la session comme le fait la connexion classique.
 */
class ManagerProjetCrudTest extends TestCase
{
    use RefreshDatabase;
    use InteractsWithRbac;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->seedRbac();
    }

    /** Simule une session de gestionnaire authentifié (guard + session). */
    private function asManager(User $manager): static
    {
        return $this->actingAs($manager)->withSession([
            'user' => [
                'name'  => $manager->name,
                'email' => $manager->email,
                'role'  => Role::MANAGER,
            ],
        ]);
    }

    /** @return array<string, mixed> Données valides pour un projet */
    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'nom'         => 'Réseau potable Zone Nord Test',
            'description' => 'Travaux de réhabilitation du réseau.',
            'date_debut'  => '2026-01-05',
            'date_fin'    => '2026-06-30',
            'budget'      => 250000,
            'progression' => 40,
            'statut'      => 'en_cours',
            'adresse'     => 'Tunis Nord, Tunis',
            'latitude'    => 36.8534,
            'longitude'   => 10.1974,
        ], $overrides);
    }

    // ── Lecture ──────────────────────────────────────────────────────────

    public function test_le_manager_voit_la_liste_des_projets(): void
    {
        $manager = $this->createUserWithRole(Role::MANAGER);
        Projet::factory()->create(['nom' => 'Projet Visible En Liste']);

        $this->asManager($manager)
            ->get(route('manager.projets.index'))
            ->assertOk()
            ->assertSee('Projet Visible En Liste');
    }

    public function test_le_manager_ouvre_le_formulaire_de_creation(): void
    {
        $manager = $this->createUserWithRole(Role::MANAGER);

        $this->asManager($manager)
            ->get(route('manager.projets.create'))
            ->assertOk();
    }

    public function test_le_manager_consulte_les_detail_d_un_projet(): void
    {
        $manager = $this->createUserWithRole(Role::MANAGER);
        $projet  = Projet::factory()->create(['nom' => 'Station de Pompage X']);

        $this->asManager($manager)
            ->get(route('manager.projets.show', $projet))
            ->assertOk()
            ->assertSee('Station de Pompage X');
    }

    public function test_le_manager_ouvre_le_formulaire_de_modification(): void
    {
        $manager = $this->createUserWithRole(Role::MANAGER);
        $projet  = Projet::factory()->create();

        $this->asManager($manager)
            ->get(route('manager.projets.edit', $projet))
            ->assertOk();
    }

    public function test_le_manager_voit_la_carte_des_projets(): void
    {
        $manager = $this->createUserWithRole(Role::MANAGER);
        Projet::factory()->create();

        $this->asManager($manager)
            ->get(route('manager.projets.map'))
            ->assertOk();
    }

    // ── Création ─────────────────────────────────────────────────────────

    public function test_le_manager_peut_creer_un_projet(): void
    {
        $manager = $this->createUserWithRole(Role::MANAGER);
        $payload = $this->validPayload(['nom' => 'Projet Créé Par Test']);

        $this->asManager($manager)
            ->from(route('manager.projets.create'))
            ->post(route('manager.projets.store'), $payload)
            ->assertRedirect();

        $this->assertDatabaseHas('projets', [
            'nom'        => 'Projet Créé Par Test',
            'budget'     => 250000,
            'statut'     => 'en_cours',
            'progression'=> 40,
        ]);
    }

    public function test_la_creation_rejette_les_donnees_invalides(): void
    {
        $manager = $this->createUserWithRole(Role::MANAGER);

        $this->asManager($manager)
            ->from(route('manager.projets.create'))
            ->post(route('manager.projets.store'), [
                'nom'        => '',                       // requis
                'date_debut' => 'pas-une-date',           // invalide
                'budget'     => -5,                       // min:0
                'progression'=> 150,                      // between:0,100
                'statut'     => 'inconnu',                // in:...
            ])
            ->assertRedirect(route('manager.projets.create'))
            ->assertSessionHasErrors(['nom', 'date_debut', 'budget', 'progression', 'statut']);

        $this->assertDatabaseCount('projets', 0);
    }

    // ── Mise à jour ──────────────────────────────────────────────────────

    public function test_le_manager_modifie_un_projet(): void
    {
        $manager = $this->createUserWithRole(Role::MANAGER);
        $projet  = Projet::factory()->create(['nom' => 'Ancien Nom']);

        $this->asManager($manager)
            ->from(route('manager.projets.edit', $projet))
            ->put(route('manager.projets.update', $projet), $this->validPayload([
                'nom'        => 'Nouveau Nom Mis À Jour',
                'progression'=> 75,
            ]))
            ->assertRedirect();

        $this->assertDatabaseHas('projets', [
            'id'          => $projet->id,
            'nom'         => 'Nouveau Nom Mis À Jour',
            'progression' => 75,
        ]);
    }

    public function test_la_modification_rejette_une_progression_hors_bornes(): void
    {
        $manager = $this->createUserWithRole(Role::MANAGER);
        $projet  = Projet::factory()->create(['progression' => 10]);

        $this->asManager($manager)
            ->from(route('manager.projets.edit', $projet))
            ->put(route('manager.projets.update', $projet), $this->validPayload([
                'progression' => 999,
            ]))
            ->assertSessionHasErrors('progression');

        $this->assertDatabaseHas('projets', ['id' => $projet->id, 'progression' => 10]);
    }

    // ── Suppression ──────────────────────────────────────────────────────

    public function test_le_manager_supprime_un_projet(): void
    {
        $manager = $this->createUserWithRole(Role::MANAGER);
        $projet  = Projet::factory()->create();

        $this->asManager($manager)
            ->from(route('manager.projets.index'))
            ->delete(route('manager.projets.destroy', $projet))
            ->assertRedirect();

        $this->assertDatabaseMissing('projets', ['id' => $projet->id]);
    }

    // ── RBAC ─────────────────────────────────────────────────────────────

    public function test_un_citoyen_ne_peut_pas_acceder_au_crud_projet(): void
    {
        $citoyen = $this->createUserWithRole(Role::CITOYEN);
        $projet  = Projet::factory()->create();

        $this->actingAs($citoyen)->get(route('manager.projets.index'))->assertForbidden();
        $this->actingAs($citoyen)->get(route('manager.projets.create'))->assertForbidden();
        $this->actingAs($citoyen)->get(route('manager.projets.edit', $projet))->assertForbidden();
        $this->actingAs($citoyen)
            ->delete(route('manager.projets.destroy', $projet))
            ->assertForbidden();

        $this->assertDatabaseHas('projets', ['id' => $projet->id]);
    }

    public function test_un_visiteur_non_connecte_est_redirige_vers_la_connexion(): void
    {
        Projet::factory()->create();

        $this->get(route('manager.projets.index'))
            ->assertRedirect(route('auth.login'));
    }
}
