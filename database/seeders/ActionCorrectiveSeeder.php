<?php

namespace Database\Seeders;

use App\Models\ActionCorrective;
use App\Models\Incident;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ActionCorrectiveSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        Incident::doesntHave('actions')->each(function (Incident $incident) {
            ActionCorrective::factory()->count(2)->for($incident)->create();
        });
    }
}