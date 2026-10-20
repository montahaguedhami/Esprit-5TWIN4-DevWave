<?php

namespace Tests\Unit\Incidents;

use App\Models\ActionCorrective;
use App\Models\Incident;
use App\Models\Infrastructure;
use App\Models\User;
use App\Models\Zone;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Tests\TestCase;

class IncidentModelTest extends TestCase
{
    public function test_fillable_attributes_are_the_expected_ones(): void
    {
        $this->assertSame([
            'titre', 'description', 'type', 'gravite', 'statut', 'date_signalement',
            'localisation', 'latitude', 'longitude', 'zone_id', 'infrastructure_id',
            'user_id', 'preuve_path',
        ], (new Incident())->getFillable());
    }

    public function test_unknown_attributes_are_not_mass_assigned(): void
    {
        $incident = new Incident(['titre' => 'Fuite', 'id' => 99, 'created_at' => '2020-01-01']);

        $this->assertSame('Fuite', $incident->titre);
        $this->assertNull($incident->id);
        $this->assertNull($incident->created_at);
    }

    public function test_date_signalement_is_cast_to_a_date(): void
    {
        $incident = new Incident(['date_signalement' => '2026-10-07 10:30:00']);

        $this->assertInstanceOf(CarbonInterface::class, $incident->date_signalement);
        $this->assertSame('2026-10-07 10:30', $incident->date_signalement->format('Y-m-d H:i'));
    }

    public function test_actions_relation_is_has_many_action_corrective(): void
    {
        $relation = (new Incident())->actions();

        $this->assertInstanceOf(HasMany::class, $relation);
        $this->assertInstanceOf(ActionCorrective::class, $relation->getRelated());
        $this->assertSame('incident_id', $relation->getForeignKeyName());
    }

    public function test_user_relation_is_belongs_to_user(): void
    {
        $relation = (new Incident())->user();

        $this->assertInstanceOf(BelongsTo::class, $relation);
        $this->assertInstanceOf(User::class, $relation->getRelated());
        $this->assertSame('user_id', $relation->getForeignKeyName());
    }

    public function test_zone_and_infrastructure_relations_are_belongs_to(): void
    {
        $incident = new Incident();

        $this->assertInstanceOf(Zone::class, $incident->zone()->getRelated());
        $this->assertSame('zone_id', $incident->zone()->getForeignKeyName());
        $this->assertInstanceOf(Infrastructure::class, $incident->infrastructure()->getRelated());
        $this->assertSame('infrastructure_id', $incident->infrastructure()->getForeignKeyName());
    }
}
