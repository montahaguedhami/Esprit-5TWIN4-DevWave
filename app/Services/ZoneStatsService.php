<?php

namespace App\Services;

use App\Enums\InfrastructureEtat;
use App\Models\Infrastructure;
use App\Models\Zone;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ZoneStatsService
{
    /**
     * Statistiques complètes pour une zone donnée.
     *
     * @return array<string, mixed>
     */
    public function statsZone(Zone $zone): array
    {
        $infrastructuresQuery = $zone->infrastructures();
        $total = $infrastructuresQuery->count();

        if ($total === 0) {
            return [
                'total' => 0,
                'repartition_etat' => [],
                'repartition_type' => [],
                'pourcentage_en_service' => 0,
                'nombre_en_retard_maintenance' => 0,
                'criticite_moyenne' => 'Faible',
                'score_sante_moyen' => 100,
            ];
        }

        // Répartition par état
        $repartitionEtat = $zone->infrastructures()
            ->select('etat', DB::raw('COUNT(*) as total'))
            ->groupBy('etat')
            ->pluck('total', 'etat')
            ->all();

        // Répartition par type
        $repartitionType = $zone->infrastructures()
            ->select('type', DB::raw('COUNT(*) as total'))
            ->groupBy('type')
            ->pluck('total', 'type')
            ->all();

        // En service & pourcentage
        $enService = (int) ($repartitionEtat[InfrastructureEtat::EnService->value] ?? 0);
        $pourcentageEnService = round(($enService / $total) * 100, 1);

        // Retard de maintenance
        $nombreEnRetardMaintenance = $zone->infrastructures()
            ->whereNotNull('date_prochaine_maintenance')
            ->whereDate('date_prochaine_maintenance', '<', now()->toDateString())
            ->count();

        // Criticité moyenne
        $avgCriticite = (float) $zone->infrastructures()
            ->selectRaw("AVG(CASE WHEN niveau_criticite = 'critique' THEN 3 WHEN niveau_criticite = 'moyen' THEN 2 ELSE 1 END) as score")
            ->value('score');

        $criticiteMoyenne = match (true) {
            $avgCriticite >= 2.5 => 'Critique',
            $avgCriticite >= 1.6 => 'Moyenne',
            default => 'Faible',
        };

        // Score de santé moyen (évaluation sur les infrastructures de la zone)
        $infrastructures = $zone->infrastructures()->get();
        $scores = $infrastructures->map(fn ($i) => $i->sante['score']);
        $scoreSanteMoyen = (int) round($scores->avg() ?: 100);

        return [
            'total' => $total,
            'repartition_etat' => $repartitionEtat,
            'repartition_type' => $repartitionType,
            'pourcentage_en_service' => $pourcentageEnService,
            'nombre_en_retard_maintenance' => $nombreEnRetardMaintenance,
            'criticite_moyenne' => $criticiteMoyenne,
            'score_sante_moyen' => $scoreSanteMoyen,
        ];
    }

    /**
     * Statistiques globales sur tout le réseau (mises en cache 5 minutes).
     *
     * @return array<string, mixed>
     */
    public function statsGlobales(): array
    {
        return Cache::remember('zone_stats_globales', 300, function (): array {
            $totalInfrastructures = Infrastructure::count();
            $totalZones = Zone::count();

            if ($totalInfrastructures === 0) {
                return [
                    'total_infrastructures' => 0,
                    'total_zones' => $totalZones,
                    'repartition_etat' => [],
                    'repartition_type' => [],
                    'pourcentage_en_service' => 0,
                    'nombre_en_retard_maintenance' => 0,
                    'score_sante_moyen' => 100,
                    'zones_a_risque' => collect(),
                ];
            }

            $repartitionEtat = Infrastructure::query()
                ->select('etat', DB::raw('COUNT(*) as total'))
                ->groupBy('etat')
                ->pluck('total', 'etat')
                ->all();

            $repartitionType = Infrastructure::query()
                ->select('type', DB::raw('COUNT(*) as total'))
                ->groupBy('type')
                ->pluck('total', 'type')
                ->all();

            $enService = (int) ($repartitionEtat[InfrastructureEtat::EnService->value] ?? 0);
            $pourcentageEnService = round(($enService / $totalInfrastructures) * 100, 1);

            $nombreEnRetardMaintenance = Infrastructure::query()
                ->whereNotNull('date_prochaine_maintenance')
                ->whereDate('date_prochaine_maintenance', '<', now()->toDateString())
                ->count();

            // Calcul du score moyen global
            $scores = Infrastructure::all()->map(fn ($i) => $i->sante['score']);
            $scoreSanteMoyen = (int) round($scores->avg() ?: 100);

            // Top 5 des zones les plus à risque :
            // Zones avec le plus grand nombre d'équipements hors service ou en maintenance
            $zonesARisque = Zone::query()
                ->has('infrastructures')
                ->withCount([
                    'infrastructures as total_infras',
                    'infrastructures as hors_service_count' => function ($q) {
                        $q->where('etat', InfrastructureEtat::HorsService->value);
                    },
                    'infrastructures as retard_count' => function ($q) {
                        $q->whereNotNull('date_prochaine_maintenance')
                            ->whereDate('date_prochaine_maintenance', '<', now()->toDateString());
                    },
                ])
                ->orderByRaw('(hors_service_count * 2 + retard_count) DESC')
                ->limit(5)
                ->get();

            return [
                'total_infrastructures' => $totalInfrastructures,
                'total_zones' => $totalZones,
                'repartition_etat' => $repartitionEtat,
                'repartition_type' => $repartitionType,
                'pourcentage_en_service' => $pourcentageEnService,
                'nombre_en_retard_maintenance' => $nombreEnRetardMaintenance,
                'score_sante_moyen' => $scoreSanteMoyen,
                'zones_a_risque' => $zonesARisque,
            ];
        });
    }
}
