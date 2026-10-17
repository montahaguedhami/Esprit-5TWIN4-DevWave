<?php

namespace App\Console\Commands;

use App\Models\PasswordResetOtp;
use Illuminate\Console\Command;

class GetLastOtp extends Command
{
    protected $signature = 'otp:get {email}';
    protected $description = 'Récupérer le dernier OTP actif pour un email (pour les tests)';

    public function handle(): void
    {
        $email = $this->argument('email');

        $record = PasswordResetOtp::where('email', $email)
            ->whereNull('consumed_at')
            ->whereNull('verified_at')
            ->where('otp_expires_at', '>', now())
            ->latest()
            ->first();

        if (! $record) {
            $this->error("Aucun OTP actif trouvé pour : {$email}");
            $this->line('→ Fais une demande sur /forgot-password d\'abord');
            return;
        }

        $this->newLine();
        $this->warn('⚠️  COMMANDE DE TEST UNIQUEMENT — NE PAS UTILISER EN PRODUCTION');
        $this->newLine();
        $this->info('📧 OTP pour : ' . $email);
        $this->line('  Request ID : ' . $record->id);
        $this->line('  Expire dans : ' . now()->diffInMinutes($record->otp_expires_at) . ' minutes');
        $this->line('  Tentatives : ' . $record->attempts . '/' . $record->max_attempts);
        $this->newLine();
        $this->warn('  Le code OTP haché est en DB (non récupérable en clair).');
        $this->line('  → Consulte storage/logs/laravel.log pour voir le code en clair.');
        $this->line('  → Cherche "code de vérification" ou le numéro à 6 chiffres.');
        $this->newLine();
        $this->line('Commande PowerShell pour extraire le code :');
        $this->info('  Select-String -Path storage/logs/laravel.log -Pattern "\*\*[0-9]{6}\*\*" | Select-Object -Last 3');
        $this->newLine();
    }
}
