<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Store status as a plain string so provider states (e.g. no_data)
        // are never blocked by a fixed database ENUM/check constraint.
        Schema::table('provider_sync_logs', function (Blueprint $table) {
            $table->string('status', 20)->default('running')->change();
        });
    }

    public function down(): void
    {
        // No safe revert without enum metadata.
    }
};