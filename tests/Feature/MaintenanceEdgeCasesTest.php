<?php

namespace Tests\Feature;

use App\Enums\InfrastructureType;
use App\Models\Infrastructure;
use App\Models\Intervention;
use App\Models\Role;
use App\Models\Technicien;
use App\Models\User;
use App\Models\Zone;
use App\Services\FailureRiskService;
use App\Services\InterventionPlanningService;
use App\Services\OllamaClient;
use App\Services\PreventiveMaintenanceService;
use App\Services\ReportAssistantService;
use App\Services\TechnicianRecommendationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Cas limites du module Maintenance : situations rares mais réelles (données absentes,
 * modèle désactivé, réponse incomplète de l'IA…) que les scénarios principaux ne traversent pas.
 */
class MaintenanceEdgeCasesTest extends TestCase
{
    use RefreshDatabase;

    private Technicien $pompage;

    private Zone $zone;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->withSession([
            'user' => ['name' => 'Gestionnaire', 'email' => 'gestionnaire@aquasecure.tn', 'role' => 'manager'],
        ]);

        $this->zone = Zone::create(['nom' => 'Zone Bizerte', 'localisation' => 'Bizerte']);
        $this->pompage = Technicien::factory()->create(['nom' => 'Sami Gharbi', 'specialite' => 'Pompage', 'disponibilite' => 'Disponible']);
    }

    private function station(array $attributs = []): Infrastructure
    {
        return Infrastructure::factory()->create(array_merge([
            'zone_id' => $this->zone->id,
            'nom' => 'Station El Alia',
            'type' => InfrastructureType::StationPompage,
        ], $attributs));
    }

    private function fakeOllama(array $reponse): void
    {
        Http::fake(['*/api/generate' => Http::response(['response' => json_encode($reponse)])]);
    }

    // ── Planification ─────────────────────────────────────────────────────────

    public function test_unknown_technician_is_left_to_the_exists_validation_rule(): void
    {
        $this->assertNull(app(InterventionPlanningService::class)->verifier(999999, today()->toDateString(), 'Planifiée'));
    }

    public function test_next_due_date_uses_the_default_period_for_an_untyped_infrastructure(): void
    {
        $depuis = today();

        $echeance = app(PreventiveMaintenanceService::class)->prochaineEcheance(new Infrastructure, $depuis);

        $this->assertSame($depuis->copy()->addMonthsNoOverflow(6)->toDateString(), $echeance->toDateString());
    }

    public function test_preventive_command_reports_when_nothing_is_due(): void
    {
        $this->artisan('maintenance:planifier-preventive')
            ->expectsOutput('Aucune maintenance préventive à planifier.')
            ->assertSuccessful();
    }

    public function test_preventive_command_lists_infrastructures_already_planned(): void
    {
        $station = $this->station(['date_prochaine_maintenance' => today()->addDays(2)->toDateString()]);
        Intervention::factory()->for($this->pompage)->create([
            'infrastructure_id' => $station->id,
            'date' => today()->addDay()->toDateString(),
            'statut' => 'Planifiée',
        ]);

        $this->artisan('maintenance:planifier-preventive', ['--simulation' => true])
            ->expectsOutputToContain('Déjà planifiée')
            ->assertSuccessful();
    }

    public function test_moving_an_intervention_to_another_infrastructure_refreshes_both_risks(): void
    {
        $ancienne = $this->station();
        $nouvelle = $this->station(['nom' => 'Station Menzel']);
        $intervention = Intervention::factory()->for($this->pompage)->create([
            'infrastructure_id' => $ancienne->id,
            'date' => today()->addDay()->toDateString(),
            'statut' => 'Planifiée',
        ]);

        $recalculees = [];
        $this->mock(FailureRiskService::class)
            ->shouldReceive('recompute')
            ->andReturnUsing(function (Infrastructure $infrastructure) use (&$recalculees) {
                $recalculees[] = $infrastructure->id;

                return [];
            });

        $intervention->update(['infrastructure_id' => $nouvelle->id]);

        $this->assertEqualsCanonicalizing([$ancienne->id, $nouvelle->id], $recalculees);
    }

    // ── Documents PDF ─────────────────────────────────────────────────────────

    public function test_pdf_of_an_intervention_without_infrastructure(): void
    {
        $intervention = Intervention::factory()->for($this->pompage)->create(['infrastructure_id' => null]);

        $this->get(route('manager.interventions.pdf', $intervention))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_monthly_report_defaults_to_the_current_month(): void
    {
        $this->get(route('manager.maintenance.rapport-mensuel'))
            ->assertOk()
            ->assertDownload('rapport-maintenance-'.today()->format('Y-m').'.pdf');
    }

    // ── Fiche technicien ──────────────────────────────────────────────────────

    public function test_edit_form_keeps_the_account_already_linked_to_the_fiche(): void
    {
        $this->artisan('db:seed', ['--class' => 'RbacSeeder']);
        $compte = User::factory()->create(['email' => 'sami.gharbi@aquasecure.tn', 'is_active' => true]);
        $compte->assignRole(Role::TECHNICIEN);
        $this->pompage->update(['user_id' => $compte->id]);

        $this->get(route('manager.techniciens.edit', $this->pompage))
            ->assertOk()
            ->assertSee('sami.gharbi@aquasecure.tn');
    }

    // ── IA : client Ollama ────────────────────────────────────────────────────

    public function test_disabled_ollama_is_never_called(): void
    {
        config(['ollama.enabled' => false]);
        Http::fake();

        $this->assertNull(app(OllamaClient::class)->json('Bonjour'));
        Http::assertNothingSent();
    }

    // ── IA : recommandation du technicien ─────────────────────────────────────

    public function test_endpoint_works_without_equipment_and_guesses_the_specialty(): void
    {
        Http::fake(['*/api/generate' => Http::response(status: 500)]);

        $this->postJson(route('manager.interventions.recommandation'), [
            'date' => today()->addDays(2)->toDateString(),
            'description' => 'Pompe de relevage bruyante',
        ])
            ->assertOk()
            ->assertJsonPath('specialite_attendue', 'Pompage')
            ->assertJsonPath('technicien.id', $this->pompage->id);

        Http::assertSent(fn (HttpRequest $requete) => str_contains($requete['prompt'], 'aucun équipement précisé'));
    }

    public function test_server_warns_when_ai_skips_an_available_specialist(): void
    {
        $electricien = Technicien::factory()->create(['nom' => 'Karim Mejri', 'specialite' => 'Électricité', 'disponibilite' => 'Disponible']);
        $this->fakeOllama(['technicien_id' => $electricien->id, 'justification' => 'Karim est disponible.', 'vigilance' => '']);

        $resultat = app(TechnicianRecommendationService::class)->recommander($this->station(), today()->addDays(2), 'Pompe en panne');

        $this->assertSame($electricien->id, $resultat['technicien']['id']);
        $this->assertSame('Un technicien « Pompage » est disponible : vérifiez ce choix hors spécialité.', $resultat['vigilance']);
    }

    public function test_experience_in_the_same_zone_is_counted(): void
    {
        $voisine = $this->station(['nom' => 'Forage voisin']);
        Intervention::factory()->for($this->pompage)->create([
            'infrastructure_id' => $voisine->id,
            'date' => today()->subWeek()->toDateString(),
            'statut' => 'Terminée',
        ]);

        $classement = app(TechnicianRecommendationService::class)
            ->classement($this->station(), today()->addDays(2), 'Pompage');

        $sami = $classement->firstWhere('id', $this->pompage->id);
        $this->assertSame(1, $sami['experience_zone']);
        $this->assertSame(0, $sami['experience_equipement']);
        $this->assertContains('1 intervention(s) dans cette zone', $sami['raisons']);
    }

    // ── IA : rédaction du rapport ─────────────────────────────────────────────

    public function test_report_assistant_handles_an_intervention_without_equipment_and_malformed_parts(): void
    {
        $intervention = Intervention::factory()->for($this->pompage)->create(['infrastructure_id' => null, 'statut' => 'En cours']);
        $this->fakeOllama([
            'status' => 'resolved',
            'work_performed' => 'Remplacement du joint de la vanne.',
            'parts' => 'un joint',
        ]);

        $rapport = app(ReportAssistantService::class)->rediger($intervention, 'joint vanne changé, fuite stoppée');

        $this->assertSame('ia', $rapport['source']);
        $this->assertSame([], $rapport['parts']);
        Http::assertSent(fn (HttpRequest $requete) => str_contains($requete['prompt'], 'équipement : non précisé'));
    }

    public function test_no_follow_up_is_invented_when_the_notes_announce_none(): void
    {
        $this->assertSame('', app(ReportAssistantService::class)->suiteADonner('Fuite réparée, réseau remis en eau.'));
    }
}
