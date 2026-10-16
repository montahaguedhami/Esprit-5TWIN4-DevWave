<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('point_mesures', function (Blueprint $table) {
            $table->foreignId('zone_id')
                ->nullable()
                ->after('zone')
                ->constrained('zones')
                ->nullOnDelete();
        });

        // Migration de données : réconciliation insensible à la casse entre la colonne texte 'zone' et 'zones.nom' / 'zones.localisation'
        $points = DB::table('point_mesures')->whereNotNull('zone')->get();
        $zones = DB::table('zones')->get();

        foreach ($points as $point) {
            $zoneText = mb_strtolower(trim((string) $point->zone));
            if ($zoneText === '') {
                continue;
            }

            $matchedZone = $zones->first(function ($zone) use ($zoneText) {
                $nom = mb_strtolower(trim((string) $zone->nom));
                $localisation = mb_strtolower(trim((string) $zone->localisation));

                return $nom === $zoneText
                    || str_contains($nom, $zoneText)
                    || str_contains($zoneText, $nom)
                    || $localisation === $zoneText
                    || str_contains($localisation, $zoneText)
                    || str_contains($zoneText, $localisation);
            });

            if ($matchedZone) {
                DB::table('point_mesures')
                    ->where('id', $point->id)
                    ->update(['zone_id' => $matchedZone->id]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('point_mesures', function (Blueprint $table) {
            $table->dropForeign(['zone_id']);
            $table->dropColumn('zone_id');
        });
    }
};
