<?php

namespace Tests\Feature;

use App\Enums\InfrastructureEtat;
use App\Enums\InfrastructureType;
use App\Enums\NiveauCriticite;
use App\Enums\ZoneStatut;
use App\Models\Infrastructure;
use App\Models\InfrastructureAlerte;
use App\Models\InfrastructureRiskPrediction;
use App\Models\Intervention;
use App\Models\Technicien;
use App\Models\Zone;
use App\Services\AiExplanationService;
use App\Services\FailureRiskService;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class FailureRiskFeatureTest extends TestCase
{
    use RefreshDatabase;

    private Zone $zone;

    protected function setUp(): void
    {
        parent::setUp();

        $this->zone = Zone::create([
            'nom' => 'Zone test risque',
            'localisation' => 'Tunis',
            'statut' => ZoneStatut::Active,
        ]);
    }

    public function test_recompute_creates_only_one_active_risk_alert(): void
    {
        $infrastructure = $this->infrastructure([
            'etat' => InfrastructureEtat::HorsService,
            'date_installation' => now()->subYears(20)->toDateString(),
            'date_prochaine_maintenance' => now()->subDays(120)->toDateString(),
            'duree_vie_annees' => 10,
            'niveau_criticite' => NiveauCriticite::Critique,
        ]);
        $service = app(FailureRiskService::class);

        $service->recompute($infrastructure);
        $service->recompute($infrastructure, now()->addSecond());

        $this->assertSame(1, InfrastructureAlerte::query()
            ->where('infrastructure_id', $infrastructure->id)
            ->where('type', 'risque_panne')
            ->where('resolue', false)
            ->count());
    }

    public function test_daily_command_is_idempotent_for_the_same_day(): void
    {
        $infrastructure = $this->infrastructure();
        $this->travelTo(now()->startOfDay()->addHours(8));

        Artisan::call('infrastructures:predict-risk');
        $countAfterFirstRun = InfrastructureRiskPrediction::query()
            ->where('infrastructure_id', $infrastructure->id)
            ->count();
        Artisan::call('infrastructures:predict-risk');
        $countAfterSecondRun = InfrastructureRiskPrediction::query()
            ->where('infrastructure_id', $infrastructure->id)
            ->count();

        $this->assertSame($countAfterFirstRun, $countAfterSecondRun);
    }

    public function test_citizen_pages_and_public_infrastructure_json_do_not_expose_risk(): void
    {
        $infrastructure = $this->infrastructure();
        $citizenSession = ['user' => ['name' => 'Citoyen', 'email' => 'citoyen@example.test', 'role' => 'citizen']];

        $this->withSession($citizenSession)
            ->get(route('citizen.infrastructures.show', $infrastructure))
            ->assertOk()
            ->assertDontSee('Risque de panne')
            ->assertDontSee('Estimation indicative');

        $this->withSession($citizenSession)
            ->getJson('/api/infrastructures')
            ->assertOk()
            ->assertJsonMissingPath('data.0.risk');

        $this->withSession($citizenSession)
            ->getJson(route('api.infrastructures.risk', $infrastructure))
            ->assertForbidden();
    }

    public function test_manager_risk_api_is_protected_and_returns_prediction_fields(): void
    {
        $infrastructure = $this->infrastructure();
        $managerSession = ['user' => ['name' => 'Gestionnaire', 'email' => 'manager@example.test', 'role' => 'manager']];

        $this->withSession($managerSession)
            ->getJson(route('api.infrastructures.risk', $infrastructure))
            ->assertOk()
            ->assertJsonStructure(['probability', 'level', 'factors', 'recommendation', 'horizon_days']);
    }

    public function test_optional_ai_explanation_falls_back_when_api_key_is_missing(): void
    {
        Config::set('services.failure_risk.api_key', '');
        Http::preventStrayRequests();

        $explanation = app(AiExplanationService::class)->explain(123, [], 'Explication locale.');

        $this->assertSame('Explication locale.', $explanation);
    }

    public function test_ai_explanation_receives_only_factor_data_and_returns_two_sentences(): void
    {
        Config::set('services.failure_risk.api_key', 'test-key');
        Http::fake([
            '*' => Http::response([
                'choices' => [['message' => ['content' => 'La maintenance est en retard. Les incidents récents augmentent le risque.']]],
            ]),
        ]);
        $factors = [[
            'key' => 'incidents',
            'name' => 'Incidents récents',
            'value' => 0.4,
            'weight' => 1.4,
            'contribution' => 12.1,
            'explanation' => '2 incidents en 12 mois',
        ]];

        $result = app(AiExplanationService::class)->explain(321, $factors, 'Repli local.');

        $this->assertSame('La maintenance est en retard. Les incidents récents augmentent le risque.', $result);
        Http::assertSent(static function (Request $request): bool {
            $payload = json_decode($request['messages'][1]['content'], true);

            return array_keys($payload[0]) === ['key', 'value', 'weight', 'contribution', 'explanation'];
        });
    }

    public function test_optional_ai_explanation_falls_back_when_the_llm_fails(): void
    {
        Config::set('services.failure_risk.api_key', 'test-key');
        Http::fake(['*' => Http::response(['error' => 'unavailable'], 503)]);

        $result = app(AiExplanationService::class)->explain(654, [], 'Repli après erreur.');

        $this->assertSame('Repli après erreur.', $result);
    }

    public function test_manager_detail_contains_risk_and_admin_settings_are_editable(): void
    {
        $infrastructure = $this->infrastructure();
        $managerSession = ['user' => ['name' => 'Gestionnaire', 'email' => 'manager@example.test', 'role' => 'manager']];
        $adminSession = ['user' => ['name' => 'Administrateur', 'email' => 'admin@example.test', 'role' => 'admin']];

        $this->withSession($managerSession)
            ->get(route('manager.infrastructures.show', $infrastructure))
            ->assertOk()
            ->assertSee('Risque de panne')
            ->assertSee('Estimation indicative');

        $this->withSession($adminSession)
            ->get(route('admin.settings.infrastructure-risk'))
            ->assertOk()
            ->assertSee('Poids des caractéristiques');
    }

    public function test_admin_can_change_model_parameters_and_recompute_all(): void
    {
        $this->infrastructure();
        $adminSession = ['user' => ['name' => 'Administrateur', 'email' => 'admin@example.test', 'role' => 'admin']];
        $settings = [
            'bias' => -3.4,
            'weights' => config('infrastructure.risk.weights'),
            'thresholds' => ['moyen' => 24, 'eleve' => 51, 'critique' => 76],
        ];

        $this->withSession($adminSession)
            ->put(route('admin.settings.infrastructure-risk.update'), $settings)
            ->assertRedirect();

        $this->assertSame(24, app(\App\Services\InfrastructureRiskSettingsService::class)->configuration()['thresholds']['moyen']);
        $this->withSession($adminSession)
            ->post(route('admin.settings.infrastructure-risk.recompute'))
            ->assertRedirect()
            ->assertSessionHas('success');
    }

    public function test_technician_sheet_shows_risk_and_technician_api_is_scoped(): void
    {
        $technician = Technicien::create([
            'nom' => 'Technicien risque',
            'specialite' => 'Pompage',
            'telephone' => '00000000',
            'disponibilite' => 'Disponible',
        ]);
        $assigned = $this->infrastructure();
        $outside = $this->infrastructure(['nom' => 'Infrastructure hors périmètre']);
        Intervention::create([
            'technicien_id' => $technician->id,
            'infrastructure_id' => $assigned->id,
            'date' => now()->toDateString(),
            'description' => 'Inspection planifiée',
        ]);
        $technicianSession = [
            'user' => [
                'name' => $technician->nom,
                'email' => 'technician@example.test',
                'role' => 'technician',
                'technicien_id' => $technician->id,
            ],
        ];

        $this->withSession($technicianSession)
            ->get(route('technician.infrastructures.show', $assigned))
            ->assertOk()
            ->assertSee('Risque de panne')
            ->assertSee('Estimation indicative')
            ->assertDontSee('Coût total');

        $this->withSession($technicianSession)
            ->getJson(route('api.infrastructures.risk', $assigned))
            ->assertOk();
        $this->withSession($technicianSession)
            ->getJson(route('api.infrastructures.risk', $outside))
            ->assertForbidden();
    }

    public function test_manager_can_filter_infrastructure_list_by_risk_level(): void
    {
        $low = $this->infrastructure(['nom' => 'Ouvrage faible']);
        $high = $this->infrastructure(['nom' => 'Ouvrage critique']);
        InfrastructureRiskPrediction::create([
            'infrastructure_id' => $low->id,
            'probability' => 10,
            'level' => 'Faible',
            'factors' => [],
            'computed_at' => now()->addMinute(),
        ]);
        InfrastructureRiskPrediction::create([
            'infrastructure_id' => $high->id,
            'probability' => 90,
            'level' => 'Critique',
            'factors' => [],
            'computed_at' => now()->addMinute(),
        ]);
        $managerSession = ['user' => ['name' => 'Gestionnaire', 'email' => 'manager@example.test', 'role' => 'manager']];

        $this->withSession($managerSession)
            ->get(route('manager.infrastructures.index', ['risk' => 'Critique', 'sort' => 'risk', 'direction' => 'desc']))
            ->assertOk()
            ->assertSee('Ouvrage critique')
            ->assertDontSee('Ouvrage faible');
    }

    public function test_manager_zone_and_dashboards_display_risk_aggregates(): void
    {
        $infrastructure = $this->infrastructure();
        app(FailureRiskService::class)->recompute($infrastructure, now()->addMinute());
        $managerSession = ['user' => ['name' => 'Gestionnaire', 'email' => 'manager@example.test', 'role' => 'manager']];
        $adminSession = ['user' => ['name' => 'Administrateur', 'email' => 'admin@example.test', 'role' => 'admin']];

        $this->withSession($managerSession)
            ->get(route('manager.dashboard'))
            ->assertOk()
            ->assertSee('Top 5 des infrastructures à risque')
            ->assertSee('Risque moyen par zone');
        $this->withSession($managerSession)
            ->get(route('manager.zones.show', $this->zone))
            ->assertOk()
            ->assertSee('Top 5 des infrastructures à risque')
            ->assertSee('Risque moyen');
        $this->withSession($adminSession)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Risque de panne — supervision globale')
            ->assertSee('Critique');
    }

    public function test_demo_seeder_creates_a_risk_spread_and_trend_history(): void
    {
        for ($index = 1; $index <= 40; $index++) {
            $this->infrastructure(['nom' => "Infrastructure de démonstration {$index}"]);
        }

        $this->seed(DemoSeeder::class);

        $latest = Infrastructure::query()
            ->with('latestRiskPrediction')
            ->get()
            ->pluck('latestRiskPrediction')
            ->filter();
        $total = $latest->count();
        $percentages = $latest->groupBy('level')
            ->map(fn ($predictions) => (int) round($predictions->count() / $total * 100));

        $this->assertGreaterThan(0, $total);
        $this->assertContains('Faible', $latest->pluck('level')->all());
        $this->assertContains('Moyen', $latest->pluck('level')->all());
        $this->assertContains('Élevé', $latest->pluck('level')->all());
        $this->assertContains('Critique', $latest->pluck('level')->all());
        $this->assertGreaterThanOrEqual(40, $percentages['Faible']);
        $this->assertLessThanOrEqual(75, $percentages['Faible']);
        $this->assertGreaterThanOrEqual(10, $percentages['Moyen']);
        $this->assertLessThanOrEqual(40, $percentages['Moyen']);
        $this->assertGreaterThanOrEqual(3, $percentages['Élevé']);
        $this->assertLessThanOrEqual(25, $percentages['Élevé']);
        $this->assertGreaterThanOrEqual(2, $percentages['Critique']);

        $this->assertGreaterThanOrEqual(
            $total * 3,
            InfrastructureRiskPrediction::query()->count(),
        );
    }

    private function infrastructure(array $overrides = []): Infrastructure
    {
        return Infrastructure::create(array_replace([
            'nom' => 'Infrastructure test',
            'type' => InfrastructureType::Reservoir,
            'etat' => InfrastructureEtat::EnService,
            'localisation' => 'Tunis',
            'zone_id' => $this->zone->id,
            'date_installation' => now()->subYears(3)->toDateString(),
            'date_prochaine_maintenance' => now()->addMonths(3)->toDateString(),
            'duree_vie_annees' => 25,
            'niveau_criticite' => NiveauCriticite::Moyen,
        ], $overrides));
    }
}
