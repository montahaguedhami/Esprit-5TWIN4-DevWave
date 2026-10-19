<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Payment attempts against an approved funding request.
     *
     * A row is created when the citizen confirms the contribution and is only
     * marked confirmed once the local simulation reported success.
     */
    public function up(): void
    {
        if (Schema::hasTable('paiements')) {
            return;
        }

        Schema::create('paiements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('demande_id')->constrained('demandes_financement')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('projet_id')->constrained('projets')->cascadeOnDelete();

            $table->decimal('montant', 15, 2);
            $table->string('devise', 3)->default('TND');
            $table->string('statut', 30)->default('en_attente');
            $table->index('statut');

            // Our own reference, unique so a double click cannot open two flows.
            $table->string('reference_interne', 64)->unique();

            // Why the simulation ended the way it did (audit trail).
            $table->text('motif')->nullable();

            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();

            $table->index(['projet_id', 'statut']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paiements');
    }
};
