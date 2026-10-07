<?php

namespace Database\Seeders;

use App\Models\User;
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
        // Créer les utilisateurs de démo
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

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
