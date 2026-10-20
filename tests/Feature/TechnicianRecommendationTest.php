<?php

namespace Tests\Feature;

use App\Enums\InfrastructureType;
use App\Models\Infrastructure;
use App\Models\Intervention;
use App\Models\Technicien;
use App\Models\Zone;
use App\Services\TechnicianRecommendationService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TechnicianRecommendationTest extends TestCase
{
    use RefreshDatabase;

    private Technicien $pompage;

    private Technicien $electricien;

    private Infrastructure $station;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->withSession([
            'user' => ['name' => 'Gestionnaire', 'email' => 'gestionnaire@aquasecure.tn', 'role' => 'manager'],
        ]);

        $this->pompage = Technicien::factory()->create(['nom' => 'Sami Gharbi', 'specialite' => 'Pompage', 'disponibilite' => 'Disponible']);
        $this->electricien = Technicien::factory()->create(['nom' => 'Karim Mejri', 'specialite' => 'Électricité', 'disponibilite' => 'Disponible']);
        $this->station = Infrastructure::factory()->create([
            'zone_id' => Zone::create(['nom' => 'Zone Bizerte', 'localisation' => 'Bizerte'])->id,
            'nom' => 'Station El Alia',
            'reference' => 'INF-IA-1',
            'type' => InfrastructureType::StationPompage,
        ]);
    }

    private function fakeOllama(array $reponse): void
    {
        Http::fake(['*/api/generate' => Http::response(['response' => json_encode($reponse)])]);
    }

    private function recommander(?Infrastructure $infra = null, string $description = 'Pompe de relevage en panne'): array
    {
        return app(TechnicianRecommendationService::class)->recommander($infra ?? $this->station, today()->addDays(2), $description);
    }

    public function test_ai_choice_and_justification_are_returned(): void
    {
        $this->fakeOllama([
            'technicien_id' => $this->pompage->id,
            'justification' => 'Sami est spécialiste du pompage et peu chargé.',
            'vigilance' => 'Station critique : prévoir une pièce de rechange.',
        ]);

        $resultat = $this->recommander();

        $this->assertSame('ia', $resultat['source']);
        $this->assertSame($this->pompage->id, $resultat['technicien']['id']);
        $this->assertSame('Sami est spécialiste du pompage et peu chargé.', $resultat['justification']);
        $this->assertSame('Station critique : prévoir une pièce de rechange.', $resultat['vigilance']);
        $this->assertSame('Pompage', $resultat['specialite_attendue']);

        // Le prompt contient le contexte et les candidats, et demande du JSON au modèle configuré
        Http::assertSent(fn (Request $request) => $request['format'] === 'json'
            && $request['model'] === config('ollama.model')
            && str_contains($request['prompt'], 'Station El Alia')
            && str_contains($request['prompt'], 'id '.$this->pompage->id.' : Sami Gharbi'));
    }

    public function test_falls_back_to_best_score_when_ollama_is_down(): void
    {
        Http::fake(fn () => throw new ConnectionException('Connection refused'));

        $resultat = $this->recommander();

        $this->assertSame('secours', $resultat['source']);
        $this->assertSame($this->pompage->id, $resultat['technicien']['id']);
        $this->assertStringContainsString('meilleur score', $resultat['justification']);
    }

    public function test_ai_answer_outside_the_candidates_is_rejected(): void
    {
        $this->fakeOllama(['technicien_id' => 9999, 'justification' => 'Technicien inventé.']);

        $resultat = $this->recommander();

        $this->assertSame('secours', $resultat['source']);
        $this->assertSame($this->pompage->id, $resultat['technicien']['id']);
    }

    public function test_out_of_specialty_suggestion_is_flagged_by_the_server(): void
    {
        $this->pompage->update(['disponibilite' => 'En congé']);
        $this->fakeOllama(['technicien_id' => $this->electricien->id, 'justification' => 'Seul disponible.', 'vigilance' => '']);

        $resultat = $this->recommander();

        $this->assertSame('ia', $resultat['source']);
        $this->assertStringContainsString('Aucun technicien « Pompage » disponible', $resultat['vigilance']);
        Http::assertSent(fn (Request $request) => str_contains($request['prompt'], "aucun candidat n'a la spécialité Pompage"));
    }

    public function test_on_leave_and_busy_technicians_are_never_proposed(): void
    {
        Http::fake(fn () => throw new ConnectionException('down'));
        $this->pompage->update(['disponibilite' => 'En congé']);
        Intervention::factory()->for($this->electricien)->create(['date' => today()->addDays(2)->toDateString(), 'statut' => 'Planifiée']);

        $resultat = $this->recommander();

        $this->assertSame('aucun', $resultat['source']);
        $this->assertStringContainsString('Aucun technicien disponible', $resultat['message']);
    }

    public function test_experience_on_the_equipment_and_workload_are_scored(): void
    {
        $autrePompage = Technicien::factory()->create(['nom' => 'Nour Ayari', 'specialite' => 'Pompage', 'disponibilite' => 'Disponible']);
        Intervention::factory()->for($autrePompage)->count(2)->create([
            'infrastructure_id' => $this->station->id,
            'date' => today()->subMonth()->toDateString(),
            'statut' => 'Terminée',
        ]);

        $classement = app(TechnicianRecommendationService::class)->classement($this->station, today()->addDays(2), 'Pompage');

        $this->assertSame('Nour Ayari', $classement->first()['nom']);
        $this->assertSame(2, $classement->first()['experience_equipement']);
        $this->assertSame('Karim Mejri', $classement->last()['nom']);
    }

    public function test_specialty_is_guessed_from_the_description_without_equipment(): void
    {
        $service = app(TechnicianRecommendationService::class);

        $this->assertSame('Électricité', $service->specialiteAttendue(null, 'Disjoncteur du tableau électrique HS'));
        $this->assertSame('Canalisations', $service->specialiteAttendue(null, 'Fuite importante rue de Marseille'));
        $this->assertNull($service->specialiteAttendue(null, 'Contrôle général'));
    }

    public function test_endpoint_returns_json_for_the_form(): void
    {
        $this->fakeOllama(['technicien_id' => $this->pompage->id, 'justification' => 'Bon profil.', 'vigilance' => '']);

        $this->postJson(route('manager.interventions.recommandation'), [
            'date' => today()->addDays(2)->toDateString(),
            'description' => 'Pompe en panne',
            'infrastructure_id' => $this->station->id,
        ])
            ->assertOk()
            ->assertJsonPath('source', 'ia')
            ->assertJsonPath('technicien.nom', 'Sami Gharbi')
            ->assertJsonPath('vigilance', null)
            ->assertJsonCount(2, 'classement');

        $this->postJson(route('manager.interventions.recommandation'), [])
            ->assertStatus(422)
            ->assertJsonValidationErrors('date');
    }

    public function test_editing_ignores_the_intervention_itself_for_conflicts(): void
    {
        Http::fake(fn () => throw new ConnectionException('down'));
        $this->electricien->update(['disponibilite' => 'En congé']);
        $intervention = Intervention::factory()->for($this->pompage)->create(['date' => today()->addDays(2)->toDateString(), 'statut' => 'Planifiée']);

        $resultat = app(TechnicianRecommendationService::class)
            ->recommander($this->station, today()->addDays(2), 'Pompe', $intervention->id);

        $this->assertSame($this->pompage->id, $resultat['technicien']['id']);
    }

    public function test_form_shows_the_ai_button_and_endpoint_is_manager_only(): void
    {
        $this->get(route('manager.interventions.create'))
            ->assertOk()
            ->assertSee("Suggérer avec l'IA", false)
            ->assertSee(route('manager.interventions.recommandation'));

        $this->withSession(['user' => ['name' => 'Amira', 'role' => 'technicien']]);
        $this->postJson(route('manager.interventions.recommandation'), ['date' => today()->toDateString()])
            ->assertForbidden();
    }
}
