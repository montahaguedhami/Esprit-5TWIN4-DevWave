<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Pondérations du calcul du score de santé d'une infrastructure
    |--------------------------------------------------------------------------
    |
    | Total des poids = 1.00 (100%)
    |
    */
    'sante' => [
        'poids_etat' => 0.40,
        'poids_age' => 0.25,
        'poids_retard_maintenance' => 0.20,
        'poids_interventions' => 0.15,
        'alerte_jours_avant' => 7,
    ],

    'risk' => [
        'bias' => -3.8,
        'weights' => [
            'etat' => 2.4,
            'age_ratio' => 1.2,
            'maintenance_delay' => 1.7,
            'interventions' => 0.8,
            'incidents' => 1.4,
            'criticite' => 0.9,
            'flapping' => 1.0,
        ],
        'etat' => [
            'En service' => 0.0,
            'En maintenance' => 0.45,
            'Hors service' => 1.0,
        ],
        'criticality' => [
            'faible' => 0.15,
            'moyen' => 0.5,
            'critique' => 1.0,
        ],
        'normalizers' => [
            'days_per_year' => 365.25,
            'maintenance_delay_days' => 180,
            'interventions_12_months' => 6,
            'incidents_12_months' => 4,
            'flapping_90_days' => 4,
        ],
        'periods' => [
            'interventions_months' => 12,
            'incidents_months' => 12,
            'flapping_days' => 90,
        ],
        'thresholds' => [
            'moyen' => 30,
            'eleve' => 55,
            'critique' => 75,
        ],
        'recommendation' => [
            'max_factors' => 2,
            'days' => 14,
        ],
    ],
];
