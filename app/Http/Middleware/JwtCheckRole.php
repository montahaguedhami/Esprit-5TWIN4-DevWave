<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class JwtCheckRole
{
    public function handle(Request $request, Closure $next, string ...$roles): mixed
    {
        $user = auth('api')->user();

        if (!$user) {
            return response()->json(['message' => 'Non authentifié.'], 401);
        }

        // Always re-check roles from DB (hasAnyRole uses DB via the HasRoles trait)
        if (!$user->hasAnyRole($roles)) {
            return response()->json(['message' => 'Accès refusé. Rôle insuffisant.'], 403);
        }

        return $next($request);
    }
}
