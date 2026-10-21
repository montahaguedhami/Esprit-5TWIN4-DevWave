@extends('layouts.frontoffice')

@php
    $title = 'Résultat de la simulation — AquaSecure';

    $config = match($paiement->statut) {
        'confirme'   => [
            'icon'   => 'badge-check',
            'ring'   => 'ring-teal-400/30',
            'iconBg' => 'bg-teal-500/15',
            'iconFg' => 'text-teal-400',
            'title'  => 'Simulation réussie',
            'msg'    => 'Votre contribution a été enregistrée. Merci pour votre soutien au réseau d\'eau.',
            'color'  => 'text-teal-300',
            'border' => 'border-teal-400/20',
        ],
        'echoue'     => [
            'icon'   => 'x-circle',
            'ring'   => 'ring-red-400/30',
            'iconBg' => 'bg-red-500/15',
            'iconFg' => 'text-red-400',
            'title'  => 'Simulation échouée',
            'msg'    => 'La simulation n\'a pas abouti. Aucun montant n\'a été comptabilisé : vous pouvez réessayer.',
            'color'  => 'text-red-300',
            'border' => 'border-red-400/20',
        ],
        'annule'     => [
            'icon'   => 'ban',
            'ring'   => 'ring-slate-400/30',
            'iconBg' => 'bg-slate-500/15',
            'iconFg' => 'text-slate-400',
            'title'  => 'Simulation annulée',
            'msg'    => 'La simulation a été annulée. Aucun montant n\'a été comptabilisé.',
            'color'  => 'text-slate-300',
            'border' => 'border-slate-400/20',
        ],
        default      => [
            'icon'   => 'clock',
            'ring'   => 'ring-amber-400/30',
            'iconBg' => 'bg-amber-500/15',
            'iconFg' => 'text-amber-400',
            'title'  => 'Simulation en attente',
            'msg'    => 'Cette opération n\'a pas encore été traitée.',
            'color'  => 'text-amber-300',
            'border' => 'border-amber-400/20',
        ],
    };
@endphp

@section('frontoffice-content')
<div class="flex items-start justify-center min-h-[60vh] pt-12 animate-fade-in-up">
<div class="max-w-md w-full space-y-6">

    {{-- ── Avertissement obligatoire ── --}}
    <div class="rounded-xl px-4 py-3 bg-amber-500/10 border border-amber-400/25 flex items-start gap-3">
        <i data-lucide="info" class="w-4 h-4 text-amber-300 flex-shrink-0 mt-0.5"></i>
        <p class="text-xs text-amber-200/85 leading-relaxed">
            <strong class="text-amber-200">Simulation — aucune transaction financière réelle.</strong>
            Aucun argent n'a été transféré et aucune banque n'a été contactée.
        </p>
    </div>

    {{-- ── Résultat principal ── --}}
    <div class="glass rounded-2xl p-8 text-center ring-1 {{ $config['ring'] }} {{ $config['border'] }}">
        <div class="w-20 h-20 {{ $config['iconBg'] }} rounded-2xl flex items-center justify-center mx-auto mb-5">
            <i data-lucide="{{ $config['icon'] }}" class="w-10 h-10 {{ $config['iconFg'] }}"></i>
        </div>
        <h1 class="text-2xl font-display font-bold text-white mb-3">{{ $config['title'] }}</h1>
        <p class="text-cyan-100/60 text-sm leading-relaxed {{ $config['color'] }}">{{ $config['msg'] }}</p>
    </div>

    {{-- ── Détail de l'opération ── --}}
    <div class="glass rounded-2xl p-5 space-y-3">
        <h3 class="font-semibold text-white text-sm">Détails de l'opération</h3>
        <div class="space-y-2 text-sm">
            @if($paiement->demande?->projet)
            <div class="flex justify-between gap-4">
                <span class="text-cyan-100/50">Projet</span>
                <span class="text-white font-medium text-right">{{ $paiement->demande->projet->nom }}</span>
            </div>
            @endif
            <div class="flex justify-between">
                <span class="text-cyan-100/50">Montant</span>
                <span class="text-white font-bold">{{ number_format((float)$paiement->montant, 2, ',', ' ') }} TND</span>
            </div>
            <div class="flex justify-between">
                <span class="text-cyan-100/50">Devise</span>
                <span class="text-white">{{ $paiement->devise }}</span>
            </div>
            <div class="flex justify-between gap-4">
                <span class="text-cyan-100/50">Référence</span>
                <span class="text-cyan-300 font-mono text-xs">{{ $paiement->reference_interne }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-cyan-100/50">Créée le</span>
                <span class="text-white">{{ $paiement->created_at->format('d/m/Y à H:i') }}</span>
            </div>
            @if($paiement->confirmed_at)
            <div class="flex justify-between">
                <span class="text-cyan-100/50">Traitée le</span>
                <span class="text-teal-300">{{ $paiement->confirmed_at->format('d/m/Y à H:i') }}</span>
            </div>
            @endif
            @if($paiement->motif)
            <div class="pt-2 border-t border-white/5">
                <p class="text-xs text-cyan-100/45">{{ $paiement->motif }}</p>
            </div>
            @endif
        </div>
    </div>

    {{-- ── Actions ── --}}
    <div class="flex flex-col sm:flex-row gap-3">
        @if($paiement->estConfirme())
        <a href="{{ route('citizen.demandes.paiement.recu', $paiement) }}"
           class="flex-1 py-2.5 rounded-xl text-center text-sm font-semibold bg-gradient-to-r from-teal-500 to-cyan-500 text-white hover:opacity-90 transition-opacity inline-flex items-center justify-center gap-2">
            <i data-lucide="receipt" class="w-4 h-4"></i>
            Voir le reçu
        </a>
        @else
        <a href="{{ route('citizen.demandes.show', $paiement->demande_id) }}"
           class="flex-1 py-2.5 rounded-xl text-center text-sm font-semibold bg-gradient-to-r from-cyan-500 to-blue-600 text-white hover:opacity-90 transition-opacity">
            Réessayer
        </a>
        @endif
        <a href="{{ route('citizen.demandes.index') }}"
           class="flex-1 py-2.5 rounded-xl text-center text-sm font-semibold glass text-cyan-300 hover:text-white transition-colors">
            Mes demandes
        </a>
    </div>

</div>
</div>
@endsection
