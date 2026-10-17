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
 * Tests for the JWT-protected Water Quality API endpoints.
 *
 * JWT tokens are generated with auth('api')->login($user) — this exercises the
 * same JWT driver as production without modifying the JwtAuthenticate middleware.
 */
class WaterQualityApiTest extends TestCase
{
    use RefreshDatabase;
    use InteractsWithRbac;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRbac();
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /** Generate a Bearer token for the given user via the JWT driver. */
    private function tokenFor(User $user): string
    {
        return auth('api')->login($user);
    }

    /** Create a minimal active measurement point. */
    private function createPoint(string $code = 'API-PT-001'): PointMesure
    {
        return PointMesure::create([
            'code'   => $code,
            'nom'    => 'Point API test',
            'type'   => 'Station',
            'statut' => 'actif',
        ]);
    }

    /** Minimal valid measurement payload. */
    private function measurePayload(int $pointId, string $ref = 'API-MES-001'): array
    {
        return [
            'reference'       => $ref,
            'point_mesure_id' => $pointId,
            'date_mesure'     => '2026-10-15 08:00:00',
            'ph'              => 7.2,
            'turbidite'       => 0.5,
            'chlore_residuel' => 0.8,
            'plomb'           => 2.0,
            'nitrates'        => 10.0,
            'source_donnee'   => 'manuel',
        ];
    }

    // -------------------------------------------------------------------------
    // 5.3 — GET /api/mesures without token → 401
    // -------------------------------------------------------------------------

    public function test_get_mesures_without_token_returns_401(): void
    {
        $response = $this->getJson('/api/mesures');

        $response->assertStatus(401);
    }

    // -------------------------------------------------------------------------
    // 5.3 — POST /api/mesures with a citoyen JWT → 403
    // -------------------------------------------------------------------------

    public function test_post_mesure_as_citizen_returns_403(): void
    {
        $citizen = $this->createUserWithRole(Role::CITOYEN);
        $token   = $this->tokenFor($citizen);
        $point   = $this->createPoint('API-PT-CIT');

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson('/api/mesures', $this->measurePayload($point->id, 'API-CIT-001'));

        // The jwt.role middleware returns 403 for insufficient role
        $response->assertStatus(403);

        $this->assertDatabaseMissing('mesures_qualites', ['reference' => 'API-CIT-001']);
    }

    // -------------------------------------------------------------------------
    // 5.3 — POST /api/mesures with a technicien JWT → 201
    // -------------------------------------------------------------------------

    public function test_post_mesure_as_technicien_returns_201(): void
    {
        $technicien = $this->createUserWithRole(Role::TECHNICIEN);
        $token      = $this->tokenFor($technicien);
        $point      = $this->createPoint('API-PT-TECH');

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson('/api/mesures', $this->measurePayload($point->id, 'API-TECH-001'));

        $response->assertStatus(201);
        $response->assertJsonPath('data.reference', 'API-TECH-001');

        $this->assertDatabaseHas('mesures_qualites', [
            'reference'    => 'API-TECH-001',
            'source_donnee' => 'manuel',
        ]);
    }

    // -------------------------------------------------------------------------
    // Bonus: ensure an expired / invalid token is also rejected with 401
    // -------------------------------------------------------------------------

    public function test_get_mesures_with_invalid_token_returns_401(): void
    {
        $response = $this->withHeaders(['Authorization' => 'Bearer invalid.token.value'])
            ->getJson('/api/mesures');

        $response->assertStatus(401);
    }
}
