# Guide des projets — AquaSecure

## Présentation

Le module Projets et Financements d'AquaSecure présente les projets d'infrastructure
eau ouverts au financement citoyen. Chaque projet dispose d'un nom, d'une description,
d'un budget cible, d'un statut et d'une localisation géographique.

## Statuts de projet

Les projets peuvent avoir l'un des statuts suivants :

- `planifie` — le projet est prévu mais pas encore commencé.
- `en_cours` — le projet est en chantier.
- `termine` — le projet est achevé.
- `suspendu` — le projet est temporairement interrompu.
- `annule` — le projet a été annulé.

Seuls les projets en statut `planifie` ou `en_cours` avec un budget disponible
supérieur à zéro sont ouverts au financement citoyen.

## Budget d'un projet

Le budget d'un projet représente le montant total visé pour sa réalisation.

Le budget disponible est calculé ainsi :

    budget_disponible = budget − total_financé − montant_réservé

Où :
- `total_financé` est la somme des contributions confirmées (statut `payee`).
- `montant_réservé` est la somme des demandes acceptées par le manager mais pas
  encore payées par le citoyen.

Ce calcul est effectué par le service métier `FinancementService` et réutilisé
par le service `BudgetReportService`. Le chatbot ne recalcule jamais ce budget
lui-même : il interroge l'outil métier `get_project_funding_status`.

## Zones et localisation

Chaque projet est associé à une ou plusieurs zones géographiques. Certains projets
disposent de coordonnées GPS (latitude/longitude) et s'affichent sur la carte
du gestionnaire.

## Recommandation de projets

Un moteur de recommandation V1 (déterministe, basé sur des règles) propose aux
citoyens des projets adaptés à leur historique : zones explorées, projets déjà
soutenus, favoris et consultations récentes. Ce moteur ne fait pas de machine
learning.
