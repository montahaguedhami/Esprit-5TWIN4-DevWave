<?php

namespace Tests\Feature;

use App\Models\Beach;
use Database\Seeders\TuniBeachApalSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TuniBeachApalSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_apal_catalogue_import_is_idempotent_and_does_not_invent_coordinates_or_bathing_data(): void
    {
        $this->seed(TuniBeachApalSeeder::class);

        $this->assertDatabaseCount('tunibeach_governorates', 11);
        $this->assertDatabaseCount('tunibeach_beaches', 129);
        $this->assertDatabaseCount('tunibeach_measurements', 0);
        $this->assertDatabaseCount('tunibeach_bathing_statuses', 0);

        $tabarka = Beach::query()->where('slug', 'jendouba-tabarka')->firstOrFail();
        $this->assertNull($tabarka->latitude);
        $this->assertNull($tabarka->longitude);
        $this->assertFalse($tabarka->coordinates_verified);
        $this->assertTrue($tabarka->is_published);
        $this->assertTrue($tabarka->governorate->is_verified);

        $this->getJson('/api/v1/beaches?status=unknown&per_page=100')
            ->assertOk()
            ->assertJsonPath('meta.total', 129);

        $this->seed(TuniBeachApalSeeder::class);

        $this->assertDatabaseCount('tunibeach_governorates', 11);
        $this->assertDatabaseCount('tunibeach_beaches', 129);
        $this->assertDatabaseCount('tunibeach_measurements', 0);
        $this->assertDatabaseCount('tunibeach_bathing_statuses', 0);
    }
}
