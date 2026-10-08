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
        Schema::create('action_correctives', function (Blueprint $table) {
            $table->id();
            $table->foreignId('incident_id')->constrained('incidents')->cascadeOnDelete();
            $table->string('titre');
            $table->text('description')->nullable();
            $table->string('responsable')->nullable();
            $table->date('date_prevue')->nullable();
            $table->timestamp('date_realisation')->nullable();
            $table->string('statut')->default('a_faire');
            $table->text('resultat')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('action_correctives');
    }
};
