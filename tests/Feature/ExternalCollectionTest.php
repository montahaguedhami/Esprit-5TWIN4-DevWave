<?php

namespace Tests\Feature;

use App\Models\MesureQualite;
use App\Models\PointMesure;
use App\Models\ProviderSyncLog;
use App\Models\Role;
use App\Services\ExternalData\CsvImportProvider;
use App\Services\ExternalData\DataGovTnProvider;
use App\Services\ExternalData\ExternalSyncService;
use App\Services\ExternalData\TunisianSimulationProvider;
use App\Services\RagAnalysisService;
use App\Services\WaterQualityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Tests\Concerns\InteractsWithRbac;
use Tests\TestCase;

class ExternalCollectionTest extends TestCase
{
    use RefreshDatabase;
    use InteractsWithRbac;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRbac();
    }

    public function test_water_quality_service_does_not_turn_null_into_zero(): void
    {
        $service = new WaterQualityService();

        $nullOnly = $service->assess(['ph' => null]);
        $this->assertSame([], $nullOnly['parameter_statuses']);
        $this->assertSame('Non evalue', $nullOnly['status']);
        $this->assertNull($nullOnly['overall_compliance']);

        $partial = $service->assess(['ph' => 7.2, 'turbidite' => null]);
        $this->assertSame(['ph' => 'Compliant'], $partial['parameter_statuses']);
        $this->assertSame(100.0, $partial['overall_compliance']);
    }

    public function test_simulation_provider_is_idempotent(): void
    {
        $point = PointMesure::create([
            'code' => 'SIM-001',
            'nom' => 'Point simulation',
            'statut' => 'actif',
            'latitude' => 36.8,
            'longitude' => 10.1,
        ]);

        $provider = app(TunisianSimulationProvider::class);
        $sync = app(ExternalSyncService::class);

        $first = $sync->runProvider($provider);
        $second = $sync->runProvider($provider);

        $this->assertGreaterThan(0, $first['imported']);
        $this->assertSame(0, $second['imported']);
        $this->assertSame(0, $second['failed']);
    }

    public function test_simulation_sources_are_never_treated_as_real(): void
    {
        $measurement = MesureQualite::create([
            'reference' => 'SIM-TEST-1',
            'point_mesure_id' => PointMesure::create([
                'code' => 'SIM-002',
                'nom' => 'Point',
                'statut' => 'actif',
            ])->id,
            'date_mesure' => now(),
            'ph' => 7,
            'turbidite' => 1,
            'chlore_residuel' => 0.5,
            'plomb' => 2,
            'nitrates' => 10,
            'source_donnee' => 'simulation',
            'source_type' => 'simulated',
            'provider' => 'simulation',
            'status' => 'Compliant',
            'overall_compliance' => 100,
        ]);

        $this->assertTrue($measurement->isSimulated());
        $this->assertSame('simulated', $measurement->source_type);
    }

    public function test_data_gov_provider_records_no_data_when_unreachable(): void
    {
        putenv('DATA_GOV_TN_DATASET_ID=demo-dataset');
        config(['app.debug' => true]);

        Http::fake([
            '*' => Http::response(['error' => 'unavailable'], 500),
        ]);

        $provider = new DataGovTnProvider();
        $sync = app(ExternalSyncService::class);

        $result = $sync->runProvider($provider);

        $this->assertSame(0, $result['imported']);
        $this->assertContains($result['error'], [null, '']);
        $this->assertSame(1, ProviderSyncLog::where('provider', 'data_gov_tn')->count());
    }

    public function test_csv_import_creates_measurement_and_log(): void
    {
        $pointCode = 'CSV-POINT';
        PointMesure::create([
            'code' => $pointCode,
            'nom' => 'Point CSV',
            'statut' => 'actif',
            'latitude' => 36.8,
            'longitude' => 10.1,
        ]);

        $csv = "code,nom,latitude,longitude,date_mesure,ph,turbidite,chlore_residuel,plomb,nitrates\n";
        $csv .= "{$pointCode},Point CSV,36.8,10.1,2026-01-01,7.2,0.4,0.8,2.0,10.0\n";

        $file = UploadedFile::fake()->createWithContent('import.csv', $csv);

        $manager = $this->createUserWithRole(Role::MANAGER);

        $response = $this->actingAs($manager)
            ->post(route('manager.data-collection.import-csv'), [
                'file' => $file,
            ]);

        $response->assertOk();
        $this->assertGreaterThanOrEqual(1, MesureQualite::count());
        $this->assertSame(1, ProviderSyncLog::where('provider', 'csv')->count());
    }

    public function test_collection_trigger_is_forbidden_for_citizens(): void
    {
        $citizen = $this->createUserWithRole(Role::CITOYEN);

        $this->actingAs($citizen)
            ->post(route('manager.data-collection.trigger'))
            ->assertForbidden();
    }

    public function test_manager_can_trigger_collection_and_status_is_available(): void
    {
        $manager = $this->createUserWithRole(Role::MANAGER);

        $this->actingAs($manager)
            ->postJson(route('manager.data-collection.trigger'), ['providers' => ['simulation']])
            ->assertOk()
            ->assertJsonStructure(['message', 'status']);

        $this->actingAs($manager)
            ->get(route('manager.data-collection.status'))
            ->assertOk()
            ->assertJsonStructure(['job', 'providers', 'stats']);
    }

    public function test_rag_analysis_falls_back_without_ollama(): void
    {
        $point = PointMesure::create([
            'code' => 'RAG-001',
            'nom' => 'Point RAG',
            'statut' => 'actif',
            'latitude' => 36.8,
            'longitude' => 10.1,
        ]);

        MesureQualite::create([
            'reference' => 'RAG-M-1',
            'point_mesure_id' => $point->id,
            'date_mesure' => now()->subDay(),
            'ph' => 7.2,
            'turbidite' => 0.5,
            'chlore_residuel' => 0.8,
            'plomb' => 2,
            'nitrates' => 10,
            'source_donnee' => 'manuel',
            'source_type' => 'real',
            'provider' => null,
            'status' => 'Compliant',
            'overall_compliance' => 100,
        ]);

        $analysis = app(RagAnalysisService::class)->analysePoint($point, 30);

        $this->assertSame('deterministic_only', $analysis['method']);
        $this->assertArrayHasKey('parameters', $analysis);
        $this->assertArrayHasKey('anomalies', $analysis);
        $this->assertArrayHasKey('limitations', $analysis);
        $this->assertArrayHasKey('executive_summary', $analysis);
        $this->assertArrayHasKey('missing_parameters', $analysis);
        $this->assertArrayHasKey('real_data_percentage', $analysis);
        $this->assertArrayHasKey('triage', $analysis);
        $this->assertArrayHasKey('severity', $analysis['triage']);
        $this->assertArrayHasKey('priority', $analysis['triage']);
        $this->assertArrayHasKey('deadline_hours', $analysis['triage']);
        $this->assertArrayHasKey('technician_required', $analysis['triage']);
        $this->assertArrayHasKey('recommended_action', $analysis['triage']);
    }

    public function test_rag_triage_marks_critical_for_high_lead(): void
    {
        $point = PointMesure::create(['code' => 'TRIAGE-1', 'nom' => 'Point triage', 'statut' => 'actif', 'latitude' => 36.8, 'longitude' => 10.1]);

        MesureQualite::create([
            'reference' => 'TRIAGE-M-1',
            'point_mesure_id' => $point->id,
            'date_mesure' => now()->subDay(),
            'ph' => 7.2,
            'turbidite' => 0.4,
            'chlore_residuel' => 0.8,
            'plomb' => 30,
            'nitrates' => 10,
            'source_donnee' => 'manuel',
            'source_type' => 'real',
            'provider' => null,
            'status' => 'Non-Compliant',
            'overall_compliance' => 75,
        ]);

        $analysis = app(RagAnalysisService::class)->analysePoint($point, 30);

        $this->assertSame('critique', $analysis['triage']['severity']);
        $this->assertTrue($analysis['triage']['technician_required']);
        $this->assertSame(24, $analysis['triage']['deadline_hours']);
    }

    public function test_rag_triage_marks_alert_for_partial_or_missing_data(): void
    {
        $point = PointMesure::create(['code' => 'TRIAGE-2', 'nom' => 'Point triage 2', 'statut' => 'actif', 'latitude' => 36.8, 'longitude' => 10.1]);

        MesureQualite::create([
            'reference' => 'TRIAGE-M-2',
            'point_mesure_id' => $point->id,
            'date_mesure' => now()->subDay(),
            'ph' => 8.9,
            'turbidite' => 0.4,
            'chlore_residuel' => 0.8,
            'plomb' => 2,
            'nitrates' => 10,
            'source_donnee' => 'manuel',
            'source_type' => 'real',
            'provider' => null,
            'status' => 'Non-Compliant',
            'overall_compliance' => 100,
        ]);

        $analysis = app(RagAnalysisService::class)->analysePoint($point, 30);

        $this->assertSame('alerte', $analysis['triage']['severity']);
        $this->assertSame(72, $analysis['triage']['deadline_hours']);
    }

    public function test_quality_page_loads_collection_card_for_managers(): void
    {
        $manager = $this->createUserWithRole(Role::MANAGER);
        $point = PointMesure::create(['code' => 'P-001', 'nom' => 'Point', 'statut' => 'actif']);
        MesureQualite::create([
            'reference' => 'WQ-001',
            'point_mesure_id' => $point->id,
            'date_mesure' => now(),
            'ph' => 7.2,
            'turbidite' => 0.4,
            'chlore_residuel' => 0.8,
            'plomb' => 2,
            'nitrates' => 10,
            'source_donnee' => 'manuel',
            'source_type' => 'real',
            'status' => 'Compliant',
            'overall_compliance' => 100,
        ]);

        $this->actingAs($manager)
            ->get(route('manager.quality'))
            ->assertOk()
            ->assertSee('Actualiser les données')
            ->assertSee('Importer un fichier CSV')
            ->assertSee('Analyse intelligente');
    }
}