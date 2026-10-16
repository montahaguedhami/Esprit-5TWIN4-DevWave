
<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Foundation\Inspiring;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Vérifier quotidiennement la maintenance des infrastructures.
Schedule::command('infrastructures:check-maintenance')
    ->dailyAt('06:00');

// Synchronisation quotidienne des données simulées.
// Le fournisseur doit éviter de régénérer les données récentes.
Schedule::command('water:sync --provider=simulation')
    ->daily()
    ->withoutOverlapping(10);

// Synchronisation hebdomadaire des données externes.
// Nécessite une configuration valide du fournisseur externe.
Schedule::command('water:sync --provider=data_gov_tn --no-simulation')
    ->weekly()
    ->withoutOverlapping(30);
