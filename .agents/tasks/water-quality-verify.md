# Vérification initiale — Module PointMesure / MesureQualite

**Date :** Étape 0 — lecture seule, aucun fichier modifié  
**Version Laravel :** 12.x (cf. audit)  
**Base de données :** MySQL — `aquasecure` sur 127.0.0.1:3306

---

## État Git

- Branche : `main`, à jour avec `origin/main`.
- Pas de changements stagés ni modifiés.
- Seul fichier non suivi : `.agents/tasks/water-quality-audit.md` (créé lors de la phase d'audit — non commité, sans impact sur le code).

---

## Migrations : statut appliqué / non appliqué

> **⚠️ Anomalie bloquante pour la vérification en ligne :** `php artisan migrate:status` expire (timeout) — le serveur MySQL n'est pas accessible dans cet environnement d'exécution. La base de données `aquasecure` tourne sur 127.0.0.1:3306 (cf. `.env`) mais n'est pas joignable depuis le shell de l'agent.

**Conséquences pratiques :**
- L'état `Ran` / `Pending` de chaque migration ne peut pas être confirmé programmatiquement.
- **Hypothèse fondée sur l'état du code :** les migrations du module (`000001_create_point_mesures_table`, `000002_create_mesures_qualites_table`) sont considérées comme appliquées, puisque le seeder et les contrôleurs les utilisent sans erreur apparente dans le code.
- Toute migration additive devra être validée manuellement par `php artisan migrate:status` puis `php artisan migrate` une fois MySQL accessible.

### Migrations du module (par ordre chronologique)

| Fichier | Statut estimé |
|---|---|
| `2026_10_08_000001_create_point_mesures_table.php` | ✅ Appliquée (hypothèse) |
| `2026_10_08_000002_create_mesures_qualites_table.php` | ✅ Appliquée (hypothèse) |

### Migrations hors scope (non modifiables)

| Fichier | Périmètre |
|---|---|
| `0001_01_01_000000_create_users_table.php` | RBAC / Auth |
| `0001_01_01_000001_create_cache_table.php` | Cache |
| `0001_01_01_000002_create_jobs_table.php` | Queue |
| `2026_10_06_031836_create_financements_table.php` | Financements |
| `2026_10_06_031836_create_projets_table.php` | Projets |
| `2026_10_06_031837_create_financements_table.php` | Financements |
| `2026_10_06_112600_create_zones_and_infrastructures_tables.php` | Zones |
| `2026_10_07_000001_create_incidents_table.php` | Incidents |
| `2026_10_07_000002_create_action_correctives_table.php` | Incidents |
| `2026_10_07_113243_add_progression_to_projets_table.php` | Projets |
| `2026_10_07_115712_create_techniciens_table.php` | Techniciens |
| `2026_10_07_115713_create_interventions_table.php` | Interventions |
| `2026_10_14_120000_create_rbac_tables.php` | RBAC |
| `2026_10_14_130000_create_jwt_refresh_tokens_table.php` | JWT |

---

## Nom exact de la clé étrangère dans `mesures_qualites`

**Fichier :** `database/migrations/2026_10_08_000002_create_mesures_qualites_table.php`

```php
$table->foreignId('point_mesure_id')->constrained('point_mesures')->cascadeOnDelete();
```

Laravel génère le nom de contrainte selon la convention `{table}_{colonne}_foreign`.  
**Nom réel attendu : `mesures_qualites_point_mesure_id_foreign`**

Ce nom sera confirmé ou corrigé lors de la Phase 1 avec une requête `SHOW CREATE TABLE mesures_qualites` si MySQL est accessible, ou via le nom conventionnel Laravel qui est fiable dans la pratique.

**Contrainte actuelle :** `CASCADE DELETE` — à remplacer par `RESTRICT` (Phase 1).

---

## Capacités définies dans `MesureQualitePolicy`

**Fichier :** `app/Policies/MesureQualitePolicy.php`

| Méthode | Permission requise | Règle supplémentaire |
|---|---|---|
| `viewAny` | `measurements.view` | — |
| `view` | `measurements.view` | — |
| `create` | `measurements.create` | — |
| `update` | `measurements.update` | Technicien ne peut modifier qu'une mesure non vérifiée |
| `validate` | `measurements.validate` | Seuls Manager/Administrateur |
| `delete` | `measurements.delete` | — |

**État d'application :**
- `WaterQualityController` (web) : **policy non appliquée** (aucun appel `$this->authorize()`).
- `PointMesureController` (web) : **aucune autorisation granulaire** (seul le middleware `role:manager` protège les routes).
- `Api\WaterQualityController` : vérifications manuelles de permission (`hasPermission(...)`) mais sans passer par la policy.

---

## `config/water_quality.php` — Présence et contenu

**Fichier :** `config/water_quality.php` — **présent**.

```php
return [
    'nitrates_max' => env('WATER_QUALITY_NITRATES_MAX'),
];
```

**État :** incomplet — seul le seuil nitrates est configuré via `env()`. Les autres seuils (pH min/max, turbidité, chlore résiduel, plomb) sont hardcodés dans `WaterQualityService.php`. La valeur `WATER_QUALITY_NITRATES_MAX` n'est pas définie dans `.env`, donc `config('water_quality.nitrates_max')` retourne `null` par défaut, ce qui désactive l'évaluation des nitrates.

---

## Anomalies bloquantes ou à surveiller

### 🔴 Bloquant pour les phases

1. **MySQL inaccessible depuis le shell de l'agent.** `php artisan migrate:status` et `php artisan migrate` ne peuvent pas être exécutés. Toutes les migrations additives créées seront prêtes à exécuter, mais leur application nécessite une intervention manuelle par l'équipe.

### ⚠️ À surveiller en Phase 1

2. **Nom de la FK `mesures_qualites_point_mesure_id_foreign` :** nom calculé selon la convention Laravel, non confirmé par `SHOW CREATE TABLE`. La migration additive utilisera `dropForeign()` avec le nom de colonne (méthode Laravel recommandée) plutôt que le nom littéral de contrainte, pour être robuste.

3. **Données existantes :** la cohérence entre `point_mesures` et `mesures_qualites` est supposée correcte (le seeder utilise `updateOrCreate` avec des FK valides). Si des orphelins existaient, changer `CASCADE` en `RESTRICT` ne poserait pas de problème (RESTRICT ne bloque que la suppression, pas la présence d'orphelins).

4. **`cascadeOnDelete()` → `restrictOnDelete()` :** la migration additive doit d'abord supprimer la contrainte existante, puis en créer une nouvelle. Risque : si la migration appliquée a un nom de contrainte différent du nom calculé, `dropForeign(['point_mesure_id'])` échouera. La méthode `dropForeign` avec tableau de colonnes est la plus portable.

---

## Synthèse pour les phases suivantes

| Item | Statut |
|---|---|
| Git propre | ✅ (seul fichier d'audit non commité) |
| Migrations module appliquées | ✅ estimé (MySQL non joignable pour confirmer) |
| FK `cascadeOnDelete` confirmée dans le code | ✅ |
| Nom FK estimé | `mesures_qualites_point_mesure_id_foreign` |
| Policy définie et complète | ✅ (6 méthodes, permissions granulaires) |
| Policy appliquée dans les contrôleurs web | ❌ (Phase 1 corrige cela) |
| `config/water_quality.php` présent | ✅ mais incomplet (Phase 3) |
| `source_donnee` absent | ❌ (Phase 2) |
| MySQL accessible pour `php artisan migrate` | ❌ — opération manuelle requise |
