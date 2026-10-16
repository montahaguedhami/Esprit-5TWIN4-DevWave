<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tracks the outcome of every external data synchronisation run per provider.
 *
 * One row per sync attempt. The importer writes a row whether the sync
 * succeeded or failed, so operators can diagnose failures and monitor volumes.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('provider_sync_logs', function (Blueprint $table) {
            $table->id();
            $table->string('provider', 50)->comment('Provider slug: emodnet, wqp, …');
            $table->string('dataset_id', 100)->nullable()->comment('Provider dataset identifier (e.g. EUT_MED_TIMESERIES)');
            $table->enum('status', ['running', 'success', 'partial', 'failed', 'no_data'])->default('running');
            $table->integer('records_fetched')->default(0)->comment('Raw records received from the API');
            $table->integer('records_imported')->default(0)->comment('New rows inserted');
            $table->integer('records_skipped')->default(0)->comment('Rows skipped (duplicate external_id)');
            $table->integer('records_failed')->default(0)->comment('Rows that raised a DB or validation error');
            $table->text('error_message')->nullable()->comment('Last error message if status = failed');
            $table->json('meta')->nullable()->comment('Provider-specific metadata (e.g. time range queried)');
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();

            $table->index(['provider', 'status']);
            $table->index('started_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provider_sync_logs');
    }
};
