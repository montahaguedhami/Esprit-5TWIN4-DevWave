<?php

namespace Tests\Feature;

use App\Models\MesureQualite;
use App\Models\PointMesure;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithRbac;
use Tests\TestCase;

class WaterQualityValidationAndSearchTest extends TestCase
{
    use RefreshDatabase;
    use InteractsWithRbac;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->seedRbac();
    }

    // =========================================================================
    // 1. POINT DE MESURE — VALIDATION RIGOUREUSE & TRIMMING
    // =========================================================================

    public function test_point_mesure_trims_whitespace_and_rejects_empty_strings(): void
    {
        $manager = $this->createUserWithRole(Role::MANAGER);

        // Store with leading/trailing spaces
        $this->actingAs($manager)
            ->post(route('manager.points-mesure.store'), [
                'code'        => '   PT-TRIM-01   ',
                'nom'         => '   Station Eau Claire   ',
                'zone'        => '   Zone Nord   ',
                'adresse'     => '   10 Rue de la Paix   ',
                'type'        => 'Station',
                'statut'      => 'actif',
                'latitude'    => 36.8000000,
                'longitude'   => 10.1800000,
                'description' => '   Description avec espaces   ',
            ])
            ->assertRedirect();

        $point = PointMesure::where('code', 'PT-TRIM-01')->first();
        $this->assertNotNull($point);
        $this->assertSame('PT-TRIM-01', $point->code);
        $this->assertSame('Station Eau Claire', $point->nom);
        $this->assertSame('Zone Nord', $point->zone);
        $this->assertSame('10 Rue de la Paix', $point->adresse);
        $this->assertSame('Description avec espaces', $point->description);

        // Reject whitespace-only strings
        $this->actingAs($manager)
            ->post(route('manager.points-mesure.store'), [
                'code'   => '   ',
                'nom'    => '   ',
                'type'   => 'Station',
                'statut' => 'actif',
            ])
            ->assertSessionHasErrors(['code', 'nom']);
    }

    public function test_point_mesure_rejects_out_of_range_coordinates(): void
    {
        $manager = $this->createUserWithRole(Role::MANAGER);

        // Latitude > 90 and Longitude < -180
        $this->actingAs($manager)
            ->post(route('manager.points-mesure.store'), [
                'code'      => 'PT-COORD-BAD',
                'nom'       => 'Station Coordonnées Invalides',
                'type'      => 'Station',
                'statut'    => 'actif',
                'latitude'  => 95.5,
                'longitude' => -190.0,
            ])
            ->assertSessionHasErrors(['latitude', 'longitude']);

        $this->assertDatabaseMissing('point_mesures', ['code' => 'PT-COORD-BAD']);
    }

    public function test_point_mesure_enforces_code_uniqueness_on_create_and_update(): void
    {
        $manager = $this->createUserWithRole(Role::MANAGER);

        $point1 = PointMesure::create([
            'code'   => 'CODE-UNIQUE-01',
            'nom'    => 'Premier Point',
            'type'   => 'Station',
            'statut' => 'actif',
        ]);

        $point2 = PointMesure::create([
            'code'   => 'CODE-UNIQUE-02',
            'nom'    => 'Deuxième Point',
            'type'   => 'Puits',
            'statut' => 'actif',
        ]);

        // Attempt to create with duplicate code
        $this->actingAs($manager)
            ->post(route('manager.points-mesure.store'), [
                'code'   => 'CODE-UNIQUE-01',
                'nom'    => 'Doublon',
                'type'   => 'Station',
                'statut' => 'actif',
            ])
            ->assertSessionHasErrors(['code']);

        // Update point2 with point1's code -> rejected
        $this->actingAs($manager)
            ->put(route('manager.points-mesure.update', $point2), [
                'code'   => 'CODE-UNIQUE-01',
                'nom'    => 'Deuxième Point Modifié',
                'type'   => 'Puits',
                'statut' => 'actif',
            ])
            ->assertSessionHasErrors(['code']);

        // Update point2 with its own code -> allowed
        $this->actingAs($manager)
            ->put(route('manager.points-mesure.update', $point2), [
                'code'   => 'CODE-UNIQUE-02',
                'nom'    => 'Deuxième Point Nom Mis à Jour',
                'type'   => 'Puits',
                'statut' => 'actif',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('point_mesures', [
            'id'  => $point2->id,
            'nom' => 'Deuxième Point Nom Mis à Jour',
        ]);
    }

    public function test_point_mesure_rejects_invalid_type_or_statut(): void
    {
        $manager = $this->createUserWithRole(Role::MANAGER);

        $this->actingAs($manager)
            ->post(route('manager.points-mesure.store'), [
                'code'   => 'PT-BAD-ENUM',
                'nom'    => 'Test Enum',
                'type'   => 'InconnuType',
                'statut' => 'supprime',
            ])
            ->assertSessionHasErrors(['type', 'statut']);
    }

    // =========================================================================
    // 2. MESURE DE QUALITÉ — VALIDATION RIGOUREUSE & TRIMMING
    // =========================================================================

    public function test_mesure_qualite_trims_whitespace_and_rejects_empty_reference(): void
    {
        $creator = $this->createUserWithRole(Role::MANAGER);
        $creator->assignRole(Role::TECHNICIEN);
        $creator = $creator->fresh()->load('roles');

        $point = PointMesure::create([
            'code'   => 'PT-FOR-MES',
            'nom'    => 'Point Pour Mesures',
            'type'   => 'Station',
            'statut' => 'actif',
        ]);

        $this->actingAs($creator)
            ->post(route('manager.quality.mesures.store'), [
                'reference'       => '   REF-TRIM-01   ',
                'point_mesure_id' => $point->id,
                'date_mesure'     => '2026-10-10 10:00:00',
                'ph'              => 7.4,
                'turbidite'       => 0.4,
                'chlore_residuel' => 0.8,
                'plomb'           => 1.5,
                'nitrates'        => 12.0,
                'source_donnee'   => '   manuel   ',
                'verifier'        => '   Labo Central   ',
            ])
            ->assertRedirect();

        $mesure = MesureQualite::where('reference', 'REF-TRIM-01')->first();
        $this->assertNotNull($mesure);
        $this->assertSame('REF-TRIM-01', $mesure->reference);
        $this->assertSame('manuel', $mesure->source_donnee);
        $this->assertSame('Labo Central', $mesure->verifier);
    }

    public function test_mesure_qualite_rejects_extreme_out_of_bound_values(): void
    {
        $creator = $this->createUserWithRole(Role::MANAGER);
        $creator->assignRole(Role::TECHNICIEN);
        $creator = $creator->fresh()->load('roles');

        $point = PointMesure::create([
            'code'   => 'PT-OUT-BOUNDS',
            'nom'    => 'Point Limites',
            'type'   => 'Station',
            'statut' => 'actif',
        ]);

        // Negative values and decimal overflow
        $this->actingAs($creator)
            ->post(route('manager.quality.mesures.store'), [
                'reference'       => 'REF-OUT-BOUNDS',
                'point_mesure_id' => $point->id,
                'date_mesure'     => '2026-10-10 10:00:00',
                'ph'              => -0.5,
                'turbidite'       => 2000, // max is 1000
                'chlore_residuel' => -1.0,
                'plomb'           => 50000, // max is 10000
                'nitrates'        => 5000, // max is 1000
                'source_donnee'   => 'manuel',
            ])
            ->assertSessionHasErrors(['ph', 'turbidite', 'chlore_residuel', 'plomb', 'nitrates']);
    }

    public function test_mesure_qualite_enforces_foreign_key_existence(): void
    {
        $creator = $this->createUserWithRole(Role::MANAGER);
        $creator->assignRole(Role::TECHNICIEN);
        $creator = $creator->fresh()->load('roles');

        $this->actingAs($creator)
            ->post(route('manager.quality.mesures.store'), [
                'reference'       => 'REF-GHOST-POINT',
                'point_mesure_id' => 999999, // non-existent ID
                'date_mesure'     => '2026-10-10 10:00:00',
                'ph'              => 7.0,
                'turbidite'       => 0.5,
                'chlore_residuel' => 0.8,
                'plomb'           => 2.0,
                'nitrates'        => 10.0,
                'source_donnee'   => 'manuel',
            ])
            ->assertSessionHasErrors(['point_mesure_id']);
    }

    // =========================================================================
    // 3. RECHERCHE ET FILTRAGE — POINTS DE MESURE
    // =========================================================================

    public function test_manager_can_search_and_filter_points_mesure(): void
    {
        $manager = $this->createUserWithRole(Role::MANAGER);

        PointMesure::create(['code' => 'TUN-NORD-01', 'nom' => 'Station Nord', 'zone' => 'Ariana', 'type' => 'Station', 'statut' => 'actif']);
        PointMesure::create(['code' => 'TUN-SUD-02', 'nom' => 'Puits Sud', 'zone' => 'Ben Arous', 'type' => 'Puits', 'statut' => 'inactif']);
        PointMesure::create(['code' => 'TUN-EST-03', 'nom' => 'Réservoir Est', 'zone' => 'Carthage', 'type' => 'Réservoir', 'statut' => 'actif']);

        // Search keyword "Ariana"
        $this->actingAs($manager)
            ->get(route('manager.points-mesure.index', ['search' => 'Ariana']))
            ->assertOk()
            ->assertSee('TUN-NORD-01')
            ->assertDontSee('TUN-SUD-02')
            ->assertDontSee('TUN-EST-03');

        // Filter by statut = "inactif"
        $this->actingAs($manager)
            ->get(route('manager.points-mesure.index', ['statut' => 'inactif']))
            ->assertOk()
            ->assertSee('TUN-SUD-02')
            ->assertDontSee('TUN-NORD-01');

        // Filter by type = "Réservoir"
        $this->actingAs($manager)
            ->get(route('manager.points-mesure.index', ['type' => 'Réservoir']))
            ->assertOk()
            ->assertSee('TUN-EST-03')
            ->assertDontSee('TUN-NORD-01');
    }

    // =========================================================================
    // 4. RECHERCHE ET FILTRAGE — MESURES DE QUALITÉ
    // =========================================================================

    public function test_manager_can_search_and_filter_mesures_qualite(): void
    {
        $manager = $this->createUserWithRole(Role::MANAGER);

        $pointA = PointMesure::create(['code' => 'PT-A', 'nom' => 'Station Alpha', 'type' => 'Station', 'statut' => 'actif']);
        $pointB = PointMesure::create(['code' => 'PT-B', 'nom' => 'Station Beta', 'type' => 'Station', 'statut' => 'actif']);

        MesureQualite::create([
            'reference'          => 'MES-ALPHA-100',
            'point_mesure_id'    => $pointA->id,
            'date_mesure'        => '2026-10-01 10:00:00',
            'ph'                 => 7.2,
            'turbidite'          => 0.4,
            'chlore_residuel'    => 0.8,
            'plomb'              => 1.0,
            'nitrates'           => 10.0,
            'source_donnee'      => 'manuel',
            'is_verified'        => true,
            'verifier'           => 'Admin',
            'overall_compliance' => 100.0,
            'status'             => 'Compliant',
        ]);

        MesureQualite::create([
            'reference'          => 'MES-BETA-200',
            'point_mesure_id'    => $pointB->id,
            'date_mesure'        => '2026-10-02 10:00:00',
            'ph'                 => 9.2, // Alert / Non-compliant
            'turbidite'          => 3.0,
            'chlore_residuel'    => 0.1,
            'plomb'              => 20.0,
            'nitrates'           => 10.0,
            'source_donnee'      => 'laboratoire',
            'is_verified'        => false,
            'overall_compliance' => 20.0,
            'status'             => 'Non-Compliant',
        ]);

        // Search by reference
        $response1 = $this->actingAs($manager)
            ->get(route('manager.quality', ['search' => 'MES-ALPHA']))
            ->assertOk();
        $records1 = $response1->viewData('records');
        $this->assertTrue($records1->contains('id', 'MES-ALPHA-100'));
        $this->assertFalse($records1->contains('id', 'MES-BETA-200'));

        // Filter by point_mesure_id
        $response2 = $this->actingAs($manager)
            ->get(route('manager.quality', ['point_mesure_id' => $pointB->id]))
            ->assertOk();
        $records2 = $response2->viewData('records');
        $this->assertTrue($records2->contains('id', 'MES-BETA-200'));
        $this->assertFalse($records2->contains('id', 'MES-ALPHA-100'));

        // Filter by status = Non-Compliant
        $response3 = $this->actingAs($manager)
            ->get(route('manager.quality', ['status' => 'Non-Compliant']))
            ->assertOk();
        $records3 = $response3->viewData('records');
        $this->assertTrue($records3->contains('id', 'MES-BETA-200'));
        $this->assertFalse($records3->contains('id', 'MES-ALPHA-100'));

        // Filter by source_donnee = laboratoire
        $response4 = $this->actingAs($manager)
            ->get(route('manager.quality', ['source_donnee' => 'laboratoire']))
            ->assertOk();
        $records4 = $response4->viewData('records');
        $this->assertTrue($records4->contains('id', 'MES-BETA-200'));
        $this->assertFalse($records4->contains('id', 'MES-ALPHA-100'));
    }
}
