<?php

namespace Database\Factories;

use App\Enums\InfrastructureEtat;
use App\Enums\InfrastructureType;
use App\Enums\NiveauCriticite;
use App\Models\Infrastructure;
use App\Models\Zone;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Infrastructure>
 */
class InfrastructureFactory extends Factory
{
    public const TYPES = ['Canalisation', 'Réservoir', 'Station de pompage', 'Station de traitement'];

    public const ETATS = ['En service', 'En service', 'En service', 'En maintenance', 'Hors service'];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement(InfrastructureType::cases());
        $dateInstallation = fake()->dateTimeBetween('-25 years', '-1 year');
        $dateDerniere = fake()->dateTimeBetween($dateInstallation, '-1 month');
        $prochaineMaintenance = fake()->dateTimeBetween('-2 months', '+6 months');

        return [
            'nom' => $type->label().' '.strtoupper(fake()->bothify('??-##')),
            'type' => $type,
            'localisation' => fake('fr_FR')->streetName(),
            'etat' => fake()->randomElement([
                InfrastructureEtat::EnService,
                InfrastructureEtat::EnService,
                InfrastructureEtat::EnService,
                InfrastructureEtat::EnMaintenance,
                InfrastructureEtat::HorsService,
            ]),
            'date_installation' => $dateInstallation,
            'zone_id' => Zone::factory(),
            'latitude' => fake()->randomFloat(7, 33.8, 37.2),
            'longitude' => fake()->randomFloat(7, 9.8, 10.9),
            'capacite' => fake()->randomFloat(2, 50, 15000),
            'unite_capacite' => fake()->randomElement(['m3', 'm3/h', 'l/s']),
            'fabricant' => fake()->randomElement([
                'SONEDE Technologies',
                'Grundfos',
                'Veolia Maghreb',
                'Schneider Water Systems',
                'KSB Pompes',
                'Flowserve Méditerranée',
            ]),
            'reference' => 'INF-'.strtoupper(fake()->unique()->bothify('??-####')),
            'date_derniere_maintenance' => $dateDerniere,
            'date_prochaine_maintenance' => $prochaineMaintenance,
            'duree_vie_annees' => fake()->randomElement([15, 20, 25, 30, 40]),
            'niveau_criticite' => fake()->randomElement(NiveauCriticite::cases()),
        ];
    }
}
