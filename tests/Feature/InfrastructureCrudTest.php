<?php

namespace Tests\Feature;

use App\Models\Infrastructure;
use App\Models\Zone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InfrastructureCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withSession([
            'user' => [
                'name' => 'Gestionnaire',
                'email' => 'gestionnaire@aquasecure.tn',
                'role' => 'manager',
            ],
        ]);
    }

    public function test_manager_can_create_update_and_delete_a_zone(): void
    {
        $this->get(route('manager.zones.index'))->assertOk();
        $this->get(route('manager.zones.create'))->assertOk();

        $this->post(route('manager.zones.store'), [
            'nom' => 'Zone Nord',
            'description' => 'Secteur nord',
            'localisation' => 'Tunis',
        ])->assertRedirect();

        $zone = Zone::where('nom', 'Zone Nord')->firstOrFail();
        $this->get(route('manager.zones.show', $zone))->assertOk();
        $this->get(route('manager.zones.edit', $zone))->assertOk();

        $this->put(route('manager.zones.update', $zone), [
            'nom' => 'Zone Nord rénovée',
            'description' => 'Secteur nord mis à jour',
            'localisation' => 'Ariana',
        ])->assertRedirect(route('manager.zones.show', $zone));

        $this->assertDatabaseHas('zones', ['id' => $zone->id, 'nom' => 'Zone Nord rénovée']);

        $this->delete(route('manager.zones.destroy', $zone))
            ->assertRedirect(route('manager.zones.index'));

        $this->assertDatabaseMissing('zones', ['id' => $zone->id]);
    }

    public function test_manager_can_manage_infrastructure_and_zone_deletion_is_restricted(): void
    {
        $zone = Zone::create([
            'nom' => 'Zone Sud',
            'description' => null,
            'localisation' => 'Sfax',
        ]);

        $this->get(route('manager.infrastructures.index'))->assertOk();
        $this->get(route('manager.infrastructures.create'))->assertOk();

        $this->post(route('manager.infrastructures.store'), [
            'nom' => 'Réservoir principal',
            'type' => 'Réservoir',
            'localisation' => 'Sfax centre',
            'etat' => 'En service',
            'date_installation' => '2024-05-10',
            'zone_id' => $zone->id,
        ])->assertRedirect();

        $infrastructure = Infrastructure::where('nom', 'Réservoir principal')->firstOrFail();
        $this->get(route('manager.infrastructures.show', $infrastructure))->assertOk();
        $this->get(route('manager.infrastructures.edit', $infrastructure))->assertOk();
        $this->get(route('manager.zones.show', $zone))->assertSee('Réservoir principal');

        $this->delete(route('manager.zones.destroy', $zone))
            ->assertRedirect(route('manager.zones.index'))
            ->assertSessionHas('error');
        $this->assertDatabaseHas('zones', ['id' => $zone->id]);

        $this->put(route('manager.infrastructures.update', $infrastructure), [
            'nom' => 'Réservoir rénové',
            'type' => 'Réservoir',
            'localisation' => 'Sfax centre',
            'etat' => 'Maintenance',
            'date_installation' => '2024-05-10',
            'zone_id' => $zone->id,
        ])->assertRedirect(route('manager.infrastructures.show', $infrastructure));

        $this->assertDatabaseHas('infrastructures', [
            'id' => $infrastructure->id,
            'nom' => 'Réservoir rénové',
            'etat' => 'Maintenance',
        ]);

        $this->delete(route('manager.infrastructures.destroy', $infrastructure))
            ->assertRedirect(route('manager.infrastructures.index'));

        $this->assertDatabaseMissing('infrastructures', ['id' => $infrastructure->id]);

        $this->delete(route('manager.zones.destroy', $zone))
            ->assertRedirect(route('manager.zones.index'));
        $this->assertDatabaseMissing('zones', ['id' => $zone->id]);
    }

    public function test_infrastructure_requires_an_existing_zone(): void
    {
        $this->post(route('manager.infrastructures.store'), [
            'nom' => 'Canal',
            'type' => 'Canalisation',
            'localisation' => 'Tunis',
            'etat' => 'En service',
            'date_installation' => '2024-05-10',
            'zone_id' => 999,
        ])->assertSessionHasErrors('zone_id');

        $this->assertDatabaseCount('infrastructures', 0);
    }

    public function test_crud_pages_require_manager_or_admin_role(): void
    {
        $this->withSession(['user' => ['name' => 'Citizen', 'role' => 'citizen']])
            ->get(route('manager.zones.index'))
            ->assertForbidden();

        $this->flushSession()
            ->get(route('manager.infrastructures.index'))
            ->assertRedirect(route('auth.login'));
    }
}
