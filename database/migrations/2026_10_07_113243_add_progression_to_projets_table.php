<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('projets', function (Blueprint $table) {
            // Ajouter le champ progression après budget
            // Valeur par défaut : 0
            // Représente le pourcentage d'avancement (0-100)
            $table->unsignedTinyInteger('progression')
                  ->default(0)
                  ->after('budget')
                  ->comment('Pourcentage d\'avancement du projet (0-100)');
            
            // Index pour faciliter les requêtes de filtrage
            $table->index('progression');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projets', function (Blueprint $table) {
            $table->dropIndex(['progression']);
            $table->dropColumn('progression');
        });
    }
};
