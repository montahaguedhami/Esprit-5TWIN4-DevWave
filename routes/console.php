<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Infrastructures : alertes de maintenance puis prédiction du risque de panne.
Schedule::command('infrastructures:check-maintenance')->dailyAt('06:00');
Schedule::command('infrastructures:predict-risk')->dailyAt('06:05')->withoutOverlapping();

// Maintenance préventive : crée chaque matin les interventions des équipements arrivant à échéance.
Schedule::command('maintenance:planifier-preventive')->dailyAt('06:15')->withoutOverlapping(10);

// Run the simulation fill nightly to keep dashboards populated when no real data arrives.
// The simulation provider only generates data for points stale for > 30 days.
// Adjust frequency as needed; 'daily' is safe because the provider is idempotent.
Schedule::command('water:sync --provider=simulation')->daily()->withoutOverlapping(10);

// Run external providers (data.gov.tn, etc.) weekly — reduce API load.
// Comment out if DATA_GOV_TN_DATASET_ID is not configured.
Schedule::command('water:sync --provider=data_gov_tn --no-simulation')->weekly()->withoutOverlapping(30);
