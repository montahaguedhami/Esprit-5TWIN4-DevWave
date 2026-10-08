<?php

namespace Tests\Feature;

use App\Models\MesureQualite;
use App\Models\PointMesure;
use App\Services\WaterQualityService;
use Database\Seeders\WaterQualitySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WaterQualityModuleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_quality_and_map_pages_render_from_the_database(): void
    {
        $this->seed(WaterQualitySeeder::class);

        $this->get(route('manager.quality'))
            ->assertOk()
            ->assertSee('WQR-2026-108')
            ->assertSee('12.4 mg/L');

        $this->get(route('manager.map'))
            ->assertOk()
            ->assertSee('TUN-BEL-04');

        $point = PointMesure::firstOrFail();
        $measurement = MesureQualite::firstOrFail();

        $this->get(route('manager.points-mesure.index'))->assertOk();
        $this->get(route('manager.points-mesure.create'))->assertOk();
        $this->get(route('manager.points-mesure.show', $point))->assertOk();
        $this->get(route('manager.points-mesure.edit', $point))->assertOk();
        $this->get(route('manager.quality.mesures.create'))->assertOk();
        $this->get(route('manager.quality.mesures.show', $measurement))->assertOk();
        $this->get(route('manager.quality.mesures.edit', $measurement))->assertOk();
    }

    public function test_manager_can_create_a_point_and_a_calculated_measurement(): void
    {
        $this->post(route('manager.points-mesure.store'), [
            'code' => 'TST-001',
            'nom' => 'Point de test',
            'zone' => 'Tunis',
            'latitude' => 36.8,
            'longitude' => 10.1,
            'type' => 'Station',
            'statut' => 'actif',
        ])->assertRedirect();

        $point = PointMesure::firstOrFail();

        $this->post(route('manager.quality.mesures.store'), [
            'reference' => 'TEST-001',
            'point_mesure_id' => $point->id,
            'date_mesure' => '2026-10-08 10:00:00',
            'ph' => 7.2,
            'turbidite' => 1.4,
            'chlore_residuel' => 0.8,
            'plomb' => 2,
            'nitrates' => 20,
            'is_verified' => 1,
        ])->assertRedirect(route('manager.quality'));

        $measurement = MesureQualite::firstOrFail();
        $this->assertSame('Alert', $measurement->status);
        $this->assertSame('90.00', $measurement->overall_compliance);
        $this->assertSame($point->id, $measurement->point_mesure_id);

        $this->put(route('manager.quality.mesures.update', $measurement), [
            'reference' => 'TEST-001',
            'point_mesure_id' => $point->id,
            'date_mesure' => '2026-10-08 11:00:00',
            'ph' => 7.3,
            'turbidite' => 0.9,
            'chlore_residuel' => 0.8,
            'plomb' => 2,
            'nitrates' => 20,
            'is_verified' => 1,
        ])->assertRedirect(route('manager.quality'));
        $this->assertDatabaseHas('mesures_qualites', ['id' => $measurement->id, 'status' => 'Compliant']);

        $this->delete(route('manager.points-mesure.destroy', $point))
            ->assertRedirect(route('manager.points-mesure.index'));
        $this->assertDatabaseHas('point_mesures', ['id' => $point->id, 'statut' => 'inactif']);

        $this->delete(route('manager.quality.mesures.destroy', $measurement))
            ->assertRedirect(route('manager.quality'));
        $this->assertDatabaseMissing('mesures_qualites', ['id' => $measurement->id]);
    }

    public function test_measurement_validation_rejects_impossible_values(): void
    {
        $point = PointMesure::create([
            'code' => 'TST-002',
            'nom' => 'Point de validation',
            'type' => 'Puits',
            'statut' => 'actif',
        ]);

        $this->from(route('manager.quality.mesures.create'))
            ->post(route('manager.quality.mesures.store'), [
                'reference' => 'TEST-INVALID',
                'point_mesure_id' => $point->id,
                'date_mesure' => '2026-10-08 10:00:00',
                'ph' => 15,
                'turbidite' => -1,
                'chlore_residuel' => 0.5,
                'plomb' => 1,
                'nitrates' => 10,
                'is_verified' => 0,
            ])
            ->assertRedirect(route('manager.quality.mesures.create'))
            ->assertSessionHasErrors(['ph', 'turbidite']);

        $this->assertDatabaseMissing('mesures_qualites', ['reference' => 'TEST-INVALID']);
    }

    public function test_nitrates_threshold_is_optional_and_configurable(): void
    {
        $service = app(WaterQualityService::class);
        $values = [
            'ph' => 7.2,
            'turbidite' => 0.5,
            'chlore_residuel' => 0.8,
            'plomb' => 2,
            'nitrates' => 25,
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
}
