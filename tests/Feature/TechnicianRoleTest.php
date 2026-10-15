<?php

namespace Tests\Feature;

use App\Enums\InfrastructureEtat;
use App\Enums\InfrastructureType;
use App\Models\Incident;
use App\Models\Infrastructure;
use App\Models\InfrastructureAlerte;
use App\Models\InfrastructureHistorique;
use App\Models\Intervention;
use App\Models\Technicien;
use App\Models\Zone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TechnicianRoleTest extends TestCase
{
    use RefreshDatabase;

    protected Technicien $technicien;
    protected Zone $zone;
    protected Infrastructure $infraAssignee;
    protected Infrastructure $infraNonAssignee;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();

        $this->zone = Zone::create([
            'nom' => 'Zone Grand Tunis',
            'localisation' => 'Tunis Centre',
            'latitude' => 36.8065,
            'longitude' => 10.1815,
        ]);

        $this->technicien = Technicien::create([
            'nom' => 'Amira Ben Ali',
            'specialite' => 'Canalisations',
            'telephone' => '+216 22 123 456',
            'disponibilite' => 'Disponible',
        ]);

        $this->infraAssignee = Infrastructure::create([
            'zone_id' => $this->zone->id,
            'nom' => 'Station Pompage El Menzah',
            'type' => InfrastructureType::StationPompage,
            'etat' => InfrastructureEtat::EnService,
            'reference' => 'INF-TUN-001',
            'localisation' => 'El Menzah 6',
            'date_installation' => '2020-01-15',
            'latitude' => 36.8300,
            'longitude' => 10.1800,
            'capacite' => 1500,
            'unite_capacite' => 'm3/h',
        ]);

        $this->infraNonAssignee = Infrastructure::create([
            'zone_id' => $this->zone->id,
            'nom' => 'Réservoir Sidi Bou Said',
            'type' => InfrastructureType::Reservoir,
            'etat' => InfrastructureEtat::EnService,
            'reference' => 'INF-TUN-002',
            'localisation' => 'Sidi Bou Said',
            'date_installation' => '2019-06-20',
            'latitude' => 36.8700,
            'longitude' => 10.3400,
        ]);

        // Intervention pour Amira sur l'infrastructure assignée
        Intervention::create([
            'technicien_id' => $this->technicien->id,
            'infrastructure_id' => $this->infraAssignee->id,
            'date' => now()->toDateString(),
            'description' => 'Inspection trimestrielle des pompes de relevage',
            'statut' => 'Planifiée',
            'cout' => 1450.75,
        ]);

        // Intervention pour un autre technicien sur l'infrastructure non assignée
        $autreTechnicien = Technicien::create([
            'nom' => 'Karim Trabelsi',
            'specialite' => 'Électricité',
            'telephone' => '+216 22 789 012',
            'disponibilite' => 'Disponible',
        ]);

        Intervention::create([
            'technicien_id' => $autreTechnicien->id,
            'infrastructure_id' => $this->infraNonAssignee->id,
            'date' => now()->toDateString(),
            'description' => 'Vérification armoire électrique',
            'statut' => 'Planifiée',
            'cout' => 890.00,
        ]);
    }

    protected function loginAsTechnician(): void
    {
        $this->withSession([
            'user' => [
                'name' => 'Amira Ben Ali',
                'email' => 'amira@aquasecure.tn',
                'role' => 'technician',
                'technicien_id' => $this->technicien->id,
            ],
        ]);
    }

    public function test_non_technician_cannot_access_technician_space(): void
    {
        // 1. Invité non authentifié -> redirection login
        $this->get(route('technician.dashboard'))->assertRedirect(route('auth.login'));
        $this->get(route('technician.infrastructures.index'))->assertRedirect(route('auth.login'));

        // 2. Citoyen -> 403 Forbidden
        $this->withSession(['user' => ['name' => 'Citoyen', 'role' => 'citizen']]);
        $this->get(route('technician.dashboard'))->assertForbidden();
        $this->get(route('technician.infrastructures.index'))->assertForbidden();
        $this->get(route('technician.tournee'))->assertForbidden();
    }

    public function test_technician_can_only_see_assigned_infrastructures(): void
    {
        $this->loginAsTechnician();

        // 1. Liste des infrastructures : voit la sienne, ne voit PAS celle de Karim
        $response = $this->get(route('technician.infrastructures.index'));
        $response->assertOk()
            ->assertSee('Station Pompage El Menzah')
            ->assertDontSee('Réservoir Sidi Bou Said');

        // 2. Consultation autorisée sur son infrastructure
        $this->get(route('technician.infrastructures.show', $this->infraAssignee))
            ->assertOk()
            ->assertSee('Station Pompage El Menzah')
            ->assertSee('INF-TUN-001');

        // 3. Consultation INTERDITE (403) sur une infrastructure hors de son périmètre
        $this->get(route('technician.infrastructures.show', $this->infraNonAssignee))
            ->assertForbidden();
    }

    public function test_technician_views_never_expose_costs_or_financial_data(): void
    {
        $this->loginAsTechnician();

        // Sur la fiche technique de l'infrastructure
        $infraResponse = $this->get(route('technician.infrastructures.show', $this->infraAssignee));
        $infraResponse->assertOk()
            ->assertDontSee('1450.75')
            ->assertDontSee('1 450,75')
            ->assertDontSee('Coût total')
            ->assertDontSee('Budget');

        // Sur la fiche de l'intervention
        $intervention = Intervention::where('technicien_id', $this->technicien->id)->firstOrFail();
        $intResponse = $this->get(route('technician.interventions.show', $intervention));
        $intResponse->assertOk()
            ->assertDontSee('1450.75')
            ->assertDontSee('1 450,75')
            ->assertDontSee('Coût :');
    }

    public function test_technician_can_change_state_only_with_open_intervention(): void
    {
        $this->loginAsTechnician();

        // L'intervention sur infraAssignee est 'Planifiée' (OUVERTE)
        // 1. Changement d'état réussi avec commentaire
        $response = $this->patch(route('technician.infrastructures.etat', $this->infraAssignee), [
            'etat' => InfrastructureEtat::EnMaintenance->value,
            'commentaire' => 'Arrêt préventif pour remplacement des paliers de pompe',
        ]);

        $response->assertRedirect(route('technician.infrastructures.show', $this->infraAssignee));
        $this->assertSame(InfrastructureEtat::EnMaintenance, $this->infraAssignee->fresh()->etat);

        // Vérification de la traçabilité dans l'historique
        $historique = InfrastructureHistorique::where('infrastructure_id', $this->infraAssignee->id)->latest('id')->first();
        $this->assertNotNull($historique);
        $this->assertStringContainsString('Arrêt préventif pour remplacement des paliers', $historique->commentaire);
        $this->assertSame('Amira Ben Ali', $historique->user_name);

        // 2. Validation : rejet si commentaire absent ou trop court
        $this->patch(route('technician.infrastructures.etat', $this->infraAssignee), [
            'etat' => InfrastructureEtat::EnService->value,
            'commentaire' => 'test', // < 5 caractères
        ])->assertSessionHasErrors('commentaire');

        // 3. Si l'intervention est clôturée (Terminée), le changement d'état est REFUSÉ (403)
        $intervention = Intervention::where('technicien_id', $this->technicien->id)->firstOrFail();
        $intervention->update(['statut' => 'Terminée']);

        $this->patch(route('technician.infrastructures.etat', $this->infraAssignee), [
            'etat' => InfrastructureEtat::EnService->value,
            'commentaire' => 'Tentative de remise en service sans intervention ouverte',
        ])->assertForbidden();
    }

    public function test_technician_can_submit_intervention_report_which_updates_infrastructure_and_resolves_alerts(): void
    {
        $this->loginAsTechnician();

        $this->infraAssignee->update([
            'etat' => InfrastructureEtat::EnMaintenance,
            'date_derniere_maintenance' => now()->subMonths(6)->toDateString(),
        ]);

        // Créer une alerte active liée à l'infrastructure
        $alerte = InfrastructureAlerte::create([
            'infrastructure_id' => $this->infraAssignee->id,
            'type' => 'panne',
            'message' => 'Vibration anormale sur moteur 2',
            'resolue' => false,
        ]);

        $intervention = Intervention::where('technicien_id', $this->technicien->id)->firstOrFail();
        $intervention->update(['statut' => 'En cours']);

        // Soumission du rapport de terrain
        $response = $this->post(route('technician.interventions.report.store', $intervention), [
            'status' => 'resolved',
            'work_performed' => 'Alignement laser du groupe motopompe et remplacement des silentblocs',
            'cause' => 'Desserrement des fixations châssis suite aux vibrations',
            'observations' => 'Contrôler le serrage au dynamomètre dans 3 mois',
            'nouvel_etat' => InfrastructureEtat::EnService->value,
        ]);

        $response->assertRedirect(route('technician.interventions.show', $intervention));

        // 1. L'intervention est passée à 'Terminée'
        $intervention->refresh();
        $this->assertSame('Terminée', $intervention->statut);
        $this->assertStringContainsString('Alignement laser du groupe motopompe', $intervention->description);

        // 2. L'infrastructure est remise en service et la date de dernière maintenance mise à jour
        $this->infraAssignee->refresh();
        $this->assertSame(InfrastructureEtat::EnService, $this->infraAssignee->etat);
        $this->assertSame(now()->toDateString(), $this->infraAssignee->date_derniere_maintenance?->toDateString());

        // 3. L'alerte liée est clôturée (resolue = true)
        $this->assertTrue($alerte->fresh()->resolue);

        // 4. L'historique d'état a consigné la clôture
        $hist = InfrastructureHistorique::where('infrastructure_id', $this->infraAssignee->id)->latest('id')->first();
        $this->assertNotNull($hist);
        $this->assertStringContainsString('Clôture Intervention #' . $intervention->id, $hist->commentaire);
    }

    public function test_technician_can_report_problem_creating_linked_incident(): void
    {
        $this->loginAsTechnician();

        $this->get(route('technician.incidents.create', ['infrastructure_id' => $this->infraAssignee->id]))
            ->assertOk()
            ->assertSee('Station Pompage El Menzah');

        $response = $this->post(route('technician.incidents.store'), [
            'titre' => 'Fuite constatée sur conduite refoulement',
            'type' => 'fuite',
            'gravite' => 'elevee',
            'description' => 'Gros suintement sous pression au niveau du joint de dilatation',
            'localisation' => 'Station Pompage El Menzah - Sas 2',
            'infrastructure_id' => $this->infraAssignee->id,
            'zone_id' => $this->zone->id,
        ]);

        $response->assertRedirect(route('technician.infrastructures.show', $this->infraAssignee));

        $incident = Incident::where('infrastructure_id', $this->infraAssignee->id)->firstOrFail();
        $this->assertSame('Fuite constatée sur conduite refoulement', $incident->titre);
        $this->assertSame('fuite', $incident->type);
        $this->assertSame('elevee', $incident->gravite);
        $this->assertSame('signale', $incident->statut);
        $this->assertStringContainsString('Amira Ben Ali', $incident->description);
    }

    public function test_technician_scan_and_tournee_pages_render(): void
    {
        $this->loginAsTechnician();

        // 1. Page de tournée du jour
        $this->get(route('technician.tournee'))
            ->assertOk()
            ->assertSee('Tournée de Maintenance')
            ->assertSee('Station Pompage El Menzah');

        // 2. Page de scan QR Code
        $this->get(route('technician.scan'))
            ->assertOk()
            ->assertSee('Scanner');

        // 3. Traitement de scan avec code valide
        $this->post(route('technician.scan.process'), ['code' => 'INF-TUN-001'])
            ->assertRedirect(route('technician.infrastructures.show', $this->infraAssignee));

        // 4. Traitement de scan avec code hors périmètre
        $this->post(route('technician.scan.process'), ['code' => 'INF-TUN-002'])
            ->assertRedirect()
            ->assertSessionHas('error');
    }

    public function test_technician_can_view_zones_and_zone_details(): void
    {
        $this->loginAsTechnician();

        // 1. Liste des zones d'intervention
        $response = $this->get(route('technician.zones.index'))
            ->assertOk()
            ->assertSee('Zones')
            ->assertSee($this->zone->nom);

        // 2. Fiche zone terrain
        $responseShow = $this->get(route('technician.zones.show', $this->zone))
            ->assertOk()
            ->assertSee($this->zone->nom)
            ->assertSee($this->infraAssignee->nom);

        // Aucun coût financier affiché
        $responseShow->assertDontSee('50 000');
        $responseShow->assertDontSee('cout_achat');
    }
}
