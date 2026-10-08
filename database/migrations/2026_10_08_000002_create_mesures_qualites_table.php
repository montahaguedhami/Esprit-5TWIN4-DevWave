<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mesures_qualites', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 50)->unique();
            $table->foreignId('point_mesure_id')->constrained('point_mesures')->cascadeOnDelete();
            $table->dateTime('date_mesure');
            $table->decimal('ph', 5, 2);
            $table->decimal('turbidite', 8, 2);
            $table->decimal('chlore_residuel', 8, 3);
            $table->decimal('plomb', 10, 3);
            $table->decimal('nitrates', 8, 2);
            $table->boolean('is_verified')->default(false);
            $table->string('verifier')->nullable();
            $table->decimal('overall_compliance', 5, 2);
            $table->string('status', 20);
            $table->timestamps();

            $table->index(['point_mesure_id', 'date_mesure']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mesures_qualites');
    }
};
