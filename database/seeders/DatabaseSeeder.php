<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::firstOrCreate(['email' => 'test@example.com'], [
            'name' => 'Test User',
            'password' => Hash::make('password'),
        ]);
        User::firstOrCreate(['email' => 'citoyen@aquasecure.tn'], [
            'name' => 'Yassine Hamdi',
            'password' => Hash::make('password'),
        ]);

        $this->call([
            IncidentSeeder::class,
            ActionCorrectiveSeeder::class,
        ]);

        $this->call([TechnicienSeeder::class]);
    }
}
