<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Removes the two gateway-specific columns and replaces them with a generic
     * audit field.
     *
     * There is no payment provider in this project, so `flouci_payment_id` and
     * `flouci_event_id` could never be filled by anything honest. `motif`
     * carries the reason of the simulated outcome instead, which is what the
     * receipt and the audit trail actually need.
     *
     * The `paiements` table holds no rows in a freshly developed database; the
     * columns are dropped with their indexes so no data is lost either way.
     */
    public function up(): void
    {
        Schema::table('paiements', function (Blueprint $table) {
            if (Schema::hasColumn('paiements', 'flouci_payment_id')) {
                $table->dropUnique(['flouci_payment_id']);
                $table->dropColumn('flouci_payment_id');
            }

            if (Schema::hasColumn('paiements', 'flouci_event_id')) {
                $table->dropUnique(['flouci_event_id']);
                $table->dropColumn('flouci_event_id');
            }

            if (! Schema::hasColumn('paiements', 'motif')) {
                $table->text('motif')->nullable()->after('statut');
            }
        });
    }

    public function down(): void
    {
        Schema::table('paiements', function (Blueprint $table) {
            if (Schema::hasColumn('paiements', 'motif')) {
                $table->dropColumn('motif');
            }

            if (! Schema::hasColumn('paiements', 'flouci_payment_id')) {
                $table->string('flouci_payment_id')->nullable()->unique();
            }

            if (! Schema::hasColumn('paiements', 'flouci_event_id')) {
                $table->string('flouci_event_id', 64)->nullable()->unique();
            }
        });
    }
};
