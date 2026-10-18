@extends('layouts.manager')

@section('title', 'Mes Interventions — Technicien')

@section('manager-content')
<div class="max-w-6xl mx-auto space-y-6">

    {{-- En-tête mobile --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-cyan-500/10 border border-cyan-400/20 text-cyan-400 text-xs font-semibold mb-2">
                <i data-lucide="wrench" class="w-3.5 h-3.5"></i>
                Technicien · {{ $technicien->nom }}
            </div>
            <h1 class="text-2xl sm:text-3xl font-display font-bold text-white tracking-tight">Mes Interventions</h1>
            <p class="text-cyan-100/60 text-sm mt-0.5">Suivi de vos missions de maintenance préventive et corrective.</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('technician.tournee') }}"
               class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-semibold bg-cyan-600 hover:bg-cyan-500 text-white shadow-md transition-all">
                <i data-lucide="map" class="w-4 h-4"></i>
                <span>Tournée du jour</span>
            </a>
            <a href="{{ route('technician.scan') }}"
               class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-semibold glass text-cyan-300 hover:text-white transition-all">
                <i data-lucide="qr-code" class="w-4 h-4"></i>
                <span>Scanner Ouvrage</span>
            </a>
        </div>
    </div>

    {{-- 3 Cartes de résumé --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <a href="{{ route('technician.interventions.index', ['status' => 'scheduled']) }}"
           class="glass rounded-2xl p-4 border {{ $selectedStatus === 'scheduled' ? 'border-cyan-400 bg-cyan-500/10' : 'border-white/10' }} hover:border-cyan-400/40 transition-all flex items-center justify-between">
            <div>
                <span class="text-xs text-cyan-100/60 block mb-1">Programmées</span>
                <span class="text-2xl font-bold font-display text-white">{{ $counts['scheduled'] }}</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-cyan-500/15 flex items-center justify-center">
                <i data-lucide="calendar" class="w-5 h-5 text-cyan-400"></i>
            </div>
        </a>

        <a href="{{ route('technician.interventions.index', ['status' => 'in_progress']) }}"
           class="glass rounded-2xl p-4 border {{ $selectedStatus === 'in_progress' ? 'border-amber-400 bg-amber-500/10' : 'border-amber-500/20' }} hover:border-amber-400/40 transition-all flex items-center justify-between">
            <div>
                <span class="text-xs text-amber-300 block mb-1">En cours</span>
                <span class="text-2xl font-bold font-display text-white">{{ $counts['in_progress'] }}</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-amber-500/15 flex items-center justify-center">
                <i data-lucide="loader" class="w-5 h-5 text-amber-400 animate-pulse"></i>
            </div>
        </a>

        <a href="{{ route('technician.interventions.index', ['status' => 'completed']) }}"
           class="glass rounded-2xl p-4 border {{ $selectedStatus === 'completed' ? 'border-emerald-400 bg-emerald-500/10' : 'border-emerald-500/20' }} hover:border-emerald-400/40 transition-all flex items-center justify-between">
            <div>
                <span class="text-xs text-emerald-300 block mb-1">Complétées</span>
                <span class="text-2xl font-bold font-display text-white">{{ $counts['completed'] }}</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-emerald-500/15 flex items-center justify-center">
                <i data-lucide="check-circle" class="w-5 h-5 text-emerald-400"></i>
            </div>
        </a>
    </div>

    {{-- Filtres par onglets --}}
    <div class="flex items-center gap-2 overflow-x-auto pb-1 text-xs font-semibold">
        <a href="{{ route('technician.interventions.index') }}"
           class="px-4 py-2 rounded-xl transition-all whitespace-nowrap {{ !$selectedStatus || $selectedStatus === 'all' ? 'bg-cyan-600 text-white shadow-md' : 'glass text-cyan-100/70 hover:text-white' }}">
            Toutes ({{ $counts['total'] }})
        </a>
        <a href="{{ route('technician.interventions.index', ['status' => 'scheduled']) }}"
           class="px-4 py-2 rounded-xl transition-all whitespace-nowrap {{ $selectedStatus === 'scheduled' ? 'bg-cyan-600 text-white shadow-md' : 'glass text-cyan-100/70 hover:text-white' }}">
            Programmées ({{ $counts['scheduled'] }})
        </a>
        <a href="{{ route('technician.interventions.index', ['status' => 'in_progress']) }}"
           class="px-4 py-2 rounded-xl transition-all whitespace-nowrap {{ $selectedStatus === 'in_progress' ? 'bg-cyan-600 text-white shadow-md' : 'glass text-cyan-100/70 hover:text-white' }}">
            En cours ({{ $counts['in_progress'] }})
        </a>
        <a href="{{ route('technician.interventions.index', ['status' => 'completed']) }}"
           class="px-4 py-2 rounded-xl transition-all whitespace-nowrap {{ $selectedStatus === 'completed' ? 'bg-cyan-600 text-white shadow-md' : 'glass text-cyan-100/70 hover:text-white' }}">
            Complétées ({{ $counts['completed'] }})
        </a>
    </div>

    {{-- Liste des interventions réelles (SANS COÛT) --}}
    @if($interventions->isEmpty())
        <div class="glass rounded-3xl p-10 text-center border border-white/10 space-y-3">
            <div class="w-12 h-12 rounded-2xl bg-cyan-500/10 flex items-center justify-center mx-auto text-cyan-400">
                <i data-lucide="check-circle-2" class="w-6 h-6"></i>
            </div>
            <h3 class="text-base font-bold text-white">Aucune intervention dans cette vue</h3>
            <p class="text-xs text-cyan-100/60 max-w-sm mx-auto">Toutes les interventions de ce statut ont été traitées ou aucune n'est planifiée.</p>
        </div>
    @else
        <div class="space-y-3">
            @foreach($interventions as $intervention)
            @php
                $infra = $intervention->infrastructure;
            @endphp
            <div class="glass rounded-2xl p-5 border border-white/10 hover:border-cyan-400/40 transition-all flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex items-start gap-4">
                    <div class="w-11 h-11 rounded-2xl {{ $intervention->statut === 'En cours' ? 'bg-amber-500/15 border border-amber-400/30' : ($intervention->statut === 'Terminée' ? 'bg-emerald-500/15 border border-emerald-400/30' : 'bg-cyan-500/15 border border-cyan-400/30') }} flex items-center justify-center shrink-0 mt-0.5">
                        @if($intervention->statut === 'En cours')
                            <i data-lucide="loader" class="w-5 h-5 text-amber-400 animate-spin"></i>
                        @elseif($intervention->statut === 'Terminée')
                            <i data-lucide="check" class="w-5 h-5 text-emerald-400"></i>
                        @else
                            <i data-lucide="calendar" class="w-5 h-5 text-cyan-400"></i>
                        @endif
                    </div>

                    <div>
                        <div class="flex items-center gap-2 mb-1 flex-wrap">
                            <span class="text-xs font-mono font-bold text-cyan-400">#{{ $intervention->id }}</span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $intervention->statut === 'Terminée' ? 'bg-emerald-500/15 text-emerald-300' : ($intervention->statut === 'En cours' ? 'bg-amber-500/15 text-amber-300' : 'bg-cyan-500/15 text-cyan-300') }}">
                                {{ $intervention->statut }}
                            </span>
                            <span class="text-xs text-cyan-100/50">
                                {{ $intervention->date?->format('d/m/Y') }}
                            </span>
                            @if($infra?->latestRiskPrediction)
                                <x-infrastructure.risk-badge :level="$infra->latestRiskPrediction->level" :probability="$infra->latestRiskPrediction->probability" />
                            @endif
                        </div>

                        <h3 class="text-sm font-bold text-white">
                            {{ $infra?->nom ?? 'Ouvrage non rattaché' }}
                        </h3>

                        <p class="text-xs text-cyan-100/60 mt-0.5">
                            <i data-lucide="map-pin" class="w-3.5 h-3.5 inline text-cyan-400"></i>
                            {{ $infra?->zone?->nom ?? 'Zone' }} · {{ $infra?->localisation ?? 'Localisation terrain' }}
                        </p>

                        <p class="text-xs text-white/80 mt-2 bg-slate-900/60 p-2.5 rounded-xl border border-white/5 line-clamp-2">
                            {{ $intervention->description }}
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2 shrink-0 self-end sm:self-center">
                    @if($infra)
                    <a href="{{ route('technician.infrastructures.show', $infra) }}"
                       class="px-3 py-1.5 rounded-xl text-xs font-semibold glass text-cyan-300 hover:text-white transition-all">
                        Fiche Ouvrage
                    </a>
                    @endif

                    @if($intervention->statut !== 'Terminée')
                    <a href="{{ route('technician.interventions.report', $intervention) }}"
                       class="px-3 py-1.5 rounded-xl text-xs font-semibold bg-cyan-600 hover:bg-cyan-500 text-white shadow-md transition-all">
                        Rapport
                    </a>
                    @endif

                    <a href="{{ route('technician.interventions.show', $intervention) }}"
                       class="p-2 rounded-xl glass text-cyan-100/50 hover:text-white transition-colors"
                       title="Détails de l'intervention">
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </a>
                </div>
            </div>
            @endforeach
        </div>

        <div class="pt-4">
            {{ $interventions->links() }}
        </div>
    @endif

</div>
@endsection
