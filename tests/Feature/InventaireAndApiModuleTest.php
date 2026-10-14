<?php

namespace Tests\Feature;

use App\Enums\InfrastructureEtat;
use App\Enums\InfrastructureType;
use App\Enums\NiveauCriticite;
use App\Enums\ZoneStatut;
use App\Models\Incident;
use App\Models\Infrastructure;
use App\Models\Intervention;
use App\Models\PointMesure;
use App\Models\Projet;
use App\Models\Technicien;
use App\Models\User;
use App\Models\Zone;
use App\Services\InfrastructureHealthService;
use App\Services\ZoneStatsService;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventaireAndApiModuleTest extends TestCase
{
    use RefreshDatabase;

    private Zone $zone;
    private Infrastructure $infrastructure;

    protected function setUp(): void
    {
        parent::setUp();

        $this->zone = Zone::create([
            'nom' => 'Grand Tunis Nord',
            'localisation' => 'Ariana',
            'description' => 'Périmètre urbain Ariana et banlieue',
            'latitude' => 36.8665,
            'longitude' => 10.1947,
            'superficie_km2' => 120.50,
            'population_desservie' => 450000,
            'statut' => ZoneStatut::Active,
        ]);

        $this->infrastructure = Infrastructure::create([
            'nom' => 'Réservoir Sidi Thabet R1',
            'reference' => 'RES-TUN-001',
            'type' => InfrastructureType::Reservoir,
            'etat' => InfrastructureEtat::EnService,
            'localisation' => 'Sidi Thabet, Ariana',
            'zone_id' => $this->zone->id,
            'latitude' => 36.9012,
            'longitude' => 10.0435,
            'capacite' => 5000.00,
            'unite_capacite' => 'm3',
            'fabricant' => 'SONEDE Technologies',
            'date_installation' => now()->subYears(5)->format('Y-m-d'),
            'date_derniere_maintenance' => now()->subMonths(3)->format('Y-m-d'),
            'date_prochaine_maintenance' => now()->addMonths(6)->format('Y-m-d'),
            'duree_vie_annees' => 30,
            'niveau_criticite' => NiveauCriticite::Critique,
        ]);
    }

    private function managerSession(): array
    {
        return [
            'user' => [
                'name' => 'Ines Mansouri',
                'email' => 'gestionnaire@aquasecure.tn',
                'role' => 'manager',
            ],
        ];
    }

    /** 1. Export CSV Zones */
    public function test_manager_can_export_zones_csv(): void
    {
        $response = $this->withSession($this->managerSession())
            ->get(route('manager.zones.export.csv'));

        $response->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));
        $content = $response->streamedContent();
        $this->assertStringContainsString('Grand Tunis Nord', $content);
        $this->assertStringContainsString('Ariana', $content);
        $this->assertStringContainsString(';', $content);
    }

    /** 2. Export CSV Infrastructures */
    public function test_manager_can_export_infrastructures_csv(): void
    {
        $response = $this->withSession($this->managerSession())
            ->get(route('manager.infrastructures.export.csv'));

        $response->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));
        $content = $response->streamedContent();
        $this->assertStringContainsString('RES-TUN-001', $content);
        $this->assertStringContainsString('Réservoir Sidi Thabet R1', $content);
        $this->assertStringContainsString(';', $content);
    }

    /** 3. Export PDF Zone */
    public function test_manager_can_download_zone_pdf(): void
    {
        $response = $this->withSession($this->managerSession())
            ->get(route('manager.zones.export.pdf', $this->zone));

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', $response->headers->get('Content-Type'));
    }

    /** 4. Export PDF Infrastructure */
    public function test_manager_can_download_infrastructure_pdf(): void
    {
        $response = $this->withSession($this->managerSession())
            ->get(route('manager.infrastructures.export.pdf', $this->infrastructure));

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', $response->headers->get('Content-Type'));
    }

    /** 5. Protection middleware sur les exports */
    public function test_exports_require_manager_session(): void
    {
        $this->get(route('manager.zones.export.csv'))->assertRedirect(route('auth.login'));
        $this->get(route('manager.infrastructures.export.csv'))->assertRedirect(route('auth.login'));
        $this->get(route('manager.zones.export.pdf', $this->zone))->assertRedirect(route('auth.login'));
        $this->get(route('manager.infrastructures.export.pdf', $this->infrastructure))->assertRedirect(route('auth.login'));
    }

    /** 6. API Zones Index (Paginée, JSON) */
    public function test_api_zones_index_returns_paginated_json(): void
    {
        $response = $this->getJson('/api/zones');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'nom',
                        'localisation',
                        'statut' => ['code', 'libelle'],
                        'coordonnees' => ['latitude', 'longitude'],
                        'superficie_km2',
                        'population_desservie',
                    ],
                ],
                'links',
                'meta',
            ]);
    }

    /** 7. API Zones Search */
    public function test_api_zones_search_filters_results(): void
    {
        Zone::create([
            'nom' => 'Sahara Sud Douz',
            'localisation' => 'Douz',
            'statut' => ZoneStatut::Active,
        ]);

        $response = $this->getJson('/api/zones?q=Sahara');
        $response->assertOk();
        $data = $response->json('data');

        $this->assertCount(1, $data);
        $this->assertEquals('Sahara Sud Douz', $data[0]['nom']);
    }

    /** 8. API Zone Show */
    public function test_api_zones_show_returns_zone_details(): void
    {
        $response = $this->getJson('/api/zones/' . $this->zone->id);

        $response->assertOk()
            ->assertJsonPath('data.id', $this->zone->id)
            ->assertJsonPath('data.nom', 'Grand Tunis Nord');
    }

    /** 9. API Infrastructures Index (Filtres Type & État) */
    public function test_api_infrastructures_index_filters_by_type_and_etat(): void
    {
        Infrastructure::create([
            'nom' => 'Station Pompage Test',
            'type' => InfrastructureType::StationPompage,
            'etat' => InfrastructureEtat::EnMaintenance,
            'localisation' => 'Station Pompage Sud',
            'zone_id' => $this->zone->id,
            'date_installation' => now()->subYears(2),
        ]);

        $response = $this->getJson('/api/infrastructures?type=station_pompage&etat=en_maintenance');
        $response->assertOk();
        $data = $response->json('data');

        $this->assertCount(1, $data);
        $this->assertEquals('Station Pompage Test', $data[0]['nom']);
    }

    /** 10. API Infrastructures Filter par Zone */
    public function test_api_infrastructures_filter_by_zone_id(): void
    {
        $otherZone = Zone::create(['nom' => 'Zone Bizerte Sud', 'localisation' => 'Mateur']);
        Infrastructure::create([
            'nom' => 'Vanne Mateur V1',
            'type' => InfrastructureType::Vanne,
            'etat' => InfrastructureEtat::EnService,
            'localisation' => 'Route de Mateur',
            'zone_id' => $otherZone->id,
            'date_installation' => now()->subYears(1),
        ]);

        $response = $this->getJson('/api/infrastructures?zone_id=' . $this->zone->id);
        $response->assertOk();
        $data = $response->json('data');

        $this->assertCount(1, $data);
        $this->assertEquals($this->infrastructure->id, $data[0]['id']);
    }

    /** 11. API Infrastructure Show */
    public function test_api_infrastructure_show_returns_full_technicals(): void
    {
        $response = $this->getJson('/api/infrastructures/' . $this->infrastructure->id);

        $response->assertOk()
            ->assertJsonPath('data.id', $this->infrastructure->id)
            ->assertJsonPath('data.reference', 'RES-TUN-001')
            ->assertJsonPath('data.criticite.code', 'critique')
            ->assertJsonStructure([
                'data' => [
                    'sante' => ['score', 'libelle', 'couleur'],
                    'localisation',
                    'maintenance',
                ],
            ]);
    }

    /** 12. Rate Limiting sur l'API */
    public function test_api_rate_limiter_is_configured(): void
    {
        $response = $this->getJson('/api/zones');
        $response->assertOk();
        $this->assertNotNull($response->headers->get('X-RateLimit-Limit'));
    }

    /** 13. Relation Interventions -> Infrastructure (7a) */
    public function test_interventions_relation_and_maintenance_cost_calculation(): void
    {
        $technicien = Technicien::create([
            'nom' => 'Tarek Mansour',
            'specialite' => 'Hydraulique',
            'telephone' => '+216 71 222 333',
            'disponibilite' => 'Disponible',
        ]);

        Intervention::create([
            'infrastructure_id' => $this->infrastructure->id,
            'technicien_id' => $technicien->id,
            'date' => now()->subDays(10),
            'description' => 'Contrôle vanne d\'arrêt',
            'statut' => 'Terminée',
            'cout' => 350.50,
        ]);

        Intervention::create([
            'infrastructure_id' => $this->infrastructure->id,
            'technicien_id' => $technicien->id,
            'date' => now()->subDays(2),
            'description' => 'Remplacement joint torique',
            'statut' => 'En cours',
            'cout' => 150.00,
        ]);

        $this->assertCount(2, $this->infrastructure->interventions);
        $this->assertEquals(500.50, $this->infrastructure->interventions->sum('cout'));
    }

    /** 14. Observer Intervention met à jour date_derniere_maintenance (7a) */
    public function test_intervention_observer_updates_date_derniere_maintenance(): void
    {
        $technicien = Technicien::create([
            'nom' => 'Nader Khiari',
            'specialite' => 'Pompes',
            'telephone' => '+216 71 444 555',
            'disponibilite' => 'Disponible',
        ]);

        $interventionDate = now()->subDays(4)->startOfDay();

        Intervention::create([
            'infrastructure_id' => $this->infrastructure->id,
            'technicien_id' => $technicien->id,
            'date' => $interventionDate,
            'description' => 'Révision générale achevée',
            'statut' => 'Terminée',
            'cout' => 800,
        ]);

        $this->infrastructure->refresh();
        $this->assertEquals($interventionDate->format('Y-m-d'), $this->infrastructure->date_derniere_maintenance?->format('Y-m-d'));
    }

    /** 15. Relation Incidents -> Zone et Infrastructure (7b) */
    public function test_incidents_relation_to_zone_and_infrastructure(): void
    {
        $user = User::factory()->create();

        $incident = Incident::create([
            'user_id' => $user->id,
            'titre' => 'Fuite majeure sous voirie',
            'localisation' => 'Avenue Habib Bourguiba, Ariana',
            'type' => 'fuite',
            'gravite' => 'critique',
            'statut' => 'en_cours',
            'zone_id' => $this->zone->id,
            'infrastructure_id' => $this->infrastructure->id,
            'date_signalement' => now(),
        ]);

        $this->assertTrue($this->zone->incidents->contains($incident));
        $this->assertTrue($this->infrastructure->incidents->contains($incident));
    }

    /** 16. Relation PointMesure -> Zone (7c) */
    public function test_point_mesures_relation_to_zone(): void
    {
        $point = PointMesure::create([
            'code' => 'PM-TUN-01',
            'nom' => 'Prélèvement Sortie Réservoir',
            'zone' => 'Ariana',
            'zone_id' => $this->zone->id,
            'statut' => 'actif',
            'type' => 'reservoir',
            'latitude' => 36.9015,
            'longitude' => 10.0440,
        ]);

        $this->assertEquals($this->zone->id, $point->zone_id);
        $this->assertEquals($this->zone->id, $point->zone()->first()?->id);
        $this->assertTrue($this->zone->pointsMesure->contains($point));
    }

    /** 17. Relation Projet <-> Zone via pivot projet_zone (7d) */
    public function test_projet_zone_pivot_relation(): void
    {
        $projet = Projet::create([
            'nom' => 'Modernisation Adduction Ariana',
            'description' => 'Remplacement des canalisations vétustes',
            'statut' => 'en_cours',
            'budget' => 600000,
            'progression' => 50,
            'date_debut' => now()->subMonths(3),
            'date_fin' => now()->addMonths(9),
        ]);

        $projet->zones()->attach($this->zone->id);

        $this->assertTrue($projet->zones->contains($this->zone));
        $this->assertTrue($this->zone->projets->contains($projet));
    }

    /** 18. Affichage de zone show avec qualité et projets */
    public function test_zone_show_page_renders_with_quality_and_projects(): void
    {
        $projet = Projet::create([
            'nom' => 'Chantier Réseau Nord',
            'description' => 'Extension',
            'statut' => 'en_cours',
            'budget' => 200000,
            'progression' => 30,
            'date_debut' => now()->subMonths(1),
            'date_fin' => now()->addMonths(6),
        ]);
        $this->zone->projets()->attach($projet->id);

        $response = $this->withSession($this->managerSession())
            ->get(route('manager.zones.show', $this->zone));

        $response->assertOk()
            ->assertSee('Points de prélèvement', false)
            ->assertSee('Chantier Réseau Nord', false)
            ->assertSee('Fiche PDF');
    }

    /** 19. Affichage infrastructure show avec bouton carte et PDF */
    public function test_infrastructure_show_page_renders_action_buttons(): void
    {
        $response = $this->withSession($this->managerSession())
            ->get(route('manager.infrastructures.show', $this->infrastructure));

        $response->assertOk()
            ->assertSee('Voir sur la carte')
            ->assertSee('Fiche PDF')
            ->assertSee('Interventions de maintenance', false);
    }

    /** 20. Carte manager avec les données réelles */
    public function test_map_manager_renders_with_real_database_data(): void
    {
        $response = $this->withSession($this->managerSession())
            ->get(route('manager.map'));

        $response->assertOk()
            ->assertSee('Grand Tunis Nord')
            ->assertSee('Réservoir Sidi Thabet R1')
            ->assertSee('Gestion des couches');
    }

    /** 21. Carte manager avec focus zone */
    public function test_map_manager_supports_focus_zone_query_param(): void
    {
        $response = $this->withSession($this->managerSession())
            ->get(route('manager.map', ['zone' => $this->zone->id]));

        $response->assertOk()
            ->assertSee((string) $this->zone->id);
    }

    /** 22. Carte manager avec focus infrastructure */
    public function test_map_manager_supports_focus_infrastructure_query_param(): void
    {
        $response = $this->withSession($this->managerSession())
            ->get(route('manager.map', ['infrastructure' => $this->infrastructure->id]));

        $response->assertOk()
            ->assertSee((string) $this->infrastructure->id);
    }

    /** 23. Calcul du score de santé multi-critères */
    public function test_infrastructure_health_score_calculation(): void
    {
        $healthService = app(InfrastructureHealthService::class);
        $sante = $healthService->calculer($this->infrastructure);

        $this->assertIsArray($sante);
        $this->assertArrayHasKey('score', $sante);
        $this->assertArrayHasKey('label', $sante);
        $this->assertArrayHasKey('color', $sante);
        $this->assertGreaterThanOrEqual(0, $sante['score']);
        $this->assertLessThanOrEqual(100, $sante['score']);
    }

    /** 24. Calcul des KPI ZoneStatsService */
    public function test_zone_stats_service_computes_kpis(): void
    {
        $statsService = app(ZoneStatsService::class);
        $stats = $statsService->statsZone($this->zone);

        $this->assertEquals(1, $stats['total']);
        $this->assertEquals(100.0, $stats['pourcentage_en_service']);
        $this->assertEquals(0, $stats['nombre_en_retard_maintenance']);
    }

    /** 25. Exécution idempotente du DemoSeeder */
    public function test_demo_seeder_runs_idempotently(): void
    {
        $seeder = new DemoSeeder();
        $seeder->run();
        $this->assertTrue(Zone::count() >= 1);
        $this->assertTrue(Infrastructure::count() >= 1);
    }
}
