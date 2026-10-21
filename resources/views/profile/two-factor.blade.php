@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto py-8 px-4">
    <div class="mb-6">
        <a href="{{ route('profile.show') }}" class="inline-flex items-center text-cyan-600 hover:text-cyan-700 mb-4">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Retour au profil
        </a>
        <h1 class="text-3xl font-bold text-slate-800 dark:text-slate-100">Authentification à deux facteurs (2FA)</h1>
        <p class="text-slate-600 dark:text-slate-400 mt-2">Renforcez la sécurité de votre compte avec l'authentification à deux facteurs.</p>
    </div>

    @if(session('success'))
    <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg">
        ✅ {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg">
        ❌ {{ session('error') }}
    </div>
    @endif

    @if(session('info'))
    <div class="mb-6 p-4 bg-blue-50 border border-blue-200 text-blue-800 rounded-lg">
        ℹ️ {{ session('info') }}
    </div>
    @endif

    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-md p-6 border border-slate-200 dark:border-slate-700">
        @if($enabled)
        {{-- 2FA activé --}}
        <div class="flex items-start gap-4 mb-6">
            <div class="flex-shrink-0 w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-slate-800 dark:text-slate-100">2FA activé</h3>
                <p class="text-sm text-slate-600 dark:text-slate-400">Votre compte est protégé par l'authentification à deux facteurs.</p>
            </div>
        </div>

        <div class="space-y-4">
            <a href="{{ route('profile.two-factor.recovery') }}" 
               class="inline-flex items-center px-4 py-2 bg-cyan-600 text-white rounded-lg hover:bg-cyan-700 transition">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                </svg>
                Voir les codes de récupération
            </a>

            <form method="POST" action="{{ route('profile.two-factor.regenerate') }}" class="inline-block">
                @csrf
                <button type="submit" 
                        class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
                        onclick="return confirm('Générer de nouveaux codes de récupération ? Les anciens ne fonctionneront plus.')">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Régénérer les codes
                </button>
            </form>

            <form method="POST" action="{{ route('profile.two-factor.disable') }}" class="mt-6 border-t pt-6">
                @csrf
                @method('DELETE')
                <h4 class="font-semibold text-slate-800 dark:text-slate-100 mb-3">Désactiver le 2FA</h4>
                <p class="text-sm text-slate-600 dark:text-slate-400 mb-4">Confirmez votre mot de passe pour désactiver l'authentification à deux facteurs.</p>
                
                <div class="mb-4">
                    <label for="password" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Mot de passe</label>
                    <input id="password" type="password" name="password" required
                           class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-cyan-500 dark:bg-slate-700 dark:text-slate-100">
                    @error('password')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" 
                        class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition"
                        onclick="return confirm('Êtes-vous sûr de vouloir désactiver le 2FA ?')">
                    Désactiver le 2FA
                </button>
            </form>
        </div>

        @else
        {{-- 2FA non activé --}}
        <div class="flex items-start gap-4 mb-6">
            <div class="flex-shrink-0 w-12 h-12 bg-amber-100 rounded-full flex items-center justify-center">
                <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-slate-800 dark:text-slate-100">2FA non activé</h3>
                <p class="text-sm text-slate-600 dark:text-slate-400">Activez l'authentification à deux facteurs pour sécuriser votre compte.</p>
            </div>
        </div>

        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4 mb-6">
            <h4 class="font-semibold text-blue-900 dark:text-blue-100 mb-2">Comment ça fonctionne ?</h4>
            <ol class="text-sm text-blue-800 dark:text-blue-200 space-y-1 list-decimal list-inside">
                <li>Installez une application d'authentification (Google Authenticator, Authy, etc.)</li>
                <li>Scannez le QR code qui sera généré</li>
                <li>Entrez le code à 6 chiffres pour confirmer l'activation</li>
                <li>Conservez vos codes de récupération en lieu sûr</li>
            </ol>
        </div>

        <form method="POST" action="{{ route('profile.two-factor.enable') }}">
            @csrf
            <button type="submit" 
                    class="inline-flex items-center px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-semibold">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
                Activer le 2FA
            </button>
        </form>
        @endif
    </div>
</div>
@endsection
