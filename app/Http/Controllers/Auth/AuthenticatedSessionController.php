<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Role;
use App\Services\LoginRateLimiter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Breeze-style controller for the browser session lifecycle.
 * AquaSecure's role checks and throttling stay part of the login flow.
 */
class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(Request $request, LoginRateLimiter $limiter): RedirectResponse
    {
        $identifier = (string) $request->input('email');
        $ip = (string) $request->ip();

        if ($limiter->isBlocked($identifier, $ip)) {
            return back()
                ->withInput($request->only('email'))
                ->with('error', $limiter->message($identifier, $ip))
                ->with('retry_after', $limiter->secondsRemaining($identifier, $ip));
        }

        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::guard('web')->attempt($credentials, $request->boolean('remember'))) {
            $limiter->hit($identifier, $ip);

            return back()
                ->withInput($request->only('email'))
                ->with('error', 'Email ou mot de passe incorrect.');
        }

        $user = Auth::guard('web')->user();

        if ($user->is_active === false) {
            return $this->rejectLogin($request, 'Votre compte a été désactivé par un administrateur.');
        }

        $user->load('roles');
        $roleSlugs = $user->getRoleSlugs();

        if ($roleSlugs === []) {
            return $this->rejectLogin($request, 'Aucun rôle assigné à ce compte. Contactez l’administrateur.');
        }

        $request->session()->regenerate();
        $primaryRole = $user->primary_role;

        $request->session()->put('user', [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $primaryRole,
            'role_key' => $primaryRole,
            'roles' => $roleSlugs,
        ]);

        AuditLog::record('user_login', 'User', $user->id, [
            'role' => $primaryRole,
            'ip' => $ip,
        ], $user);

        $limiter->clear($identifier, $ip);

        return redirect()->intended(route(Role::homeRoute($primaryRole)));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('landing');
    }

    private function rejectLogin(Request $request, string $message): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('auth.login')
            ->withInput($request->only('email'))
            ->with('error', $message);
    }
}
