<?php

namespace Database\Seeders;

use App\Models\Intervention;
use App\Models\Technicien;
use App\Models\User;
use Illuminate\Database\Seeder;

class TechnicienSeeder extends Seeder
{
    /**
     * Crée la fiche liée au compte technicien de démo, puis 8 techniciens avec 2 à 6 interventions chacun.
     */
    public function run(): void
    {
        // Fiche liée au compte de démo amira@aquasecure.tn (créé par RbacSeeder)
        $amira = User::where('email', 'amira@aquasecure.tn')->first();
        if ($amira) {
            $technicien = Technicien::firstOrCreate(
                ['user_id' => $amira->id],
                [
                    'nom' => $amira->name,
                    'specialite' => 'Canalisations',
                    'telephone' => '+216 22 123 456',
                    'disponibilite' => 'Disponible',
                ]
            );

            if ($technicien->wasRecentlyCreated) {
                Intervention::factory()->count(4)->for($technicien)->create();
            }
        }

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
