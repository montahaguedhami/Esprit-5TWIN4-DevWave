<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('zones', function (Blueprint $table): void {
            $table->decimal('latitude', 10, 7)->nullable()->after('localisation');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            $table->decimal('superficie_km2', 8, 2)->nullable()->after('longitude');
            $table->unsignedInteger('population_desservie')->nullable()->after('superficie_km2');
            $table->string('statut')->default('active')->after('population_desservie');
            $table->foreignId('responsable_id')
                ->nullable()
                ->after('statut')
                ->constrained('users')
                ->nullOnDelete();
            $table->softDeletes()->after('updated_at');
        });

        Schema::table('infrastructures', function (Blueprint $table): void {
            $table->decimal('latitude', 10, 7)->nullable()->after('localisation');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            $table->decimal('capacite', 12, 2)->nullable()->after('longitude');
            $table->string('unite_capacite')->nullable()->after('capacite');
            $table->string('fabricant')->nullable()->after('unite_capacite');
            $table->string('reference')->nullable()->unique()->after('fabricant');
            $table->date('date_derniere_maintenance')->nullable()->after('date_installation');
            $table->date('date_prochaine_maintenance')->nullable()->after('date_derniere_maintenance');
            $table->unsignedSmallInteger('duree_vie_annees')->nullable()->after('date_prochaine_maintenance');
            $table->string('niveau_criticite')->default('moyen')->after('duree_vie_annees');
            $table->softDeletes()->after('updated_at');
        });
    }

    public function down(): void
    {
        Schema::table('infrastructures', function (Blueprint $table): void {
            $table->dropSoftDeletes();
            $table->dropColumn([
                'latitude',
                'longitude',
                'capacite',
                'unite_capacite',
                'fabricant',
                'reference',
                'date_derniere_maintenance',
                'date_prochaine_maintenance',
                'duree_vie_annees',
                'niveau_criticite',
            ]);
        });

        Schema::table('zones', function (Blueprint $table): void {
            $table->dropForeign(['responsable_id']);
            $table->dropSoftDeletes();
            $table->dropColumn([
                'latitude',
                'longitude',
                'superficie_km2',
                'population_desservie',
                'statut',
                'responsable_id',
            ]);
        });
    }
};
