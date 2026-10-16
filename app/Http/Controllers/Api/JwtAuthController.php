<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\JwtRefreshToken;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Exceptions\JWTException;

class JwtAuthController extends Controller
{
    /**
     * Issue a new access + refresh token pair for the given user.
     */
    private function issueTokenPair(User $user, Request $request): array
    {
        // Access token (60 min via config)
        $accessToken = auth('api')->login($user);

        // Refresh token: random hex, store sha256 hash
        $rawRefresh = bin2hex(random_bytes(32));
        $hashed = hash('sha256', $rawRefresh);

        // Revoke all active refresh tokens for this user (cleanup)
        JwtRefreshToken::where('user_id', $user->id)
            ->where('expires_at', '>', now())
            ->where('revoked', false)
            ->update(['revoked' => true, 'revoked_at' => now()]);

        JwtRefreshToken::create([
            'user_id'    => $user->id,
            'jti'        => $hashed,
            'expires_at' => now()->addDays(7),
            'revoked'    => false,
            'ip_address' => $request->ip(),
            'user_agent' => substr($request->userAgent() ?? '', 0, 512),
        ]);

        return [
            'token_type'    => 'bearer',
            'access_token'  => $accessToken,
            'expires_in'    => (int) config('jwt.ttl', 60) * 60, // seconds — driven by JWT_TTL env
            'refresh_token' => $rawRefresh,
        ];
    }

    /**
     * Optionally attach HttpOnly cookies to the response when client sends
     * the X-Token-Delivery: cookie header.
     */
    private function applyTokenCookies(JsonResponse $response, array $tokenData, bool $expire = false): JsonResponse
    {
        $secure = app()->environment('production');

        if ($expire) {
            return $response
                ->withCookie(cookie()->forget('access_token'))
                ->withCookie(cookie()->forget('refresh_token'));
        }

        return $response
            ->withCookie(cookie('access_token',  $tokenData['access_token'],  60,    '/',                    null, $secure, true, false, 'lax'))
            ->withCookie(cookie('refresh_token', $tokenData['refresh_token'], 10080, '/api/auth/refresh', null, $secure, true, false, 'lax'));
    }

    /**
     * POST /api/auth/login
     */
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (!$user) {
            return response()->json(['message' => 'Identifiants invalides.'], 401);
        }

        if (!$user->is_active) {
            return response()->json(['message' => 'Compte désactivé.'], 403);
        }

        if (!Hash::check($credentials['password'], $user->password)) {
            return response()->json(['message' => 'Identifiants invalides.'], 401);
        }

        $tokenData = $this->issueTokenPair($user, $request);

        try {
            AuditLog::record('api_login', 'User', $user->id, [
                'ip'   => $request->ip(),
                'role' => $user->primary_role,
            ], $user);
        } catch (\Throwable $e) {
            // Non-blocking — audit failure must not prevent login
        }

        $response = response()->json($tokenData);

        if ($request->header('X-Token-Delivery') === 'cookie') {
            $response = $this->applyTokenCookies($response, $tokenData);
        }

        return $response;
    }

    /**
     * POST /api/auth/refresh
     */
    public function refresh(Request $request): JsonResponse
    {
        $raw = $request->input('refresh_token') ?? $request->cookie('refresh_token');

        if (!$raw) {
            return response()->json([
                'message' => 'Refresh token manquant.',
                'error'   => 'missing_refresh_token',
            ], 401);
        }

        $hashed = hash('sha256', $raw);
        $record = JwtRefreshToken::where('jti', $hashed)
            ->where('revoked', false)
            ->where('expires_at', '>', now())
            ->first();

        if (!$record) {
            return response()->json([
                'message' => 'Refresh token invalide ou expiré.',
                'error'   => 'invalid_refresh_token',
            ], 401);
        }

        // Revoke old refresh token (single-use rotation)
        $record->update(['revoked' => true, 'revoked_at' => now()]);

        $user = User::find($record->user_id);

        if (!$user) {
            return response()->json(['message' => 'Utilisateur introuvable.'], 401);
        }

        if (!$user->is_active) {
            return response()->json(['message' => 'Compte désactivé.'], 403);
        }

        $tokenData = $this->issueTokenPair($user, $request);

        $response = response()->json($tokenData);

        if ($request->header('X-Token-Delivery') === 'cookie') {
            $response = $this->applyTokenCookies($response, $tokenData);
        }

        return $response;
    }

    /**
     * POST /api/auth/logout
     */
    public function logout(Request $request): JsonResponse
    {
        $user = auth('api')->user();

        try {
            auth('api')->invalidate(true);
        } catch (JWTException $e) {
            // Already invalid — fine
        }

        if ($user) {
            JwtRefreshToken::where('user_id', $user->id)
                ->where('revoked', false)
                ->update(['revoked' => true, 'revoked_at' => now()]);

            try {
                AuditLog::record('api_logout', 'User', $user->id, [
                    'ip' => $request->ip(),
                ], $user);
            } catch (\Throwable $e) {
                // Non-blocking
            }
        }

        $response = response()->json(['message' => 'Déconnecté avec succès.']);

        if ($request->header('X-Token-Delivery') === 'cookie') {
            $response = $this->applyTokenCookies($response, [], true);
        }

        return $response;
    }

    /**
     * GET /api/auth/me
     */
    public function me(Request $request): JsonResponse
    {
        $user = auth('api')->user()->load('roles.permissions');

        return response()->json([
            'id'          => $user->id,
            'name'        => $user->name,
            'email'       => $user->email,
            'is_active'   => $user->is_active,
            'roles'       => $user->roles->pluck('slug'),
            'permissions' => $user->roles->flatMap(fn($r) => $r->permissions->pluck('name'))->unique()->values(),
        ]);
    }
}
