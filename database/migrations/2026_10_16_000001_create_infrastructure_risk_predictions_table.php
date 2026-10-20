<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('infrastructure_risk_predictions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('infrastructure_id')
                ->constrained('infrastructures')
                ->cascadeOnDelete();
            $table->unsignedTinyInteger('probability');
            $table->string('level');
            $table->json('factors');
            $table->timestamp('computed_at');
            $table->unique(['infrastructure_id', 'computed_at'], 'risk_pred_infra_computed_unique');
            $table->index(['level', 'computed_at']);
        });

        Schema::create('infrastructure_risk_settings', function (Blueprint $table): void {
            $table->unsignedTinyInteger('id')->primary();
            $table->json('parameters');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('infrastructure_risk_settings');
        Schema::dropIfExists('infrastructure_risk_predictions');
    }
};
