<?php

namespace App\Observers;

use App\Enums\InfrastructureEtat;
use App\Models\Infrastructure;
use App\Models\InfrastructureHistorique;
use Illuminate\Support\Facades\Cache;

class InfrastructureObserver
{
    /**
     * Conserve temporairement l'ancien état avant synchronisation.
     */
    public function updating(Infrastructure $infrastructure): void
    {
        if ($infrastructure->isDirty('etat')) {
            $infrastructure->ancienEtatAvantSauvegarde = $infrastructure->getOriginal('etat');
        }
    }

    public function created(Infrastructure $infrastructure): void
    {
        $nouvelEtat = $infrastructure->etat instanceof InfrastructureEtat
            ? $infrastructure->etat->value
            : (string) $infrastructure->etat;

        InfrastructureHistorique::create([
            'infrastructure_id' => $infrastructure->id,
            'ancien_etat' => null,
            'nouvel_etat' => $nouvelEtat,
            'commentaire' => $infrastructure->etat_changement_commentaire ?: 'Création initiale',
            'user_name' => session('user.name', 'Système'),
            'created_at' => now(),
        ]);

        Cache::forget('zone_stats_globales');
    }

    public function updated(Infrastructure $infrastructure): void
    {
        if ($infrastructure->wasChanged('etat')) {
            $ancienEtatRaw = $infrastructure->ancienEtatAvantSauvegarde;

            $ancienEtat = $ancienEtatRaw instanceof InfrastructureEtat
                ? $ancienEtatRaw->value
                : ($ancienEtatRaw ? (string) $ancienEtatRaw : null);

            $nouvelEtat = $infrastructure->etat instanceof InfrastructureEtat
                ? $infrastructure->etat->value
                : (string) $infrastructure->etat;

            InfrastructureHistorique::create([
                'infrastructure_id' => $infrastructure->id,
                'ancien_etat' => $ancienEtat,
                'nouvel_etat' => $nouvelEtat,
                'commentaire' => $infrastructure->etat_changement_commentaire,
                'user_name' => session('user.name', 'Système'),
                'created_at' => now(),
            ]);
        }

        Cache::forget('zone_stats_globales');
    }

    public function deleted(Infrastructure $infrastructure): void
    {
        Cache::forget('zone_stats_globales');
    }

    public function restored(Infrastructure $infrastructure): void
    {
        Cache::forget('zone_stats_globales');
    }
}
