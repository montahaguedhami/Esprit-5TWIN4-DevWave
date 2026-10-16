<?php

namespace Tests\Feature;

use App\Enums\InfrastructureType;
use App\Enums\NiveauCriticite;
use App\Models\Infrastructure;
use App\Models\Intervention;
use App\Models\Technicien;
use App\Models\Zone;
use App\Services\PreventiveMaintenanceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PreventiveMaintenanceTest extends TestCase
{
    use RefreshDatabase;

    private Zone $zone;

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

        $this->zone = Zone::create(['nom' => 'Zone Grand Tunis', 'localisation' => 'Tunis Centre']);
    }

    private function infrastructure(array $attributs = []): Infrastructure
    {
        static $numero = 0;
        $numero++;

        return Infrastructure::factory()->create(array_merge([
            'zone_id' => $this->zone->id,
            'nom' => 'Station Pompage '.$numero,
            'reference' => 'INF-PREV-'.$numero,
            'type' => InfrastructureType::StationPompage,
            'niveau_criticite' => NiveauCriticite::Moyen,
            'date_prochaine_maintenance' => today()->addDays(3)->toDateString(),
        ], $attributs));
    }

    private function technicien(string $nom, string $specialite, string $disponibilite = 'Disponible'): Technicien
    {
        return Technicien::factory()->create(compact('nom', 'specialite', 'disponibilite'));
    }

    public function test_due_infrastructure_gets_a_preventive_intervention_for_the_right_specialist(): void
    {
        $this->technicien('Karim Electricien', 'Électricité');
        $pompage = $this->technicien('Sami Pompage', 'Pompage');
        $infra = $this->infrastructure();

        $this->artisan('maintenance:planifier-preventive')->assertSuccessful();

        $intervention = Intervention::sole();
        $this->assertSame(Intervention::TYPE_PREVENTIVE, $intervention->type);
        $this->assertSame('Planifiée', $intervention->statut);
        $this->assertTrue($intervention->technicien->is($pompage));
        $this->assertTrue($intervention->infrastructure->is($infra));
        $this->assertSame(today()->addDays(3)->toDateString(), $intervention->date->toDateString());
    }

    public function test_not_yet_due_or_already_planned_infrastructures_are_skipped(): void
    {
        $this->technicien('Sami Pompage', 'Pompage');
        $this->infrastructure(['date_prochaine_maintenance' => today()->addDays(30)->toDateString()]);
        $dejaPlanifiee = $this->infrastructure();
        Intervention::factory()->create([
            'infrastructure_id' => $dejaPlanifiee->id,
            'date' => today()->addDay()->toDateString(),
            'statut' => 'Planifiée',
        ]);

        $lignes = app(PreventiveMaintenanceService::class)->planifier();

        $this->assertCount(1, $lignes);
        $this->assertSame(PreventiveMaintenanceService::DEJA_PLANIFIEE, $lignes->first()['resultat']);
        $this->assertDatabaseCount('interventions', 1);
    }

    public function test_overdue_maintenance_is_planned_from_today(): void
    {
        $this->technicien('Sami Pompage', 'Pompage');
        $this->infrastructure(['date_prochaine_maintenance' => today()->subDays(10)->toDateString()]);

        app(PreventiveMaintenanceService::class)->planifier();

        $this->assertSame(today()->toDateString(), Intervention::sole()->date->toDateString());
    }

    public function test_busy_or_on_leave_technicians_are_avoided_and_workload_is_balanced(): void
    {
        $this->technicien('Amel Conge', 'Pompage', 'En congé');
        $occupe = $this->technicien('Bilel Occupe', 'Pompage');
        $libre = $this->technicien('Chiraz Libre', 'Pompage');
        Intervention::factory()->for($occupe)->create(['date' => today()->addDays(3)->toDateString(), 'statut' => 'Planifiée']);

        $this->infrastructure();
        $this->infrastructure();

        app(PreventiveMaintenanceService::class)->planifier();

        $preventives = Intervention::where('type', Intervention::TYPE_PREVENTIVE)->get();
        $this->assertCount(2, $preventives);
        // Chiraz prend la première ; le même jour elle n'est plus libre et Bilel est occupé : décalage au lendemain
        $this->assertTrue($preventives[0]->technicien->is($libre));
        $this->assertSame(today()->addDays(3)->toDateString(), $preventives[0]->date->toDateString());
        $this->assertSame(today()->addDays(4)->toDateString(), $preventives[1]->date->toDateString());
        $this->assertFalse($preventives->contains(fn (Intervention $i) => $i->technicien->nom === 'Amel Conge'));
    }

    public function test_falls_back_to_another_specialty_when_no_specialist_exists(): void
    {
        $this->technicien('Karim Electricien', 'Électricité');
        $this->infrastructure();

        $ligne = app(PreventiveMaintenanceService::class)->planifier()->first();

        $this->assertSame(PreventiveMaintenanceService::PLANIFIEE, $ligne['resultat']);
        $this->assertTrue($ligne['hors_specialite']);
    }

    public function test_reports_when_no_technician_is_available(): void
    {
        $this->technicien('Amel Conge', 'Pompage', 'En congé');
        $this->infrastructure();

        $ligne = app(PreventiveMaintenanceService::class)->planifier()->first();

        $this->assertSame(PreventiveMaintenanceService::AUCUN_TECHNICIEN, $ligne['resultat']);
        $this->assertDatabaseCount('interventions', 0);
    }

    public function test_simulation_creates_nothing(): void
    {
        $this->technicien('Sami Pompage', 'Pompage');
        $this->infrastructure();

        $this->artisan('maintenance:planifier-preventive', ['--simulation' => true])
            ->expectsOutputToContain('Simulation : 1 intervention(s) seraient créées.')
            ->assertSuccessful();

        $this->assertDatabaseCount('interventions', 0);
    }

    public function test_finishing_a_preventive_intervention_schedules_the_next_one(): void
    {
        $technicien = $this->technicien('Sami Pompage', 'Pompage');
        $normale = $this->infrastructure(['niveau_criticite' => NiveauCriticite::Moyen]);
        $critique = $this->infrastructure(['niveau_criticite' => NiveauCriticite::Critique]);
        $date = today()->subDay();

        foreach ([$normale, $critique] as $infra) {
            Intervention::factory()->for($technicien)->create([
                'infrastructure_id' => $infra->id,
                'date' => $date->toDateString(),
                'statut' => 'Terminée',
                'type' => Intervention::TYPE_PREVENTIVE,
            ]);
        }

        // Station de pompage : tous les 3 mois, et 2 fois plus souvent si critique
        $this->assertSame($date->copy()->addMonthsNoOverflow(3)->toDateString(), $normale->fresh()->date_prochaine_maintenance->toDateString());
        $this->assertSame($date->copy()->addMonthsNoOverflow(2)->toDateString(), $critique->fresh()->date_prochaine_maintenance->toDateString());
        $this->assertSame($date->toDateString(), $normale->fresh()->date_derniere_maintenance->toDateString());
    }

    public function test_finishing_a_corrective_intervention_keeps_the_schedule(): void
    {
        $infra = $this->infrastructure();

        Intervention::factory()->create([
            'infrastructure_id' => $infra->id,
            'date' => today()->subDay()->toDateString(),
            'statut' => 'Terminée',
            'type' => Intervention::TYPE_CORRECTIVE,
        ]);

        $this->assertSame(today()->addDays(3)->toDateString(), $infra->fresh()->date_prochaine_maintenance->toDateString());
    }

    public function test_manager_can_simulate_then_run_from_the_planning_page(): void
    {
        $this->technicien('Sami Pompage', 'Pompage');
        $this->infrastructure(['nom' => 'Station Ennasr']);

        $this->post(route('manager.planning.preventive'), ['simulation' => 1])
            ->assertRedirect(route('manager.planning.index'))
            ->assertSessionHas('success', 'Simulation : 1 intervention(s) préventive(s) seraient créées.');
        $this->assertDatabaseCount('interventions', 0);

        $this->followingRedirects()
            ->post(route('manager.planning.preventive'))
            ->assertOk()
            ->assertSee('1 intervention(s) préventive(s) créée(s).')
            ->assertSee('Station Ennasr')
            ->assertSee('Planifiée #'.Intervention::sole()->id);
    }

    public function test_preventive_planning_is_reserved_to_managers(): void
    {
        $this->withSession(['user' => ['name' => 'Amira', 'role' => 'technicien']]);

        $this->post(route('manager.planning.preventive'))->assertForbidden();
    }
}
