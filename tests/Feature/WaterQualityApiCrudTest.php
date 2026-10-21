<?php

namespace Tests\Feature;

use App\Models\MesureQualite;
use App\Models\PointMesure;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithRbac;
use Tests\TestCase;

/**
 * Tests complémentaires de l'API JWT des mesures de qualité :
 * filtres de liste, visibilité citoyenne, création, modification,
 * validation et suppression selon les permissions.
 */
class WaterQualityApiCrudTest extends TestCase
{
    use RefreshDatabase;
    use InteractsWithRbac;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRbac();
    }

    // -------------------------------------------------------------------------
    // Outils
    // -------------------------------------------------------------------------

    private function bearer(User $user): array
    {
        return ['Authorization' => 'Bearer ' . auth('api')->login($user)];
    }

    private function point(string $code, string $nom = 'Point API', string $statut = 'actif', string $zone = 'Zone Nord'): PointMesure
    {
        return PointMesure::create([
            'code' => $code,
            'nom' => $nom,
            'zone' => $zone,
            'type' => 'Station',
            'statut' => $statut,
        ]);
    }

    private function mesure(PointMesure $point, string $reference, array $overrides = []): MesureQualite
    {
        return MesureQualite::create(array_merge([
            'reference' => $reference,
            'point_mesure_id' => $point->id,
            'date_mesure' => now()->subDay(),
            'ph' => 7.2,
            'turbidite' => 0.5,
            'chlore_residuel' => 0.8,
            'plomb' => 2,
            'nitrates' => 10,
            'source_donnee' => 'manuel',
            'status' => 'Compliant',
            'overall_compliance' => 100,
            'is_verified' => false,
        ], $overrides));
    }

    private function payload(int $pointId, string $reference, array $overrides = []): array
    {
        return array_merge([
            'reference' => $reference,
            'point_mesure_id' => $pointId,
            'date_mesure' => '2026-10-15 08:00:00',
            'ph' => 7.2,
            'turbidite' => 0.5,
            'chlore_residuel' => 0.8,
            'plomb' => 2.0,
            'nitrates' => 10.0,
        ], $overrides);
    }

    /** Utilisateur actif sans aucun rôle : authentifié mais sans permission. */
    private function userWithoutRole(): User
    {
        return User::factory()->create(['is_active' => true]);
    }

    // -------------------------------------------------------------------------
    // GET /api/points-mesure
    // -------------------------------------------------------------------------

    public function test_points_list_returns_active_points_sorted_and_searchable(): void
    {
        $tech = $this->createUserWithRole(Role::TECHNICIEN);
        $this->point('PT-B', 'Bravo');
        $this->point('PT-A', 'Alpha', 'actif', 'Zone Sud');
        $this->point('PT-OFF', 'Hors service', 'inactif');

        $headers = $this->bearer($tech);

        $this->withHeaders($headers)->getJson('/api/points-mesure')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.nom', 'Alpha')
            ->assertJsonPath('data.1.nom', 'Bravo');

        $this->withHeaders($headers)->getJson('/api/points-mesure?search=Sud')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.code', 'PT-A');

        $this->withHeaders($headers)->getJson('/api/points-mesure?statut=inactif')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.code', 'PT-OFF');
    }

    public function test_points_list_requires_the_view_permission(): void
    {
        $this->withHeaders($this->bearer($this->userWithoutRole()))
            ->getJson('/api/points-mesure')
            ->assertForbidden()
            ->assertJsonPath('message', 'Accès refusé : permission measurements.view requise.');
    }

    // -------------------------------------------------------------------------
    // GET /api/mesures
    // -------------------------------------------------------------------------

    public function test_citizen_only_sees_verified_measurements(): void
    {
        $point = $this->point('PT-CIT');
        $this->mesure($point, 'MES-PUBLIQUE', ['is_verified' => true]);
        $this->mesure($point, 'MES-BROUILLON');

        $citizen = $this->createUserWithRole(Role::CITOYEN);
        $manager = $this->createUserWithRole(Role::MANAGER);

        $this->withHeaders($this->bearer($citizen))->getJson('/api/mesures')
            ->assertOk()
            ->assertJsonPath('data.total', 1)
            ->assertJsonPath('data.data.0.reference', 'MES-PUBLIQUE');

        $this->withHeaders($this->bearer($manager))->getJson('/api/mesures')
            ->assertOk()
            ->assertJsonPath('data.total', 2);
    }

    public function test_measurement_list_filters_are_combined(): void
    {
        $nord = $this->point('PT-NORD', 'Forage Nord');
        $sud = $this->point('PT-SUD', 'Puits Sud');
        $this->mesure($nord, 'MES-001', ['status' => 'Compliant', 'is_verified' => true]);
        $this->mesure($nord, 'MES-002', ['status' => 'Alert', 'source_donnee' => 'laboratoire']);
        $this->mesure($sud, 'MES-003', ['status' => 'Non-Compliant']);

        $headers = $this->bearer($this->createUserWithRole(Role::TECHNICIEN));

        // Recherche par nom de point
        $this->withHeaders($headers)->getJson('/api/mesures?search=Puits')
            ->assertOk()->assertJsonPath('data.total', 1)
            ->assertJsonPath('data.data.0.reference', 'MES-003');

        // Recherche par référence
        $this->withHeaders($headers)->getJson('/api/mesures?search=MES-002')
            ->assertOk()->assertJsonPath('data.total', 1);

        $this->withHeaders($headers)->getJson('/api/mesures?point_mesure_id=' . $nord->id)
            ->assertOk()->assertJsonPath('data.total', 2);

        $this->withHeaders($headers)->getJson('/api/mesures?status=Alert')
            ->assertOk()->assertJsonPath('data.total', 1)
            ->assertJsonPath('data.data.0.reference', 'MES-002');

        // Un statut inconnu est ignoré au lieu de vider la liste.
        $this->withHeaders($headers)->getJson('/api/mesures?status=Inconnu')
            ->assertOk()->assertJsonPath('data.total', 3);

        $this->withHeaders($headers)->getJson('/api/mesures?source_donnee=laboratoire')
            ->assertOk()->assertJsonPath('data.total', 1);

        $this->withHeaders($headers)->getJson('/api/mesures?is_verified=1')
            ->assertOk()->assertJsonPath('data.total', 1)
            ->assertJsonPath('data.data.0.reference', 'MES-001');

        $this->withHeaders($headers)->getJson('/api/mesures?is_verified=0&per_page=1')
            ->assertOk()
            ->assertJsonPath('data.total', 2)
            ->assertJsonPath('data.per_page', 1)
            ->assertJsonCount(1, 'data.data');
    }

    public function test_measurement_list_requires_the_view_permission(): void
    {
        $this->withHeaders($this->bearer($this->userWithoutRole()))
            ->getJson('/api/mesures')
            ->assertForbidden();
    }

    // -------------------------------------------------------------------------
    // GET /api/mesures/{mesure}
    // -------------------------------------------------------------------------

    public function test_show_hides_unverified_measurements_from_citizens(): void
    {
        $point = $this->point('PT-SHOW');
        $public = $this->mesure($point, 'MES-PUB', ['is_verified' => true]);
        $draft = $this->mesure($point, 'MES-DRAFT');

        $citizen = $this->bearer($this->createUserWithRole(Role::CITOYEN));

        $this->withHeaders($citizen)->getJson("/api/mesures/{$public->id}")
            ->assertOk()
            ->assertJsonPath('data.reference', 'MES-PUB')
            ->assertJsonPath('data.point_mesure.code', 'PT-SHOW');

        $this->withHeaders($citizen)->getJson("/api/mesures/{$draft->id}")
            ->assertForbidden()
            ->assertJsonPath('message', 'Cette mesure n\'est pas encore validée et n\'est pas accessible au public.');

        $this->withHeaders($this->bearer($this->createUserWithRole(Role::TECHNICIEN)))
            ->getJson("/api/mesures/{$draft->id}")
            ->assertOk()
            ->assertJsonPath('data.reference', 'MES-DRAFT');
    }

    public function test_show_requires_the_view_permission(): void
    {
        $mesure = $this->mesure($this->point('PT-SHOW-2'), 'MES-X');

        $this->withHeaders($this->bearer($this->userWithoutRole()))
            ->getJson("/api/mesures/{$mesure->id}")
            ->assertForbidden();
    }

    // -------------------------------------------------------------------------
    // POST /api/mesures
    // -------------------------------------------------------------------------

    public function test_store_validates_the_payload(): void
    {
        $point = $this->point('PT-VAL');
        $this->mesure($point, 'MES-EXISTANTE');

        $this->withHeaders($this->bearer($this->createUserWithRole(Role::TECHNICIEN)))
            ->postJson('/api/mesures', $this->payload(999999, 'MES-EXISTANTE', [
                'ph' => 15,
                'turbidite' => -1,
                'source_donnee' => 'inconnue',
            ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['reference', 'point_mesure_id', 'ph', 'turbidite', 'source_donnee']);

        $this->assertSame(1, MesureQualite::count());
    }

    public function test_technician_cannot_self_validate_a_new_measurement(): void
    {
        $point = $this->point('PT-TECH');
        $tech = $this->createUserWithRole(Role::TECHNICIEN);

        $this->withHeaders($this->bearer($tech))
            ->postJson('/api/mesures', $this->payload($point->id, 'MES-TECH', [
                'is_verified' => true,
                'verifier' => 'Moi-même',
                'ph' => 9.5,
            ]))
            ->assertCreated()
            ->assertJsonPath('data.is_verified', false)
            ->assertJsonPath('data.verifier', null)
            ->assertJsonPath('assessment.status', 'Non-Compliant')
            ->assertJsonPath('message', 'La mesure a été enregistrée avec succès.');

        $this->assertDatabaseHas('mesures_qualites', [
            'reference' => 'MES-TECH',
            'is_verified' => false,
            'source_donnee' => 'manuel',
            'status' => 'Non-Compliant',
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'measurement_created',
            'user_id' => $tech->id,
        ]);
    }

    public function test_administrator_can_create_an_already_validated_measurement(): void
    {
        $point = $this->point('PT-ADM');

        $this->withHeaders($this->bearer($this->createUserWithRole(Role::ADMINISTRATEUR)))
            ->postJson('/api/mesures', $this->payload($point->id, 'MES-ADM', [
                'is_verified' => true,
                'verifier' => 'Laboratoire central',
                'source_donnee' => 'laboratoire',
            ]))
            ->assertCreated()
            ->assertJsonPath('data.is_verified', true)
            ->assertJsonPath('data.verifier', 'Laboratoire central')
            ->assertJsonPath('assessment.status', 'Compliant');

        $this->assertDatabaseHas('mesures_qualites', ['reference' => 'MES-ADM', 'source_donnee' => 'laboratoire', 'is_verified' => true]);
    }

    public function test_manager_role_without_create_permission_cannot_store(): void
    {
        $point = $this->point('PT-MGR');

        // Le gestionnaire passe le filtre de rôle mais n'a pas la permission measurements.create.
        $this->withHeaders($this->bearer($this->createUserWithRole(Role::MANAGER)))
            ->postJson('/api/mesures', $this->payload($point->id, 'MES-MGR'))
            ->assertForbidden()
            ->assertJsonPath('required_permission', 'measurements.create');

        $this->assertDatabaseMissing('mesures_qualites', ['reference' => 'MES-MGR']);
    }

    // -------------------------------------------------------------------------
    // PUT /api/mesures/{mesure}
    // -------------------------------------------------------------------------

    public function test_technician_updates_an_unverified_measurement_and_status_is_recomputed(): void
    {
        $mesure = $this->mesure($this->point('PT-UPD'), 'MES-UPD');
        $tech = $this->createUserWithRole(Role::TECHNICIEN);

        $this->withHeaders($this->bearer($tech))
            ->putJson("/api/mesures/{$mesure->id}", ['turbidite' => 1.5])
            ->assertOk()
            ->assertJsonPath('message', 'La mesure a été mise à jour.')
            ->assertJsonPath('assessment.status', 'Alert');

        $mesure->refresh();
        $this->assertEquals(1.5, (float) $mesure->turbidite);
        $this->assertSame('Alert', $mesure->status);
        $this->assertDatabaseHas('audit_logs', ['action' => 'measurement_updated', 'resource_id' => $mesure->id]);
    }

    public function test_technician_cannot_update_a_validated_measurement_but_manager_can(): void
    {
        $mesure = $this->mesure($this->point('PT-LOCK'), 'MES-LOCK', ['is_verified' => true]);

        $this->withHeaders($this->bearer($this->createUserWithRole(Role::TECHNICIEN)))
            ->putJson("/api/mesures/{$mesure->id}", ['ph' => 6.0])
            ->assertForbidden()
            ->assertJsonPath('message', 'Impossible de modifier une mesure déjà validée.');

        $this->withHeaders($this->bearer($this->createUserWithRole(Role::MANAGER)))
            ->putJson("/api/mesures/{$mesure->id}", ['ph' => 6.0])
            ->assertOk()
            ->assertJsonPath('assessment.status', 'Non-Compliant');

        $this->assertEquals(6.0, (float) $mesure->refresh()->ph);
    }

    public function test_update_rejects_invalid_values(): void
    {
        $mesure = $this->mesure($this->point('PT-UPD-2'), 'MES-UPD-2');

        $this->withHeaders($this->bearer($this->createUserWithRole(Role::MANAGER)))
            ->putJson("/api/mesures/{$mesure->id}", ['ph' => 'acide', 'plomb' => -3, 'point_mesure_id' => 999999])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['ph', 'plomb', 'point_mesure_id']);

        $this->assertEquals(7.2, (float) $mesure->refresh()->ph);
    }

    public function test_update_requires_the_update_permission(): void
    {
        $mesure = $this->mesure($this->point('PT-UPD-3'), 'MES-UPD-3');
        $manager = $this->createUserWithRole(Role::MANAGER);
        Role::where('slug', Role::MANAGER)->firstOrFail()->revokePermission('measurements.update');

        $this->withHeaders($this->bearer($manager))
            ->putJson("/api/mesures/{$mesure->id}", ['ph' => 6.9])
            ->assertForbidden()
            ->assertJsonPath('message', 'Accès refusé : permission measurements.update requise.');
    }

    // -------------------------------------------------------------------------
    // POST /api/mesures/{mesure}/validate
    // -------------------------------------------------------------------------

    public function test_manager_validates_a_measurement(): void
    {
        $mesure = $this->mesure($this->point('PT-VALID'), 'MES-VALID');
        $manager = $this->createUserWithRole(Role::MANAGER, ['name' => 'Ines Gestion']);

        $this->withHeaders($this->bearer($manager))
            ->postJson("/api/mesures/{$mesure->id}/validate")
            ->assertOk()
            ->assertJsonPath('message', 'La mesure MES-VALID a été validée avec succès.')
            ->assertJsonPath('data.is_verified', true);

        $mesure->refresh();
        $this->assertTrue($mesure->is_verified);
        $this->assertSame('Ines Gestion (manager)', $mesure->verifier);
        $this->assertDatabaseHas('audit_logs', ['action' => 'measurement_validated', 'resource_id' => $mesure->id]);
    }

    public function test_technician_cannot_validate_and_permission_is_checked_for_managers(): void
    {
        $mesure = $this->mesure($this->point('PT-VALID-2'), 'MES-VALID-2');

        $this->withHeaders($this->bearer($this->createUserWithRole(Role::TECHNICIEN)))
            ->postJson("/api/mesures/{$mesure->id}/validate")
            ->assertForbidden();

        $manager = $this->createUserWithRole(Role::MANAGER);
        Role::where('slug', Role::MANAGER)->firstOrFail()->revokePermission('measurements.validate');

        $this->withHeaders($this->bearer($manager))
            ->postJson("/api/mesures/{$mesure->id}/validate")
            ->assertForbidden()
            ->assertJsonPath('required_permission', 'measurements.validate');

        $this->assertFalse($mesure->refresh()->is_verified);
    }

    // -------------------------------------------------------------------------
    // DELETE /api/mesures/{mesure}
    // -------------------------------------------------------------------------

    public function test_manager_deletes_a_measurement_and_it_is_audited(): void
    {
        $mesure = $this->mesure($this->point('PT-DEL'), 'MES-DEL');

        $this->withHeaders($this->bearer($this->createUserWithRole(Role::MANAGER)))
            ->deleteJson("/api/mesures/{$mesure->id}")
            ->assertOk()
            ->assertJsonPath('message', 'La mesure MES-DEL a été supprimée.');

        $this->assertDatabaseMissing('mesures_qualites', ['id' => $mesure->id]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'measurement_deleted', 'resource_id' => null]);
    }

    public function test_delete_is_refused_to_technicians_and_without_permission(): void
    {
        $mesure = $this->mesure($this->point('PT-DEL-2'), 'MES-DEL-2');

        $this->withHeaders($this->bearer($this->createUserWithRole(Role::TECHNICIEN)))
            ->deleteJson("/api/mesures/{$mesure->id}")
            ->assertForbidden();

        $manager = $this->createUserWithRole(Role::MANAGER);
        Role::where('slug', Role::MANAGER)->firstOrFail()->revokePermission('measurements.delete');

        $this->withHeaders($this->bearer($manager))
            ->deleteJson("/api/mesures/{$mesure->id}")
            ->assertForbidden()
            ->assertJsonPath('message', 'Accès refusé : permission measurements.delete requise.');

        $this->assertDatabaseHas('mesures_qualites', ['id' => $mesure->id]);
    }
}
