<?php

namespace App\Http\Middleware;

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

        abort_unless($request->session()->get('user.role') === $role, 403);

        if ($role === 'citizen') {
            $user = User::where('email', $request->session()->get('user.email'))->first();
            abort_unless($user, 403, 'Le compte citoyen doit exister en base de donnees.');
            $request->attributes->set('incident_user_id', $user->id);
        }

        return $next($request);
    }
}