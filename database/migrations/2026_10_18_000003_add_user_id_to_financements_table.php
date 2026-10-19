<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ties a confirmed funding back to the citizen who paid for it.
     *
     * Nullable and added without touching existing rows: financements created
     * by a manager before this feature simply keep a NULL citizen.
     */
    public function up(): void
    {
        if (Schema::hasColumn('financements', 'user_id')) {
            return;
        }

        Schema::table('financements', function (Blueprint $table) {
            $table->foreignId('user_id')
                ->nullable()
                ->after('projet_id')
                ->constrained('users')
                ->nullOnDelete();

            $table->index('user_id');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('financements', 'user_id')) {
            return;
        }

        Schema::table('financements', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
        });
    }
};
