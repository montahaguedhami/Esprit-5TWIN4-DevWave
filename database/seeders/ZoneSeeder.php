<?php

namespace Database\Seeders;

use App\Models\Infrastructure;
use App\Models\Zone;
use Illuminate\Database\Seeder;

class ZoneSeeder extends Seeder
{
    /**
     * Crée 6 zones du réseau, chacune avec 3 à 6 infrastructures.
     */
    public function run(): void
    {
        $zones = [
            ['nom' => 'Grand Tunis', 'localisation' => 'Tunis', 'description' => 'Réseau principal de la capitale et de sa banlieue.'],
            ['nom' => 'Cap Bon', 'localisation' => 'Nabeul', 'description' => 'Zone côtière à forte demande estivale.'],
            ['nom' => 'Sahel', 'localisation' => 'Sousse', 'description' => 'Secteur urbain et touristique du littoral.'],
            ['nom' => 'Sfax', 'localisation' => 'Sfax', 'description' => 'Zone industrielle et résidentielle du sud-est.'],
            ['nom' => 'Djerba', 'localisation' => 'Djerba', 'description' => 'Île alimentée par dessalement et adduction.'],
            ['nom' => 'Bizerte', 'localisation' => 'Bizerte', 'description' => 'Secteur nord desservi par le barrage de Sidi Salem.'],
        ];

        foreach ($zones as $donnees) {
            $zone = Zone::create($donnees);

            Infrastructure::factory()
                ->count(fake()->numberBetween(3, 6))
                ->for($zone)
                ->create();
        }
    }
}
