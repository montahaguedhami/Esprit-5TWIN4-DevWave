<?php

namespace Database\Seeders;

use App\Enums\InfrastructureEtat;
use App\Enums\InfrastructureType;
use App\Enums\NiveauCriticite;
use App\Models\Incident;
use App\Models\Infrastructure;
use App\Models\InfrastructureRiskPrediction;
use App\Models\Intervention;
use App\Models\PointMesure;
use App\Models\Projet;
use App\Models\Technicien;
use App\Models\Zone;
use Illuminate\Database\Seeder;

class DemoSeeder extends Seeder
{
    /**
     * Seeder de démonstration complet pour le réseau AquaSecure.
     * Interconnecte les modules Zones, Infrastructures, Interventions, Incidents, Mesures et Projets.
     */
    public function run(): void
    {
        // 1. S'assurer que les zones et infrastructures sont semées
        $this->call(ZoneSeeder::class);

        $zones = Zone::with('infrastructures')->get();
        if ($zones->isEmpty()) {
            return;
        }

        $allInfrastructures = Infrastructure::all();

        // 2. Interconnexion Interventions -> Infrastructures (Module 7a)
        $techniciens = Technicien::all();
        $interventions = Intervention::whereNull('infrastructure_id')->get();

        if ($interventions->isNotEmpty() && $allInfrastructures->isNotEmpty()) {
            foreach ($interventions as $intervention) {
                $infra = $allInfrastructures->random();
                $intervention->infrastructure_id = $infra->id;
                $intervention->save();
            }
        }

        // Si peu d'interventions, en créer quelques-unes réalistes
        if (Intervention::count() < 10 && $allInfrastructures->isNotEmpty()) {
            $technicien = $techniciens->first() ?? Technicien::create([
                'nom' => 'Hédi Baccouche',
                'specialite' => 'Électromécanique',
                'telephone' => '+216 71 222 333',
                'disponibilite' => 'Disponible',
            ]);
            foreach ($allInfrastructures->take(8) as $infra) {
                Intervention::create([
                    'infrastructure_id' => $infra->id,
                    'technicien_id' => $technicien->id,
                    'date' => now()->subDays(rand(5, 60)),
                    'description' => 'Maintenance préventive : purge, graissage et vérification des raccords hydrauliques sur ' . $infra->nom,
                    'statut' => 'Terminée',
                    'cout' => rand(250, 1850),
                ]);
            }
        }

        // 3. Interconnexion Incidents -> Zones et Infrastructures (Module 7b)
        $incidents = Incident::all();
        if ($incidents->isNotEmpty() && $zones->isNotEmpty()) {
            foreach ($incidents as $incident) {
                $zone = $zones->random();
                $incident->zone_id = $zone->id;

                if ($zone->infrastructures->isNotEmpty() && rand(0, 1) === 1) {
                    $incident->infrastructure_id = $zone->infrastructures->random()->id;
                }

                $incident->save();
            }
        }

        // 4. Interconnexion PointMesure -> Zones (Module 7c)
        $points = PointMesure::all();
        foreach ($points as $point) {
            if (!$point->zone_id) {
                // Trouver une zone correspondant au nom ou localisation
                $matchedZone = $zones->first(function ($z) use ($point) {
                    return stripos($point->nom, $z->localisation) !== false
                        || stripos($point->adresse, $z->localisation) !== false
                        || stripos($point->zone ?? '', $z->nom) !== false;
                }) ?? $zones->random();

                $point->zone_id = $matchedZone->id;
                $point->save();
            }
        }

        // 5. Interconnexion Projets -> Zones (Module 7d - Table pivot projet_zone)
        $projets = Projet::all();
        if ($projets->isNotEmpty() && $zones->isNotEmpty()) {
            foreach ($projets as $projet) {
                if ($projet->zones()->count() === 0) {
                    // Rattacher 1 à 2 zones à chaque projet
                    $selectedZones = $zones->random(min(rand(1, 2), $zones->count()));
                    $projet->zones()->syncWithoutDetaching($selectedZones->pluck('id'));
                }
            }
        }

        // 6. Historique de risque de démonstration : répartition et évolution sur 60 jours.
        $riskLevels = ['Faible', 'Moyen', 'Élevé', 'Critique'];
        foreach ($allInfrastructures->values() as $index => $infrastructure) {
            $levelIndex = match (true) {
                $index < (int) ceil($allInfrastructures->count() * 0.60) => 0,
                $index < (int) ceil($allInfrastructures->count() * 0.80) => 1,
                $index < (int) ceil($allInfrastructures->count() * 0.95) => 2,
                default => 3,
            };
            $level = $riskLevels[$levelIndex];
            $base = [0 => 15, 1 => 42, 2 => 63, 3 => 84][$levelIndex];
            foreach ([60 => -7, 30 => -3, 0 => 0] as $daysAgo => $delta) {
                $computedAt = now()->subDays($daysAgo)->startOfDay();
                InfrastructureRiskPrediction::query()->updateOrCreate(
                    ['infrastructure_id' => $infrastructure->id, 'computed_at' => $computedAt],
                    ['probability' => $base + $delta, 'level' => $level, 'factors' => []],
                );
            }
        }
    }
}
