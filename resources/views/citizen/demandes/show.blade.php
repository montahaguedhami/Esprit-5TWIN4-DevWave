@extends('layouts.frontoffice')

@php
    $title = 'Demande de financement — AquaSecure';

    $statutConfig = [
        'en_attente' => ['bg' => 'bg-amber-500/15', 'text' => 'text-amber-300', 'icon' => 'clock',        'label' => 'En attente'],
        'acceptee'   => ['bg' => 'bg-cyan-500/15',  'text' => 'text-cyan-300',  'icon' => 'check-circle', 'label' => 'Acceptée'],
        'refusee'    => ['bg' => 'bg-red-500/15',   'text' => 'text-red-300',   'icon' => 'x-circle',     'label' => 'Refusée'],
        'payee'      => ['bg' => 'bg-teal-500/15',  'text' => 'text-teal-300',  'icon' => 'badge-check',  'label' => 'Payée'],
    ];

    $sc = $statutConfig[$demande->statut] ?? $statutConfig['en_attente'];
@endphp

@section('frontoffice-content')
<div class="space-y-6 animate-fade-in-up max-w-2xl mx-auto">

    {{-- ── Fil d'Ariane ── --}}
    <div>
        <a href="{{ route('citizen.demandes.index') }}"
           class="text-cyan-400 hover:text-cyan-300 text-sm font-semibold flex items-center gap-1 mb-3">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            Mes demandes
        </a>
        <h1 class="text-2xl sm:text-3xl font-display font-bold text-white">Détail de ma demande</h1>
    </div>

    {{-- ── Flash ── --}}
    @if(session('success'))
    <div class="glass rounded-xl px-5 py-3 border border-teal-400/30 text-teal-300 text-sm flex items-center gap-2">
        <i data-lucide="check-circle" class="w-4 h-4 flex-shrink-0"></i>
        {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="glass rounded-xl px-5 py-3 border border-red-400/30 text-red-300 text-sm flex items-center gap-2">
        <i data-lucide="alert-circle" class="w-4 h-4 flex-shrink-0"></i>
        {{ session('error') }}
    </div>
    @endif

    {{-- ── Statut principal ── --}}
    <div class="glass rounded-2xl p-6">
        <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
            <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-sm font-semibold {{ $sc['bg'] }} {{ $sc['text'] }}">
                <i data-lucide="{{ $sc['icon'] }}" class="w-4 h-4"></i>
                {{ $sc['label'] }}
            </span>
            <span class="text-cyan-100/40 text-xs">Soumise le {{ $demande->created_at->format('d/m/Y à H:i') }}</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="glass rounded-xl p-4">
                <p class="text-xs text-cyan-100/50 mb-1">Projet</p>
                <p class="font-semibold text-white text-sm">{{ $demande->projet->nom ?? '—' }}</p>
            </div>
            <div class="glass rounded-xl p-4">
                <p class="text-xs text-cyan-100/50 mb-1">Montant demandé</p>
                <p class="font-bold text-white text-lg">{{ number_format((float)$demande->montant, 2, ',', ' ') }} <span class="text-sm font-normal text-cyan-100/60">TND</span></p>
            </div>
        </div>
    </div>

    {{-- ── Décision du manager ── --}}
    @if($demande->statut !== 'en_attente')
    <div class="glass rounded-2xl p-6 space-y-3">
        <h3 class="font-semibold text-white flex items-center gap-2">
            <i data-lucide="user-check" class="w-4 h-4 text-cyan-400"></i>
            Décision du gestionnaire
        </h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <p class="text-xs text-cyan-100/50 mb-1">Décidé le</p>
                <p class="text-sm text-white">{{ $demande->decided_at?->format('d/m/Y à H:i') ?? '—' }}</p>
            </div>
            @if($demande->decideur)
            <div>
                <p class="text-xs text-cyan-100/50 mb-1">Gestionnaire</p>
                <p class="text-sm text-white">{{ $demande->decideur->name }}</p>
            </div>
            @endif
        </div>
        @if($demande->motif_refus)
        <div class="rounded-xl px-4 py-3 bg-red-500/10 border border-red-400/20">
            <p class="text-xs text-red-400/70 mb-1 font-semibold uppercase tracking-wider">Motif du refus</p>
            <p class="text-sm text-red-300">{{ $demande->motif_refus }}</p>
        </div>
        @endif
    </div>
    @endif

    {{-- ── Avertissement simulation ── --}}
    <div class="rounded-xl px-4 py-3 bg-amber-500/10 border border-amber-400/25 flex items-start gap-3">
        <i data-lucide="info" class="w-4 h-4 text-amber-300 flex-shrink-0 mt-0.5"></i>
        <p class="text-xs text-amber-200/85 leading-relaxed">
            <strong class="text-amber-200">Paiement simulé à des fins académiques.</strong>
            Aucun argent réel n'est transféré, aucune donnée bancaire n'est demandée et
            aucune passerelle de paiement externe n'est utilisée.
        </p>
    </div>

    {{-- ── Contribuer maintenant ── --}}
    @if($demande->estPayable())
    <div class="glass rounded-2xl p-6 border border-teal-400/20">
        <div class="flex items-start gap-4">
            <div class="w-12 h-12 rounded-xl bg-teal-500/15 flex items-center justify-center flex-shrink-0">
                <i data-lucide="heart-handshake" class="w-6 h-6 text-teal-400"></i>
            </div>
            <div class="flex-1">
                <h3 class="font-semibold text-white mb-1">Votre demande a été acceptée</h3>
                <p class="text-cyan-100/55 text-sm mb-4">
                    Vous pouvez maintenant effectuer une contribution simulée de
                    <strong class="text-white">{{ number_format((float)$demande->montant, 2, ',', ' ') }} TND</strong>
                    pour ce projet.
                </p>
                <a href="{{ route('citizen.demandes.recap', $demande) }}"
                   class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-gradient-to-r from-teal-500 to-cyan-500 text-white font-semibold text-sm hover:opacity-90 active:scale-[.98] transition-all">
                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    Contribuer {{ number_format((float)$demande->montant, 2, ',', ' ') }} TND
                </a>
            </div>
        </div>
    </div>
    @endif

    {{-- ── Opérations de paiement simulées ── --}}
    @if($demande->paiements->count() > 0)
    <div class="glass rounded-2xl p-6">
        <h3 class="font-semibold text-white mb-4 flex items-center gap-2">
            <i data-lucide="receipt" class="w-4 h-4 text-cyan-400"></i>
            Historique des simulations
        </h3>
        <div class="space-y-2">
            @foreach($demande->paiements as $p)
            @php
                $ps = [
                    'en_attente' => ['bg' => 'bg-amber-500/15', 'text' => 'text-amber-300', 'label' => 'En attente'],
                    'confirme'   => ['bg' => 'bg-teal-500/15',  'text' => 'text-teal-300',  'label' => 'Confirmé'],
                    'echoue'     => ['bg' => 'bg-red-500/15',   'text' => 'text-red-300',   'label' => 'Échoué'],
                    'annule'     => ['bg' => 'bg-slate-500/15', 'text' => 'text-slate-300', 'label' => 'Annulé'],
                ];
                $psc = $ps[$p->statut] ?? $ps['en_attente'];
            @endphp
            <div class="glass rounded-xl px-4 py-3 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <p class="text-xs text-cyan-100/50">Réf. {{ $p->reference_interne }}</p>
                    <p class="text-sm font-semibold text-white">{{ number_format((float)$p->montant, 2, ',', ' ') }} TND</p>
                    @if($p->motif)
                    <p class="text-xs text-cyan-100/45 mt-0.5">{{ $p->motif }}</p>
                    @endif
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('citizen.demandes.paiement.resultat', $p) }}"
                       class="text-xs text-cyan-300 hover:text-cyan-200 font-semibold">Résultat</a>
                    @if($p->estConfirme())
                    <a href="{{ route('citizen.demandes.paiement.recu', $p) }}"
                       class="text-xs text-teal-300 hover:text-teal-200 font-semibold">Reçu</a>
                    @endif
                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $psc['bg'] }} {{ $psc['text'] }}">
                        {{ $psc['label'] }}
                    </span>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

</div>
@endsection
