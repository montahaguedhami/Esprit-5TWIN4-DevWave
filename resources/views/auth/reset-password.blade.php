@extends('layouts.auth')

@section('auth-content')
<style>
    .aqua-auth-page { min-height: 100vh; width: 100%; box-sizing: border-box; align-content: center; background: #f3f7fb; color: #102a43; padding: 5rem 1.5rem 1.5rem; }
    .aqua-auth-page > section { border: 1px solid #dce7ef; border-radius: 1.5rem; box-shadow: 0 18px 48px rgba(15,42,67,.08); }
    .aqua-auth-page > section:first-child { background: linear-gradient(145deg,#123b57,#0d5b76); border-color: #1e7892; }
    .aqua-auth-page > section:first-child .text-white { color: #fff !important; }
    .aqua-auth-page > section:first-child [class*="text-cyan-100/"] { color: #c5e5ef !important; }
    .aqua-auth-page > section:last-child { background: #fff; }
    .aqua-auth-page [class~="text-white"] { color: #102a43 !important; }
    .aqua-auth-page [class*="text-cyan-100/"] { color: #647b8e !important; }
    .aqua-auth-page [class*="bg-slate-950"] { background-color: #f5f8fb !important; }
    .aqua-auth-page [class*="border-cyan-400/"] { border-color: #d5e2eb !important; }
    .aqua-auth-page input:not([type="checkbox"]), .aqua-auth-page select { color: #102a43; background: #f8fafc; border-color: #cbd9e5; }
    .aqua-auth-page input::placeholder { color: #94a3b8; }
    .aqua-auth-page [class*="bg-gradient-to-r"],
    .aqua-auth-page [class*="bg-gradient-to-r"] [class~="text-white"],
    .aqua-auth-page .btn-primary { color: #fff !important; }
    @media (max-width: 640px) { .aqua-auth-page { padding: 1rem; } }

    .pw-reqs { display: grid; gap: .35rem; }
    .pw-req { display: flex; align-items: center; gap: .5rem; font-size: .74rem; color: #7c8fa1; transition: color .2s ease; }
    .pw-req .dot { width: .4rem; height: .4rem; border-radius: 50%; background: #cbd5e1; transition: background .2s ease; }
    .pw-req.is-ok { color: #15803d; }
    .pw-req.is-ok .dot { background: #22c55e; }
</style>
<div class="aqua-auth-page w-full max-w-5xl grid lg:grid-cols-[1.05fr_0.95fr] gap-8 items-stretch">
    <!-- Left Section - Info (Desktop only) -->
    <section class="hidden lg:flex flex-col justify-between glass p-9 animate-fade-in-up">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-cyan-400/10 border border-cyan-400/25 text-cyan-200 text-xs font-semibold mb-8">
                <span class="w-2 h-2 rounded-full bg-teal-400 pulse-glow"></span>
                Plateforme nationale de surveillance
            </div>
            <h1 class="text-4xl xl:text-5xl font-display font-bold text-white leading-tight mb-6">
                Chaque goutte compte.
                <span class="block text-gradient">Chaque intervention aussi.</span>
            </h1>
            <p class="text-cyan-100/65 leading-relaxed max-w-lg">
                AquaSecure relie les citoyens, techniciens et gestionnaires autour d'un réseau d'eau potable plus sûr, plus transparent et plus résilient.
            </p>
        </div>

        <div class="grid grid-cols-3 gap-3 mt-12">
            @foreach([
                ['value' => '14', 'label' => 'zones suivies'],
                ['value' => '24/7', 'label' => 'surveillance'],
                ['value' => '100%', 'label' => 'transparent'],
            ] as $stat)
            <div class="glass-strong p-4 rounded-2xl">
                <div class="text-xl font-display font-bold text-white">{{ $stat['value'] }}</div>
                <div class="text-[11px] text-cyan-100/50 mt-1">{{ $stat['label'] }}</div>
            </div>
            @endforeach
        </div>
    </section>

    <!-- Right Section - New Password Form -->
    <section class="glass-strong p-6 sm:p-8 animate-fade-in-up" style="animation-delay: 0.12s">
        <div class="flex items-center justify-between mb-7">
            <div>
                <p class="text-xs font-semibold tracking-wider text-cyan-300 uppercase">Étape 3 sur 3</p>
                <h2 class="text-2xl font-display font-bold text-white mt-1">Nouveau mot de passe</h2>
            </div>
            <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-cyan-500/20 to-blue-600/20 border border-cyan-400/25 flex items-center justify-center">
                <svg class="w-5 h-5 text-cyan-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                </svg>
            </div>
        </div>

        <!-- Progress steps -->
        <div class="flex items-center gap-2 mb-6">
            <div class="h-1.5 flex-1 rounded-full bg-emerald-400/70"></div>
            <div class="h-1.5 flex-1 rounded-full bg-emerald-400/70"></div>
            <div class="h-1.5 flex-1 rounded-full bg-cyan-400/70"></div>
        </div>

        <form method="POST" action="{{ route('password.reset.post') }}" class="space-y-4" id="resetForm">
            @csrf

            @if ($errors->any())
                <div role="alert" class="rounded-xl border border-red-400/25 bg-red-500/10 p-3 text-sm text-red-200">
                    {{ $errors->first() }}
                </div>
            @endif

            <div>
                <label class="text-xs font-semibold text-cyan-100/70 mb-2 block">Nouveau mot de passe</label>
                <div class="relative">
                    <input
                        type="password"
                        name="password"
                        id="password"
                        required
                        minlength="12"
                        autocomplete="new-password"
                        placeholder="Votre nouveau mot de passe"
                        class="w-full bg-slate-950/35 border border-cyan-400/15 rounded-xl pl-11 pr-11 py-3 text-sm text-white placeholder:text-cyan-100/30 focus:outline-none focus:border-cyan-400/50 transition-colors"
                    />
                    <svg class="w-4 h-4 text-cyan-100/30 absolute left-4 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <rect x="4" y="10" width="16" height="11" rx="2" stroke="currentColor" stroke-width="2"/>
                        <path d="M8 10V7a4 4 0 118 0v3" stroke="currentColor" stroke-width="2"/>
                    </svg>
                    <button type="button" onclick="togglePw('password')" class="absolute right-3 top-1/2 -translate-y-1/2 text-cyan-100/40 hover:text-cyan-200" aria-label="Afficher">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M2.5 12s3.4-6 9.5-6 9.5 6 9.5 6-3.4 6-9.5 6-9.5-6-9.5-6z" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="12" r="2.5" stroke="currentColor" stroke-width="2"/></svg>
                    </button>
                </div>
            </div>

            <!-- Requirements checklist -->
            <div class="glass p-4 rounded-xl pw-reqs">
                <div class="pw-req" data-rule="len"><span class="dot"></span> 12 caractères minimum</div>
                <div class="pw-req" data-rule="lower"><span class="dot"></span> Une lettre minuscule</div>
                <div class="pw-req" data-rule="upper"><span class="dot"></span> Une lettre majuscule</div>
                <div class="pw-req" data-rule="digit"><span class="dot"></span> Un chiffre</div>
                <div class="pw-req" data-rule="symbol"><span class="dot"></span> Un caractère spécial</div>
            </div>

            <div>
                <label class="text-xs font-semibold text-cyan-100/70 mb-2 block">Confirmer le mot de passe</label>
                <div class="relative">
                    <input
                        type="password"
                        name="password_confirmation"
                        id="password_confirmation"
                        required
                        minlength="12"
                        autocomplete="new-password"
                        placeholder="Retapez le mot de passe"
                        class="w-full bg-slate-950/35 border border-cyan-400/15 rounded-xl pl-11 pr-11 py-3 text-sm text-white placeholder:text-cyan-100/30 focus:outline-none focus:border-cyan-400/50 transition-colors"
                    />
                    <svg class="w-4 h-4 text-cyan-100/30 absolute left-4 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <button type="button" onclick="togglePw('password_confirmation')" class="absolute right-3 top-1/2 -translate-y-1/2 text-cyan-100/40 hover:text-cyan-200" aria-label="Afficher">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M2.5 12s3.4-6 9.5-6 9.5 6 9.5 6-3.4 6-9.5 6-9.5-6-9.5-6z" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="12" r="2.5" stroke="currentColor" stroke-width="2"/></svg>
                    </button>
                </div>
            </div>

            <button type="submit" id="reset-submit" class="w-full flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-cyan-500 to-blue-600 text-white font-semibold py-3.5 shadow-lg shadow-cyan-500/20 transition-opacity disabled:opacity-60">
                Enregistrer le nouveau mot de passe
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </button>
        </form>

        <!-- Back to login -->
        <div class="mt-6 text-center">
            <a href="{{ route('auth.login') }}" class="text-sm text-cyan-300/80 hover:text-cyan-200 transition-colors inline-flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Annuler et retourner à la connexion
            </a>
        </div>
    </section>
</div>

@push('scripts')
<script>
    function togglePw(id) {
        const el = document.getElementById(id);
        el.type = el.type === 'password' ? 'text' : 'password';
    }

    (function () {
        const pw = document.getElementById('password');
        const rules = {
            len:    v => v.length >= 12,
            lower:  v => /[a-z]/.test(v),
            upper:  v => /[A-Z]/.test(v),
            digit:  v => /[0-9]/.test(v),
            symbol: v => /[^A-Za-z0-9]/.test(v),
        };

        pw.addEventListener('input', function () {
            const v = pw.value;
            Object.keys(rules).forEach(function (key) {
                const el = document.querySelector('[data-rule="' + key + '"]');
                el.classList.toggle('is-ok', rules[key](v));
            });
        });

        document.getElementById('resetForm').addEventListener('submit', function () {
            const btn = document.getElementById('reset-submit');
            btn.disabled = true;
            btn.textContent = 'Enregistrement…';
        });
    })();
</script>
@endpush
@endsection
