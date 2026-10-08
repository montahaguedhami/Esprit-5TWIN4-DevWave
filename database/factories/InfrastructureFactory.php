<?php

namespace Database\Factories;

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
        $type = fake()->randomElement(self::TYPES);

        return [
            'nom' => $type.' '.strtoupper(fake()->bothify('??-##')),
            'type' => $type,
            'localisation' => fake('fr_FR')->streetName(),
            'etat' => fake()->randomElement(self::ETATS),
            'date_installation' => fake()->dateTimeBetween('-30 years', '-6 months'),
            'zone_id' => Zone::factory(),
        ];
    }
}
