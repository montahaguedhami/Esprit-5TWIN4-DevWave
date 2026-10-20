<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Maintenance préventive
    |--------------------------------------------------------------------------
    |
    | Périodicité (en mois) entre deux maintenances préventives selon le type
    | d'infrastructure, et spécialité de technicien attendue pour chaque type.
    |
    */

    'periodicite_mois' => [
        'Canalisation' => 12,
        'Réservoir' => 6,
        'Station de pompage' => 3,
        'Station de traitement' => 3,
        'Forage' => 6,
        'Vanne' => 12,
    ],

    // Périodicité par défaut si le type n'est pas listé
    'periodicite_defaut_mois' => 6,

    // Une infrastructure critique est entretenue deux fois plus souvent
    'facteur_criticite' => [
        'faible' => 1.0,
        'moyen' => 1.0,
        'critique' => 0.5,
    ],

    'specialite_par_type' => [
        'Canalisation' => 'Canalisations',
        'Réservoir' => 'Traitement de l\'eau',
        'Station de pompage' => 'Pompage',
        'Station de traitement' => 'Traitement de l\'eau',
        'Forage' => 'Pompage',
        'Vanne' => 'Canalisations',
    ],

    // Sans infrastructure, la spécialité attendue est déduite de la description (mots-clés en minuscules)
    'mots_cles_specialite' => [
        'Pompage' => ['pompe', 'pompage', 'forage', 'relevage', 'surpresseur', 'débit'],
        'Électricité' => ['électrique', 'electrique', 'tableau', 'armoire', 'moteur', 'câble', 'disjoncteur'],
        'Traitement de l\'eau' => ['chlore', 'traitement', 'turbidité', 'qualité', 'désinfection', 'réservoir', 'filtre'],
        'Compteurs' => ['compteur', 'relève', 'consommation'],
        'Canalisations' => ['fuite', 'canalisation', 'conduite', 'vanne', 'tuyau', 'rupture', 'branchement'],
    ],

    // Délai maximal (secondes) laissé au modèle pour une recommandation interactive
    'ia_timeout' => (int) env('MAINTENANCE_IA_TIMEOUT', 30),

    // Horizon (jours) : maintenances dues d'ici N jours sont planifiées
    'horizon_jours' => (int) env('MAINTENANCE_HORIZON_JOURS', 7),

    // Si aucun technicien n'est libre le jour prévu, on cherche sur les N jours suivants
    'recherche_jours' => 7,

    // Charge comptée sur les N prochains jours pour équilibrer les affectations
    'fenetre_charge_jours' => 30,

];
