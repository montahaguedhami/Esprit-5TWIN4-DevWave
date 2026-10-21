<?php

/*
|--------------------------------------------------------------------------
| Paiement simulé — AquaSecure
|--------------------------------------------------------------------------
|
| Ce projet académique n'utilise AUCUNE passerelle de paiement externe.
| Aucune API bancaire n'est appelée, aucune donnée de carte n'est collectée
| et aucun transfert d'argent réel n'a lieu. Le résultat d'une contribution
| est produit localement par App\Services\Payments\SimulatedPaymentProvider.
|
| `driver` n'accepte que la valeur "simulated". C'est volontaire : toute autre
| valeur est refusée à la construction du provider, afin qu'aucun paiement
| réel ne puisse jamais être activé accidentellement par une variable
| d'environnement oubliée ou mal renseignée.
|
*/

return [

    'driver' => env('PAYMENT_DRIVER', 'simulated'),

    /*
    | Permet au citoyen de choisir le scénario (succès, refus, erreur,
    | annulation) sur la page de récapitulatif. C'est ce qui rend les cas
    | d'échec démontrables pendant une soutenance.
    |
    | Doit rester `false` en production : en dehors du développement et des
    | tests, le résultat est toujours un succès et le citoyen ne peut en
    | aucun cas influencer un statut.
    */
    'scenario_choix' => env('PAYMENT_SIMULATION_SCENARIO_CHOICE', false),

];
