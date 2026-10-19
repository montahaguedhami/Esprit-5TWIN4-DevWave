<?php

namespace App\Console\Commands;

use App\Models\MesureQualite;
use App\Models\PointMesure;
use App\Services\WaterQualityService;
use Illuminate\Console\Command;

class SyncWater extends Command
{
    protected $signature = 'water:sync {--reference= : Référence de la mesure à importer}';

    protected $description = 'Synchronise une mesure de qualité de l’eau depuis les données de démonstration';

    public function handle(WaterQualityService $waterQualityService): int
    {
        $reference = $this->option('reference') ?: 'WQR-'.now()->format('YmdHis');
        $point = PointMesure::query()->where('statut', 'actif')->first();

        if (! $point) {
            $this->error('Aucun point de mesure actif n’est disponible.');

            return self::FAILURE;
        }

        $values = [
            'ph' => 7.4,
            'turbidite' => 0.4,
            'chlore_residuel' => 0.8,
            'plomb' => 2.0,
            'nitrates' => 15.2,
        ];
        $assessment = $waterQualityService->assess($values);

        $mesure = MesureQualite::updateOrCreate(
            ['reference' => $reference],
            $values + [
                'point_mesure_id' => $point->id,
                'date_mesure' => now(),
                'is_verified' => false,
                'verifier' => 'API interne',
                'status' => $assessment['status'],
                'overall_compliance' => $assessment['overall_compliance'],
            ]
        );

        $this->info("Mesure {$mesure->reference} synchronisée ({$assessment['status']}).");

        return self::SUCCESS;
    }
}
