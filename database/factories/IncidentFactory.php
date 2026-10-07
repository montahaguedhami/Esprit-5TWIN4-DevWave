<?php

namespace Database\Factories;

use App\Models\Incident;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Incident>
 */
class IncidentFactory extends Factory
{
    protected $model = Incident::class;

    public function definition(): array
    {
        $types = ['fuite', 'pollution', 'panne', 'rupture', 'contamination'];
        $gravites = ['faible', 'moyenne', 'elevee', 'critique'];
        $statuts = ['signale', 'en_cours', 'resolu', 'cloture'];

        return [
            'titre' => $this->faker->sentence(4),
            'description' => $this->faker->paragraph(),
            'type' => $this->faker->randomElement($types),
            'gravite' => $this->faker->randomElement($gravites),
            'statut' => $this->faker->randomElement($statuts),
            'date_signalement' => $this->faker->dateTimeBetween('-1 years', 'now'),
            'localisation' => $this->faker->city() . ', ' . $this->faker->country(),
            'user_id' => User::factory(),
        ];
    }
}
