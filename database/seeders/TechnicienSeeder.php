<?php

namespace Database\Seeders;

use App\Models\Intervention;
use App\Models\Technicien;
use Illuminate\Database\Seeder;

class TechnicienSeeder extends Seeder
{
    /**
     * Crée 8 techniciens, chacun avec 2 à 6 interventions.
     */
    public function run(): void
    {
        Technicien::factory()
            ->count(8)
            ->create()
            ->each(function (Technicien $technicien) {
                Intervention::factory()
                    ->count(fake()->numberBetween(2, 6))
                    ->for($technicien)
                    ->create();
            });
    }
}
