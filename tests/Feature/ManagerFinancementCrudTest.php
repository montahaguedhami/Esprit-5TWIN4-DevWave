<?php

namespace Tests\Feature;

use App\Models\Financement;
use App\Models\Projet;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithRbac;
use Tests\TestCase;

/**
 * Tests du CRUD gestionnaire des financements (module Projets et Financements).
 *
 * Comme pour les projets, les contrôleurs manager exigent la clé de SESSION
 * `user.role` (`Role::sessionMatches`) : la session est peuplée en plus de
 * `actingAs()`.
 */
class ManagerFinancementCrudTest extends TestCase
{
    use RefreshDatabase;
    use InteractsWithRbac;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->seedRbac();
    }

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

    /** @return array<string, mixed> */
    private function validPayload(Projet $projet, array $overrides = []): array
    {
        return array_merge([
            'projet_id'        => $projet->id,
            'source'           => 'Banque Mondiale',
            'montant'          => 150000,
            'date_financement' => '2026-03-15',
        ], $overrides);
    }

    // ── Lecture ──────────────────────────────────────────────────────────

    public function test_le_manager_voit_la_liste_des_financements(): void
    {
        $manager     = $this->createUserWithRole(Role::MANAGER);
        $projet      = Projet::factory()->create();
        $financement = Financement::factory()->create([
            'projet_id' => $projet->id,
            'source'    => 'Source Visible Liste Test',
        ]);

        $this->asManager($manager)
            ->get(route('manager.financements.index'))
            ->assertOk()
            ->assertSee('Source Visible Liste Test');
    }

    public function test_le_manager_ouvre_le_formulaire_de_creation_avec_les_projets(): void
    {
        $manager = $this->createUserWithRole(Role::MANAGER);
        Projet::factory()->create();

        $this->asManager($manager)
            ->get(route('manager.financements.create'))
            ->assertOk();
    }

    public function test_le_manager_consulte_les_detail_d_un_financement(): void
    {
        $manager     = $this->createUserWithRole(Role::MANAGER);
        $financement = Financement::factory()->create(['source' => 'AFD Détail Test']);

        $this->asManager($manager)
            ->get(route('manager.financements.show', $financement))
            ->assertOk()
            ->assertSee('AFD Détail Test');
    }

    public function test_le_manager_ouvre_le_formulaire_de_modification(): void
    {
        $manager     = $this->createUserWithRole(Role::MANAGER);
        $financement = Financement::factory()->create();

        $this->asManager($manager)
            ->get(route('manager.financements.edit', $financement))
            ->assertOk();
    }

    // ── Création ─────────────────────────────────────────────────────────

    public function test_le_manager_peut_creer_un_financement(): void
    {
        $manager = $this->createUserWithRole(Role::MANAGER);
        $projet  = Projet::factory()->create();

        $this->asManager($manager)
            ->from(route('manager.financements.create'))
            ->post(route('manager.financements.store'), $this->validPayload($projet, [
                'source' => 'Financement Créé Par Test',
                'montant'=> 88000,
            ]))
            ->assertRedirect();

        $this->assertDatabaseHas('financements', [
            'projet_id' => $projet->id,
            'source'    => 'Financement Créé Par Test',
            'montant'   => 88000,
        ]);
    }

    public function test_la_creation_rejette_un_projet_inexistant(): void
    {
        $manager = $this->createUserWithRole(Role::MANAGER);

        $this->asManager($manager)
            ->from(route('manager.financements.create'))
            ->post(route('manager.financements.store'), [
                'projet_id'        => 999999,          // exists:projets,id
                'source'           => 'Inconnue',
                'montant'          => 1000,
                'date_financement' => '2026-03-15',
            ])
            ->assertRedirect(route('manager.financements.create'))
            ->assertSessionHasErrors('projet_id');

        $this->assertDatabaseCount('financements', 0);
    }

    public function test_la_creation_rejette_un_montant_negatif(): void
    {
        $manager = $this->createUserWithRole(Role::MANAGER);
        $projet  = Projet::factory()->create();

        $this->asManager($manager)
            ->from(route('manager.financements.create'))
            ->post(route('manager.financements.store'), $this->validPayload($projet, [
                'montant' => -100,
            ]))
            ->assertSessionHasErrors('montant');

        $this->assertDatabaseCount('financements', 0);
    }

    // ── Mise à jour ──────────────────────────────────────────────────────

    public function test_le_manager_modifie_un_financement(): void
    {
        $manager     = $this->createUserWithRole(Role::MANAGER);
        $projet      = Projet::factory()->create();
        $financement = Financement::factory()->create([
            'projet_id' => $projet->id,
            'source'    => 'Ancienne Source',
            'montant'   => 10000,
        ]);

        $this->asManager($manager)
            ->from(route('manager.financements.edit', $financement))
            ->put(route('manager.financements.update', $financement), $this->validPayload($projet, [
                'source' => 'Nouvelle Source Mise À Jour',
                'montant'=> 99000,
            ]))
            ->assertRedirect();

        $this->assertDatabaseHas('financements', [
            'id'     => $financement->id,
            'source' => 'Nouvelle Source Mise À Jour',
            'montant'=> 99000,
        ]);
    }

    // ── Suppression ──────────────────────────────────────────────────────

    public function test_le_manager_supprime_un_financement(): void
    {
        $manager     = $this->createUserWithRole(Role::MANAGER);
        $financement = Financement::factory()->create();

        $this->asManager($manager)
            ->from(route('manager.financements.index'))
            ->delete(route('manager.financements.destroy', $financement))
            ->assertRedirect();

        $this->assertDatabaseMissing('financements', ['id' => $financement->id]);
    }

    // ── RBAC ─────────────────────────────────────────────────────────────

    public function test_un_citoyen_ne_peut_pas_acceder_au_crud_financement(): void
    {
        $citoyen     = $this->createUserWithRole(Role::CITOYEN);
        $financement = Financement::factory()->create();

        $this->actingAs($citoyen)->get(route('manager.financements.index'))->assertForbidden();
        $this->actingAs($citoyen)->get(route('manager.financements.create'))->assertForbidden();
        $this->actingAs($citoyen)->get(route('manager.financements.edit', $financement))->assertForbidden();
        $this->actingAs($citoyen)
            ->delete(route('manager.financements.destroy', $financement))
            ->assertForbidden();

        $this->assertDatabaseHas('financements', ['id' => $financement->id]);
    }
}
