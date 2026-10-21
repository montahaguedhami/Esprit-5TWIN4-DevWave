@extends('layouts.auth')

@section('auth-content')
<div class="w-full max-w-5xl grid lg:grid-cols-[1.05fr_0.95fr] gap-8 items-stretch">
    <!-- Left Section - Info (Desktop only) -->
    <section class="hidden lg:flex flex-col justify-between glass p-9 animate-fade-in-up">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-cyan-400/10 border border-cyan-400/25 text-cyan-200 text-xs font-semibold mb-8">
                <span class="w-2 h-2 rounded-full bg-teal-400 pulse-glow"></span>
                Plateforme nationale de surveillance
            </div>
            <h1 class="text-4xl xl:text-5xl font-display font-bold text-white leading-tight mb-6">
                Choisissez un nouveau mot de passe.
                <span class="block text-gradient">Le réseau vous en remercie.</span>
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

    <!-- Right Section - Reset Password Form -->
    <section class="glass-strong p-6 sm:p-8 animate-fade-in-up" style="animation-delay: 0.12s">
        <div class="flex items-center justify-between mb-7">
            <div>
                <p class="text-xs font-semibold tracking-wider text-cyan-300 uppercase">Accès sécurisé</p>
                <h2 class="text-2xl font-display font-bold text-white mt-1">Définir un nouveau mot de passe</h2>
            </div>
            <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-cyan-500/20 to-blue-600/20 border border-cyan-400/25 flex items-center justify-center">
                <svg class="w-5 h-5 text-cyan-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a4 4 0 11-8 0 4 4 0 018 0zm-4 4a4 4 0 11-8 0 4 4 0 018 0zM12 19v1a3 3 0 01-3 3H6a3 3 0 01-3-3V8a3 3 0 013-3h6a3 3 0 013 3v1z"></path>
                </svg>
            </div>
        </div>

        <!-- Error / Success Messages -->
        @if (session('error') || $errors->any())
            <div role="alert" class="rounded-xl border border-red-400/25 bg-red-500/10 p-3 mb-6 text-sm text-red-200">
                {{ session('error') ?? $errors->first() }}
            </div>
        @endif

        @if (session('success'))
            <div role="status" class="rounded-xl border border-teal-400/25 bg-teal-500/10 p-3 mb-6 text-sm text-teal-200">
                {{ session('success') }}
            </div>
        @endif

        <!-- Reset Password Form -->
        <form method="POST" action="{{ route('password.reset.post') }}" class="space-y-4">
            @csrf
            {{-- La demande vérifiée (code OTP validé) est identifiée par la session : ni jeton ni e-mail à saisir --}}

            <div>
                <label for="password" class="text-xs font-semibold text-cyan-100/70 mb-2 block">Mot de passe</label>
                <div class="relative">
                    <input type="password" name="password" id="password" required minlength="12" autocomplete="new-password" class="w-full bg-slate-950/35 border border-cyan-400/15 rounded-xl pl-11 pr-11 py-3 text-sm text-white placeholder:text-cyan-100/30 focus:outline-none focus:border-cyan-400/50 transition-colors" />
                    <svg class="w-4 h-4 text-cyan-100/30 absolute right-4 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                    <button type="button" onclick="togglePasswordVisibility()" class="text-cyan-100/35 hover:text-cyan-300 absolute right-4 top-1/2 -translate-y-1/2" aria-label="Afficher le mot de passe">
                        <svg class="w-5 h-5 eye-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                        <svg class="w-5 h-5 eye-off-icon hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 L21 21"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <div>
                <label for="password_confirmation" class="text-xs font-semibold text-cyan-100/70 mb-2 block">Confirmer le mot de passe</label>
                <div class="relative">
                    <input type="password" name="password_confirmation" id="password_confirmation" required autocomplete="new-password" class="w-full bg-slate-950/35 border border-cyan-400/15 rounded-xl pl-11 pr-4 py-3 text-sm text-white placeholder:text-cyan-100/30 focus:outline-none focus:border-cyan-400/50 transition-colors" />
                    <svg class="w-4 h-4 text-cyan-100/30 absolute left-4 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                </div>
            </div>

            <x-ripple-button type="submit" size="lg" class="w-full flex items-center justify-center gap-2">
                Réinitialiser le mot de passe
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                </svg>
            </x-ripple-button>
        </form>

        <!-- Back to login -->
        <div class="mt-6 text-center">
            <a href="{{ route('auth.login') }}" class="text-sm text-cyan-300/80 hover:text-cyan-200 transition-colors inline-flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Retour à la connexion
            </a>
        </div>
    </section>
</div>

@push('scripts')
<script>
    function togglePasswordVisibility() {
        const input = document.getElementById('password');
        const eyeIcon = document.querySelector('.eye-icon');
        const eyeOffIcon = document.querySelector('.eye-off-icon');

        if (input.type === 'password') {
            input.type = 'text';
            eyeIcon.classList.add('hidden');
            eyeOffIcon.classList.remove('hidden');
        } else {
            input.type = 'password';
            eyeIcon.classList.remove('hidden');
            eyeOffIcon.classList.add('hidden');
        }
    }
</script>
@endpush

@endsection
