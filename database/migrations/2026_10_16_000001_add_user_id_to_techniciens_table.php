<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Lie chaque fiche technicien à un compte utilisateur (1:1).
     */
    public function up(): void
    {
        Schema::table('techniciens', function (Blueprint $table) {
            $table->foreignId('user_id')
                ->nullable()
                ->unique()
                ->after('id')
                ->constrained('users')
                ->nullOnDelete();
        });

        // Migration de données : rattache les fiches existantes aux comptes « technicien » portant exactement le même nom
        $comptes = DB::table('users')
            ->join('role_user', 'role_user.user_id', '=', 'users.id')
            ->join('roles', 'roles.id', '=', 'role_user.role_id')
            ->where('roles.slug', 'technicien')
            ->select('users.id', 'users.name')
            ->get();

        foreach ($comptes as $compte) {
            $technicien = DB::table('techniciens')
                ->whereNull('user_id')
                ->where('nom', $compte->name)
                ->orderBy('id')
                ->first();

            if ($technicien && ! DB::table('techniciens')->where('user_id', $compte->id)->exists()) {
                DB::table('techniciens')->where('id', $technicien->id)->update(['user_id' => $compte->id]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('techniciens', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropUnique(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};
