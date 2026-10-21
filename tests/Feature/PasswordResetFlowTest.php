<?php

namespace Tests\Feature;

use App\Models\JwtRefreshToken;
use App\Models\PasswordResetOtp;
use App\Models\User;
use App\Notifications\SendPasswordResetOtp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

/**
 * Mot de passe oublié par code OTP (PasswordResetController) :
 *   A) demande du code par email,
 *   B) vérification du code,
 *   C) choix du nouveau mot de passe avec la preuve émise à l'étape B.
 *
 * Chaque test reste sous la limite « throttle:10,1 » des routes POST, qui est
 * partagée par adresse IP.
 */
class PasswordResetFlowTest extends TestCase
{
    use RefreshDatabase;

    private const GENERIC_STATUS = 'Si un compte est associé à cette adresse, un code de vérification a été envoyé.';

    private const NEW_PASSWORD = 'Nouveau#Secret2026';

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    /* ────────────────────────────────────────────────────────────────────────
     |  Étape A — demande du code
     |_______________________________________________________________________*/

    public function test_request_requires_a_valid_email(): void
    {
        Notification::fake();

        $this->from(route('auth.forgot-password'))
            ->post(route('forgot-password.post'), ['email' => 'pas-un-email'])
            ->assertRedirect(route('auth.forgot-password'))
            ->assertSessionHasErrors('email');

        Notification::assertNothingSent();
        $this->assertDatabaseCount('password_reset_otps', 0);
    }

    public function test_unknown_email_gets_the_generic_answer_and_no_code(): void
    {
        Notification::fake();

        $this->from(route('auth.forgot-password'))
            ->post(route('forgot-password.post'), ['email' => 'inconnu@aquasecure.tn'])
            ->assertRedirect(route('auth.forgot-password'))
            ->assertSessionHas('status', self::GENERIC_STATUS)
            ->assertSessionMissing('password_reset_request_id');

        Notification::assertNothingSent();
        $this->assertDatabaseCount('password_reset_otps', 0);

        // La demande compte quand même dans la limitation (clé hachée sha256).
        $this->assertSame(1, RateLimiter::attempts('password-reset-request:'.hash('sha256', 'inconnu@aquasecure.tn')));
    }

    public function test_deactivated_account_never_receives_a_code(): void
    {
        Notification::fake();
        $user = $this->makeUser(isActive: false);

        $this->from(route('auth.forgot-password'))
            ->post(route('forgot-password.post'), ['email' => $user->email])
            ->assertRedirect(route('auth.forgot-password'))
            ->assertSessionHas('status', self::GENERIC_STATUS)
            ->assertSessionMissing('password_reset_request_id');

        Notification::assertNothingSent();
        $this->assertDatabaseCount('password_reset_otps', 0);
    }

    public function test_code_is_emailed_and_only_its_hash_is_stored(): void
    {
        Notification::fake();
        $user = $this->makeUser(email: 'nour@example.tn');

        // L'adresse est normalisée (casse) avant la recherche du compte.
        $this->post(route('forgot-password.post'), ['email' => 'NOUR@Example.TN'])
            ->assertRedirect(route('password.verify.otp'))
            ->assertSessionHas('password_reset_request_id');

        $otp = $this->sentOtp($user);
        $this->assertMatchesRegularExpression('/^\d{6}$/', $otp);

        $record = PasswordResetOtp::firstOrFail();
        $this->assertSame($user->id, $record->user_id);
        $this->assertSame('nour@example.tn', $record->email);
        $this->assertNotSame($otp, $record->otp_hash, 'Le code ne doit jamais être stocké en clair.');
        $this->assertTrue(Hash::check($otp, $record->otp_hash));
        $this->assertSame(0, $record->attempts);
        $this->assertSame(PasswordResetOtp::MAX_ATTEMPTS, $record->max_attempts);
        $this->assertSame($record->id, session('password_reset_request_id'));

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'action'  => 'password_reset_requested',
        ]);
    }

    public function test_fourth_request_within_a_minute_is_throttled(): void
    {
        Notification::fake();
        $user = $this->makeUser();

        for ($i = 0; $i < 3; $i++) {
            $this->post(route('forgot-password.post'), ['email' => $user->email])
                ->assertRedirect(route('password.verify.otp'));
        }

        $this->from(route('auth.forgot-password'))
            ->post(route('forgot-password.post'), ['email' => $user->email])
            ->assertRedirect(route('auth.forgot-password'))
            ->assertSessionHasErrors('email');

        $this->assertStringStartsWith('Trop de demandes. Veuillez réessayer dans', session('errors')->first('email'));
        Notification::assertSentToTimes($user, SendPasswordResetOtp::class, 3);
        $this->assertDatabaseCount('password_reset_otps', 3);
    }

    public function test_mail_failure_is_never_disclosed_to_the_visitor(): void
    {
        $user = $this->makeUser();

        // Panne SMTP simulée lors de l'envoi du code.
        Notification::shouldReceive('send')->once()->andThrow(new \RuntimeException('SMTP indisponible'));

        $this->from(route('auth.forgot-password'))
            ->post(route('forgot-password.post'), ['email' => $user->email])
            ->assertRedirect(route('auth.forgot-password'))
            ->assertSessionHas('status', self::GENERIC_STATUS)
            ->assertSessionMissing('password_reset_request_id')
            ->assertSessionHasNoErrors();

        $this->assertDatabaseMissing('audit_logs', ['action' => 'password_reset_requested']);
    }

    /* ────────────────────────────────────────────────────────────────────────
     |  Étape B — vérification du code
     |_______________________________________________________________________*/

    public function test_otp_form_requires_a_pending_request(): void
    {
        $this->get(route('password.verify.otp'))
            ->assertRedirect(route('auth.forgot-password'));

        $this->withSession(['password_reset_request_id' => 1])
            ->get(route('password.verify.otp'))
            ->assertOk()
            ->assertViewIs('auth.verify-otp');
    }

    public function test_valid_code_unlocks_the_reset_form(): void
    {
        Notification::fake();
        $user = $this->makeUser();

        $this->post(route('forgot-password.post'), ['email' => $user->email]);
        $otp = $this->sentOtp($user);

        $this->post(route('password.verify.otp.post'), ['otp' => $otp])
            ->assertRedirect(route('password.reset.form'))
            ->assertSessionMissing('password_reset_request_id')
            ->assertSessionHas('password_reset_token');

        $record = PasswordResetOtp::firstOrFail();
        $this->assertNotNull($record->verified_at);
        $this->assertTrue($record->hasValidResetToken());
        // La session ne contient que l'empreinte du jeton, identique à celle en base.
        $this->assertSame($record->reset_token_hash, session('password_reset_token'));

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'action'  => 'password_reset_otp_verified',
        ]);
    }

    public function test_reset_form_page_renders_for_a_verified_request(): void
    {
        $this->verifiedRequest();

        $this->get(route('password.reset.form'))
            ->assertOk()
            ->assertViewIs('auth.reset-password')
            ->assertSee(route('password.reset.post'), false);
    }

    public function test_code_must_have_six_characters(): void
    {
        [$record] = $this->pendingRequest();

        $this->withSession(['password_reset_request_id' => $record->id])
            ->from(route('password.verify.otp'))
            ->post(route('password.verify.otp.post'), ['otp' => '123'])
            ->assertRedirect(route('password.verify.otp'))
            ->assertSessionHasErrors('otp');

        $this->assertSame(0, $record->fresh()->attempts, 'Une saisie invalide ne consomme pas d\'essai.');
    }

    public function test_wrong_codes_count_down_then_invalidate_the_request(): void
    {
        [$record, $otp] = $this->pendingRequest();
        $wrong = $otp === '000000' ? '111111' : '000000';

        $this->withSession(['password_reset_request_id' => $record->id]);

        $attendus = [
            'Code incorrect. 4 essais restants.',
            'Code incorrect. 3 essais restants.',
            'Code incorrect. 2 essais restants.',
            'Code incorrect. 1 essai restant.',
        ];

        foreach ($attendus as $message) {
            $this->from(route('password.verify.otp'))
                ->post(route('password.verify.otp.post'), ['otp' => $wrong])
                ->assertRedirect(route('password.verify.otp'));

            $this->assertSame($message, session('errors')->first('otp'));
        }

        // Cinquième erreur : la demande est invalidée.
        $this->post(route('password.verify.otp.post'), ['otp' => $wrong])
            ->assertRedirect(route('auth.forgot-password'));
        $this->assertSame('Nombre d\'essais dépassé. Demandez un nouveau code.', session('errors')->first('otp'));

        $record->refresh();
        $this->assertSame(5, $record->attempts);
        $this->assertTrue($record->isExpired());
        $this->assertNull($record->verified_at);

        // Même le bon code n'est plus accepté ensuite.
        $this->post(route('password.verify.otp.post'), ['otp' => $otp])
            ->assertRedirect(route('auth.forgot-password'))
            ->assertSessionMissing('password_reset_token');
    }

    public function test_request_with_exhausted_attempts_is_refused_upfront(): void
    {
        [$record, $otp] = $this->pendingRequest();
        $record->forceFill(['attempts' => PasswordResetOtp::MAX_ATTEMPTS])->save();

        $this->withSession(['password_reset_request_id' => $record->id])
            ->post(route('password.verify.otp.post'), ['otp' => $otp])
            ->assertRedirect(route('auth.forgot-password'))
            ->assertSessionMissing('password_reset_token');

        $this->assertSame('Nombre d\'essais dépassé. Demandez un nouveau code.', session('errors')->first('otp'));
        $this->assertTrue($record->fresh()->isExpired());
    }

    public function test_expired_code_is_refused(): void
    {
        [$record, $otp] = $this->pendingRequest();

        // Le code n'est valable qu'une minute.
        $this->travel(PasswordResetOtp::OTP_TTL_MINUTES + 1)->minutes();

        $this->withSession(['password_reset_request_id' => $record->id])
            ->post(route('password.verify.otp.post'), ['otp' => $otp])
            ->assertRedirect(route('auth.forgot-password'))
            ->assertSessionMissing('password_reset_token');

        $this->assertSame('Le code a expiré. Demandez un nouveau code.', session('errors')->first('otp'));
        $this->assertNull($record->fresh()->verified_at);
    }

    public function test_missing_or_consumed_request_is_no_longer_valid(): void
    {
        // Aucune demande en session.
        $this->post(route('password.verify.otp.post'), ['otp' => '123456'])
            ->assertRedirect(route('auth.forgot-password'))
            ->assertSessionHasErrors('otp');

        // Demande déjà consommée par une réinitialisation précédente.
        [$record, $otp] = $this->pendingRequest();
        $record->markConsumed();

        $this->withSession(['password_reset_request_id' => $record->id])
            ->post(route('password.verify.otp.post'), ['otp' => $otp])
            ->assertRedirect(route('auth.forgot-password'))
            ->assertSessionMissing('password_reset_request_id');

        $this->assertSame('Cette demande n\'est plus valide. Refaites une demande.', session('errors')->first('otp'));
    }

    public function test_already_verified_request_goes_straight_to_the_reset_form(): void
    {
        [$record] = $this->pendingRequest();
        $record->mintResetToken();
        $hashAvant = $record->fresh()->reset_token_hash;

        $this->withSession(['password_reset_request_id' => $record->id])
            ->post(route('password.verify.otp.post'), ['otp' => '999999'])
            ->assertRedirect(route('password.reset.form'));

        // Idempotent : aucun nouveau jeton, aucun essai consommé.
        $record->refresh();
        $this->assertSame($hashAvant, $record->reset_token_hash);
        $this->assertSame(0, $record->attempts);
    }

    /* ────────────────────────────────────────────────────────────────────────
     |  Renvoi du code
     |_______________________________________________________________________*/

    public function test_resend_without_pending_request_returns_to_the_form(): void
    {
        Notification::fake();

        $this->post(route('password.resend.otp'))
            ->assertRedirect(route('auth.forgot-password'))
            ->assertSessionHas('status', self::GENERIC_STATUS);

        Notification::assertNothingSent();
    }

    public function test_resend_issues_a_fresh_code_and_invalidates_the_previous_one(): void
    {
        Notification::fake();
        $user = $this->makeUser();

        $this->post(route('forgot-password.post'), ['email' => $user->email]);
        $premier = PasswordResetOtp::firstOrFail();

        $this->withSession(['password_reset_token' => 'ancienne-preuve'])
            ->from(route('password.verify.otp'))
            ->post(route('password.resend.otp'))
            ->assertRedirect(route('password.verify.otp'))
            ->assertSessionHas('status', 'Un nouveau code a été envoyé.')
            ->assertSessionMissing('password_reset_token');

        $second = PasswordResetOtp::latest('id')->firstOrFail();
        $this->assertNotSame($premier->id, $second->id);
        $this->assertSame($second->id, session('password_reset_request_id'));
        $this->assertTrue($premier->fresh()->isExpired(), 'L\'ancien code doit être invalidé.');
        Notification::assertSentToTimes($user, SendPasswordResetOtp::class, 2);
    }

    public function test_fourth_resend_is_throttled(): void
    {
        Notification::fake();
        [$record] = $this->pendingRequest();
        $this->withSession(['password_reset_request_id' => $record->id]);

        for ($i = 0; $i < 3; $i++) {
            $this->from(route('password.verify.otp'))
                ->post(route('password.resend.otp'))
                ->assertSessionHas('status', 'Un nouveau code a été envoyé.');
        }

        $this->from(route('password.verify.otp'))
            ->post(route('password.resend.otp'))
            ->assertRedirect(route('password.verify.otp'))
            ->assertSessionHasErrors('otp');

        $this->assertStringStartsWith('Trop de renvois. Veuillez réessayer dans', session('errors')->first('otp'));
        $this->assertSame(3, RateLimiter::attempts('password-reset-resend:'.hash('sha256', $record->email)));
        Notification::assertSentToTimes($record->user, SendPasswordResetOtp::class, 3);
    }

    public function test_resend_is_refused_for_a_deactivated_account(): void
    {
        Notification::fake();
        [$record] = $this->pendingRequest();
        $record->user->forceFill(['is_active' => false])->save();

        $this->withSession(['password_reset_request_id' => $record->id])
            ->post(route('password.resend.otp'))
            ->assertRedirect(route('auth.forgot-password'));

        Notification::assertNothingSent();
        $this->assertDatabaseCount('password_reset_otps', 1);
    }

    public function test_resend_mail_failure_keeps_the_current_request(): void
    {
        [$record] = $this->pendingRequest();

        Notification::shouldReceive('send')->once()->andThrow(new \RuntimeException('SMTP indisponible'));

        $this->withSession(['password_reset_request_id' => $record->id])
            ->from(route('password.verify.otp'))
            ->post(route('password.resend.otp'))
            ->assertRedirect(route('password.verify.otp'))
            ->assertSessionHas('status', 'Un nouveau code a été envoyé.');

        // La panne n'est pas divulguée, et la session pointe toujours sur l'ancienne demande.
        $this->assertSame($record->id, session('password_reset_request_id'));
    }

    /* ────────────────────────────────────────────────────────────────────────
     |  Étape C — nouveau mot de passe
     |_______________________________________________________________________*/

    public function test_reset_form_requires_a_valid_proof(): void
    {
        $this->get(route('password.reset.form'))
            ->assertRedirect(route('auth.forgot-password'));

        $this->withSession(['password_reset_token' => hash('sha256', 'jeton-forge')])
            ->get(route('password.reset.form'))
            ->assertRedirect(route('auth.forgot-password'));

        // Preuve authentique mais expirée (30 minutes).
        [$record] = $this->pendingRequest();
        $raw = $record->mintResetToken();
        $this->travel(PasswordResetOtp::TOKEN_TTL_MINUTES + 1)->minutes();

        $this->withSession(['password_reset_token' => hash('sha256', $raw)])
            ->get(route('password.reset.form'))
            ->assertRedirect(route('auth.forgot-password'));
    }

    public function test_reset_without_proof_is_refused(): void
    {
        $this->post(route('password.reset.post'), [
            'password'              => self::NEW_PASSWORD,
            'password_confirmation' => self::NEW_PASSWORD,
        ])
            ->assertRedirect(route('auth.forgot-password'))
            ->assertSessionHasErrors('password');

        $this->assertSame(
            'Cette session de réinitialisation n\'est plus valide. Refaites une demande.',
            session('errors')->first('password'),
        );
    }

    public function test_new_password_must_follow_the_policy(): void
    {
        [$record, , $user] = $this->verifiedRequest();
        $ancienHash = $user->password;

        $cas = [
            'Court#1a'           => 'Le mot de passe doit contenir au moins 12 caractères.',
            'sansmajuscule#2026' => 'Le mot de passe doit contenir au moins une minuscule, une majuscule, un chiffre et un caractère spécial.',
            'SansSymbole2026ab'  => 'Le mot de passe doit contenir au moins une minuscule, une majuscule, un chiffre et un caractère spécial.',
        ];

        foreach ($cas as $password => $message) {
            $this->from(route('password.reset.form'))
                ->post(route('password.reset.post'), [
                    'password'              => $password,
                    'password_confirmation' => $password,
                ])
                ->assertRedirect(route('password.reset.form'));

            $this->assertSame($message, session('errors')->first('password'), "Mot de passe testé : {$password}");
        }

        // Confirmation différente.
        $this->from(route('password.reset.form'))
            ->post(route('password.reset.post'), [
                'password'              => self::NEW_PASSWORD,
                'password_confirmation' => 'Autre#Secret2026',
            ])
            ->assertSessionHasErrors('password');

        $this->assertSame($ancienHash, $user->fresh()->password);
        $this->assertNull($record->fresh()->consumed_at);
    }

    public function test_successful_reset_changes_the_password_and_revokes_everything(): void
    {
        [$record, , $user] = $this->verifiedRequest();

        // Une autre demande en attente, déjà vérifiée, pour la même adresse.
        $autre = PasswordResetOtp::create([
            'user_id'                => $user->id,
            'email'                  => $user->email,
            'otp_hash'               => Hash::make('123456'),
            'otp_expires_at'         => now()->addMinute(),
            'attempts'               => 0,
            'max_attempts'           => 5,
            'reset_token_hash'       => hash('sha256', 'autre-jeton'),
            'reset_token_expires_at' => now()->addMinutes(30),
        ]);

        // Sessions et jetons de rafraîchissement ouverts sur d'autres appareils.
        DB::table('sessions')->insert([
            'id'            => 'session-autre-appareil',
            'user_id'       => $user->id,
            'ip_address'    => '10.0.0.1',
            'user_agent'    => 'Navigateur',
            'payload'       => '',
            'last_activity' => now()->timestamp,
        ]);
        $refresh = JwtRefreshToken::create([
            'user_id'    => $user->id,
            'jti'        => 'jti-test-reset',
            'expires_at' => now()->addDay(),
            'revoked'    => false,
        ]);

        $this->post(route('password.reset.post'), [
            'password'              => self::NEW_PASSWORD,
            'password_confirmation' => self::NEW_PASSWORD,
        ])
            ->assertRedirect(route('auth.login'))
            ->assertSessionHas('success', 'Votre mot de passe a été réinitialisé. Connectez-vous avec votre nouveau mot de passe.')
            ->assertSessionMissing('password_reset_token');

        $this->assertTrue(Hash::check(self::NEW_PASSWORD, $user->fresh()->password));
        $this->assertGuest();

        $record->refresh();
        $this->assertNotNull($record->consumed_at);
        $this->assertNull($record->reset_token_hash);
        $this->assertSame('invalidated', $record->otp_hash);

        $autre->refresh();
        $this->assertNull($autre->reset_token_hash, 'Les autres preuves en attente doivent être révoquées.');
        $this->assertNull($autre->reset_token_expires_at);

        $this->assertDatabaseMissing('sessions', ['user_id' => $user->id]);
        $this->assertTrue($refresh->fresh()->revoked);
        $this->assertNotNull($refresh->fresh()->revoked_at);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'action'  => 'password_reset_completed',
        ]);
    }

    public function test_proof_cannot_be_reused_after_a_reset(): void
    {
        [, $raw] = $this->verifiedRequest();

        $this->post(route('password.reset.post'), [
            'password'              => self::NEW_PASSWORD,
            'password_confirmation' => self::NEW_PASSWORD,
        ])->assertRedirect(route('auth.login'));

        // Rejeu de la même preuve.
        $this->withSession(['password_reset_token' => hash('sha256', $raw)])
            ->post(route('password.reset.post'), [
                'password'              => 'Encore#Autre2026',
                'password_confirmation' => 'Encore#Autre2026',
            ])
            ->assertRedirect(route('auth.forgot-password'))
            ->assertSessionHasErrors('password');
    }

    public function test_reset_is_refused_for_an_account_deactivated_meanwhile(): void
    {
        [$record, , $user] = $this->verifiedRequest();
        $user->forceFill(['is_active' => false])->save();
        $ancienHash = $user->fresh()->password;

        $this->post(route('password.reset.post'), [
            'password'              => self::NEW_PASSWORD,
            'password_confirmation' => self::NEW_PASSWORD,
        ])
            ->assertRedirect(route('auth.forgot-password'));

        $this->assertSame('Ce compte a été désactivé.', session('errors')->first('password'));
        $this->assertSame($ancienHash, $user->fresh()->password);
        $this->assertNull($record->fresh()->consumed_at);
    }

    public function test_reset_is_refused_when_the_account_no_longer_exists(): void
    {
        [$record, , $user] = $this->verifiedRequest();
        $user->delete(); // user_id passe à NULL (nullOnDelete)

        $this->post(route('password.reset.post'), [
            'password'              => self::NEW_PASSWORD,
            'password_confirmation' => self::NEW_PASSWORD,
        ])
            ->assertRedirect(route('auth.forgot-password'));

        $this->assertSame('Compte introuvable.', session('errors')->first('password'));
        $this->assertNull($record->fresh()->consumed_at);
    }

    /* ────────────────────────────────────────────────────────────────────────
     |  Helpers
     |_______________________________________________________________________*/

    private function makeUser(?string $email = null, bool $isActive = true): User
    {
        return User::factory()->create([
            'email'     => $email ?? uniqid('reset.', true).'@test.local',
            'password'  => 'Ancien#Secret2026',
            'is_active' => $isActive,
        ]);
    }

    /**
     * Code envoyé par la dernière notification adressée à l'utilisateur.
     */
    private function sentOtp(User $user): string
    {
        $otp = null;

        Notification::assertSentTo($user, SendPasswordResetOtp::class, function (SendPasswordResetOtp $n) use (&$otp) {
            $otp = $n->otp;

            return true;
        });

        return (string) $otp;
    }

    /**
     * Demande en attente créée directement par le modèle.
     *
     * @return array{0: PasswordResetOtp, 1: string, 2: User}
     */
    private function pendingRequest(): array
    {
        $user = $this->makeUser();
        [$record, $otp] = PasswordResetOtp::issue($user, '127.0.0.1', 'PHPUnit');

        return [$record, $otp, $user];
    }

    /**
     * Demande déjà vérifiée : la preuve (empreinte du jeton) est en session.
     *
     * @return array{0: PasswordResetOtp, 1: string, 2: User}
     */
    private function verifiedRequest(): array
    {
        [$record, , $user] = $this->pendingRequest();
        $raw = $record->mintResetToken();

        $this->withSession(['password_reset_token' => hash('sha256', $raw)]);

        return [$record, $raw, $user];
    }
}
