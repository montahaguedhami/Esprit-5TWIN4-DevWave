<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Distingue les interventions correctives (panne) des préventives (planifiées).
     */
    public function up(): void
    {
        Schema::table('interventions', function (Blueprint $table) {
            $table->string('type', 20)->default('Corrective')->after('statut');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('interventions', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
