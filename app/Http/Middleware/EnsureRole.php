<?php

namespace App\Http\Middleware;

use App\Models\Role;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->session()->get('user');

        if (! $user) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Non authentifié.'], 401);
            }
            return redirect()->route('auth.login');
        }

        $userRole = Role::normalizeSlug($user['role'] ?? null);

        // Éclater les rôles possibles (séparés par virgules ou paramètres multiples)
        $allowedRoles = [];
        foreach ($roles as $role) {
            foreach (explode(',', $role) as $r) {
                $trimmed = trim($r);
                if ($trimmed !== '') {
                    $allowedRoles[] = Role::normalizeSlug($trimmed);
                }
            }
        }

        if (! empty($allowedRoles) && ! in_array($userRole, $allowedRoles, true)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Accès interdit pour ce rôle.'], 403);
            }
            abort(403, 'Accès réservé au rôle ' . implode(', ', $allowedRoles) . '.');
        }

        return $next($request);
    }
}
