<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Make all measured parameters nullable so partially available external data
 * can be stored without inventing missing values.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mesures_qualites', function (Blueprint $table) {
            $table->decimal('ph', 5, 2)->nullable()->change();
            $table->decimal('turbidite', 8, 2)->nullable()->change();
            $table->decimal('chlore_residuel', 8, 3)->nullable()->change();
            $table->decimal('plomb', 10, 3)->nullable()->change();
            $table->decimal('nitrates', 8, 2)->nullable()->change();
            $table->decimal('overall_compliance', 5, 2)->nullable()->change();
        });
    }

    public function down(): void
    {
        // No safe automatic revert on SQLite/MySQL without backfill.
    }
};