<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds an explicit `source_type` discriminator to mesures_qualites.
 *
 * Values:
 *   'real'      — field-collected or laboratory-verified observation
 *   'simulated' — synthetically generated; must never be presented as a real measurement
 *
 * Existing rows: source_donnee = 'simule' → 'simulated', all others → 'real'.
 * This keeps backward compatibility while giving a single unambiguous column for
 * the dashboard real/simulated indicator.
 *
 * Also adds a composite index on (source_type, date_mesure) for dashboard queries.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mesures_qualites', function (Blueprint $table) {
            $table->string('source_type', 20)
                ->default('real')
                ->after('source_donnee')
                ->comment('real = field/lab data | simulated = synthetic placeholder');

            $table->index(['source_type', 'date_mesure'], 'mesures_qualites_source_type_date_idx');
        });

        // Backfill: mark existing 'simule' rows as 'simulated'
        \Illuminate\Support\Facades\DB::table('mesures_qualites')
            ->where('source_donnee', 'simule')
            ->update(['source_type' => 'simulated']);
    }

    public function down(): void
    {
        Schema::table('mesures_qualites', function (Blueprint $table) {
            $table->dropIndex('mesures_qualites_source_type_date_idx');
            $table->dropColumn('source_type');
        });
    }
};
