<?php

namespace Database\Factories;

use App\Models\Intervention;
use App\Models\Technicien;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Intervention>
 */
class InterventionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $date = fake()->dateTimeBetween('-6 months', '+1 month');

        return [
            'technicien_id' => Technicien::factory(),
            'date' => $date,
            'description' => fake()->randomElement([
                'Réparation d\'une fuite sur la canalisation principale',
                'Remplacement de la pompe de la station Nord',
                'Contrôle et étalonnage des capteurs de pression',
                'Nettoyage et désinfection du réservoir',
                'Remplacement d\'un compteur défectueux',
                'Réparation du tableau électrique de la station de pompage',
            ]),
            // Une intervention à venir ne peut pas déjà être terminée
            'statut' => $date > now()
                ? 'Planifiée'
                : fake()->randomElement(['En cours', 'Terminée', 'Terminée', 'Annulée']),
            'cout' => fake()->randomFloat(2, 80, 8000),
        ];
    }
}
