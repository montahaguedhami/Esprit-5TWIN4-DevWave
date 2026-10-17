<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

/**
 * Google OAuth via Laravel Socialite.
 *
 * Security rules enforced here:
 *  - A social account is ALWAYS created with the citizen role. A role is
 *    never taken from the browser nor from Google.
 *  - A disabled account is never reactivated automatically.
 *  - The RBAC / policies of the application are never bypassed.
 *  - A local account sharing the same address is never auto-linked while
 *    browsing anonymously: the user must authenticate first and link
 *    explicitly from a logged-in session.
 */
class GoogleAuthController extends Controller
{
    /**
     * Are the Google OAuth credentials available?
     */
    public static function configured(): bool
    {
        return filled(config('services.google.client_id'))
            && filled(config('services.google.client_secret'));
    }

    /* ────────────────────────────────────────────────────────────────────────
     |  Redirect to Google
     |_______________________________________________________________________*/

    public function redirect(Request $request): RedirectResponse
    {
        if (! self::configured()) {
            return redirect()->route('auth.login')
                ->with('error', $this->notConfiguredMessage());
        }

        // remember=1 keeps the "link provider" intent across the round trip.
        return Socialite::driver('google')
            ->with(['prompt' => 'select_account'])
            ->redirect();
    }

    /* ────────────────────────────────────────────────────────────────────────
     |  Callback
     |_______________________________________________________________________*/

    public function callback(Request $request): RedirectResponse
    {
        if (! self::configured()) {
            return redirect()->route('auth.login')
                ->with('error', $this->notConfiguredMessage());
        }

        // The user denied the consent screen, or Google returned an error.
        if ($request->filled('error')) {
            return redirect()->route('auth.login')
                ->with('error', 'La connexion avec Google a été annulée.');
        }

        try {
            $socialUser = Socialite::driver('google')->user();
        } catch (\Laravel\Socialite\Two\InvalidStateException) {
            return redirect()->route('auth.login')
                ->with('error', 'La session de connexion Google a expiré. Réessayez.');
        } catch (\Throwable $e) {
            report($e);

            return redirect()->route('auth.login')
                ->with('error', 'Impossible de communiquer avec Google pour le moment. Réessayez.');
        }

        $providerId   = (string) $socialUser->getId();
        $providerEmail = $socialUser->getEmail();

        if (blank($providerId)) {
            return redirect()->route('auth.login')
                ->with('error', 'Google n\'a pas retourné d\'identifiant valide.');
        }

        // An authenticated user asking to link a Google account to their profile.
        if ($request->session()->has('user.id')) {
            return $this->linkToCurrentAccount($request, $providerId, $providerEmail);
        }

        // Existing social account for this provider id.
        $user = User::where('provider', 'google')
            ->where('provider_id', $providerId)
            ->first();

        if ($user) {
            return $this->loginSocialUser($request, $user);
        }

        // No social account yet. Does the email already belong to a local account?
        $localAccount = filled($providerEmail)
            ? User::where('email', $providerEmail)->first()
            : null;

        if ($localAccount) {
            // Refuse to auto-link while browsing anonymously: the user must
            // sign in with their own credentials first, then link explicitly.
            return redirect()->route('auth.login')
                ->with('error', 'Un compte existe déjà avec cette adresse email. Connectez-vous avec votre mot de passe, puis liez Google depuis votre profil.');
        }

        // Brand new account — always a citizen.
        return $this->createCitizen($request, $socialUser, $providerId, $providerEmail);
    }

    /* ────────────────────────────────────────────────────────────────────────
     |  Outcomes
     |_______________________________________________________________________*/

    private function loginSocialUser(Request $request, User $user): RedirectResponse
    {
        if (! $user->is_active) {
            return redirect()->route('auth.login')
                ->with('error', 'Ce compte a été désactivé par un administrateur.');
        }

        if ($user->getRoleSlugs() === []) {
            return redirect()->route('auth.login')
                ->with('error', 'Aucun rôle assigné à ce compte. Contactez l\'administrateur.');
        }

        $this->establishSession($request, $user);

        AuditLog::record('social_login', 'User', $user->id, [
            'provider' => 'google',
            'ip'       => (string) $request->ip(),
        ], $user);

        $primaryRole = $user->primary_role;

        return redirect()->intended(route(Role::homeRoute($primaryRole)));
    }

    private function createCitizen(Request $request, object $socialUser, string $providerId, ?string $email): RedirectResponse
    {
        $citoyenRole = Role::where('slug', Role::CITOYEN)->first();

        if (! $citoyenRole) {
            return redirect()->route('auth.login')
                ->with('error', 'Le système n\'est pas encore configuré. Contactez l\'administrateur.');
        }

        // Google may return an account without a public email address.
        if (blank($email)) {
            return redirect()->route('auth.login')
                ->with('error', 'Votre compte Google ne fournit pas d\'adresse email publique. Autorisez l\'accès à l\'adresse email.');
        }

        $user = User::create([
            'name'         => $socialUser->getName() ?: Str::before($email, '@'),
            'email'        => $email,
            'password'     => Hash::make(Str::random(32)), // random, unusable
            'is_active'    => true,
            'provider'     => 'google',
            'provider_id'  => $providerId,
            'avatar'       => $socialUser->getAvatar(),
        ]);

        // Social accounts are ALWAYS plain citizens.
        $user->assignRole($citoyenRole);

        $this->establishSession($request, $user);

        AuditLog::record('social_register', 'User', $user->id, [
            'provider' => 'google',
            'ip'       => (string) $request->ip(),
        ], $user);

        // Envoyer l'email de bienvenu
        try {
            $user->notify(new \App\Notifications\WelcomeNotification());
        } catch (\Throwable $e) {
            report($e);
        }

        return redirect()->intended(route(Role::homeRoute($user->primary_role)));
    }

    /**
     * Explicit linking performed from an authenticated session.
     */
    private function linkToCurrentAccount(Request $request, string $providerId, ?string $providerEmail): RedirectResponse
    {
        $current = User::find($request->session()->get('user.id'));

        if (! $current) {
            return redirect()->route('auth.login')->with('error', 'Session expirée. Reconnectez-vous.');
        }

        if (! $current->is_active) {
            return redirect()->route('auth.login')->with('error', 'Ce compte a été désactivé par un administrateur.');
        }

        // Is this Google identity already attached to another account?
        $taken = User::where('provider', 'google')
            ->where('provider_id', $providerId)
            ->where('id', '!=', $current->id)
            ->exists();

        if ($taken) {
            return redirect()->route('profile.show')
                ->with('error', 'Ce compte Google est déjà lié à un autre utilisateur AquaSecure.');
        }

        // When Google exposes an email that belongs to someone else, refuse.
        if (filled($providerEmail)) {
            $owner = User::where('email', $providerEmail)
                ->where('id', '!=', $current->id)
                ->first();

            if ($owner) {
                return redirect()->route('profile.show')
                    ->with('error', 'Cette adresse Google est déjà utilisée par un autre compte.');
            }
        }

        $current->forceFill([
            'provider'    => 'google',
            'provider_id' => $providerId,
        ])->save();

        AuditLog::record('social_link', 'User', $current->id, [
            'provider' => 'google',
            'ip'       => (string) $request->ip(),
        ], $current);

        return redirect()->route('profile.show')
            ->with('success', 'Votre compte Google a été lié avec succès.');
    }

    /* ────────────────────────────────────────────────────────────────────────
     |  Helpers
     |_______________________________________________________________________*/

    /**
     * Mirror the exact session shape built by the classic web login so the
     * existing role middleware keeps working unchanged.
     */
    private function establishSession(Request $request, User $user): void
    {
        $user->load('roles');
        $roleSlugs = $user->getRoleSlugs();

        Auth::login($user, true);
        $request->session()->regenerate();

        $primaryRole = $user->primary_role;

        session([
            'user' => [
                'id'       => $user->id,
                'name'     => $user->name,
                'email'    => $user->email,
                'role'     => $primaryRole,
                'role_key' => $primaryRole,
                'roles'    => $roleSlugs,
            ],
        ]);
    }

    private function notConfiguredMessage(): string
    {
        if (app()->environment('production')) {
            return 'La connexion Google est momentanément indisponible.';
        }

        return 'Connexion Google non configurée : renseignez GOOGLE_CLIENT_ID et GOOGLE_CLIENT_SECRET dans le fichier .env puis redémarrez le serveur.';
    }
}
