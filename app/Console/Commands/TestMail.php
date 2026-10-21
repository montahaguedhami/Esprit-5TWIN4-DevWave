<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use App\Models\User;

class TestMail extends Command
{
    protected $signature = 'mail:test {email=abdelganibanouri@gmail.com}';
    protected $description = 'Tester l\'envoi d\'email';

    public function handle(): void
    {
        $email = $this->argument('email');

        $this->info('Configuration mail actuelle:');
        $this->line('  MAILER  = ' . config('mail.default'));
        $this->line('  HOST    = ' . config('mail.mailers.smtp.host'));
        $this->line('  FROM    = ' . config('mail.from.address'));
        $this->newLine();

        $this->info("Envoi d'un email de test à : {$email}");

        try {
            Mail::raw('Ceci est un email de test AquaSecure. Si tu reçois ceci, l\'envoi fonctionne !', function ($message) use ($email) {
                $message->to($email)
                        ->subject('✅ Test Email AquaSecure - ' . now()->format('H:i:s'));
            });

            $this->info('✅ Email envoyé sans erreur !');
            $this->newLine();

            if (config('mail.default') === 'log') {
                $this->warn('📋 Mode LOG : L\'email est dans storage/logs/laravel.log');
                $this->line('   Cherche "Test Email AquaSecure" dans le fichier de logs.');
            } else {
                $this->info('📧 Mode SMTP : Vérifie ta boîte email ' . $email);
            }

        } catch (\Exception $e) {
            $this->error('❌ ERREUR : ' . $e->getMessage());
        }
    }
}
