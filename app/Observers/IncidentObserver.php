<?php

namespace App\Observers;

use App\Models\Incident;
use App\Models\Infrastructure;
use App\Services\FailureRiskService;

class IncidentObserver
{
    public function updating(Incident $incident): void
    {
        if ($incident->isDirty('infrastructure_id')) {
            $oldInfrastructureId = $incident->getOriginal('infrastructure_id');
            $incident->ancienneInfrastructureId = $oldInfrastructureId === null ? null : (int) $oldInfrastructureId;
        }
    }

    public function saved(Incident $incident): void
    {
        $this->recomputeRisk(
            app(FailureRiskService::class),
            $incident->infrastructure_id === null ? null : (int) $incident->infrastructure_id,
            $incident->ancienneInfrastructureId,
        );
        $incident->ancienneInfrastructureId = null;
    }

    public function deleted(Incident $incident): void
    {
        $this->recomputeRisk(
            app(FailureRiskService::class),
            $incident->infrastructure_id === null ? null : (int) $incident->infrastructure_id,
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
