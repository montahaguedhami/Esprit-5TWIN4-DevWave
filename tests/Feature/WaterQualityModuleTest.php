<?php

namespace Tests\Feature;

use App\Models\MesureQualite;
use App\Models\PointMesure;
use App\Models\Role;
use App\Services\WaterQualityService;
use Database\Seeders\WaterQualitySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithRbac;
use Tests\TestCase;

class WaterQualityModuleTest extends TestCase
{
    use RefreshDatabase;
    use InteractsWithRbac;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->seedRbac();
    }

    public function test_quality_and_map_pages_render_from_the_database(): void
    {
        $this->seed(WaterQualitySeeder::class);
        $manager = $this->createUserWithRole(Role::MANAGER);

        $this->actingAs($manager)
            ->get(route('manager.quality'))
            ->assertOk()
            ->assertSee('WQR-2026-108')
            ->assertSee('12.4 mg/L')
            ->assertDontSee('aq-accessibility-fab')
            ->assertSee('aq-topbar-acc-btn')
            ->assertSee('openAccessibilityModal()')
            ->assertSee('aq-accessibility-modal');

        $this->actingAs($manager)
            ->get(route('manager.map'))
            ->assertOk()
            ->assertSee('TUN-BEL-04');

        $point = PointMesure::firstOrFail();
        $measurement = MesureQualite::firstOrFail();

        $this->actingAs($manager)->get(route('manager.points-mesure.index'))->assertOk();
        $this->actingAs($manager)->get(route('manager.points-mesure.create'))->assertOk();
        $this->actingAs($manager)->get(route('manager.points-mesure.show', $point))->assertOk();
        $this->actingAs($manager)->get(route('manager.points-mesure.edit', $point))->assertOk();
        $this->actingAs($manager)->get(route('manager.quality.mesures.show', $measurement))->assertOk();
        $this->actingAs($manager)->get(route('manager.quality.mesures.edit', $measurement))->assertOk();

        // The create form is behind role:manager; a manager with create permission can access it
        // (users with both manager and technician roles have measurements.create)
        $managerWithCreate = $this->createUserWithRole(Role::MANAGER);
        $managerWithCreate->assignRole(Role::TECHNICIEN);
        $managerWithCreate = $managerWithCreate->fresh()->load('roles');
        $this->actingAs($managerWithCreate)->get(route('manager.quality.mesures.create'))->assertOk();
    }

    public function test_technician_can_create_measurement_and_manager_can_update_validate_delete(): void
    {
        // A user with both manager (web access) and technician (measurements.create) roles
        $creator = $this->createUserWithRole(Role::MANAGER);
        $creator->assignRole(Role::TECHNICIEN);
        $creator = $creator->fresh()->load('roles');

        $manager = $this->createUserWithRole(Role::MANAGER);

        // Manager creates a point (no policy on PointMesureController, role:manager middleware)
        $this->actingAs($manager)
            ->post(route('manager.points-mesure.store'), [
                'code'      => 'TST-001',
                'nom'       => 'Point de test',
                'zone'      => 'Tunis',
                'latitude'  => 36.8,
                'longitude' => 10.1,
                'type'      => 'Station',
                'statut'    => 'actif',
            ])->assertRedirect();

        $point = PointMesure::firstOrFail();

        // User with manager+technician roles creates a measurement (measurements.create permission)
        $this->actingAs($creator)
            ->post(route('manager.quality.mesures.store'), [
                'reference'       => 'TEST-001',
                'point_mesure_id' => $point->id,
                'date_mesure'     => '2026-10-08 10:00:00',
                'ph'              => 7.2,
                'turbidite'       => 1.4,
                'chlore_residuel' => 0.8,
                'plomb'           => 2,
                'nitrates'        => 20,
                'source_donnee'   => 'manuel',
                'is_verified'     => 1,
            ])->assertRedirect(route('manager.quality'));

        $measurement = MesureQualite::firstOrFail();
        $this->assertSame('Alert', $measurement->status);
        $this->assertSame('90.00', $measurement->overall_compliance);
        $this->assertSame($point->id, $measurement->point_mesure_id);

        // Manager updates the measurement (measurements.update permission)
        $this->actingAs($manager)
            ->put(route('manager.quality.mesures.update', $measurement), [
                'reference'       => 'TEST-001',
                'point_mesure_id' => $point->id,
                'date_mesure'     => '2026-10-08 11:00:00',
                'ph'              => 7.3,
                'turbidite'       => 0.9,
                'chlore_residuel' => 0.8,
                'plomb'           => 2,
                'nitrates'        => 20,
                'source_donnee'   => 'manuel',
                'is_verified'     => 1,
            ])->assertRedirect(route('manager.quality'));

        $this->assertDatabaseHas('mesures_qualites', ['id' => $measurement->id, 'status' => 'Compliant']);

        // Manager deactivates point (has measurements → deactivated instead of deleted)
        $this->actingAs($manager)
            ->delete(route('manager.points-mesure.destroy', $point))
            ->assertRedirect(route('manager.points-mesure.index'));
        $this->assertDatabaseHas('point_mesures', ['id' => $point->id, 'statut' => 'inactif']);

        // Manager deletes measurement (measurements.delete permission)
        $this->actingAs($manager)
            ->delete(route('manager.quality.mesures.destroy', $measurement))
            ->assertRedirect(route('manager.quality'));
        $this->assertDatabaseMissing('mesures_qualites', ['id' => $measurement->id]);
    }

    public function test_measurement_validation_rejects_impossible_values(): void
    {
        // A user with web access (manager) and create permission (technician role)
        $creator = $this->createUserWithRole(Role::MANAGER);
        $creator->assignRole(Role::TECHNICIEN);
        $creator = $creator->fresh()->load('roles');

        $point = PointMesure::create([
            'code'   => 'TST-002',
            'nom'    => 'Point de validation',
            'type'   => 'Puits',
            'statut' => 'actif',
        ]);

        $this->actingAs($creator)
            ->from(route('manager.quality.mesures.create'))
            ->post(route('manager.quality.mesures.store'), [
                'reference'       => 'TEST-INVALID',
                'point_mesure_id' => $point->id,
                'date_mesure'     => '2026-10-08 10:00:00',
                'ph'              => 15,
                'turbidite'       => -1,
                'chlore_residuel' => 0.5,
                'plomb'           => 1,
                'nitrates'        => 10,
                'source_donnee'   => 'manuel',
                'is_verified'     => 0,
            ])
            ->assertRedirect(route('manager.quality.mesures.create'))
            ->assertSessionHasErrors(['ph', 'turbidite']);

        $this->assertDatabaseMissing('mesures_qualites', ['reference' => 'TEST-INVALID']);
    }

    public function test_manager_cannot_create_measurement_policy_enforced(): void
    {
        $manager = $this->createUserWithRole(Role::MANAGER);

        $point = PointMesure::create([
            'code'   => 'TST-003',
            'nom'    => 'Point manager test',
            'type'   => 'Station',
            'statut' => 'actif',
        ]);

        $this->actingAs($manager)
            ->post(route('manager.quality.mesures.store'), [
                'reference'       => 'TEST-MANAGER-CREATE',
                'point_mesure_id' => $point->id,
                'date_mesure'     => '2026-10-08 10:00:00',
                'ph'              => 7.2,
                'turbidite'       => 0.5,
                'chlore_residuel' => 0.8,
                'plomb'           => 2,
                'nitrates'        => 10,
                'source_donnee'   => 'manuel',
                'is_verified'     => 0,
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('mesures_qualites', ['reference' => 'TEST-MANAGER-CREATE']);
    }

    public function test_citizen_cannot_create_update_or_delete_measurement(): void
    {
        $citizen = $this->createUserWithRole(Role::CITOYEN);

        $point = PointMesure::create([
            'code'   => 'TST-004',
            'nom'    => 'Point citoyen test',
            'type'   => 'Station',
            'statut' => 'actif',
        ]);

        $mesure = MesureQualite::create([
            'reference'          => 'MES-CITIZEN-TEST',
            'point_mesure_id'    => $point->id,
            'date_mesure'        => now(),
            'ph'                 => 7.2,
            'turbidite'          => 0.5,
            'chlore_residuel'    => 0.8,
            'plomb'              => 2,
            'nitrates'           => 10,
            'is_verified'        => false,
            'overall_compliance' => 100,
            'status'             => 'Compliant',
        ]);

        // Citizen cannot access manager routes (role:manager middleware returns 403)
        $this->actingAs($citizen)
            ->post(route('manager.quality.mesures.store'), [])
            ->assertForbidden();

        $this->actingAs($citizen)
            ->delete(route('manager.quality.mesures.destroy', $mesure))
            ->assertForbidden();
    }

    public function test_fk_restrict_prevents_deleting_point_with_measurements(): void
    {
        $manager = $this->createUserWithRole(Role::MANAGER);

        $point = PointMesure::create([
            'code'   => 'TST-005',
            'nom'    => 'Point avec mesures',
            'type'   => 'Station',
            'statut' => 'actif',
        ]);

        MesureQualite::create([
            'reference'          => 'MES-FK-TEST',
            'point_mesure_id'    => $point->id,
            'date_mesure'        => now(),
            'ph'                 => 7.2,
            'turbidite'          => 0.5,
            'chlore_residuel'    => 0.8,
            'plomb'              => 2,
            'nitrates'           => 10,
            'is_verified'        => false,
            'overall_compliance' => 100,
            'status'             => 'Compliant',
        ]);

        // PointMesureController::destroy() deactivates instead of deleting when measurements exist
        $this->actingAs($manager)
            ->delete(route('manager.points-mesure.destroy', $point))
            ->assertRedirect(route('manager.points-mesure.index'));

        // Point must still exist (deactivated, not deleted)
        $this->assertDatabaseHas('point_mesures', ['id' => $point->id, 'statut' => 'inactif']);
        // Measurement must still exist (not deleted)
        $this->assertDatabaseHas('mesures_qualites', ['reference' => 'MES-FK-TEST']);
    }

    public function test_source_donnee_is_stored_and_seeder_marks_simule(): void
    {
        // A user with create permission
        $creator = $this->createUserWithRole(Role::MANAGER);
        $creator->assignRole(Role::TECHNICIEN);
        $creator = $creator->fresh()->load('roles');

        $point = PointMesure::create([
            'code'   => 'TST-SRC',
            'nom'    => 'Point source test',
            'type'   => 'Station',
            'statut' => 'actif',
        ]);

        // Store with source_donnee = 'laboratoire'
        $this->actingAs($creator)
            ->post(route('manager.quality.mesures.store'), [
                'reference'       => 'SRC-LAB-001',
                'point_mesure_id' => $point->id,
                'date_mesure'     => '2026-10-10 09:00:00',
                'ph'              => 7.1,
                'turbidite'       => 0.3,
                'chlore_residuel' => 0.9,
                'plomb'           => 1.5,
                'nitrates'        => 8,
                'source_donnee'   => 'laboratoire',
                'is_verified'     => 0,
            ])->assertRedirect(route('manager.quality'));

        $this->assertDatabaseHas('mesures_qualites', [
            'reference'    => 'SRC-LAB-001',
            'source_donnee' => 'laboratoire',
        ]);

        // Store with source_donnee = 'manuel'
        $this->actingAs($creator)
            ->post(route('manager.quality.mesures.store'), [
                'reference'       => 'SRC-MAN-001',
                'point_mesure_id' => $point->id,
                'date_mesure'     => '2026-10-10 10:00:00',
                'ph'              => 7.2,
                'turbidite'       => 0.4,
                'chlore_residuel' => 0.8,
                'plomb'           => 2.0,
                'nitrates'        => 9,
                'source_donnee'   => 'manuel',
                'is_verified'     => 0,
            ])->assertRedirect(route('manager.quality'));

        $this->assertDatabaseHas('mesures_qualites', [
            'reference'    => 'SRC-MAN-001',
            'source_donnee' => 'manuel',
        ]);

        // Invalid source_donnee value is rejected
        $this->actingAs($creator)
            ->from(route('manager.quality.mesures.create'))
            ->post(route('manager.quality.mesures.store'), [
                'reference'       => 'SRC-BAD-001',
                'point_mesure_id' => $point->id,
                'date_mesure'     => '2026-10-10 11:00:00',
                'ph'              => 7.2,
                'turbidite'       => 0.4,
                'chlore_residuel' => 0.8,
                'plomb'           => 2.0,
                'nitrates'        => 9,
                'source_donnee'   => 'inconnu',
                'is_verified'     => 0,
            ])->assertRedirect(route('manager.quality.mesures.create'))
              ->assertSessionHasErrors(['source_donnee']);

        $this->assertDatabaseMissing('mesures_qualites', ['reference' => 'SRC-BAD-001']);

        // Seeder marks its data as 'simule'
        $this->seed(\Database\Seeders\WaterQualitySeeder::class);
        $this->assertDatabaseHas('mesures_qualites', [
            'reference'    => 'WQR-2026-108',
            'source_donnee' => 'simule',
        ]);
    }

    public function test_nitrates_threshold_is_optional_and_configurable(): void
    {
        $service = app(WaterQualityService::class);
        $values  = [
            'ph'              => 7.2,
            'turbidite'       => 0.5,
            'chlore_residuel' => 0.8,
            'plomb'           => 2,
            'nitrates'        => 25,
        ];

        config(['water_quality.nitrates_max' => null]);
        $unconfigured = $service->assess($values);
        $this->assertSame('Compliant', $unconfigured['status']);
        $this->assertArrayNotHasKey('nitrates', $unconfigured['parameter_statuses']);

        config(['water_quality.nitrates_max' => 30]);
        $this->assertSame('Compliant', $service->assess($values)['parameter_statuses']['nitrates']);

        config(['water_quality.nitrates_max' => 20]);
        $assessment = $service->assess($values);
        $this->assertSame('Non-Compliant', $assessment['status']);
        $this->assertSame(80.0, $assessment['overall_compliance']);
    }

    /** POST without source_donnee must be rejected (required rule). */
    public function test_source_donnee_is_required_on_store(): void
    {
        $creator = $this->createUserWithRole(Role::MANAGER);
        $creator->assignRole(Role::TECHNICIEN);
        $creator = $creator->fresh()->load('roles');

        $point = PointMesure::create([
            'code'   => 'TST-SRC-REQ',
            'nom'    => 'Point src requis',
            'type'   => 'Station',
            'statut' => 'actif',
        ]);

        $this->actingAs($creator)
            ->from(route('manager.quality.mesures.create'))
            ->post(route('manager.quality.mesures.store'), [
                'reference'       => 'SRC-MISSING',
                'point_mesure_id' => $point->id,
                'date_mesure'     => '2026-10-12 09:00:00',
                'ph'              => 7.0,
                'turbidite'       => 0.5,
                'chlore_residuel' => 0.8,
                'plomb'           => 2,
                'nitrates'        => 10,
                // source_donnee intentionally omitted
                'is_verified'     => 0,
            ])
            ->assertRedirect(route('manager.quality.mesures.create'))
            ->assertSessionHasErrors(['source_donnee']);

        $this->assertDatabaseMissing('mesures_qualites', ['reference' => 'SRC-MISSING']);
    }

    /** POST with an unrecognised source_donnee value must be rejected. */
    public function test_source_donnee_rejects_invalid_value(): void
    {
        $creator = $this->createUserWithRole(Role::MANAGER);
        $creator->assignRole(Role::TECHNICIEN);
        $creator = $creator->fresh()->load('roles');

        $point = PointMesure::create([
            'code'   => 'TST-SRC-BAD',
            'nom'    => 'Point src invalide',
            'type'   => 'Station',
            'statut' => 'actif',
        ]);

        $this->actingAs($creator)
            ->from(route('manager.quality.mesures.create'))
            ->post(route('manager.quality.mesures.store'), [
                'reference'       => 'SRC-INVALIDE',
                'point_mesure_id' => $point->id,
                'date_mesure'     => '2026-10-12 10:00:00',
                'ph'              => 7.0,
                'turbidite'       => 0.5,
                'chlore_residuel' => 0.8,
                'plomb'           => 2,
                'nitrates'        => 10,
                'source_donnee'   => 'invalide',
                'is_verified'     => 0,
            ])
            ->assertRedirect(route('manager.quality.mesures.create'))
            ->assertSessionHasErrors(['source_donnee']);

        $this->assertDatabaseMissing('mesures_qualites', ['reference' => 'SRC-INVALIDE']);
    }

    /** Pagination: 30 measurements → index renders pagination links. */
    public function test_index_paginates_measurements(): void
    {
        $manager = $this->createUserWithRole(Role::MANAGER);

        $point = PointMesure::create([
            'code'   => 'TST-PAG',
            'nom'    => 'Point pagination',
            'type'   => 'Station',
            'statut' => 'actif',
        ]);

        // Insert 30 measurements directly (page size is 25 → forces 2 pages)
        for ($i = 1; $i <= 30; $i++) {
            MesureQualite::create([
                'reference'          => sprintf('PAG-%03d', $i),
                'point_mesure_id'    => $point->id,
                'date_mesure'        => now()->subMinutes($i),
                'ph'                 => 7.0,
                'turbidite'          => 0.5,
                'chlore_residuel'    => 0.8,
                'plomb'              => 2,
                'nitrates'           => 10,
                'source_donnee'      => 'simule',
                'is_verified'        => false,
                'overall_compliance' => 100,
                'status'             => 'Compliant',
            ]);
        }

        $response = $this->actingAs($manager)
            ->get(route('manager.quality'))
            ->assertOk();

        // The Blade view renders pagination links when there are multiple pages.
        // assertSee checks raw HTML, so we look for a rel="next" or page=2 link.
        $response->assertSee('page=2', false);
    }

    /** Validate button appears for an unverified measurement and disappears once verified. */
    public function test_validate_button_visibility_on_show(): void
    {
        $manager = $this->createUserWithRole(Role::MANAGER);

        $point = PointMesure::create([
            'code'   => 'TST-BTN',
            'nom'    => 'Point bouton valider',
            'type'   => 'Station',
            'statut' => 'actif',
        ]);

        $unverified = MesureQualite::create([
            'reference'          => 'BTN-UNVERIFIED',
            'point_mesure_id'    => $point->id,
            'date_mesure'        => now(),
            'ph'                 => 7.2,
            'turbidite'          => 0.5,
            'chlore_residuel'    => 0.8,
            'plomb'              => 2,
            'nitrates'           => 10,
            'source_donnee'      => 'manuel',
            'is_verified'        => false,
            'overall_compliance' => 100,
            'status'             => 'Compliant',
        ]);

        // Unverified → "Valider" button must be present
        $this->actingAs($manager)
            ->get(route('manager.quality.mesures.show', $unverified))
            ->assertOk()
            ->assertSee('Valider');

        $verified = MesureQualite::create([
            'reference'          => 'BTN-VERIFIED',
            'point_mesure_id'    => $point->id,
            'date_mesure'        => now(),
            'ph'                 => 7.2,
            'turbidite'          => 0.5,
            'chlore_residuel'    => 0.8,
            'plomb'              => 2,
            'nitrates'           => 10,
            'source_donnee'      => 'manuel',
            'is_verified'        => true,
            'verifier'           => 'Manager (manager)',
            'overall_compliance' => 100,
            'status'             => 'Compliant',
        ]);

        // Already verified → "Valider" button must NOT be present
        $this->actingAs($manager)
            ->get(route('manager.quality.mesures.show', $verified))
            ->assertOk()
            ->assertDontSee('Valider');
    }
}
