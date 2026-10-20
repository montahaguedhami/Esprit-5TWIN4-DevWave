<?php

namespace App\Services\Chatbot\Tools;

use App\Http\Requests\StoreDemandeFinancementRequest;
use App\Models\DemandeFinancement;
use App\Services\Chatbot\ToolExecutor;

/**
 * Retourne les règles métier vérifiées du module Projets et Financements.
 *
 * Le montant minimum est lu depuis la source de vérité du code
 * (StoreDemandeFinancementRequest::MONTANT_MINIMUM), jamais dupliqué.
 */
class GetFundingRulesTool implements ToolExecutor
{
    public function name(): string
    {
        return 'get_funding_rules';
    }

    public function isAuthorized(mixed $user): bool
    {
        return $user !== null;
    }

    public function validateArguments(array $arguments): array
    {
        return [];
    }

    public function execute(mixed $user, array $validatedArguments): array
    {
        return [
            'montant_minimum_tnd' => (int) StoreDemandeFinancementRequest::MONTANT_MINIMUM,
            'devise' => 'TND',
            'statuts_projet_eligible' => DemandeFinancement::STATUTS_PROJET_ELIGIBLE,
            'statuts_demande' => DemandeFinancement::STATUTS,
            'statuts_paiement' => [
                'en_attente' => 'Tentative ouverte',
                'confirme' => 'Simulation réussie (contribution enregistrée)',
                'echoue' => 'Simulation refusée ou erreur technique',
                'annule' => 'Annulée par le citoyen',
            ],
            'regles' => [
                'un_projet_est_eligible_si_statut_planifie_ou_en_cours_et_budget_disponible_positif' => true,
                'le_montant_provient_toujours_de_la_base_de_donnees_pas_du_navigateur' => true,
                'une_demande_refusee_necessite_un_motif' => true,
                'le_paiement_est_simule_aucune_transaction_reelle' => true,
                'une_demande_deja_payee_ne_peut_pas_etre_payee_une_second_fois' => true,
            ],
            'workflow' => [
                '1_soumettre' => 'Le citoyen crée une demande en_attente (aucun montant déplacé).',
                '2_decision_manager' => 'Le manager accepte ou refuse la demande.',
                '3_reservation' => 'Si acceptée, le montant est réservé sur le budget du projet.',
                '4_confirmation_paiement' => 'Le citoyen confirme le paiement simulé.',
                '5_finalisation' => 'En cas de succès, un Financement est créé et la demande passe en payee.',
            ],
        ];
    }
}
