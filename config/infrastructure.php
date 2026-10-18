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

    /*
    |--------------------------------------------------------------------------
    | Modèle de risque de panne à 30 jours
    |--------------------------------------------------------------------------
    |
    | Les poids multiplient des caractéristiques normalisées entre 0 et 1.
    | Les seuils sont exprimés en pourcentage de probabilité.
    |
    */
    'risk' => [
        'bias' => -3.2,
        'weights' => [
            'age_ratio' => 1.0,
            'maintenance_delay' => 1.5,
            'etat' => 2.0,
            'interventions' => 0.8,
            'incidents' => 1.4,
            'criticite' => 0.7,
            'flapping' => 1.2,
        ],
        'thresholds' => [
            'moyen' => 25,
            'eleve' => 50,
            'critique' => 75,
        ],
        'normalizers' => [
            'days_per_year' => 365.2425,
            'maintenance_delay_days' => 90,
            'interventions_12_months' => 10,
            'incidents_12_months' => 5,
            'flapping_90_days' => 10,
        ],
        'periods' => [
            'interventions_months' => 12,
            'incidents_months' => 12,
            'flapping_days' => 90,
        ],
        'criticality' => [
            'faible' => 0.0,
            'moyen' => 0.5,
            'critique' => 1.0,
        ],
        'etat' => [
            'En service' => 0.0,
            'En maintenance' => 0.5,
            'Hors service' => 1.0,
        ],
        'recommendation' => [
            'max_factors' => 2,
            'days' => 7,
        ],
    ],
];
