<?php

namespace Tests\Feature;

use App\Models\JwtRefreshToken;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\Concerns\InteractsWithRbac;
use Tests\TestCase;

/**
 * Tests de l'authentification JWT de l'API (/api/auth/*) :
 * connexion, rotation du refresh token, cookies HttpOnly, déconnexion,
 * profil et permissions de l'utilisateur connecté.
 *
 * Les routes login/refresh partagent une limite de 5 requêtes par minute :
 * chaque test en effectue au plus 5.
 */
class JwtAuthApiTest extends TestCase
{
    use RefreshDatabase;
    use InteractsWithRbac;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRbac();
    }

    private function technician(array $attrs = []): User
    {
        return $this->createUserWithRole(Role::TECHNICIEN, array_merge([
            'email' => 'tech.api@aquasecure.test',
            'password' => 'secret-pass-123',
        ], $attrs));
    }

    private function login(string $email = 'tech.api@aquasecure.test', string $password = 'secret-pass-123', array $headers = []): TestResponse
    {
        return $this->withHeaders($headers)->postJson('/api/auth/login', [
            'email' => $email,
            'password' => $password,
        ]);
    }

    // -------------------------------------------------------------------------
    // POST /api/auth/login
    // -------------------------------------------------------------------------

    public function test_login_validates_credentials_format(): void
    {
        $this->postJson('/api/auth/login', ['email' => 'pas-un-email'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email', 'password']);
    }

    public function test_login_rejects_unknown_email_wrong_password_and_inactive_account(): void
    {
        $this->technician();
        $this->createUserWithRole(Role::TECHNICIEN, [
            'email' => 'inactif@aquasecure.test',
            'password' => 'secret-pass-123',
            'is_active' => false,
        ]);

        $this->login('inconnu@aquasecure.test')
            ->assertUnauthorized()
            ->assertJsonPath('message', 'Identifiants invalides.');

        $this->login(password: 'mauvais-mot-de-passe')
            ->assertUnauthorized()
            ->assertJsonPath('message', 'Identifiants invalides.');

        $this->login('inactif@aquasecure.test')
            ->assertForbidden()
            ->assertJsonPath('message', 'Compte désactivé.');

        $this->assertSame(0, JwtRefreshToken::count());
    }

    public function test_successful_login_returns_a_token_pair_usable_on_protected_routes(): void
    {
        $user = $this->technician();

        $response = $this->login()
            ->assertOk()
            ->assertJsonStructure(['token_type', 'access_token', 'expires_in', 'refresh_token', 'refresh_expires_in'])
            ->assertJsonPath('token_type', 'bearer')
            ->assertJsonPath('expires_in', (int) config('jwt.ttl') * 60);

        // Le refresh token n'est stocké que sous forme de hachage SHA-256.
        $refresh = $response->json('refresh_token');
        $this->assertDatabaseHas('jwt_refresh_tokens', [
            'user_id' => $user->id,
            'jti' => hash('sha256', $refresh),
            'revoked' => false,
        ]);
        $this->assertDatabaseMissing('jwt_refresh_tokens', ['jti' => $refresh]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'api_login', 'user_id' => $user->id]);

        $this->withHeaders(['Authorization' => 'Bearer ' . $response->json('access_token')])
            ->getJson('/api/auth/me')
            ->assertOk()
            ->assertJsonPath('id', $user->id)
            ->assertJsonPath('email', 'tech.api@aquasecure.test')
            ->assertJsonPath('is_active', true)
            ->assertJsonPath('roles', ['technicien'])
            ->assertJsonFragment(['measurements.create']);
    }

    public function test_new_login_revokes_previous_refresh_tokens(): void
    {
        $user = $this->technician();

        $first = $this->login()->assertOk()->json('refresh_token');
        $second = $this->login()->assertOk()->json('refresh_token');

        $this->assertNotSame($first, $second);
        $this->assertTrue(JwtRefreshToken::where('jti', hash('sha256', $first))->value('revoked'));
        $this->assertSame(1, JwtRefreshToken::where('user_id', $user->id)->where('revoked', false)->count());
    }

    public function test_login_can_deliver_tokens_as_http_only_cookies(): void
    {
        $this->technician();

        $response = $this->login(headers: ['X-Token-Delivery' => 'cookie'])->assertOk();

        $access = $response->getCookie('access_token', false);
        $refresh = $response->getCookie('refresh_token', false);

        $this->assertNotNull($access);
        $this->assertNotNull($refresh);
        $this->assertTrue($access->isHttpOnly());
        $this->assertTrue($refresh->isHttpOnly());
        $this->assertSame('/api/auth/refresh', $refresh->getPath());
        $this->assertSame('lax', $access->getSameSite());
        $this->assertSame($response->json('access_token'), $access->getValue());
    }

    // -------------------------------------------------------------------------
    // POST /api/auth/refresh
    // -------------------------------------------------------------------------

    public function test_refresh_rejects_missing_unknown_and_expired_tokens(): void
    {
        $user = $this->technician();
        JwtRefreshToken::create([
            'user_id' => $user->id,
            'jti' => hash('sha256', 'jeton-expire'),
            'expires_at' => now()->subMinute(),
            'revoked' => false,
        ]);

        $this->postJson('/api/auth/refresh')
            ->assertUnauthorized()
            ->assertJsonPath('error', 'missing_refresh_token');

        $this->postJson('/api/auth/refresh', ['refresh_token' => 'jeton-inconnu'])
            ->assertUnauthorized()
            ->assertJsonPath('error', 'invalid_refresh_token');

        $this->postJson('/api/auth/refresh', ['refresh_token' => 'jeton-expire'])
            ->assertUnauthorized()
            ->assertJsonPath('error', 'invalid_refresh_token');
    }

    public function test_refresh_token_is_single_use_and_rotated(): void
    {
        $this->technician();
        $old = $this->login()->json('refresh_token');

        $response = $this->postJson('/api/auth/refresh', ['refresh_token' => $old])
            ->assertOk()
            ->assertJsonStructure(['access_token', 'refresh_token']);

        $new = $response->json('refresh_token');
        $this->assertNotSame($old, $new);
        $this->assertTrue(JwtRefreshToken::where('jti', hash('sha256', $old))->value('revoked'));

        // Rejouer l'ancien jeton est refusé.
        $this->postJson('/api/auth/refresh', ['refresh_token' => $old])
            ->assertUnauthorized()
            ->assertJsonPath('error', 'invalid_refresh_token');

        $this->withHeaders(['Authorization' => 'Bearer ' . $response->json('access_token')])
            ->getJson('/api/auth/me')
            ->assertOk();
    }

    public function test_refresh_reads_the_cookie_and_can_answer_with_cookies(): void
    {
        $this->technician();
        $raw = $this->login()->json('refresh_token');

        // Les requêtes JSON de test n'envoient les cookies qu'avec withCredentials().
        $response = $this->withCredentials()
            ->withUnencryptedCookie('refresh_token', $raw)
            ->withHeaders(['X-Token-Delivery' => 'cookie'])
            ->postJson('/api/auth/refresh')
            ->assertOk();

        $this->assertSame($response->json('refresh_token'), $response->getCookie('refresh_token', false)->getValue());
        $this->assertNotNull($response->getCookie('access_token', false));
    }

    public function test_refresh_is_refused_when_the_account_was_deactivated(): void
    {
        $user = $this->technician();
        $raw = $this->login()->json('refresh_token');

        $user->update(['is_active' => false]);

        $this->postJson('/api/auth/refresh', ['refresh_token' => $raw])
            ->assertForbidden()
            ->assertJsonPath('message', 'Compte désactivé.');

        // Le jeton présenté est tout de même consommé.
        $this->assertTrue(JwtRefreshToken::where('jti', hash('sha256', $raw))->value('revoked'));
    }

    // -------------------------------------------------------------------------
    // POST /api/auth/logout, GET /api/auth/me, GET /api/user/permissions
    // -------------------------------------------------------------------------

    public function test_logout_blacklists_the_access_token_and_revokes_refresh_tokens(): void
    {
        $user = $this->technician();
        $tokens = $this->login()->json();
        $headers = ['Authorization' => 'Bearer ' . $tokens['access_token']];

        $this->withHeaders($headers)
            ->postJson('/api/auth/logout')
            ->assertOk()
            ->assertJsonPath('message', 'Déconnecté avec succès.');

        $this->assertSame(0, JwtRefreshToken::where('user_id', $user->id)->where('revoked', false)->count());
        $this->assertDatabaseHas('audit_logs', ['action' => 'api_logout', 'user_id' => $user->id]);

        $this->withHeaders($headers)->getJson('/api/auth/me')->assertUnauthorized();
        $this->postJson('/api/auth/refresh', ['refresh_token' => $tokens['refresh_token']])->assertUnauthorized();
    }

    public function test_logout_with_cookie_delivery_expires_the_cookies(): void
    {
        $user = $this->technician();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . auth('api')->login($user),
            'X-Token-Delivery' => 'cookie',
        ])->postJson('/api/auth/logout')->assertOk();

        $this->assertTrue($response->getCookie('access_token', false)->isCleared());
        $this->assertTrue($response->getCookie('refresh_token', false)->isCleared());
    }

    public function test_access_token_can_be_read_from_the_cookie(): void
    {
        $user = $this->technician();

        $this->withCredentials()
            ->withUnencryptedCookie('access_token', auth('api')->login($user))
            ->getJson('/api/user/permissions')
            ->assertOk()
            ->assertJsonPath('roles', ['technicien']);
    }

    public function test_permissions_endpoint_lists_role_permissions(): void
    {
        $manager = $this->createUserWithRole(Role::MANAGER);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . auth('api')->login($manager)])
            ->getJson('/api/user/permissions')
            ->assertOk()
            ->assertJsonPath('roles', ['manager']);

        $permissions = $response->json('permissions');
        $this->assertContains('measurements.validate', $permissions);
        $this->assertNotContains('measurements.create', $permissions);
        $this->assertSame(count($permissions), count(array_unique($permissions)));

        $this->flushHeaders()->getJson('/api/user/permissions')
            ->assertUnauthorized()
            ->assertJsonPath('error', 'unauthenticated');
    }

    public function test_deactivated_account_cannot_use_a_still_valid_token(): void
    {
        $user = $this->technician();
        $token = auth('api')->login($user);
        $user->update(['is_active' => false]);

        $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->getJson('/api/auth/me')
            ->assertForbidden()
            ->assertJsonPath('error', 'account_inactive');
    }
}
