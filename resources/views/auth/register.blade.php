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
        <form method="POST" action="{{ route('register.post') }}" id="registerForm" class="space-y-4">
            @csrf
            
            <div>
                <label class="text-xs font-semibold text-cyan-100/70 mb-2 block">
                    Nom complet <span class="text-rose-400">*</span>
                </label>
                <div class="relative">
                    <input 
                        type="text" 
                        name="name" 
                        id="name"
                        required 
                        minlength="3"
                        placeholder="Ex. Yassine Hamdi" 
                        class="w-full bg-slate-950/35 border border-cyan-400/15 rounded-xl pl-11 pr-4 py-3 text-sm text-white placeholder:text-cyan-100/30 focus:outline-none focus:border-cyan-400/50 transition-colors" 
                    />
                    <i data-lucide="user" class="w-4 h-4 text-cyan-100/30 absolute left-4 top-1/2 -translate-y-1/2"></i>
                </div>
            </div>

            <div>
                <label class="text-xs font-semibold text-cyan-100/70 mb-2 block">
                    Adresse email <span class="text-rose-400">*</span>
                </label>
                <div class="relative">
                    <input 
                        type="email" 
                        name="email" 
                        id="email"
                        required 
                        placeholder="vous@exemple.tn" 
                        class="w-full bg-slate-950/35 border border-cyan-400/15 rounded-xl pl-11 pr-4 py-3 text-sm text-white placeholder:text-cyan-100/30 focus:outline-none focus:border-cyan-400/50 transition-colors" 
                    />
                    <i data-lucide="mail" class="w-4 h-4 text-cyan-100/30 absolute left-4 top-1/2 -translate-y-1/2"></i>
                </div>
            </div>

            <div>
                <label class="text-xs font-semibold text-cyan-100/70 mb-2 block">
                    Numéro de téléphone
                </label>
                <div class="relative">
                    <input 
                        type="tel" 
                        name="phone" 
                        placeholder="+216 XX XXX XXX" 
                        class="w-full bg-slate-950/35 border border-cyan-400/15 rounded-xl pl-11 pr-4 py-3 text-sm text-white placeholder:text-cyan-100/30 focus:outline-none focus:border-cyan-400/50 transition-colors" 
                    />
                    <i data-lucide="phone" class="w-4 h-4 text-cyan-100/30 absolute left-4 top-1/2 -translate-y-1/2"></i>
                </div>
            </div>

            <div>
                <label class="text-xs font-semibold text-cyan-100/70 mb-2 block">
                    Mot de passe <span class="text-rose-400">*</span>
                </label>
                <div class="relative">
                    <input 
                        type="password" 
                        name="password" 
                        id="password" 
                        required 
                        minlength="6" 
                        placeholder="••••••••" 
                        class="w-full bg-slate-950/35 border border-cyan-400/15 rounded-xl pl-11 pr-11 py-3 text-sm text-white placeholder:text-cyan-100/30 focus:outline-none focus:border-cyan-400/50 transition-colors" 
                    />
                    <i data-lucide="lock" class="w-4 h-4 text-cyan-100/30 absolute left-4 top-1/2 -translate-y-1/2"></i>
                    <button type="button" onclick="togglePasswordVisibility('password')" class="text-cyan-100/35 hover:text-cyan-300 absolute right-4 top-1/2 -translate-y-1/2" aria-label="Afficher le mot de passe">
                        <i data-lucide="eye" class="w-4 h-4 eye-icon-password"></i>
                        <i data-lucide="eye-off" class="w-4 h-4 eye-off-icon-password hidden"></i>
                    </button>
                </div>
                <!-- Password Strength Indicator -->
                <div class="mt-2">
                    <div class="flex gap-1 mb-1">
                        <div class="h-1 flex-1 rounded-full bg-slate-800/50" id="strength-bar-1"></div>
                        <div class="h-1 flex-1 rounded-full bg-slate-800/50" id="strength-bar-2"></div>
                        <div class="h-1 flex-1 rounded-full bg-slate-800/50" id="strength-bar-3"></div>
                        <div class="h-1 flex-1 rounded-full bg-slate-800/50" id="strength-bar-4"></div>
                    </div>
                    <p class="text-xs text-cyan-100/50" id="strength-text">Minimum 6 caractères</p>
                </div>
            </div>

            <div>
                <label class="text-xs font-semibold text-cyan-100/70 mb-2 block">
                    Confirmer le mot de passe <span class="text-rose-400">*</span>
                </label>
                <div class="relative">
                    <input 
                        type="password" 
                        name="password_confirmation" 
                        id="password_confirmation" 
                        required 
                        minlength="6" 
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
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="text-xs font-semibold text-cyan-100/70 mb-2 block">Profil</label>
                    <div class="relative">
                        <select name="role" class="w-full bg-slate-950/35 border border-cyan-400/15 rounded-xl pl-11 pr-4 py-3 text-sm text-white focus:outline-none focus:border-cyan-400/50 appearance-none cursor-pointer">
                            <option value="citizen">Citoyen</option>
                            <option value="technician">Technicien</option>
                            <option value="manager">Gestionnaire</option>
                        </select>
                        <i data-lucide="user-circle" class="w-4 h-4 text-cyan-100/30 absolute left-4 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                        <i data-lucide="chevron-down" class="w-4 h-4 text-cyan-100/30 absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                    </div>
                </div>
                <div>
                    <label class="text-xs font-semibold text-cyan-100/70 mb-2 block">Zone</label>
                    <div class="relative">
                        <select name="zone" class="w-full bg-slate-950/35 border border-cyan-400/15 rounded-xl pl-11 pr-4 py-3 text-sm text-white focus:outline-none focus:border-cyan-400/50 appearance-none cursor-pointer">
                            @foreach(App\Data\PlaceholderData::zones() as $zone)
                            <option>{{ $zone['name'] }}</option>
                            @endforeach
                        </select>
                        <i data-lucide="map-pin" class="w-4 h-4 text-cyan-100/30 absolute left-4 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                        <i data-lucide="chevron-down" class="w-4 h-4 text-cyan-100/30 absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                    </div>
                </div>
            </div>

            <!-- Terms Checkbox -->
            <div class="flex items-start gap-3 p-4 rounded-xl bg-cyan-500/5 border border-cyan-400/10">
                <input 
                    type="checkbox" 
                    name="terms" 
                    id="terms" 
                    required
                    class="mt-0.5 w-4 h-4 rounded border-cyan-400/30 bg-slate-950/50 text-cyan-500 focus:ring-2 focus:ring-cyan-400/50 focus:ring-offset-0"
                />
                <label for="terms" class="text-xs text-cyan-100/70 leading-relaxed">
                    J'accepte les <a href="#" class="text-cyan-400 hover:text-cyan-300 underline">conditions d'utilisation</a> 
                    et la <a href="#" class="text-cyan-400 hover:text-cyan-300 underline">politique de confidentialité</a> d'AquaSecure
                </label>
            </div>

            <x-ripple-button type="submit" size="lg" class="w-full flex items-center justify-center gap-2">
                <i data-lucide="user-plus" class="w-5 h-5"></i>
                Créer mon compte
                <i data-lucide="arrow-right" class="w-4 h-4"></i>
            </x-ripple-button>
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
    
    // Password strength checker
    const passwordInput = document.getElementById('password');
    const strengthBars = [
        document.getElementById('strength-bar-1'),
        document.getElementById('strength-bar-2'),
        document.getElementById('strength-bar-3'),
        document.getElementById('strength-bar-4'),
    ];
    const strengthText = document.getElementById('strength-text');
    
    passwordInput.addEventListener('input', function() {
        const password = this.value;
        let strength = 0;
        
        // Reset bars
        strengthBars.forEach(bar => {
            bar.style.background = '#1e293b';
        });
        
        if (password.length >= 6) strength++;
        if (password.length >= 10) strength++;
        if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
        if (/[0-9]/.test(password) && /[^A-Za-z0-9]/.test(password)) strength++;
        
        const colors = ['#ef4444', '#f97316', '#eab308', '#22c55e'];
        const texts = ['Faible', 'Moyen', 'Bon', 'Excellent'];
        
        if (strength > 0) {
            for (let i = 0; i < strength; i++) {
                strengthBars[i].style.background = colors[strength - 1];
            }
            strengthText.textContent = `Force du mot de passe : ${texts[strength - 1]}`;
            strengthText.style.color = colors[strength - 1];
        } else {
            strengthText.textContent = 'Minimum 6 caractères';
            strengthText.style.color = 'rgba(240, 253, 255, 0.5)';
        }
    });
    
    // Password match validation
    const confirmInput = document.getElementById('password_confirmation');
    const matchError = document.getElementById('password-match-error');
    
    confirmInput.addEventListener('input', function() {
        if (this.value && this.value !== passwordInput.value) {
            matchError.classList.remove('hidden');
            this.classList.add('border-rose-400');
        } else {
            matchError.classList.add('hidden');
            this.classList.remove('border-rose-400');
        }
    });
    
    // Form validation
    document.getElementById('registerForm').addEventListener('submit', function(e) {
        if (passwordInput.value !== confirmInput.value) {
            e.preventDefault();
            matchError.classList.remove('hidden');
            confirmInput.focus();
            return;
        }
    });
</script>
@endpush
@endsection
