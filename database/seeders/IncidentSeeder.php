<?php

namespace Database\Seeders;

use App\Models\Incident;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class IncidentSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::whereNotIn('email', [
            'gestionnaire@aquasecure.tn', 'admin@aquasecure.tn',
            'amira@aquasecure.tn', 'quality@aquasecure.tn', 'finance@aquasecure.tn',
        ])->get();

        foreach ($users as $user) {
            if (! Incident::where('user_id', $user->id)->exists()) {
                Incident::factory()->count(3)->for($user)->create();
            }
        }
    }
}
