<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Historique des projets réellement ouverts par un citoyen.
     *
     * Une consultation est un signal d'intérêt IMPLICITE, plus faible qu'un
     * favori : le citoyen n'a rien demandé, il a seulement regardé.
     *
     * MINIMISATION DES DONNÉES — c'est volontaire :
     *   - aucun champ autre que la paire (citoyen, projet) et son horodatage ;
     *   - PAS d'adresse IP, PAS de géolocalisation, PAS de user-agent,
     *     PAS de fingerprint navigateur, PAS de données d'écran.
     * Rien de tout cela n'est nécessaire au classement, donc rien n'est collecté.
     *
     * La date d'interaction est `created_at` : c'est elle qui sera utilisée
     * comme horodatage fiable pour la reconstruction du dataset de la V2.
     *
     * Table inexistante dans le dépôt avant cette création (vérifié).
     */
    public function up(): void
    {
        if (Schema::hasTable('consultation_projets')) {
            return;
        }

        Schema::create('consultation_projets', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('projet_id')->constrained('projets')->cascadeOnDelete();

            $table->timestamps();

            // ── Regroupement des consultations répétées ─────────────────────
            // Un rafraîchissement (F5) ou un rechargement d'icône ne doit pas
            // fabriquer un signal d'intérêt. On regroupe les consultations
            // d'une même paire (citoyen, projet) dans une fenêtre de
            // 30 minutes, matérialisée par ce « bucket » (index de fenêtre).
            //
            // `consultation_bucket_size()` est LA référence unique de la
            // fenêtre : la règle est écrite une seule fois, dans le modèle,
            // pour que l'application, la base et les futurs tests partagent
            // exactement la même définition.
            $table->unsignedBigInteger('bucket')->default(0);

            // L'unicité est portée par la base : deux écritures concurrentes
            // dans la même fenêtre ne peuvent pas créer deux lignes.
            $table->unique(
                ['user_id', 'projet_id', 'bucket'],
                'consultation_user_projet_bucket_unique'
            );

            // Historique du citoyen : « récemment consultés », du plus récent
            // au plus ancien.
            $table->index(['user_id', 'created_at'], 'consultation_user_created_index');

            // Agrégats du moteur : compter les consultations d'un citoyen,
            // par projet puis par zone.
            $table->index(['projet_id'], 'consultation_projet_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consultation_projets');
    }
};
