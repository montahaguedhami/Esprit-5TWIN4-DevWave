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

    <!-- Right Section - OTP Form -->
    <section class="glass-strong p-6 sm:p-8 animate-fade-in-up" style="animation-delay: 0.12s">
        <div class="flex items-center justify-between mb-7">
            <div>
                <p class="text-xs font-semibold tracking-wider text-cyan-300 uppercase">Étape 2 sur 3</p>
                <h2 class="text-2xl font-display font-bold text-white mt-1">Vérifier le code</h2>
            </div>
            <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-cyan-500/20 to-blue-600/20 border border-cyan-400/25 flex items-center justify-center">
                <svg class="w-5 h-5 text-cyan-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>

        @if (session('status'))
            <div role="status" class="glass p-4 rounded-xl mb-6 flex gap-3">
                <svg class="w-5 h-5 text-emerald-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="text-sm text-cyan-100/70">{{ session('status') }}</p>
            </div>
        @endif

        <!-- Progress steps -->
        <div class="flex items-center gap-2 mb-6">
            <div class="h-1.5 flex-1 rounded-full bg-emerald-400/70"></div>
            <div class="h-1.5 flex-1 rounded-full bg-cyan-400/70"></div>
            <div class="h-1.5 flex-1 rounded-full bg-slate-400/25"></div>
        </div>

        <!-- Info Box -->
        <div class="glass p-4 rounded-xl mb-6 flex gap-3">
            <svg class="w-5 h-5 text-cyan-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
            </svg>
            <p class="text-sm text-cyan-100/70">
                Nous avons envoyé un code à six chiffres à votre adresse email. Saisissez-le pour continuer.
            </p>
        </div>

        <form method="POST" action="{{ route('password.verify.otp.post') }}" class="space-y-4">
            @csrf

            @if ($errors->any())
                <div role="alert" class="rounded-xl border border-red-400/25 bg-red-500/10 p-3 text-sm text-red-200">
                    {{ $errors->first() }}
                </div>
            @endif

            <div>
                <label class="text-xs font-semibold text-cyan-100/70 mb-2 block">Code de vérification</label>
                <input
                    type="text"
                    name="otp"
                    id="otp"
                    required
                    inputmode="numeric"
                    pattern="[0-9]{6}"
                    maxlength="6"
                    minlength="6"
                    autocomplete="one-time-code"
                    placeholder="000000"
                    autofocus
                    class="w-full text-center tracking-[0.6em] bg-slate-950/35 border border-cyan-400/15 rounded-xl px-4 py-4 text-xl font-bold text-white placeholder:text-cyan-100/25 focus:outline-none focus:border-cyan-400/50 transition-colors"
                />
            </div>

            <!-- Countdown (client side, indicative only) -->
            <p class="text-center text-xs text-cyan-100/50" id="otp-countdown">
                Le code expire dans <b id="otp-seconds">60</b> secondes.
            </p>

            <button type="submit" id="otp-submit" class="w-full flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-cyan-500 to-blue-600 text-white font-semibold py-3.5 shadow-lg shadow-cyan-500/20 transition-opacity disabled:opacity-60">
                Vérifier le code
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </button>
        </form>

        <!-- Resend -->
        <form method="POST" action="{{ route('password.resend.otp') }}" class="mt-4">
            @csrf
            <button type="submit" id="resend-btn" class="w-full rounded-xl border border-cyan-400/25 text-cyan-200 text-sm font-semibold py-2.5 hover:bg-cyan-400/10 transition-colors">
                Renvoyer le code
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
    (function () {
        const countdownEl = document.getElementById('otp-seconds');
        const box = document.getElementById('otp-countdown');
        const resendBtn = document.getElementById('resend-btn');
        const submitBtn = document.getElementById('otp-submit');
        const otpInput = document.getElementById('otp');

        let remaining = 60; // 1 minute — mirrors the server side TTL
        resendBtn.disabled = true;
        resendBtn.classList.add('opacity-50', 'cursor-not-allowed');

        const timer = setInterval(function () {
            remaining -= 1;
            if (remaining <= 0) {
                clearInterval(timer);
                box.innerHTML = '<b class="text-red-300">Le code a expiré.</b> Demandez un nouveau code.';
                resendBtn.disabled = false;
                resendBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                return;
            }
            countdownEl.textContent = remaining;
            if (remaining <= 30 && resendBtn.disabled) {
                resendBtn.disabled = false;
                resendBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            }
        }, 1000);

        // Digits only.
        otpInput.addEventListener('input', function () {
            this.value = this.value.replace(/\D/g, '').slice(0, 6);
        });

        // Loading state on submit.
        document.querySelector('form[action="{{ route('password.verify.otp.post') }}"]')
            .addEventListener('submit', function () {
                submitBtn.disabled = true;
                submitBtn.textContent = 'Vérification…';
            });
    })();
</script>
@endpush
@endsection
