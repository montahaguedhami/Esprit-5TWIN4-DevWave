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
    .aqua-auth-page [class~="text-cyan-100/70"], .aqua-auth-page [class~="text-cyan-100/65"] { color: #52677a !important; }
    .aqua-auth-page [class*="text-cyan-100/"] { color: #647b8e !important; }
    .aqua-auth-page [class*="bg-slate-950"] { background-color: #f5f8fb !important; }
    .aqua-auth-page [class*="border-cyan-400/"] { border-color: #d5e2eb !important; }
    .aqua-auth-page input:not([type="checkbox"]), .aqua-auth-page select { color: #102a43; background: #f8fafc; border-color: #cbd9e5; }
    /* Keep values clear of leading icons even if utility CSS is purged. */
    .aqua-auth-page .relative > input:not([type="checkbox"]) { padding-left: 2.75rem !important; padding-right: 2.75rem !important; }
    .aqua-auth-page .relative > i { z-index: 1; pointer-events: none; }
    .aqua-auth-page input[aria-invalid="true"] { border-color: #f43f5e !important; }
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

    <!-- Right Section - Register Form -->
    <section class="glass-strong p-6 sm:p-8 animate-fade-in-up" style="animation-delay: 0.12s">
        <div class="flex items-center justify-between mb-7">
            <div>
                <p class="text-xs font-semibold tracking-wider text-cyan-300 uppercase">Accès sécurisé</p>
                <h2 class="text-2xl font-display font-bold text-white mt-1">Créer votre compte</h2>
            </div>
            <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-cyan-500/20 to-blue-600/20 border border-cyan-400/25 flex items-center justify-center">
                <svg class="w-5 h-5 text-cyan-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                </svg>
            </div>
        </div>

        <!-- Mode Toggle Tabs -->
        <div class="grid grid-cols-3 gap-1 p-1 rounded-xl bg-slate-950/30 border border-white/5 mb-7">
            <a href="{{ route('auth.login') }}" class="flex items-center justify-center gap-1.5 rounded-lg px-2 py-2 text-[11px] sm:text-xs font-semibold transition-all text-cyan-100/45 hover:text-cyan-100/80">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                </svg>
                <span class="hidden sm:inline">Connexion</span>
            </a>
            <a href="{{ route('auth.register') }}" class="flex items-center justify-center gap-1.5 rounded-lg px-2 py-2 text-[11px] sm:text-xs font-semibold transition-all bg-gradient-to-r from-cyan-500 to-blue-600 text-white shadow-lg shadow-cyan-500/20">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                </svg>
                <span class="hidden sm:inline">Inscription</span>
            </a>
            <a href="{{ route('auth.forgot-password') }}" class="flex items-center justify-center gap-1.5 rounded-lg px-2 py-2 text-[11px] sm:text-xs font-semibold transition-all text-cyan-100/45 hover:text-cyan-100/80">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                </svg>
                <span class="hidden sm:inline">Mot de passe</span>
            </a>
        </div>

        <!-- Register Form -->
        <form method="POST" action="{{ route('register.post') }}" id="registerForm" class="space-y-4" novalidate>
            @csrf
        @if (session('error'))
                <div role="alert" class="rounded-xl border border-red-400/25 bg-red-500/10 p-3 text-sm text-red-200">
                    {{ session('error') }}
                </div>
            @endif
            
            <div>
                <label for="name" class="text-xs font-semibold text-cyan-100/70 mb-2 block">
                    Nom complet <span class="text-rose-400">*</span>
                </label>
                <div class="relative">
                    <input 
                        type="text" 
                        name="name" 
                        id="name"
                        aria-invalid="{{ $errors->has('name') ? 'true' : 'false' }}"
                        required 
                        minlength="3"
                        value="{{ old('name') }}"
                        placeholder="Ex. Yassine Hamdi" 
                        class="w-full bg-slate-950/35 border border-cyan-400/15 rounded-xl pl-11 pr-4 py-3 text-sm text-white placeholder:text-cyan-100/30 focus:outline-none focus:border-cyan-400/50 transition-colors" 
                    />
                    <i data-lucide="user" class="w-4 h-4 text-cyan-100/30 absolute left-4 top-1/2 -translate-y-1/2"></i>
                </div>
                <p class="text-[11px] text-rose-400 mt-1.5 hidden" id="name-error">
                    <i data-lucide="alert-circle" class="w-3.5 h-3.5 inline"></i>
                    Lettres, espaces, apostrophes, tirets et points uniquement (3 caractères minimum).
                </p>
                @error('name')
                    <p class="field-server-error mt-1.5 text-xs text-rose-500" data-field-error="name" role="alert">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="email" class="text-xs font-semibold text-cyan-100/70 mb-2 block">
                    Adresse email <span class="text-rose-400">*</span>
                </label>
                <div class="relative">
                    <input 
                        type="email" 
                        name="email" 
                        id="email"
                        aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}"
                        value="{{ old('email') }}"
                        required 
                        placeholder="vous@exemple.tn" 
                        class="w-full bg-slate-950/35 border border-cyan-400/15 rounded-xl pl-11 pr-4 py-3 text-sm text-white placeholder:text-cyan-100/30 focus:outline-none focus:border-cyan-400/50 transition-colors" 
                    />
                    <i data-lucide="mail" class="w-4 h-4 text-cyan-100/30 absolute left-4 top-1/2 -translate-y-1/2"></i>
                </div>
                <p class="text-[11px] text-rose-400 mt-1.5 hidden" id="email-error">
                    <i data-lucide="alert-circle" class="w-3.5 h-3.5 inline"></i>
                    Adresse email invalide (ex. vous@exemple.tn).
                </p>
                @error('email')
                    <p class="field-server-error mt-1.5 text-xs text-rose-500" data-field-error="email" role="alert">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="phone" class="text-xs font-semibold text-cyan-100/70 mb-2 block">
                    Numéro de téléphone
                </label>
                <div class="relative">
                    <input id="phone" 
                        aria-invalid="{{ $errors->has('phone') ? 'true' : 'false' }}"
                        type="tel" 
                        name="phone" 
                        value="{{ old('phone') }}"
                        placeholder="+216 XX XXX XXX" 
                        class="w-full bg-slate-950/35 border border-cyan-400/15 rounded-xl pl-11 pr-4 py-3 text-sm text-white placeholder:text-cyan-100/30 focus:outline-none focus:border-cyan-400/50 transition-colors" 
                    />
                    <i data-lucide="phone" class="w-4 h-4 text-cyan-100/30 absolute left-4 top-1/2 -translate-y-1/2"></i>
                </div>
                <p class="text-[11px] text-rose-400 mt-1.5 hidden" id="phone-error">
                    <i data-lucide="alert-circle" class="w-3.5 h-3.5 inline"></i>
                    Numéro invalide. Format accepté : +216 XX XXX XXX (8 chiffres minimum).
                </p>
                @error('phone')
                    <p class="field-server-error mt-1.5 text-xs text-rose-500" data-field-error="phone" role="alert">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="text-xs font-semibold text-cyan-100/70 mb-2 block">
                    Mot de passe <span class="text-rose-400">*</span>
                </label>
                <div class="relative">
                    <input 
                        type="password" 
                        name="password" 
                        id="password" 
                        aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}"
                        required 
                        minlength="12" 
                        placeholder="••••••••" 
                        class="w-full bg-slate-950/35 border border-cyan-400/15 rounded-xl pl-11 pr-11 py-3 text-sm text-white placeholder:text-cyan-100/30 focus:outline-none focus:border-cyan-400/50 transition-colors" 
                    />
                    <i data-lucide="lock" class="w-4 h-4 text-cyan-100/30 absolute left-4 top-1/2 -translate-y-1/2"></i>
                    <button type="button" onclick="togglePasswordVisibility('password')" class="text-cyan-100/35 hover:text-cyan-300 absolute right-4 top-1/2 -translate-y-1/2" aria-label="Afficher le mot de passe">
                        <i data-lucide="eye" class="w-4 h-4 eye-icon-password"></i>
                        <i data-lucide="eye-off" class="w-4 h-4 eye-off-icon-password hidden"></i>
                    </button>
                </div>
                @error('password')
                    <p class="field-server-error mt-1.5 text-xs text-rose-500" data-field-error="password" role="alert">{{ $message }}</p>
                @enderror
                <p class="mt-1.5 hidden text-xs text-rose-500" id="password-client-error" role="alert" aria-live="polite"></p>
                <!-- Password Strength Indicator -->
                <div class="mt-2">
                    <div class="flex gap-1 mb-1">
                        <div class="h-1 flex-1 rounded-full bg-slate-800/50" id="strength-bar-1"></div>
                        <div class="h-1 flex-1 rounded-full bg-slate-800/50" id="strength-bar-2"></div>
                        <div class="h-1 flex-1 rounded-full bg-slate-800/50" id="strength-bar-3"></div>
                        <div class="h-1 flex-1 rounded-full bg-slate-800/50" id="strength-bar-4"></div>
                    </div>
                    <p class="text-xs text-cyan-100/50" id="strength-text">12 caractères minimum</p>
                </div>

                <!-- Exigences en temps réel (miroir exact des règles serveur) -->
                <ul class="mt-2.5 grid gap-1 text-[11px]" id="pw-rules">
                    <li class="pw-rule flex items-center gap-1.5 text-cyan-100/45 transition-colors" data-rule="len">
                        <span class="pw-mark w-3.5 h-3.5 rounded-full border border-current flex items-center justify-center text-[8px] leading-none font-bold shrink-0">✓</span>
                        12 caractères minimum
                    </li>
                    <li class="pw-rule flex items-center gap-1.5 text-cyan-100/45 transition-colors" data-rule="lower">
                        <span class="pw-mark w-3.5 h-3.5 rounded-full border border-current flex items-center justify-center text-[8px] leading-none font-bold shrink-0">✓</span>
                        Une lettre minuscule
                    </li>
                    <li class="pw-rule flex items-center gap-1.5 text-cyan-100/45 transition-colors" data-rule="upper">
                        <span class="pw-mark w-3.5 h-3.5 rounded-full border border-current flex items-center justify-center text-[8px] leading-none font-bold shrink-0">✓</span>
                        Une lettre majuscule
                    </li>
                    <li class="pw-rule flex items-center gap-1.5 text-cyan-100/45 transition-colors" data-rule="digit">
                        <span class="pw-mark w-3.5 h-3.5 rounded-full border border-current flex items-center justify-center text-[8px] leading-none font-bold shrink-0">✓</span>
                        Un chiffre
                    </li>
                    <li class="pw-rule flex items-center gap-1.5 text-cyan-100/45 transition-colors" data-rule="symbol">
                        <span class="pw-mark w-3.5 h-3.5 rounded-full border border-current flex items-center justify-center text-[8px] leading-none font-bold shrink-0">✓</span>
                        Un caractère spécial
                    </li>
                    <li class="pw-rule flex items-center gap-1.5 text-cyan-100/45 transition-colors" data-rule="common">
                        <span class="pw-mark w-3.5 h-3.5 rounded-full border border-current flex items-center justify-center text-[8px] leading-none font-bold shrink-0">✓</span>
                        Différent des mots de passe courants
                    </li>
                </ul>

                <button type="button" id="pw-generate" class="mt-2.5 inline-flex items-center gap-1.5 text-[11px] font-semibold text-cyan-400 hover:text-cyan-300 transition-colors">
                    <i data-lucide="wand-2" class="w-3.5 h-3.5"></i>
                    Suggérer un mot de passe sécurisé
                </button>
                <p class="text-[11px] text-emerald-400 mt-1.5 hidden" id="pw-generated">
                    <i data-lucide="check-circle" class="w-3.5 h-3.5 inline"></i>
                    Mot de passe généré — copiez-le dans un gestionnaire de mots de passe.
                </p>
            </div>

            <div>
                <label for="password_confirmation" class="text-xs font-semibold text-cyan-100/70 mb-2 block">
                    Confirmer le mot de passe <span class="text-rose-400">*</span>
                </label>
                <div class="relative">
                    <input 
                        type="password" 
                        name="password_confirmation" 
                        id="password_confirmation" 
                        aria-invalid="{{ $errors->has('password_confirmation') ? 'true' : 'false' }}"
                        required 
                        minlength="12" 
                        placeholder="••••••••" 
                        class="w-full bg-slate-950/35 border border-cyan-400/15 rounded-xl pl-11 pr-11 py-3 text-sm text-white placeholder:text-cyan-100/30 focus:outline-none focus:border-cyan-400/50 transition-colors" 
                    />
                    <i data-lucide="lock" class="w-4 h-4 text-cyan-100/30 absolute left-4 top-1/2 -translate-y-1/2"></i>
                    <button type="button" onclick="togglePasswordVisibility('password_confirmation')" class="text-cyan-100/35 hover:text-cyan-300 absolute right-4 top-1/2 -translate-y-1/2" aria-label="Afficher le mot de passe">
                        <i data-lucide="eye" class="w-4 h-4 eye-icon-confirmation"></i>
                        <i data-lucide="eye-off" class="w-4 h-4 eye-off-icon-confirmation hidden"></i>
                    </button>
                </div>
                <p class="text-xs text-rose-400 mt-1.5 hidden" id="password-match-error">
                    <i data-lucide="alert-circle" class="w-3.5 h-3.5 inline"></i>
                    Les mots de passe ne correspondent pas
                </p>
                @error('password_confirmation')
                    <p class="field-server-error mt-1.5 text-xs text-rose-500" data-field-error="password_confirmation" role="alert">{{ $message }}</p>
                @enderror
            </div>
<!-- Terms Checkbox -->
            <div class="flex items-start gap-3 p-4 rounded-xl bg-cyan-500/5 border border-cyan-400/10">
                <input 
                    type="checkbox" 
                    name="terms" 
                    id="terms" 
                    aria-invalid="{{ $errors->has('terms') ? 'true' : 'false' }}"
                    required
                    class="mt-0.5 w-4 h-4 rounded border-cyan-400/30 bg-slate-950/50 text-cyan-500 focus:ring-2 focus:ring-cyan-400/50 focus:ring-offset-0"
                />
                <label for="terms" class="text-xs text-cyan-100/70 leading-relaxed">
                    J'accepte les <a href="#" class="text-cyan-400 hover:text-cyan-300 underline">conditions d'utilisation</a> 
                    et la <a href="#" class="text-cyan-400 hover:text-cyan-300 underline">politique de confidentialité</a> d'AquaSecure
                </label>
            </div>
            @error('terms')
                <p class="field-server-error -mt-3 text-xs text-rose-500" data-field-error="terms" role="alert">{{ $message }}</p>
            @enderror
            <p class="-mt-3 hidden text-xs text-rose-500" id="terms-client-error" role="alert" aria-live="polite"></p>

            <p id="register-client-error" role="alert" aria-live="assertive" class="hidden rounded-xl border border-amber-400/30 bg-amber-500/10 px-3 py-2 text-sm text-amber-200"></p>
            <button type="submit" id="register-submit" class="ripple-btn rounded-xl font-semibold transition-all duration-300 cursor-pointer bg-gradient-to-r from-cyan-500 to-blue-600 text-white hover:from-cyan-400 hover:to-blue-500 shadow-lg shadow-cyan-500/25 px-8 py-4 text-lg w-full flex items-center justify-center gap-2">
                <i data-lucide="user-plus" class="w-5 h-5"></i>
                Créer mon compte
                <i data-lucide="arrow-right" class="w-4 h-4"></i>
            </button>
        </form>

        <p class="text-xs text-cyan-100/40 mt-4 text-center">
            Déjà inscrit ? 
            <a href="{{ route('auth.login') }}" class="text-cyan-400 hover:text-cyan-300 font-semibold">
                Se connecter
            </a>
        </p>
    </section>
</div>

@push('scripts')
<script>
    // Password visibility toggle
    function togglePasswordVisibility(fieldId) {
        const input = document.getElementById(fieldId);
        const eyeIcon = document.querySelector(`.eye-icon-${fieldId.replace('_', '-')}`);
        const eyeOffIcon = document.querySelector(`.eye-off-icon-${fieldId.replace('_', '-')}`);
        
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
    
    // ── Password rules ────────────────────────────────────────────────────────
    // Mirrors the server-side rule in routes/web.php:
    // Password::min(12)->letters()->mixedCase()->numbers()->symbols()
    // plus the rejection of well-known passwords.
    const COMMON_PASSWORDS = [
        'password', 'password1', 'password123', '123456', '12345678', '123456789',
        'qwerty', 'azerty', 'iloveyou', 'admin', 'welcome', 'letmein', 'monkey',
        'dragon', 'sunshine', 'princess', 'football', 'abc123', 'motdepasse',
        'motdepasse123', 'azertyuiop', 'qwertyuiop', 'aquasecure', 'citoyen',
        'password123!', 'password1234!', 'welcome12345!', 'qwerty12345!',
        'azerty12345!', 'admin12345!', 'letmein12345!', 'motdepasse1234!',
    ];
    const RULES = {
        len:     (v) => [...v].length >= 12,
        lower:   (v) => /\p{Ll}/u.test(v),
        upper:   (v) => /\p{Lu}/u.test(v),
        digit:   (v) => /\p{N}/u.test(v),
        symbol:  (v) => /[\p{Z}\p{S}\p{P}]/u.test(v),
        common:  (v) => ! COMMON_PASSWORDS.includes(v.toLowerCase()),
    };
    const RULE_LABELS = {
        len: '12 caractères minimum',
        lower: 'une lettre minuscule',
        upper: 'une lettre majuscule',
        digit: 'un chiffre',
        symbol: 'un caractère spécial',
        common: 'un mot de passe différent des mots courants',
    };
    const OK_COLOR = '#34d399';

    const passwordInput = document.getElementById('password');
    const confirmInput = document.getElementById('password_confirmation');
    const matchError = document.getElementById('password-match-error');
    const strengthBars = [1, 2, 3, 4].map(i => document.getElementById('strength-bar-' + i));
    const strengthText = document.getElementById('strength-text');
    const ruleItems = Array.from(document.querySelectorAll('#pw-rules .pw-rule'));

    function evaluatePassword(value) {
        const passed = {};
        for (const [key, test] of Object.entries(RULES)) {
            passed[key] = test(value);
        }
        return passed;
    }

    function renderPasswordFeedback(passed = evaluatePassword(passwordInput.value)) {
        const value = passwordInput.value;
        const satisfied = Object.values(passed).filter(Boolean).length;

        ruleItems.forEach(item => {
            const ok = passed[item.dataset.rule];
            item.classList.toggle('pw-ok', ok);
            item.style.color = ok ? OK_COLOR : 'rgba(194, 231, 242, 0.45)';
            const mark = item.querySelector('.pw-mark');
            mark.style.opacity = ok ? '1' : '0.25';
            mark.style.background = ok ? OK_COLOR : 'transparent';
            mark.style.borderColor = ok ? OK_COLOR : 'currentColor';
            mark.style.color = ok ? '#04212b' : 'transparent';
            mark.style.transform = ok ? 'scale(1)' : 'scale(.85)';
        });

        // Strength = number of satisfied rules, capped at the 4 bars.
        const filled = value ? Math.max(1, satisfied) : 0;
        const colors = ['#ef4444', '#f97316', '#eab308', '#22c55e'];
        const texts = ['Faible', 'Moyen', 'Bon', 'Excellent'];

        strengthBars.forEach((bar, i) => {
            bar.style.background = i < filled ? colors[Math.min(filled, 4) - 1] : '#1e293b';
        });

        if (value) {
            strengthText.textContent = `Force du mot de passe : ${texts[Math.min(filled, 4) - 1]}`;
            strengthText.style.color = colors[Math.min(filled, 4) - 1];
        } else {
            strengthText.textContent = '12 caractères minimum';
            strengthText.style.color = 'rgba(240, 253, 255, 0.5)';
        }

        // The user has typed a password: re-check the confirmation too.
        renderMatchFeedback();
        return passed;
    }

    function renderMatchFeedback() {
        const mismatch = confirmInput.value && confirmInput.value !== passwordInput.value;
        matchError.classList.toggle('hidden', ! mismatch);
        confirmInput.classList.toggle('border-rose-400', !! mismatch);
    }

    passwordInput.addEventListener('input', renderPasswordFeedback);
    confirmInput.addEventListener('input', renderMatchFeedback);

    // ── Password generator ────────────────────────────────────────────────────
    // Uses the browser CSPRNG, guarantees one character of every category and
    // returns a 16-character string (well above the 8-character minimum).
    function generateStrongPassword(length = 16) {
        const LOWER = 'abcdefghijkmnopqrstuvwxyz';
        const UPPER = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
        const DIGIT = '23456789';
        const SYMBOL = '!@#$%^&*?-_=+';
        const ALL = LOWER + UPPER + DIGIT + SYMBOL;

        const randomInt = (max) => {
            // Rejection sampling keeps the distribution uniform.
            const limit = Math.floor(0xFFFFFFFF / max) * max;
            const buf = new Uint32Array(1);
            do { crypto.getRandomValues(buf); } while (buf[0] >= limit);
            return buf[0] % max;
        };
        const pick = (set) => set[randomInt(set.length)];

        const chars = [pick(LOWER), pick(UPPER), pick(DIGIT), pick(SYMBOL)];
        while (chars.length < length) chars.push(pick(ALL));

        // Fisher–Yates shuffle driven by the CSPRNG.
        for (let i = chars.length - 1; i > 0; i--) {
            const j = randomInt(i + 1);
            [chars[i], chars[j]] = [chars[j], chars[i]];
        }
        return chars.join('');
    }

    const generatedNotice = document.getElementById('pw-generated');
    document.getElementById('pw-generate').addEventListener('click', () => {
        const value = generateStrongPassword();
        passwordInput.value = value;
        confirmInput.value = value;
        // Reveal it so the user can copy it before closing the tab.
        passwordInput.type = 'text';
        confirmInput.type = 'text';
        document.querySelectorAll('.eye-icon-password, .eye-icon-confirmation').forEach(el => el.classList.add('hidden'));
        document.querySelectorAll('.eye-off-icon-password, .eye-off-icon-confirmation').forEach(el => el.classList.remove('hidden'));
        generatedNotice.classList.remove('hidden');
        matchError.classList.add('hidden');
        confirmInput.classList.remove('border-rose-400');
        renderPasswordFeedback();
    });

    // ── Field validation (mirrors the server rules) ───────────────────────────
    const NAME_RE  = /^[\p{L}\p{M}\s'\-\.]+$/u;
    const EMAIL_RE = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;
    const PHONE_RE = /^\+?[0-9\s\-\(\)]{8,20}$/;

    const nameInput  = document.getElementById('name');
    const emailInput = document.getElementById('email');
    const phoneInput = document.getElementById('phone');

    /**
     * Attach live validation to a field.
     *
     * @param {boolean} optional  An empty value is acceptable (phone only).
     * @returns {() => boolean}   Current validity of the field.
     */
    function bindFieldCheck(input, errorId, test, optional = false) {
        const error = document.getElementById(errorId);
        const check = () => {
            const empty = input.value.trim() === '';
            // An empty optional field is never flagged while typing.
            if (empty && optional) {
                error.classList.add('hidden');
                input.classList.remove('border-rose-400');
                input.setAttribute('aria-invalid', 'false');
                return true;
            }
            const bad = empty || ! test(input.value.trim());
            error.classList.toggle('hidden', ! bad);
            input.classList.toggle('border-rose-400', bad);
            input.setAttribute('aria-invalid', String(bad));
            return ! bad;
        };
        input.addEventListener('input', check);
        input.addEventListener('blur', check);
        return check;
    }

    const checks = {
        name:  bindFieldCheck(nameInput,  'name-error',  v => v.length >= 3 && NAME_RE.test(v)),
        email: bindFieldCheck(emailInput, 'email-error', v => EMAIL_RE.test(v)),
        phone: bindFieldCheck(phoneInput, 'phone-error', v => PHONE_RE.test(v), true),
    };

    // ── Form validation ───────────────────────────────────────────────────────
    const registerForm = document.getElementById('registerForm');
    const registerClientError = document.getElementById('register-client-error');
    const registerSubmit = document.getElementById('register-submit');
    const passwordClientError = document.getElementById('password-client-error');
    const termsClientError = document.getElementById('terms-client-error');
    const showRegisterError = () => {
        registerClientError.classList.remove('hidden');
        registerClientError.scrollIntoView({ behavior: 'smooth', block: 'center' });
    };
    registerForm.addEventListener('input', (event) => {
        registerClientError.classList.add('hidden');
        if (event.target.name === 'password') passwordClientError.classList.add('hidden');
        if (event.target.name === 'terms') termsClientError.classList.add('hidden');
        const fieldError = [...registerForm.querySelectorAll('[data-field-error]')]
            .find((error) => error.dataset.fieldError === event.target.name);
        if (fieldError) fieldError.remove();
        if (event.target.name) event.target.setAttribute('aria-invalid', 'false');
    });
    registerForm.addEventListener('submit', function (e) {
        registerClientError.classList.add('hidden');
        registerClientError.textContent = '';
        if (passwordInput.value !== confirmInput.value) {
            e.preventDefault();
            matchError.textContent = confirmInput.value
                ? 'Les deux mots de passe doivent \u00eatre identiques.'
                : 'Veuillez confirmer votre mot de passe.';
            matchError.classList.remove('hidden');
            confirmInput.classList.add('border-rose-400');
            confirmInput.focus();
            matchError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            return;
        }
        // Stop an obviously weak password before the round-trip to the server.
        const passed = renderPasswordFeedback();
        const failedRules = Object.entries(passed)
            .filter(([, ok]) => ! ok)
            .map(([rule]) => RULE_LABELS[rule]);
        if (failedRules.length) {
            e.preventDefault();
            passwordClientError.textContent = `À corriger : ${failedRules.join(', ')}.`;
            passwordClientError.classList.remove('hidden');
            passwordClientError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            passwordInput.focus();
            return;
        }
        // Report every field that fails its own rule, starting with the first.
        let firstBad = null;
        for (const [key, check] of Object.entries(checks)) {
            if (! check() && ! firstBad) {
                firstBad = key;
            }
        }
        if (firstBad) {
            e.preventDefault();
            registerClientError.textContent = 'V\u00e9rifiez les champs signal\u00e9s avant de cr\u00e9er votre compte.';
            showRegisterError();
            document.getElementById(firstBad).focus();
            return;
        }
        if (!document.getElementById('terms').checked) {
            e.preventDefault();
            termsClientError.textContent = 'Veuillez accepter les conditions pour créer votre compte.';
            termsClientError.classList.remove('hidden');
            termsClientError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            document.getElementById('terms').focus();
            return;
        }
        registerSubmit.disabled = true;
        registerSubmit.setAttribute('aria-busy', 'true');
        registerSubmit.textContent = 'Création du compte…';
    });

    // Initial paint (also corrects state after a server-side redirect).
    renderPasswordFeedback();
</script>
@endpush
@endsection
