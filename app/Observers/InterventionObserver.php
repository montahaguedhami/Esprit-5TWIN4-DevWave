<?php

namespace App\Observers;

use App\Models\Infrastructure;
use App\Models\Intervention;
use App\Services\FailureRiskService;

class InterventionObserver
{
    public function updating(Intervention $intervention): void
    {
        if ($intervention->isDirty('infrastructure_id')) {
            $oldInfrastructureId = $intervention->getOriginal('infrastructure_id');
            $intervention->ancienneInfrastructureId = $oldInfrastructureId === null ? null : (int) $oldInfrastructureId;
        }
    }

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

        $this->recomputeRisk(
            app(FailureRiskService::class),
            $intervention->infrastructure_id === null ? null : (int) $intervention->infrastructure_id,
            $intervention->ancienneInfrastructureId,
        );
        $intervention->ancienneInfrastructureId = null;
    }

    public function deleted(Intervention $intervention): void
    {
        $this->recomputeRisk(
            app(FailureRiskService::class),
            $intervention->infrastructure_id === null ? null : (int) $intervention->infrastructure_id,
        );
    }

    private function recomputeRisk(FailureRiskService $riskService, ?int ...$infrastructureIds): void
    {
        foreach (array_unique(array_filter($infrastructureIds)) as $infrastructureId) {
            $infrastructure = Infrastructure::find($infrastructureId);
            if ($infrastructure) {
                $riskService->recompute($infrastructure);
            }
        }
    }
}
