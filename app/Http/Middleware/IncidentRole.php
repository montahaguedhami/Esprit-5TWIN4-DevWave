<?php

namespace App\Http\Middleware;

use App\Models\Role;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IncidentRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (! $request->session()->has('user')) {
            return redirect()->route('auth.login');
        }

        $sessionRole  = Role::normalizeSlug($request->session()->get('user.role'));
        $expectedRole = Role::normalizeSlug($role);

        abort_unless($sessionRole === $expectedRole, 403);

        if ($expectedRole === Role::CITOYEN) {
            $user = User::where('email', $request->session()->get('user.email'))->first();
            abort_unless($user, 403, 'Le compte citoyen doit exister en base de donnees.');
            abort_unless($user->is_active, 403, 'Ce compte citoyen est désactivé.');
            $request->attributes->set('incident_user_id', $user->id);
        }

        return $next($request);
    }
}
