<?php

namespace Database\Factories;

use App\Enums\ZoneStatut;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Zone>
 */
class ZoneFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $villes = [
            'Tunis' => ['lat' => 36.8065, 'lng' => 10.1815, 'superficie' => 346.50, 'pop' => 1056000],
            'Nabeul' => ['lat' => 36.4561, 'lng' => 10.7376, 'superficie' => 480.00, 'pop' => 380000],
            'Sousse' => ['lat' => 35.8256, 'lng' => 10.6411, 'superficie' => 266.00, 'pop' => 675000],
            'Sfax' => ['lat' => 34.7406, 'lng' => 10.7603, 'superficie' => 520.00, 'pop' => 955000],
            'Djerba' => ['lat' => 33.8076, 'lng' => 10.8572, 'superficie' => 514.00, 'pop' => 164000],
            'Bizerte' => ['lat' => 37.2744, 'lng' => 9.8739, 'superficie' => 395.00, 'pop' => 568000],
        ];

        $villeNom = fake()->randomElement(array_keys($villes));
        $villeData = $villes[$villeNom];

        return [
            'nom' => 'Zone '.$villeNom.' '.fake()->randomElement(['Nord', 'Sud', 'Est', 'Ouest', 'Centre', 'Littoral']),
            'description' => fake()->randomElement([
                'Secteur résidentiel et commercial desservi par le réseau principal de distribution.',
                'Zone industrielle à haute demande hydrique et surveillance continue de pression.',
                'Secteur littoral soumis à de fortes variations saisonnières de consommation.',
                'Périmètre périurbain en extension rapide avec nouvelles adductions.',
            ]),
            'localisation' => $villeNom,
            'latitude' => $villeData['lat'],
            'longitude' => $villeData['lng'],
            'superficie_km2' => $villeData['superficie'],
            'population_desservie' => $villeData['pop'],
            'statut' => fake()->randomElement([ZoneStatut::Active, ZoneStatut::Active, ZoneStatut::Active, ZoneStatut::Inactive]),
            'responsable_id' => User::inRandomOrder()->value('id'),
        ];
    }
}
