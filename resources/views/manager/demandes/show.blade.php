@extends('layouts.manager')

@php
    $title = 'Demande #' . $demande->id . ' — AquaSecure';

    $statutStyle = [
        'en_attente' => ['bg' => 'bg-amber-500/15',  'text' => 'text-amber-300',  'icon' => 'clock',        'label' => 'En attente'],
        'acceptee'   => ['bg' => 'bg-cyan-500/15',   'text' => 'text-cyan-300',   'icon' => 'check-circle', 'label' => 'Acceptée'],
        'refusee'    => ['bg' => 'bg-red-500/15',    'text' => 'text-red-300',    'icon' => 'x-circle',     'label' => 'Refusée'],
        'payee'      => ['bg' => 'bg-teal-500/15',   'text' => 'text-teal-300',   'icon' => 'badge-check',  'label' => 'Payée'],
    ];

    $ss = $statutStyle[$demande->statut] ?? $statutStyle['en_attente'];
@endphp

@section('manager-content')
<div class="space-y-6 animate-fade-in-up max-w-3xl">

    {{-- ── Fil d'Ariane ── --}}
    <div>
        <a href="{{ route('manager.demandes.index') }}"
           class="text-cyan-400 hover:text-cyan-300 text-sm font-semibold flex items-center gap-1 mb-3">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            Toutes les demandes
        </a>
        <div class="flex flex-wrap items-center gap-3">
            <h1 class="text-2xl sm:text-3xl font-display font-bold text-white">Demande #{{ $demande->id }}</h1>
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-sm font-semibold {{ $ss['bg'] }} {{ $ss['text'] }}">
                <i data-lucide="{{ $ss['icon'] }}" class="w-3.5 h-3.5"></i>{{ $ss['label'] }}
            </span>
        </div>
    </div>

    {{-- ── Flash ── --}}
    @if(session('success'))
    <div class="glass rounded-xl px-5 py-3 border border-teal-400/30 text-teal-300 text-sm flex items-center gap-2">
        <i data-lucide="check-circle" class="w-4 h-4 flex-shrink-0"></i>{{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="glass rounded-xl px-5 py-3 border border-red-400/30 text-red-300 text-sm flex items-center gap-2">
        <i data-lucide="alert-circle" class="w-4 h-4 flex-shrink-0"></i>{{ session('error') }}
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        {{-- ── Colonne principale (2/3) ── --}}
        <div class="lg:col-span-2 space-y-5">

            {{-- Citoyen ── --}}
            <div class="glass rounded-2xl p-5 space-y-3">
                <h3 class="font-semibold text-white flex items-center gap-2 text-sm">
                    <i data-lucide="user" class="w-4 h-4 text-cyan-400"></i>
                    Citoyen demandeur
                </h3>
                @if($demande->citoyen)
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-full bg-gradient-to-br from-cyan-500 to-blue-600 flex items-center justify-center text-white font-bold text-lg flex-shrink-0">
                        {{ strtoupper(mb_substr($demande->citoyen->name, 0, 1)) }}
                    </div>
                    <div>
                        <p class="font-semibold text-white">{{ $demande->citoyen->name }}</p>
                        <p class="text-cyan-100/50 text-sm">{{ $demande->citoyen->email }}</p>
                    </div>
                </div>
                @endif
            </div>

            {{-- Projet ── --}}
            <div class="glass rounded-2xl p-5 space-y-3">
                <h3 class="font-semibold text-white flex items-center gap-2 text-sm">
                    <i data-lucide="briefcase" class="w-4 h-4 text-cyan-400"></i>
                    Projet concerné
                </h3>
                @if($demande->projet)
                <div>
                    <a href="{{ route('manager.projets.show', $demande->projet) }}"
                       class="font-semibold text-cyan-300 hover:text-white transition-colors">
                        {{ $demande->projet->nom }}
                    </a>
                    <div class="grid grid-cols-3 gap-3 mt-3">
                        <div class="glass rounded-xl p-3 text-center">
                            <p class="text-xs text-cyan-100/50 mb-0.5">Budget</p>
                            <p class="font-bold text-white text-sm">{{ number_format((float)$demande->projet->budget, 0, ',', ' ') }} TND</p>
                        </div>
                        <div class="glass rounded-xl p-3 text-center">
                            <p class="text-xs text-cyan-100/50 mb-0.5">Financé</p>
                            <p class="font-bold text-teal-300 text-sm">{{ number_format((float)$demande->projet->total_finance, 0, ',', ' ') }} TND</p>
                        </div>
                        <div class="glass rounded-xl p-3 text-center">
                            <p class="text-xs text-cyan-100/50 mb-0.5">Disponible</p>
                            <p class="font-bold text-cyan-300 text-sm">{{ number_format((float)$demande->projet->budget_disponible, 0, ',', ' ') }} TND</p>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            {{-- Paiements ── --}}
            @if($demande->paiements->count() > 0)
            <div class="glass rounded-2xl p-5 space-y-3">
                <h3 class="font-semibold text-white flex items-center gap-2 text-sm">
                    <i data-lucide="receipt" class="w-4 h-4 text-cyan-400"></i>
                    Paiements
                </h3>
                @foreach($demande->paiements as $p)
                @php
                    $ps = ['en_attente' => ['text' => 'text-amber-300', 'label' => 'En attente'], 'confirme' => ['text' => 'text-teal-300', 'label' => 'Confirmé'], 'echoue' => ['text' => 'text-red-300', 'label' => 'Échoué'], 'annule' => ['text' => 'text-slate-300', 'label' => 'Annulé']];
                    $psc = $ps[$p->statut] ?? $ps['en_attente'];
                @endphp
                <div class="flex items-center justify-between glass rounded-xl px-4 py-3 text-sm">
                    <div>
                        <p class="text-xs text-cyan-100/40 font-mono">{{ $p->reference_interne }}</p>
                        <p class="text-white font-bold">{{ number_format((float)$p->montant, 2, ',', ' ') }} TND</p>
                    </div>
                    <div class="text-right">
                        <p class="font-semibold text-xs {{ $psc['text'] }}">{{ $psc['label'] }}</p>
                        @if($p->confirmed_at)
                        <p class="text-cyan-100/40 text-xs">{{ $p->confirmed_at->format('d/m/Y H:i') }}</p>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
            @endif

        </div>

        {{-- ── Colonne latérale (1/3) ── --}}
        <div class="space-y-5">

            {{-- Montant ── --}}
            <div class="glass rounded-2xl p-5 text-center">
                <p class="text-xs text-cyan-100/50 mb-1">Montant demandé</p>
                <p class="text-3xl font-display font-bold text-white">{{ number_format((float)$demande->montant, 0, ',', ' ') }}</p>
                <p class="text-cyan-100/50 text-sm mt-0.5">TND</p>
                <p class="text-xs text-cyan-100/40 mt-2">Soumise le {{ $demande->created_at->format('d/m/Y') }}</p>
            </div>

            {{-- Décision existante ── --}}
            @if($demande->statut !== 'en_attente' && $demande->decideur)
            <div class="glass rounded-2xl p-5 space-y-2">
                <h3 class="font-semibold text-white text-sm flex items-center gap-2">
                    <i data-lucide="user-check" class="w-4 h-4 text-cyan-400"></i>
                    Décision
                </h3>
                <p class="text-white text-sm font-medium">{{ $demande->decideur->name }}</p>
                <p class="text-cyan-100/50 text-xs">{{ $demande->decided_at?->format('d/m/Y à H:i') }}</p>
                @if($demande->motif_refus)
                <div class="rounded-lg px-3 py-2 bg-red-500/10 border border-red-400/20 text-xs text-red-300 mt-2">
                    {{ $demande->motif_refus }}
                </div>
                @endif
            </div>
            @endif

            {{-- ── Actions en attente ── --}}
            @if($demande->estEnAttente())
            <div class="glass rounded-2xl p-5 space-y-3">
                <h3 class="font-semibold text-white text-sm">Décision</h3>

                {{-- Accepter ── --}}
                <form method="POST" action="{{ route('manager.demandes.accepter', $demande) }}">
                    @csrf
                    <button type="submit"
                            onclick="return confirm('Accepter cette demande de {{ number_format((float)$demande->montant, 0, ',', ' ') }} TND ?')"
                            class="w-full py-2.5 rounded-xl bg-gradient-to-r from-teal-500 to-cyan-500 text-white font-semibold text-sm hover:opacity-90 transition-opacity flex items-center justify-center gap-2">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        Accepter
                    </button>
                </form>

                {{-- Refuser ── --}}
                <div>
                    <button
                        type="button"
                        onclick="document.getElementById('refus-panel').classList.toggle('hidden')"
                        class="w-full py-2.5 rounded-xl glass border border-red-400/30 text-red-300 hover:text-white font-semibold text-sm transition-colors flex items-center justify-center gap-2">
                        <i data-lucide="x" class="w-4 h-4"></i>
                        Refuser
                    </button>

                    <div id="refus-panel" class="hidden mt-3">
                        <form method="POST" action="{{ route('manager.demandes.refuser', $demande) }}" class="space-y-3">
                            @csrf
                            @error('motif_refus')
                            <p class="text-red-400 text-xs flex items-center gap-1">
                                <i data-lucide="alert-circle" class="w-3 h-3"></i>{{ $message }}
                            </p>
                            @enderror
                            <textarea
                                name="motif_refus"
                                rows="3"
                                required
                                placeholder="Motif du refus (obligatoire, visible par le citoyen)…"
                                class="w-full px-3 py-2 rounded-xl bg-white/5 border border-red-400/20 text-white placeholder:text-cyan-100/30 text-sm focus:border-red-400/50 focus:outline-none resize-none">{{ old('motif_refus') }}</textarea>
                            <button type="submit"
                                    class="w-full py-2 rounded-xl bg-red-500/20 border border-red-400/30 text-red-300 hover:bg-red-500/30 font-semibold text-sm transition-colors">
                                Confirmer le refus
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @endif

        </div>
    </div>

</div>
@endsection
