@extends('layouts.frontoffice')

@php
    $title = 'Récapitulatif de la contribution — AquaSecure';

    $sc = $demande->projet;
@endphp

@section('frontoffice-content')
<div class="max-w-2xl mx-auto space-y-6 animate-fade-in-up">

    <div>
        <a href="{{ route('citizen.demandes.show', $demande) }}"
           class="text-cyan-400 hover:text-cyan-300 text-sm font-semibold flex items-center gap-1 mb-3">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            Ma demande
        </a>
        <h1 class="text-2xl sm:text-3xl font-display font-bold text-white">Récapitulatif de la contribution</h1>
        <p class="text-cyan-100/55 text-sm mt-2">Vérifiez les informations ci-dessous avant de lancer la simulation.</p>
    </div>

    {{-- ── Avertissement obligatoire ── --}}
    <div class="rounded-xl px-4 py-3 bg-amber-500/10 border border-amber-400/25 flex items-start gap-3">
        <i data-lucide="info" class="w-4 h-4 text-amber-300 flex-shrink-0 mt-0.5"></i>
        <p class="text-xs text-amber-200/85 leading-relaxed">
            <strong class="text-amber-200">Paiement simulé à des fins académiques.</strong>
            Aucun argent réel n'est transféré. Aucune donnée bancaire ni carte bancaire
            n'est demandée, et aucune passerelle de paiement externe n'est utilisée.
        </p>
    </div>

    {{-- ── Récapitulatif ── --}}
    <div class="glass rounded-2xl p-6">
        <h3 class="font-semibold text-white mb-4 flex items-center gap-2">
            <i data-lucide="clipboard-list" class="w-4 h-4 text-cyan-400"></i>
            Détail de la contribution simulée
        </h3>

        <div class="space-y-3 text-sm">
            <div class="flex justify-between gap-4">
                <span class="text-cyan-100/50">Projet</span>
                <span class="text-white font-medium text-right">{{ $sc->nom ?? '—' }}</span>
            </div>
            <div class="flex justify-between gap-4">
                <span class="text-cyan-100/50">Description</span>
                <span class="text-cyan-100/70 text-right max-w-[60%]">{{ \Illuminate\Support\Str::limit($sc->description ?? '—', 120) }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-cyan-100/50">Budget cible</span>
                <span class="text-white">{{ number_format((float)($sc->budget ?? 0), 2, ',', ' ') }} TND</span>
            </div>
            <div class="flex justify-between">
                <span class="text-cyan-100/50">Déjà collecté</span>
                <span class="text-teal-300">{{ number_format((float)($sc->total_finance ?? 0), 2, ',', ' ') }} TND</span>
            </div>
            <div class="flex justify-between">
                <span class="text-cyan-100/50">Restant à collecter</span>
                <span class="text-cyan-200">{{ number_format((float)($sc->budget_restant ?? 0), 2, ',', ' ') }} TND</span>
            </div>

            <div class="pt-3 mt-1 border-t border-white/5 flex justify-between items-center">
                <span class="text-cyan-100/50">Contribution proposée</span>
                <span class="text-2xl font-display font-bold text-white">
                    {{ number_format((float)$demande->montant, 2, ',', ' ') }}
                    <span class="text-sm font-normal text-cyan-100/60">TND</span>
                </span>
            </div>
        </div>
    </div>

    {{-- ── Choix du scénario — développement uniquement ── --}}
    @if(count($scenarios) > 0)
    <div class="glass rounded-2xl p-6 border border-amber-400/20">
        <div class="flex items-start gap-3 mb-4">
            <i data-lucide="flask-conical" class="w-4 h-4 text-amber-300 flex-shrink-0 mt-0.5"></i>
            <div>
                <h3 class="font-semibold text-white text-sm">Mode démonstration</h3>
                <p class="text-amber-200/70 text-xs mt-1 leading-relaxed">
                    Ce bloc n'apparaît qu'en environnement de développement. Il permet de
                    choisir l'issue de la simulation afin de présenter les différents cas.
                    En production, ce choix n'est pas proposé et le résultat est toujours un succès.
                </p>
            </div>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
            @foreach($scenarios as $s)
            <label class="cursor-pointer">
                <input type="radio" name="scenario" value="{{ $s }}"
                       class="peer sr-only"
                       {{ $loop->first ? 'checked' : '' }}>
                <span class="block text-center px-3 py-2 rounded-xl text-xs font-semibold glass
                             text-cyan-100/70 border border-white/10
                             peer-checked:bg-cyan-500/20 peer-checked:border-cyan-400/50 peer-checked:text-white
                             transition-all">
                    {{ $libelles[$s] ?? $s }}
                </span>
            </label>
            @endforeach
        </div>
    </div>
    @endif

    {{-- ── Actions ── --}}
    <div class="glass rounded-2xl p-6">
        <p class="text-cyan-100/55 text-sm mb-4">
            Une fois confirmée, la simulation sera traitée immédiatement et son résultat
            vous sera affiché. Vous pourrez consulter le reçu associé si elle réussit.
        </p>

        <div class="flex flex-col sm:flex-row gap-3">
            <form method="POST" action="{{ route('citizen.demandes.confirmer', $demande) }}" class="flex-1">
                @csrf
                <button type="submit"
                        class="w-full py-2.5 rounded-xl text-sm font-semibold bg-gradient-to-r from-teal-500 to-cyan-500 text-white hover:opacity-90 active:scale-[.98] transition-all inline-flex items-center justify-center gap-2">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    Confirmer la simulation
                </button>
            </form>

            <form method="POST" action="{{ route('citizen.demandes.annuler', $demande) }}" class="flex-1">
                @csrf
                <button type="submit"
                        class="w-full py-2.5 rounded-xl text-sm font-semibold glass text-cyan-300 hover:text-white border border-white/10 transition-all">
                    Annuler
                </button>
            </form>
        </div>
    </div>

</div>
@endsection
