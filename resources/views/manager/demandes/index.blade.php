@extends('layouts.manager')

@php
    $title = 'Demandes de financement — AquaSecure';

    $statutStyle = [
        'en_attente' => ['bg' => 'bg-amber-500/15',  'text' => 'text-amber-300',  'icon' => 'clock',        'label' => 'En attente'],
        'acceptee'   => ['bg' => 'bg-cyan-500/15',   'text' => 'text-cyan-300',   'icon' => 'check-circle', 'label' => 'Acceptée'],
        'refusee'    => ['bg' => 'bg-red-500/15',    'text' => 'text-red-300',    'icon' => 'x-circle',     'label' => 'Refusée'],
        'payee'      => ['bg' => 'bg-teal-500/15',   'text' => 'text-teal-300',   'icon' => 'badge-check',  'label' => 'Payée'],
    ];

    $compteurTotal = array_sum($compteurs->toArray());
@endphp

@section('manager-content')
<div class="space-y-6 animate-fade-in-up">

    {{-- ── En-tête ── --}}
    <div>
        <h1 class="text-2xl sm:text-3xl font-display font-bold text-white">Demandes de financement</h1>
        <p class="text-cyan-100/55 text-sm mt-0.5">Examinez et validez les contributions citoyennes aux projets</p>
    </div>

    {{-- ── Compteurs par statut ── --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        @foreach(['en_attente' => 'En attente', 'acceptee' => 'Acceptées', 'refusee' => 'Refusées', 'payee' => 'Payées'] as $slug => $lbl)
        @php $ss = $statutStyle[$slug]; $n = $compteurs->get($slug, 0); @endphp
        <a href="{{ route('manager.demandes.index', ['statut' => $slug]) }}"
           class="glass rounded-2xl p-4 flex flex-col gap-1 hover:border-white/20 transition-colors {{ $filtreStatut === $slug ? 'ring-1 ring-cyan-400/40' : '' }}">
            <span class="text-2xl font-bold text-white">{{ $n }}</span>
            <span class="inline-flex items-center gap-1 text-xs font-semibold {{ $ss['text'] }}">
                <i data-lucide="{{ $ss['icon'] }}" class="w-3 h-3"></i>{{ $lbl }}
            </span>
        </a>
        @endforeach
    </div>

    {{-- ── Filtres ── --}}
    <div class="glass rounded-2xl p-4">
        <form method="GET" action="{{ route('manager.demandes.index') }}" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[180px]">
                <label class="block text-xs text-cyan-100/50 mb-1.5 font-medium">Statut</label>
                <select name="statut"
                        class="w-full px-3 py-2 rounded-xl bg-white/5 border border-white/10 text-white text-sm focus:border-cyan-400/50 focus:outline-none">
                    <option value="">Tous les statuts</option>
                    @foreach(['en_attente' => 'En attente', 'acceptee' => 'Acceptée', 'refusee' => 'Refusée', 'payee' => 'Payée'] as $s => $l)
                    <option value="{{ $s }}" {{ $filtreStatut === $s ? 'selected' : '' }}>{{ $l }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex-1 min-w-[180px]">
                <label class="block text-xs text-cyan-100/50 mb-1.5 font-medium">Projet</label>
                <select name="projet_id"
                        class="w-full px-3 py-2 rounded-xl bg-white/5 border border-white/10 text-white text-sm focus:border-cyan-400/50 focus:outline-none">
                    <option value="">Tous les projets</option>
                    @foreach($projets as $p)
                    <option value="{{ $p->id }}" {{ request('projet_id') == $p->id ? 'selected' : '' }}>{{ $p->nom }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit"
                        class="px-4 py-2 rounded-xl bg-cyan-500/15 text-cyan-300 hover:text-white border border-cyan-400/25 text-sm font-semibold transition-colors">
                    Filtrer
                </button>
                @if($filtreStatut || request('projet_id'))
                <a href="{{ route('manager.demandes.index') }}"
                   class="px-4 py-2 rounded-xl glass text-cyan-100/60 hover:text-white text-sm font-semibold transition-colors">
                    Réinitialiser
                </a>
                @endif
            </div>
        </form>
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

    {{-- ── Table ── --}}
    @if($demandes->count() > 0)
    <div class="glass rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-white/[.03] border-b border-white/10">
                    <tr>
                        <th class="px-5 py-4 text-left text-xs font-semibold text-cyan-100/60 uppercase tracking-wider">Citoyen</th>
                        <th class="px-5 py-4 text-left text-xs font-semibold text-cyan-100/60 uppercase tracking-wider">Projet</th>
                        <th class="px-5 py-4 text-left text-xs font-semibold text-cyan-100/60 uppercase tracking-wider">Montant</th>
                        <th class="px-5 py-4 text-left text-xs font-semibold text-cyan-100/60 uppercase tracking-wider">Statut</th>
                        <th class="px-5 py-4 text-left text-xs font-semibold text-cyan-100/60 uppercase tracking-wider">Date</th>
                        <th class="px-5 py-4 text-right text-xs font-semibold text-cyan-100/60 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @foreach($demandes as $demande)
                    @php $ss = $statutStyle[$demande->statut] ?? $statutStyle['en_attente']; @endphp
                    <tr class="hover:bg-white/[.02] transition-colors">
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-cyan-500 to-blue-600 flex items-center justify-center text-white font-bold text-xs flex-shrink-0">
                                    {{ strtoupper(mb_substr($demande->citoyen?->name ?? '?', 0, 1)) }}
                                </div>
                                <div>
                                    <p class="text-white text-sm font-semibold">{{ $demande->citoyen?->name ?? '—' }}</p>
                                    <p class="text-cyan-100/40 text-xs">{{ $demande->citoyen?->email ?? '' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-4">
                            <p class="text-white text-sm font-medium">{{ $demande->projet?->nom ?? '—' }}</p>
                        </td>
                        <td class="px-5 py-4">
                            <span class="text-white font-bold text-sm">{{ number_format((float)$demande->montant, 2, ',', ' ') }}</span>
                            <span class="text-cyan-100/50 text-xs ml-1">TND</span>
                        </td>
                        <td class="px-5 py-4">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold {{ $ss['bg'] }} {{ $ss['text'] }}">
                                <i data-lucide="{{ $ss['icon'] }}" class="w-3 h-3"></i>{{ $ss['label'] }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-cyan-100/60 text-sm">
                            {{ $demande->created_at->format('d/m/Y') }}
                        </td>
                        <td class="px-5 py-4 text-right">
                            <a href="{{ route('manager.demandes.show', $demande) }}"
                               class="glass px-3 py-1.5 rounded-lg text-xs text-cyan-300 hover:text-white transition-colors inline-flex items-center gap-1.5">
                                <i data-lucide="eye" class="w-3 h-3"></i>Voir
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @if($demandes->hasPages())
    <div class="flex justify-center">{{ $demandes->links() }}</div>
    @endif

    @else
    <div class="glass rounded-2xl p-12 text-center">
        <div class="w-16 h-16 rounded-2xl bg-cyan-500/10 flex items-center justify-center mx-auto mb-4">
            <i data-lucide="inbox" class="w-8 h-8 text-cyan-400"></i>
        </div>
        <h3 class="text-lg font-semibold text-white mb-2">Aucune demande</h3>
        <p class="text-cyan-100/50 text-sm">
            {{ $filtreStatut ? 'Aucune demande avec ce statut.' : 'Aucune demande de financement citoyen pour le moment.' }}
        </p>
    </div>
    @endif

</div>
@endsection
