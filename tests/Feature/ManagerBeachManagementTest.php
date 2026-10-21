<?php

namespace Tests\Feature;

use App\Http\Controllers\Manager\BeachManagementController;
use App\Models\Beach;
use App\Models\BeachBathingStatus;
use App\Models\BeachHistoricalAdvisoryPoint;
use App\Models\Governorate;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\Concerns\InteractsWithRbac;
use Tests\TestCase;

/**
 * Tests de l'espace gestionnaire « Plages » : relevé partagé des points
 * signalés, fiche d'une plage et décisions de baignade.
 *
 * Les routes manager.beaches.* utilisées par les vues ne sont déclarées nulle
 * part dans routes/web.php (voir le test marqué « Bug ») : elles sont
 * déclarées ici avec les noms attendus par les vues pour tester le contrôleur.
 */
class ManagerBeachManagementTest extends TestCase
{
    use RefreshDatabase;
    use InteractsWithRbac;

    private User $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->seedRbac();
        $this->manager = $this->createUserWithRole(Role::MANAGER);
    }

    // -------------------------------------------------------------------------
    // Outils
    // -------------------------------------------------------------------------


    private function asManager(): static
    {
        return $this->actingAs($this->manager)->withSession([
            'user' => [
                'name' => $this->manager->name,
                'email' => $this->manager->email,
                'role' => Role::MANAGER,
            ],
        ]);
    }

    private function governorate(string $name, string $slug, bool $verified = true): Governorate
    {
        return Governorate::create(['name' => $name, 'slug' => $slug, 'is_verified' => $verified, 'is_published' => true]);
    }

    private function beach(Governorate $governorate, string $name = 'Bou Jaafar', string $slug = 'bou-jaafar'): Beach
    {
        return Beach::create([
            'governorate_id' => $governorate->id,
            'name' => $name,
            'slug' => $slug,
            'is_published' => true,
            'coordinates_verified' => false,
        ]);
    }

    private function advisoryPayload(Governorate $governorate, array $overrides = []): array
    {
        return array_merge([
            'point_name' => '  Plage de la Corniche  ',
            'governorate_id' => $governorate->id,
            'reported_at' => now()->subDay()->toDateString(),
            'reported_status' => 'restricted',
            'source_name' => ' Ministère de la Santé ',
            'source_url' => ' https://www.santetunisie.rns.tn/avis ',
        ], $overrides);
    }

    // -------------------------------------------------------------------------
    // Bug de câblage
    // -------------------------------------------------------------------------

    public function test_manager_beach_routes_are_declared(): void
    {

        foreach (['index', 'advisories.store', 'edit', 'update', 'statuses.store'] as $name) {
            $this->assertTrue(Route::has("manager.beaches.{$name}"), "Route manager.beaches.{$name} manquante");
        }
    }

    // -------------------------------------------------------------------------
    // Liste et ajout des points signalés
    // -------------------------------------------------------------------------

    public function test_index_lists_governorates_and_shared_advisory_points(): void
    {
        $sousse = $this->governorate('Sousse', 'sousse');
        $this->governorate('Bizerte', 'bizerte');
        BeachHistoricalAdvisoryPoint::create([
            'reported_at' => '2026-06-20',
            'governorate_name' => 'Sousse',
            'point_name' => 'Plage Boujaafar Est',
            'reported_status' => 'restricted',
            'source_name' => 'ONAS',
            'source_url' => 'https://example.tn/releve',
        ]);

        $response = $this->asManager()->get(route('manager.beaches.index'))
            ->assertOk()
            ->assertViewIs('manager.beaches.index')
            ->assertSee('Ajouter un point signalé')
            ->assertSee('Plage Boujaafar Est')
            ->assertSee('20/06/2026')
            ->assertSee('<option value="' . $sousse->id . '" >Sousse</option>', false)
            // Valeur par défaut de l'état : « restricted » présélectionné.
            ->assertSee('<option value="restricted" selected>Restriction signalée</option>', false);

        $this->assertSame(['Bizerte', 'Sousse'], $response->viewData('allGovernorates')->pluck('name')->all());
        $this->assertSame(1, $response->viewData('advisoryPoints')->total());
    }

    public function test_index_shows_an_empty_state_and_is_reserved_to_managers(): void
    {

        $this->asManager()->get(route('manager.beaches.index'))
            ->assertOk()
            ->assertSee('Aucun point signalé enregistré.');

        $citizen = $this->createUserWithRole(Role::CITOYEN);
        $this->actingAs($citizen)
            ->withSession(['user' => ['name' => $citizen->name, 'email' => $citizen->email, 'role' => Role::CITOYEN]])
            ->get(route('manager.beaches.index'))
            ->assertForbidden();
    }

    public function test_index_restores_previous_governorate_and_status_after_a_validation_error(): void
    {
        $this->governorate('Bizerte', 'bizerte');
        $nabeul = $this->governorate('Nabeul', 'nabeul');

        // Nom du point trop court : le formulaire est réaffiché avec les valeurs saisies (old()).
        $response = $this->asManager()
            ->from(route('manager.beaches.index'))
            ->followingRedirects()
            ->post(route('manager.beaches.advisories.store'), $this->advisoryPayload($nabeul, [
                'point_name' => 'X',
                'reported_status' => 'unknown',
            ]));

        $response->assertOk()
            ->assertSee('Le nom du point doit contenir au moins 2 caractères.')
            ->assertSee('<option value="' . $nabeul->id . '" selected>Nabeul</option>', false)
            ->assertDontSee('<option value="" selected>', false)
            ->assertSee('<option value="unknown" selected>À confirmer</option>', false)
            ->assertDontSee('<option value="restricted" selected>', false)
            ->assertSee('value="Ministère de la Santé"', false);

        $this->assertSame(0, BeachHistoricalAdvisoryPoint::count());
    }

    public function test_manager_adds_a_trimmed_advisory_point(): void
    {
        $sousse = $this->governorate('Sousse', 'sousse');

        $this->asManager()
            ->post(route('manager.beaches.advisories.store'), $this->advisoryPayload($sousse))
            ->assertRedirect(route('manager.beaches.index'))
            ->assertSessionHas('success');

        $point = BeachHistoricalAdvisoryPoint::sole();
        $this->assertSame('Plage de la Corniche', $point->point_name);
        $this->assertSame('Sousse', $point->governorate_name);
        $this->assertSame('restricted', $point->reported_status);
        $this->assertSame('Ministère de la Santé', $point->source_name);
        $this->assertSame('https://www.santetunisie.rns.tn/avis', $point->source_url);
    }

    public function test_duplicate_advisory_point_is_refused_with_input_kept(): void
    {
        $sousse = $this->governorate('Sousse', 'sousse');
        $payload = $this->advisoryPayload($sousse, ['reported_status' => 'unknown']);
        BeachHistoricalAdvisoryPoint::create([
            'reported_at' => $payload['reported_at'],
            'governorate_name' => 'Sousse',
            'point_name' => 'Plage de la Corniche',
            'reported_status' => 'restricted',
            'source_name' => 'ONAS',
            'source_url' => 'https://example.tn',
        ]);

        $this->asManager()
            ->from(route('manager.beaches.index'))
            ->followingRedirects()
            ->post(route('manager.beaches.advisories.store'), $payload)
            ->assertOk()
            ->assertSee('Ce point existe déjà pour cette date et ce gouvernorat.')
            ->assertSee('<option value="' . $sousse->id . '" selected>Sousse</option>', false)
            ->assertSee('<option value="unknown" selected>À confirmer</option>', false);

        $this->assertSame(1, BeachHistoricalAdvisoryPoint::count());
    }

    public function test_advisory_point_validation_rules(): void
    {
        $sousse = $this->governorate('Sousse', 'sousse');

        $this->asManager()
            ->post(route('manager.beaches.advisories.store'), $this->advisoryPayload($sousse, [
                'governorate_id' => 999999,
                'reported_at' => now()->addDay()->toDateString(),
                'reported_status' => 'safe',
                'source_url' => 'ftp://example.tn/fichier',
            ]))
            ->assertSessionHasErrors([
                'governorate_id' => 'Le gouvernorat sélectionné n’existe pas.',
                'reported_at' => 'La date ne peut pas être dans le futur.',
                'reported_status' => 'L’état sélectionné est invalide.',
                'source_url' => 'L’URL doit commencer par http:// ou https://.',
            ]);

        $this->assertSame(0, BeachHistoricalAdvisoryPoint::count());
    }

    // -------------------------------------------------------------------------
    // Fiche d'une plage
    // -------------------------------------------------------------------------

    public function test_edit_page_shows_decisions_and_historical_points_of_the_governorate(): void
    {
        $sousse = $this->governorate('Sousse', 'sousse');
        $this->governorate('Gouvernorat brouillon', 'brouillon', verified: false);
        $beach = $this->beach($sousse);
        BeachBathingStatus::create([
            'beach_id' => $beach->id,
            'status' => 'prohibited',
            'source_name' => 'Direction régionale de la santé',
            'valid_from' => '2026-07-01 08:00:00',
            'valid_until' => '2026-07-10 08:00:00',
            'is_verified' => true,
        ]);
        foreach ([['Sousse', 'Point du port'], ['Nabeul', 'Point hors gouvernorat']] as [$gov, $name]) {
            BeachHistoricalAdvisoryPoint::create([
                'reported_at' => '2026-06-20',
                'governorate_name' => $gov,
                'point_name' => $name,
                'reported_status' => 'restricted',
                'source_name' => 'Relevé ministériel',
                'source_url' => 'https://example.tn',
            ]);
        }

        $response = $this->asManager()->get(route('manager.beaches.edit', $beach))
            ->assertOk()
            ->assertSee('Mettre à jour Bou Jaafar')
            ->assertSee('Gouvernorat brouillon (non vérifié)')
            ->assertSee('Direction régionale de la santé')
            ->assertSee('01/07/2026 08:00')
            ->assertSee('Vérifiée')
            ->assertSee('Points signalés dans Sousse — 20/06/2026')
            ->assertSee('Point du port')
            ->assertDontSee('Point hors gouvernorat');

        $this->assertCount(1, $response->viewData('historicalPoints'));
    }

    public function test_edit_page_without_decision_states_that_status_is_unknown(): void
    {
        $beach = $this->beach($this->governorate('Mahdia', 'mahdia'), 'Mahdia', 'mahdia');

        $this->asManager()->get(route('manager.beaches.edit', $beach))
            ->assertOk()
            ->assertSee('Son statut actuel est inconnu.')
            ->assertDontSee('Points signalés dans');
    }

    public function test_manager_updates_the_beach_sheet(): void
    {
        $sousse = $this->governorate('Sousse', 'sousse');
        $monastir = $this->governorate('Monastir', 'monastir');
        $beach = $this->beach($sousse);

        $this->asManager()
            ->put(route('manager.beaches.update', $beach), [
                'name' => 'Bou Jaafar Centre',
                'governorate_id' => $monastir->id,
                'description' => 'Plage urbaine.',
                'latitude' => 35.83,
                'longitude' => 10.64,
                'coordinates_verified' => '1',
            ])
            ->assertRedirect(route('manager.beaches.edit', $beach))
            ->assertSessionHas('success');

        $beach->refresh();
        $this->assertSame('Bou Jaafar Centre', $beach->name);
        $this->assertSame($monastir->id, $beach->governorate_id);
        $this->assertTrue($beach->coordinates_verified);
        // Case non cochée : la plage est retirée du catalogue public.
        $this->assertFalse($beach->is_published);
    }

    public function test_coordinates_cannot_be_marked_verified_without_both_values(): void
    {
        $sousse = $this->governorate('Sousse', 'sousse');
        $beach = $this->beach($sousse);

        $this->asManager()
            ->put(route('manager.beaches.update', $beach), [
                'name' => 'Bou Jaafar',
                'governorate_id' => $sousse->id,
                'latitude' => 35.83,
                'coordinates_verified' => '1',
                'is_published' => '1',
            ])
            ->assertRedirect();

        $beach->refresh();
        $this->assertFalse($beach->coordinates_verified);
        $this->assertTrue($beach->is_published);

        $this->asManager()
            ->put(route('manager.beaches.update', $beach), [
                'name' => '',
                'governorate_id' => 999999,
                'latitude' => 120,
            ])
            ->assertSessionHasErrors(['name', 'governorate_id', 'latitude']);
    }

    // -------------------------------------------------------------------------
    // Décisions de baignade
    // -------------------------------------------------------------------------

    public function test_manager_records_a_verified_bathing_decision(): void
    {
        $beach = $this->beach($this->governorate('Sousse', 'sousse'));

        $this->asManager()
            ->post(route('manager.beaches.statuses.store', $beach), [
                'status' => 'restricted',
                'source_name' => 'Direction régionale de la santé',
                'source_url' => 'https://example.tn/decision',
                'source_reference' => 'DRS-2026-14',
                'valid_from' => '2026-07-01T08:00',
                'valid_until' => '2026-07-05T08:00',
                'is_verified' => '1',
            ])
            ->assertRedirect(route('manager.beaches.edit', $beach))
            ->assertSessionHas('success', 'La nouvelle décision de baignade a été enregistrée.');

        $status = $beach->bathingStatuses()->sole();
        $this->assertSame('restricted', $status->status);
        $this->assertTrue($status->is_verified);
        $this->assertNotNull($status->verified_at);
    }

    public function test_unverified_decision_has_no_verification_date_and_dates_are_checked(): void
    {
        $beach = $this->beach($this->governorate('Sousse', 'sousse'));

        $this->asManager()
            ->post(route('manager.beaches.statuses.store', $beach), [
                'status' => 'safe',
                'source_name' => 'Commune',
                'valid_from' => '2026-07-01T08:00',
                'valid_until' => '2026-07-02T08:00',
            ])
            ->assertRedirect();

        $status = $beach->bathingStatuses()->sole();
        $this->assertFalse($status->is_verified);
        $this->assertNull($status->verified_at);

        $this->asManager()
            ->post(route('manager.beaches.statuses.store', $beach), [
                'status' => 'ferme',
                'source_name' => '',
                'valid_from' => '2026-07-05T08:00',
                'valid_until' => '2026-07-01T08:00',
            ])
            ->assertSessionHasErrors(['status', 'source_name', 'valid_until']);

        $this->assertSame(1, $beach->bathingStatuses()->count());
    }
}
