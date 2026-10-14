# Phase 1 — Résultats des tests

**Date :** Phase 1 complète  
**Commande :** `php artisan test --filter="WaterQuality|MesureQualitePolicy|PointMesure"`

---

## Résumé

| Suite | Tests | Assertions | Statut |
|---|---|---|---|
| `WaterQualityServiceTest` (Unit) | 8 | — | ✅ |
| `MesureQualitePolicyTest` (Unit) | 5 | — | ✅ |
| `WaterQualityModuleTest` (Feature) | 7 | — | ✅ |
| **Total** | **20** | **106** | **✅ Tous réussis** |

---

## Tests réussis

### WaterQualityServiceTest (8)
- `all compliant parameters yield compliant status and full score`
- `ph boundaries are inclusive`
- `turbidity uses alert band between one and two`
- `residual chlorine boundaries are inclusive`
- `lead threshold is inclusive`
- `non compliant overrides alert for overall status`
- `nitrates are evaluated only when a maximum is configured`
- `score is rounded to two decimals`

### MesureQualitePolicyTest (5)
- `citizen can view but cannot create update validate or delete`
- `technician can create and update unverified but cannot validate`
- `manager can update verified measurements and validate` *(corrigé : assert deny sur create pour manager)*
- `administrator can manage measurements`
- `technician who is also manager can update verified measurements`

### WaterQualityModuleTest (7)
- `quality and map pages render from the database`
- `technician can create measurement and manager can update validate delete`
- `measurement validation rejects impossible values`
- `manager cannot create measurement policy enforced` *(nouveau — régression volontaire vérifiée : 403)*
- `citizen cannot create update or delete measurement` *(nouveau)*
- `fk restrict prevents deleting point with measurements` *(nouveau)*
- `nitrates threshold is optional and configurable`

---

## Tests échoués

Aucun.

---

## Erreurs préexistantes corrigées (distinctes des régressions introduites)

### 1. `WaterQualityController::create()` — syntaxe PHP invalide (préexistant)
- **Fichier :** `app/Http/Controllers/Manager/WaterQualityController.php`
- **Cause :** apostrophe curviligne Unicode `'` (`\u2019`) dans la chaîne `'Créez d'abord…'`, invalide en PHP
- **Correction :** remplacement de la chaîne par des guillemets doubles

### 2. `MesureQualitePolicyTest::test_manager_can_update_verified_measurements_and_validate` — assertion incorrecte (préexistant)
- **Cause :** le test assertait `$policy->create($manager)->allowed()` mais le rôle manager n'a pas la permission `measurements.create` selon le RbacSeeder
- **Correction :** assertion remplacée par `->denied()` pour refléter la réalité du seeder

### 3. `Controller` sans `AuthorizesRequests` (préexistant)
- **Fichier :** `app/Http/Controllers/Controller.php`
- **Cause :** Laravel 12 ne l'inclut plus dans le stub du Controller de base
- **Correction :** ajout de `use Illuminate\Foundation\Auth\Access\AuthorizesRequests;` dans la classe `Controller`

---

## Risques restants

| Risque | Sévérité | Mitigation |
|---|---|---|
| Migration FK `restrictOnDelete` non appliquée (MySQL non joignable depuis l'agent) | MOYEN | Exécuter `php artisan migrate` manuellement une fois MySQL accessible |
| Manager sans `measurements.create` — aucun utilisateur avec `role:manager` seul ne peut créer de mesure via le web | MOYEN | Documenté et accepté (option 3 validée par l'utilisateur) |
| `validateMeasurement()` appelle `auth()->user()` — si le middleware ne garantit pas l'auth (sessions mock), null pointer possible | FAIBLE | Le middleware `role:manager` résout toujours l'utilisateur via Eloquent avant d'arriver au contrôleur |

---

## Fichiers modifiés dans la Phase 1

| Fichier | Action |
|---|---|
| `database/migrations/2026_10_14_140000_change_point_mesure_fk_to_restrict.php` | **Créé** — migration additive CASCADE→RESTRICT |
| `app/Http/Controllers/Controller.php` | **Modifié** — ajout `AuthorizesRequests` |
| `app/Http/Controllers/Manager/WaterQualityController.php` | **Modifié** — `authorize()` sur store/update/destroy/validateMeasurement + fix apostrophe + fix auth hybride |
| `resources/views/manager/quality.blade.php` | **Modifié** — bouton "Nouvelle mesure" conditionné par `@can('create', MesureQualite::class)` |
| `resources/views/manager/points-mesure/show.blade.php` | **Modifié** — bouton "Ajouter une mesure" conditionné par `@can` |
| `resources/views/manager/quality-measures/show.blade.php` | **Modifié** — boutons "Modifier" et "Supprimer" conditionnés par `@can` |
| `tests/Feature/WaterQualityModuleTest.php` | **Modifié** — `withSession()` → `actingAs()`, 3 nouveaux tests ajoutés |
| `tests/Unit/MesureQualitePolicyTest.php` | **Modifié** — assertion `create($manager)` corrigée |
| `.agents/tasks/phase1-test-results.md` | **Créé** — ce fichier |
