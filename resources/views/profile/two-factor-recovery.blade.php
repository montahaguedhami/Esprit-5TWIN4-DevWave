@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto py-8 px-4">
    <div class="mb-6">
        <a href="{{ route('profile.two-factor') }}" class="inline-flex items-center text-cyan-600 hover:text-cyan-700 mb-4">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Retour au 2FA
        </a>
        <h1 class="text-3xl font-bold text-slate-800 dark:text-slate-100">Codes de récupération 2FA</h1>
        <p class="text-slate-600 dark:text-slate-400 mt-2">Utilisez ces codes si vous perdez accès à votre application d'authentification.</p>
    </div>

    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-md p-6 border border-slate-200 dark:border-slate-700">
        
        <div class="mb-6 p-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg">
            <h3 class="font-semibold text-amber-900 dark:text-amber-100 mb-2">⚠️ Important</h3>
            <ul class="text-sm text-amber-800 dark:text-amber-200 space-y-1 list-disc list-inside">
                <li>Chaque code ne peut être utilisé qu'<strong>une seule fois</strong></li>
                <li>Conservez-les dans un endroit sûr (gestionnaire de mots de passe, coffre-fort, etc.)</li>
                <li>Ne les partagez jamais avec personne</li>
                <li>Si vous les perdez, vous pouvez en régénérer de nouveaux</li>
            </ul>
        </div>

        <div class="bg-slate-50 dark:bg-slate-900 p-6 rounded-lg mb-6">
            <div class="grid grid-cols-2 gap-3 font-mono text-lg text-center">
                @foreach($recoveryCodes as $code)
                <div class="p-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-800 dark:text-slate-200">
                    {{ $code }}
                </div>
                @endforeach
            </div>
        </div>

        <div class="flex gap-3">
            <button onclick="printCodes()" 
                    class="flex-1 px-4 py-2 bg-cyan-600 text-white rounded-lg hover:bg-cyan-700 transition">
                <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Imprimer
            </button>
            <button onclick="copyCodes()" 
                    class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                </svg>
                Copier
            </button>
        </div>
    </div>
</div>

<script>
function printCodes() {
    window.print();
}

function copyCodes() {
    const codes = @json($recoveryCodes);
    const text = codes.join('\n');
    navigator.clipboard.writeText(text).then(() => {
        alert('✓ Codes copiés dans le presse-papiers');
    });
}
</script>
@endsection
