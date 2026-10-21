@extends('layouts.frontoffice')

@php
    $title = 'Mes demandes de financement — AquaSecure';

    $statutStyle = [
        'en_attente' => ['bg' => 'bg-amber-500/15',  'text' => 'text-amber-300',  'icon' => 'clock',        'label' => 'En attente'],
        'acceptee'   => ['bg' => 'bg-cyan-500/15',   'text' => 'text-cyan-300',   'icon' => 'check-circle', 'label' => 'Acceptée'],
        'refusee'    => ['bg' => 'bg-red-500/15',    'text' => 'text-red-300',    'icon' => 'x-circle',     'label' => 'Refusée'],
        'payee'      => ['bg' => 'bg-teal-500/15',   'text' => 'text-teal-300',   'icon' => 'badge-check',  'label' => 'Payée'],
    ];
@endphp

@section('frontoffice-content')
<div class="space-y-6 animate-fade-in-up">

    {{-- ── En-tête ── --}}
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-display font-bold text-white">Mes demandes de financement</h1>
            <p class="text-cyan-100/55 text-sm mt-1">Suivez l'état de vos contributions aux projets d'infrastructure</p>
        </div>
        <a href="{{ route('citizen.projets.index') }}"
           class="glass px-4 py-2 rounded-xl text-sm text-cyan-300 hover:text-white font-semibold flex items-center gap-2 transition-all hover:border-cyan-400/40">
            <i data-lucide="plus" class="w-4 h-4"></i>
            Financer un projet
        </a>
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

    {{-- ── Liste ── --}}
    @if($demandes->count() > 0)
    <div class="glass rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-white/[.03] border-b border-white/10">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-cyan-100/60 uppercase tracking-wider">Projet</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-cyan-100/60 uppercase tracking-wider">Montant</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-cyan-100/60 uppercase tracking-wider">Statut</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-cyan-100/60 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-cyan-100/60 uppercase tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @foreach($demandes as $demande)
                    @php $ss = $statutStyle[$demande->statut] ?? $statutStyle['en_attente']; @endphp
                    <tr class="hover:bg-white/[.02] transition-colors">
                        <td class="px-6 py-4">
                            <div class="font-semibold text-white text-sm">{{ $demande->projet->nom ?? '—' }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-white font-bold text-sm">{{ number_format((float)$demande->montant, 2, ',', ' ') }} TND</span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold {{ $ss['bg'] }} {{ $ss['text'] }}">
                                <i data-lucide="{{ $ss['icon'] }}" class="w-3 h-3"></i>
                                {{ $ss['label'] }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-cyan-100/60 text-sm">
                            {{ $demande->created_at->format('d/m/Y') }}
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('citizen.demandes.show', $demande) }}"
                                   class="glass px-3 py-1.5 rounded-lg text-xs text-cyan-300 hover:text-white transition-colors">
                                    Voir
                                </a>
                                @if($demande->estPayable())
                                <a href="{{ route('citizen.demandes.recap', $demande) }}"
                                   class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-teal-500/20 text-teal-300 hover:bg-teal-500/30 hover:text-white transition-colors flex items-center gap-1.5">
                                    <i data-lucide="hand-coins" class="w-3 h-3"></i>
                                    Contribuer
                                </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pagination --}}
    @if($demandes->hasPages())
    <div class="flex justify-center">
        {{ $demandes->links() }}
    </div>
    @endif

    @else
    {{-- Empty state --}}
    <div class="glass rounded-2xl p-12 text-center">
        <div class="w-16 h-16 rounded-2xl bg-cyan-500/10 flex items-center justify-center mx-auto mb-4">
            <i data-lucide="hand-coins" class="w-8 h-8 text-cyan-400"></i>
        </div>
        <h3 class="text-lg font-semibold text-white mb-2">Aucune demande</h3>
        <p class="text-cyan-100/50 text-sm mb-6">Vous n'avez pas encore soumis de demande de financement.</p>
        <a href="{{ route('citizen.projets.index') }}"
           class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-cyan-500 to-blue-600 text-white text-sm font-semibold hover:opacity-90 transition-opacity">
            <i data-lucide="search" class="w-4 h-4"></i>
            Parcourir les projets
        </a>
    </div>
    @endif

</div>
@endsection
