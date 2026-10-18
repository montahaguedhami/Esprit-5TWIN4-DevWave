<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Doit s'exécuter APRÈS create_projets_table (clé étrangère vers projets),
     * d'où l'horodatage 031837.
     */
    public function up(): void
    {
        if (Schema::hasTable('financements')) {
            return;
        }

        Schema::create('financements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('projet_id')->constrained('projets')->cascadeOnDelete();
            $table->string('source');
            $table->decimal('montant', 15, 2);
            $table->date('date_financement');
            $table->timestamps();
            
            // Index pour améliorer les performances
            $table->index('projet_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financements');
    }
};
