<?php

namespace Tests\Feature;

use App\Enums\InfrastructureType;
use App\Models\Infrastructure;
use App\Models\Intervention;
use App\Models\Technicien;
use App\Models\Zone;
use App\Services\ReportAssistantService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ReportAssistantTest extends TestCase
{
    use RefreshDatabase;

    private Technicien $technicien;

    private Intervention $intervention;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        $this->technicien = Technicien::factory()->create(['nom' => 'Amira Ben Ali', 'disponibilite' => 'Disponible']);
        $station = Infrastructure::factory()->create([
            'zone_id' => Zone::create(['nom' => 'Zone Tunis', 'localisation' => 'Tunis'])->id,
            'nom' => 'Station El Menzah',
            'reference' => 'INF-RAP-1',
            'type' => InfrastructureType::StationPompage,
        ]);
        $this->intervention = Intervention::factory()->for($this->technicien)->create([
            'infrastructure_id' => $station->id,
            'date' => today()->toDateString(),
            'statut' => 'En cours',
            'description' => 'Fuite au niveau de la pompe 2',
        ]);

        $this->withSession(['user' => [
            'name' => 'Amira Ben Ali',
            'email' => 'amira@aquasecure.tn',
            'role' => 'technicien',
            'technicien_id' => $this->technicien->id,
        ]]);
    }

    private function fakeOllama(array $reponse): void
    {
        Http::fake(['*/api/generate' => Http::response(['response' => json_encode($reponse)])]);
    }

    private function rediger(string $notes = 'joint pompe 2 HS changé, 2 joints DN100, fuite stoppée, revoir pression dans 1 semaine'): array
    {
        return $this->postJson(route('technician.interventions.report.ia', $this->intervention->id), ['notes' => $notes])
            ->assertOk()
            ->json();
    }

    public function test_ai_turns_quick_notes_into_a_structured_report(): void
    {
        $this->fakeOllama([
            'status' => 'followup',
            'work_performed' => 'Remplacement des joints de la pompe 2. La fuite est stoppée.',
            'cause' => 'Usure des joints de la pompe 2.',
            'observations' => 'Contrôler la pression dans une semaine.',
            'nouvel_etat' => 'En service',
            'parts' => [['name' => 'Joint DN100', 'qty' => 2]],
        ]);

        $rapport = $this->rediger();

        $this->assertSame('ia', $rapport['source']);
        $this->assertSame('followup', $rapport['status']);
        $this->assertSame('Remplacement des joints de la pompe 2. La fuite est stoppée.', $rapport['work_performed']);
        $this->assertSame('Usure des joints de la pompe 2.', $rapport['cause']);
        $this->assertSame('En service', $rapport['nouvel_etat']);
        $this->assertSame([['name' => 'Joint DN100', 'qty' => 2]], $rapport['parts']);

        // Contexte transmis au modèle : notes, équipement, consigne de ne rien inventer
        Http::assertSent(fn (Request $request) => $request['format'] === 'json'
            && str_contains($request['prompt'], 'joint pompe 2 HS changé')
            && str_contains($request['prompt'], 'Station El Menzah')
            && str_contains($request['prompt'], "N'invente aucun fait"));
    }

    public function test_invalid_ai_values_are_sanitised(): void
    {
        $this->fakeOllama([
            'status' => 'parfait',
            'work_performed' => 'Remplacement du joint.',
            'nouvel_etat' => 'Comme neuf',
            'cause' => ['pas une chaîne'],
            'parts' => [['name' => 'Joint', 'qty' => 500], ['name' => ''], 'n\'importe quoi', ['qty' => 3]],
        ]);

        $rapport = $this->rediger('joint changé, réparation provisoire en attendant la pièce');

        $this->assertSame('ia', $rapport['source']);
        $this->assertSame('partial', $rapport['status']); // statut inventé → mots-clés des notes
        $this->assertNull($rapport['nouvel_etat']);
        $this->assertSame('', $rapport['cause']);
        $this->assertSame([['name' => 'Joint', 'qty' => 99]], $rapport['parts']);
    }

    public function test_the_most_cautious_result_wins_and_faulty_parts_are_dropped(): void
    {
        $this->fakeOllama([
            'status' => 'resolved',
            'work_performed' => 'Remplacement des joints.',
            'parts' => [
                ['name' => 'Joint DN100', 'qty' => 2],
                ['name' => 'joint DN100', 'qty' => 2],
                ['name' => 'Joint pompe 2 HS', 'qty' => 1],
            ],
        ]);

        $rapport = $this->rediger('joints changés, fuite stoppée, revoir dans 1 semaine');

        $this->assertSame('followup', $rapport['status']);
        $this->assertSame([['name' => 'Joint DN100', 'qty' => 2]], $rapport['parts']);
    }

    public function test_ai_text_claiming_success_on_a_failure_is_discarded(): void
    {
        $this->fakeOllama([
            'status' => 'resolved',
            'work_performed' => 'Vanne remplacée, réseau rétabli.',
            'nouvel_etat' => 'En service',
        ]);

        $rapport = $this->rediger('impossible de fermer la vanne, pièce manquante, réseau coupé');

        $this->assertSame('secours', $rapport['source']);
        $this->assertSame('failed', $rapport['status']);
        $this->assertSame('Impossible de fermer la vanne, pièce manquante, réseau coupé.', $rapport['work_performed']);
        $this->assertNull($rapport['nouvel_etat']);
    }

    public function test_a_failure_described_in_the_notes_takes_precedence(): void
    {
        $this->fakeOllama(['status' => 'followup', 'work_performed' => 'Fermeture de la vanne impossible faute de pièce.']);

        $rapport = $this->rediger('impossible de fermer la vanne, pièce manquante');

        $this->assertSame('ia', $rapport['source']);
        $this->assertSame('failed', $rapport['status']);
    }

    public function test_failed_or_partial_work_is_never_marked_in_service(): void
    {
        $this->fakeOllama([
            'status' => 'partial',
            'work_performed' => 'Réparation provisoire du collier.',
            'nouvel_etat' => 'En service',
        ]);

        $rapport = $this->rediger('collier posé en provisoire');

        $this->assertSame('partial', $rapport['status']);
        $this->assertNull($rapport['nouvel_etat']);
        $this->assertSame('À prévoir : collier posé en provisoire.', $rapport['observations']);
    }

    public function test_draft_without_ollama_reuses_notes_and_guesses_the_result(): void
    {
        Http::fake(fn () => throw new ConnectionException('Connection refused'));

        $rapport = $this->rediger('impossible de démonter la vanne, pièce manquante');

        $this->assertSame('secours', $rapport['source']);
        $this->assertSame('failed', $rapport['status']);
        $this->assertSame('Impossible de démonter la vanne, pièce manquante.', $rapport['work_performed']);
        $this->assertSame([], $rapport['parts']);
    }

    public function test_empty_ai_answer_falls_back_to_the_draft(): void
    {
        $this->fakeOllama(['status' => 'resolved', 'work_performed' => '   ']);

        $this->assertSame('secours', $this->rediger()['source']);
    }

    public function test_keyword_detection_for_each_result(): void
    {
        $service = app(ReportAssistantService::class);

        $this->assertSame('resolved', $service->resultatParMotsCles('fuite réparée, tout fonctionne'));
        $this->assertSame('partial', $service->resultatParMotsCles('réparation provisoire'));
        $this->assertSame('followup', $service->resultatParMotsCles('à revoir la semaine prochaine'));
        $this->assertSame('failed', $service->resultatParMotsCles('échec, ouvrage à l\'arrêt'));
    }

    public function test_nothing_is_saved_until_the_technician_validates(): void
    {
        $this->fakeOllama(['status' => 'resolved', 'work_performed' => 'Réparation effectuée.']);

        $this->rediger();

        $intervention = $this->intervention->fresh();
        $this->assertSame('En cours', $intervention->statut);
        $this->assertSame('Fuite au niveau de la pompe 2', $intervention->description);
    }

    public function test_notes_are_required_and_only_the_assigned_technician_can_use_it(): void
    {
        $this->postJson(route('technician.interventions.report.ia', $this->intervention->id), ['notes' => ''])
            ->assertStatus(422)
            ->assertJsonValidationErrors('notes');

        $autre = Intervention::factory()->create(['statut' => 'En cours', 'date' => today()->toDateString()]);
        $this->postJson(route('technician.interventions.report.ia', $autre->id), ['notes' => 'notes de test'])
            ->assertForbidden();

        $this->withSession(['user' => ['name' => 'Gestionnaire', 'role' => 'manager']]);
        $this->postJson(route('technician.interventions.report.ia', $this->intervention->id), ['notes' => 'notes de test'])
            ->assertForbidden();
    }

    public function test_report_page_shows_the_assistant(): void
    {
        $this->get(route('technician.interventions.report', $this->intervention->id))
            ->assertOk()
            ->assertSee('Assistant de rédaction IA')
            ->assertSee(route('technician.interventions.report.ia', $this->intervention->id));
    }
}
