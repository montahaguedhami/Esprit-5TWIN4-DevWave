# JWT Security Implementation for AquaSecure API

The change introduces a complete JWT authentication layer scoped exclusively to `/api/*` routes using `tymon/jwt-auth ^2.1` with HS256. It delivers login, refresh, logout, and `/me` endpoints through `JwtAuthController`; token validation via `JwtAuthenticate` middleware; role enforcement via `JwtCheckRole`; and a dedicated `jwt_refresh_tokens` table for DB-backed refresh token lifecycle management. The Blade/session layer (`routes/web.php`) is untouched. The core security posture is strong — DB re-validation of both identity and roles on every request, single-use refresh token rotation, and access token blacklisting on logout — but three confirmed issues need to be addressed before production: the `aud` claim is emitted in the token payload but not listed in `required_claims` so tymon never validates it; `expires_in` is hardcoded to 3600 rather than reading `config('jwt.ttl')`; and both the middleware and the login controller return 401 for a disabled account when 403 is the correct semantic.

**Watch for:** `aud` not in `required_claims` (confirmed, checklist item 1 violated); hardcoded `expires_in: 3600` ignores `JWT_TTL` env changes; deactivated accounts return 401 everywhere but should return 403; no rate limiting on `/api/auth/login` or `/api/auth/refresh`.

**Verdict**: NEEDS_CHANGES

---

## High-level view

The `required_claims` list in `config/jwt.php` covers `iss`, `iat`, `exp`, `nbf`, `sub`, and `jti`, but `aud` is absent. The controller emits `aud: aquasecure-api` via `getJWTCustomClaims()` and the docs describe its validation, but tymon only enforces claims that appear in `required_claims` — so a token with a forged or missing audience is accepted silently.

The refresh token path correctly stores `sha256(raw_hex)`, enforces single-use rotation, and re-validates `is_active` before issuing a new pair. The `issueTokenPair()` method revokes all existing active tokens for the user on every call — this is broader than a strict single-use rotation but is safe and simplifies concurrent-session cleanup.

Cookie delivery is opt-in via `X-Token-Delivery: cookie`. The `access_token` and `refresh_token` cookies are written with `HttpOnly: true`, `Secure: true` (production), `SameSite: lax`. The `SameSite` value is lowercase `'lax'` — Symfony's `Cookie` class accepts this, but the canonical form is `'Lax'`; worth normalising. The `EncryptCookies` middleware is not configured to exclude these cookies — since `config/jwt.php` sets `decrypt_cookies: false`, the raw JWT in the cookie will be double-encrypted by Laravel and fail to parse on the server side. This is a confirmed blocking gap for cookie-mode users.

The `expires_in` field in every token response is hardcoded to `3600` seconds. If `JWT_TTL` is changed in the environment, the response will advertise the wrong lifetime to clients while the actual token TTL changes correctly. This is a confirmed inconsistency.

Deactivated accounts are caught correctly in both paths, but both `JwtAuthenticate` and `JwtAuthController::login()` return 401 for an inactive account. The docs and RFC 7235 semantics both point to 403 for this case.

No rate limiting is applied to `/api/auth/login` or `/api/auth/refresh`. Both are public endpoints where brute-force or token-stuffing is a realistic attack vector.

---

<details>
<summary>Issues (6)</summary>

1. **`aud` not validated** — `aud` is emitted in the token but absent from `required_claims` in `config/jwt.php`. Add `'aud'` to the `required_claims` array. Checklist item 1 violated. (confirmed)

2. **Cookie double-encryption** — `config/jwt.php` has `decrypt_cookies: false` but the `EncryptCookies` middleware is not configured to exclude `access_token` and `refresh_token`. Cookies written by `applyTokenCookies()` will be encrypted by Laravel and fail to parse as JWTs. Add both cookie names to the `$except` array in `App\Http\Middleware\EncryptCookies` (or set `decrypt_cookies: true` in `config/jwt.php`). (confirmed — blocks cookie delivery mode entirely)

3. **Hardcoded `expires_in: 3600`** — `issueTokenPair()` returns `'expires_in' => 3600` regardless of `JWT_TTL`. Replace with `config('jwt.ttl') * 60`. (confirmed)

4. **Inactive-account status code** — Both `JwtAuthenticate` and `JwtAuthController::login()` return 401 for a disabled account. 403 is the correct semantic (identity is confirmed, access is denied). Update both paths. (confirmed)

5. **No rate limiting on auth endpoints** — `/api/auth/login` and `/api/auth/refresh` have no `throttle` middleware. Apply `throttle:5,1` (5 attempts per minute per IP) as a minimum. Noted as a future recommendation in the docs but the endpoints are live without it. (confirmed)

6. **`expires_in` inconsistency between docs and config** — `docs/jwt-security.md` references `tymon/jwt-auth ^2.3` in the header but `composer.json` targets `^2.1`. Minor, but misleads operators checking the docs. (confirmed)

</details>

---

<details>
<summary>Details</summary>

## `aud` claim emitted but never enforced

`User::getJWTCustomClaims()` adds `'aud' => 'aquasecure-api'` to every issued token. The `docs/jwt-security.md` claims the audience is validated at every request. However, `config/jwt.php`'s `required_claims` array contains only `['iss', 'iat', 'exp', 'nbf', 'sub', 'jti']` — `aud` is not listed. tymon/jwt-auth enforces the `required_claims` list when parsing a token; claims outside that list are carried through but never checked. A token with `"aud": "evil-service"` or with the claim stripped entirely is accepted by the middleware without error. Adding `'aud'` to `required_claims` closes this gap; tymon will then reject any token whose audience doesn't match. (confirmed — verified by cross-referencing `config/jwt.php` lines 142–149 with the `getJWTCustomClaims()` return value)

## Cookie double-encryption gap

`config/jwt.php` sets `'decrypt_cookies' => false`, which means tymon will not attempt to decrypt cookies before parsing. This is the setting for when Laravel's `EncryptCookies` middleware has been configured to exclude the JWT cookie names, so the cookies arrive unencrypted. However, nothing in the codebase adds `access_token` or `refresh_token` to `EncryptCookies::$except`. The result: Laravel encrypts the cookie on the way out, the raw encrypted blob is sent to the client, the client echoes it back, and tymon reads an encrypted string as if it were a raw JWT — which fails signature validation with `TokenInvalidException`. Cookie-mode login works in the sense that the response is returned, but every subsequent request using the cookie will be rejected. Fix is to add to `App\Http\Middleware\EncryptCookies`:

```php
protected $except = [
    'access_token',
    'refresh_token',
];
```

or alternatively set `'decrypt_cookies' => true` in `config/jwt.php` to let tymon handle the decryption. (confirmed)

## `expires_in` hardcoded at 3600

`issueTokenPair()` returns `'expires_in' => 3600` unconditionally. If an operator sets `JWT_TTL=30` for a shorter session window, the actual token expires in 30 minutes but clients are told they have 60. The fix is one line:

```php
'expires_in' => config('jwt.ttl') * 60,
```

(confirmed — line 47 of JwtAuthController.php)

## Inactive-account response code

Both `JwtAuthenticate` (line 49) and `JwtAuthController::login()` (line 84) return 401 for `is_active = false`. RFC 7235 semantics: 401 means "I don't know who you are", 403 means "I know who you are, but you're not allowed." A deactivated account is an authorization rejection. Using 401 causes clients to prompt for re-login when the correct UX is a "your account is disabled" message. Both paths should return 403 for `is_active = false`.

## No rate limiting on public auth endpoints

`/api/auth/login` and `/api/auth/refresh` carry no `throttle` middleware. An attacker can enumerate credentials or replay stolen refresh tokens at full network speed. The docs acknowledge this as a future recommendation but the routes are already in production shape. Wrapping the public `auth` prefix group with `throttle:5,1` is a two-line change:

```php
Route::prefix('auth')->middleware('throttle:5,1')->group(function () {
    Route::post('login',   [JwtAuthController::class, 'login']);
    Route::post('refresh', [JwtAuthController::class, 'refresh']);
});
```

(confirmed — absence verified in routes/api.php)

## Security checklist pass

| # | Item | Status |
|---|------|--------|
| 1 | Signature, exp, nbf, iss, aud, jti, sub validated | ⚠️ `aud` not in `required_claims` — **BLOCKING** |
| 2 | JWT_SECRET in .env only, never hard-coded | ✅ `config/jwt.php` reads `env('JWT_SECRET')` only |
| 3 | Refresh token stored as sha256 hash, raw returned to client | ✅ `bin2hex(random_bytes(32))` raw, `hash('sha256', $raw)` stored |
| 4 | Refresh token single-use with rotation | ✅ Old record revoked before new issued in `issueTokenPair()` |
| 5 | `is_active` re-validated from DB in `JwtAuthenticate` | ✅ `User::find($user->id)` + `is_active` check on every request |
| 6 | Role re-validated from DB in `JwtCheckRole` | ✅ `$user->hasAnyRole($roles)` — `HasRoles` trait reads DB |
| 7 | Logout revokes all refresh tokens + blacklists access token | ✅ `auth('api')->invalidate(true)` + bulk `revoked = true` update |
| 8 | HttpOnly, Secure (prod), SameSite=Lax on cookies | ⚠️ Cookie double-encryption blocks delivery — **BLOCKING** |
| 9 | No sensitive data in JWT payload | ✅ `rol` (role slug) only; no password, permissions, or PII |
| 10 | `routes/web.php` not modified | ✅ grep confirmed no JWT references in web.php |
| 11 | No secrets hard-coded in committed files | ✅ All secrets via `env()` |
| 12 | PHP syntax valid on all new files | ✅ All files passed `php -l` per verification notes |

## Docs quality

`docs/jwt-security.md` is thorough and accurately describes the intended architecture. Two corrections needed: the package version in the header (`^2.3`) does not match `composer.json` (`^2.1`), and the docs state that `aud` is validated per-request — which is currently false until `aud` is added to `required_claims`.

</details>

---

<details>
<summary>File map</summary>

| File | Change |
|------|--------|
| `app/Http/Controllers/Api/JwtAuthController.php` | New — login, refresh, logout, me, issueTokenPair, applyTokenCookies |
| `app/Http/Middleware/JwtAuthenticate.php` | New — Bearer/cookie extraction, tymon validation, DB is_active check |
| `app/Http/Middleware/JwtCheckRole.php` | New — variadic role check delegating to HasRoles trait (DB) |
| `app/Models/JwtRefreshToken.php` | New — Eloquent model for refresh token records |
| `app/Models/User.php` | Modified — added JWTSubject interface + getJWTIdentifier/getJWTCustomClaims |
| `database/migrations/2026_10_14_130000_create_jwt_refresh_tokens_table.php` | New — jwt_refresh_tokens schema with jti, expires_at, revoked |
| `config/auth.php` | Modified — added `api` JWT guard |
| `config/jwt.php` | New (vendor:publish) — HS256, TTL=60, blacklist enabled |
| `routes/api.php` | Modified — jwt.auth groups, role-based sub-groups, permissions endpoint |
| `bootstrap/app.php` | Modified — registered jwt.auth and jwt.role middleware aliases |
| `docs/jwt-security.md` | New — French-language security documentation |

Full diff: `git diff main` from project root.

</details>
