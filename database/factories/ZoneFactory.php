<?php

namespace Database\Factories;

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
        $ville = fake()->randomElement(['Tunis', 'Ariana', 'Ben Arous', 'Sfax', 'Sousse', 'Monastir', 'Bizerte', 'Nabeul', 'Djerba', 'Gabès']);

        return [
            'nom' => 'Zone '.$ville.' '.fake()->randomElement(['Nord', 'Sud', 'Est', 'Ouest', 'Centre']),
            'description' => fake()->randomElement([
                'Secteur résidentiel desservi par le réseau principal.',
                'Zone industrielle à forte consommation.',
                'Secteur côtier soumis à des variations saisonnières de la demande.',
                'Quartiers périurbains en extension.',
            ]),
            'localisation' => $ville,
        ];
    }
}
