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

        $this->assertSoftDeleted($zone);
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
            'etat' => 'En maintenance',
            'date_installation' => '2024-05-10',
            'zone_id' => $zone->id,
        ])->assertRedirect(route('manager.infrastructures.show', $infrastructure));

        $this->assertDatabaseHas('infrastructures', [
            'id' => $infrastructure->id,
            'nom' => 'Réservoir rénové',
            'etat' => 'En maintenance',
        ]);

        $this->delete(route('manager.infrastructures.destroy', $infrastructure))
            ->assertRedirect(route('manager.infrastructures.index'));

        $this->assertSoftDeleted($infrastructure);

        $this->delete(route('manager.zones.destroy', $zone))
            ->assertRedirect(route('manager.zones.index'));
        $this->assertSoftDeleted($zone);
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

    public function test_admin_can_manage_zones_and_infrastructures_from_the_admin_layout(): void
    {
        $this->withSession([
            'user' => [
                'name' => 'Administrateur',
                'email' => 'admin@aquasecure.tn',
                'role' => 'admin',
            ],
        ]);

        $this->get(route('admin.zones.index'))
            ->assertOk()
            ->assertSee('id="admin-sidebar"', false)
            ->assertSee(route('admin.infrastructures.index'))
            ->assertDontSee(route('manager.infrastructures.index'));

        $this->get(route('admin.infrastructures.index'))
            ->assertOk()
            ->assertSee('id="admin-sidebar"', false)
            ->assertSee(route('admin.zones.index'))
            ->assertDontSee(route('manager.zones.index'));

        $this->post(route('admin.zones.store'), [
            'nom' => 'Zone Admin',
            'description' => 'Secteur administré',
            'localisation' => 'Tunis',
        ])->assertRedirect(route('admin.zones.show', Zone::where('nom', 'Zone Admin')->firstOrFail()));

        $zone = Zone::where('nom', 'Zone Admin')->firstOrFail();
        $this->post(route('admin.infrastructures.store'), [
            'nom' => 'Canal Admin',
            'type' => 'Canalisation',
            'localisation' => 'Tunis centre',
            'etat' => 'En service',
            'date_installation' => '2024-05-10',
            'zone_id' => $zone->id,
        ])->assertRedirect(route('admin.infrastructures.show', Infrastructure::where('nom', 'Canal Admin')->firstOrFail()));

        $infrastructure = Infrastructure::where('nom', 'Canal Admin')->firstOrFail();
        $this->get(route('admin.zones.show', $zone))
            ->assertOk()
            ->assertSee(route('admin.infrastructures.show', $infrastructure))
            ->assertDontSee(route('manager.infrastructures.show', $infrastructure));
        $this->get(route('admin.infrastructures.show', $infrastructure))
            ->assertOk()
            ->assertSee(route('admin.zones.show', $zone))
            ->assertDontSee(route('manager.zones.show', $zone));
    }

    public function test_invalid_type_or_etat_is_rejected_with_french_message(): void
    {
        $zone = Zone::create(['nom' => 'Zone Test', 'localisation' => 'Tunis']);

        $response = $this->post(route('manager.infrastructures.store'), [
            'nom' => 'Invalide',
            'type' => 'TypeInconnu',
            'localisation' => 'Tunis',
            'etat' => 'EtatInconnu',
            'date_installation' => '2024-01-01',
            'zone_id' => $zone->id,
        ]);

        $response->assertSessionHasErrors([
            'type' => 'Le type d’infrastructure sélectionné n’est pas valide.',
            'etat' => 'L’état sélectionné n’est pas valide.',
        ]);
    }

    public function test_future_installation_date_is_rejected_with_french_message(): void
    {
        $zone = Zone::create(['nom' => 'Zone Test Date', 'localisation' => 'Tunis']);

        $response = $this->post(route('manager.infrastructures.store'), [
            'nom' => 'Futur',
            'type' => 'Canalisation',
            'localisation' => 'Tunis',
            'etat' => 'En service',
            'date_installation' => now()->addDays(5)->format('Y-m-d'),
            'zone_id' => $zone->id,
        ]);

        $response->assertSessionHasErrors([
            'date_installation' => 'La date d’installation ne peut pas être dans le futur.',
        ]);
    }

    public function test_infrastructure_name_must_be_unique_per_zone(): void
    {
        $zoneA = Zone::create(['nom' => 'Zone A', 'localisation' => 'Tunis']);
        $zoneB = Zone::create(['nom' => 'Zone B', 'localisation' => 'Sousse']);

        Infrastructure::create([
            'nom' => 'Station Centrale',
            'type' => 'Station de pompage',
            'localisation' => 'Tunis',
            'etat' => 'En service',
            'date_installation' => '2024-01-01',
            'zone_id' => $zoneA->id,
        ]);

        $this->post(route('manager.infrastructures.store'), [
            'nom' => 'Station Centrale',
            'type' => 'Station de pompage',
            'localisation' => 'Tunis',
            'etat' => 'En service',
            'date_installation' => '2024-01-01',
            'zone_id' => $zoneA->id,
        ])->assertSessionHasErrors([
            'nom' => 'Ce nom d’infrastructure est déjà utilisé dans cette zone.',
        ]);

        $this->post(route('manager.infrastructures.store'), [
            'nom' => 'Station Centrale',
            'type' => 'Station de pompage',
            'localisation' => 'Sousse',
            'etat' => 'En service',
            'date_installation' => '2024-01-01',
            'zone_id' => $zoneB->id,
        ])->assertRedirect();
    }

    public function test_zone_name_must_be_unique(): void
    {
        Zone::create(['nom' => 'Zone Doublon', 'localisation' => 'Tunis']);

        $this->post(route('manager.zones.store'), [
            'nom' => 'Zone Doublon',
            'localisation' => 'Autre',
        ])->assertSessionHasErrors([
            'nom' => 'Ce nom de zone est déjà utilisé.',
        ]);
    }

    public function test_zone_search_and_sort(): void
    {
        Zone::create(['nom' => 'Alpha Zone', 'localisation' => 'Tunis', 'description' => 'Nord']);
        Zone::create(['nom' => 'Omega Zone', 'localisation' => 'Sfax', 'description' => 'Sud']);

        $response = $this->get(route('manager.zones.index', ['q' => 'Alpha']));
        $response->assertOk()
            ->assertSee('Alpha Zone')
            ->assertDontSee('Omega Zone');

        $responseDesc = $this->get(route('manager.zones.index', ['sort' => 'nom', 'direction' => 'desc']));
        $responseDesc->assertOk()
            ->assertSeeInOrder(['Omega Zone', 'Alpha Zone']);
    }

    public function test_infrastructure_filters_combine_and_retain_query_strings(): void
    {
        $zone1 = Zone::create(['nom' => 'Zone 1', 'localisation' => 'Tunis']);
        $zone2 = Zone::create(['nom' => 'Zone 2', 'localisation' => 'Bizerte']);

        Infrastructure::create([
            'nom' => 'Canalisation Nord',
            'type' => 'Canalisation',
            'localisation' => 'Tunis',
            'etat' => 'En service',
            'date_installation' => '2023-01-01',
            'zone_id' => $zone1->id,
        ]);

        Infrastructure::create([
            'nom' => 'Réservoir Sud',
            'type' => 'Réservoir',
            'localisation' => 'Tunis',
            'etat' => 'En service',
            'date_installation' => '2023-01-02',
            'zone_id' => $zone1->id,
        ]);

        Infrastructure::create([
            'nom' => 'Canalisation Est',
            'type' => 'Canalisation',
            'localisation' => 'Bizerte',
            'etat' => 'En maintenance',
            'date_installation' => '2023-01-03',
            'zone_id' => $zone2->id,
        ]);

        $response = $this->get(route('manager.infrastructures.index', [
            'zone_id' => $zone1->id,
            'type' => 'Canalisation',
            'etat' => 'En service',
        ]));

        $response->assertOk()
            ->assertSee('Canalisation Nord')
            ->assertDontSee('Réservoir Sud')
            ->assertDontSee('Canalisation Est')
            ->assertSee('1 infrastructure trouvée');
    }

    public function test_invalid_sort_parameter_is_ignored_without_500_error(): void
    {
        $zone = Zone::create(['nom' => 'Zone Test Sort', 'localisation' => 'Tunis']);
        Infrastructure::create([
            'nom' => 'Infra Test',
            'type' => 'Canalisation',
            'localisation' => 'Tunis',
            'etat' => 'En service',
            'date_installation' => '2024-01-01',
            'zone_id' => $zone->id,
        ]);

        $response = $this->get(route('manager.infrastructures.index', [
            'sort' => 'malicious_column_drop_table',
            'direction' => 'invalid_direction',
        ]));

        $response->assertOk()
            ->assertSee('Infra Test');

        $responseZone = $this->get(route('manager.zones.index', [
            'sort' => 'hacked_column',
            'direction' => 'bad_dir',
        ]));

        $responseZone->assertOk()
            ->assertSee('Zone Test Sort');
    }

    public function test_zone_show_filters_infrastructures_by_etat(): void
    {
        $zone = Zone::create(['nom' => 'Zone Filtre', 'localisation' => 'Tunis']);

        Infrastructure::create([
            'nom' => 'Infra Active',
            'type' => 'Vanne',
            'localisation' => 'Tunis',
            'etat' => 'En service',
            'date_installation' => '2024-01-01',
            'zone_id' => $zone->id,
        ]);

        Infrastructure::create([
            'nom' => 'Infra En Panne',
            'type' => 'Vanne',
            'localisation' => 'Tunis',
            'etat' => 'Hors service',
            'date_installation' => '2024-01-01',
            'zone_id' => $zone->id,
        ]);

        $this->get(route('manager.zones.show', $zone))
            ->assertOk()
            ->assertSee('Infra Active')
            ->assertSee('Infra En Panne');

        $this->get(route('manager.zones.show', [$zone, 'etat' => 'Hors service']))
            ->assertOk()
            ->assertSee('Infra En Panne')
            ->assertDontSee('Infra Active');
    }

    public function test_no_n_plus_one_queries_on_infrastructure_index(): void
    {
        $zone = Zone::create(['nom' => 'Zone N+1', 'localisation' => 'Tunis']);

        for ($i = 1; $i <= 5; $i++) {
            Infrastructure::create([
                'nom' => "Infra {$i}",
                'type' => 'Canalisation',
                'localisation' => 'Tunis',
                'etat' => 'En service',
                'date_installation' => '2024-01-01',
                'zone_id' => $zone->id,
            ]);
        }

        \Illuminate\Support\Facades\DB::enableQueryLog();

        $this->get(route('manager.infrastructures.index'))->assertOk();

        $queries = \Illuminate\Support\Facades\DB::getQueryLog();
        \Illuminate\Support\Facades\DB::disableQueryLog();

        $this->assertLessThan(10, count($queries));
    }

    public function test_manager_can_update_etat_rapide_with_history(): void
    {
        $zone = Zone::create(['nom' => 'Zone Historique', 'localisation' => 'Tunis']);
        $infra = Infrastructure::create([
            'nom' => 'Pompe P1',
            'type' => 'Station de pompage',
            'localisation' => 'Tunis',
            'etat' => 'En service',
            'date_installation' => '2023-01-01',
            'zone_id' => $zone->id,
        ]);

        $this->patch(route('manager.infrastructures.etat', $infra), [
            'etat' => 'En maintenance',
            'commentaire' => 'Maintenance planifiée des roulements',
        ])->assertRedirect();

        $infra->refresh();
        $this->assertSame(\App\Enums\InfrastructureEtat::EnMaintenance, $infra->etat);

        $this->assertDatabaseHas('infrastructure_historiques', [
            'infrastructure_id' => $infra->id,
            'ancien_etat' => 'En service',
            'nouvel_etat' => 'En maintenance',
            'commentaire' => 'Maintenance planifiée des roulements',
        ]);
    }

    public function test_manager_can_resoudre_alerte(): void
    {
        $zone = Zone::create(['nom' => 'Zone Alerte', 'localisation' => 'Tunis']);
        $infra = Infrastructure::create([
            'nom' => 'Vanne V1',
            'type' => 'Vanne',
            'localisation' => 'Tunis',
            'etat' => 'En service',
            'date_installation' => '2023-01-01',
            'zone_id' => $zone->id,
        ]);

        $alerte = \App\Models\InfrastructureAlerte::create([
            'infrastructure_id' => $infra->id,
            'type' => 'retard',
            'message' => 'Test alerte maintenance',
            'resolue' => false,
        ]);

        $this->patch(route('manager.infrastructures.alertes.resoudre', $alerte))
            ->assertRedirect();

        $this->assertTrue($alerte->fresh()->resolue);
    }

    public function test_zone_stats_service_computes_stats(): void
    {
        $zone = Zone::create(['nom' => 'Zone Stats Test', 'localisation' => 'Tunis']);
        Infrastructure::create([
            'nom' => 'Infra Stats 1',
            'type' => 'Réservoir',
            'localisation' => 'Tunis',
            'etat' => 'En service',
            'date_installation' => '2022-01-01',
            'zone_id' => $zone->id,
        ]);

        $service = app(\App\Services\ZoneStatsService::class);
        $statsZone = $service->statsZone($zone);
        $this->assertSame(1, $statsZone['total']);
        $this->assertSame(100.0, (float) $statsZone['pourcentage_en_service']);

        $statsGlobales = $service->statsGlobales();
        $this->assertGreaterThanOrEqual(1, $statsGlobales['total_infrastructures']);
    }

    public function test_zone_and_infrastructure_form_input_validation_controls(): void
    {
        $zone = Zone::create(['nom' => 'Zone Validation', 'localisation' => 'Tunis']);

        // Zone nom trop court (< 3 caractères)
        $this->post(route('manager.zones.store'), [
            'nom' => 'AB',
            'localisation' => 'Tunis',
        ])->assertSessionHasErrors(['nom']);

        // Zone localisation trop courte (< 2 caractères)
        $this->post(route('manager.zones.store'), [
            'nom' => 'Zone Correcte',
            'localisation' => 'T',
        ])->assertSessionHasErrors(['localisation']);

        // Infrastructure nom trop court (< 3 caractères)
        $this->post(route('manager.infrastructures.store'), [
            'nom' => 'AB',
            'type' => 'Réservoir',
            'localisation' => 'Tunis Centre',
            'etat' => 'En service',
            'date_installation' => now()->subYear()->format('Y-m-d'),
            'zone_id' => $zone->id,
        ])->assertSessionHasErrors(['nom']);

        // Infrastructure durée de vie excessive (> 150 ans)
        $this->post(route('manager.infrastructures.store'), [
            'nom' => 'Réservoir Valide',
            'type' => 'Réservoir',
            'localisation' => 'Tunis Centre',
            'etat' => 'En service',
            'date_installation' => now()->subYear()->format('Y-m-d'),
            'zone_id' => $zone->id,
            'duree_vie_annees' => 250,
        ])->assertSessionHasErrors(['duree_vie_annees']);

        // Infrastructure date prochaine maintenance antérieure à la dernière maintenance
        $this->post(route('manager.infrastructures.store'), [
            'nom' => 'Réservoir Maintenance',
            'type' => 'Réservoir',
            'localisation' => 'Tunis Centre',
            'etat' => 'En service',
            'date_installation' => now()->subYears(2)->format('Y-m-d'),
            'zone_id' => $zone->id,
            'date_derniere_maintenance' => now()->subDays(10)->format('Y-m-d'),
            'date_prochaine_maintenance' => now()->subDays(20)->format('Y-m-d'),
        ])->assertSessionHasErrors(['date_prochaine_maintenance']);
    }
}
