<?php

namespace App\Http\Middleware;

use App\Models\Role;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class IncidentRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $expectedRole = Role::normalizeSlug($role);

        // 1. Auth::user() — primary source (real authenticated users)
        $authUser = Auth::user();

        if ($authUser) {
            abort_unless($authUser->is_active, 403, 'Ce compte est désactivé.');
            abort_unless($authUser->hasAnyRole([$expectedRole]), 403, 'Accès refusé : rôle insuffisant.');

            if ($expectedRole === Role::CITOYEN) {
                $request->attributes->set('incident_user_id', $authUser->id);
            }

            return $next($request);
        }

        // 2. Session fallback (legacy / tests)
        if (! $request->session()->has('user')) {
            return redirect()->route('auth.login');
        }

        $sessionRole = Role::normalizeSlug($request->session()->get('user.role'));
        abort_unless($sessionRole === $expectedRole, 403);

        if ($expectedRole === Role::CITOYEN) {
            $user = User::where('email', $request->session()->get('user.email'))->first();
            abort_unless($user, 403, 'Le compte citoyen doit exister en base de données.');
            abort_unless($user->is_active, 403, 'Ce compte citoyen est désactivé.');
            $request->attributes->set('incident_user_id', $user->id);
        }

        return $next($request);
    }
}
