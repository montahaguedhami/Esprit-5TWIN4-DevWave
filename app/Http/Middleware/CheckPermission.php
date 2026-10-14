<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $this->resolveUser($request);

        if (! $user) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['message' => 'Non authentifié.'], 401);
            }

            return redirect()->route('auth.login');
        }

        if (! $user->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $message = 'Votre compte est désactivé.';

            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['message' => $message], 403);
            }

            return redirect()->route('auth.login')->with('error', $message);
        }

        if (! $user->hasPermission($permission)) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => 'Action non autorisée.',
                    'required_permission' => $permission,
                ], 403);
            }

            abort(403, "Action non autorisée : permission '{$permission}' manquante.");
        }

        return $next($request);
    }

    private function resolveUser(Request $request): ?User
    {
        if (Auth::check()) {
            return Auth::user();
        }

        $sessionUser = $request->session()->get('user');
        if ($sessionUser && ! empty($sessionUser['email'])) {
            $user = User::where('email', $sessionUser['email'])->first();
            if ($user) {
                Auth::login($user);

                return $user;
            }
        }

        return null;
    }
}
