<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    /**
     * Afficher le profil de l'utilisateur connecté.
     */
    public function show(Request $request)
    {
        return view('profile.show', [
            'user' => $request->user() ?? Auth::user(),
        ]);
    }

    /**
     * Afficher le formulaire d'édition du profil.
     */
    public function edit(Request $request)
    {
        return view('profile.edit', [
            'user' => $request->user() ?? Auth::user(),
        ]);
    }

    /**
     * Mettre à jour les informations personnelles (nom + email).
     */
    public function update(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user() ?? Auth::user();

        $validated = $request->validate([
            'name'  => ['required', 'string', 'min:2', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
        ]);

        $emailChanged = $validated['email'] !== $user->email;

        $user->fill([
            'name'  => $validated['name'],
            'email' => $validated['email'],
        ]);

        // Si l'email change, forcer une nouvelle vérification
        if ($emailChanged) {
            $user->email_verified_at = null;
        }

        $user->save();

        AuditLog::record('profile_updated', 'User', $user->id, [
            'ip'            => $request->ip(),
            'email_changed' => $emailChanged,
        ], $user);

        if ($emailChanged) {
            $user->sendEmailVerificationNotification();
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('verification.notice')
                ->with('success', 'Email mis à jour. Un lien de vérification a été envoyé à votre nouvelle adresse.');
        }

        return redirect()->route('profile.show')
            ->with('success', 'Informations mises à jour avec succès.');
    }

    /**
     * Mettre à jour le mot de passe.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user() ?? Auth::user();

        $validated = $request->validate([
            'current_password'      => ['required', 'string'],
            'password'              => [
                'required', 'confirmed',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
            ],
        ], [
            'password.min'     => 'Le mot de passe doit contenir au moins 8 caractères.',
            'password.letters' => 'Le mot de passe doit contenir au moins une lettre.',
            'password.mixed'   => 'Le mot de passe doit contenir des majuscules et minuscules.',
            'password.numbers' => 'Le mot de passe doit contenir au moins un chiffre.',
            'password.symbols' => 'Le mot de passe doit contenir au moins un caractère spécial.',
        ]);

        if (! Hash::check($validated['current_password'], $user->password)) {
            return back()->withErrors([
                'current_password' => 'Le mot de passe actuel est incorrect.',
            ])->with('tab', 'password');
        }

        $user->forceFill([
            'password' => Hash::make($validated['password']),
        ])->save();

        AuditLog::record('password_changed', 'User', $user->id, [
            'ip' => $request->ip(),
        ], $user);

        // Invalider toutes les autres sessions
        Auth::logoutOtherDevices($validated['password']);

        return redirect()->route('profile.show')
            ->with('success', 'Mot de passe mis à jour avec succès.');
    }

    /**
     * Uploader une photo de profil (avatar).
     */
    public function updateAvatar(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user() ?? Auth::user();

        $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ], [
            'avatar.image'    => 'Le fichier doit être une image.',
            'avatar.mimes'    => 'Formats acceptés : jpeg, png, jpg, webp.',
            'avatar.max'      => 'L\'image ne doit pas dépasser 2 Mo.',
        ]);

        // Supprimer l'ancien avatar local si existe
        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }

        $path = $request->file('avatar')->store('avatars', 'public');

        $user->forceFill(['avatar' => $path])->save();

        AuditLog::record('avatar_updated', 'User', $user->id, [
            'ip' => $request->ip(),
        ], $user);

        return redirect()->route('profile.show')
            ->with('success', 'Photo de profil mise à jour.');
    }

    /**
     * Supprimer la photo de profil.
     */
    public function deleteAvatar(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user() ?? Auth::user();

        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }

        $user->forceFill(['avatar' => null])->save();

        return redirect()->route('profile.show')
            ->with('success', 'Photo de profil supprimée.');
    }

    /**
     * Supprimer le compte de l'utilisateur connecté.
     */
    public function destroy(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user() ?? Auth::user();

        $request->validate([
            'password' => ['required', 'string'],
        ], [
            'password.required' => 'Veuillez confirmer votre mot de passe pour supprimer le compte.',
        ]);

        if (! Hash::check($request->password, $user->password)) {
            return back()->withErrors([
                'password' => 'Mot de passe incorrect.',
            ])->with('tab', 'danger');
        }

        // Protection : le dernier administrateur ne peut pas supprimer son compte
        if ($user->isLastAdministrator()) {
            return back()->with('error', 'Impossible de supprimer le dernier compte administrateur du système.');
        }

        // Supprimer l'avatar
        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }

        AuditLog::record('account_deleted', 'User', $user->id, [
            'ip'    => $request->ip(),
            'email' => $user->email,
        ], $user);

        Auth::logout();
        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('landing')
            ->with('success', 'Votre compte a été supprimé avec succès.');
    }
}
