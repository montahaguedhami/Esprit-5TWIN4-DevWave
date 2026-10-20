<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('techniciens', function (Blueprint $table) {
            if (! Schema::hasColumn('techniciens', 'user_id')) {
                $table->foreignId('user_id')->nullable()->unique()->constrained('users')->nullOnDelete();
            }
        });

        Schema::table('action_correctives', function (Blueprint $table) {
            if (! Schema::hasColumn('action_correctives', 'technicien_id')) {
                $table->foreignId('technicien_id')->nullable()->constrained('techniciens')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('action_correctives', function (Blueprint $table) {
            if (Schema::hasColumn('action_correctives', 'technicien_id')) {
                $table->dropConstrainedForeignId('technicien_id');
            }
        });

        Schema::table('techniciens', function (Blueprint $table) {
            if (Schema::hasColumn('techniciens', 'user_id')) {
                $table->dropConstrainedForeignId('user_id');
            }
        });
    }
};
