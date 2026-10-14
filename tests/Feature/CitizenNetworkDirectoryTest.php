<?php

namespace Tests\Feature;

use App\Enums\InfrastructureEtat;
use App\Enums\InfrastructureType;
use App\Enums\ZoneStatut;
use App\Models\Infrastructure;
use App\Models\Zone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CitizenNetworkDirectoryTest extends TestCase
{
    use RefreshDatabase;

    private function asCitizen(): static
    {
        return $this->withSession([
            'user' => [
                'name' => 'Yassine Hamdi',
                'email' => 'citoyen@aquasecure.tn',
                'role' => 'citizen',
            ],
        ]);
    }

    public function test_only_citizens_can_open_the_network_directory(): void
    {
        $this->get(route('citizen.zones.index'))->assertRedirect(route('auth.login'));

        $this->withSession(['user' => ['name' => 'Gestionnaire', 'role' => 'manager']])
            ->get(route('citizen.infrastructures.index'))
            ->assertForbidden();

        $this->asCitizen()
            ->get(route('citizen.infrastructures.index'))
            ->assertOk();
    }

    public function test_zone_directory_lists_active_zones_and_hides_inactive_zone_details(): void
    {
        $active = Zone::factory()->create([
            'nom' => 'Zone publique test',
            'statut' => ZoneStatut::Active,
        ]);
        $inactive = Zone::factory()->create([
            'nom' => 'Zone inactive confidentielle',
            'statut' => ZoneStatut::Inactive,
        ]);

        $this->asCitizen()
            ->get(route('citizen.zones.index'))
            ->assertOk()
            ->assertSee('Zone publique test')
            ->assertDontSee('Zone inactive confidentielle');

        $this->get(route('citizen.zones.show', $active))->assertOk();
        $this->get(route('citizen.zones.show', $inactive))->assertNotFound();
    }

    public function test_citizens_can_search_public_equipment_without_internal_inventory_fields(): void
    {
        $zone = Zone::factory()->create(['statut' => ZoneStatut::Active]);
        $infrastructure = Infrastructure::factory()->for($zone)->create([
            'nom' => 'Réservoir public',
            'type' => InfrastructureType::Reservoir,
            'etat' => InfrastructureEtat::EnService,
            'reference' => 'INTERNAL-REF-991',
            'fabricant' => 'Fabricant privé',
        ]);

        $this->asCitizen()
            ->get(route('citizen.infrastructures.index', ['q' => 'Réservoir public']))
            ->assertOk()
            ->assertSee('Réservoir public')
            ->assertDontSee('INTERNAL-REF-991')
            ->assertDontSee('Fabricant privé');

        $this->get(route('citizen.infrastructures.show', $infrastructure))
            ->assertOk()
            ->assertSee('Réservoir public')
            ->assertSee($zone->nom)
            ->assertDontSee('INTERNAL-REF-991')
            ->assertDontSee('Fabricant privé')
            ->assertDontSee('capacite');
    }

    public function test_infrastructure_in_an_inactive_zone_is_not_visible_to_citizens(): void
    {
        $zone = Zone::factory()->create(['statut' => ZoneStatut::Inactive]);
        $infrastructure = Infrastructure::factory()->for($zone)->create();

        $this->asCitizen()
            ->get(route('citizen.infrastructures.index'))
            ->assertOk()
            ->assertDontSee($infrastructure->nom);

        $this->get(route('citizen.infrastructures.show', $infrastructure))->assertNotFound();
    }
}
