# Paiements simulés — AquaSecure

## Principe

Aucune transaction financière réelle n'a lieu dans ce module. Les paiements sont
simulés localement par le service `SimulatedPaymentProvider`. Aucun appel réseau
n'est effectué vers une passerelle bancaire et aucune donnée de carte bancaire
n'est collectée.

## Statuts d'un paiement

| Statut | Signification |
|--------|---------------|
| `en_attente` | Tentative ouverte, en attente du résultat de la simulation. |
| `confirme` | Simulation réussie : un Financement a été créé. |
| `echoue` | Simulation refusée ou erreur technique. Aucun montant n'a bougé. |
| `annule` | Annulée par le citoyen. Aucun montant n'a bougé. |

## Reçu

Après une simulation réussie, le citoyen peut consulter et télécharger un reçu.
Ce reçu porte la mention : « Simulation — aucune transaction financière réelle ».
Il s'agit d'une attestation d'enregistrement académique, pas d'une preuve
bancaire ni d'un justificatif comptable.

## Idempotence

Une double confirmation de la même demande ne crée pas de second Financement.
La tentative de paiement existante est réutilisée.
