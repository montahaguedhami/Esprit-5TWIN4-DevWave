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
        if (Schema::hasTable('projets')) {
            Schema::table('projets', function (Blueprint $table) {
                $table->string('statut', 50)->change();
            });

            if (! Schema::hasIndex('projets', 'projets_statut_index')) {
                Schema::table('projets', function (Blueprint $table) {
                    $table->index('statut');
                });
            }

            if (! Schema::hasIndex('projets', 'projets_latitude_longitude_index')) {
                Schema::table('projets', function (Blueprint $table) {
                    $table->index(['latitude', 'longitude']);
                });
            }

            return;
        }

        Schema::create('projets', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->text('description')->nullable();
            $table->date('date_debut');
            $table->date('date_fin')->nullable();
            $table->decimal('budget', 15, 2);
            $table->string('statut', 50);
            $table->string('adresse')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->timestamps();
            
            // Index pour améliorer les performances
            $table->index('statut');
            $table->index(['latitude', 'longitude']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projets');
    }
};
