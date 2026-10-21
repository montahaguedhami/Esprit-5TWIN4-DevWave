@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto py-8 px-4">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-slate-800 dark:text-slate-100">Configurer l'authentification à deux facteurs</h1>
        <p class="text-slate-600 dark:text-slate-400 mt-2">Scannez le QR code avec votre application d'authentification.</p>
    </div>

    @if($errors->any())
    <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg">
        @foreach($errors->all() as $error)
        <p>❌ {{ $error }}</p>
        @endforeach
    </div>
    @endif

    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-md p-6 border border-slate-200 dark:border-slate-700">
        
        {{-- Étape 1 : QR Code --}}
        <div class="mb-8">
            <h3 class="text-lg font-semibold text-slate-800 dark:text-slate-100 mb-4">Étape 1 : Scannez ce QR code</h3>
            <div class="flex justify-center p-6 bg-slate-50 dark:bg-slate-900 rounded-lg">
                {!! $qrCodeSvg !!}
            </div>
            <p class="text-sm text-slate-600 dark:text-slate-400 mt-4 text-center">
                Ou entrez cette clé manuellement : <code class="bg-slate-200 dark:bg-slate-700 px-2 py-1 rounded font-mono text-xs">{{ $secret }}</code>
            </p>
        </div>

        {{-- Étape 2 : Codes de récupération --}}
        <div class="mb-8 p-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg">
            <h3 class="text-lg font-semibold text-amber-900 dark:text-amber-100 mb-3">⚠️ Codes de récupération</h3>
            <p class="text-sm text-amber-800 dark:text-amber-200 mb-4">
                Conservez ces codes en lieu sûr. Ils vous permettront de vous connecter si vous perdez accès à votre application d'authentification.
            </p>
            <div class="grid grid-cols-2 gap-2 bg-white dark:bg-slate-800 p-4 rounded font-mono text-sm">
                @foreach($recoveryCodes as $code)
                <div style="color:var(--text-secondary,#475569);">{{ $code }}</div>
                @endforeach
            </div>
            <p class="text-xs text-amber-700 dark:text-amber-300 mt-3">
                💡 Astuce : Imprimez ou enregistrez ces codes dans un gestionnaire de mots de passe sécurisé.
            </p>
        </div>

        {{-- Étape 3 : Confirmation --}}
        <form method="POST" action="{{ route('profile.two-factor.confirm') }}">
            @csrf
            <h3 class="text-lg font-semibold text-slate-800 dark:text-slate-100 mb-4">Étape 3 : Confirmez avec un code</h3>
            <p class="text-sm text-slate-600 dark:text-slate-400 mb-4">
                Entrez le code à 6 chiffres affiché dans votre application d'authentification.
            </p>

            <div class="mb-6">
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Code à 6 chiffres</label>
                <input type="text" 
                       name="code" 
                       maxlength="6" 
                       pattern="[0-9]{6}"
                       placeholder="123456"
                       required
                       autofocus
                       class="w-full px-4 py-3 text-center text-2xl font-mono border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-cyan-500 dark:bg-slate-700 dark:text-slate-100">
                @error('code')
                <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex gap-3">
                <button type="submit" 
                        class="flex-1 px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-semibold">
                    ✓ Confirmer et activer
                </button>
                <a href="{{ route('profile.two-factor') }}" 
                   class="px-6 py-3 bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-lg hover:bg-slate-300 dark:hover:bg-slate-600 transition font-semibold">
                    Annuler
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
