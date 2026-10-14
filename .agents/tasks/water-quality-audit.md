# Audit du module PointMesure / MesureQualite — AquaSecure

**Date de l'audit :** phase de lecture seule — aucun fichier modifié  
**Version Laravel :** 12.x (confirmée via `composer.json`)  
**Base de données :** MySQL (`aquasecure`, port 3306, driver `mysql`)

---

## Résumé exécutif

Le module est en **état fonctionnel solide** pour une application universitaire. Les deux tables sont migrées, les relations Eloquent sont correctes, un service métier `WaterQualityService` centralise les calculs, les deux vues principales consomment des données réelles de la base, et une suite de tests couvre le service, les modèles et les politiques. Les problèmes détectés sont réels mais aucun n'est bloquant à l'exécution immédiate ; les plus importants concernent la sécurité (la politique `MesureQualitePolicy` existe mais n'est pas appliquée dans les contrôleurs web), les données de démonstration non labellisées dans le seeder, l'absence d'un champ `source_donnee` requis par le cahier des charges, et quelques incohérences mineures.

**Points forts identifiés :**
- Structure DB propre, FK avec index composite `(point_mesure_id, date_mesure)`.
- `WaterQualityService::assess()` bien testé (7 tests unitaires + 4 tests de fonctionnalité).
- Les deux vues (`quality.blade.php`, `map.blade.php`) s'alimentent exclusivement depuis Eloquent — aucune donnée fictive de `PlaceholderData` dans ces vues.
- Politique de suppression intelligente : désactivation si historique, suppression physique sinon.
- Seuil nitrates configurable via `config/water_quality.php`.

---

## 1. Structure de la base de données

### Table `point_mesures`

**Fichier :** `database/migrations/2026_10_08_000001_create_point_mesures_table.php`

| Champ | Type | Observations |
|---|---|---|
| `id` | bigint AI | ✅ |
| `code` | varchar(50) UNIQUE | ✅ |
| `nom` | varchar | ✅ |
| `zone` | varchar nullable | ✅ |
| `adresse` | varchar nullable | ✅ |
| `latitude` | decimal(10,7) nullable | ✅ |
| `longitude` | decimal(10,7) nullable | ✅ |
| `type` | varchar(30) default 'Autre' | ✅ |
| `statut` | varchar(20) default 'actif' | ✅ |
| `description` | text nullable | ✅ |
| `created_at`, `updated_at` | timestamps | ✅ |

**Problème MOYEN — Index manquant sur `statut` :**  
La colonne `statut` est filtrée dans plusieurs requêtes (`where('statut', 'actif')`) sans index dédié. Sur une petite démo cela n'a pas d'impact, mais c'est un oubli de conception.  
Impact : dégradation des performances sur volume de données.

**Problème MOYEN — Pas de contrainte de valeurs sur `type` et `statut` :**  
Les champs `type` (Station, Puits, Réservoir, Réseau, Autre) et `statut` (actif, inactif) sont validés dans les `FormRequest` mais pas contraints au niveau DB (`ENUM` ou `CHECK`). Une insertion directe en DB peut insérer une valeur invalide.

---

### Table `mesures_qualites`

**Fichier :** `database/migrations/2026_10_08_000002_create_mesures_qualites_table.php`

| Champ | Type | Observations |
|---|---|---|
| `id` | bigint AI | ✅ |
| `reference` | varchar(50) UNIQUE | ✅ |
| `point_mesure_id` | FK → point_mesures(id) | ✅ CASCADE DELETE (⚠️ voir ci-dessous) |
| `date_mesure` | datetime | ✅ |
| `ph` | decimal(5,2) | ✅ |
| `turbidite` | decimal(8,2) | ✅ |
| `chlore_residuel` | decimal(8,3) | ✅ |
| `plomb` | decimal(10,3) | ✅ |
| `nitrates` | decimal(8,2) | ✅ |
| `is_verified` | boolean default false | ✅ |
| `verifier` | varchar nullable | ✅ |
| `overall_compliance` | decimal(5,2) | ✅ |
| `status` | varchar(20) | ✅ |
| Index composite `(point_mesure_id, date_mesure)` | | ✅ |

**Problème CRITIQUE — CASCADE DELETE incompatible avec le cahier des charges :**  
La migration utilise `->cascadeOnDelete()` sur la FK vers `point_mesures`. Le cahier des charges exige explicitement que « l'historique des mesures doit être conservé lorsqu'un point est désactivé ». Le contrôleur `PointMesureController::destroy()` gère ce cas en désactivant plutôt qu'en supprimant si des mesures existent, mais la contrainte en base est `CASCADE DELETE` : si jamais une suppression physique est déclenchée directement (admin, artisan tinker, migration), toutes les mesures associées disparaissent silencieusement.  
Impact : risque de perte irréversible de l'historique de mesures.  
Correction recommandée : remplacer `->cascadeOnDelete()` par `->restrictOnDelete()`.

**Problème ÉLEVÉE — Champ `source_donnee` absent :**  
Le cahier des charges requiert une distinction entre données simulées, résultats de laboratoire et saisies manuelles. Ce champ n'existe ni dans la migration, ni dans le modèle, ni dans les formulaires.  
Impact : impossible de tracer la provenance des données ; les données du seeder (réelles au sens BD, mais inventées) ne peuvent pas être identifiées comme telles.

**Problème FAIBLE — `status` est de type `varchar(20)` sans contrainte de valeur :**  
Valeurs attendues : `'Compliant'`, `'Alert'`, `'Non-Compliant'`. Aucune contrainte DB ne l'impose.

---

## 2. Modèles Eloquent

### `PointMesure.php`
**Fichier :** `app/Models/PointMesure.php`

- `$fillable` : complet, correspond exactement à la migration. ✅
- Casts : latitude/longitude en `decimal:7`. ✅
- Relations : `hasMany(MesureQualite::class)` et `hasOne(...)->latestOfMany('date_mesure')` pour `derniereMesure`. ✅
- Pas de scopes (`scopeActif`, `scopeAvecCoordonnees`) — tout le filtrage est répété dans les contrôleurs.

**Problème FAIBLE — Scopes utiles non définis :**  
`where('statut', 'actif')`, `whereNotNull('latitude')->whereNotNull('longitude')` sont répétés dans `MapController`, `WaterQualityController` et `PointMesureController`. Des scopes locaux (`scopeActif()`, `scopeGeolocalise()`) élimineraient cette répétition.

**Problème FAIBLE — Pas de factory :**  
`PointMesureFactory` et `MesureQualiteFactory` n'existent pas. Les tests créent les entités manuellement avec `PointMesure::create([...])`. Ceci alourdit les tests et empêche l'utilisation de `factory(15)->create()` pour des jeux de données variés.

---

### `MesureQualite.php`
**Fichier :** `app/Models/MesureQualite.php`

- `$table = 'mesures_qualites'` explicite. ✅
- `$fillable` : complet. ✅
- Casts : datetime, decimals, boolean. ✅
- Relation `belongsTo(PointMesure::class)`. ✅
- Pas de scope `scopeVerified()`.

**Problème FAIBLE — Champ `source_donnee` absent du modèle (cohérent avec la migration) :**  
Reporté ci-dessus (section DB).

---

## 3. Contrôleurs

### `Manager\PointMesureController.php`
**Fichier :** `app/Http/Controllers/Manager/PointMesureController.php`

CRUD complet (index / create / store / show / edit / update / destroy). ✅  
Logique de désactivation vs suppression dans `destroy()`. ✅  
Pas de N+1 dans `index()` : `with('derniereMesure')` chargé. ✅

**Problème ÉLEVÉE — Aucune vérification d'autorisation dans les méthodes :**  
Le contrôleur est protégé par le middleware `role:manager`, mais n'appelle jamais `$this->authorize()` ni `Gate::authorize()`. La `MesureQualitePolicy` est définie et testée, mais n'est pas appliquée aux actions web.  
Impact : un utilisateur avec le rôle `manager` mais sans les permissions granulaires (`measurements.create`, `measurements.update`, `measurements.delete`) peut quand même effectuer toutes les opérations via Blade.

---

### `Manager\WaterQualityController.php`
**Fichier :** `app/Http/Controllers/Manager/WaterQualityController.php`

CRUD complet. ✅ Service injecté via constructeur. ✅

**Problème CRITIQUE — La politique `MesureQualitePolicy` n'est jamais appliquée dans le contrôleur web :**  
Ni `$this->authorize('create', MesureQualite::class)`, ni `$this->authorize('update', $mesure)`, ni `$this->authorize('validate', $mesure)` ne sont appelés. La politique existe (`app/Policies/MesureQualitePolicy.php`) et est testée dans `MesureQualitePolicyTest`. Les règles d'autorisation granulaires (technicien ne peut pas valider, technicien ne peut pas modifier une mesure vérifiée) ne sont donc **pas enforced côté web**.  
Impact : faille de sécurité fonctionnelle — violation du principe du moindre privilège.

**Problème ÉLEVÉE — Absence de pagination dans `index()` :**  
`MesureQualite::with('pointMesure')->latest('date_mesure')->get()` charge **toutes** les mesures en mémoire. En production avec des milliers de mesures, ceci provoque une surcharge mémoire et un ralentissement de la page.

**Problème ÉLEVÉE — Recalcul de `assess()` à chaque affichage (`recordForView`) :**  
Pour chaque mesure, `WaterQualityService::assess()` est appelé à nouveau en PHP même si `status` et `overall_compliance` sont déjà stockés en base. Pour N mesures, c'est N appels inutiles au service.  
Ligne 144 : `$assessment = $this->waterQualityService->assess($mesure->only([...]))`.  
Le contrôleur devrait lire directement `$mesure->status` et `$mesure->overall_compliance`.  
Impact : dégradation des performances proportionnelle au nombre de mesures.

**Problème MOYENNE — Hardcoded fallback username dans `index()` :**  
Ligne 44 : `$user = session('user', ['name' => 'Dr. Selim Dridi', 'role' => 'manager'])`.  
Ce fallback avec un nom fictif est transmis à la vue mais la vue ne l'utilise pas (elle utilise `<x-user-menu />`). La variable est inutilisée et le nom hardcodé est trompeur.

**Problème MOYENNE — `validateMeasurement()` utilise un mécanisme d'authentification hybride fragile :**  
Ligne 111 : `$sessionEmail = session('user.email')`. Si la session est vide, `$verifierName` devient `'Gestionnaire AquaSecure'` — un nom fictif est écrit comme vérificateur. Le middleware `role:manager` garantit qu'un utilisateur est connecté, donc `auth()->user()` ne devrait jamais être null dans ce contexte.

---

### `Api\WaterQualityController.php`
**Fichier :** `app/Http/Controllers/Api/WaterQualityController.php`

- Toutes les méthodes CRUD + validation. ✅
- JWT + fallback session. ✅
- Permissions granulaires vérifiées manuellement (sans la policy). 
- `AuditLog` enregistré pour création, validation, suppression. ✅

**Problème MOYENNE — La politique `MesureQualitePolicy` non utilisée non plus côté API :**  
Les vérifications de permissions sont faites manuellement (`hasPermission('measurements.create')`) plutôt que via la policy. Cela crée une duplication de règles entre le controller, la policy, et le test.

**Problème FAIBLE — `update()` utilise `array_merge($mesure->toArray(), $validated)` :**  
Le `->toArray()` inclut tous les attributs castés et computed du modèle, dont des clés non passées à `assess()`. L'appel est correct car `assess()` accède uniquement aux 5 clés qu'il connaît, mais c'est fragile si le modèle évolue. Utiliser `array_merge($mesure->only([...keys...]), $validated)` serait plus explicite.

---

## 4. Validations (FormRequests)

### `StorePointMesureRequest.php`
**Fichier :** `app/Http/Requests/StorePointMesureRequest.php`

- Code unique, champs obligatoires, `Rule::in()` pour type et statut, coordonnées avec `between`. ✅
- `authorize()` retourne `true` sans vérification. ⚠️ (discuté en section 3)

### `UpdatePointMesureRequest.php`
**Fichier :** `app/Http/Requests/UpdatePointMesureRequest.php`

- `Rule::unique()->ignore()` correctement implémenté. ✅

### `StoreMesureQualiteRequest.php`
**Fichier :** `app/Http/Requests/StoreMesureQualiteRequest.php`

**Problème ÉLEVÉE — `is_verified` est `required` dans le FormRequest web :**  
```php
'is_verified' => ['required', 'boolean'],
```
Dans le formulaire Blade (`form.blade.php`), un champ `<input type="hidden" name="is_verified" value="0">` est présent, suivi d'une checkbox `value="1"`. Cela fonctionne en pratique mais signifie que n'importe quel manager peut cocher "Mesure vérifiée" lors de la création, sans que la policy soit appliquée. Le champ devrait être `'sometimes'` et la vérification de permission devrait être effectuée dans le contrôleur (comme dans l'API).

**Problème FAIBLE — Pas de limite supérieure sur `turbidite`, `chlore_residuel`, `plomb`, `nitrates` :**  
Ces champs n'ont que `min:0`. Une valeur absurde (turbidité = 999 999 NTU) est validée sans erreur.

### `UpdateMesureQualiteRequest.php`
**Fichier :** `app/Http/Requests/UpdateMesureQualiteRequest.php`

- `Rule::unique()->ignore()` correctement implémenté. ✅
- Même problème que `Store` pour `is_verified` : `required`.

---

## 5. Vues

### `quality.blade.php`
**Fichier :** `resources/views/manager/quality.blade.php`

**Données utilisées :** 100% Eloquent via les variables passées par `WaterQualityController::index()`.  
`$records`, `$latestMeasurement`, `$latestAssessment`, `$averageCompliance`, `$activePointsCount`, `$chartRecords`, `$points` — toutes injectées par le contrôleur. ✅  
**Aucun appel à `PlaceholderData` dans cette vue.** ✅

**Problème ÉLEVÉE — Layout incorrect : `@extends('layouts.app')` au lieu de `@extends('layouts.manager')` :**  
Toutes les sous-vues du module (`quality-measures/create`, `edit`, `show`) utilisent `@extends('layouts.manager')` et injectent leur contenu dans `@section('manager-content')`. Mais `quality.blade.php` étend `layouts.app` et utilise `@section('content')`. Si les deux layouts sont différents (nav, sidebar, style global), la page de tableau de bord qualité n'est pas cohérente avec les autres pages du module manager.  
Impact : incohérence visuelle entre les pages du module.

**Problème MOYENNE — La modal "Régler les seuils" est factice :**  
Le bouton "Enregistrer les seuils" dans la modal appelle `closeThresholdModal(); showToast('Threshold parameters updated successfully!', 'success')` — il n'envoie rien en base, n'écrit rien dans `.env`. Les seuils affichés dans la modal (pH max 8.5, turbidité 1.0, etc.) sont des valeurs HTML statiques sans liaison au config PHP réel. La variable `config('water_quality.nitrates_max')` est bien utilisée dans la vue pour les nitrates, mais les autres paramètres ne proviennent pas de la configuration.  
Impact : l'interface laisse croire que les seuils peuvent être modifiés dynamiquement alors que ce n'est pas le cas.

**Problème FAIBLE — `$user` défini en `@php` dans la vue mais jamais utilisé :**  
Ligne 5 : `$user = session('user', [...])`. Ce `$user` n'est affiché nulle part dans la vue (le header utilise `<x-user-menu />`). Variable morte.

**Problème FAIBLE — Pas de lien "Valider" dans la liste des mesures :**  
La route `manager.quality.mesures.validate` est définie. Le contrôleur possède `validateMeasurement()`. Mais la vue `quality.blade.php` n'affiche pas de bouton "Valider" pour les mesures non vérifiées. L'accès à la validation n'est possible que via `quality-measures/show.blade.php` — qui lui non plus n'affiche pas ce bouton (vérifié : aucune mention de `validate` dans cette vue).  
Impact : la fonctionnalité de validation est présente côté serveur mais inaccessible via l'interface.

**Problème FAIBLE — Les couleurs de status dans la vue sont hardcodées en anglais :**  
Le tableau compare `$rec['status'] === 'Compliant'` etc. pour les classes CSS. Ces valeurs proviennent du service et correspondent. Pas de bug, mais fragile si les valeurs changent.

### `map.blade.php`
**Fichier :** `resources/views/manager/map.blade.php`

**Données utilisées :** 
- `$zones` et `$pipelines` : **données fictives** de `PlaceholderData::mapZones()` et `PlaceholderData::mapPipelines()`. C'est intentionnel et documenté — ces zones représentent le réseau géographique, pas les points de mesure.
- `$measurementPoints` : **données Eloquent réelles** de `PointMesure` + `derniereMesure`. ✅

**Problème MOYENNE — `$measurementPoints->count()` appelé sur la collection Eloquent :**  
`MapController` fait `->map(...)` sur la collection, transformant les `PointMesure` en tableaux PHP. Après `->map()`, le résultat est une `Collection` de tableaux. La vue appelle `$measurementPoints->count()` (ligne 123) — cela fonctionne car `Collection::count()` est supporté. Mais si jamais le controller change le type de retour, cela casserait silencieusement.

**Problème FAIBLE — Le ticker bas de page affiche des valeurs fictives hardcodées :**  
Débit total `38 200 m³/j`, Pression moy `3.42 bar` sont des constantes HTML. Pas de lien avec les données réelles. C'est acceptable pour la démo mais devrait être labellisé explicitement.

### `points-mesure/index.blade.php` et `show.blade.php`
✅ Utilisent exclusivement les données Eloquent passées par le contrôleur.  
✅ `derniereMesure` préchargé dans le contrôleur pour éviter les N+1.  
Le `show.blade.php` itère sur `$point->mesuresQualites` qui est chargé avec `load(['mesuresQualites' => fn($q)=> $q->latest('date_mesure')])` dans le contrôleur. ✅

**Problème FAIBLE — Pas de pagination dans `show.blade.php` :**  
Un point avec beaucoup de mesures les affiche toutes sans pagination.

### `quality-measures/form.blade.php`
✅ Formulaire complet, unités affichées, validation Blade pour les erreurs.  
La checkbox `is_verified` avec le hidden `value="0"` fonctionne correctement.

---

## 6. Service `WaterQualityService`

**Fichier :** `app/Services/WaterQualityService.php`

✅ Service bien centralisé, injecté par constructeur.  
✅ Seuils définis pour pH, turbidité, chlore résiduel, plomb.  
✅ Nitrates configurable via `config('water_quality.nitrates_max')`.  
✅ Statut global déterminé sans masquage des non-conformités critiques.  
✅ Score calculé par moyenne simple.  
✅ Testé exhaustivement (7 tests unitaires).

**Problème ÉLEVÉE — Seuils hardcodés dans le code source, non configurables à chaud :**  
pH (6.5–8.5), turbidité (1–2), chlore résiduel (0.2–2.0), plomb (15) sont définis directement dans le code PHP. Seul le seuil nitrates utilise `config()`. Si un gestionnaire veut ajuster le seuil pH, il doit modifier le code source.  
La modal "Régler les seuils" dans `quality.blade.php` laisse croire que c'est possible, mais elle ne fait rien.  
Correction recommandée : déplacer tous les seuils dans `config/water_quality.php` sur le modèle du seuil nitrates existant.

**Problème FAIBLE — `assess()` retourne des labels anglais (`'Compliant'`, `'Non-Compliant'`, `'Alert'`) :**  
La vue `quality.blade.php` compare ces labels anglais pour les classes CSS. La vue `quality-measures/show.blade.php` affiche directement `$mesure->status` (stocké en anglais). Cohérent mais mixte avec le reste de l'interface en français.

**Problème FAIBLE — Nitrates non inclus dans le score quand `nitrates_max` est null :**  
Si le seuil nitrates n'est pas configuré, il n'est pas évalué ET n'est pas inclus dans le calcul du score. La conformité globale est calculée sur 4 paramètres au lieu de 5. Ce comportement est documenté implicitement par les tests mais pas dans le code.

---

## 7. Seeders

**Fichier :** `database/seeders/WaterQualitySeeder.php`

✅ Le seeder crée 4 points de mesure réels avec des coordonnées GPS tunisiennes valides.  
✅ Il utilise `updateOrCreate` pour être idempotent.  
✅ Il appelle `WaterQualityService::assess()` pour calculer les statuts — cohérence garantie.  
✅ `DatabaseSeeder.php` appelle `WaterQualitySeeder::class`. ✅

**Problème ÉLEVÉE — Données non marquées comme `simulated` / `source_donnee` :**  
Le seeder insère des données inventées (valeurs pH, turbidité etc.) comme s'il s'agissait de vraies mesures de laboratoire. Le champ `verifier` contient `'Laboratoire Central INNORPI'` et `'Laboratoire Régional Sousse'` sur des données inventées. Comme le champ `source_donnee` n'existe pas, il est impossible de filtrer ou d'identifier les données de démonstration dans l'interface ou l'API.  
Impact : confusion entre démo et données réelles ; non-conformité avec le cahier des charges.

**Problème FAIBLE — Le seeder ne crée que 4 mesures :**  
Les graphiques "30 jours" dans `quality.blade.php` ne montreront rien d'utile avec des mesures espacées de 2 jours. Un jeu de données plus riche sur 30 jours serait nécessaire pour valider l'affichage des graphiques.

---

## 8. API

**Fichier :** `routes/api.php`, `app/Http/Controllers/Api/WaterQualityController.php`

✅ Routes protégées par `jwt.auth`.  
✅ Écriture restreinte aux rôles technicien/manager/administrateur.  
✅ Validation/suppression restreinte à manager/administrateur.  
✅ Pagination dans `index()` (contrairement à la version web). ✅  
✅ Audit log sur création, validation, suppression.  
✅ Citoyens peuvent lire uniquement les mesures vérifiées.

**Problème MOYENNE — Route `points-mesure` de l'API retourne tous les champs sans `source_donnee` :**  
L'API expose `PointMesure::query()->where('statut', 'actif')->get()` sans transformation. Si un champ `source_donnee` est ajouté, il sera exposé automatiquement. Utiliser `->select([...])` ou une Resource API est recommandé.

**Problème FAIBLE — Pas de route API pour les `PointMesure` (CRUD complet) :**  
L'API expose `GET /points-mesure` (lecture seule) mais pas de routes pour créer, modifier, supprimer des points de mesure via API.

---

## 9. Tests

| Fichier | Couverture |
|---|---|
| `WaterQualityServiceTest.php` | ✅ Exhaustif — 7 cas (pH, turbidité, chlore, plomb, nitrates, combinaisons, score) |
| `WaterQualityModuleTest.php` | ✅ Bon — rendu des pages, CRUD complet, validation des règles |
| `MesureQualitePolicyTest.php` | ✅ Bon — 5 cas (citoyen, technicien, manager, admin, multi-rôle) |
| `ModelRelationsTest.php` | ✅ Couvre `derniereMesure`, casts, relations |

**Problème MOYENNE — Aucun test pour l'API `Api\WaterQualityController` :**  
Le contrôleur API est le plus critique (authentification JWT, permissions) mais aucun test feature ne le couvre.

**Problème MOYENNE — `WaterQualityModuleTest` utilise la session comme auth :**  
Le test simule `session(['user' => [...]])` mais le vrai middleware `CheckRole` lit `Auth::user()`. Si le middleware change, les tests pourraient rester verts alors que les routes seraient inaccessibles en vrai. Les tests devraient utiliser `$this->actingAs(...)`.

**Problème FAIBLE — Factories absentes :**  
`PointMesureFactory` et `MesureQualiteFactory` n'existent pas. Les tests créent les entités avec des tableaux `create([...])` verbeux.

---

## 10. Fichier `PlaceholderData.php`

**Fichier :** `app/Data/PlaceholderData.php`

✅ Utilisé uniquement par `MapController` pour les zones réseau (`mapZones`, `mapPipelines`, `stats`).  
✅ Les méthodes `quality*` (s'il en existait) ne sont **pas** appelées dans les vues du module — les données de qualité sont 100% Eloquent.  
✅ La classe contient des données pour les modules d'autres développeurs (citoyen, technicien, admin) qui sont hors scope de ce module.

**Observation :** `PlaceholderData::mapZones()` et `PlaceholderData::mapPipelines()` sont légitimement utilisées dans `MapController` pour les zones géographiques du réseau. Il ne s'agit pas d'un problème mais d'une conception intentionnelle séparant les données de zone réseau (fictives, appartenant à d'autres modules) des points de mesure qualité (réels, Eloquent).

---

## Corrections recommandées (priorisées)

### Priorité 1 — Sécurité / Intégrité des données

1. **[CRITIQUE] Remplacer `cascadeOnDelete()` par `restrictOnDelete()`** dans `2026_10_08_000002_create_mesures_qualites_table.php`. Créer une migration additive pour modifier la contrainte FK existante.

2. **[CRITIQUE] Appliquer la `MesureQualitePolicy` dans `Manager\WaterQualityController`** :  
   Ajouter `$this->authorize('create', MesureQualite::class)` dans `store()`, `$this->authorize('update', $mesure)` dans `update()`, `$this->authorize('validate', $mesure)` dans `validateMeasurement()`, `$this->authorize('delete', $mesure)` dans `destroy()`.

### Priorité 2 — Conformité au cahier des charges

3. **[ÉLEVÉE] Ajouter le champ `source_donnee`** : créer une migration additive pour ajouter `source_donnee` (enum : `'laboratoire'`, `'manuel'`, `'simule'`) dans `mesures_qualites`, l'ajouter dans `$fillable` et les casts du modèle, dans `StoreMesureQualiteRequest` et `UpdateMesureQualiteRequest`, et dans `form.blade.php`.

4. **[ÉLEVÉE] Marquer les données du seeder** : ajouter `'source_donnee' => 'simule'` dans `WaterQualitySeeder`.

5. **[ÉLEVÉE] Déplacer tous les seuils dans `config/water_quality.php`** : extraire pH_min (6.5), pH_max (8.5), turbidite_alert (1), turbidite_max (2), chlore_min (0.2), chlore_max (2.0), plomb_max (15) dans la config, et connecter la modal de seuils à ces valeurs (ou ajouter une route pour les persister dans `.env`).

### Priorité 3 — Performances et qualité de code

6. **[ÉLEVÉE] Paginer la liste des mesures** dans `Manager\WaterQualityController::index()` : utiliser `->paginate(25)` au lieu de `->get()`.

7. **[ÉLEVÉE] Arrêter le recalcul de `assess()` dans `recordForView()`** : lire directement `$mesure->status` et `$mesure->overall_compliance` au lieu de rappeler le service.

8. **[ÉLEVÉE] `is_verified` en `sometimes` dans `StoreMesureQualiteRequest`** : et vérifier la permission `measurements.validate` dans le contrôleur avant de permettre `is_verified = true`.

### Priorité 4 — Ergonomie / Cohérence

9. **[MOYENNE] Corriger le layout dans `quality.blade.php`** : remplacer `@extends('layouts.app')` par `@extends('layouts.manager')` et `@section('content')` par `@section('manager-content')`.

10. **[MOYENNE] Ajouter le bouton "Valider"** dans `quality-measures/show.blade.php` pour les mesures non vérifiées, visible uniquement pour les rôles manager/administrateur.

11. **[MOYENNE] Supprimer la variable `$user` hardcodée** dans `WaterQualityController::index()` (ligne ~44) puisqu'elle n'est pas utilisée par la vue.

12. **[MOYENNE] Corriger `validateMeasurement()` web** : remplacer le mécanisme hybride session/auth par `auth()->user()` uniquement (le middleware garantit son existence).

13. **[MOYENNE] Ajouter des scopes** dans `PointMesure` : `scopeActif()`, `scopeGeolocalise()`.

### Priorité 5 — Tests et outillage

14. **[MOYENNE] Ajouter des tests Feature pour `Api\WaterQualityController`** : couvrir create, update, validate, delete avec les différents rôles.

15. **[MOYENNE] Utiliser `actingAs()` dans `WaterQualityModuleTest`** au lieu de `withSession(['user' => [...]])`.

16. **[FAIBLE] Créer `PointMesureFactory` et `MesureQualiteFactory`** pour simplifier les tests.

17. **[FAIBLE] Enrichir le seeder** avec 30 jours de mesures (au moins une par jour) pour valider l'affichage des graphiques.

18. **[FAIBLE] Ajouter des index sur `statut`** dans `point_mesures` et sur `status`, `is_verified` dans `mesures_qualites`.

19. **[FAIBLE] Ajouter pagination** dans `points-mesure/show.blade.php` pour l'historique de mesures.

---

## Annexe — Cartographie des fichiers audités

| Fichier | Statut | Problèmes |
|---|---|---|
| `database/migrations/2026_10_08_000001_create_point_mesures_table.php` | ⚠️ | Index manquants |
| `database/migrations/2026_10_08_000002_create_mesures_qualites_table.php` | 🔴 | CASCADE DELETE, absence source_donnee |
| `app/Models/PointMesure.php` | ✅ | Scopes manquants (mineur) |
| `app/Models/MesureQualite.php` | ✅ | source_donnee absent (mineur) |
| `app/Http/Controllers/Manager/PointMesureController.php` | ⚠️ | Pas de authorize() |
| `app/Http/Controllers/Manager/WaterQualityController.php` | 🔴 | Policy non appliquée, pas de pagination, recalcul assess() |
| `app/Http/Controllers/Api/WaterQualityController.php` | ✅ | Bon état |
| `app/Services/WaterQualityService.php` | ⚠️ | Seuils hardcodés |
| `app/Http/Requests/StorePointMesureRequest.php` | ✅ | — |
| `app/Http/Requests/UpdatePointMesureRequest.php` | ✅ | — |
| `app/Http/Requests/StoreMesureQualiteRequest.php` | ⚠️ | is_verified required, pas de max sur numériques |
| `app/Http/Requests/UpdateMesureQualiteRequest.php` | ⚠️ | is_verified required |
| `app/Policies/MesureQualitePolicy.php` | ✅ | Bien définie, non appliquée |
| `resources/views/manager/quality.blade.php` | ⚠️ | Mauvais layout, modal seuils factice |
| `resources/views/manager/map.blade.php` | ✅ | Points de mesure en Eloquent |
| `resources/views/manager/points-mesure/*.blade.php` | ✅ | — |
| `resources/views/manager/quality-measures/*.blade.php` | ⚠️ | Pas de bouton valider |
| `database/seeders/WaterQualitySeeder.php` | ⚠️ | Données non marquées comme simulées |
| `config/water_quality.php` | ⚠️ | Incomplet (seuil nitrates seulement) |
| `tests/Unit/WaterQualityServiceTest.php` | ✅ | Exhaustif |
| `tests/Feature/WaterQualityModuleTest.php` | ⚠️ | Auth via session, pas actingAs() |
| `tests/Unit/MesureQualitePolicyTest.php` | ✅ | Complet |
| `tests/Unit/ModelRelationsTest.php` | ✅ | Couvre les relations |
| `app/Data/PlaceholderData.php` | ✅ | Non utilisé par le module qualité |
| `routes/web.php` | ✅ | Routes complètes |
| `routes/api.php` | ✅ | Routes protégées correctement |
