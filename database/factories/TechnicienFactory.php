<?php

namespace Database\Factories;

use App\Models\Technicien;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Technicien>
 */
class TechnicienFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nom' => fake('fr_FR')->firstName().' '.fake('fr_FR')->lastName(),
            'specialite' => fake()->randomElement(Technicien::SPECIALITES),
            // Numéro tunisien : 8 chiffres commençant par 2, 5 ou 9
            'telephone' => '+216 '.fake()->randomElement(['2', '5', '9']).fake()->numerify('#######'),
            'disponibilite' => fake()->randomElement(Technicien::DISPONIBILITES),
        ];
    }
}
