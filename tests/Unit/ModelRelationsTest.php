<?php

namespace Tests\Unit;

use App\Models\ActionCorrective;
use App\Models\Incident;
use App\Models\Infrastructure;
use App\Models\Intervention;
use App\Models\MesureQualite;
use App\Models\PointMesure;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModelRelationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_incident_belongs_to_user_and_has_many_actions(): void
    {
        $user = User::factory()->create();
        $incident = Incident::factory()->for($user)->create();
        $action = ActionCorrective::factory()->for($incident)->create();

        $this->assertTrue($incident->user->is($user));
        $this->assertTrue($incident->actions->contains($action));
        $this->assertTrue($action->incident->is($incident));
    }

    public function test_point_mesure_derniere_mesure_uses_latest_date(): void
    {
        $point = PointMesure::create([
            'code' => 'TUN-01',
            'nom' => 'Station centre',
            'type' => 'Station',
            'statut' => 'actif',
        ]);

        $older = MesureQualite::create([
            'reference' => 'MES-OLD',
            'point_mesure_id' => $point->id,
            'date_mesure' => '2026-01-01 08:00:00',
            'ph' => 7.0,
            'turbidite' => 0.4,
            'chlore_residuel' => 0.5,
            'plomb' => 1,
            'nitrates' => 8,
            'is_verified' => false,
            'overall_compliance' => 100,
            'status' => 'Compliant',
        ]);

        $newer = MesureQualite::create([
            'reference' => 'MES-NEW',
            'point_mesure_id' => $point->id,
            'date_mesure' => '2026-10-01 08:00:00',
            'ph' => 7.1,
            'turbidite' => 0.6,
            'chlore_residuel' => 0.6,
            'plomb' => 1,
            'nitrates' => 9,
            'is_verified' => true,
            'overall_compliance' => 90,
            'status' => 'Alert',
        ]);

        $this->assertCount(2, $point->mesuresQualites);
        $this->assertTrue($point->derniereMesure->is($newer));
        $this->assertFalse($point->derniereMesure->is($older));
        $this->assertTrue($newer->pointMesure->is($point));
    }

    public function test_intervention_defaults_to_planned_status_and_belongs_to_technician(): void
    {
        $intervention = Intervention::factory()->create(['statut' => null]);
        $fresh = new Intervention(['technicien_id' => $intervention->technicien_id, 'date' => now(), 'description' => 'Test']);

        $this->assertSame('Planifiée', $fresh->statut);
        $this->assertNotNull($intervention->technicien);
        $this->assertContains('Planifiée', Intervention::STATUTS);
        $this->assertContains('Terminée', Intervention::STATUTS);
    }

    public function test_zone_has_infrastructures_and_cannot_be_deleted_while_linked(): void
    {
        $zone = Zone::factory()->create();
        $infrastructure = Infrastructure::factory()->for($zone)->create();

        $this->assertTrue($zone->infrastructures->contains($infrastructure));
        $this->assertTrue($infrastructure->zone->is($zone));

        $this->assertFalse($zone->delete());
        $this->assertFalse($zone->trashed());
    }

    public function test_mesure_qualite_casts_numeric_and_boolean_fields(): void
    {
        $point = PointMesure::create([
            'code' => 'TUN-CAST',
            'nom' => 'Station cast',
            'type' => 'Puits',
            'statut' => 'actif',
        ]);

        $mesure = MesureQualite::create([
            'reference' => 'MES-CAST',
            'point_mesure_id' => $point->id,
            'date_mesure' => '2026-10-08 10:00:00',
            'ph' => 7.25,
            'turbidite' => 1.40,
            'chlore_residuel' => 0.800,
            'plomb' => 2.000,
            'nitrates' => 12.50,
            'is_verified' => 1,
            'overall_compliance' => 90.00,
            'status' => 'Alert',
        ]);

        $this->assertTrue($mesure->is_verified);
        $this->assertSame('7.25', $mesure->ph);
        $this->assertSame('90.00', $mesure->overall_compliance);
        $this->assertTrue($mesure->date_mesure->equalTo('2026-10-08 10:00:00'));
    }
}
