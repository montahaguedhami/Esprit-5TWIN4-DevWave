<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Authentification à deux facteurs (TwoFactorController) : activation,
 * confirmation par code TOTP, désactivation et codes de récupération.
 *
 * Les codes TOTP sont calculés dans le test (RFC 6238, HMAC-SHA1, 6 chiffres,
 * période de 30 s), indépendamment de la bibliothèque utilisée par
 * l'application.
 */
class TwoFactorAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    private const PASSWORD = 'Actuel#Secret2026';

    /** Secret base32 de démonstration (RFC 4648). */
    private const SECRET = 'JBSWY3DPEHPK3PXP';

    private const MISSING_LIBRARY = 'Bibliothèques 2FA absentes (pragmarx/google2fa, bacon/bacon-qr-code) : lancer composer install.';

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->artisan('db:seed', ['--class' => 'RbacSeeder']);
    }

    /* ────────────────────────────────────────────────────────────────────────
     |  Page de configuration
     |_______________________________________________________________________*/

    public function test_guest_cannot_reach_two_factor_settings(): void
    {
        $this->get(route('profile.two-factor'))->assertRedirect(route('auth.login'));
        $this->post(route('profile.two-factor.enable'))->assertRedirect(route('auth.login'));
    }

    public function test_settings_page_reflects_the_current_state(): void
    {
        $user = $this->loginAs();

        $this->get(route('profile.two-factor'))
            ->assertOk()
            ->assertViewIs('profile.two-factor')
            ->assertViewHas('enabled', false);

        $this->enableFor($user);

        $this->get(route('profile.two-factor'))
            ->assertOk()
            ->assertViewHas('enabled', true);
    }

    /* ────────────────────────────────────────────────────────────────────────
     |  Activation
     |_______________________________________________________________________*/

    public function test_enabling_generates_a_secret_and_recovery_codes(): void
    {
        $this->requireTwoFactorLibrary();
        $user = $this->loginAs();

        $this->post(route('profile.two-factor.enable'))
            ->assertRedirect(route('profile.two-factor.confirm'))
            ->assertSessionHas('success');

        $user->refresh();
        $this->assertNotNull($user->two_factor_secret);
        $this->assertNull($user->two_factor_confirmed_at, 'La 2FA n\'est active qu\'après confirmation.');
        $this->assertMatchesRegularExpression('/^[A-Z2-7]{16,}$/', decrypt($user->two_factor_secret));

        $codes = json_decode(decrypt($user->two_factor_recovery_codes), true);
        $this->assertCount(8, $codes);
        $this->assertCount(8, array_unique($codes));

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'action'  => 'two_factor_setup_started',
        ]);
    }

    public function test_enabling_again_keeps_the_pending_secret(): void
    {
        $user = $this->loginAs();
        $this->pendingSetupFor($user);
        $secretAvant = $user->fresh()->two_factor_secret;

        // Un second clic ne doit pas invalider le QR code déjà scanné.
        $this->post(route('profile.two-factor.enable'))
            ->assertRedirect(route('profile.two-factor.confirm'));

        $this->assertSame($secretAvant, $user->fresh()->two_factor_secret);
        $this->assertDatabaseMissing('audit_logs', ['action' => 'two_factor_setup_started']);
    }

    public function test_enabling_when_already_active_changes_nothing(): void
    {
        $user = $this->loginAs();
        $this->enableFor($user);
        $secretAvant = $user->fresh()->two_factor_secret;

        $this->post(route('profile.two-factor.enable'))
            ->assertRedirect(route('profile.two-factor'))
            ->assertSessionHas('info', 'L\'authentification à deux facteurs est déjà activée.');

        $this->assertSame($secretAvant, $user->fresh()->two_factor_secret);
    }

    /* ────────────────────────────────────────────────────────────────────────
     |  Confirmation
     |_______________________________________________________________________*/

    public function test_confirmation_page_needs_a_pending_setup(): void
    {
        $user = $this->loginAs();

        // Aucune configuration en cours.
        $this->get(route('profile.two-factor.confirm'))
            ->assertRedirect(route('profile.two-factor'));

        // Déjà confirmée.
        $this->enableFor($user);
        $this->get(route('profile.two-factor.confirm'))
            ->assertRedirect(route('profile.two-factor'));
    }

    public function test_confirmation_page_shows_the_qr_code_and_the_manual_key(): void
    {
        $this->requireTwoFactorLibrary();
        $user = $this->loginAs();
        $codes = $this->pendingSetupFor($user);

        $this->get(route('profile.two-factor.confirm'))
            ->assertOk()
            ->assertViewIs('profile.two-factor-confirm')
            ->assertViewHas('secret', self::SECRET)
            ->assertViewHas('recoveryCodes', $codes)
            ->assertViewHas('qrCodeSvg', fn (string $svg) => str_contains($svg, '<svg'))
            ->assertSee(self::SECRET);
    }

    public function test_confirmation_code_is_validated(): void
    {
        $user = $this->loginAs();

        $this->from(route('profile.two-factor.confirm'))
            ->post(route('profile.two-factor.confirm.post'), ['code' => '123'])
            ->assertSessionHasErrors('code');

        // Code bien formé mais aucune configuration en cours.
        $this->post(route('profile.two-factor.confirm.post'), ['code' => '123456'])
            ->assertRedirect(route('profile.two-factor'))
            ->assertSessionHas('error', 'Aucune configuration 2FA en cours.');

        $this->assertNull($user->fresh()->two_factor_confirmed_at);
    }

    public function test_wrong_totp_code_does_not_enable_two_factor(): void
    {
        $this->requireTwoFactorLibrary();
        $user = $this->loginAs();
        $this->pendingSetupFor($user);

        // Code calculé pour une fenêtre très éloignée : forcément refusé.
        $code = $this->totp(self::SECRET, time() - 3600);

        $this->from(route('profile.two-factor.confirm'))
            ->post(route('profile.two-factor.confirm.post'), ['code' => $code])
            ->assertRedirect(route('profile.two-factor.confirm'))
            ->assertSessionHasErrors(['code' => 'Code invalide. Vérifiez le code dans votre application.']);

        $this->assertNull($user->fresh()->two_factor_confirmed_at);
    }

    public function test_valid_totp_code_enables_two_factor(): void
    {
        $this->requireTwoFactorLibrary();
        $user = $this->loginAs();
        $this->pendingSetupFor($user);

        $this->post(route('profile.two-factor.confirm.post'), ['code' => $this->totp(self::SECRET)])
            ->assertRedirect(route('profile.two-factor'))
            ->assertSessionHas('success');

        $this->assertNotNull($user->fresh()->two_factor_confirmed_at);
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'action'  => 'two_factor_enabled',
        ]);
    }

    /* ────────────────────────────────────────────────────────────────────────
     |  Désactivation
     |_______________________________________________________________________*/

    public function test_disabling_requires_the_password(): void
    {
        $user = $this->loginAs();
        $this->enableFor($user);

        $this->from(route('profile.two-factor'))
            ->delete(route('profile.two-factor.disable'), [])
            ->assertSessionHasErrors('password');

        $this->from(route('profile.two-factor'))
            ->delete(route('profile.two-factor.disable'), ['password' => 'Mauvais#Secret2026'])
            ->assertRedirect(route('profile.two-factor'))
            ->assertSessionHasErrors(['password' => 'Mot de passe incorrect.']);

        $this->assertNotNull($user->fresh()->two_factor_confirmed_at);
    }

    public function test_disabling_clears_every_two_factor_secret(): void
    {
        $user = $this->loginAs();
        $this->enableFor($user);

        $this->delete(route('profile.two-factor.disable'), ['password' => self::PASSWORD])
            ->assertRedirect(route('profile.two-factor'))
            ->assertSessionHas('success', 'Authentification à deux facteurs désactivée.');

        $user->refresh();
        $this->assertNull($user->two_factor_secret);
        $this->assertNull($user->two_factor_recovery_codes);
        $this->assertNull($user->two_factor_confirmed_at);
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'action'  => 'two_factor_disabled',
        ]);
    }

    /* ────────────────────────────────────────────────────────────────────────
     |  Codes de récupération
     |_______________________________________________________________________*/

    public function test_recovery_codes_require_two_factor_to_be_active(): void
    {
        $user = $this->loginAs();
        $this->pendingSetupFor($user); // en attente, pas encore confirmée

        $this->post(route('profile.two-factor.regenerate'))
            ->assertRedirect(route('profile.two-factor'))
            ->assertSessionHas('error', '2FA non activé.');

        $this->get(route('profile.two-factor.recovery'))
            ->assertRedirect(route('profile.two-factor'));
    }

    public function test_recovery_codes_can_be_viewed_and_regenerated(): void
    {
        $user = $this->loginAs();
        $anciens = $this->enableFor($user);

        $this->get(route('profile.two-factor.recovery'))
            ->assertOk()
            ->assertViewIs('profile.two-factor-recovery')
            ->assertViewHas('recoveryCodes', $anciens)
            ->assertSee($anciens[0]);

        $this->post(route('profile.two-factor.regenerate'))
            ->assertRedirect(route('profile.two-factor'))
            ->assertSessionHas('success', 'Codes de récupération régénérés. Notez-les en lieu sûr.');

        $nouveaux = json_decode(decrypt($user->fresh()->two_factor_recovery_codes), true);
        $this->assertCount(8, $nouveaux);
        $this->assertEmpty(array_intersect($anciens, $nouveaux), 'Les anciens codes ne doivent plus être valables.');
        foreach ($nouveaux as $code) {
            $this->assertMatchesRegularExpression('/^[A-Z0-9]{1,10}$/', $code);
        }

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'action'  => 'two_factor_recovery_codes_regenerated',
        ]);
    }

    /* ────────────────────────────────────────────────────────────────────────
     |  Helpers
     |_______________________________________________________________________*/

    private function requireTwoFactorLibrary(): void
    {
        if (! class_exists(\PragmaRX\Google2FA\Google2FA::class) || ! class_exists(\BaconQrCode\Writer::class)) {
            $this->markTestSkipped(self::MISSING_LIBRARY);
        }
    }

    private function loginAs(): User
    {
        $user = User::factory()->create([
            'email'     => uniqid('2fa.', true).'@test.local',
            'password'  => self::PASSWORD,
            'is_active' => true,
        ]);
        $user->assignRole(Role::CITOYEN);

        $this->actingAs($user);
        $this->withSession(['user' => [
            'id'       => $user->id,
            'name'     => $user->name,
            'email'    => $user->email,
            'role'     => Role::CITOYEN,
            'role_key' => Role::CITOYEN,
            'roles'    => [Role::CITOYEN],
        ]]);

        return $user;
    }

    /**
     * Configuration en attente de confirmation (secret connu du test).
     *
     * @return array<int, string> codes de récupération
     */
    private function pendingSetupFor(User $user): array
    {
        $codes = ['AAAA111111', 'BBBB222222', 'CCCC333333', 'DDDD444444',
            'EEEE555555', 'FFFF666666', 'GGGG777777', 'HHHH888888'];

        $user->forceFill([
            'two_factor_secret'         => encrypt(self::SECRET),
            'two_factor_recovery_codes' => encrypt(json_encode($codes)),
            'two_factor_confirmed_at'   => null,
        ])->save();

        return $codes;
    }

    /**
     * 2FA déjà activée et confirmée.
     *
     * @return array<int, string> codes de récupération
     */
    private function enableFor(User $user): array
    {
        $codes = $this->pendingSetupFor($user);
        $user->forceFill(['two_factor_confirmed_at' => now()])->save();

        return $codes;
    }

    /**
     * Code TOTP (RFC 6238) pour un secret base32.
     */
    private function totp(string $base32Secret, ?int $timestamp = null): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $bits = '';
        foreach (str_split(rtrim(strtoupper($base32Secret), '=')) as $char) {
            $bits .= str_pad(decbin(strpos($alphabet, $char)), 5, '0', STR_PAD_LEFT);
        }
        $key = '';
        foreach (str_split($bits, 8) as $byte) {
            if (strlen($byte) === 8) {
                $key .= chr(bindec($byte));
            }
        }

        $counter = intdiv($timestamp ?? time(), 30);
        $hash = hash_hmac('sha1', pack('N*', 0, $counter), $key, true);
        $offset = ord($hash[19]) & 0x0F;
        $value = ((ord($hash[$offset]) & 0x7F) << 24)
            | (ord($hash[$offset + 1]) << 16)
            | (ord($hash[$offset + 2]) << 8)
            | ord($hash[$offset + 3]);

        return str_pad((string) ($value % 1000000), 6, '0', STR_PAD_LEFT);
    }
}
