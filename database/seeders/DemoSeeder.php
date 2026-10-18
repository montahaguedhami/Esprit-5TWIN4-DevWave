<?php

namespace Database\Seeders;

use App\Enums\InfrastructureEtat;
use App\Enums\InfrastructureType;
use App\Enums\NiveauCriticite;
use App\Models\ActionCorrective;
use App\Models\Incident;
use App\Models\Infrastructure;
use App\Models\InfrastructureHistorique;
use App\Models\Intervention;
use App\Models\PointMesure;
use App\Models\Projet;
use App\Models\Technicien;
use App\Models\Zone;
use App\Models\User;
use App\Services\FailureRiskService;
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
        $this->seedRiskDemonstration($allInfrastructures);
    }

    private function seedRiskDemonstration($infrastructures): void
    {
            $infrastructures = $infrastructures->values();
            $total = $infrastructures->count();
            if ($total === 0) {
                return;
            }

            $lowCount = (int) round($total * 0.60);
            $mediumCount = (int) round($total * 0.25);
            $highCount = (int) round($total * 0.10);
            $criticalStart = $lowCount + $mediumCount + $highCount;
            $technician = Technicien::first() ?? Technicien::create([
                'nom' => 'Hédi Baccouche',
                'specialite' => 'Électromécanique',
                'telephone' => '+216 71 222 333',
                'disponibilite' => 'Disponible',
            ]);
            $user = User::first() ?? User::create([
                'name' => 'Compte de démonstration',
                'email' => 'demo-risk@aquasecure.tn',
                'password' => bcrypt('password'),
            ]);

            foreach ($infrastructures as $index => $infrastructure) {
                $profile = match (true) {
                    $index < $lowCount => 'low',
                    $index < $lowCount + $mediumCount => 'medium',
                    $index < $criticalStart => 'high',
                    default => 'critical',
                };

                $values = match ($profile) {
                    'low' => [
                        'etat' => InfrastructureEtat::EnService,
                        'date_installation' => now()->subYear()->toDateString(),
                        'duree_vie_annees' => 30,
                        'date_prochaine_maintenance' => now()->addYear()->toDateString(),
                        'niveau_criticite' => NiveauCriticite::Faible,
                    ],
                    'medium' => [
                        'etat' => InfrastructureEtat::EnService,
                        'date_installation' => now()->subYears(5)->toDateString(),
                        'duree_vie_annees' => 10,
                        'date_prochaine_maintenance' => now()->subDays(15)->toDateString(),
                        'niveau_criticite' => NiveauCriticite::Moyen,
                    ],
                    'high' => [
                        'etat' => InfrastructureEtat::EnMaintenance,
                        'date_installation' => now()->subYears(10)->toDateString(),
                        'duree_vie_annees' => 10,
                        'date_prochaine_maintenance' => now()->subDays(24)->toDateString(),
                        'niveau_criticite' => NiveauCriticite::Moyen,
                    ],
                    default => [
                        'etat' => InfrastructureEtat::HorsService,
                        'date_installation' => now()->subYears(15)->toDateString(),
                        'duree_vie_annees' => 10,
                        'date_prochaine_maintenance' => now()->subDays(120)->toDateString(),
                        'niveau_criticite' => NiveauCriticite::Critique,
                    ],
                };

                $infrastructure->forceFill($values)->saveQuietly();

                $incidentCount = match ($profile) {
                    'medium' => 4,
                    'high' => 3,
                    'critical' => 5,
                    default => 0,
                };
                for ($number = 1; $number <= $incidentCount; $number++) {
                    $incident = Incident::query()->firstOrCreate(
                        ['titre' => "Démo risque {$profile} #{$infrastructure->id} incident {$number}"],
                        [
                            'description' => 'Incident de démonstration pour illustrer le calcul de risque.',
                            'type' => 'maintenance',
                            'gravite' => $profile === 'critical' ? 'critique' : 'moyenne',
                            'statut' => 'signale',
                            'date_signalement' => now()->subDays($number * 4),
                            'localisation' => $infrastructure->localisation,
                            'zone_id' => $infrastructure->zone_id,
                            'infrastructure_id' => $infrastructure->id,
                            'user_id' => $user->id,
                        ],
                    );
                    if (! $incident->actions()->exists()) {
                        ActionCorrective::factory()->count(2)->create(['incident_id' => $incident->id]);
                    }
                }

                $interventionCount = match ($profile) {
                    'medium' => 1,
                    'high' => 2,
                    'critical' => 10,
                    default => 0,
                };
                for ($number = 1; $number <= $interventionCount; $number++) {
                    Intervention::query()->firstOrCreate(
                        ['description' => "Démo risque {$profile} #{$infrastructure->id} intervention {$number}"],
                        [
                            'technicien_id' => $technician->id,
                            'infrastructure_id' => $infrastructure->id,
                            'date' => now()->subDays($number * 8)->toDateString(),
                            'statut' => 'Terminée',
                            'cout' => 0,
                        ],
                    );
                }

                $flappingCount = match ($profile) {
                    'high' => 1,
                    'critical' => 10,
                    default => 0,
                };
                for ($number = 1; $number <= $flappingCount; $number++) {
                    $comment = "Démo de risque {$profile} - changement d'état {$number}";
                    InfrastructureHistorique::query()->firstOrCreate(
                        ['infrastructure_id' => $infrastructure->id, 'commentaire' => $comment],
                        [
                            'ancien_etat' => $number % 2 ? InfrastructureEtat::EnService->value : InfrastructureEtat::HorsService->value,
                            'nouvel_etat' => $number % 2 ? InfrastructureEtat::HorsService->value : InfrastructureEtat::EnService->value,
                            'user_name' => 'Démonstration',
                            'created_at' => now()->subDays($number),
                        ],
                    );
                }

                $riskService = app(FailureRiskService::class);
                $riskService->recompute($infrastructure, now()->subDays(60)->startOfDay());
                $riskService->recompute($infrastructure, now()->subDays(30)->startOfDay());
                $riskService->recompute($infrastructure);
            }
    }
}
