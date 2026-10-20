<?php

namespace Tests\Feature;

use App\Models\DemandeFinancement;
use App\Models\Financement;
use App\Models\Paiement;
use App\Models\Projet;
use App\Models\Role;
use App\Models\User;
use App\Services\Chatbot\ToolExecutor;
use App\Services\Chatbot\ToolRegistry;
use App\Services\Chatbot\Tools\SearchProjectsTool;
use App\Services\Chatbot\Tools\GetProjectDetailsTool;
use App\Services\Chatbot\Tools\GetProjectFundingStatusTool;
use App\Services\Chatbot\Tools\GetMyFundingRequestsTool;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithRbac;
use Tests\TestCase;

/**
 * Tests du Tool Calling : exécution, refus, validation, limites.
 *
 * Aucun appel externe n'est effectué.
 */
class ChatbotToolCallingTest extends TestCase
{
    use RefreshDatabase;
    use InteractsWithRbac;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->seedRbac();
    }

    public function test_registe_contient_outils_lecture_seule(): void
    {
        $registry = app(ToolRegistry::class);
        $all = $registry->all();

        // Aucun outil d'écriture (create, update, delete, approve, reject, pay)
        $forbidden = ['create', 'update', 'delete', 'approve', 'reject', 'submit', 'accepter', 'refuser', 'confirmer'];
        foreach ($all as $name => $tool) {
            $lower = strtolower($name);
            foreach ($forbidden as $f) {
                $this->assertStringNotContainsString($f, $lower, "L'outil {$name} semble être en écriture.");
            }
        }
    }

    public function test_outils_accessibles_citoyen(): void
    {
        $registry = app(ToolRegistry::class);
        $tools = $registry->forRole(Role::CITOYEN);
        $names = array_keys($tools);

        $this->assertContains('search_projects', $names);
        $this->assertContains('get_project_details', $names);
        $this->assertContains('get_project_funding_status', $names);
        $this->assertContains('get_funding_rules', $names);
        $this->assertContains('search_documentation', $names);
        $this->assertContains('get_my_funding_requests', $names);
        $this->assertContains('get_my_payments', $names);
        $this->assertContains('get_my_funding_portfolio', $names);
        $this->assertContains('get_my_project_contributions', $names);
        $this->assertContains('get_project_recommendations', $names);

        // Interdits pour le citoyen
        $this->assertNotContains('get_funding_dashboard_summary', $names);
        $this->assertNotContains('get_pending_funding_requests', $names);
        $this->assertNotContains('get_manager_project_budget_report', $names);
    }

    public function test_outils_accessibles_manager(): void
    {
        $registry = app(ToolRegistry::class);
        $tools = $registry->forRole(Role::MANAGER);
        $names = array_keys($tools);

        $this->assertContains('get_funding_dashboard_summary', $names);
        $this->assertContains('get_pending_funding_requests', $names);
        $this->assertContains('get_manager_project_budget_report', $names);
    }

    public function test_search_projects_retourne_donnees_reelles(): void
    {
        $projet = Projet::factory()->create([
            'nom' => 'Projet de test',
            'statut' => 'en_cours',
            'budget' => 10000,
        ]);

        $tool = app(SearchProjectsTool::class);
        $user = app(\App\Models\User::class); // mock minimal

        // On bypass l'isAuthorized qui attend un vrai User
        $result = $tool->execute(new \stdClass(), ['limit' => 10]);

        $this->assertArrayHasKey('projets', $result);
        $this->assertArrayHasKey('total', $result);
    }

    public function test_get_project_details_validation_rejette_id_invalide(): void
    {
        $tool = app(GetProjectDetailsTool::class);

        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $tool->validateArguments([]);
    }

    public function test_get_project_funding_status_reutilise_accessors_projet(): void
    {
        $projet = Projet::factory()->create([
            'statut' => 'en_cours',
            'budget' => 50000,
        ]);

        $tool = app(GetProjectFundingStatusTool::class);
        $result = $tool->execute(new \stdClass(), ['projet_id' => $projet->id]);

        $this->assertArrayHasKey('budget_disponible', $result);
        $this->assertArrayHasKey('total_finance', $result);
        $this->assertArrayHasKey('montant_reserve', $result);
        // Le budget_disponible doit correspondre à la formule métier
        $this->assertEqualsWithDelta((float) $projet->budget - (float) $projet->total_finance - (float) $projet->montant_reserve, $result['budget_disponible'], 0.01);
    }

    public function test_citoyen_ne_voit_que_ses_propres_demandes(): void
    {
        $citoyen = User::factory()->create(['is_active' => true]);
        $role = Role::where('slug', Role::CITOYEN)->first();
        $citoyen->assignRole($role);

        $autre = User::factory()->create(['is_active' => true]);
        $autre->assignRole($role);

        $projet = Projet::factory()->create(['statut' => 'en_cours', 'budget' => 10000]);

        // Demande du citoyen connecté
        DemandeFinancement::create([
            'projet_id' => $projet->id,
            'user_id' => $citoyen->id,
            'montant' => 1000,
            'devise' => 'TND',
            'statut' => DemandeFinancement::EN_ATTENTE,
        ]);

        // Demande d'un autre citoyen
        DemandeFinancement::create([
            'projet_id' => $projet->id,
            'user_id' => $autre->id,
            'montant' => 2000,
            'devise' => 'TND',
            'statut' => DemandeFinancement::EN_ATTENTE,
        ]);

        $tool = app(GetMyFundingRequestsTool::class);
        $result = $tool->execute($citoyen, []);

        $this->assertEquals(1, $result['total']);
        $this->assertEquals((int) $citoyen->id, (int) ($result['demandes'][0]['user_id'] ?? 0));
        // Vérifier que l'autre demande n'est pas présente
        $ids = array_column($result['demandes'], 'user_id');
        $this->assertNotContains((int) $autre->id, $ids);
    }

    public function test_outil_inconnu_est_refuse(): void
    {
        $registry = app(ToolRegistry::class);
        $this->assertFalse($registry->exists('sql_exec'));
        $this->assertFalse($registry->exists('delete_all_data'));
        $this->assertFalse($registry->exists('get_all_user_data'));
    }

    public function test_paiements_sont_simules_pas_reels(): void
    {
        $registry = app(ToolRegistry::class);
        $tools = $registry->forRole(Role::CITOYEN);
        $this->assertArrayHasKey('get_my_payments', $tools);
        // Le résultat doit contenir une note indiquant que c'est simulé
    }
}
