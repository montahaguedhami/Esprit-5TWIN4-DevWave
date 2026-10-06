<?php

namespace Database\Factories;

use App\Models\Projet;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Projet>
 */
class ProjetFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Noms réalistes de projets en Tunisie
        $projets = [
            'Réhabilitation du réseau d\'eau de Houmt Souk',
            'Extension du réseau potable de Djerba Midoun',
            'Modernisation de la station de pompage de Tunis Nord',
            'Rénovation de la conduite principale d\'Ariana',
            'Projet de réduction des pertes d\'eau à Ben Arous',
            'Installation d\'équipements de traitement à Sfax',
            'Réhabilitation du réseau de distribution de Sousse',
            'Extension du réseau d\'eau de Nabeul',
            'Modernisation de la station d\'épuration de Bizerte',
            'Rénovation des canalisations de Monastir',
            'Projet de sécurisation du réseau de Gabès',
            'Extension du réseau potable de La Marsa',
            'Réhabilitation des infrastructures de Hammamet',
            'Modernisation du réseau de Kairouan',
            'Projet d\'efficacité énergétique à Gafsa',
        ];

        // Descriptions réalistes
        $descriptions = [
            'Travaux de réhabilitation complète du réseau de distribution d\'eau potable avec remplacement des conduites vétustes et installation de nouveaux équipements de régulation.',
            'Extension du réseau existant pour desservir les nouvelles zones urbaines avec installation de conduites principales et branchements.',
            'Modernisation des équipements de pompage avec installation de systèmes automatisés de contrôle et télégestion pour optimiser la distribution.',
            'Rénovation d\'urgence de la conduite principale avec remplacement des tronçons défaillants et renforcement de la capacité hydraulique.',
            'Programme de détection et réparation des fuites avec installation de compteurs intelligents et réhabilitation des branchements.',
            'Installation d\'une nouvelle unité de traitement d\'eau avec système de filtration avancée et contrôle automatisé de la qualité.',
            'Réhabilitation du réseau secondaire avec remplacement des canalisations en amiante et installation de nouveaux équipements de régulation.',
            'Extension du réseau pour raccorder les quartiers périphériques avec construction de réservoirs de stockage et stations de pompage.',
            'Mise à niveau de la station avec installation de nouveaux équipements de traitement biologiques et systèmes de contrôle avancés.',
            'Rénovation complète des canalisations du centre-ville avec techniques sans tranchée et installation de vannes de sectionnement.',
            'Sécurisation du réseau contre les pertes avec installation de systèmes de détection précoce et réhabilitation des infrastructures critiques.',
            'Extension du réseau côtier avec installation de nouvelles conduites et amélioration de la pression de distribution.',
            'Réhabilitation des infrastructures hydrauliques de la zone touristique avec modernisation des équipements de pompage.',
            'Modernisation complète du système de distribution avec installation de compteurs intelligents et système de télégestion.',
            'Projet d\'optimisation énergétique avec installation de pompes à haut rendement et panneaux solaires.',
        ];

        // Localisations réalistes en Tunisie avec coordonnées GPS précises
        $localisations = [
            ['adresse' => 'Houmt Souk, Djerba, Médenine', 'lat' => 33.8751, 'lng' => 10.8576],
            ['adresse' => 'Midoun, Djerba, Médenine', 'lat' => 33.8042, 'lng' => 10.9926],
            ['adresse' => 'Tunis Nord, Tunis', 'lat' => 36.8534, 'lng' => 10.1974],
            ['adresse' => 'Ariana Centre, Ariana', 'lat' => 36.8625, 'lng' => 10.1956],
            ['adresse' => 'Ben Arous, Ben Arous', 'lat' => 36.7474, 'lng' => 10.2199],
            ['adresse' => 'Sfax Centre, Sfax', 'lat' => 34.7406, 'lng' => 10.7603],
            ['adresse' => 'Sousse Ville, Sousse', 'lat' => 35.8256, 'lng' => 10.6369],
            ['adresse' => 'Nabeul Centre, Nabeul', 'lat' => 36.4561, 'lng' => 10.7356],
            ['adresse' => 'Bizerte Nord, Bizerte', 'lat' => 37.2746, 'lng' => 9.8739],
            ['adresse' => 'Monastir Centre, Monastir', 'lat' => 35.7775, 'lng' => 10.8262],
            ['adresse' => 'Gabès Sud, Gabès', 'lat' => 33.8815, 'lng' => 10.0982],
            ['adresse' => 'La Marsa, Tunis', 'lat' => 36.8784, 'lng' => 10.3253],
            ['adresse' => 'Hammamet Nord, Nabeul', 'lat' => 36.4099, 'lng' => 10.6214],
            ['adresse' => 'Kairouan Centre, Kairouan', 'lat' => 35.6781, 'lng' => 10.0963],
            ['adresse' => 'Gafsa Nord, Gafsa', 'lat' => 34.4250, 'lng' => 8.7842],
        ];

        $statuts = ['planifie', 'en_cours', 'termine', 'suspendu', 'annule'];
        $statutWeights = [0.2, 0.4, 0.25, 0.1, 0.05]; // Probabilités

        // Sélection pondérée du statut
        $rand = fake()->randomFloat(2, 0, 1);
        $cumulative = 0;
        $statut = 'en_cours';
        foreach ($statutWeights as $i => $weight) {
            $cumulative += $weight;
            if ($rand <= $cumulative) {
                $statut = $statuts[$i];
                break;
            }
        }

        // Sélection aléatoire d'un projet
        $index = fake()->numberBetween(0, count($projets) - 1);
        $nom = $projets[$index];
        $description = $descriptions[$index];
        $localisation = $localisations[$index];

        // Budget entre 50 000 et 5 000 000 DT
        $budget = fake()->numberBetween(50000, 5000000);

        // Dates cohérentes - toujours dans le passé pour éviter les erreurs de seeding
        $dateDebut = fake()->dateTimeBetween('-2 years', '-1 month');
        $dateFinEstimee = (clone $dateDebut)->modify('+' . fake()->numberBetween(3, 36) . ' months');
        
        // Date de fin si projet terminé
        $dateFin = null;
        if ($statut === 'termine') {
            $dateFin = fake()->dateTimeBetween($dateDebut, 'now');
        }

        return [
            'nom' => $nom,
            'description' => $description,
            'date_debut' => $dateDebut,
            'date_fin' => $dateFin ?: ($statut === 'en_cours' ? $dateFinEstimee : null),
            'budget' => $budget,
            'statut' => $statut,
            'adresse' => $localisation['adresse'],
            'latitude' => $localisation['lat'],
            'longitude' => $localisation['lng'],
        ];
    }
}
