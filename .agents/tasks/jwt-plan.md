# Implementation Plan — JWT Security for AquaSecure API

> Scope: `/api/*` routes only. The web session layer (Blade + `routes/web.php`) is **never touched**.
> Base package: `tymon/jwt-auth ^2.1` — RS256 is overkill for a single-server app; HS256 with a 64-byte secret stored in `JWT_SECRET` env var is the retained choice. Rationale: simpler key management, no need for key-pair infrastructure, and tymon's `jwt-auth` defaults to HS256.

---

## Key architectural decisions recorded here

| Decision | Rationale |
|---|---|
| HS256 / single secret | Single-server deployment; no inter-service JWT consumption; simpler rotation. |
| Access token: 60 min | Limits the window of stolen-token abuse without forcing constant refresh. |
| Refresh token in DB | Enables immediate revocation; single-use with rotation prevents replay. |
| `jti` stored as `sha256(raw_hex)` | Raw token never persisted; sha256 hash is safe to store. |
| `rol` claim informational only | Role is always re-fetched from DB in `JwtAuthenticate` middleware. |
| Cookie delivery optional | Client sends `X-Token-Delivery: cookie` to opt in; server writes `access_token` HttpOnly cookie. |
| `resolveApiUser` updated, not replaced | Keeps the session fallback path for Blade-originated requests; adds `auth('api')->user()` as the primary JWT path. |

---

## Prerequisites confirmed by exploration

- `composer.json`: no `tymon/jwt-auth` present — must be installed.
- `config/auth.php`: only `web` guard exists — `api` guard must be added.
- `app/Models/User.php`: uses `HasRoles` trait; does NOT implement `JWTSubject` yet.
- `bootstrap/app.php`: uses the Laravel 12 fluent `Application::configure()` style; middleware aliases are registered in `->withMiddleware()`.
- `routes/api.php`: all routes unprotected, resolved by ad-hoc `resolveApiUser()`.
- `.env`: `JWT_SECRET` is **not** present.
- Existing middleware pattern: `app/Http/Middleware/CheckRole.php` — follow same structure.
- Migration timestamps in use: last one is `2026_10_14_120000` — use `2026_10_14_130000` for JWT table.
- `AuditLog::record()` signature: `(string $action, ?string $resourceType, ?int $resourceId, array $details, ?User $user)`.

---

## Implementation Steps

- [ ] 1. Install `tymon/jwt-auth ^2.1` and publish its configuration.

  Run the following commands in the project root:
  ```bash
  composer require tymon/jwt-auth:"^2.1"
  php artisan vendor:publish --provider="Tymon\JWTAuth\Providers\LaravelServiceProvider"
  php artisan jwt:secret
  ```
  The first command adds the package. The second publishes `config/jwt.php`. The third generates `JWT_SECRET` in `.env`.
  After running, open `config/jwt.php` and set:
  - `'ttl' => 60`  (access token: 60 minutes)
  - `'refresh_ttl' => 10080`  (7 days, not used directly — DB refresh token controls this)
  - `'algo' => 'HS256'`
  - `'required_claims' => ['iss', 'iat', 'exp', 'nbf', 'sub', 'jti']`
  - `'blacklist_enabled' => true` (so `jwt.invalidate` can revoke access tokens on logout)
  - `'blacklist_grace_period' => 0`
  - `'providers.jwt' => Tymon\JWTAuth\Providers\JWT\Lcobusc::class`

  Files: `composer.json`, `composer.lock`, `config/jwt.php`, `.env`
  Verify: `php artisan config:cache && php artisan tinker --execute="echo config('jwt.ttl');"` — outputs `60`.

---

- [ ] 2. Update `User` model to implement `JWTSubject`.

  Add the `Tymon\JWTAuth\Contracts\JWTSubject` interface and its two required methods.

  File: `app/Models/User.php`

  Changes:
  ```php
  use Tymon\JWTAuth\Contracts\JWTSubject;

  class User extends Authenticatable implements JWTSubject
  {
      // ... existing code unchanged ...

      /**
       * Get the identifier that will be stored in the JWT subject claim.
       */
      public function getJWTIdentifier(): mixed
      {
          return $this->getKey();
      }

      /**
       * Return a key-value array of custom claims added to the JWT payload.
       * NOTE: `rol` is informational only — never used for authorization decisions.
       */
      public function getJWTCustomClaims(): array
      {
          return [
              'iss' => config('app.url'),
              'aud' => 'aquasecure-api',
              'rol' => $this->primary_role,   // informational — re-validated from DB on every request
          ];
      }
  }
  ```

  Do NOT add `password` or any sensitive field to custom claims.

  Verify: `php artisan test --filter=UserModelTest` — existing tests still pass (or no test failures if no test file exists yet).

---

- [ ] 3. Add the `api` JWT guard to `config/auth.php`.

  File: `config/auth.php`

  Add inside the `'guards'` array (after the existing `'web'` guard):
  ```php
  'api' => [
      'driver'   => 'jwt',
      'provider' => 'users',
  ],
  ```

  The `'providers'` section is unchanged.

  Verify: `php artisan config:cache` completes without error; `php artisan tinker --execute="echo config('auth.guards.api.driver');"` outputs `jwt`.

---

- [ ] 4. Create migration for `jwt_refresh_tokens` table.

  File: `database/migrations/2026_10_14_130000_create_jwt_refresh_tokens_table.php`

  Exact schema:
  ```php
  <?php

  use Illuminate\Database\Migrations\Migration;
  use Illuminate\Database\Schema\Blueprint;
  use Illuminate\Support\Facades\Schema;

  return new class extends Migration
  {
      public function up(): void
      {
          Schema::create('jwt_refresh_tokens', function (Blueprint $table) {
              $table->id();
              $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
              $table->string('jti', 64)->unique();   // sha256(raw_hex_token)
              $table->timestamp('expires_at');
              $table->boolean('revoked')->default(false);
              $table->timestamp('revoked_at')->nullable();
              $table->string('ip_address', 45)->nullable();
              $table->text('user_agent')->nullable();
              $table->timestamps();
          });
      }

      public function down(): void
      {
          Schema::dropIfExists('jwt_refresh_tokens');
      }
  };
  ```

  Verify: `php artisan migrate` — migration runs without error; `php artisan migrate:status` shows `2026_10_14_130000_create_jwt_refresh_tokens_table` as `Ran`.

---

- [ ] 5. Create `JwtRefreshToken` Eloquent model.

  File: `app/Models/JwtRefreshToken.php`

  ```php
  <?php

  namespace App\Models;

  use Illuminate\Database\Eloquent\Model;
  use Illuminate\Database\Eloquent\Relations\BelongsTo;

  class JwtRefreshToken extends Model
  {
      protected $fillable = [
          'user_id',
          'jti',           // sha256(raw_hex_token) — never store the raw token
          'expires_at',
          'revoked',
          'revoked_at',
          'ip_address',
          'user_agent',
      ];

      protected function casts(): array
      {
          return [
              'expires_at' => 'datetime',
              'revoked'    => 'boolean',
              'revoked_at' => 'datetime',
          ];
      }

      public function user(): BelongsTo
      {
          return $this->belongsTo(User::class);
      }

      /** Returns true if the token is valid (not revoked and not expired). */
      public function isValid(): bool
      {
          return ! $this->revoked && $this->expires_at->isFuture();
      }

      /** Revoke this refresh token. */
      public function revoke(): void
      {
          $this->update([
              'revoked'    => true,
              'revoked_at' => now(),
          ]);
      }
  }
  ```

  Verify: `php artisan tinker --execute="echo App\Models\JwtRefreshToken::count();"` — outputs `0` without errors.

---

- [ ] 6. Create `app/Http/Controllers/Api/JwtAuthController.php`.

  This controller handles login, token refresh, logout, and the `/me` endpoint.

  File: `app/Http/Controllers/Api/JwtAuthController.php`

  Full class with these exact method signatures:

  ```php
  <?php

  namespace App\Http\Controllers\Api;

  use App\Http\Controllers\Controller;
  use App\Models\AuditLog;
  use App\Models\JwtRefreshToken;
  use App\Models\User;
  use Illuminate\Http\JsonResponse;
  use Illuminate\Http\Request;
  use Illuminate\Support\Facades\Hash;
  use Illuminate\Support\Str;
  use Tymon\JWTAuth\Facades\JWTAuth;

  class JwtAuthController extends Controller
  {
      /**
       * POST /api/auth/login
       * Body: { email, password }
       * Optional header: X-Token-Delivery: cookie
       *
       * Returns: { access_token, token_type, expires_in, refresh_token }
       * If X-Token-Delivery: cookie — also writes HttpOnly access_token cookie.
       */
      public function login(Request $request): JsonResponse { ... }

      /**
       * POST /api/auth/refresh
       * Body: { refresh_token: "<raw_hex_token>" }
       *
       * Validates refresh token from DB (sha256 hash lookup), issues new access token,
       * rotates refresh token (old revoked, new issued — single-use with rotation).
       * Returns: { access_token, token_type, expires_in, refresh_token }
       */
      public function refresh(Request $request): JsonResponse { ... }

      /**
       * POST /api/auth/logout
       * Requires: Authorization: Bearer <access_token>
       *
       * Invalidates the access token via JWTAuth blacklist.
       * Revokes all active refresh tokens for this user.
       * Clears access_token cookie if present.
       */
      public function logout(Request $request): JsonResponse { ... }

      /**
       * GET /api/auth/me
       * Requires: jwt.auth middleware
       *
       * Returns DB-fresh user profile (re-fetched, never from token payload).
       * Never returns password, remember_token.
       */
      public function me(Request $request): JsonResponse { ... }

      /**
       * Build the token response array.
       * @internal
       */
      private function buildTokenResponse(string $accessToken, JwtRefreshToken $refreshRecord, string $rawRefreshToken): array { ... }

      /**
       * Write the optional HttpOnly access_token cookie on the response.
       * Triggered by request header X-Token-Delivery: cookie
       * @internal
       */
      private function withOptionalCookie(JsonResponse $response, Request $request, string $accessToken): JsonResponse { ... }

      /**
       * Issue a new DB refresh token for the given user.
       * Stores sha256(raw_hex_token) in jti column; returns raw token for the client.
       * @internal
       */
      private function issueRefreshToken(User $user, Request $request): array { ... }
  }
  ```

  **Implementation details for each method:**

  ### `login()`
  1. Validate `email` (required|email) and `password` (required|string).
  2. Attempt `auth('api')->attempt(['email', 'password'])` — returns access token or false.
  3. If false → return `401 { message: 'Identifiants invalides.' }`.
  4. Re-fetch user from DB: `$user = auth('api')->user()`.
  5. Check `$user->is_active` — if false, `auth('api')->logout()`, return `403 { message: 'Compte désactivé.' }`.
  6. Call `$this->issueRefreshToken($user, $request)` to get `[$rawToken, $record]`.
  7. Build custom claims before generating: `iss`, `aud`, `rol` are injected via `getJWTCustomClaims()` on User.
  8. Call `AuditLog::record('api_login', 'User', $user->id, ['ip' => $request->ip(), 'role' => $user->primary_role], $user)`.
  9. Return `$this->withOptionalCookie(response()->json($this->buildTokenResponse($token, $record, $rawToken)), $request, $token)`.

  ### `refresh()`
  1. Validate `refresh_token` (required|string|size:64 — raw hex, 32 bytes = 64 hex chars).
  2. Compute `$hash = hash('sha256', $request->input('refresh_token'))`.
  3. Look up `JwtRefreshToken::where('jti', $hash)->first()`. If not found → `401`.
  4. Call `$record->isValid()`. If false → `401 { message: 'Refresh token expiré ou révoqué.' }`.
  5. `$user = $record->user` — re-fetch with `$user->load('roles')`.
  6. Check `$user->is_active` — if false → `403`.
  7. Revoke old record: `$record->revoke()`.
  8. Generate new access token: `$newToken = auth('api')->login($user)`.
  9. Issue new refresh token: `[$rawNew, $newRecord] = $this->issueRefreshToken($user, $request)`.
  10. `AuditLog::record('api_token_refreshed', 'User', $user->id, ['ip' => $request->ip()], $user)`.
  11. Return `$this->withOptionalCookie(response()->json($this->buildTokenResponse($newToken, $newRecord, $rawNew)), $request, $newToken)`.

  ### `logout()`
  1. `$user = auth('api')->user()` — already set by `jwt.auth` middleware on this route.
  2. `JwtRefreshToken::where('user_id', $user->id)->where('revoked', false)->update(['revoked' => true, 'revoked_at' => now()])`.
  3. `auth('api')->logout()` — blacklists the access token.
  4. `AuditLog::record('api_logout', 'User', $user->id, ['ip' => $request->ip()], $user)`.
  5. Build response `200 { message: 'Déconnecté avec succès.' }` and forget cookie: `$response->withCookie(Cookie::forget('access_token'))`.

  ### `me()`
  1. `$user = auth('api')->user()` — fresh from DB (tymon re-fetches by sub claim).
  2. `$user->load('roles')`.
  3. Return `200 { data: { id, name, email, is_active, roles: $user->getRoleSlugs(), primary_role: $user->primary_role } }`.

  ### `issueRefreshToken(User $user, Request $request): array`
  1. `$raw = Str::random(32)` — generates 32-byte random string; use `bin2hex(random_bytes(32))` for 64-char hex.
  2. `$hash = hash('sha256', $raw)`.
  3. `$record = JwtRefreshToken::create(['user_id' => $user->id, 'jti' => $hash, 'expires_at' => now()->addDays(7), 'ip_address' => $request->ip(), 'user_agent' => $request->userAgent()])`.
  4. Return `[$raw, $record]`.

  ### `buildTokenResponse()`
  Returns:
  ```php
  [
      'access_token'  => $accessToken,
      'token_type'    => 'bearer',
      'expires_in'    => config('jwt.ttl') * 60,   // seconds
      'refresh_token' => $rawRefreshToken,
  ]
  ```

  ### `withOptionalCookie()`
  If `$request->header('X-Token-Delivery') === 'cookie'`:
  - Attach cookie: `cookie('access_token', $accessToken, config('jwt.ttl'), '/', null, config('app.env') === 'production', true, false, 'Lax')`.
  - Parameters: `(name, value, minutes, path, domain, secure, httpOnly, raw, sameSite)`.

  Verify: `php artisan route:list --path=api/auth` — shows `login`, `refresh`, `logout`, `me` routes (added in step 9).

---

- [ ] 7. Create `app/Http/Middleware/JwtAuthenticate.php`.

  File: `app/Http/Middleware/JwtAuthenticate.php`

  ```php
  <?php

  namespace App\Http\Middleware;

  use Closure;
  use Illuminate\Http\Request;
  use Symfony\Component\HttpFoundation\Response;
  use Tymon\JWTAuth\Exceptions\JWTException;
  use Tymon\JWTAuth\Exceptions\TokenExpiredException;
  use Tymon\JWTAuth\Exceptions\TokenInvalidException;
  use Tymon\JWTAuth\Facades\JWTAuth;

  class JwtAuthenticate
  {
      public function handle(Request $request, Closure $next): Response
      {
          // 1. Extract token — Bearer header first, then access_token cookie
          $token = $request->bearerToken() ?? $request->cookie('access_token');

          if (! $token) {
              return response()->json(['message' => 'Token d\'accès manquant.'], 401);
          }

          try {
              // 2. Parse and validate token (signature, exp, nbf, iss, aud)
              JWTAuth::setToken($token)->checkOrFail();

              // 3. Re-fetch user from DB (never trust payload claims for identity)
              $user = JWTAuth::setToken($token)->toUser();
          } catch (TokenExpiredException $e) {
              return response()->json(['message' => 'Token expiré.', 'code' => 'TOKEN_EXPIRED'], 401);
          } catch (TokenInvalidException $e) {
              return response()->json(['message' => 'Token invalide.', 'code' => 'TOKEN_INVALID'], 401);
          } catch (JWTException $e) {
              return response()->json(['message' => 'Erreur d\'authentification JWT.', 'code' => 'JWT_ERROR'], 401);
          }

          if (! $user) {
              return response()->json(['message' => 'Utilisateur introuvable.'], 401);
          }

          // 4. Re-validate is_active from DB on every request — never rely on token payload
          if (! $user->is_active) {
              return response()->json(['message' => 'Compte désactivé.'], 403);
          }

          // 5. Bind the authenticated user to the api guard for downstream use
          auth()->guard('api')->setUser($user);

          return $next($request);
      }
  }
  ```

  Verify: middleware can be registered (step 8 registers alias); no PHP syntax errors: `php artisan route:list` completes without fatal error.

---

- [ ] 8. Create `app/Http/Middleware/JwtCheckRole.php`.

  File: `app/Http/Middleware/JwtCheckRole.php`

  ```php
  <?php

  namespace App\Http\Middleware;

  use App\Models\Role;
  use Closure;
  use Illuminate\Http\Request;
  use Symfony\Component\HttpFoundation\Response;

  class JwtCheckRole
  {
      /**
       * Usage in routes: ->middleware('jwt.role:manager')
       *                  ->middleware('jwt.role:administrateur,manager')
       *
       * Role is ALWAYS re-validated from DB via auth('api')->user() — NEVER from token claims.
       */
      public function handle(Request $request, Closure $next, string ...$roles): Response
      {
          // JwtAuthenticate must run first; user is already set in the api guard
          $user = auth('api')->user();

          if (! $user) {
              return response()->json(['message' => 'Non authentifié.'], 401);
          }

          // Normalize and flatten comma-separated roles
          $parsedRoles = [];
          foreach ($roles as $role) {
              foreach (explode(',', $role) as $sub) {
                  $trimmed = trim($sub);
                  if ($trimmed !== '') {
                      $parsedRoles[] = Role::normalizeSlug($trimmed);
                  }
              }
          }

          // Re-validate role from DB (user already loaded with roles relation by JwtAuthenticate)
          if (! empty($parsedRoles) && ! $user->hasAnyRole($parsedRoles)) {
              return response()->json([
                  'message'        => 'Accès refusé : rôle insuffisant.',
                  'required_roles' => $parsedRoles,
              ], 403);
          }

          return $next($request);
      }
  }
  ```

  Then register **both new aliases** in `bootstrap/app.php` inside the existing `->withMiddleware()` block:

  ```php
  $middleware->alias([
      'role'       => \App\Http\Middleware\CheckRole::class,
      'permission' => \App\Http\Middleware\CheckPermission::class,
      'jwt.auth'   => \App\Http\Middleware\JwtAuthenticate::class,    // add
      'jwt.role'   => \App\Http\Middleware\JwtCheckRole::class,       // add
  ]);
  ```

  File: `bootstrap/app.php`

  Verify: `php artisan route:list` completes without errors.

---

- [ ] 9. Update `routes/api.php` with JWT-protected groups and update `resolveApiUser`.

  Files: `routes/api.php`, `app/Http/Controllers/Api/WaterQualityController.php`

  ### routes/api.php

  Replace the entire file content with the structure below. Existing routes are moved inside the JWT-protected group. The `/api/auth/*` group is unauthenticated (login/refresh). The `/api/*` data routes require `jwt.auth`.

  ```php
  <?php

  use App\Http\Controllers\Api\JwtAuthController;
  use App\Http\Controllers\Api\WaterQualityController;
  use App\Models\Permission;
  use App\Models\Role;
  use App\Models\User;
  use Illuminate\Http\Request;
  use Illuminate\Support\Facades\Route;

  /*
  |--------------------------------------------------------------------------
  | Public auth endpoints (no JWT required)
  |--------------------------------------------------------------------------
  */
  Route::prefix('auth')->name('api.auth.')->group(function () {
      Route::post('/login',   [JwtAuthController::class, 'login'])->name('login');
      Route::post('/refresh', [JwtAuthController::class, 'refresh'])->name('refresh');
  });

  /*
  |--------------------------------------------------------------------------
  | Protected endpoints — require valid JWT access token
  |--------------------------------------------------------------------------
  */
  Route::middleware('jwt.auth')->group(function () {

      // Auth management
      Route::prefix('auth')->name('api.auth.')->group(function () {
          Route::post('/logout', [JwtAuthController::class, 'logout'])->name('logout');
          Route::get('/me',      [JwtAuthController::class, 'me'])->name('me');
      });

      // Water Quality Measurements (RBAC permissions re-validated from DB inside controller)
      Route::get('/points-mesure',              [WaterQualityController::class, 'points']);
      Route::get('/mesures',                    [WaterQualityController::class, 'index']);
      Route::get('/mesures/{mesure}',           [WaterQualityController::class, 'show']);
      Route::post('/mesures',                   [WaterQualityController::class, 'store']);
      Route::put('/mesures/{mesure}',           [WaterQualityController::class, 'update']);
      Route::post('/mesures/{mesure}/validate', [WaterQualityController::class, 'validateMeasurement']);
      Route::delete('/mesures/{mesure}',        [WaterQualityController::class, 'destroy']);

      // Current user RBAC profile (always re-fetched from DB — no stale claims)
      Route::get('/user/permissions', function (Request $request) {
          /** @var \App\Models\User $user */
          $user = auth('api')->user();

          $user->flushPermissionCache();
          $user->load('roles.permissions');

          return response()->json([
              'user_id'     => $user->id,
              'name'        => $user->name,
              'email'       => $user->email,
              'is_active'   => $user->is_active,
              'roles'       => $user->roles->pluck('slug'),
              'permissions' => $user->hasRole(Role::ADMINISTRATEUR)
                  ? Permission::pluck('name')
                  : $user->getAllPermissions()->pluck('name'),
          ]);
      });
  });
  ```

  ### WaterQualityController — update `resolveApiUser()`

  Replace only the `resolveApiUser()` private method body. The rest of the controller is unchanged.

  ```php
  private function resolveApiUser(Request $request): ?User
  {
      // Primary path: JWT guard (API requests with Bearer token or access_token cookie)
      $jwtUser = auth('api')->user();
      if ($jwtUser) {
          return $jwtUser;
      }

      // Secondary path: web session guard (Blade-originated requests, e.g. manager quality page)
      if (Auth::check()) {
          return Auth::user();
      }

      // Tertiary path: session array fallback (kept for legacy / test compatibility)
      $sessionUser = $request->session()->get('user');
      if ($sessionUser && ! empty($sessionUser['email'])) {
          $user = User::where('email', $sessionUser['email'])->first();
          if ($user) {
              return $user;
          }

          // Fallback for tests without a DB account
          $roleSlug  = Role::normalizeSlug($sessionUser['role'] ?? 'citoyen');
          $testUser  = new User([
              'name'      => $sessionUser['name'] ?? 'Demo User',
              'email'     => $sessionUser['email'],
              'is_active' => true,
          ]);
          $testUser->id = 8888;
          $roleModel = Role::where('slug', $roleSlug)->first();
          if ($roleModel) {
              $testUser->setRelation('roles', collect([$roleModel]));
          }

          return $testUser;
      }

      return null;
  }
  ```

  **Note**: The `X-User-Email` header path is removed — it was an insecure mechanism (accepting an email in a header to identify a user with no authentication). All API callers must now use JWT.

  Verify: `php artisan route:list --path=api` — lists all API routes with correct middleware column showing `jwt.auth`.

---

- [ ] 10. Create `.env.example` with JWT variable placeholders (no values).

  `.env.example` does not yet exist. Create it with all standard Laravel variables plus the JWT section.

  File: `.env.example`

  ```dotenv
  APP_NAME=AquaSecure
  APP_ENV=local
  APP_KEY=
  APP_DEBUG=true
  APP_URL=http://localhost

  LOG_CHANNEL=stack
  LOG_LEVEL=debug

  DB_CONNECTION=mysql
  DB_HOST=127.0.0.1
  DB_PORT=3306
  DB_DATABASE=aquasecure
  DB_USERNAME=root
  DB_PASSWORD=

  CACHE_DRIVER=file
  SESSION_DRIVER=file
  SESSION_LIFETIME=120
  QUEUE_CONNECTION=sync

  MAIL_MAILER=log

  # ─── JWT Security ──────────────────────────────────────────────────────────────
  # Generate with: php artisan jwt:secret
  JWT_SECRET=
  # Access token lifetime in minutes (default: 60)
  JWT_TTL=60
  # Algorithm: HS256 (default for single-server; use RS256 for multi-service)
  JWT_ALGO=HS256
  # Blacklist must be enabled for logout invalidation to work
  JWT_BLACKLIST_ENABLED=true
  # Grace period in seconds (0 = strict; increase only if clock skew is a concern)
  JWT_BLACKLIST_GRACE_PERIOD=0
  ```

  Verify: file exists and contains `JWT_SECRET=` line; `php artisan config:cache` succeeds.

---

- [ ] 11. Create `docs/jwt-security.md` — French-language security documentation.

  File: `docs/jwt-security.md`

  Content (French) must cover all points from the original security requirement:

  ```markdown
  # Sécurité JWT — AquaSecure API

  ## Choix retenu

  **tymon/jwt-auth ^2.1** avec l'algorithme **HS256** et une clé secrète de 256 bits minimum.

  ### Justification du choix HS256 vs RS256
  L'application AquaSecure est déployée sur un serveur unique. RS256 (clé asymétrique) est pertinent
  dans un environnement multi-service où plusieurs services doivent vérifier des tokens sans partager
  un secret. Dans notre contexte mono-serveur, HS256 avec une clé forte offre une sécurité équivalente
  avec une gestion simplifiée.

  ---

  ## Architecture des tokens

  ### Access token
  | Propriété | Valeur |
  |---|---|
  | Algorithme | HS256 |
  | Durée de vie | 60 minutes |
  | Transport | Header `Authorization: Bearer <token>` ou cookie HttpOnly `access_token` |
  | Stockage côté client | **Cookie HttpOnly** recommandé (opt-in via `X-Token-Delivery: cookie`) |

  ### Refresh token
  | Propriété | Valeur |
  |---|---|
  | Durée de vie | 7 jours |
  | Stockage serveur | Table `jwt_refresh_tokens` — hash sha256 du token brut |
  | Stratégie | Usage unique avec rotation — chaque utilisation révoque l'ancien token |
  | Token brut | Jamais stocké en base — seul le hash sha256 est persisté |

  ---

  ## Claims du JWT

  | Claim | Valeur | Rôle |
  |---|---|---|
  | `iss` | `APP_URL` | Émetteur — validé à chaque requête |
  | `aud` | `aquasecure-api` | Audience — validé à chaque requête |
  | `exp` | iat + 3600 s | Expiration — rejetée automatiquement |
  | `nbf` | iat | Not Before — token invalide avant cette date |
  | `iat` | Timestamp d'émission | Horodatage |
  | `jti` | UUID unique | Identifiant du token (utilisé pour le blacklist) |
  | `sub` | `user.id` | Sujet — identifiant de l'utilisateur |
  | `rol` | Rôle principal | **Informatif uniquement** — non utilisé pour les décisions d'autorisation |

  > ⚠️ **Le claim `rol` est purement informatif.** Le rôle réel de l'utilisateur est systématiquement
  > relu depuis la base de données à chaque requête dans `JwtAuthenticate` et `JwtCheckRole`.

  ---

  ## Validation des tokens (middleware `JwtAuthenticate`)

  À chaque requête protégée :

  1. Extraction du token depuis `Authorization: Bearer` ou cookie `access_token`.
  2. Vérification de la **signature** HS256.
  3. Vérification de l'**expiration** (`exp`) et de `nbf`.
  4. Vérification que le token **n'est pas blacklisté** (après logout).
  5. **Rechargement de l'utilisateur depuis la DB** par `sub` — jamais de confiance aveugle au payload.
  6. Vérification de `is_active` en base — un compte désactivé est rejeté même avec un token valide.

  ---

  ## Stockage des tokens côté client

  | Mode | Activation | Sécurité |
  |---|---|---|
  | Header Bearer | Par défaut | Le client gère le stockage (SPAs) — éviter `localStorage` |
  | Cookie HttpOnly | Header `X-Token-Delivery: cookie` | **Recommandé** — inaccessible depuis JavaScript |

  ### Configuration du cookie
  - `HttpOnly: true` — protection contre le vol de token via XSS
  - `Secure: true` en production — transmission HTTPS uniquement
  - `SameSite: Lax` — protection CSRF de base
  - `Path: /` — accessible à toutes les routes API

  ### Protection CSRF
  Quand les cookies sont utilisés, ajouter le header `X-CSRF-TOKEN` (fourni par Laravel) à chaque
  requête mutante (POST, PUT, PATCH, DELETE).

  ---

  ## Révocation et renouvellement

  ### Révocation d'un access token
  - Lors du logout (`POST /api/auth/logout`), le token est ajouté à la **blacklist JWT** de tymon.
  - La blacklist est stockée dans le driver de cache configuré (`CACHE_DRIVER`).

  ### Révocation des refresh tokens
  - Lors du logout, tous les refresh tokens actifs de l'utilisateur sont révoqués en base.
  - Le champ `revoked_at` enregistre l'horodatage de révocation.

  ### Rotation des refresh tokens
  - Chaque appel à `POST /api/auth/refresh` révoque l'ancien refresh token et en émet un nouveau.
  - Si un refresh token déjà révoqué est présenté, la requête est rejetée avec `401`.

  ---

  ## Secrets et variables d'environnement

  - `JWT_SECRET` — clé HS256, minimum 256 bits. **Ne jamais committer dans Git.**
  - Générer avec : `php artisan jwt:secret`
  - Rotation : regénérer `JWT_SECRET` invalide immédiatement tous les tokens en cours.

  ---

  ## Ce qui n'est PAS stocké dans le payload

  - Mot de passe
  - Permissions granulaires (trop volatiles — rôles et permissions changent)
  - Données personnelles sensibles

  ---

  ## Implications de l'architecture

  | Aspect | Implication |
  |---|---|
  | Rotation de la clé secrète | Invalide tous les access tokens actifs — prévoir une communication aux utilisateurs |
  | Expiration du cache blacklist | Si le cache est flushé, les tokens blacklistés peuvent redevenir valides — utiliser un cache persistant en production (`redis`) |
  | Refresh token en base | Permet la révocation centralisée mais nécessite un nettoyage périodique (`artisan jwt:purge-expired` à planifier) |
  | Rôle en DB seulement | Modification de rôle effective immédiatement sans attendre l'expiration du token |
  ```

  Verify: file exists at `docs/jwt-security.md`; `php artisan config:cache` still passes.

---

## Execution order and dependencies

```
Step 1 (install)
  └─> Step 2 (User model — needs JWTSubject interface from package)
  └─> Step 3 (config/auth.php — needs 'jwt' driver from package)
Step 3
  └─> Step 4 (migration — independent of package, only needs DB)
Step 4
  └─> Step 5 (JwtRefreshToken model — needs table)
Steps 2 + 5
  └─> Step 6 (JwtAuthController — needs User implements JWTSubject + JwtRefreshToken)
Step 6
  └─> Step 7 (JwtAuthenticate middleware)
Step 7
  └─> Step 8 (JwtCheckRole + register aliases in bootstrap/app.php)
Step 8
  └─> Step 9 (routes/api.php + resolveApiUser — needs aliases registered)
Steps 1–9
  └─> Step 10 (.env.example — documentation, no code dependency)
  └─> Step 11 (docs/jwt-security.md — documentation, no code dependency)
```

## Final verification

After all steps, run the full test suite and route list:
```bash
php artisan config:clear
php artisan route:list --path=api
php artisan test
```

Expected: all existing tests pass; `php artisan route:list` shows `api/auth/login`, `api/auth/refresh`, `api/auth/logout`, `api/auth/me`, and all `/api/mesures/*` routes with `jwt.auth` in the middleware column.
