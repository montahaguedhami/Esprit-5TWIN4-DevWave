# AquaSecure — Auth & RBAC Investigation Report

**Project**: AquaSecure (Laravel 12, PHP 8.2, MySQL)  
**Investigation scope**: Authentication, role-based routing, middleware, views, database  
**Roles**: `administrateur`, `manager`, `technicien`, `citoyen`

---

## EXECUTIVE SUMMARY

The auth/RBAC architecture is **well-designed overall** — custom RBAC via a `roles` pivot table, a `HasRoles` trait, four middleware classes, and a complete seeder. However there are **two blockers** that will prevent the application from running at all on a fresh install, plus several lower-severity issues.

| Severity | Count | Short description |
|----------|-------|-------------------|
| CRITICAL | 2 | DB connection broken (.env); session driver mismatch can break logins |
| HIGH | 2 | `.env` leading-space keys; role assignment silently no-ops when roles not seeded |
| MEDIUM | 3 | Dual session/Auth strategies; `EnsureManager` vs `CheckRole` inconsistency; `Gate::authorize('permission',…)` wrong signature |
| LOW | 3 | Duplicate migration timestamp; `TravauxController` statut string case mismatch; no CSRF protection awareness note |

---

## 1. AUTH SYSTEM

### Routes (`routes/web.php`)

No separate `routes/auth.php`. All auth routes are inline in `web.php`:

| Method | URI | Name | Action |
|--------|-----|------|--------|
| GET | `/` | `landing` | Returns `views/landing` |
| GET | `/login` | `auth.login` | Returns `views/auth/login` |
| GET | `/register` | `auth.register` | Returns `views/auth/register` |
| GET | `/forgot-password` | `auth.forgot-password` | Returns `views/auth/forgot-password` |
| POST | `/login` | `login.post` | Inline closure — validates, `Auth::attempt()`, sets `session['user']`, redirects by role |
| POST | `/register` | `register.post` | Creates user, assigns `citoyen` role |
| POST | `/forgot-password` | `forgot-password.post` | Stub — returns success flash only |
| POST | `/logout` | `logout` | `Auth::logout()`, session invalidate, redirect landing |

**No Laravel Breeze/Jetstream/Fortify** is used. All auth logic is handwritten in `web.php` closures.

### Login flow (POST `/login`)

1. Validates `email` + `password`.
2. Calls `Auth::attempt($credentials)` — standard Laravel session-based auth.
3. Checks `$user->is_active`; if false, logs out and redirects back.
4. Calls `$request->session()->regenerate()`.
5. Loads `$user->roles` relation.
6. Writes `session(['user' => ['id', 'name', 'email', 'role', 'role_key', 'roles']])`.
7. Writes an `AuditLog` record.
8. Redirects to `Role::homeRoute($primaryRole)`:
   - `administrateur` → `admin.dashboard`
   - `manager` → `manager.dashboard`
   - `technicien` → `technician.dashboard`
   - `citoyen` → `citizen.dashboard`

**The redirect logic is correct.** All four role home routes exist and have corresponding views.

### `config/auth.php`

Standard Laravel config. Guard `web` uses `session` driver with Eloquent `User` model. No issues.

---

## 2. ROLE MIDDLEWARE

### Registration (`bootstrap/app.php`)

```php
$middleware->alias([
    'manager'    => EnsureManager::class,
    'role'       => CheckRole::class,
    'permission' => CheckPermission::class,
]);
```

`IncidentRole` is used inline via FQCN (not aliased) — works but is inconsistent.

### `CheckRole` (`role:…`)

- Resolves user via `Auth::check()` first, then falls back to `session['user']`.
- Supports multiple comma-separated roles: `role:administrateur,manager`.
- Normalizes slugs via `Role::normalizeSlug()`.
- For unauthenticated: redirects to `auth.login`.
- For wrong role: `abort(403)`.
- Contains a mock-user fallback path for tests (not a runtime bug).

### `EnsureManager` (`manager`)

- **Session-only** — does NOT check `Auth::check()`.
- Reads `session('user.role')` and normalizes.
- If role ≠ `manager`, `abort(403)`.
- Checks `is_active` by querying DB only if `email` is in session.

**MEDIUM bug**: Inconsistency with `CheckRole`. If the session `user` key is missing (e.g., session expired mid-request after `Auth::check()` would still return true), `EnsureManager` redirects to login while `CheckRole` would re-authorize via `Auth`. Routes using `middleware('manager')` are slightly less robust than those using `middleware('role:manager')`.

### `CheckPermission` (`permission:…`)

Resolves user the same way as `CheckRole`. Calls `$user->hasPermission($permission)`. Well-implemented.

### `IncidentRole`

- Used as FQCN + parameter: `\App\Http\Middleware\IncidentRole::class . ':citizen'`.
- Uses **session-only** (no `Auth::check()` fallback).
- Normalizes the passed role string — `'citizen'` correctly normalizes to `'citoyen'` via `Role::normalizeSlug()`.
- For `citoyen` role: also queries DB to validate user exists and `is_active`.

No functional bug here, but the session-only check is weaker than `CheckRole`.

---

## 3. AFTER-LOGIN REDIRECT

Handled inline in `web.php` POST `/login`:

```php
return redirect()->route(\App\Models\Role::homeRoute($primaryRole));
```

`Role::homeRoute()` maps all 4 roles:

```php
return match (self::normalizeSlug($role)) {
    self::ADMINISTRATEUR => 'admin.dashboard',
    self::MANAGER        => 'manager.dashboard',
    self::TECHNICIEN     => 'technician.dashboard',
    self::CITOYEN        => 'citizen.dashboard',
    default              => 'landing',
};
```

**This is correct.** All four named routes are defined in `web.php` and have corresponding views.

---

## 4. VIEWS

### Auth views (`resources/views/auth/`)
- `login.blade.php` ✅ — extends `layouts.auth`, fully implemented with form POST to `login.post`.
- `register.blade.php` ✅
- `forgot-password.blade.php` ✅ (stub only — POST handler does nothing).

### Role-based view folders

| Folder | Layout | Status |
|--------|--------|--------|
| `views/admin/` | `layouts.admin` | ✅ All referenced views present |
| `views/manager/` | `layouts.app` | ✅ All referenced views present |
| `views/technician/` | `layouts.technician` | ✅ All referenced views present |
| `views/citizen/` | `layouts.frontoffice` | ✅ All referenced views present |
| `views/front/incidents/` | `layouts.frontoffice` | ✅ All present |

### Layouts (`resources/views/layouts/`)

- `auth.blade.php`, `app.blade.php`, `admin.blade.php`, `manager.blade.php`, `technician.blade.php`, `frontoffice.blade.php`, `public.blade.php` — all exist.
- `frontoffice.blade.php` itself extends `layouts.app`.

---

## 5. ROLE MODEL

**Custom RBAC — no Spatie package** (confirmed: not in `composer.json`).

Roles stored in a dedicated `roles` table with `slug` column. Users linked via `role_user` pivot. Permissions via `permissions` table linked via `permission_role` pivot.

`app/Traits/HasRoles.php` provides:
- `roles()` BelongsToMany relation
- `assignRole()`, `removeRole()`, `syncRoles()`
- `hasRole()`, `hasAnyRole()`, `getRoleSlugs()`
- `primary_role` attribute (priority: administrateur > manager > technicien > citoyen)
- `hasPermission()` with 30-minute cache
- last-administrator protection

`app/Models/Role.php` provides:
- Constants: `ADMINISTRATEUR`, `MANAGER`, `TECHNICIEN`, `CITOYEN`
- `normalizeSlug()` with alias support (`citizen`→`citoyen`, `admin`→`administrateur`, etc.)
- `homeRoute()` — maps role to dashboard route name
- `sessionMatches()` — checks session role

---

## 6. DATABASE

### Migrations (in run order)

1. `0001_01_01_000000_create_users_table` — users, password_reset_tokens, sessions
2. `0001_01_01_000001_create_cache_table` — cache, cache_locks
3. `0001_01_01_000002_create_jobs_table` — jobs
4. `2026_10_06_031836_create_projets_table` — creates `projets` (or alters if exists)
5. `2026_10_06_031836_create_financements_table` — **EMPTY STUB** (intentionally empty)
6. `2026_10_06_031837_create_financements_table` — actual `financements` table
7. `2026_10_06_112600` — `zones`, `infrastructures`
8. `2026_10_07_000001` — `incidents`
9. `2026_10_07_000002` — `action_correctives`
10. `2026_10_07_113243` — adds `progression` to `projets`
11. `2026_10_07_115712` — `techniciens`
12. `2026_10_07_115713` — `interventions`
13. `2026_10_08_000001` — `point_mesures`
14. `2026_10_08_000002` — `mesures_qualites`
15. `2026_10_14_120000_create_rbac_tables` — `roles`, `permissions`, `role_user`, `permission_role`, adds `is_active` to users, `audit_logs`

**users table** has NO `role` string column — roles are exclusively via `role_user` pivot (correct RBAC design).

### Seeders

`DatabaseSeeder` runs: `RbacSeeder`, `IncidentSeeder`, `ActionCorrectiveSeeder`, `TechnicienSeeder`, `WaterQualitySeeder`, `ZoneSeeder`, then factory data for projets/financements.

**`RbacSeeder` creates demo accounts**:

| Email | Password | Role |
|-------|----------|------|
| `admin@aquasecure.tn` | `password123` | administrateur |
| `gestionnaire@aquasecure.tn` | `password123` | manager |
| `amira@aquasecure.tn` | `password123` | technicien |
| `citoyen@aquasecure.tn` | `password123` | citoyen |

---

## 7. BUGS — DETAILED FINDINGS

---

### BUG-01 — CRITICAL: `.env` DB variables have leading spaces

**File**: `.env`

**Evidence**:
```
DB_CONNECTION=mysql
 DB_HOST=127.0.0.1      ← leading space
 DB_PORT=3306           ← leading space
 DB_DATABASE=aquasecure ← leading space
 DB_USERNAME=root       ← leading space
 DB_PASSWORD=           ← leading space
```

The `vlucas/phpdotenv` library (used by Laravel) treats ` DB_HOST` (with leading space) as a different key from `DB_HOST`. Therefore `env('DB_HOST')` returns `null`, `env('DB_PORT')` returns `null`, etc.

**Effect**: Laravel falls back to default values (`127.0.0.1`, `3306`, `forge`). The database name `aquasecure` is completely lost — Laravel will try to connect to a database named `forge`. **The entire application will fail to connect to the database.**

**Fix**: Remove the leading space from each DB line:
```
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=aquasecure
DB_USERNAME=root
DB_PASSWORD=
```

---

### BUG-02 — CRITICAL: Roles must be seeded before `register` route works

**Files**: `routes/web.php` (register closure), `app/Traits/HasRoles.php`

**Evidence**:
```php
// web.php register closure
$user->assignRole(\App\Models\Role::CITOYEN);  // assigns string 'citoyen'

// HasRoles::resolveRoleModel
return Role::where('slug', $slug)->first();  // returns null if table is empty

// HasRoles::assignRole
if ($roleModel) {  // silently skips if $roleModel is null
    $this->roles()->syncWithoutDetaching(...);
}
```

If a user registers before `php artisan db:seed` has been run (or on a fresh DB where only `migrate` was run without seeding), `assignRole('citoyen')` silently does nothing. The new user has no role.

**Effect**: After login, `$user->primary_role` returns `'citoyen'` (the default fallback in `getPrimaryRoleAttribute`), so login redirect works. But `CheckRole` middleware calls `$user->hasAnyRole(['citoyen'])` → `getRoleSlugs()` → empty array → returns `false` → **403 Forbidden on every protected route**. The citizen is completely locked out.

**Fix (two options)**:
1. **Run seeder before accepting registrations** — document in README that `php artisan db:seed` is required.
2. **Make registration robust** — in the register closure, add a guard:
```php
$roleModel = \App\Models\Role::where('slug', \App\Models\Role::CITOYEN)->first();
if ($roleModel) {
    $user->assignRole($roleModel);
} else {
    // Log a warning or throw a clear error
    \Log::error('RbacSeeder has not been run — citoyen role missing');
}
```

---

### BUG-03 — HIGH: `EnsureManager` middleware is session-only; inconsistent with `CheckRole`

**Files**: `app/Http/Middleware/EnsureManager.php`, `routes/web.php` lines 193 and 231

**Evidence**:

`EnsureManager` reads `session('user')` directly — it does **not** call `Auth::check()` or attempt to reload the user from DB via `Auth::login()`. This is used on:
- `Route::middleware('manager')->prefix('manager')->name('manager.')` group (zones, infrastructures)
- Another `middleware('manager')` group (techniciens, interventions)

`CheckRole` (`role:manager`) is used on the main `manager.dashboard`, `manager.budget`, etc. group — it checks `Auth::check()` first and can re-authenticate via session if `Auth` has expired.

**Effect**: If a manager's `Auth` session expires but the session `user` key is still present (possible in some edge cases), routes using `middleware('manager')` will work while routes using `middleware('role:manager')` will redirect to login — inconsistent behavior within the same manager area.

More practically: `EnsureManager` silently lets through any user who has `session['user']['role'] = 'manager'` even if the DB user no longer exists or has been deactivated (it only checks DB `is_active` if email is in session — but the check is `if (! empty($sessionUser['email']))`, which it always is after login).

**Fix**: Either:
1. Replace all `middleware('manager')` usages with `middleware('role:manager')` and remove `EnsureManager`, or
2. Rewrite `EnsureManager` to mirror the `CheckRole` resolveUser pattern.

---

### BUG-04 — MEDIUM: `Gate::authorize('permission', 'roles.view')` uses wrong Gate signature

**Files**: `app/Http/Controllers/Admin/RoleController.php`, `app/Http/Controllers/Admin/AuditLogController.php`

**Evidence**:
```php
// RoleController.php
Gate::forUser($currentUser)->authorize('permission', 'roles.view');

// AuditLogController.php
Gate::forUser($currentUser)->authorize('permission', 'audit.view');
```

`Gate::authorize($ability, $arguments)` looks for a gate or policy named `'permission'`. No such gate is registered via `Gate::define()` anywhere. The `Gate::after()` callback only handles abilities containing `.` (dot-notation):
```php
Gate::after(function ($user, $ability, $result) {
    if ($result !== null) { return $result; }
    if ($user instanceof User && is_string($ability) && str_contains($ability, '.')) {
        return $user->hasPermission($ability);
    }
    return null;
});
```

`'permission'` does not contain `.` — so `Gate::after` returns null. For non-admin users this would result in a 403.

**In practice this is masked**: These controllers are inside `middleware('role:administrateur')` routes, so only admins ever reach them. And `Gate::before` gives admins `true` for all non-protected abilities. So admins work fine. But **the authorization code is semantically wrong** and would break if these controllers were ever accessed by a non-admin.

**Fix**: Replace:
```php
Gate::forUser($currentUser)->authorize('permission', 'roles.view');
```
with:
```php
Gate::forUser($currentUser)->authorize('roles.view');
```
This routes through `Gate::after` correctly and checks `$user->hasPermission('roles.view')`.

---

### BUG-05 — MEDIUM: `TravauxController` statut string case sensitivity risk

**File**: `app/Http/Controllers/TravauxController.php`

**Evidence**:
```php
// TravauxController uses:
->where('statut', 'En cours')
->where('statut', 'Planifiée')
->where('statut', 'Terminée')

// Intervention::STATUTS constant defines:
public const STATUTS = ['Planifiée', 'En cours', 'Terminée', 'Annulée'];

// interventions migration default:
$table->string('statut')->default('Planifiée');
```

The strings match exactly — this is correct. However, note that `InterventionRequest` validation (for manager-created interventions) constrains the allowed values. If any UI ever lowercases the values or the validation changes to lowercase, the citizen travaux page would show nothing.

**Actual risk**: LOW if the database is only written via the validated `InterventionRequest`. Verified as consistent. **No active bug.**

---

### BUG-06 — LOW: Duplicate migration timestamp `2026_10_06_031836_`

**Files**:
- `2026_10_06_031836_create_financements_table.php` (empty stub)
- `2026_10_06_031836_create_projets_table.php` (actual projets table)

Both share the same timestamp prefix. Laravel runs migrations ordered by filename. Since they're different class names, Laravel can distinguish them, but the order within the same timestamp is filesystem-dependent.

The empty stub has a comment explaining it was intentionally emptied to fix a foreign key ordering issue (financements→projets). This is an acceptable workaround but creates confusion.

**Risk**: On some operating systems, file ordering within the same timestamp may vary. The `2026_10_06_031837_create_financements_table.php` (actual financements migration) is safe because it's 1 second later.

**Fix**: Rename the empty stub to a clearly past timestamp (e.g., `2026_10_06_000000_placeholder_financements_stub.php`) so intent is obvious.

---

### BUG-07 — LOW: Password reset is a stub

**File**: `routes/web.php`, `resources/views/auth/forgot-password.blade.php`

The POST `/forgot-password` handler:
```php
return back()->with('success', 'Si ce compte existe, un email a été envoyé.');
```

No actual email is sent. The `MAIL_MAILER=log` in `.env` would at least log it, but the handler does nothing at all.

**Risk**: No security issue (it doesn't leak info), but users who forget passwords have no recovery path.

**Fix**: Implement using Laravel's built-in `Password::sendResetLink($request->only('email'))` with appropriate views, or document that this feature is not yet implemented.

---

## ROUTING CONSISTENCY ANALYSIS

The application uses three different middleware approaches on manager routes, which is confusing but not broken:

| Routes | Middleware used | Auth strategy |
|--------|----------------|---------------|
| `manager.dashboard`, `manager.budget`, `manager.analytics`, `manager.map`, `manager.quality`, `manager.teams`, `manager.reports`, `manager.projets.*`, `manager.financements.*` | `role:manager` | Auth + session fallback |
| `manager.zones.*`, `manager.infrastructures.*`, `manager.techniciens.*`, `manager.interventions.*` | `manager` (EnsureManager) | Session-only |
| `manager.incidents.*`, `manager.actions.*` | `IncidentRole:manager` | Session-only |

**Recommended normalization**: Use `role:manager` everywhere in manager routes and deprecate `EnsureManager`.

---

## QUICK-WIN FIX CHECKLIST

1. **[CRITICAL]** Fix `.env` — remove leading spaces from `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`.
2. **[CRITICAL]** Document / enforce that `php artisan db:seed` must run before registration is used. Optionally add a check in the register closure.
3. **[HIGH]** Replace `middleware('manager')` with `middleware('role:manager')` on zone/infrastructure/technicien/intervention route groups, and delete `EnsureManager.php`.
4. **[MEDIUM]** Fix `Gate::authorize('permission', 'audit.view')` → `Gate::authorize('audit.view')` in `AuditLogController` and `Gate::authorize('permission', 'roles.view')` / `Gate::authorize('permission', 'roles.manage')` → `Gate::authorize('roles.view')` / `Gate::authorize('roles.manage')` in `RoleController`.
5. **[LOW]** Implement the forgot-password flow or clearly mark it as not implemented in UI.
6. **[LOW]** Rename the empty migration stub so its purpose is self-evident.

---

## WHAT IS WORKING CORRECTLY

- `Auth::attempt()` + manual session write after login — both Auth and session state are set correctly.
- Role-based redirect after login — all 4 roles map to correct dashboard routes and views.
- `CheckRole` middleware handles Auth + session fallback correctly for all role-protected routes.
- `Role::normalizeSlug()` handles `citizen`/`citoyen` alias — `IncidentRole:citizen` works.
- RBAC pivot table design is sound — no `role` string column on users.
- Last-administrator protection in delete/deactivate flows.
- All four role dashboards and their views exist.
- `RbacSeeder` creates all 4 demo accounts with correct roles and permissions.
- `Gate::before` ensures admins bypass all authorization checks as expected.
- Logout properly calls `Auth::logout()`, `session()->invalidate()`, and `regenerateToken()`.
