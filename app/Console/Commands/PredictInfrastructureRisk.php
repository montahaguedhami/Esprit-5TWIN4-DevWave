<?php

namespace App\Console\Commands;

use App\Models\Infrastructure;
use App\Services\FailureRiskService;
use Illuminate\Console\Command;

class PredictInfrastructureRisk extends Command
{
    protected $signature = 'infrastructures:predict-risk {--zone= : Limiter le calcul à une zone}';

    protected $description = 'Calcule et historise le risque de panne estimé des infrastructures.';

    public function handle(FailureRiskService $riskService): int
    {
        $zoneId = $this->option('zone');
        if ($zoneId !== null && (! ctype_digit((string) $zoneId) || (int) $zoneId < 1)) {
            $this->error("L'option --zone doit être un identifiant de zone positif.");

            return self::FAILURE;
        }

        $computedAt = now()->startOfDay();
        $processed = 0;

        Infrastructure::query()
            ->when($zoneId !== null, fn ($query) => $query->where('zone_id', (int) $zoneId))
            ->chunkById(100, function ($infrastructures) use ($riskService, $computedAt, &$processed): void {
                foreach ($infrastructures as $infrastructure) {
                    $riskService->recompute($infrastructure, $computedAt);
                    $processed++;
                }
            });

        $this->info("Risque recalculé pour {$processed} infrastructure(s).");

        return self::SUCCESS;
    }
}
