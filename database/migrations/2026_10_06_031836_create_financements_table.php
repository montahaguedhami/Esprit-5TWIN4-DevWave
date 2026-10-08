<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Migration vide conservée pour les bases où elle a déjà été exécutée.
     * Avec le même horodatage que create_projets_table, elle passait AVANT
     * projets : MySQL refusait alors la clé étrangère.
     * La table est maintenant créée par 2026_10_06_031837_create_financements_table.
     */
    public function up(): void
    {
        //
    }

    public function down(): void
    {
        //
    }
};
