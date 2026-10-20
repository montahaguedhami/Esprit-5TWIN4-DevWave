<?php

namespace Tests\Feature;

use App\Models\Beach;
use App\Models\BeachBathingStatus;
use App\Models\BeachMeasurement;
use App\Models\Governorate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TuniBeachApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_catalogue_filters_published_beaches_by_governorate_and_paginates(): void
    {
        $publishedGovernorate = $this->governorate('Sousse', 'sousse');
        $otherGovernorate = $this->governorate('Nabeul', 'nabeul');
        $bouJaafar = $this->beach($publishedGovernorate, 'Bou Jaafar', 'bou-jaafar');
        $this->beach($publishedGovernorate, 'Kantaoui', 'kantaoui');
        $this->beach($otherGovernorate, 'Hammamet', 'hammamet');
        $unverifiedGovernorate = Governorate::create(['name' => 'Governorate draft', 'slug' => 'governorate-draft', 'is_published' => true]);
        $this->beach($unverifiedGovernorate, 'Hidden beach', 'hidden-beach');
        $this->beach($publishedGovernorate, 'Non publiée', 'unpublished', ['is_published' => false]);
        BeachBathingStatus::create([
            'beach_id' => $bouJaafar->id,
            'status' => 'prohibited',
            'source_name' => 'Verified health authority',
            'source_url' => 'https://example.gov.tn/bathing/decision',
            'valid_from' => now()->subDay(),
            'valid_until' => now()->addDay(),
            'verified_at' => now(),
            'is_verified' => true,
        ]);

        $this->getJson('/api/v1/governorates?per_page=1')
            ->assertOk()
            ->assertJsonPath('meta.total', 2)
            ->assertJsonPath('meta.last_page', 2);

        $this->getJson('/api/v1/beaches?governorate=sousse&per_page=1&page=2')
            ->assertOk()
            ->assertJsonPath('meta.total', 2)
            ->assertJsonPath('data.0.slug', 'kantaoui');

        $this->getJson('/api/v1/beaches?q=Hamma')
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.slug', 'hammamet');

        $this->getJson('/api/v1/beaches?status=prohibited')
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.slug', 'bou-jaafar');

        $this->getJson('/api/v1/beaches?status=unknown')
            ->assertOk()
            ->assertJsonPath('meta.total', 2);

        $this->getJson('/api/v1/beaches?status=invalid')->assertUnprocessable();
    }

    public function test_beach_details_default_to_unknown_and_only_expose_a_current_verified_decision(): void
    {
        $governorate = $this->governorate('Monastir', 'monastir');
        $beach = $this->beach($governorate, 'Skanes', 'skanes', [
            'latitude' => 35.77,
            'longitude' => 10.75,
            'coordinates_verified' => false,
            'description' => 'Public description',
        ]);

        $this->getJson('/api/v1/beaches/skanes')
            ->assertOk()
            ->assertJsonPath('data.bathing_status.value', 'unknown')
            ->assertJsonPath('data.coordinates.verified', false)
            ->assertJsonPath('data.description', 'Public description');

        $status = BeachBathingStatus::create([
            'beach_id' => $beach->id,
            'status' => 'safe',
            'source_name' => 'Authority example',
            'source_url' => 'https://example.gov.tn/bathing/skanes',
            'source_reference' => 'DEC-2026-42',
            'issued_at' => now()->subDay(),
            'valid_from' => now()->subDay(),
            'valid_until' => now()->addDay(),
            'verified_at' => now()->subDay(),
            'is_verified' => true,
        ]);

        $this->getJson('/api/v1/beaches/skanes')
            ->assertOk()
            ->assertJsonPath('data.bathing_status.value', 'safe')
            ->assertJsonPath('data.bathing_status.source.reference', 'DEC-2026-42')
            ->assertJsonPath('data.bathing_status.verified_at', $status->verified_at->toISOString());

        $status->update(['valid_until' => now()->subMinute()]);

        $this->getJson('/api/v1/beaches/skanes')
            ->assertOk()
            ->assertJsonPath('data.bathing_status.value', 'unknown');

        $this->getJson('/api/v1/beaches/not-published')->assertNotFound();
    }

    public function test_measurement_history_returns_only_public_records_and_whitelisted_fields(): void
    {
        $governorate = $this->governorate('Mahdia', 'mahdia');
        $beach = $this->beach($governorate, 'Mahdia', 'mahdia-beach');

        BeachMeasurement::create([
            'beach_id' => $beach->id,
            'reference' => 'SAMPLE-17',
            'sampled_at' => now()->subDay(),
            'parameters' => [
                'ph' => ['value' => 7.4, 'unit' => 'pH'],
                'internal_note' => ['value' => 'private'],
            ],
            'source_name' => 'Public Lab',
            'source_url' => 'https://example.gov.tn/lab/17',
            'is_verified' => false,
            'is_public' => true,
        ]);
        BeachMeasurement::create([
            'beach_id' => $beach->id,
            'reference' => 'PRIVATE-18',
            'sampled_at' => now(),
            'is_public' => false,
        ]);
        BeachMeasurement::create([
            'beach_id' => $beach->id,
            'reference' => 'UNVERIFIED-19',
            'sampled_at' => now()->addMinute(),
            'source_name' => 'Unverified source',
            'is_verified' => false,
            'is_public' => true,
        ]);
        BeachMeasurement::create([
            'beach_id' => $beach->id,
            'reference' => 'VERIFIED-20',
            'sampled_at' => now()->subHours(2),
            'parameters' => ['ph' => ['value' => 7.4, 'unit' => 'pH']],
            'source_name' => 'Public Lab',
            'source_url' => 'https://example.gov.tn/lab/20',
            'is_verified' => true,
            'is_public' => true,
        ]);

        $this->getJson('/api/v1/beaches/mahdia-beach/measurements')
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.reference', 'VERIFIED-20')
            ->assertJsonPath('data.0.parameters.ph.value', 7.4)
            ->assertJsonPath('data.0.parameters.ph.unit', 'pH')
            ->assertJsonPath('data.0.verification.verified', true)
            ->assertJsonMissingPath('data.0.parameters.internal_note')
            ->assertDontSee('PRIVATE-18')
            ->assertDontSee('UNVERIFIED-19');

        $this->getJson('/api/v1/beaches/missing/measurements')->assertNotFound();
    }

    public function test_invalid_pagination_is_rejected_and_model_changes_invalidate_the_catalogue_cache(): void
    {
        $this->getJson('/api/v1/beaches?per_page=101')->assertUnprocessable();

        $this->getJson('/api/v1/beaches')->assertJsonPath('meta.total', 0);
        $governorate = $this->governorate('Bizerte', 'bizerte');
        $this->beach($governorate, 'Sidi Salem', 'sidi-salem');

        $this->getJson('/api/v1/beaches')->assertJsonPath('meta.total', 1);
    }

    public function test_citizen_beach_map_is_role_protected(): void
    {
        $this->get('/citizen/beaches')->assertRedirect(route('auth.login'));

        $this->withSession(['user' => ['name' => 'Manager', 'role' => 'manager']])
            ->get('/citizen/beaches')
            ->assertForbidden();

        $this->withSession(['user' => ['name' => 'Citizen', 'email' => 'citizen@example.test', 'role' => 'citizen']])
            ->get('/citizen/beaches')
            ->assertOk()
            ->assertSee('Plages tunisiennes')
            ->assertSee('bathing-status-filter')
            ->assertSee('inconnu')
            ->assertSee('ne signifie pas')
            ->assertSee('Aucune plage publiée');
    }

    private function governorate(string $name, string $slug): Governorate
    {
        return Governorate::create(['name' => $name, 'slug' => $slug, 'is_verified' => true, 'is_published' => true]);
    }

    /** @param array<string, mixed> $overrides */
    private function beach(Governorate $governorate, string $name, string $slug, array $overrides = []): Beach
    {
        return Beach::create(array_merge([
            'governorate_id' => $governorate->id,
            'name' => $name,
            'slug' => $slug,
            'is_published' => true,
            'coordinates_verified' => false,
        ], $overrides));
    }
}
