<?php

namespace Tests\Unit;

use App\Models\PasswordResetOtp;
use App\Models\User;
use App\Notifications\SendPasswordResetOtp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Invariants de sécurité du modèle PasswordResetOtp : codes et jetons
 * stockés uniquement hachés, expiration, nombre d'essais limité, jeton à
 * usage unique.
 */
class PasswordResetOtpTest extends TestCase
{
    use RefreshDatabase;

    public function test_generated_otp_is_always_six_digits(): void
    {
        for ($i = 0; $i < 50; $i++) {
            $this->assertMatchesRegularExpression('/^\d{6}$/', PasswordResetOtp::generateOtp());
        }
    }

    public function test_issue_stores_a_hashed_code_with_its_metadata(): void
    {
        $user = User::factory()->create();

        [$request, $otp] = PasswordResetOtp::issue($user, '10.0.0.7', 'Mozilla/5.0 PHPUnit');

        $this->assertTrue($request->exists);
        $this->assertSame($user->email, $request->email);
        $this->assertTrue(Hash::check($otp, $request->otp_hash));
        $this->assertNotSame($otp, $request->otp_hash);
        $this->assertSame('10.0.0.7', $request->ip_address);
        $this->assertSame('Mozilla/5.0 PHPUnit', $request->user_agent);
        $this->assertTrue($request->otp_expires_at->isFuture());
        $this->assertTrue($request->otp_expires_at->lte(now()->addMinutes(PasswordResetOtp::OTP_TTL_MINUTES)));
        $this->assertFalse($request->isExpired());
        $this->assertFalse($request->isVerified());
        $this->assertFalse($request->isConsumed());
        $this->assertTrue($request->user->is($user));

        [$sansAgent] = PasswordResetOtp::issue($user);
        $this->assertNull($sansAgent->fresh()->user_agent);
        $this->assertNull($sansAgent->fresh()->ip_address);
    }

    public function test_issue_accepts_a_very_long_user_agent(): void
    {
        $user = User::factory()->create();

        [$request] = PasswordResetOtp::issue($user, '10.0.0.7', str_repeat('A', 300));

        $this->assertTrue($request->exists);
        $this->assertLessThanOrEqual(191, strlen($request->fresh()->user_agent));
    }

    public function test_issue_invalidates_previous_outstanding_requests_only(): void
    {
        $user = User::factory()->create();

        [$consommee] = PasswordResetOtp::issue($user);
        $consommee->markConsumed();
        $consommeeAvant = $consommee->fresh()->otp_expires_at;

        [$ancienne] = PasswordResetOtp::issue($user);
        $ancienne->mintResetToken();

        [$nouvelle] = PasswordResetOtp::issue($user);

        $ancienne->refresh();
        $this->assertTrue($ancienne->isExpired());
        $this->assertNull($ancienne->reset_token_hash);
        $this->assertFalse($ancienne->hasValidResetToken());

        // Une demande déjà consommée n'est pas retouchée.
        $this->assertEquals($consommeeAvant, $consommee->fresh()->otp_expires_at);

        $this->assertFalse($nouvelle->fresh()->isExpired());
    }

    public function test_find_active_returns_the_latest_unconsumed_request(): void
    {
        $user = User::factory()->create(['email' => 'actif@test.local']);

        [$premiere] = PasswordResetOtp::issue($user);
        [$seconde] = PasswordResetOtp::issue($user);

        // L'adresse est normalisée avant la recherche.
        $this->assertTrue(PasswordResetOtp::findActive('  ACTIF@test.local ')->is($seconde));

        $seconde->markConsumed();
        $this->assertTrue(PasswordResetOtp::findActive('actif@test.local')->is($premiere));

        $premiere->markConsumed();
        $this->assertNull(PasswordResetOtp::findActive('actif@test.local'));
        $this->assertNull(PasswordResetOtp::findActive('inconnu@test.local'));
    }

    public function test_verify_otp_counts_every_attempt(): void
    {
        [$request, $otp] = PasswordResetOtp::issue(User::factory()->create());

        $this->assertFalse($request->verifyOtp($otp === '000000' ? '111111' : '000000'));
        $this->assertSame(1, $request->fresh()->attempts);

        $this->assertTrue($request->verifyOtp($otp));
        $this->assertSame(2, $request->fresh()->attempts);
    }

    public function test_verify_otp_refuses_without_counting_once_blocked(): void
    {
        $user = User::factory()->create();

        // Essais épuisés.
        [$epuisee, $otp] = PasswordResetOtp::issue($user);
        $epuisee->forceFill(['attempts' => PasswordResetOtp::MAX_ATTEMPTS])->save();
        $this->assertTrue($epuisee->hasExhaustedAttempts());
        $this->assertFalse($epuisee->verifyOtp($otp));
        $this->assertSame(PasswordResetOtp::MAX_ATTEMPTS, $epuisee->fresh()->attempts);

        // Code expiré.
        [$expiree, $otp] = PasswordResetOtp::issue($user);
        $this->travel(PasswordResetOtp::OTP_TTL_MINUTES + 1)->minutes();
        $this->assertTrue($expiree->isExpired());
        $this->assertFalse($expiree->verifyOtp($otp));
        $this->assertSame(0, $expiree->fresh()->attempts);
        $this->travelBack();

        // Demande consommée.
        [$consommee, $otp] = PasswordResetOtp::issue($user);
        $consommee->markConsumed();
        $this->assertFalse($consommee->verifyOtp($otp));
        $this->assertSame(0, $consommee->fresh()->attempts);

        // Date d'expiration absente : considérée comme expirée.
        $this->assertTrue((new PasswordResetOtp(['otp_expires_at' => null]))->isExpired());
    }

    public function test_reset_token_is_hashed_short_lived_and_single_use(): void
    {
        [$request] = PasswordResetOtp::issue(User::factory()->create());
        $this->assertFalse($request->hasValidResetToken());

        $raw = $request->mintResetToken();

        $request->refresh();
        $this->assertSame(64, strlen($raw));
        $this->assertSame(hash('sha256', $raw), $request->reset_token_hash);
        $this->assertTrue($request->isVerified());
        $this->assertTrue($request->hasValidResetToken());

        // Au-delà de 30 minutes, la preuve n'est plus valable.
        $this->travel(PasswordResetOtp::TOKEN_TTL_MINUTES + 1)->minutes();
        $this->assertFalse($request->hasValidResetToken());
        $this->travelBack();

        $request->markConsumed();
        $request->refresh();
        $this->assertTrue($request->isConsumed());
        $this->assertNull($request->reset_token_hash);
        $this->assertNull($request->reset_token_expires_at);
        $this->assertSame('invalidated', $request->otp_hash);
        $this->assertFalse($request->hasValidResetToken());
    }

    public function test_invalidate_expires_the_code_and_drops_the_token(): void
    {
        [$request] = PasswordResetOtp::issue(User::factory()->create());
        $request->mintResetToken();

        $request->invalidate();
        $request->refresh();

        $this->assertTrue($request->isExpired());
        $this->assertFalse($request->hasValidResetToken());
        $this->assertFalse($request->isConsumed());
    }

    public function test_notification_mails_the_code_without_storing_it(): void
    {
        $user = User::factory()->create(['name' => 'Nour']);
        [$request, $otp] = PasswordResetOtp::issue($user);

        $notification = new SendPasswordResetOtp($otp, $request);

        $this->assertSame(['mail'], $notification->via($user));

        $mail = $notification->toMail($user);
        $this->assertSame('AquaSecure — Votre code de réinitialisation', $mail->subject);
        $this->assertSame('Bonjour Nour,', $mail->greeting);
        $this->assertContains('**'.$otp.'**', $mail->introLines);
        $this->assertContains('Ce code est valable pendant '.PasswordResetOtp::OTP_TTL_MINUTES.' minutes.', $mail->introLines);

        // La représentation tableau ne contient jamais le code en clair.
        $tableau = $notification->toArray($user);
        $this->assertSame($request->id, $tableau['request_id']);
        $this->assertSame($request->otp_expires_at->toIso8601String(), $tableau['expires_at']);
        $this->assertNotContains($otp, $tableau);
    }
}
