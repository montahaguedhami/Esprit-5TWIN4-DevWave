<?php

namespace Tests\Feature;

use App\Models\Intervention;
use App\Models\Role;
use App\Models\Technicien;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TechnicienUserLinkTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->artisan('db:seed', ['--class' => 'RbacSeeder']);
    }

    private function userWithRole(string $roleSlug, array $attrs = []): User
    {
        $user = User::factory()->create(array_merge(['is_active' => true], $attrs));
        $user->assignRole($roleSlug);

        return $user->refresh()->load('roles');
    }

    private function loginAs(User $user): self
    {
        return $this->actingAs($user)->withSession([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->primary_role,
                'role_key' => $user->primary_role,
                'roles' => $user->getRoleSlugs(),
            ],
        ]);
    }

    public function test_linked_technician_only_sees_own_interventions(): void
    {
        $user = $this->userWithRole(Role::TECHNICIEN, ['name' => 'Sami Gharbi']);
        $sami = Technicien::factory()->create(['nom' => 'Sami Gharbi', 'user_id' => $user->id]);
        $autre = Technicien::factory()->create();

        Intervention::factory()->for($sami)->create(['description' => 'Réparation vanne secteur nord']);
        Intervention::factory()->for($autre)->create(['description' => 'Remplacement compteur secteur sud']);

        $this->loginAs($user)
            ->get(route('technician.interventions.index'))
            ->assertOk()
            ->assertSee('Réparation vanne secteur nord')
            ->assertDontSee('Remplacement compteur secteur sud');
    }

    public function test_technician_account_without_linked_fiche_is_forbidden(): void
    {
        $user = $this->userWithRole(Role::TECHNICIEN);

        $this->loginAs($user)
            ->get(route('technician.interventions.index'))
            ->assertForbidden();

        // Plus aucune fiche « de secours » n'est créée automatiquement
        $this->assertDatabaseCount('techniciens', 0);
    }

    public function test_same_name_is_not_enough_to_access_a_fiche(): void
    {
        $user = $this->userWithRole(Role::TECHNICIEN, ['name' => 'Karim Trabelsi']);
        $fiche = Technicien::factory()->create(['nom' => 'Karim Trabelsi']);
        Intervention::factory()->for($fiche)->create();

        $this->loginAs($user)
            ->get(route('technician.interventions.index'))
            ->assertForbidden();
    }

    public function test_manager_can_link_a_technician_account(): void
    {
        $manager = $this->userWithRole(Role::MANAGER);
        $compte = $this->userWithRole(Role::TECHNICIEN);

        $this->loginAs($manager)
            ->post(route('manager.techniciens.store'), [
                'user_id' => $compte->id,
                'nom' => 'Nour Ayari',
                'specialite' => 'Pompage',
                'telephone' => '+216 55 111 222',
                'disponibilite' => 'Disponible',
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('techniciens', ['nom' => 'Nour Ayari', 'user_id' => $compte->id]);
        $this->assertTrue($compte->fresh()->technicien->is(Technicien::where('nom', 'Nour Ayari')->first()));
    }

    public function test_link_rejects_non_technician_and_already_linked_accounts(): void
    {
        $manager = $this->userWithRole(Role::MANAGER);
        $citoyen = $this->userWithRole(Role::CITOYEN);
        $dejaLie = $this->userWithRole(Role::TECHNICIEN);
        Technicien::factory()->create(['user_id' => $dejaLie->id]);

        $donnees = [
            'nom' => 'Nour Ayari',
            'specialite' => 'Pompage',
            'telephone' => '+216 55 111 222',
            'disponibilite' => 'Disponible',
        ];

        $this->loginAs($manager)
            ->post(route('manager.techniciens.store'), $donnees + ['user_id' => $citoyen->id])
            ->assertSessionHasErrors(['user_id' => 'Le compte choisi doit avoir le rôle Technicien.']);

        $this->loginAs($manager)
            ->post(route('manager.techniciens.store'), $donnees + ['user_id' => $dejaLie->id])
            ->assertSessionHasErrors(['user_id' => 'Ce compte est déjà lié à une autre fiche technicien.']);

        $this->assertDatabaseMissing('techniciens', ['nom' => 'Nour Ayari']);
    }

    public function test_editing_keeps_the_current_link_valid(): void
    {
        $manager = $this->userWithRole(Role::MANAGER);
        $compte = $this->userWithRole(Role::TECHNICIEN);
        $fiche = Technicien::factory()->create(['user_id' => $compte->id, 'disponibilite' => 'Disponible']);

        $this->loginAs($manager)
            ->put(route('manager.techniciens.update', $fiche), [
                'user_id' => $compte->id,
                'nom' => $fiche->nom,
                'specialite' => $fiche->specialite,
                'telephone' => $fiche->telephone,
                'disponibilite' => 'En congé',
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('techniciens', ['id' => $fiche->id, 'user_id' => $compte->id, 'disponibilite' => 'En congé']);
    }
}
