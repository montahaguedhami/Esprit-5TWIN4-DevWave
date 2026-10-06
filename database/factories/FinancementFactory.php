<?php

namespace Database\Factories;

use App\Models\Financement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Financement>
 */
class FinancementFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Sources de financement réalistes
        $sources = [
            'Ministère de l\'Équipement et de l\'Eau',
            'Banque Mondiale',
            'Banque Africaine de Développement',
            'Agence Française de Développement (AFD)',
            'Union Européenne',
            'Banque Européenne d\'Investissement',
            'Fonds Koweitien pour le Développement',
            'Budget de l\'État Tunisien',
            'Société Nationale d\'Exploitation et de Distribution des Eaux (SONEDE)',
            'Coopération Allemande (KfW)',
            'Coopération Japonaise (JICA)',
            'Fonds Saoudien pour le Développement',
            'Collectivité locale',
            'Partenariat Public-Privé',
            'Programme des Nations Unies pour le Développement',
        ];

        return [
            'projet_id' => \App\Models\Projet::factory(),
            'source' => fake()->randomElement($sources),
            'montant' => fake()->numberBetween(10000, 1500000),
            'date_financement' => fake()->dateTimeBetween('-1 year', 'now'),
        ];
    }
}
