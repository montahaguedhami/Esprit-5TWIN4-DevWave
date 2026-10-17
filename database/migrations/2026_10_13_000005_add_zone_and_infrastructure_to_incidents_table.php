<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('incidents', function (Blueprint $table) {
            $table->foreignId('zone_id')
                ->nullable()
                ->after('localisation')
                ->constrained('zones')
                ->nullOnDelete();

            $table->foreignId('infrastructure_id')
                ->nullable()
                ->after('zone_id')
                ->constrained('infrastructures')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('incidents', function (Blueprint $table) {
            $table->dropForeign(['zone_id']);
            $table->dropForeign(['infrastructure_id']);
            $table->dropColumn(['zone_id', 'infrastructure_id']);
        });
    }
};
