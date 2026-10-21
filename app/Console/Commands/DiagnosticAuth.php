<?php

namespace App\Console\Commands;

use App\Models\PasswordResetOtp;
use App\Models\User;
use App\Notifications\SendPasswordResetOtp;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

class DiagnosticAuth extends Command
{
    protected $signature = 'auth:diagnostic {email}';
    protected $description = 'Diagnostiquer les problèmes auth pour un email';

    public function handle(): void
    {
        $email = $this->argument('email');

        $this->newLine();
        $this->line('════════════════════════════════════════');
        $this->info("  DIAGNOSTIC AUTH : {$email}");
        $this->line('════════════════════════════════════════');

        // ── 1. Config mail ────────────────────────────
        $this->newLine();
        $this->warn('📧 CONFIG MAIL :');
        $this->line('  MAILER     = ' . (config('mail.default') ?: '❌ VIDE'));
        $this->line('  HOST       = ' . (config('mail.mailers.smtp.host') ?: '❌ VIDE'));
        $this->line('  FROM       = ' . (config('mail.from.address') ?: '❌ VIDE'));
        $this->line('  ENCRYPTION = ' . (config('mail.mailers.smtp.encryption') ?: '❌ VIDE'));

        // ── 2. User en DB ─────────────────────────────
        $this->newLine();
        $this->warn('👤 UTILISATEUR EN DB :');
        $user = User::where('email', $email)->first();

        if (! $user) {
            $this->error("  ❌ Utilisateur '{$email}' introuvable en base !");
            $this->line('  → Fais d\'abord une inscription sur /register');
            $this->newLine();
            return;
        }

        $this->info('  ✅ Utilisateur trouvé : ' . $user->name);
        $this->line('  is_active      = ' . ($user->is_active ? '✅ actif' : '❌ inactif'));
        $this->line('  email_verified = ' . ($user->email_verified_at ? '✅ vérifié' : '⚠️  non vérifié'));
        $this->line('  password_hash  = ' . substr($user->password, 0, 15) . '...');

        $roles = $user->roles->pluck('slug')->join(', ');
        $this->line('  rôles          = ' . ($roles ?: '❌ AUCUN RÔLE !'));

        // ── 3. Test password ──────────────────────────
        $this->newLine();
        $this->warn('🔑 TEST MOT DE PASSE :');
        $testPasswords = ['SecurePass123!', 'Test1234!', 'NewPass123!', 'SecurePass456!'];
        $found = false;
        foreach ($testPasswords as $pwd) {
            if (Hash::check($pwd, $user->password)) {
                $this->info("  ✅ Password trouvé : '{$pwd}'");
                $found = true;
                break;
            }
        }
        if (! $found) {
            $this->warn('  ⚠️  Password non trouvé dans la liste de test');
            $this->line('  → Utilise le password que tu as saisi lors de l\'inscription');
        }

        // ── 4. Test envoi email ───────────────────────
        $this->newLine();
        $this->warn('📨 TEST ENVOI EMAIL :');
        try {
            Mail::raw("Test diagnostic AquaSecure pour {$email}", function ($m) use ($email) {
                $m->to($email)->subject('Test Diagnostic AquaSecure');
            });
            $this->info('  ✅ Email envoyé sans erreur');
            if (config('mail.default') === 'log') {
                $this->line('  → Vérifier dans storage/logs/laravel.log');
            } else {
                $this->line("  → Vérifier la boîte mail : {$email}");
            }
        } catch (\Exception $e) {
            $this->error('  ❌ ERREUR email : ' . $e->getMessage());
        }

        // ── 5. Test envoi OTP ─────────────────────────
        $this->newLine();
        $this->warn('🔐 TEST ENVOI OTP :');
        try {
            [$record, $otp] = PasswordResetOtp::issue($user, '127.0.0.1', 'diagnostic');
            $this->info("  ✅ OTP généré : {$otp}");
            $this->line('  expires_at : ' . $record->otp_expires_at);

            Notification::send($user, new SendPasswordResetOtp($otp, $record));
            $this->info('  ✅ Notification OTP envoyée sans erreur');

            if (config('mail.default') === 'log') {
                $this->line('  → Code visible dans storage/logs/laravel.log');
                $this->line("  → Cherche : **{$otp}**");
            }

            // Nettoyer l'OTP de test
            $record->delete();
            $this->line('  (OTP de test supprimé)');

        } catch (\Exception $e) {
            $this->error('  ❌ ERREUR OTP : ' . $e->getMessage());
        }

        // ── 6. Résumé ─────────────────────────────────
        $this->newLine();
        $this->line('════════════════════════════════════════');
        $this->info('  RÉSUMÉ DIAGNOSTIC');
        $this->line('════════════════════════════════════════');

        $issues = [];

        if (! $user->is_active)
            $issues[] = '❌ Compte désactivé';
        if (! $roles)
            $issues[] = '❌ Aucun rôle assigné';
        if (! $user->email_verified_at)
            $issues[] = '⚠️  Email non vérifié (normal avant 1er login)';
        if (config('mail.default') === 'log')
            $issues[] = '📋 Mode LOG : emails dans laravel.log (pas en boîte réelle)';

        if (empty($issues)) {
            $this->info('  ✅ Tout semble correct !');
        } else {
            foreach ($issues as $i) {
                $this->line("  {$i}");
            }
        }
        $this->newLine();
    }
}
