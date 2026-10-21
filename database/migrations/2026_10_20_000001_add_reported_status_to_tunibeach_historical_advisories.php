<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tunibeach_historical_advisory_points', function (Blueprint $table): void {
            $table->string('reported_status', 20)->default('restricted')->after('point_name');
        });
    }

    public function down(): void
    {
        Schema::table('tunibeach_historical_advisory_points', function (Blueprint $table): void {
            $table->dropColumn('reported_status');
        });
    }
};
