<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RbacSeeder::class,
            IncidentSeeder::class,
            ActionCorrectiveSeeder::class,
            TechnicienSeeder::class,
            WaterQualitySeeder::class,
            ZoneSeeder::class,
        ]);

        // === MODULE QUALITÉ DE L'EAU — points de mesure de démonstration =========
        // A few demo measurement points, so the quality dashboards are usable
        // before any real data has been imported. Only managers/techniciens can
        // import real measurements through the external connectors.
        $demoPoints = [
            [
                'code'      => 'DEM-001',
                'nom'       => 'Poste T1 — Centre Ville',
                'zone'      => 'Grand Tunis',
                'adresse'   => 'Rue des Sources, Tunis',
                'latitude'  => 36.81,
                'longitude' => 10.18,
                'type'      => 'Station',
                'statut'    => 'actif',
            ],
            [
                'code'      => 'DEM-002',
                'nom'       => 'Station T2 — Belvédère',
                'zone'      => 'Grand Tunis',
                'adresse'   => 'Av. de l’Océan, Tunis',
                'latitude'  => 36.79,
                'longitude' => 10.22,
                'type'      => 'Station',
                'statut'    => 'actif',
            ],
            [
                'code'      => 'DEM-003',
                'nom'       => 'Puits rural — Sousse',
                'zone'      => 'Sahel',
                'adresse'   => 'Km 4, route de Sousse',
                'latitude'  => 35.83,
                'longitude' => 10.63,
                'type'      => 'Puits',
                'statut'    => 'actif',
            ],
            [
                'code'      => 'DEM-004',
                'nom'       => 'Réservoir — Djerba',
                'zone'      => 'Djerba',
                'adresse'   => 'Port de Djerba',
                'latitude'  => 34.87,
                'longitude' => 10.37,
                'type'      => 'Réservoir',
                'statut'    => 'actif',
            ],
            [
                'code'      => 'DEM-005',
                'nom'       => 'Station T3 — Sfax',
                'zone'      => 'Sfax',
                'adresse'   => 'Avenue de la Liberté',
                'latitude'  => 34.74,
                'longitude' => 10.76,
                'type'      => 'Station',
                'statut'    => 'actif',
            ],
        ];

        foreach ($demoPoints as $donnees) {
            PointMesure::firstOrCreate(
                ['code' => $donnees['code']],
                $donnees + ['description' => 'Point de mesure de démonstration.']
            );
        }

        // Synthetic measurements for those demo points (one per point, mixed
        // outcomes so every dashboard state is visible). They are never
        // presented as real field data: source_type is 'simulated'.
        $syntheticMeasurements = [
            [
                'DEM-001', 'DEM-R01', '2026-10-10 08:30', 7.20, 0.40, 0.60,  5.00, 12.00, 'Compliant',
            ],
            [
                'DEM-002', 'DEM-R02', '2026-10-11 09:15', 9.80, 2.50, null, 40.00, 25.00, 'Non-Compliant',
            ],
            [
                'DEM-003', 'DEM-R03', '2026-10-12 10:00', 7.10, 0.90, 0.90, 12.00, 8.00, 'Compliant',
            ],
            [
                'DEM-004', 'DEM-R04', '2026-10-13 11:45', 6.40, 1.20, null, 18.00, 10.00, 'Alert',
            ],
            [
                'DEM-005', 'DEM-R05', '2026-10-14 14:20', 8.10, 0.30, 0.50, 25.00, 15.00, 'Compliant',
            ],
        ];

        foreach ($syntheticMeasurements as [$pointCode, $reference, $date, $ph, $turbidite, $chloreResiduel, $plomb, $nitrates, $status]) {
            $point = PointMesure::where('code', $pointCode)->firstOrFail();

            MesureQualite::create([
                'reference'          => $reference,
                'point_mesure_id'   => $point->id,
                'date_mesure'       => $date,
                'ph'                => $ph,
                'turbidite'         => $turbidite,
                'chlore_residuel'   => $chloreResiduel,
                'plomb'             => $plomb,
                'nitrates'          => $nitrates,
                'source_donnee'     => 'simule',
                'source_type'       => 'simulated',
                'status'            => $status,
                'overall_compliance' => match ($status) {
                    'Compliant'      => 100.0,
                    'Alert'          => 60.0,
                    'Non-Compliant'  => 0.0,
                    default           => null,
                },
            ]);
        }

        // === MODULE PROJETS/FINANCEMENTS (Ghada) ===
        
        // Créer 15 projets avec des financements variés
        $projets = \App\Models\Projet::factory(15)->create();

        foreach ($projets as $projet) {
            // Déterminer le nombre de financements en fonction du statut et du budget
            $nombreFinancements = match($projet->statut) {
                'planifie' => fake()->numberBetween(0, 1),  // Projets planifiés : peu ou pas de financement
                'en_cours' => fake()->numberBetween(1, 3),  // Projets en cours : 1-3 financements
                'termine' => fake()->numberBetween(2, 4),   // Projets terminés : 2-4 financements
                'suspendu' => fake()->numberBetween(1, 2),  // Projets suspendus : 1-2 financements
                'annule' => fake()->numberBetween(0, 1),    // Projets annulés : peu ou pas de financement
                default => fake()->numberBetween(0, 2),
            };

            // Créer les financements
            if ($nombreFinancements > 0) {
                $budgetRestant = $projet->budget;
                
                for ($i = 0; $i < $nombreFinancements; $i++) {
                    // Calculer un montant cohérent
                    if ($i === $nombreFinancements - 1) {
                        // Dernier financement : entre 20% et 80% du budget restant
                        $montantMax = $budgetRestant * 0.8;
                        $montantMin = min($budgetRestant * 0.2, $montantMax);
                    } else {
                        // Autres financements : entre 15% et 40% du budget total
                        $montantMax = $projet->budget * 0.4;
                        $montantMin = $projet->budget * 0.15;
                    }
                    
                    $montant = fake()->numberBetween((int)$montantMin, (int)$montantMax);
                    
                    // Date de financement : entre date début du projet et maintenant (ou date début si future)
                    $dateFinancementStart = $projet->date_debut > now() ? $projet->date_debut : $projet->date_debut;
                    $dateFinancementEnd = $projet->date_debut > now() ? $projet->date_debut : 'now';
                    
                    // Créer le financement
                    \App\Models\Financement::factory()->create([
                        'projet_id' => $projet->id,
                        'montant' => $montant,
                        'date_financement' => fake()->dateTimeBetween($dateFinancementStart, $dateFinancementEnd),
                    ]);
                    
                    $budgetRestant -= $montant;
                    
                    // Arrêter si le budget est dépassé
                    if ($budgetRestant <= 0) {
                        break;
                    }
                }
            }
        }

        // Créer quelques cas spéciaux pour les tests
        
        // 1. Projet sans financement
        \App\Models\Projet::factory()->create([
            'nom' => 'Étude de faisabilité réseau Tozeur',
            'description' => 'Étude préliminaire pour l\'extension du réseau d\'eau potable dans la région de Tozeur.',
            'statut' => 'planifie',
            'progression' => 0,
            'budget' => 150000,
            'adresse' => 'Tozeur Centre, Tozeur',
            'latitude' => 33.9197,
            'longitude' => 8.1335,
        ]);

        // 2. Projet totalement financé
        $projetFinance = \App\Models\Projet::factory()->create([
            'nom' => 'Réhabilitation réseau Ksar Hellal',
            'description' => 'Réhabilitation complète du réseau de distribution d\'eau potable de Ksar Hellal.',
            'statut' => 'en_cours',
            'progression' => 65,
            'budget' => 800000,
            'adresse' => 'Ksar Hellal, Monastir',
            'latitude' => 35.6475,
            'longitude' => 10.8908,
        ]);
        
        \App\Models\Financement::factory()->create([
            'projet_id' => $projetFinance->id,
            'source' => 'Banque Mondiale',
            'montant' => 500000,
            'date_financement' => fake()->dateTimeBetween($projetFinance->date_debut, 'now'),
        ]);
        
        \App\Models\Financement::factory()->create([
            'projet_id' => $projetFinance->id,
            'source' => 'Budget de l\'État Tunisien',
            'montant' => 300000,
            'date_financement' => fake()->dateTimeBetween($projetFinance->date_debut, 'now'),
        ]);

        // 3. Projet avec dépassement de budget (pour tester la logique)
        $projetDepasse = \App\Models\Projet::factory()->create([
            'nom' => 'Urgence fuite Manouba',
            'description' => 'Réparation d\'urgence suite à une rupture majeure de la conduite principale.',
            'statut' => 'termine',
            'progression' => 100,
            'budget' => 120000,
            'adresse' => 'Manouba Centre, Manouba',
            'latitude' => 36.8080,
            'longitude' => 10.0965,
        ]);
        
        \App\Models\Financement::factory()->create([
            'projet_id' => $projetDepasse->id,
            'source' => 'SONEDE - Fonds d\'urgence',
            'montant' => 150000,
            'date_financement' => fake()->dateTimeBetween($projetDepasse->date_debut, 'now'),
        ]);

        // 4. Projet sans géolocalisation (pour tester les cas nullable)
        \App\Models\Projet::factory()->create([
            'nom' => 'Étude nationale qualité des eaux',
            'description' => 'Programme national d\'analyse et d\'amélioration de la qualité de l\'eau potable.',
            'statut' => 'en_cours',
            'progression' => 40,
            'budget' => 2500000,
            'adresse' => null,
            'latitude' => null,
            'longitude' => null,
        ]);
    }
}
