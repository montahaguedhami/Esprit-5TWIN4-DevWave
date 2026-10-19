<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Citoyen funding requests, waiting for a manager decision.
     *
     * Deliberately separate from `financements`: a request is only a proposal
     * and must never move the financed total before the payment is confirmed.
     */
    public function up(): void
    {
        if (Schema::hasTable('demandes_financement')) {
            return;
        }

        Schema::create('demandes_financement', function (Blueprint $table) {
            $table->id();
            $table->foreignId('projet_id')->constrained('projets')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            $table->decimal('montant', 15, 2);
            $table->string('devise', 3)->default('TND');

            $table->string('statut', 30)->default('en_attente');
            $table->index('statut');

            // Manager who ruled on the request.
            $table->foreignId('decided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('decided_at')->nullable();
            $table->text('motif_refus')->nullable();

            $table->timestamps();

            // The manager list filters on (statut, projet) and sorts by date.
            $table->index(['projet_id', 'statut']);
            $table->index(['user_id', 'statut']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('demandes_financement');
    }
};
