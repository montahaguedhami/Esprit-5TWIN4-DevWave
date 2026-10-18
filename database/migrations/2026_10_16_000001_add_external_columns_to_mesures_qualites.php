<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Additive migration: support external public data sources in mesures_qualites.
 *
 * - provider:     name of the data provider ('emodnet', 'wqp', null for local)
 * - external_id:  original record identifier from the provider (for idempotent upserts)
 * - observation_date: actual observation date as reported by the provider
 *                     (may differ from date_mesure which is the import date for external data)
 * - chlore_residuel and plomb are made nullable because external sources (EMODnet,…)
 *   typically do not carry residual chlorine or lead measurements.
 *
 * Existing rows are unaffected: nullable columns default to NULL, existing non-null
 * chlore_residuel and plomb values are preserved.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mesures_qualites', function (Blueprint $table) {
            // External provider slug (null = local manual/lab/simulated data)
            $table->string('provider', 50)
                ->nullable()
                ->default(null)
                ->after('source_donnee')
                ->comment('Data provider slug: emodnet | wqp | null for local data');

            // Stable external identifier from the provider — used for idempotent sync
            $table->string('external_id', 255)
                ->nullable()
                ->default(null)
                ->after('provider')
                ->comment('Stable external record ID from provider (e.g. LOCAL_CDI_ID for EMODnet)');

            // External providers report data with a real observation date separate from import date
            $table->dateTime('observation_date')
                ->nullable()
                ->default(null)
                ->after('external_id')
                ->comment('Actual observation date as reported by the external provider');

            // External quality flag as received from provider (e.g. SeaDataNet QV codes: 1=good, 2=probably_good)
            $table->tinyInteger('external_quality_flag')
                ->nullable()
                ->default(null)
                ->after('observation_date')
                ->comment('Quality flag from provider (SeaDataNet L20: 1=good, 2=probably_good, 3=bad, 4=changed, 0=no_qc)');

            // Make chlore_residuel and plomb nullable to support partial external datasets
            $table->decimal('chlore_residuel', 8, 3)->nullable()->change();
            $table->decimal('plomb', 10, 3)->nullable()->change();

            // Index for efficient idempotent lookup
            $table->index(['provider', 'external_id'], 'mesures_qualites_provider_external_id_idx');
        });
    }

    public function down(): void
    {
        Schema::table('mesures_qualites', function (Blueprint $table) {
            $table->dropIndex('mesures_qualites_provider_external_id_idx');
            $table->dropColumn(['provider', 'external_id', 'observation_date', 'external_quality_flag']);
            // Note: reverting nullable → not-null requires backfilling, skipped in down()
        });
    }
};
