@extends('layouts.frontoffice')

@php
    $title = 'Mon portefeuille — AquaSecure';

    // Statuts réellement définis pour un projet (voir StoreProjetRequest /
    // UpdateProjetRequest : planifie, en_cours, termine, suspendu, annule).
    $statutProjetStyle = [
        'planifie' => ['bg' => 'bg-blue-500/15',  'text' => 'text-blue-300',  'icon' => 'calendar',    'label' => 'Planifié'],
        'en_cours' => ['bg' => 'bg-amber-500/15', 'text' => 'text-amber-300', 'icon' => 'loader-circle', 'label' => 'En cours'],
        'termine'  => ['bg' => 'bg-teal-500/15',  'text' => 'text-teal-300',  'icon' => 'check-circle', 'label' => 'Terminé'],
        'suspendu' => ['bg' => 'bg-orange-500/15','text' => 'text-orange-300','icon' => 'pause-circle', 'label' => 'Suspendu'],
        'annule'   => ['bg' => 'bg-red-500/15',   'text' => 'text-red-300',   'icon' => 'x-circle',    'label' => 'Annulé'],
    ];

    $fmt = fn ($v) => number_format((float) $v, 2, ',', ' ');

    $cartes = [
        [
            'label'   => 'Total de mes contributions',
            'valeur'  => $fmt($totalContributions) . ' TND',
            'icon'    => 'wallet',
            'accent'  => 'from-cyan-500/20 to-blue-600/10',
            'iconBg'  => 'bg-cyan-500/15',
            'iconTx'  => 'text-cyan-300',
            'note'    => 'Somme des contributions enregistrées à votre nom',
        ],
        [
            'label'   => 'Projets soutenus',
            'valeur'  => (string) $projetsSoutenus,
            'icon'    => 'folder-heart',
            'accent'  => 'from-blue-500/20 to-indigo-600/10',
            'iconBg'  => 'bg-blue-500/15',
            'iconTx'  => 'text-blue-300',
            'note'    => 'Projets distincts auxquels vous avez contribué',
        ],
        [
            'label'   => 'Projets terminés soutenus',
            'valeur'  => (string) $projetsTerminesSoutenus,
            'icon'    => 'badge-check',
            'accent'  => 'from-teal-500/20 to-emerald-600/10',
            'iconBg'  => 'bg-teal-500/15',
            'iconTx'  => 'text-teal-300',
            'note'    => 'Projets achevés parmi ceux que vous avez soutenus',
        ],
    ];
@endphp

@section('frontoffice-content')
<div class="space-y-6 animate-fade-in-up">

    {{-- ── En-tête ── --}}
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-display font-bold text-white">Mon portefeuille et mon impact</h1>
            <p class="text-cyan-100/55 text-sm mt-1">Suivez vos contributions aux projets AquaSecure et leur aboutissement.</p>
        </div>
        <a href="{{ route('citizen.demandes.index') }}"
           class="glass px-4 py-2 rounded-xl text-sm text-cyan-300 hover:text-white font-semibold flex items-center gap-2 transition-all hover:border-cyan-400/40">
            <i data-lucide="hand-coins" class="w-4 h-4"></i>
            Mes demandes
        </a>
    </div>

    {{-- ── Cartes statistiques ── --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        @foreach($cartes as $carte)
        <div class="glass rounded-2xl p-5 relative overflow-hidden border border-white/10">
            <div class="absolute inset-0 bg-gradient-to-br {{ $carte['accent'] }} pointer-events-none"></div>
            <div class="relative">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-[11px] font-semibold uppercase tracking-wider text-cyan-100/55">{{ $carte['label'] }}</span>
                    <span class="w-9 h-9 rounded-xl {{ $carte['iconBg'] }} flex items-center justify-center flex-shrink-0">
                        <i data-lucide="{{ $carte['icon'] }}" class="w-4 h-4 {{ $carte['iconTx'] }}"></i>
                    </span>
                </div>
                <div class="text-2xl font-display font-bold text-white leading-tight">{{ $carte['valeur'] }}</div>
                <div class="text-[11px] text-cyan-100/45 mt-2">{{ $carte['note'] }}</div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- ── Historique des contributions ── --}}
    <div class="flex items-center justify-between">
        <h2 class="text-lg font-display font-bold text-white">Historique de mes contributions</h2>
        @if($contributions->total() > 0)
        <span class="text-xs text-cyan-100/50">{{ $contributions->total() }} contribution(s)</span>
        @endif
    </div>

    @if($contributions->count() > 0)
    <div class="glass rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-white/[.03] border-b border-white/10">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-cyan-100/60 uppercase tracking-wider">Projet soutenu</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-cyan-100/60 uppercase tracking-wider">Montant</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-cyan-100/60 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-cyan-100/60 uppercase tracking-wider">Statut du projet</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-cyan-100/60 uppercase tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @foreach($contributions as $contribution)
                        @php
                            $projet  = $contribution->projet;
                            $statut  = $projet?->statut;
                            $ss      = $statutProjetStyle[$statut] ?? [
                                'bg' => 'bg-slate-500/15', 'text' => 'text-slate-300',
                                'icon' => 'circle-help', 'label' => ucfirst((string) $statut ?: '—'),
                            ];
                        @endphp
                        <tr class="hover:bg-white/[.02] transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-semibold text-white text-sm">{{ $projet?->nom ?? 'Projet supprimé' }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-white font-bold text-sm">{{ $fmt($contribution->montant) }} TND</span>
                            </td>
                            <td class="px-6 py-4 text-cyan-100/60 text-sm">
                                {{ $contribution->date_financement?->format('d/m/Y') ?? '—' }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold {{ $ss['bg'] }} {{ $ss['text'] }}">
                                    <i data-lucide="{{ $ss['icon'] }}" class="w-3 h-3"></i>
                                    {{ $ss['label'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                @if($projet)
                                <a href="{{ route('citizen.projets.show', $projet) }}"
                                   class="glass px-3 py-1.5 rounded-lg text-xs text-cyan-300 hover:text-white transition-colors inline-flex items-center gap-1.5">
                                    <i data-lucide="arrow-up-right" class="w-3 h-3"></i>
                                    Voir le projet
                                </a>
                                @else
                                <span class="text-xs text-cyan-100/30">—</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pagination --}}
    @if($contributions->hasPages())
    <div class="flex justify-center">
        {{ $contributions->links() }}
    </div>
    @endif

    @else
    {{-- Empty state --}}
    <div class="glass rounded-2xl p-12 text-center">
        <div class="w-16 h-16 rounded-2xl bg-cyan-500/10 flex items-center justify-center mx-auto mb-4">
            <i data-lucide="wallet" class="w-8 h-8 text-cyan-400"></i>
        </div>
        <h3 class="text-lg font-semibold text-white mb-2">Votre portefeuille est vide</h3>
        <p class="text-cyan-100/50 text-sm mb-6">Vous n'avez encore contribué à aucun projet. Découvrez les projets en cours de financement.</p>
        <a href="{{ route('citizen.projets.index') }}"
           class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-cyan-500 to-blue-600 text-white text-sm font-semibold hover:opacity-90 transition-opacity">
            <i data-lucide="search" class="w-4 h-4"></i>
            Parcourir les projets
        </a>
    </div>
    @endif

</div>
@endsection
