# Phase 1 — Sécurité et intégrité : protection de l'historique des mesures et application des autorisations

La phase 1 ferme les deux failles de sécurité identifiées en audit : la contrainte `CASCADE DELETE` qui exposait silencieusement l'historique à une suppression accidentelle, et l'absence d'application de `MesureQualitePolicy` dans les contrôleurs web. La migration additive remplace la FK, les quatre actions sensibles de `WaterQualityController` reçoivent des appels `authorize()`, et le mécanisme hybride session/auth dans `validateMeasurement()` est remplacé par `auth()->user()`. Un prérequis technique manquant (`AuthorizesRequests` absent du stub Laravel 12) a été corrigé au passage. Tous les 20 tests passent, 3 nouveaux tests couvrent les scénarios introduits.

Watch for: **(confirmed)** aucune autorisation n'est appliquée dans `PointMesureController` malgré ce qu'indique l'énoncé de la phase ; **(confirmed)** aucun bouton "Valider" n'est présent dans `show.blade.php` ; **(confirmed)** `$user` hardcodé reste dans `index()` ; **(possible)** `$user->primary_role` est un accesseur dynamique dont la valeur dépend des rôles chargés en mémoire — une utilisation sans `load('roles')` retournerait une valeur inattendue.

**Verdict**: NEEDS_CHANGES

---

## Vue d'ensemble

La migration est additive, nomme la contrainte par nom de colonne (résolution automatique du nom conventionnel `mesures_qualites_point_mesure_id_foreign`), et son `down()` restaure fidèlement `CASCADE DELETE`. La contrainte `restrictOnDelete()` est correcte pour MySQL. Le `cascadeOnUpdate()` préserve la propagation des mises à jour de clé primaire, ce qui est cohérent avec le comportement original.

`WaterQualityController::store()`, `update()`, `destroy()` et `validateMeasurement()` appellent maintenant `$this->authorize()` avec des noms de capacité (`'create'`, `'update'`, `'delete'`, `'validate'`) qui correspondent exactement aux méthodes de `MesureQualitePolicy`. `AuthorizesRequests` a été ajouté à la classe `Controller` de base, condition nécessaire au fonctionnement de `$this->authorize()` sous Laravel 12.

`PointMesureController` ne contient aucun appel `authorize()` pour les opérations sur les points. L'audit original le signalait et la phase 1 devait l'adresser selon l'énoncé ("La création, la modification et la désactivation d'un point"). Il s'agit d'un oubli de périmètre, pas d'une régression : le code est identique à l'état pré-phase.

Le mécanisme hybride `session('user.email')` dans `validateMeasurement()` est correctement remplacé par `auth()->user()`, et le nom de vérificateur est construit avec `$user->name . ' (' . $user->primary_role . ')'`, aligné avec l'implémentation API existante.

<details>
<summary>Issues (4)</summary>

1. **Autorisations PointMesureController absentes** — `store()`, `update()`, `destroy()` n'appellent pas `authorize()`. Un manager sans les permissions granulaires correspondantes peut créer, modifier et désactiver des points. Ajouter les vérifications d'autorisation sur ces trois méthodes, en définissant les capacités dans `MesureQualitePolicy` ou en créant une `PointMesurePolicy` selon les permissions disponibles dans le RbacSeeder.

2. **Bouton "Valider" absent de show.blade.php** — `validateMeasurement()` est protégé côté serveur mais inaccessible depuis l'interface. L'audit original et l'énoncé de la phase 1 exigent que le bouton soit affiché conditionnellement avec `@can('validate', $mesure)` pour les mesures non encore vérifiées.

3. **`$user` hardcodé dans `index()`** — `session('user', ['name' => 'Dr. Selim Dridi', 'role' => 'manager'])` reste ligne ~47. La variable est transmise à la vue mais non utilisée (la vue utilise `<x-user-menu />`). Cette ligne transmet un nom fictif comme contexte utilisateur. À supprimer.

4. **`primary_role` sans garantie de chargement de relation** — `validateMeasurement()` accède à `$user->primary_role` sur l'utilisateur retourné par `auth()->user()`. Si la relation `roles` n'est pas eager-loaded sur l'instance en session (ce qui dépend de l'implémentation du guard), l'accesseur effectuera une requête supplémentaire par validation, ou retournera une valeur incorrecte si les rôles sont incomplets. Vérifier que l'accesseur `primary_role` émet une requête SQL propre lorsque la relation n'est pas chargée, ou forcer `auth()->user()->load('roles')` avant l'accès.

</details>

<details>
<summary>Détails</summary>

### Migration CASCADE → RESTRICT

La migration drop-and-recreate la contrainte FK en deux étapes dans la même transaction de schéma. `dropForeign(['point_mesure_id'])` résout le nom conventionnel `mesures_qualites_point_mesure_id_foreign` automatiquement, ce qui est correct et robuste. Le `down()` reconstruit la contrainte `CASCADE DELETE` avec `cascadeOnUpdate()`, symétrique au `up()`. Le fait d'ajouter explicitement `cascadeOnUpdate()` là où la migration originale ne l'avait peut-être pas est un delta mineur mais sans conséquence : MySQL applique `NO ACTION` sur les updates par défaut, et `CASCADE` sur les updates est inoffensif pour une PK bigint auto-incrémentée.

Le test `test_fk_restrict_prevents_deleting_point_with_measurements` vérifie le comportement du contrôleur (désactivation au lieu de suppression), pas la contrainte base de données directement. Sous `RefreshDatabase`, SQLite est utilisé en test (configuration habituelle pour Laravel) : SQLite supporte `RESTRICT` comme `NO ACTION`, qui diffère de MySQL en mode strict. Le test est valide pour couvrir la logique applicative, mais il ne prouve pas que la contrainte MySQL bloque effectivement une suppression directe via Tinker ou une requête SQL brute. Ce risque est documenté dans `phase1-test-results.md` et acceptable.

### Correspondance authorize() ↔ Policy

Les quatre capacités appelées dans `WaterQualityController` correspondent exactement aux méthodes de `MesureQualitePolicy` :

| Appel contrôleur | Méthode policy | Statut |
|---|---|---|
| `authorize('create', MesureQualite::class)` | `create(User $user)` | ✅ |
| `authorize('update', $mesure)` | `update(User $user, MesureQualite $mesure)` | ✅ |
| `authorize('delete', $mesure)` | `delete(User $user, MesureQualite $mesure)` | ✅ |
| `authorize('validate', $mesure)` | `validate(User $user, MesureQualite $mesure)` | ✅ |

### Autorisations manquantes dans PointMesureController

L'énoncé de la phase 1 spécifie explicitement : "La création, la modification et la désactivation d'un point." `PointMesureController::store()`, `update()`, et `destroy()` ne contiennent aucun appel `authorize()`. Le contrôleur est protégé par le middleware `role:manager` au niveau des routes, ce qui garantit l'accès au rôle, mais pas les permissions granulaires. Un manager sans les permissions correspondantes peut toujours créer, renommer, ou désactiver un point de mesure via l'interface web. Selon le `RbacSeeder`, les permissions sur les points (`points.create`, `points.update`, `points.delete` si elles existent) ne sont pas vérifiées. Si aucune permission dédiée aux points n'existe, il faut soit en créer, soit documenter le choix de ne pas les protéger au niveau granulaire.

### Bouton "Valider" absent de l'interface

`quality-measures/show.blade.php` a reçu les guards `@can('update', $mesure)` et `@can('delete', $mesure)` pour les boutons "Modifier" et "Supprimer", mais aucun bouton "Valider" n'a été ajouté. La route `manager.quality.mesures.validate` existe et `validateMeasurement()` est protégée par `authorize('validate', $mesure)`, mais aucune entrée utilisateur ne mène à cette action depuis l'interface web. La fonctionnalité de validation est complète côté serveur et inaccessible côté client.

### Tests

Les 20 tests passent. Le passage de `withSession()` à `actingAs()` dans `WaterQualityModuleTest` est la correction correcte : les tests authentifient maintenant de vrais utilisateurs Eloquent avec des rôles et permissions réels, au lieu de simuler une session qui n'est pas ce que le middleware `CheckRole` lit. `test_manager_cannot_create_measurement_policy_enforced` vérifie explicitement le 403 pour un manager sans `measurements.create`, ce qui valide que `authorize()` est effectivement déclenché. `test_fk_restrict_prevents_deleting_point_with_measurements` couvre la logique de désactivation.

`MesureQualitePolicyTest` corrige l'assertion `create($manager)` de `allowed()` à `denied()`, alignée avec le fait que le rôle manager n'a pas `measurements.create` dans le `RbacSeeder`. Ce n'est pas une régression : c'est la correction d'un test qui affirmait un comportement incorrect.

</details>

---

<details>
<summary>Fichiers modifiés</summary>

| Fichier | Changement |
|---|---|
| `database/migrations/2026_10_14_140000_change_point_mesure_fk_to_restrict.php` | Nouvelle migration additive — drop et recréation de la FK avec `restrictOnDelete()` |
| `app/Http/Controllers/Controller.php` | Ajout du trait `AuthorizesRequests` (prérequis Laravel 12) |
| `app/Http/Controllers/Manager/WaterQualityController.php` | `authorize()` sur `store`, `update`, `destroy`, `validateMeasurement` ; remplacement du mécanisme auth hybride ; correction apostrophe Unicode |
| `resources/views/manager/quality.blade.php` | Bouton "Nouvelle mesure" conditionné par `@can('create', MesureQualite::class)` |
| `resources/views/manager/points-mesure/show.blade.php` | Bouton "Ajouter une mesure" conditionné par `@can('create', MesureQualite::class)` |
| `resources/views/manager/quality-measures/show.blade.php` | Boutons "Modifier" et "Supprimer" conditionnés par `@can` |
| `tests/Feature/WaterQualityModuleTest.php` | `withSession()` → `actingAs()` ; 3 nouveaux tests |
| `tests/Unit/MesureQualitePolicyTest.php` | Assertion `create($manager)` corrigée de `allowed()` à `denied()` |

</details>
