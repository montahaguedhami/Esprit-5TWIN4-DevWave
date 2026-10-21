<?php

namespace Tests\Feature;

use App\Models\PasswordResetOtp;
use App\Models\Role;
use App\Models\User;
use App\Notifications\SendPasswordResetOtp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Commandes de diagnostic de l'authentification :
 *   - auth:diagnostic {email}   (DiagnosticAuth)
 *   - login:diagnostic {email?} (DiagnosticLogin)
 */
class AuthDiagnosticCommandsTest extends TestCase
{
    use RefreshDatabase;

    /* ────────────────────────────────────────────────────────────────────────
     |  auth:diagnostic
     |_______________________________________________________________________*/

    public function test_auth_diagnostic_reports_an_unknown_account(): void
    {
        Notification::fake();

        $this->artisan('auth:diagnostic', ['email' => 'fantome@test.local'])
            ->expectsOutputToContain("Utilisateur 'fantome@test.local' introuvable en base")
            ->doesntExpectOutputToContain('TEST ENVOI OTP')
            ->assertExitCode(0);

        Notification::assertNothingSent();
        $this->assertDatabaseCount('password_reset_otps', 0);
    }

    public function test_auth_diagnostic_on_a_healthy_account(): void
    {
        Notification::fake();
        config()->set('mail.default', 'array');
        $this->artisan('db:seed', ['--class' => 'RbacSeeder']);

        $user = User::factory()->create([
            'name'      => 'Compte Sain',
            'email'     => 'sain@test.local',
            'password'  => 'Test1234!',
            'is_active' => true,
        ]);
        $user->assignRole(Role::CITOYEN);

        $this->artisan('auth:diagnostic', ['email' => 'sain@test.local'])
            ->expectsOutputToContain('Utilisateur trouvé : Compte Sain')
            ->expectsOutputToContain('is_active      = ✅ actif')
            ->expectsOutputToContain('email_verified = ✅ vérifié')
            ->expectsOutputToContain('rôles          = citoyen')
            ->expectsOutputToContain("Password trouvé : 'Test1234!'")
            ->expectsOutputToContain('Email envoyé sans erreur')
            ->expectsOutputToContain('Vérifier la boîte mail : sain@test.local')
            ->expectsOutputToContain('OTP généré')
            ->expectsOutputToContain('Notification OTP envoyée sans erreur')
            ->expectsOutputToContain('(OTP de test supprimé)')
            ->expectsOutputToContain('Tout semble correct')
            ->assertExitCode(0);

        // L'email de test est réellement passé par le mailer (transport array).
        $messages = app('mailer')->getSymfonyTransport()->messages();
        $this->assertCount(1, $messages);
        $this->assertSame('Test Diagnostic AquaSecure', $messages[0]->getOriginalMessage()->getSubject());

        Notification::assertSentTo($user, SendPasswordResetOtp::class);
        // L'OTP de test ne reste pas en base.
        $this->assertDatabaseCount('password_reset_otps', 0);
    }

    public function test_auth_diagnostic_lists_every_problem_found(): void
    {
        Notification::fake();
        config()->set('mail.default', 'log');
        config()->set('mail.mailers.log.channel', 'null');

        User::factory()->unverified()->create([
            'email'     => 'probleme@test.local',
            'password'  => 'Inconnu#Secret2026',
            'is_active' => false,
        ]);

        $this->artisan('auth:diagnostic', ['email' => 'probleme@test.local'])
            ->expectsOutputToContain('is_active      = ❌ inactif')
            ->expectsOutputToContain('email_verified = ⚠️  non vérifié')
            ->expectsOutputToContain('AUCUN RÔLE')
            ->expectsOutputToContain('Password non trouvé dans la liste de test')
            ->expectsOutputToContain('Vérifier dans storage/logs/laravel.log')
            ->expectsOutputToContain('Code visible dans storage/logs/laravel.log')
            ->expectsOutputToContain('❌ Compte désactivé')
            ->expectsOutputToContain('❌ Aucun rôle assigné')
            ->expectsOutputToContain('Email non vérifié')
            ->expectsOutputToContain('Mode LOG')
            ->doesntExpectOutputToContain('Tout semble correct')
            ->assertExitCode(0);
    }

    public function test_auth_diagnostic_reports_mail_and_otp_failures(): void
    {
        $this->artisan('db:seed', ['--class' => 'RbacSeeder']);

        Mail::shouldReceive('raw')->once()->andThrow(new \Exception('Connexion SMTP refusée'));
        Notification::shouldReceive('send')->once()->andThrow(new \Exception('Canal mail indisponible'));

        $this->artisan('auth:diagnostic', ['email' => 'citoyen@aquasecure.tn'])
            ->expectsOutputToContain('ERREUR email : Connexion SMTP refusée')
            ->expectsOutputToContain('ERREUR OTP : Canal mail indisponible')
            ->doesntExpectOutputToContain('Email envoyé sans erreur')
            ->assertExitCode(0);
    }

    /* ────────────────────────────────────────────────────────────────────────
     |  login:diagnostic
     |_______________________________________________________________________*/

    public function test_login_diagnostic_without_any_citizen(): void
    {
        $this->artisan('login:diagnostic')
            ->expectsOutputToContain('Aucun citoyen trouvé en DB')
            ->assertExitCode(0);

        $this->artisan('login:diagnostic', ['email' => 'inconnu@test.local'])
            ->expectsOutputToContain('Aucun citoyen trouvé pour inconnu@test.local')
            ->assertExitCode(0);
    }

    public function test_login_diagnostic_on_a_citizen_session(): void
    {
        $this->artisan('db:seed', ['--class' => 'RbacSeeder']);
        $citoyen = User::where('email', 'citoyen@aquasecure.tn')->firstOrFail();

        $this->artisan('login:diagnostic')
            ->expectsOutputToContain('Email     : citoyen@aquasecure.tn')
            ->expectsOutputToContain('Rôles     : citoyen')
            ->expectsOutputToContain('sessionMatches(CITOYEN)  : OUI')
            ->expectsOutputToContain('Auth::check()            : OUI')
            ->expectsOutputToContain('citizen.projets.index')
            ->expectsOutputToContain('Tout semble correct')
            ->assertExitCode(0);

        // La commande simule réellement la session et la connexion.
        $this->assertSame(Role::CITOYEN, session('user.role'));
        $this->assertSame($citoyen->id, Auth::id());
    }

    public function test_login_diagnostic_flags_a_non_citizen_account(): void
    {
        $this->artisan('db:seed', ['--class' => 'RbacSeeder']);

        $this->artisan('login:diagnostic', ['email' => 'gestionnaire@aquasecure.tn'])
            ->expectsOutputToContain('Primary   : '.Role::MANAGER)
            ->expectsOutputToContain('sessionMatches(CITOYEN)  : NON')
            ->expectsOutputToContain('Problème détecté')
            ->expectsOutputToContain('sessionMatches(CITOYEN) retourne false')
            ->doesntExpectOutputToContain('Tout semble correct')
            ->assertExitCode(0);
    }
}
