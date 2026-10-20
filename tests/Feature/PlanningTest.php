<?php

namespace Tests\Feature;

use App\Models\Intervention;
use App\Models\Technicien;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlanningTest extends TestCase
{
    use RefreshDatabase;

    private Technicien $technicien;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->withSession([
            'user' => [
                'name' => 'Gestionnaire',
                'email' => 'gestionnaire@aquasecure.tn',
                'role' => 'manager',
            ],
        ]);

        $this->technicien = Technicien::factory()->create(['nom' => 'Amira Ben Ali', 'disponibilite' => 'Disponible']);
    }

    private function intervention(array $attributs = []): Intervention
    {
        return Intervention::factory()->for($this->technicien)->create(array_merge([
            'date' => today()->addDays(3)->toDateString(),
            'statut' => 'Planifiée',
            'description' => 'Contrôle des vannes du secteur nord',
        ], $attributs));
    }

    private function donnees(array $attributs = []): array
    {
        return array_merge([
            'technicien_id' => $this->technicien->id,
            'date' => today()->addDays(3)->toDateString(),
            'description' => 'Remplacement de la pompe de relevage',
            'statut' => 'Planifiée',
            'cout' => 500,
        ], $attributs);
    }

    // ── Règles de planification (formulaire) ─────────────────────────────────

    public function test_cannot_assign_two_active_interventions_same_day(): void
    {
        $existante = $this->intervention();

        $this->post(route('manager.interventions.store'), $this->donnees())
            ->assertSessionHasErrors('date');

        $this->assertDatabaseCount('interventions', 1);
        $this->assertStringContainsString(
            '#'.$existante->id,
            session('errors')->first('date')
        );
    }

    public function test_finished_or_cancelled_interventions_do_not_block_the_day(): void
    {
        $this->intervention(['statut' => 'Annulée']);

        $this->post(route('manager.interventions.store'), $this->donnees())
            ->assertSessionHasNoErrors();

        $this->assertDatabaseCount('interventions', 2);
    }

    public function test_cannot_plan_a_technician_on_leave(): void
    {
        $this->technicien->update(['disponibilite' => 'En congé']);

        $this->post(route('manager.interventions.store'), $this->donnees())
            ->assertSessionHasErrors(['date' => 'Amira Ben Ali est en congé : impossible de lui planifier une intervention.']);
    }

    public function test_editing_an_intervention_does_not_conflict_with_itself(): void
    {
        $intervention = $this->intervention();

        $this->put(route('manager.interventions.update', $intervention), $this->donnees([
            'description' => 'Contrôle des vannes — mise à jour',
        ]))->assertSessionHasNoErrors();

        $this->assertSame('Contrôle des vannes — mise à jour', $intervention->fresh()->description);
    }

    // ── Calendrier ────────────────────────────────────────────────────────────

    public function test_planning_page_lists_existing_conflicts(): void
    {
        $a = $this->intervention();
        $b = $this->intervention(['description' => 'Deuxième intervention du même jour']);

        $this->get(route('manager.planning.index'))
            ->assertOk()
            ->assertSee('1 conflit(s) de planification à venir')
            ->assertSee('#'.$a->id)
            ->assertSee('#'.$b->id);
    }

    public function test_planning_page_flags_interventions_of_technicians_on_leave(): void
    {
        $intervention = $this->intervention();
        $this->technicien->update(['disponibilite' => 'En congé']);

        $this->get(route('manager.planning.index'))
            ->assertOk()
            ->assertSee('1 intervention(s) à venir affectée(s) à un technicien en congé')
            ->assertSee(route('manager.interventions.edit', $intervention));
    }

    public function test_events_feed_returns_range_filter_and_conflict_flag(): void
    {
        $a = $this->intervention();
        $b = $this->intervention(['description' => 'Deuxième intervention du même jour']);
        $autre = Intervention::factory()->create(['date' => today()->addDays(3)->toDateString(), 'statut' => 'Planifiée']);
        Intervention::factory()->for($this->technicien)->create(['date' => today()->addMonths(3)->toDateString(), 'statut' => 'Planifiée']);

        $plage = ['start' => today()->toDateString(), 'end' => today()->addDays(10)->toDateString()];

        $tous = $this->getJson(route('manager.planning.evenements', $plage))->assertOk()->json();
        $this->assertEqualsCanonicalizing([$a->id, $b->id, $autre->id], array_column($tous, 'id'));

        $filtres = $this->getJson(route('manager.planning.evenements', $plage + ['technicien_id' => $this->technicien->id]))->json();
        $this->assertEqualsCanonicalizing([$a->id, $b->id], array_column($filtres, 'id'));
        $this->assertTrue($filtres[0]['extendedProps']['conflit']);

        $evenementAutre = collect($tous)->firstWhere('id', $autre->id);
        $this->assertFalse($evenementAutre['extendedProps']['conflit']);
    }

    public function test_drag_and_drop_reschedules_when_day_is_free(): void
    {
        $intervention = $this->intervention();
        $nouvelleDate = today()->addDays(5)->toDateString();

        $this->patchJson(route('manager.planning.deplacer', $intervention), ['date' => $nouvelleDate])
            ->assertOk()
            ->assertJsonFragment(['message' => 'Intervention #'.$intervention->id.' replanifiée au '.today()->addDays(5)->format('d/m/Y').'.']);

        $this->assertSame($nouvelleDate, $intervention->fresh()->date->toDateString());
    }

    public function test_drag_and_drop_is_refused_on_conflict(): void
    {
        $this->intervention(['date' => today()->addDays(5)->toDateString()]);
        $intervention = $this->intervention();

        $this->patchJson(route('manager.planning.deplacer', $intervention), ['date' => today()->addDays(5)->toDateString()])
            ->assertStatus(422)
            ->assertJsonPath('message', fn (string $message) => str_contains($message, 'a déjà une intervention'));

        $this->assertSame(today()->addDays(3)->toDateString(), $intervention->fresh()->date->toDateString());
    }

    public function test_finished_interventions_cannot_be_moved(): void
    {
        $intervention = $this->intervention(['date' => today()->subDay()->toDateString(), 'statut' => 'Terminée']);

        $this->patchJson(route('manager.planning.deplacer', $intervention), ['date' => today()->subDays(2)->toDateString()])
            ->assertStatus(422);

        $this->assertSame(today()->subDay()->toDateString(), $intervention->fresh()->date->toDateString());
    }

    public function test_planning_is_reserved_to_managers(): void
    {
        $this->withSession(['user' => ['name' => 'Citoyen', 'role' => 'citoyen']]);
        $this->get(route('manager.planning.index'))->assertForbidden();

        $this->withSession(['user' => ['name' => 'Amira', 'role' => 'technicien']]);
        $this->get(route('manager.planning.index'))->assertForbidden();
    }
}
