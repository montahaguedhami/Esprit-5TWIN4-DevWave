<?php

namespace Tests\Feature;

use App\Enums\InfrastructureType;
use App\Models\Infrastructure;
use App\Models\Intervention;
use App\Models\Technicien;
use App\Models\Zone;
use App\Services\MaintenanceStatsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MaintenanceDashboardTest extends TestCase
{
    use RefreshDatabase;

    private Technicien $sami;

    private Technicien $nour;

    private Infrastructure $station;

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

        $this->sami = Technicien::factory()->create(['nom' => 'Sami Gharbi', 'disponibilite' => 'Disponible']);
        $this->nour = Technicien::factory()->create(['nom' => 'Nour Ayari', 'disponibilite' => 'Disponible']);
        $this->station = Infrastructure::factory()->create([
            'zone_id' => Zone::create(['nom' => 'Zone Bizerte', 'localisation' => 'Bizerte'])->id,
            'nom' => 'Station El Alia',
            'reference' => 'INF-DASH-1',
            'type' => InfrastructureType::StationPompage,
        ]);

        // 3 interventions échues : 2 terminées, 1 en retard → taux de réalisation 67 %
        $this->creer($this->sami, today()->subDays(10), 'Terminée', 1000, Intervention::TYPE_CORRECTIVE, $this->station);
        $this->creer($this->sami, today()->subDays(5), 'Terminée', 500, Intervention::TYPE_PREVENTIVE, $this->station);
        $this->creer($this->nour, today()->subDays(2), 'Planifiée', 300, Intervention::TYPE_CORRECTIVE, $this->station);
        // À venir
        $this->creer($this->nour, today()->addDays(3), 'Planifiée', 200, Intervention::TYPE_PREVENTIVE);
    }

    private function creer(Technicien $technicien, $date, string $statut, float $cout, string $type, ?Infrastructure $infra = null): Intervention
    {
        return Intervention::factory()->for($technicien)->create([
            'date' => $date->toDateString(),
            'statut' => $statut,
            'cout' => $cout,
            'type' => $type,
            'infrastructure_id' => $infra?->id,
            'description' => 'Intervention de test '.$statut,
        ]);
    }

    public function test_indicators_are_computed_from_real_interventions(): void
    {
        $indicateurs = app(MaintenanceStatsService::class)->indicateurs();

        $this->assertSame(67.0, $indicateurs['taux_realisation']);
        $this->assertSame(1, $indicateurs['en_retard']);
        $this->assertSame(1, $indicateurs['a_venir']);
        $this->assertSame(1500.0, $indicateurs['cout_total']);
        $this->assertSame(750.0, $indicateurs['cout_moyen']);
        $this->assertSame(50.0, $indicateurs['part_preventive']);
    }

    public function test_monthly_costs_split_preventive_and_corrective(): void
    {
        $couts = app(MaintenanceStatsService::class)->coutsParMois();

        $this->assertCount(12, $couts['labels']);
        $this->assertSame(1500.0, array_sum($couts['preventive']) + array_sum($couts['corrective']));
        $this->assertSame(500.0, array_sum($couts['preventive']));
    }

    public function test_workload_and_breakdowns(): void
    {
        $stats = app(MaintenanceStatsService::class);

        $nour = $stats->chargeTechniciens()->firstWhere('technicien.nom', 'Nour Ayari');
        $this->assertSame(1, $nour['a_venir']);
        $this->assertSame(1, $nour['en_retard']);

        $sami = $stats->chargeTechniciens()->firstWhere('technicien.nom', 'Sami Gharbi');
        $this->assertSame(2, $sami['terminees_30j']);
        $this->assertSame(1500.0, $sami['cout_12m']);

        // Les préventives ne comptent pas comme des pannes
        $pannes = $stats->infrastructuresAPannes();
        $this->assertSame('Station El Alia', $pannes->first()['infrastructure']->nom);
        $this->assertSame(2, $pannes->first()['pannes']);
    }

    public function test_statistics_page_shows_real_data(): void
    {
        $this->get(route('manager.maintenance.statistiques'))
            ->assertOk()
            ->assertSee('Statistiques maintenance')
            ->assertSee('67 %')
            ->assertSee('1 500,00 DT')
            ->assertSee('Sami Gharbi')
            ->assertSee('Station El Alia');
    }

    public function test_interventions_page_offers_monthly_report_and_statistics(): void
    {
        $this->get(route('manager.interventions.index'))
            ->assertOk()
            ->assertSee('Rapport mensuel')
            ->assertSee(route('manager.maintenance.rapport-mensuel'))
            ->assertSee(route('manager.maintenance.statistiques'));
    }

    public function test_teams_page_is_left_to_its_original_module(): void
    {
        $this->get(route('manager.teams'))
            ->assertOk()
            ->assertDontSee('Statistiques maintenance');
    }

    public function test_intervention_pdf_is_downloadable(): void
    {
        $intervention = Intervention::where('statut', 'Terminée')->first();

        $response = $this->get(route('manager.interventions.pdf', $intervention))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');

        $this->assertStringContainsString('intervention-'.$intervention->id.'.pdf', $response->headers->get('content-disposition'));
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }

    public function test_monthly_report_pdf_and_month_validation(): void
    {
        $response = $this->get(route('manager.maintenance.rapport-mensuel', ['mois' => today()->format('Y-m')]))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
        $this->assertStringStartsWith('%PDF', $response->getContent());

        $rapport = app(MaintenanceStatsService::class)->rapportMensuel(today()->subDays(2)->startOfMonth());
        $this->assertGreaterThanOrEqual(1, $rapport['interventions']->count());

        $this->get(route('manager.maintenance.rapport-mensuel', ['mois' => 'octobre']))
            ->assertSessionHasErrors('mois');
    }

    public function test_dashboard_and_pdfs_are_reserved_to_managers(): void
    {
        $intervention = Intervention::first();
        $this->withSession(['user' => ['name' => 'Amira', 'role' => 'technicien']]);

        $this->get(route('manager.maintenance.statistiques'))->assertForbidden();
        $this->get(route('manager.maintenance.rapport-mensuel'))->assertForbidden();
        $this->get(route('manager.interventions.pdf', $intervention))->assertForbidden();
    }
}
