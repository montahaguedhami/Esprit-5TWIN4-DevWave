<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('point_mesures', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('nom');
            $table->string('zone')->nullable();
            $table->string('adresse')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('type', 30)->default('Autre');
            $table->string('statut', 20)->default('actif');
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('point_mesures');
    }
};
