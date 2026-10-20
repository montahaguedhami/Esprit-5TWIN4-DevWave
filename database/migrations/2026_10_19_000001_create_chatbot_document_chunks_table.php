<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Index documentaire du chatbot.
     *
     * Chaque ligne est un passage (chunk) extrait d'un document autorisé
     * stocké sous docs/chatbot/. La recherche textuelle s'effectue sur
     * cette table. Aucune donnée privée de citoyen n'y est jamais indexée.
     */
    public function up(): void
    {
        if (Schema::hasTable('chatbot_document_chunks')) {
            return;
        }

        Schema::create('chatbot_document_chunks', function (Blueprint $table) {
            $table->id();
            $table->string('document_id');      // ex: guide-projets
            $table->string('document_title');   // ex: Guide des projets
            $table->integer('chunk_index');     // position dans le document
            $table->text('content');            // texte du passage
            $table->string('section', 100)->nullable(); // section détectée (H1/H2)
            $table->timestamps();

            $table->index(['document_id', 'chunk_index']);
            $table->index('document_id');

            // Index full-text uniquement sur MySQL (SQLite ne le supporte pas)
            if (DB::getDriverName() !== 'sqlite') {
                $table->fulltext('content');
            } // recherche full-text MySQL
        });
    }

    /**
     * Reverse the migrations.
     *
     * Cette méthode est volontairement restrictive : la suppression de la
     * table n'est pas automatique pour éviter toute perte accidentelle de
     * l'index documentaire. Elle doit être invoquée explicitement.
     */
    public function down(): void
    {
        // Non destructive par défaut : ne pas drop la table automatiquement.
        // Schema::dropIfExists('chatbot_document_chunks');
    }
};
