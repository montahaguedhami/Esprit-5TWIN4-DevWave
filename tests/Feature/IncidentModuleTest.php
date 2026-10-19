<?php

namespace Tests\Feature;

use App\Models\ActionCorrective;
use App\Models\Incident;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class IncidentModuleTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:', 'database.connections.sqlite.url' => null]);
        DB::purge('sqlite');
        $this->artisan('migrate', ['--force' => true])->assertExitCode(0);
        $this->withoutVite();
    }

    private function citizen(User $user): array
    {
        return ['user' => ['name' => $user->name, 'email' => $user->email, 'role' => 'citizen']];
    }

    private function incidentData(): array
    {
        return ['titre' => 'Fuite dans ma rue', 'description' => 'Une canalisation fuit.', 'type' => 'fuite', 'gravite' => 'elevee', 'statut' => 'signale', 'date_signalement' => '2026-10-07T10:00', 'localisation' => 'Tunis'];
    }

    public function test_citizen_cannot_access_another_citizens_incident(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $incident = Incident::factory()->for($other)->create();
        $this->withSession($this->citizen($owner));
        $this->get('/incidents/'.$incident->id)->assertNotFound();
        $this->get('/incidents/'.$incident->id.'/edit')->assertNotFound();
        $this->put('/incidents/'.$incident->id, $this->incidentData())->assertNotFound();
        $this->delete('/incidents/'.$incident->id)->assertNotFound();
        $this->assertDatabaseHas('incidents', ['id' => $incident->id, 'user_id' => $other->id]);
    }

    public function test_creation_uses_the_connected_citizen_not_the_submitted_owner(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $this->withSession($this->citizen($owner))->post('/incidents', array_merge($this->incidentData(), ['user_id' => $other->id, 'statut' => 'cloture']))->assertRedirect(route('incidents.index'));
        $this->assertDatabaseHas('incidents', ['titre' => 'Fuite dans ma rue', 'user_id' => $owner->id, 'statut' => 'signale']);
    }

    public function test_citizen_can_complete_the_crud_and_only_sees_owned_incidents(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $foreign = Incident::factory()->for($other)->create(['titre' => 'Incident prive autre citoyen']);
        $this->withSession($this->citizen($owner));
        $this->get('/incidents/create')->assertOk()
            ->assertDontSee('name="user_id"', false)
            ->assertSee('name="description" rows="4" maxlength="5000"', false)
            ->assertSee('name="localisation" maxlength="255"', false);
        $this->post('/incidents', $this->incidentData())->assertRedirect(route('incidents.index'));
        $incident = Incident::where('user_id', $owner->id)->firstOrFail();
        $this->get('/incidents')->assertOk()->assertSee($incident->titre)->assertDontSee($foreign->titre);
        $this->get('/incidents/'.$incident->id)->assertOk()->assertDontSee('Ajouter l&#039;action', false)->assertDontSee('/actions', false);
        $this->get('/incidents/'.$incident->id.'/edit')->assertOk();
        $this->put('/incidents/'.$incident->id, array_merge($this->incidentData(), ['titre' => 'Fuite corrigee', 'user_id' => $other->id]))->assertRedirect(route('incidents.show', $incident));
        $this->assertDatabaseHas('incidents', ['id' => $incident->id, 'titre' => 'Fuite corrigee', 'user_id' => $owner->id]);
        $action = ActionCorrective::factory()->for($incident)->create();
        $this->delete('/incidents/'.$incident->id)->assertRedirect(route('incidents.index'));
        $this->assertDatabaseMissing('incidents', ['id' => $incident->id]);
        $this->assertDatabaseMissing('action_correctives', ['id' => $action->id]);
        $this->assertDatabaseHas('incidents', ['id' => $foreign->id]);
    }

    public function test_manager_reads_all_incidents_and_completes_action_crud(): void
    {
        $incidents = Incident::factory()->count(2)->create();
        $incident = $incidents->first();
        $this->withSession(['user' => ['name' => 'Gestionnaire', 'role' => 'manager', 'email' => 'gestionnaire@aquasecure.tn']]);
        $response = $this->get('/manager/incidents')->assertOk()->assertDontSee('Nouveau incident')->assertDontSee('/incidents/create', false);
        foreach ($incidents as $record) {
            $response->assertSee($record->titre);
        }
        $this->get('/manager/incidents/'.$incident->id)->assertOk();
        $data = ['titre' => 'Fermer la vanne', 'description' => 'Isoler la conduite.', 'responsable' => 'Equipe reseau', 'date_prevue' => '2026-10-07', 'date_realisation' => '2026-10-07T12:00', 'statut' => 'a_faire', 'resultat' => 'Vanne fermee.'];
        $this->post('/manager/incidents/'.$incident->id.'/actions', array_merge($data, ['incident_id' => $incidents->last()->id]))->assertRedirect(route('manager.incidents.show', $incident));
        $action = $incident->actions()->firstOrFail();
        $this->assertDatabaseHas('action_correctives', ['id' => $action->id, 'incident_id' => $incident->id]);
        $this->get('/manager/incidents/'.$incident->id)->assertOk()->assertSee('Fermer la vanne');
        $this->get('/manager/actions/'.$action->id.'/edit')->assertOk();
        $this->put('/manager/actions/'.$action->id, array_merge($data, ['statut' => 'terminee', 'date_realisation' => '2026-10-07T12:00', 'resultat' => 'Zone isolee']))->assertRedirect(route('manager.incidents.show', $incident));
        $this->assertDatabaseHas('action_correctives', ['id' => $action->id, 'statut' => 'terminee', 'resultat' => 'Zone isolee']);
        $this->delete('/manager/actions/'.$action->id)->assertRedirect(route('manager.incidents.show', $incident));
        $this->assertDatabaseMissing('action_correctives', ['id' => $action->id]);
        $this->assertDatabaseHas('incidents', ['id' => $incident->id]);
        $this->post('/manager/incidents', $this->incidentData())->assertStatus(405);
        $this->put('/manager/incidents/'.$incident->id, $this->incidentData())->assertStatus(405);
        $this->delete('/manager/incidents/'.$incident->id)->assertStatus(405);
    }

    public function test_separate_seeders_reuse_existing_users_and_incidents(): void
    {
        $users = User::factory()->count(2)->create();
        $this->seed(\Database\Seeders\IncidentSeeder::class);
        $this->assertDatabaseCount('users', 2);
        $this->assertDatabaseCount('incidents', 6);
        $this->assertDatabaseCount('action_correctives', 0);
        foreach ($users as $user) {
            $this->assertSame(3, Incident::where('user_id', $user->id)->count());
        }
        $this->seed(\Database\Seeders\ActionCorrectiveSeeder::class);
        $this->assertDatabaseCount('users', 2);
        $this->assertDatabaseCount('incidents', 6);
        $this->assertDatabaseCount('action_correctives', 12);
        foreach (Incident::withCount('actions')->get() as $incident) {
            $this->assertSame(2, $incident->actions_count);
        }
        $this->seed(\Database\Seeders\IncidentSeeder::class);
        $this->seed(\Database\Seeders\ActionCorrectiveSeeder::class);
        $this->assertDatabaseCount('incidents', 6);
        $this->assertDatabaseCount('action_correctives', 12);
    }

    public function test_database_seeder_can_be_rerun_without_replacing_existing_data(): void
    {
        $existing = User::factory()->create(['email' => 'test@example.com', 'name' => 'Nom existant']);
        $this->seed();
        $counts = [User::count(), Incident::count(), ActionCorrective::count()];
        $this->seed();
        $this->assertSame($counts, [User::count(), Incident::count(), ActionCorrective::count()]);
        $this->assertSame('Nom existant', $existing->fresh()->name);
        $citizen = User::where('email', 'citoyen@aquasecure.tn')->firstOrFail();
        $this->assertTrue(Incident::where('user_id', $citizen->id)->exists());
    }

    public function test_demo_login_resolves_the_existing_citizen_account(): void
    {
        $this->seed();
        $citizen = User::where('email', 'citoyen@aquasecure.tn')->firstOrFail();
        $this->post('/login', ['email' => $citizen->email])->assertRedirect(route('citizen.dashboard'));
        $this->get('/incidents')->assertOk()->assertViewHas('incidents', function ($incidents) use ($citizen) {
            return $incidents->count() > 0 && $incidents->every(fn ($incident) => $incident->user_id === $citizen->id);
        });
        $this->post('/logout')->assertRedirect(route('landing'));
        $this->get('/incidents')->assertRedirect(route('auth.login'));
        $this->post('/login', ['email' => 'gestionnaire@aquasecure.tn'])->assertRedirect(route('manager.dashboard'));
        $this->get('/manager/incidents')->assertOk();
    }

    public function test_invalid_values_are_rejected_and_do_not_modify_records(): void
    {
        $owner = User::factory()->create();
        $incident = Incident::factory()->for($owner)->create();
        $this->withSession($this->citizen($owner));
        $this->post('/incidents', array_merge($this->incidentData(), ['type' => 'inconnu', 'gravite' => 'inconnue']))->assertSessionHasErrors(['type', 'gravite']);
        $this->put('/incidents/'.$incident->id, array_merge($this->incidentData(), ['statut' => 'inconnu']))->assertSessionHasErrors('statut');
        $this->assertDatabaseCount('incidents', 1);
        $this->withSession(['user' => ['name' => 'Gestionnaire', 'role' => 'manager', 'email' => 'gestionnaire@aquasecure.tn']]);
        $this->post('/manager/incidents/'.$incident->id.'/actions', ['titre' => 'Action', 'statut' => 'inconnu'])->assertSessionHasErrors('statut');
        $this->post('/manager/incidents/'.$incident->id.'/actions', ['titre' => 'Action', 'statut' => 'terminee'])->assertSessionHasErrors('date_realisation');
        $this->assertDatabaseCount('action_correctives', 0);
    }

    public function test_missing_citizen_account_does_not_fall_back_to_another_user(): void
    {
        User::factory()->create();
        $this->withSession(['user' => ['name' => 'Absent', 'role' => 'citizen', 'email' => 'absent@example.com']]);
        $this->post('/incidents', $this->incidentData())->assertForbidden();
        $this->assertDatabaseCount('incidents', 0);
    }

    public function test_action_list_detail_and_create_pages_are_available_to_manager_only(): void
    {
        $incident = Incident::factory()->create();
        $action = ActionCorrective::factory()->for($incident)->create(['titre' => 'Action liee a cet incident']);
        $otherAction = ActionCorrective::factory()->create(['titre' => 'Action autre incident']);
        $urls = [
            route('manager.actions.index', $incident),
            route('manager.actions.create', $incident),
            route('manager.actions.show', $action),
        ];
        foreach ($urls as $url) {
            $this->get($url)->assertRedirect(route('auth.login'));
        }
        $this->withSession($this->citizen($incident->user));
        foreach ($urls as $url) {
            $this->get($url)->assertForbidden();
        }
        $this->withSession(['user' => ['name' => 'Gestionnaire', 'role' => 'manager', 'email' => 'gestionnaire@aquasecure.tn']]);
        $this->get($urls[0])->assertOk()->assertSee($action->titre)->assertDontSee($otherAction->titre);
        $this->get($urls[1])->assertOk()->assertSee($incident->titre)
            ->assertDontSee('name="incident_id"', false)
            ->assertSee('name="description" rows="3" maxlength="5000"', false)
            ->assertSee('name="responsable" maxlength="255"', false)
            ->assertSee('name="date_prevue"', false)
            ->assertSee('name="date_realisation"', false)
            ->assertSee('name="resultat" rows="3" maxlength="5000"', false);
        $this->get($urls[2])->assertOk()->assertSee($action->titre)->assertSee($action->description)->assertSee($incident->titre);
        $this->get('/manager/actions/999999')->assertNotFound();
        $this->get('/manager/incidents/999999/actions')->assertNotFound();
    }

    public function test_incident_validation_checks_required_fields_types_lengths_dates_and_form_errors(): void
    {
        $owner = User::factory()->create();
        $this->withSession($this->citizen($owner));
        $this->post('/incidents', [])->assertSessionHasErrors(['titre', 'description', 'type', 'gravite', 'date_signalement', 'localisation']);
        $this->post('/incidents', array_merge($this->incidentData(), [
            'titre' => str_repeat('a', 256),
            'description' => ['incorrect'],
            'localisation' => str_repeat('a', 256),
            'date_signalement' => 'pas-une-date',
        ]))->assertSessionHasErrors(['titre', 'description', 'localisation', 'date_signalement']);
        $this->get('/incidents/create')->assertOk()->assertSee('Le champ titre ne doit pas depasser 255 caracteres.');
        $incident = Incident::factory()->for($owner)->create();
        $this->put('/incidents/'.$incident->id, [])->assertSessionHasErrors(['titre', 'description', 'type', 'gravite', 'statut', 'date_signalement', 'localisation']);
        $this->from(route('incidents.create'))->post('/incidents', array_merge($this->incidentData(), ['date_signalement' => 'invalide']))
            ->assertRedirect(route('incidents.create'))->assertSessionHasErrors('date_signalement')->assertSessionHasInput('titre', 'Fuite dans ma rue');
        $this->get('/incidents/create')->assertOk()->assertSee('Le champ date de signalement doit contenir une date valide.')->assertSee('value="Fuite dans ma rue"', false);
        $this->assertDatabaseCount('incidents', 1);
    }

    public function test_action_validation_checks_required_fields_types_lengths_dates_and_form_errors(): void
    {
        $incident = Incident::factory()->create();
        $action = ActionCorrective::factory()->for($incident)->create(['titre' => 'Action originale']);
        $this->withSession(['user' => ['name' => 'Gestionnaire', 'role' => 'manager', 'email' => 'gestionnaire@aquasecure.tn']]);
        $storeUrl = route('incidents.actions.store', $incident);
        $createUrl = route('manager.actions.create', $incident);
        $this->post($storeUrl, [])->assertSessionHasErrors(['titre', 'description', 'responsable', 'date_prevue', 'date_realisation', 'statut', 'resultat']);
        $this->put(route('manager.actions.update', $action), [])->assertSessionHasErrors(['titre', 'description', 'responsable', 'date_prevue', 'date_realisation', 'statut', 'resultat']);
        $invalidData = [
            'titre' => str_repeat('a', 256),
            'description' => ['incorrect'],
            'responsable' => str_repeat('a', 256),
            'date_prevue' => 'invalide',
            'date_realisation' => 'invalide',
            'statut' => 'invalide',
            'resultat' => ['incorrect'],
        ];
        $fields = array_keys($invalidData);
        $this->post($storeUrl, $invalidData)->assertSessionHasErrors($fields);
        $this->put(route('manager.actions.update', $action), $invalidData)->assertSessionHasErrors($fields);
        $this->get(route('manager.actions.edit', $action))->assertOk()->assertSee('Le champ responsable ne doit pas depasser 255 caracteres.');
        $this->assertSame('Action originale', $action->fresh()->titre);
        $this->from($createUrl)->post($storeUrl, ['titre' => 'Action conservee', 'statut' => 'terminee'])
            ->assertRedirect($createUrl)->assertSessionHasErrors(['description', 'responsable', 'date_prevue', 'date_realisation', 'resultat'])->assertSessionHasInput('titre', 'Action conservee');
        $this->get($createUrl)->assertOk()->assertSee('Le champ description est obligatoire.')->assertSee('value="Action conservee"', false);
        $this->post($storeUrl, [
            'titre' => 'Action complete',
            'description' => 'Action corrective requise.',
            'responsable' => 'Equipe reseau',
            'date_prevue' => '2026-10-07',
            'date_realisation' => '2026-10-07T12:00',
            'statut' => 'terminee',
            'resultat' => 'Intervention terminee.',
        ])
            ->assertRedirect(route('manager.incidents.show', $incident));
        $this->assertDatabaseHas('action_correctives', ['titre' => 'Action complete', 'responsable' => 'Equipe reseau']);
        $this->assertDatabaseCount('action_correctives', 2);
    }

    public function test_roles_are_enforced_on_incident_and_action_routes(): void
    {
        $owner = User::factory()->create();
        $incident = Incident::factory()->for($owner)->create();
        $action = ActionCorrective::factory()->for($incident)->create();
        $this->get('/incidents')->assertRedirect(route('auth.login'));
        $this->get('/manager/incidents')->assertRedirect(route('auth.login'));
        $this->withSession($this->citizen($owner));
        $this->get('/manager/incidents')->assertForbidden();
        $this->get('/manager/incidents/'.$incident->id)->assertForbidden();
        $this->post('/manager/incidents/'.$incident->id.'/actions', [])->assertForbidden();
        $this->get('/manager/actions/'.$action->id.'/edit')->assertForbidden();
        $this->put('/manager/actions/'.$action->id, [])->assertForbidden();
        $this->delete('/manager/actions/'.$action->id)->assertForbidden();
        $this->withSession(['user' => ['name' => 'Gestionnaire', 'role' => 'manager', 'email' => 'gestionnaire@aquasecure.tn']]);
        $this->get('/incidents/create')->assertForbidden();
        $this->post('/incidents', $this->incidentData())->assertForbidden();
        $this->put('/incidents/'.$incident->id, $this->incidentData())->assertForbidden();
        $this->delete('/incidents/'.$incident->id)->assertForbidden();
    }
}