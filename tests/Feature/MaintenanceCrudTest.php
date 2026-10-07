<?php

namespace Tests\Feature;

use App\Models\Intervention;
use App\Models\Technicien;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MaintenanceCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->withSession([
            'user' => [
                'name' => 'Gestionnaire',
                'email' => 'gestionnaire@aquasecure.tn',
                'role' => 'manager',
            ],
        ]);
    }

    public function test_manager_can_create_update_and_delete_a_technicien(): void
    {
        $this->get(route('manager.techniciens.index'))->assertOk();
        $this->get(route('manager.techniciens.create'))->assertOk();

        $this->post(route('manager.techniciens.store'), [
            'nom' => 'Amira Ben Ali',
            'specialite' => 'Canalisations',
            'telephone' => '+216 22 123 456',
            'disponibilite' => 'Disponible',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $technicien = Technicien::where('nom', 'Amira Ben Ali')->firstOrFail();
        $this->get(route('manager.techniciens.show', $technicien))->assertOk()->assertSee('Amira Ben Ali');
        $this->get(route('manager.techniciens.edit', $technicien))->assertOk();

        $this->put(route('manager.techniciens.update', $technicien), [
            'nom' => 'Amira Ben Ali',
            'specialite' => 'Pompage',
            'telephone' => '+216 22 123 456',
            'disponibilite' => 'En congé',
        ])->assertRedirect(route('manager.techniciens.show', $technicien));

        $this->assertSame('Pompage', $technicien->fresh()->specialite);

        $this->delete(route('manager.techniciens.destroy', $technicien))
            ->assertRedirect(route('manager.techniciens.index'));

        $this->assertModelMissing($technicien);
    }

    public function test_technicien_validation_rejects_invalid_data(): void
    {
        $this->post(route('manager.techniciens.store'), [
            'nom' => '',
            'specialite' => 'Inconnue',
            'telephone' => 'abc',
            'disponibilite' => 'Peut-être',
        ])->assertSessionHasErrors(['nom', 'specialite', 'telephone', 'disponibilite']);

        $this->assertDatabaseCount('techniciens', 0);
    }

    public function test_manager_can_create_update_and_delete_an_intervention(): void
    {
        $technicien = Technicien::factory()->create();

        $this->get(route('manager.interventions.index'))->assertOk();
        $this->get(route('manager.interventions.create', ['technicien_id' => $technicien->id]))->assertOk();

        $this->post(route('manager.interventions.store'), [
            'technicien_id' => $technicien->id,
            'date' => now()->subDay()->toDateString(),
            'description' => 'Réparation d\'une fuite rue Habib Bourguiba',
            'statut' => 'Terminée',
            'cout' => '1250.50',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $intervention = Intervention::firstOrFail();
        $this->assertTrue($intervention->technicien->is($technicien));
        $this->get(route('manager.interventions.show', $intervention))->assertOk()->assertSee($technicien->nom);
        $this->get(route('manager.interventions.edit', $intervention))->assertOk();
        $this->get(route('manager.techniciens.show', $technicien))->assertSee('Réparation d&#039;une fuite', false);

        $this->put(route('manager.interventions.update', $intervention), [
            'technicien_id' => $technicien->id,
            'date' => now()->addWeek()->toDateString(),
            'description' => 'Réparation reportée',
            'statut' => 'Planifiée',
            'cout' => '0',
        ])->assertRedirect(route('manager.interventions.show', $intervention));

        $this->assertSame('Planifiée', $intervention->fresh()->statut);

        $this->delete(route('manager.interventions.destroy', $intervention))
            ->assertRedirect(route('manager.interventions.index'));

        $this->assertModelMissing($intervention);
    }

    public function test_intervention_validation_rules(): void
    {
        $this->post(route('manager.interventions.store'), [
            'technicien_id' => 999,
            'date' => 'pas-une-date',
            'description' => 'abc',
            'statut' => 'Inconnu',
            'cout' => -5,
        ])->assertSessionHasErrors(['technicien_id', 'date', 'description', 'statut', 'cout']);

        // Une intervention terminée ne peut pas être datée dans le futur
        $technicien = Technicien::factory()->create();
        $this->post(route('manager.interventions.store'), [
            'technicien_id' => $technicien->id,
            'date' => now()->addMonth()->toDateString(),
            'description' => 'Contrôle des capteurs',
            'statut' => 'Terminée',
            'cout' => 100,
        ])->assertSessionHasErrors('date');

        $this->assertDatabaseCount('interventions', 0);
    }

    public function test_deleting_a_technicien_deletes_its_interventions(): void
    {
        $technicien = Technicien::factory()->has(Intervention::factory()->count(3))->create();

        $this->delete(route('manager.techniciens.destroy', $technicien));

        $this->assertDatabaseCount('interventions', 0);
    }

    public function test_citizen_sees_public_maintenance_works_without_personal_data(): void
    {
        $technicien = Technicien::factory()->create(['specialite' => 'Canalisations', 'telephone' => '+216 99 888 777']);
        $enCours = Intervention::factory()->for($technicien)->create(['statut' => 'En cours', 'description' => 'Réparation rue de Marseille']);
        Intervention::factory()->for($technicien)->create(['statut' => 'Annulée', 'description' => 'Travaux annulés']);

        $this->get(route('citizen.travaux.index'))
            ->assertOk()
            ->assertSee('Réparation rue de Marseille')
            ->assertDontSee('Travaux annulés')
            ->assertDontSee('+216 99 888 777');

        $this->get(route('citizen.travaux.show', $enCours))
            ->assertOk()
            ->assertSee('Canalisations')
            ->assertDontSee('+216 99 888 777');
    }

    public function test_cancelled_intervention_is_hidden_from_citizens(): void
    {
        $annulee = Intervention::factory()->create(['statut' => 'Annulée']);

        $this->get(route('citizen.travaux.show', $annulee))->assertNotFound();
    }

    public function test_interventions_can_be_filtered_by_technicien(): void
    {
        $amira = Technicien::factory()->create(['nom' => 'Amira Ben Ali']);
        $karim = Technicien::factory()->create(['nom' => 'Karim Trabelsi']);
        Intervention::factory()->for($amira)->create(['description' => 'Intervention de Amira']);
        Intervention::factory()->for($karim)->create(['description' => 'Intervention de Karim']);

        $this->get(route('manager.interventions.index', ['technicien_id' => $amira->id]))
            ->assertOk()
            ->assertSee('Intervention de Amira')
            ->assertDontSee('Intervention de Karim');
    }
}
