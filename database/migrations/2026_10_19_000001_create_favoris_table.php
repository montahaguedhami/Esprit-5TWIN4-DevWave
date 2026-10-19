<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Projets sauvegardés par un citoyen.
     *
     * Le favori est le signal d'intérêt EXPLICITE du citoyen : il l'a choisi
     * lui-même, contrairement à une consultation qui n'est qu'implicite.
     *
     * Cette table n'existait pas dans le dépôt — vérifié avant création
     * (recherche sur les tables `favor*`, `categor*`, `tag*` : aucun résultat).
     */
    public function up(): void
    {
        if (Schema::hasTable('favoris')) {
            return;
        }

        Schema::create('favoris', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('projet_id')->constrained('projets')->cascadeOnDelete();

            $table->timestamps();

            // Un citoyen ne peut sauvegarder un projet qu'UNE fois. La contrainte
            // est portée par la base elle-même : un double clic, un appel
            // répété ou deux requêtes simultanées ne peuvent pas créer deux
            // lignes, même si le contrôleur était contourné.
            $table->unique(['user_id', 'projet_id'], 'favoris_user_projet_unique');

            // « Mes favoris » est paginé et trié par date d'ajout.
            $table->index(['user_id', 'created_at'], 'favoris_user_created_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('favoris');
    }
};
