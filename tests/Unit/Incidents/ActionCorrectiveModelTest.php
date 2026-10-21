<?php

namespace Tests\Unit\Incidents;

use App\Models\ActionCorrective;
use App\Models\Incident;
use App\Models\Technicien;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Tests\TestCase;

class ActionCorrectiveModelTest extends TestCase
{
    public function test_it_uses_the_action_correctives_table(): void
    {
        $this->assertSame('action_correctives', (new ActionCorrective())->getTable());
    }

    public function test_fillable_attributes_are_the_expected_ones(): void
    {
        $this->assertSame([
            'incident_id', 'technicien_id', 'titre', 'description', 'responsable',
            'date_prevue', 'date_realisation', 'statut', 'resultat',
        ], (new ActionCorrective())->getFillable());
    }

    public function test_dates_are_cast(): void
    {
        $action = new ActionCorrective([
            'date_prevue' => '2026-10-07',
            'date_realisation' => '2026-10-08 14:15:00',
        ]);

        $this->assertInstanceOf(CarbonInterface::class, $action->date_prevue);
        $this->assertSame('2026-10-07', $action->date_prevue->format('Y-m-d'));
        $this->assertInstanceOf(CarbonInterface::class, $action->date_realisation);
        $this->assertSame('14:15', $action->date_realisation->format('H:i'));
    }

    public function test_date_realisation_can_be_null(): void
    {
        $this->assertNull((new ActionCorrective(['date_realisation' => null]))->date_realisation);
    }

    public function test_incident_relation_is_belongs_to_incident(): void
    {
        $relation = (new ActionCorrective())->incident();

        $this->assertInstanceOf(BelongsTo::class, $relation);
        $this->assertInstanceOf(Incident::class, $relation->getRelated());
        $this->assertSame('incident_id', $relation->getForeignKeyName());
    }

    public function test_technicien_relation_is_belongs_to_technicien(): void
    {
        $relation = (new ActionCorrective())->technicien();

        $this->assertInstanceOf(BelongsTo::class, $relation);
        $this->assertInstanceOf(Technicien::class, $relation->getRelated());
        $this->assertSame('technicien_id', $relation->getForeignKeyName());
    }
}
