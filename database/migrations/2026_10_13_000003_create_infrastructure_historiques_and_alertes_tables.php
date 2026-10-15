<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('infrastructure_historiques', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('infrastructure_id')
                ->constrained('infrastructures')
                ->cascadeOnDelete();
            $table->string('ancien_etat')->nullable();
            $table->string('nouvel_etat');
            $table->text('commentaire')->nullable();
            $table->string('user_name')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('infrastructure_alertes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('infrastructure_id')
                ->constrained('infrastructures')
                ->cascadeOnDelete();
            $table->string('type');
            $table->text('message');
            $table->boolean('resolue')->default(false);
            $table->timestamps();

            $table->index(['infrastructure_id', 'type', 'resolue']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('infrastructure_alertes');
        Schema::dropIfExists('infrastructure_historiques');
    }
};
