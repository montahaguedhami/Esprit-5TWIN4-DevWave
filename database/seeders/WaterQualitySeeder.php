<?php

namespace Database\Seeders;

use App\Models\MesureQualite;
use App\Models\PointMesure;
use App\Services\WaterQualityService;
use Illuminate\Database\Seeder;

class WaterQualitySeeder extends Seeder
{
    public function run(): void
    {
        $service = app(WaterQualityService::class);

        $records = [
            [
                'reference' => 'WQR-2026-108',
                'code' => 'TUN-BEL-04',
                'nom' => 'Station Belvédère - Point #04',
                'zone' => 'Tunis Centre',
                'adresse' => 'Belvédère, Tunis',
                'latitude' => 36.8190,
                'longitude' => 10.1657,
                'type' => 'Station',
                'date_mesure' => '2026-09-26 08:30:00',
                'ph' => 7.35,
                'turbidite' => 0.38,
                'chlore_residuel' => 0.85,
                'plomb' => 2.1,
                'nitrates' => 12.4,
                'is_verified' => true,
                'verifier' => 'Laboratoire Central INNORPI',
            ],
            [
                'reference' => 'WQR-2026-107',
                'code' => 'ARI-PUIT-01',
                'nom' => 'Puits d\'Alimentation Ariana-Nord',
                'zone' => 'Ariana',
                'adresse' => 'Ariana-Nord',
                'latitude' => 36.8665,
                'longitude' => 10.1647,
                'type' => 'Puits',
                'date_mesure' => '2026-09-25 14:15:00',
                'ph' => 7.82,
                'turbidite' => 1.25,
                'chlore_residuel' => 0.35,
                'plomb' => 6.8,
                'nitrates' => 28.1,
                'is_verified' => true,
                'verifier' => 'Unité Mobile Qualité #2',
            ],
            [
                'reference' => 'WQR-2026-106',
                'code' => 'BA-RES-01',
                'nom' => 'Réservoir Industriel Ben Arous',
                'zone' => 'Ben Arous',
                'adresse' => 'Ben Arous',
                'latitude' => 36.7533,
                'longitude' => 10.2286,
                'type' => 'Réservoir',
                'date_mesure' => '2026-09-24 11:00:00',
                'ph' => 8.65,
                'turbidite' => 2.45,
                'chlore_residuel' => 0.12,
                'plomb' => 18.2,
                'nitrates' => 42.5,
                'is_verified' => false,
                'verifier' => 'En attente de vérification',
            ],
            [
                'reference' => 'WQR-2026-105',
                'code' => 'SOU-PORT-01',
                'nom' => 'Réseau Sousse Port-Nord',
                'zone' => 'Sousse',
                'adresse' => 'Port-Nord, Sousse',
                'latitude' => 35.8245,
                'longitude' => 10.6346,
                'type' => 'Réseau',
                'date_mesure' => '2026-09-24 09:40:00',
                'ph' => 7.20,
                'turbidite' => 0.22,
                'chlore_residuel' => 1.10,
                'plomb' => 1.4,
                'nitrates' => 9.2,
                'is_verified' => true,
                'verifier' => 'Laboratoire Régional Sousse',
            ],
        ];

        foreach ($records as $record) {
            $point = PointMesure::updateOrCreate(
                ['code' => $record['code']],
                [
                    'nom' => $record['nom'],
                    'zone' => $record['zone'],
                    'adresse' => $record['adresse'],
                    'latitude' => $record['latitude'],
                    'longitude' => $record['longitude'],
                    'type' => $record['type'],
                    'statut' => 'actif',
                ]
            );

            $values = array_intersect_key($record, array_flip([
                'ph',
                'turbidite',
                'chlore_residuel',
                'plomb',
                'nitrates',
            ]));
            $assessment = $service->assess($values);

            MesureQualite::updateOrCreate(
                ['reference' => $record['reference']],
                array_merge($values, [
                    'point_mesure_id' => $point->id,
                    'date_mesure' => $record['date_mesure'],
                    'is_verified' => $record['is_verified'],
                    'verifier' => $record['verifier'],
                    'status' => $assessment['status'],
                    'overall_compliance' => $assessment['overall_compliance'],
                ])
            );
        }
    }
}
