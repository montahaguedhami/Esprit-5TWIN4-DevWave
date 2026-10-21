@extends('layouts.frontoffice')

@php
    $title = 'Financer un projet — AquaSecure';
    $budgetDisponible = $projet->budget_disponible;
@endphp

@section('frontoffice-content')
<div class="space-y-6 animate-fade-in-up max-w-2xl mx-auto">

    {{-- ── Fil d'Ariane ── --}}
    <div>
        <a href="{{ route('citizen.projets.show', $projet) }}"
           class="text-cyan-400 hover:text-cyan-300 text-sm font-semibold flex items-center gap-1 mb-3">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            Retour au projet
        </a>
        <h1 class="text-2xl sm:text-3xl font-display font-bold text-white">Financer ce projet</h1>
        <p class="text-cyan-100/55 text-sm mt-1">Votre demande sera examinée par un gestionnaire avant tout paiement.</p>
    </div>

    {{-- ── Récap projet ── --}}
    <div class="glass rounded-2xl p-5 space-y-3">
        <div class="flex items-start gap-4">
            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-cyan-500 to-blue-600 flex items-center justify-center flex-shrink-0">
                <i data-lucide="briefcase" class="w-6 h-6 text-white"></i>
            </div>
            <div class="flex-1 min-w-0">
                <h2 class="font-bold text-white text-lg leading-tight">{{ $projet->nom }}</h2>
                @if($projet->description)
                <p class="text-cyan-100/55 text-sm mt-1 line-clamp-2">{{ $projet->description }}</p>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-3 gap-3 pt-2 border-t border-white/10">
            <div class="text-center">
                <div class="text-xs text-cyan-100/50 mb-1">Budget total</div>
                <div class="font-bold text-white text-sm">{{ number_format((float)$projet->budget, 0, ',', ' ') }} TND</div>
            </div>
            <div class="text-center">
                <div class="text-xs text-cyan-100/50 mb-1">Déjà financé</div>
                <div class="font-bold text-teal-300 text-sm">{{ number_format((float)$projet->total_finance, 0, ',', ' ') }} TND</div>
            </div>
            <div class="text-center">
                <div class="text-xs text-cyan-100/50 mb-1">Disponible</div>
                <div class="font-bold text-cyan-300 text-sm">{{ number_format($budgetDisponible, 0, ',', ' ') }} TND</div>
            </div>
        </div>
    </div>

    {{-- ── Alerte projet non éligible ── --}}
    @if(! $eligible)
    <div class="glass rounded-xl px-5 py-4 border border-amber-400/30 text-amber-300 text-sm flex items-start gap-3">
        <i data-lucide="alert-triangle" class="w-5 h-5 flex-shrink-0 mt-0.5"></i>
        <div>
            <p class="font-semibold mb-1">Projet non disponible au financement citoyen</p>
            <p class="text-amber-300/70 text-xs">Ce projet est terminé, suspendu ou son budget est entièrement financé.</p>
        </div>
    </div>

    @elseif($dejaSoumis)
    <div class="glass rounded-xl px-5 py-4 border border-cyan-400/30 text-cyan-300 text-sm flex items-start gap-3">
        <i data-lucide="info" class="w-5 h-5 flex-shrink-0 mt-0.5"></i>
        <div>
            <p class="font-semibold mb-1">Demande déjà en cours</p>
            <p class="text-cyan-300/70 text-xs">Vous avez déjà une demande en attente ou acceptée pour ce projet.</p>
        </div>
    </div>

    @else
    {{-- ── Formulaire ── --}}
    <div class="glass rounded-2xl p-6">
        <h3 class="font-semibold text-white mb-5 flex items-center gap-2">
            <i data-lucide="hand-coins" class="w-5 h-5 text-cyan-400"></i>
            Votre contribution
        </h3>

        {{-- Erreurs serveur (après soumission) --}}
        @if($errors->any())
        <div class="rounded-xl px-4 py-3 mb-5 bg-red-500/10 border border-red-400/30 text-red-300 text-sm space-y-1">
            @foreach($errors->all() as $err)
            <p class="flex items-center gap-1.5">
                <i data-lucide="alert-circle" class="w-3.5 h-3.5 flex-shrink-0"></i>{{ $err }}
            </p>
            @endforeach
        </div>
        @endif

        <form
            id="form-financement"
            method="POST"
            action="{{ route('citizen.demandes.store') }}"
            class="space-y-5"
            novalidate
        >
            @csrf
            <input type="hidden" name="projet_id" value="{{ $projet->id }}">

            <div>
                <label for="montant" class="block text-sm font-semibold text-cyan-100/70 mb-2">
                    Montant à contribuer (TND)
                    <span class="text-red-400 ml-0.5">*</span>
                </label>

                <div class="relative">
                    <input
                        type="number"
                        id="montant"
                        name="montant"
                        value="{{ old('montant') }}"
                        step="0.01"
                        placeholder="{{ number_format($minimum, 0) }}"
                        autocomplete="off"
                        data-min="{{ $minimum }}"
                        data-max="{{ $budgetDisponible }}"
                        class="w-full px-4 py-3 pr-16 rounded-xl bg-white/5 border border-white/10 text-white placeholder:text-cyan-100/30 focus:border-cyan-400/50 focus:outline-none focus:bg-white/[.07] transition-colors"
                        id="montant"
                    >
                    <span class="absolute right-4 top-1/2 -translate-y-1/2 text-cyan-100/50 text-sm font-semibold pointer-events-none">TND</span>
                </div>

                {{-- Message d'erreur client (JS) + serveur --}}
                <p id="montant-error"
                   class="hidden mt-2 text-sm font-medium text-red-400 flex items-center gap-1.5">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span id="montant-error-text"></span>
                </p>

                @error('montant')
                <p class="mt-2 text-sm font-medium text-red-400 flex items-center gap-1.5">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    {{ $message }}
                </p>
                @enderror

                <p class="text-cyan-100/40 text-xs mt-1.5">
                    Minimum <strong class="text-cyan-100/70">{{ number_format($minimum, 0, ',', ' ') }} TND</strong>
                    · Maximum <strong class="text-cyan-100/70">{{ number_format($budgetDisponible, 0, ',', ' ') }} TND</strong>
                </p>
            </div>

            {{-- Barre de progression live --}}
            <div id="montant-preview" class="hidden glass rounded-xl px-4 py-3 border border-cyan-400/15">
                <div class="flex justify-between text-xs text-cyan-100/50 mb-1.5">
                    <span>Votre contribution représente</span>
                    <span id="montant-pct" class="font-semibold text-cyan-300"></span>
                </div>
                <div class="h-2 rounded-full bg-white/5 overflow-hidden">
                    <div id="montant-bar"
                         class="h-full rounded-full bg-gradient-to-r from-cyan-500 to-blue-500 transition-all duration-300"
                         style="width:0%"></div>
                </div>
            </div>

            <div class="glass rounded-xl px-4 py-3 border border-white/5 text-xs text-cyan-100/50 space-y-1">
                <p class="flex items-center gap-1.5">
                    <i data-lucide="shield-check" class="w-3.5 h-3.5 text-cyan-400"></i>
                    Votre demande sera examinée par un gestionnaire.
                </p>
                <p class="flex items-center gap-1.5">
                    <i data-lucide="credit-card" class="w-3.5 h-3.5 text-cyan-400"></i>
                    Le paiement simulé n'intervient qu'après acceptation.
                </p>
                <p class="flex items-center gap-1.5">
                    <i data-lucide="lock" class="w-3.5 h-3.5 text-cyan-400"></i>
                    Aucun montant ne vous sera prélevé à cette étape.
                </p>
            </div>

            <button
                type="submit"
                id="btn-submit"
                class="w-full py-3 rounded-xl bg-gradient-to-r from-cyan-500 to-blue-600 text-white font-semibold text-sm hover:opacity-90 active:scale-[.98] transition-all flex items-center justify-center gap-2 disabled:opacity-40 disabled:cursor-not-allowed disabled:scale-100">
                <i data-lucide="send" class="w-4 h-4"></i>
                Envoyer la demande
            </button>
        </form>
    </div>
    @endif

</div>
@endsection

@push('scripts')
<script>
(function () {
    const input   = document.getElementById('montant');
    const errBox  = document.getElementById('montant-error');
    const errText = document.getElementById('montant-error-text');
    const preview = document.getElementById('montant-preview');
    const bar     = document.getElementById('montant-bar');
    const pct     = document.getElementById('montant-pct');
    const btn     = document.getElementById('btn-submit');
    const form    = document.getElementById('form-financement');

    if (!input) return;

    const MIN = parseFloat(input.dataset.min);
    const MAX = parseFloat(input.dataset.max);

    // ── Validation en temps réel ──────────────────────────────────────────
    function validate(val) {
        if (val === '' || val === null) {
            return 'Le montant est obligatoire.';
        }
        const n = parseFloat(val);
        if (isNaN(n) || n <= 0) {
            return 'Veuillez saisir un montant valide.';
        }
        if (n < MIN) {
            return 'Le montant minimum est de ' + MIN.toLocaleString('fr-TN') + ' TND.';
        }
        if (n > MAX) {
            return 'Le montant dépasse le budget disponible (' + MAX.toLocaleString('fr-TN') + ' TND).';
        }
        return null; // valide
    }

    function showError(msg) {
        errText.textContent = msg;
        errBox.classList.remove('hidden');
        errBox.classList.add('flex');
        input.classList.add('border-red-500/60');
        input.classList.remove('border-white/10', 'border-cyan-400/50');
        btn.disabled = true;
    }

    function clearError() {
        errBox.classList.add('hidden');
        errBox.classList.remove('flex');
        input.classList.remove('border-red-500/60');
        input.classList.add('border-white/10');
        btn.disabled = false;
    }

    function updatePreview(val) {
        const n = parseFloat(val);
        if (!isNaN(n) && n > 0 && MAX > 0) {
            const ratio = Math.min((n / MAX) * 100, 100).toFixed(1);
            bar.style.width = ratio + '%';
            pct.textContent = ratio + '% du budget disponible';
            preview.classList.remove('hidden');
        } else {
            preview.classList.add('hidden');
        }
    }

    // ── Événements ────────────────────────────────────────────────────────
    input.addEventListener('input', function () {
        const err = validate(this.value);
        if (err) {
            showError(err);
        } else {
            clearError();
        }
        updatePreview(this.value);
    });

    // Re-valider à la perte du focus pour les cas où l'utilisateur efface tout
    input.addEventListener('blur', function () {
        const err = validate(this.value);
        if (err) showError(err);
    });

    // ── Bloquer la soumission si invalide ─────────────────────────────────
    form.addEventListener('submit', function (e) {
        const err = validate(input.value);
        if (err) {
            e.preventDefault();
            showError(err);
            input.focus();
        }
    });

    // ── Initialisation si old('montant') présent ──────────────────────────
    if (input.value) {
        const err = validate(input.value);
        if (err) {
            showError(err);
        } else {
            updatePreview(input.value);
        }
    }
})();
</script>
@endpush
