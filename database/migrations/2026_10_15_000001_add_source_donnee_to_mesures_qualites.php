<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Additive migration: add source_donnee to mesures_qualites.
 *
 * Values: 'simule', 'manuel', 'laboratoire'
 *
 * Existing rows receive the default 'manuel' to remain neutral (neither
 * claiming laboratory provenance nor marking demo data — the seeder will
 * set 'simule' via updateOrCreate on its next run).
 *
 * The column is nullable to avoid blocking a migration on a live table
 * while the default propagates; application code treats null as 'manuel'.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mesures_qualites', function (Blueprint $table) {
            $table->string('source_donnee', 20)
                ->nullable()
                ->default('manuel')
                ->after('nitrates')
                ->comment('Provenance : simule | manuel | laboratoire');
        });
    }

    public function down(): void
    {
        Schema::table('mesures_qualites', function (Blueprint $table) {
            $table->dropColumn('source_donnee');
        });
    }
};
