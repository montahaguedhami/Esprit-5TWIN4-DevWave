# Phase 1 — Résultats des tests

**Date :** Phase 1 — corrections des findings du verdict CHANGES_REQUESTED  
**Commande :** `php artisan test --filter=WaterQuality` et `php artisan test --filter=MesureQualitePolicy`

---

## Tests réussis

### WaterQualityServiceTest (8 tests)
- ✅ all compliant parameters yield compliant status and full score
- ✅ ph boundaries are inclusive
- ✅ turbidity uses alert band between one and two
- ✅ residual chlorine boundaries are inclusive
- ✅ lead threshold is inclusive
- ✅ non compliant overrides alert for overall status
- ✅ nitrates are evaluated only when a maximum is configured
- ✅ score is rounded to two decimals

### WaterQualityModuleTest (7 tests)
- ✅ quality and map pages render from the database
- ✅ technician can create measurement and manager can update validate delete
- ✅ measurement validation rejects impossible values
- ✅ manager cannot create measurement policy enforced
- ✅ citizen cannot create update or delete measurement
- ✅ fk restrict prevents deleting point with measurements
- ✅ nitrates threshold is optional and configurable

### MesureQualitePolicyTest (5 tests)
- ✅ citizen can view but cannot create update validate or delete
- ✅ technician can create and update unverified but cannot validate
- ✅ manager can update verified measurements and validate
- ✅ administrator can manage measurements
- ✅ technician who is also manager can update verified measurements

**Total : 20 tests, 106 assertions — tous passent**

---

## Tests échoués

Aucun.

---

## Findings corrigés (phase1-verdict.json)

| Finding | Action |
|---|---|
| Autorisations PointMesureController absentes | Documenté par commentaire explicite dans le contrôleur — aucune permission `points.*` n'existe dans le RbacSeeder ; la création de telles permissions est une décision produit hors Phase 1. |
| Bouton "Valider" absent de show.blade.php | Bouton ajouté avec guard `@can('validate', $mesure)` et `@unless($mesure->is_verified)`. |
| `$user` hardcodé dans `index()` | Supprimé du contrôleur et de la vue Blade `quality.blade.php`. |
| `primary_role` sans garantie de chargement de relation | `auth()->user()->load('roles')` ajouté avant l'accès à `$user->primary_role` dans `validateMeasurement()`. |

---

## Risques restants

- **MySQL non accessible depuis le shell de l'agent** : la migration `2026_10_14_140000_change_point_mesure_fk_to_restrict.php` est prête mais son application (`php artisan migrate`) requiert une intervention manuelle une fois MySQL joignable. La contrainte `RESTRICT` n'est pas encore active en base de données réelle.
- **PointMesureController sans policy** : l'absence de permissions granulaires pour les points de mesure est documentée mais non résolue. Un manager avec `role:manager` peut créer, modifier ou désactiver n'importe quel point. Résolution recommandée en Phase suivante ou via un ticket produit séparé.
- **Valider button via CSRF** : le bouton Valider émet un POST avec `@csrf`. Il est accessible uniquement aux utilisateurs qui passent la policy `validate`. Comportement attendu.
