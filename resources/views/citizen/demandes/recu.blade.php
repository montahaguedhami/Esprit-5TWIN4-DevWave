@extends('layouts.frontoffice')

@push('styles')
<style>
    @media print {
        .fo-nav, .no-print { display: none !important; }
        body { background: #fff !important; }
        .glass { background: #fff !important; border: 1px solid #d1d5db !important; box-shadow: none !important; }
        .text-white, .text-cyan-300, .text-teal-300 { color: #111827 !important; }
        .text-cyan-100\/50, .text-cyan-100\/55, .text-cyan-100\/60, .text-cyan-100\/70 { color: #4b5563 !important; }
    }
</style>
@endpush

@php
    $title = 'Reçu de contribution — AquaSecure';
    $ref   = sprintf('%s', $paiement->reference_interne);
@endphp

@section('frontoffice-content')
<div class="max-w-2xl mx-auto space-y-6 animate-fade-in-up">

    <div class="no-print">
        <a href="{{ route('citizen.demandes.paiement.resultat', $paiement) }}"
           class="text-cyan-400 hover:text-cyan-300 text-sm font-semibold flex items-center gap-1 mb-3">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            Résultat de la simulation
        </a>
        <h1 class="text-2xl sm:text-3xl font-display font-bold text-white">Reçu de contribution</h1>
    </div>

    {{-- ── Mention obligatoire, non négociable ── --}}
    <div class="rounded-xl px-5 py-4 bg-amber-500/10 border border-amber-400/30 flex items-start gap-3">
        <i data-lucide="triangle-alert" class="w-5 h-5 text-amber-300 flex-shrink-0 mt-0.5"></i>
        <div>
            <p class="text-sm font-bold text-amber-200">
                Simulation — aucune transaction financière réelle
            </p>
            <p class="text-xs text-amber-200/75 mt-1 leading-relaxed">
                Ce document est une démonstration académique. Il ne constitue ni une preuve
                de paiement, ni un justificatif comptable, ni une confirmation bancaire.
                Aucun argent n'a été débité ou transféré et aucune passerelle de paiement
                externe n'a été contactée.
            </p>
        </div>
    </div>

    {{-- ── Reçu ── --}}
    <div class="glass rounded-2xl p-6 sm:p-8">
        <div class="flex flex-wrap items-start justify-between gap-4 pb-5 mb-5 border-b border-white/10">
            <div>
                <p class="text-lg font-display font-bold text-white">AquaSecure</p>
                <p class="text-xs text-cyan-100/50 mt-0.5">Reçu de contribution simulée</p>
            </div>
            <div class="text-right">
                <p class="text-xs text-cyan-100/50">Référence</p>
                <p class="text-cyan-300 font-mono text-sm font-semibold">{{ $ref }}</p>
            </div>
        </div>

        <div class="space-y-3.5 text-sm">
            <div class="flex justify-between gap-4">
                <span class="text-cyan-100/50">Bénéficiaire</span>
                <span class="text-white font-medium text-right">{{ $paiement->citoyen?->name ?? '—' }}</span>
            </div>
            <div class="flex justify-between gap-4">
                <span class="text-cyan-100/50">E-mail</span>
                <span class="text-cyan-100/80 text-right">{{ $paiement->citoyen?->email ?? '—' }}</span>
            </div>

            <div class="pt-3 border-t border-white/5">
                <p class="text-xs text-cyan-100/50 mb-2">Projet financé</p>
                <p class="text-white font-semibold">{{ $paiement->demande?->projet?->nom ?? '—' }}</p>
                @if($paiement->demande?->projet?->description)
                <p class="text-cyan-100/45 text-xs mt-1 leading-relaxed">
                    {{ \Illuminate\Support\Str::limit($paiement->demande->projet->description, 180) }}
                </p>
                @endif
            </div>

            <div class="pt-3 border-t border-white/5 flex justify-between items-end gap-4">
                <span class="text-cyan-100/50">Montant de la contribution</span>
                <span class="text-3xl font-display font-bold text-white">
                    {{ number_format((float)$paiement->montant, 2, ',', ' ') }}
                    <span class="text-base font-normal text-cyan-100/60">{{ $paiement->devise }}</span>
                </span>
            </div>

            <div class="pt-3 mt-1 border-t border-white/5 space-y-2.5">
                <div class="flex justify-between">
                    <span class="text-cyan-100/50">Statut</span>
                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $paiement->estConfirme() ? 'bg-teal-500/15 text-teal-300' : 'bg-red-500/15 text-red-300' }}">
                        {{ $paiement->libelleStatut() }}
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-cyan-100/50">Demande liée</span>
                    <span class="text-white">n° {{ $paiement->demande_id }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-cyan-100/50">Demandée le</span>
                    <span class="text-white">{{ $paiement->demande?->created_at?->format('d/m/Y à H:i') ?? '—' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-cyan-100/50">Décidée par</span>
                    <span class="text-white">{{ $paiement->demande?->decideur?->name ?? '—' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-cyan-100/50">Traitée le</span>
                    <span class="text-white">{{ ($paiement->confirmed_at ?? $paiement->created_at)->format('d/m/Y à H:i') }}</span>
                </div>
                @if($paiement->motif)
                <div class="flex justify-between gap-4">
                    <span class="text-cyan-100/50">Observation</span>
                    <span class="text-cyan-100/70 text-right">{{ $paiement->motif }}</span>
                </div>
                @endif
            </div>
        </div>

        <p class="text-[11px] text-cyan-100/35 leading-relaxed mt-7 pt-5 border-t border-white/5">
            Document généré automatiquement par AquaSecure le {{ now()->format('d/m/Y à H:i') }}.
            Ce reçu atteste uniquement de l'enregistrement d'une <strong>contribution simulée</strong>
            dans le cadre d'un projet académique. Il ne vaut pas preuve de réception de fonds.
        </p>
    </div>

    {{-- ── Actions : le citoyen choisit entre imprimer et télécharger ── --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 no-print">
        {{-- 1. Impression via le navigateur (imprimante ou « Enregistrer en PDF ») --}}
        <button type="button" onclick="window.print()"
                class="py-2.5 rounded-xl text-sm font-semibold bg-gradient-to-r from-teal-500 to-cyan-500 text-white hover:opacity-90 transition-opacity inline-flex items-center justify-center gap-2">
            <i data-lucide="printer" class="w-4 h-4"></i>
            Imprimer
        </button>

        {{-- 2. Fichier PDF généré par le serveur, sans passer par l'impression --}}
        <a href="{{ route('citizen.demandes.paiement.recu.pdf', $paiement) }}"
           download
           class="py-2.5 rounded-xl text-sm font-semibold text-center glass text-cyan-300 hover:text-white border border-white/10 hover:border-cyan-400/40 transition-colors inline-flex items-center justify-center gap-2">
            <i data-lucide="download" class="w-4 h-4"></i>
            Télécharger le PDF
        </a>
    </div>

    <div class="flex flex-col sm:flex-row gap-3 no-print">
        <a href="{{ route('citizen.demandes.index') }}"
           class="flex-1 py-2.5 rounded-xl text-center text-sm font-semibold glass text-cyan-300 hover:text-white border border-white/10 transition-colors inline-flex items-center justify-center gap-2">
            <i data-lucide="list-checks" class="w-4 h-4"></i>
            Mes demandes
        </a>
        <a href="{{ route('citizen.demandes.paiement.resultat', $paiement) }}"
           class="flex-1 py-2.5 rounded-xl text-center text-sm font-semibold glass text-cyan-300 hover:text-white border border-white/10 transition-colors inline-flex items-center justify-center gap-2">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            Résultat de la simulation
        </a>
    </div>

    <p class="no-print text-center text-xs text-cyan-100/40">
        « Imprimer » ouvre la fenêtre d'impression du navigateur (où vous pouvez aussi choisir
        « Enregistrer au format PDF »). « Télécharger le PDF » enregistre directement le fichier,
        sans fenêtre d'impression.
    </p>

</div>
@endsection
