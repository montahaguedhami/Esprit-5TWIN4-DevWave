# Règles de financement — AquaSecure

## Montant minimum

Le montant minimum d'une contribution citoyenne est de **1 000 TND**.
Cette valeur est définie dans `StoreDemandeFinancementRequest::MONTANT_MINIMUM`
et constitue la source de vérité. Aucun montant inférieur n'est accepté.

## Conditions d'éligibilité d'un projet

Un projet peut recevoir une demande de financement citoyen si et seulement si :

1. Son statut est `planifie` ou `en_cours` ;
2. Son budget disponible est strictement positif.

Les projets `termine`, `suspendu` et `annule` sont exclus du financement citoyen.

## Workflow d'une demande de financement

1. Le citoyen sélectionne un projet éligible et saisit un montant (≥ 1 000 TND).
2. Une demande est créée avec le statut `en_attente`. Aucun montant n'est déplacé.
3. Un gestionnaire (manager) examine la demande et peut l'accepter ou la refuser.
4. Si acceptée, le montant est réservé sur le budget du projet.
5. Le citoyen confirme le paiement (simulé) pour finaliser la contribution.
6. En cas de succès, un enregistrement `Financement` est créé et la demande passe
   en statut `payee`.

## Refus de demande

Un refus doit obligatoirement être accompagné d'un motif. Le motif est affiché
au citoyen. Une demande refusée ne peut plus être payée.

## Réservation de budget

Lorsqu'un manager accepte une demande, le montant est réservé sur le projet.
Plusieurs demandes acceptées ne peuvent pas ensemble dépasser le budget restant.
La réservation est levée si le citoyen ne confirme pas le paiement (échec ou
annulation de la simulation).

## Paiement simulé

Tous les paiements dans ce module sont simulés : aucune passerelle bancaire,
aucune carte de crédit, aucun argent réel. Le résultat est produit localement
par `SimulatedPaymentProvider`. Le reçu généré porte la mention expresse qu'il
s'agit d'une simulation à des fins académiques.
