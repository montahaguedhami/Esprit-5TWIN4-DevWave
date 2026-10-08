@extends('layouts.frontoffice')

@section('title', 'Travaux de maintenance')

@section('frontoffice-content')
<div class="space-y-8">
    {{-- En-tête --}}
    <div class="flex items-center gap-3">
        <a href="{{ route('citizen.dashboard') }}" class="w-10 h-10 rounded-2xl glass flex items-center justify-center text-cyan-300 hover:text-white hover:bg-white/10 transition-all shadow-sm shrink-0" title="Retour au tableau de bord">
            <i data-lucide="arrow-left" class="w-5 h-5"></i>
        </a>
        <div>
            <h1 class="text-2xl sm:text-3xl font-display font-bold text-white tracking-tight">Travaux de maintenance</h1>
            <p class="text-cyan-100/60 text-xs sm:text-sm mt-0.5">Suivez les interventions de nos équipes sur le réseau d'eau potable</p>
        </div>
    </div>

    {{-- Chiffres clés --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <x-ui.card padding="sm" class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-amber-500/10 border border-amber-400/20 flex items-center justify-center"><i data-lucide="construction" class="w-6 h-6 text-amber-400"></i></div>
            <div>
                <p class="text-3xl font-display font-bold text-white">{{ $enCours->count() }}</p>
                <p class="text-xs text-cyan-100/60">Travaux en cours</p>
            </div>
        </x-ui.card>
        <x-ui.card padding="sm" class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-cyan-500/10 border border-cyan-400/20 flex items-center justify-center"><i data-lucide="calendar-clock" class="w-6 h-6 text-cyan-400"></i></div>
            <div>
                <p class="text-3xl font-display font-bold text-white">{{ $aVenir->count() }}</p>
                <p class="text-xs text-cyan-100/60">Interventions à venir</p>
            </div>
        </x-ui.card>
        <x-ui.card padding="sm" class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-500/10 border border-emerald-400/20 flex items-center justify-center"><i data-lucide="check-circle-2" class="w-6 h-6 text-emerald-400"></i></div>
            <div>
                <p class="text-3xl font-display font-bold text-white">{{ $termineesCeMois }}</p>
                <p class="text-xs text-cyan-100/60">Terminées ce mois-ci</p>
            </div>
        </x-ui.card>
    </div>

    @if($enCours->isNotEmpty() || $aVenir->isNotEmpty())
        <x-ui.alert type="warning" title="Perturbations possibles">
            Pendant certains travaux, des baisses de pression ou de courtes coupures d'eau peuvent survenir. Merci de votre compréhension.
        </x-ui.alert>
    @endif

    {{-- Sections : en cours / à venir / terminées --}}
    @foreach([
        ['titre' => 'En cours', 'icone' => 'construction', 'items' => $enCours, 'vide' => 'Aucun travail en cours actuellement.'],
        ['titre' => 'À venir', 'icone' => 'calendar-clock', 'items' => $aVenir, 'vide' => 'Aucune intervention planifiée.'],
        ['titre' => 'Récemment terminées', 'icone' => 'check-circle-2', 'items' => $terminees, 'vide' => 'Aucune intervention terminée récemment.'],
    ] as $section)
        <section>
            <h2 class="text-lg font-display font-bold text-white mb-4 flex items-center gap-2">
                <i data-lucide="{{ $section['icone'] }}" class="w-5 h-5 text-cyan-300"></i>
                {{ $section['titre'] }}
                <span class="text-xs font-semibold text-cyan-100/50">({{ $section['items']->count() }})</span>
            </h2>

            @if($section['items']->isEmpty())
                <x-ui.card padding="sm"><p class="text-sm text-cyan-100/50 text-center py-4">{{ $section['vide'] }}</p></x-ui.card>
            @else
                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($section['items'] as $intervention)
                        <x-maintenance.travail-card :intervention="$intervention" />
                    @endforeach
                </div>
            @endif
        </section>
    @endforeach
</div>
@endsection
