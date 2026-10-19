# Financement citoyen avec paiement simulé

> **Avertissement.** Ce module est une **démonstration académique**. Aucun argent réel
> n'est débité ou transféré, aucune donnée de carte bancaire n'est collectée, aucune
> passerelle de paiement externe n'est utilisée et **aucun appel réseau n'est effectué**
> lors d'une contribution. Ce document ne constitue pas non plus une preuve de réception
> de fonds.

---

## 1. Architecture

Le module s'appuie sur le modèle `Projet` et sur `Financement`, qui reste
l'**enregistrement définitif** d'une contribution. Il ajoute deux tables et un service.

```
Citoyen                     Gestionnaire                 Local (aucun réseau)
   │                              │                              │
   │ 1. POST /citizen/demandes    │                              │
   │──────────► DemandeFinancement (statut: en_attente)          │
   │                              │                              │
   │                              │ 2. accepter() / refuser()    │
   │                              │◄─────────────────────────────│
   │ 3. GET  .../recapitulatif    │   (verrous + transaction)    │
   │◄─────────────────────────────│                              │
   │ 4. POST .../confirmer        │                              │
   │──────────────────────────────┼──────────► SimulatedPaymentProvider
   │                              │                              │
   │ 5. GET  /citizen/paiements/{id}  ◄── appliquerSimulation() ─┘
   │ 6. GET  .../recu                 Financement + demande=payee
```

| Classe | Responsabilité |
|---|---|
| `App\Services\FinancementService` | Toutes les transitions d'état, dans des transactions avec `lockForUpdate()`. **Seul endroit autorisé à créer un `Financement`.** |
| `App\Services\Payments\SimulatedPaymentProvider` | Produit le résultat de la simulation. Aucune requête HTTP, aucune dépendance réseau. |
| `App\Services\Payments\SimulationResult` | Valeur transportant le scénario, la réussite et le motif. |
| `App\Http\Controllers\Citizen\DemandeFinancementController` | Parcours citoyen : formulaire, récapitulatif, confirmation, résultat, reçu. |
| `App\Http\Controllers\Manager\DemandeFinancementController` | Liste filtrable, détail, acceptation, refus. |

Aucune interface ni factory n'a été introduite : il n'existe qu'**un seul** moteur de
paiement, et le configurer reviendrait à prétendre qu'une passerelle réelle est possible.

---

## 2. Workflow d'une contribution

| Étape | Route | Ce qui se passe |
|---|---|---|
| 1. Sélection | `GET /citizen/projets/{projet}` → bouton « Contribuer à ce projet » | Affiche budget cible, déjà collecté, restant. |
| 2. Formulaire | `GET /citizen/demandes/create?projet_id=` puis `POST /citizen/demandes` | Validation serveur ; crée la demande en `en_attente`. **Aucun montant n'est comptabilisé.** |
| 3. Décision manager | `POST /manager/demandes/{id}/accepter` ou `/refuser` | Vérifie l'éligibilité et le budget **sous verrou**, rejoue le budget disponible, enregistre `decided_by` / `decided_at`. Le refus exige un motif. |
| 4. Récapitulatif | `GET /citizen/demandes/{id}/recapitulatif` | Récapitulatif + avertissement obligatoire + boutons Confirmer / Annuler. |
| 5. Simulation | `POST /citizen/demandes/{id}/confirmer` | Crée (ou réutilise) la tentative, lance la simulation, applique le résultat. |
| 6. Résultat | `GET /citizen/paiements/{id}` | Statut, motif, référence. |
| 7. Reçu | `GET /citizen/paiements/{id}/recu` | Reçu imprimable portant la mention de simulation. |

---

## 3. Statuts

**`demandes_financement.statut`**

| Valeur | Signification |
|---|---|
| `en_attente` | Soumise, pas encore examinée. Ne déplace aucun montant. |
| `acceptee` | Approuvée par un manager. Le montant est **réservé**. |
| `refusee` | Refusée, avec motif obligatoire. Jamais payable. |
| `payee` | Une contribution a été enregistrée. |

**`paiements.statut`**

| Valeur | Signification |
|---|---|
| `en_attente` | Tentative ouverte. |
| `confirme` | Simulation réussie → un `Financement` a été créé. |
| `echoue` | Simulation refusée ou erreur technique. **Le total n'a pas bougé.** |
| `annule` | Annulée par le citoyen. **Le total n'a pas bougé.** |

---

## 4. Règles métier

- **Montant minimum : 1 000 TND** (`StoreDemandeFinancementRequest::MONTANT_MINIMUM`).
- **Éligibilité du projet** : `planifie` ou `en_cours` **et** budget disponible > 0.
  Les projets `termine`, `suspendu` et `annule` sont exclus (`Projet::estEligibleAuFinancementCitoyen()`).
- **Budget disponible** = `budget − total_finance − montant réservé`.
  Le montant réservé somme les demandes `acceptee` encore non payées : plusieurs
  acceptations ne peuvent donc pas dépasser le budget.
- **Le montant provient toujours de la base.** Rien de ce que le navigateur envoie
  n'atteint le calcul, ce qui est couvert par un test dédié.

### Intégrité

- Chaque transition est exécutée dans `DB::transaction()` avec `lockForUpdate()`
  sur la demande **et** le projet.
- Une requête rejouée réutilise la tentative `en_attente` au lieu d'en ouvrir une seconde.
- `confirmer` est **idempotent** : une demande déjà `payee` ne peut plus produire
  de second `Financement`.
- `paiements.reference_interne` porte une contrainte d'unicité.
- Un échec, une annulation ou une erreur technique ferment la tentative **sans jamais
  toucher aux totaux**.

---

## 5. Sécurité et RBAC

Les routes sont gardées par les middlewares du projet, `role:citoyen` et `role:manager`,
qui s'appuient sur `hasAnyRole()`. Les contrôleurs rejouent ce contrôle indépendamment
(`estCitoyen()` / `estManager()`) puis vérifient la **propriété** de la ressource :

- un citoyen ne voit que ses propres demandes, opérations et reçus → **403** sinon ;
- un citoyen ne peut ni accepter ni refuser (403) ;
- `POST /citizen/demandes/{id}/confirmer` est en `throttle:10,1` ;
- toutes les soumissions passent par `@csrf` ;
- **aucune route n'est exempte de CSRF** : sans fournisseur externe, aucun callback
  serveur-à-serveur n'existe (`bootstrap/app.php`) ;
- aucun secret de paiement n'existe, donc aucun n'est commité ;
- les journaux ne contiennent que la référence, l'identifiant du projet et le montant —
  aucune donnée personnelle.

---

## 6. Configuration

```
PAYMENT_DRIVER=simulated
PAYMENT_SIMULATION_SCENARIO_CHOICE=true
```

`PAYMENT_DRIVER` n'accepte que `simulated`. Toute autre valeur lève une `RuntimeException`
au démarrage du provider (`AppServiceProvider::register()`), ce qui empêche par
construction d'activer accidentellement un paiement réel.

`PAYMENT_SIMULATION_SCENARIO_CHOICE` n'a d'effet **que** si `APP_ENV` vaut `local` ou
`testing`. En production il est ignoré : le résultat est alors toujours un succès, et le
citoyen ne peut en aucun cas influencer un statut.

Le choix du scénario n'est jamais lu dans le payload sans vérification : la valeur est
confrontée à `SimulationResult::SCENARIOS` puis ignorée si le mode démo est désactivé.

---

## 7. Scénarios de démonstration

Sur la page de récapitulatif, en mode local, quatre boutons permettent de choisir l'issue :

| Scénario | Résultat | `paiements.statut` | `Financement` | Total financé |
|---|---|---|---|---|
| Simulation réussie | Contribution enregistrée | `confirme` | créé (1 fois) | augmente |
| Simulation refusée | Aucun effet | `echoue` | aucun | inchangé |
| Erreur technique simulée | Aucun effet, motif tracé | `echoue` | aucun | inchangé |
| Simulation annulée | Aucun effet | `annule` | aucun | inchangé |

---

## 8. Lancement et tests en local

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve            # http://localhost:8000
```

```bash
php artisan test --filter=SimulatedPaymentProviderTest      # tests unitaires
php artisan test --filter=ContributionSimuleeTest           # parcours citoyen
php artisan test --filter=DemandeFinancementManagerTest     # volet manager
php artisan test --compact                                  # suite complète
```

Comptes de démonstration (créés par les seeders) :

| E-mail | Rôle | Mot de passe |
|---|---|---|
| `citoyen@aquasecure.tn` | citoyen | `password123` |
| `gestionnaire@aquasecure.tn` | manager | `password123` |
| `admin@aquasecure.tn` | administrateur | `password123` |

---

## 9. Limites assumées

1. **Aucun paiement réel.** Aucune passerelle, aucun compte marchand, aucune API.
2. **Aucune donnée bancaire** n'est demandée ni stockée, par construction.
3. Le reçu est une **attestation d'enregistrement**, pas une preuve bancaire ni un
   justificatif comptable.
4. Un échec ferme la tentative, mais la demande reste `acceptee` : le citoyen peut
   réessayer. Une demande acceptée jamais payée reste réservée indéfiniment — un
   mécanisme d'expiration des réservations n'est pas implémenté.
5. Les totaux sont recalculés par agrégat (`sum`) depuis `financements`, ce qui garantit
   la cohérence mais coûte une requête par projet.

---

## 10. Scénario de soutenance

Projet de référence (ch réels de la base, vérifiés au moment de la rédaction) :

| # | Projet | Statut | Budget cible | Déjà collecté | Réservé | Disponible |
|---|---|---|---|---|---|---|
| 5 | Projet de réduction des pertes d'eau à Bizerte | `en_cours` | 214 709,00 | 121 302,00 | 0,00 | 93 407,00 |

> Les montants affichés sont fictifs, comme le reste du module : il s'agit de données
> de démonstration, **pas de fonds réels**.

**Étape 1 — Création de la demande (citoyen)**

1. Se connecter en `citoyen@aquasecure.tn` / `password123`.
2. Ouvrir le projet **#5** et cliquer **« Contribuer à ce projet »**.
3. Saisir `1000`, envoyer → statut **En attente**.
4. Revenir sur le projet : le montant collecté est **toujours 121 302,00**. Aucune
   contribution n'a été comptabilisée.

**Étape 2 — Validation (manager)**

5. Se connecter en `gestionnaire@aquasecure.tn` / `password123`.
6. **Demandes de financement** → ouvrir la demande → **Accepter**.
7. Le montant réservé passe à 1 000,00, le budget disponible à 92 407,00 — toujours
   sans que le total collecté ne bouge.

**Étape 3 — Simulation (citoyen)**

8. Revenir en citoyen → **Contribuer** → vérifier le récapitulatif : projet, budget,
   déjà collecté, restant, et l'avertissement « Paiement simulé à des fins
   académiques ».
9. Choisir **« Simulation refusée »** → confirmer. Résultat : statut `echoue`,
   le collecté reste à **121 302,00**, la demande reste payable.
10. Choisir **« Simulation réussie »** → confirmer. Le collecté passe à
    **122 302,00** et la demande devient **Payée**.
11. Ouvrir le **reçu** : la mention « Simulation — aucune transaction financière
    réelle » doit être visible. L'imprimer en PDF.

**Étape 4 — Intégrité**

12. Rejouer la même confirmation (double clic ou retour arrière + renvoi) : le total
    **reste** à 122 302,00 et un seul `Financement` existe. La contribution n'est
    jamais comptée deux fois.
