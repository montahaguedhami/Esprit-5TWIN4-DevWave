<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureManager
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->session()->get('user');

        if (! $user) {
            return redirect()->route('auth.login');
        }

        abort_unless(in_array($user['role'] ?? null, ['manager', 'admin'], true), 403);

        return $next($request);
    }
}
