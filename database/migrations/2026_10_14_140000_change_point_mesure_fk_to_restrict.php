<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Additive migration: change the FK on mesures_qualites.point_mesure_id
 * from CASCADE DELETE to RESTRICT, so that a point with historical
 * measurements cannot be accidentally deleted at the database level.
 *
 * The original migration (2026_10_08_000002) used ->cascadeOnDelete().
 * This migration replaces that constraint without touching the original file.
 *
 * Rollback restores CASCADE DELETE behaviour.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mesures_qualites', function (Blueprint $table) {
            // Drop by column name — Laravel resolves the conventional constraint name
            // mesures_qualites_point_mesure_id_foreign automatically.
            $table->dropForeign(['point_mesure_id']);

            $table->foreign('point_mesure_id')
                ->references('id')
                ->on('point_mesures')
                ->restrictOnDelete()
                ->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::table('mesures_qualites', function (Blueprint $table) {
            $table->dropForeign(['point_mesure_id']);

            $table->foreign('point_mesure_id')
                ->references('id')
                ->on('point_mesures')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
        });
    }
};
