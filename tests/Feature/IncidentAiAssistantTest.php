<?php

namespace Tests\Feature;

use App\Models\ActionCorrective;
use App\Models\Incident;
use App\Models\User;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class IncidentAiAssistantTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:', 'database.connections.sqlite.url' => null]);
        DB::purge('sqlite');
        $this->artisan('migrate', ['--force' => true])->assertExitCode(0);
        $this->withoutVite();
        config([
            'services.gemini.api_key' => 'test-key',
            'services.gemini.cache_minutes' => 0,
        ]);
    }

    private function fakeGemini(array $json): void
    {
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [['content' => ['parts' => [['text' => json_encode($json)]]]]],
            ]),
        ]);
    }

    private function citizenSession(User $user): array
    {
        return ['user' => ['name' => $user->name, 'email' => $user->email, 'role' => 'citizen']];
    }

    private function managerSession(): array
    {
        return ['user' => ['name' => 'Gestionnaire', 'role' => 'manager', 'email' => 'gestionnaire@aquasecure.tn']];
    }

    public function test_citizen_gets_advice_for_own_incident_without_status_change(): void
    {
        $owner = User::factory()->create();
        $incident = Incident::factory()->for($owner)->create(['type' => 'fuite', 'statut' => 'signale']);
        $this->fakeGemini([
            'resume' => 'Une fuite a été signalée.',
            'conseils' => ['Évitez la zone glissante.', 'Éloignez les enfants.'],
            'a_eviter' => ['Ne tentez pas de réparer.'],
            'contacter_rapidement_si' => ['L’eau approche des prises électriques.'],
            'message_rassurant' => 'Votre demande est transmise.',
        ]);

        $this->withSession($this->citizenSession($owner))
            ->postJson(route('incidents.ai.advice', $incident))
            ->assertOk()
            ->assertJsonPath('data.conseils.0', 'Évitez la zone glissante.');

        $this->assertSame('signale', $incident->fresh()->statut);
        Http::assertSent(fn (HttpRequest $r) => $r->hasHeader('x-goog-api-key', 'test-key')
            && ! str_contains($r->body(), $owner->email));
    }

    public function test_citizen_cannot_request_advice_for_another_citizens_incident(): void
    {
        $owner = User::factory()->create();
        $incident = Incident::factory()->for(User::factory()->create())->create();
        Http::fake();

        $this->withSession($this->citizenSession($owner))
            ->postJson(route('incidents.ai.advice', $incident))
            ->assertNotFound();

        Http::assertNothingSent();
    }

    public function test_citizen_preview_is_validated(): void
    {
        $owner = User::factory()->create();
        Http::fake();

        $this->withSession($this->citizenSession($owner))
            ->postJson(route('incidents.ai.preview'), ['type' => 'inconnu', 'description' => ''])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['type', 'description']);

        Http::assertNothingSent();
    }

    public function test_manager_analysis_returns_proposals_and_does_not_create_actions(): void
    {
        $incident = Incident::factory()->create(['statut' => 'signale']);
        $this->fakeGemini([
            'resume_technique' => 'Fuite sur conduite principale.',
            'categorie_suggeree' => 'fuite',
            'priorite_suggeree' => 'elevee',
            'justification_priorite' => 'Perte d’eau importante.',
            'faits' => ['Fuite signalée.'],
            'hypotheses' => [['hypothese' => 'Joint usé', 'confiance' => 'moyenne']],
            'verifications' => ['Localiser la fuite.'],
            'outils' => ['Clé de vanne'],
            'epi' => ['Gants'],
            'etapes_resolution' => ['Isoler le tronçon.'],
            'actions_proposees' => [['titre' => 'Isoler le tronçon', 'description' => 'Fermer la vanne amont.', 'resultat_attendu' => 'Débit stoppé', 'delai_jours' => 1]],
            'prevention' => ['Inspection régulière.'],
            'arret_specialiste' => ['Présence de câbles électriques.'],
        ]);

        $this->withSession($this->managerSession())
            ->postJson(route('manager.incidents.ai.analysis', $incident))
            ->assertOk()
            ->assertJsonPath('data.priorite_suggeree', 'elevee')
            ->assertJsonPath('data.actions_proposees.0.titre', 'Isoler le tronçon');

        $this->assertSame(0, ActionCorrective::count());
        $this->assertSame('signale', $incident->fresh()->statut);
    }

    public function test_citizen_cannot_call_manager_analysis(): void
    {
        $incident = Incident::factory()->create();
        Http::fake();

        $this->withSession($this->citizenSession(User::factory()->create()))
            ->postJson(route('manager.incidents.ai.analysis', $incident))
            ->assertForbidden();

        Http::assertNothingSent();
    }

    public function test_missing_api_key_returns_friendly_error(): void
    {
        config(['services.gemini.api_key' => null]);
        $incident = Incident::factory()->create();

        $this->withSession($this->managerSession())
            ->postJson(route('manager.incidents.ai.analysis', $incident))
            ->assertStatus(503)
            ->assertJsonStructure(['message']);
    }
}
