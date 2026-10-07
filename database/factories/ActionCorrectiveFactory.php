<?php

namespace Database\Factories;

use App\Models\ActionCorrective;
use App\Models\Incident;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ActionCorrective>
 */
class ActionCorrectiveFactory extends Factory
{
    protected $model = ActionCorrective::class;

    public function definition(): array
    {
        $statuts = ['a_faire', 'en_cours', 'terminee'];
        $statut = $this->faker->randomElement($statuts);

        return [
            'incident_id' => Incident::factory(),
            'titre' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(),
            'responsable' => $this->faker->name(),
            'date_prevue' => $this->faker->dateTimeBetween('-1 month', 'now')->format('Y-m-d'),
            'date_realisation' => now(),
            'statut' => $statut,
            'resultat' => $this->faker->sentence(),
        ];
    }
}
