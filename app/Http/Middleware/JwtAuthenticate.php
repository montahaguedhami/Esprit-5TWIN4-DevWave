<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;

class JwtAuthenticate
{
    public function handle(Request $request, Closure $next): mixed
    {
        // 1. Try Bearer header first, then access_token cookie
        $token = null;
        $authHeader = $request->header('Authorization');

        if ($authHeader && str_starts_with($authHeader, 'Bearer ')) {
            $token = substr($authHeader, 7);
        } elseif ($request->hasCookie('access_token')) {
            $token = $request->cookie('access_token');
        }

        if (!$token) {
            return response()->json(['message' => 'Token manquant.', 'error' => 'unauthenticated'], 401);
        }

        try {
            JWTAuth::setToken($token);
            $user = JWTAuth::authenticate();
        } catch (TokenExpiredException $e) {
            return response()->json(['message' => 'Token expiré.', 'error' => 'token_expired'], 401);
        } catch (TokenInvalidException $e) {
            return response()->json(['message' => 'Token invalide.', 'error' => 'token_invalid'], 401);
        } catch (JWTException $e) {
            return response()->json(['message' => 'Non authentifié.', 'error' => 'unauthenticated'], 401);
        }

        if (!$user) {
            return response()->json(['message' => 'Non authentifié.', 'error' => 'unauthenticated'], 401);
        }

        // 2. Re-validate is_active from DB (never trust stale token state)
        $freshUser = \App\Models\User::find($user->id);

        if (!$freshUser || !$freshUser->is_active) {
            return response()->json(['message' => 'Compte désactivé.', 'error' => 'account_inactive'], 401);
        }

        auth('api')->setUser($freshUser);

        return $next($request);
    }
}
