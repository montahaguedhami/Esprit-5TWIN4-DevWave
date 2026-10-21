<?php

namespace Tests\Feature;

use App\Models\Infrastructure;
use App\Models\Intervention;
use App\Models\Role;
use App\Models\Technicien;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MetricsEndpointTest extends TestCase
{
    use RefreshDatabase;

    /** Réponse simulée du serveur Ollama. */
    private int $statutOllama = 200;

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.metrics.token' => 'jeton-de-test', 'ollama.url' => 'http://ollama.test']);
        Http::fake(['ollama.test/*' => fn () => Http::response(['models' => []], $this->statutOllama)]);
    }

    private function metriques(): string
    {
        return $this->withToken('jeton-de-test')->get('/metrics')
            ->assertOk()
            ->assertHeader('Content-Type', 'text/plain; version=0.0.4; charset=utf-8')
            ->getContent();
    }

    public function test_endpoint_is_disabled_without_configured_token(): void
    {
        config(['services.metrics.token' => null]);

        $this->get('/metrics')->assertNotFound();
    }

    public function test_wrong_or_missing_token_is_refused(): void
    {
        $this->get('/metrics')->assertUnauthorized();
        $this->withToken('mauvais-jeton')->get('/metrics')->assertUnauthorized();
    }

    public function test_exposes_business_metrics_in_prometheus_format(): void
    {
        $this->artisan('db:seed', ['--class' => 'RbacSeeder']);
        foreach ([Role::TECHNICIEN, Role::TECHNICIEN, Role::MANAGER] as $slug) {
            User::factory()->create()->assignRole($slug);
        }

        $technicien = Technicien::factory()->create();
        Intervention::factory()->for($technicien)->create(['statut' => 'Planifiée', 'date' => today()->addDays(2)->toDateString()]);
        Intervention::factory()->for($technicien)->create(['statut' => 'En cours', 'date' => today()->subDays(3)->toDateString()]);
        Intervention::factory()->for($technicien)->create(['statut' => 'Terminée', 'date' => today()->subDays(5)->toDateString()]);
        Infrastructure::factory()->create([
            'zone_id' => Zone::create(['nom' => 'Zone Supervision', 'localisation' => 'Tunis'])->id,
            'date_prochaine_maintenance' => today()->addDays(3)->toDateString(),
        ]);

        $texte = $this->metriques();

        $this->assertStringContainsString('# TYPE aquasecure_users_total gauge', $texte);
        $this->assertMatchesRegularExpression('/^aquasecure_users_total\{role="technicien"\} \d+$/m', $texte);
        $this->assertStringContainsString('aquasecure_interventions_total{statut="Planifiée"} 1', $texte);
        $this->assertStringContainsString('aquasecure_interventions_total{statut="Terminée"} 1', $texte);
        // Seule l'intervention « En cours » datée d'il y a 3 jours est en retard
        $this->assertStringContainsString("aquasecure_interventions_overdue_total 1\n", $texte);
        $this->assertStringContainsString("aquasecure_preventive_maintenance_due_total 1\n", $texte);
        $this->assertStringContainsString("aquasecure_database_up 1\n", $texte);
        $this->assertStringContainsString("aquasecure_queue_jobs_failed_total 0\n", $texte);
        $this->assertStringContainsString("aquasecure_ollama_up 1\n", $texte);
        $this->assertMatchesRegularExpression('/^aquasecure_build_info\{app_env="testing",php_version="[^"]+",laravel_version="[^"]+"\} 1$/m', $texte);
    }

    public function test_unreachable_ollama_is_reported_without_breaking_the_scrape(): void
    {
        $this->statutOllama = 500;

        $this->assertStringContainsString("aquasecure_ollama_up 0\n", $this->metriques());
    }

    public function test_every_line_is_valid_prometheus_exposition(): void
    {
        foreach (array_filter(explode("\n", $this->metriques())) as $ligne) {
            $this->assertMatchesRegularExpression(
                '/^(# (HELP|TYPE) [a-z_]+ .+|[a-z_]+(\{[a-z_]+="[^"]*"(,[a-z_]+="[^"]*")*\})? -?[0-9.e+-]+)$/',
                $ligne
            );
        }
    }
}
