<?php

namespace App\Observers;

use App\Models\Infrastructure;
use App\Models\Intervention;

class InterventionObserver
{
    /**
     * Dès qu'une intervention est enregistrée (création ou modification),
     * si elle est rattachée à une infrastructure et marquée comme 'Terminée',
     * on synchronise la date de dernière maintenance de l'équipement si celle-ci est plus récente.
     */
    public function saved(Intervention $intervention): void
    {
        if ($intervention->infrastructure_id && $intervention->statut === 'Terminée') {
            $infrastructure = Infrastructure::find($intervention->infrastructure_id);

            if ($infrastructure && $intervention->date) {
                if (! $infrastructure->date_derniere_maintenance || $intervention->date->gt($infrastructure->date_derniere_maintenance)) {
                    $infrastructure->date_derniere_maintenance = $intervention->date;
                    $infrastructure->saveQuietly();
                }
            }
        }
    }
}
