<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tunibeach_historical_advisory_points', function (Blueprint $table): void {
            $table->id();
            $table->date('reported_at');
            $table->string('governorate_name', 120);
            $table->string('point_name', 255);
            $table->string('source_name', 180);
            $table->text('source_url');
            $table->timestamps();
            $table->unique(['reported_at', 'governorate_name', 'point_name'], 'tunibeach_historical_point_unique');
            $table->index(['reported_at', 'governorate_name'], 'tunibeach_historical_gov_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tunibeach_historical_advisory_points');
    }
};
