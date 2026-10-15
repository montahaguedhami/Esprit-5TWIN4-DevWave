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
];
