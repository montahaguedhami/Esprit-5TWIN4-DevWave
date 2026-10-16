<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $types = [
            'canalisation' => 'Canalisation',
            'réservoir' => 'Réservoir',
            'reservoir' => 'Réservoir',
            'station de pompage' => 'Station de pompage',
            'station pompage' => 'Station de pompage',
            'pompage' => 'Station de pompage',
            'station_pompage' => 'Station de pompage',
            'station de traitement' => 'Station de traitement',
            'station traitement' => 'Station de traitement',
            'traitement' => 'Station de traitement',
            'station_traitement' => 'Station de traitement',
            'forage' => 'Forage',
            'vanne' => 'Vanne',
        ];

        $etats = [
            'en service' => 'En service',
            'en_service' => 'En service',
            'service' => 'En service',
            'actif' => 'En service',
            'ok' => 'En service',
            'en maintenance' => 'En maintenance',
            'en_maintenance' => 'En maintenance',
            'maintenance' => 'En maintenance',
            'en surveillance' => 'En maintenance',
            'surveillance' => 'En maintenance',
            'hors service' => 'Hors service',
            'hors_service' => 'Hors service',
            'hs' => 'Hors service',
            'panne' => 'Hors service',
            'arret' => 'Hors service',
            'arrêt' => 'Hors service',
        ];

        DB::table('infrastructures')
            ->select(['id', 'type', 'etat'])
            ->orderBy('id')
            ->chunkById(500, function ($infrastructures) use ($types, $etats): void {
                foreach ($infrastructures as $infrastructure) {
                    $rawType = (string) ($infrastructure->type ?? '');
                    $rawEtat = (string) ($infrastructure->etat ?? '');

                    $typeKey = mb_strtolower(trim($rawType), 'UTF-8');
                    $etatKey = mb_strtolower(trim($rawEtat), 'UTF-8');

                    $normalizedType = $types[$typeKey] ?? 'Canalisation';
                    $normalizedEtat = $etats[$etatKey] ?? 'En service';

                    if ($rawType !== $normalizedType || $rawEtat !== $normalizedEtat) {
                        DB::table('infrastructures')
                            ->where('id', $infrastructure->id)
                            ->update([
                                'type' => $normalizedType,
                                'etat' => $normalizedEtat,
                            ]);
                    }
                }
            });
    }

    public function down(): void
    {
        // Les valeurs sont canonicalisées et ne peuvent pas être restaurées sans perte d’information.
    }
};
