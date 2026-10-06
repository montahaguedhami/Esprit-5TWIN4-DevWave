@extends('layouts.frontoffice')

@php
    $title = $projet->nom . ' — AquaSecure';
    
    $statusStyle = [
        'planifie'    => ['bg'=>'bg-blue-500/15','text'=>'text-blue-300','label'=>'Planifié','icon'=>'calendar'],
        'en_cours'    => ['bg'=>'bg-amber-500/15','text'=>'text-amber-300','label'=>'En cours','icon'=>'loader'],
        'termine'     => ['bg'=>'bg-teal-500/15','text'=>'text-teal-300','label'=>'Terminé','icon'=>'check-circle'],
        'suspendu'    => ['bg'=>'bg-slate-500/15','text'=>'text-slate-300','label'=>'Suspendu','icon'=>'pause-circle'],
        'annule'      => ['bg'=>'bg-red-500/15','text'=>'text-red-300','label'=>'Annulé','icon'=>'x-circle'],
    ];
    
    $ss = $statusStyle[$projet->statut];
@endphp

@section('frontoffice-content')
<div class="space-y-6 animate-fade-in-up">

    <!-- Breadcrumb -->
    <div>
        <a href="{{ route('citizen.projets.index') }}" class="text-cyan-400 hover:text-cyan-300 text-sm font-semibold flex items-center gap-1 mb-3">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            Retour aux projets
        </a>
    </div>

    <!-- Project Header -->
    <div class="glass rounded-2xl p-6">
        <div class="flex items-start gap-4 mb-4">
            <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-cyan-500 to-blue-600 flex items-center justify-center flex-shrink-0">
                <i data-lucide="briefcase" class="w-7 h-7 text-white"></i>
            </div>
            <div class="flex-1">
                <div class="flex flex-wrap items-center gap-2 mb-2">
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $ss['bg'] }} {{ $ss['text'] }} flex items-center gap-1">
                        <i data-lucide="{{ $ss['icon'] }}" class="w-3 h-3"></i>
                        {{ $ss['label'] }}
                    </span>
                    @if($projet->hasGeolocation())
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-cyan-500/15 text-cyan-300 flex items-center gap-1">
                        <i data-lucide="map-pin" class="w-3 h-3"></i>
                        Géolocalisé
                    </span>
                    @endif
                </div>
                <h1 class="text-2xl sm:text-3xl font-display font-bold text-white leading-tight">{{ $projet->nom }}</h1>
                @if($projet->adresse)
                <p class="text-cyan-100/60 text-sm mt-2 flex items-center gap-1">
                    <i data-lucide="map-pin" class="w-4 h-4"></i>
                    {{ $projet->adresse }}
                </p>
                @endif
            </div>
        </div>
    </div>

    <!-- Description -->
    @if($projet->description)
    <div class="glass rounded-2xl p-6">
        <h2 class="text-lg font-display font-bold text-white mb-3 flex items-center gap-2">
            <i data-lucide="file-text" class="w-5 h-5 text-cyan-400"></i>
            À propos du projet
        </h2>
        <p class="text-cyan-100/80 leading-relaxed">{{ $projet->description }}</p>
    </div>
    @endif

    <!-- Financial Overview -->
    <div class="glass rounded-2xl p-6">
        <h2 class="text-lg font-display font-bold text-white mb-4 flex items-center gap-2">
            <i data-lucide="wallet" class="w-5 h-5 text-cyan-400"></i>
            Financement du Projet
        </h2>

        <!-- Budget Cards -->
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 mb-4">
            <div class="bg-white/[.03] rounded-xl px-4 py-3">
                <p class="text-cyan-100/50 text-xs mb-1">Budget Total</p>
                <p class="text-white font-bold text-lg">{{ number_format($projet->budget, 0, ',', ' ') }}</p>
                <p class="text-cyan-400 text-xs font-semibold">DT</p>
            </div>
            <div class="bg-white/[.03] rounded-xl px-4 py-3">
                <p class="text-cyan-100/50 text-xs mb-1">Montant Financé</p>
                <p class="text-white font-bold text-lg">{{ number_format($projet->total_finance, 0, ',', ' ') }}</p>
                <p class="text-cyan-400 text-xs font-semibold">DT</p>
            </div>
            <div class="bg-white/[.03] rounded-xl px-4 py-3 col-span-2 sm:col-span-1">
                <p class="text-cyan-100/50 text-xs mb-1">Pourcentage Financé</p>
                <p class="text-white font-bold text-lg">{{ number_format($projet->pourcentage_finance, 1) }}</p>
                <p class="text-cyan-400 text-xs font-semibold">%</p>
            </div>
        </div>

        <!-- Progress Bar -->
        <div class="mb-4">
            <div class="flex justify-between text-xs text-cyan-100/50 mb-2">
                <span>Progression du financement</span>
                <span class="font-bold text-white">{{ number_format($projet->pourcentage_finance, 1) }}%</span>
            </div>
            <div class="h-3 rounded-full bg-white/5 overflow-hidden">
                @php
                    $percentage = min($projet->pourcentage_finance, 100);
                    $color = $percentage >= 100 ? 'bg-teal-400' : ($percentage >= 50 ? 'bg-cyan-400' : 'bg-blue-400');
                @endphp
                <div class="h-full rounded-full {{ $color }} transition-all duration-500" style="width: {{ $percentage }}%"></div>
            </div>
        </div>

        @if($projet->budget_restant < 0)
        <div class="p-3 rounded-xl bg-teal-500/10 border border-teal-400/20">
            <p class="text-teal-300 text-sm flex items-center gap-2">
                <i data-lucide="check-circle" class="w-4 h-4"></i>
                <strong>Projet entièrement financé !</strong>
            </p>
        </div>
        @elseif($projet->budget_restant > 0)
        <div class="p-3 rounded-xl bg-cyan-500/10 border border-cyan-400/20">
            <p class="text-cyan-300 text-sm">
                <strong>Montant restant à financer :</strong> {{ number_format($projet->budget_restant, 0, ',', ' ') }} DT
            </p>
        </div>
        @endif
    </div>

    <!-- Funding Sources -->
    @if($projet->financements->count() > 0)
    <div class="glass rounded-2xl p-6">
        <h2 class="text-lg font-display font-bold text-white mb-4 flex items-center gap-2">
            <i data-lucide="banknote" class="w-5 h-5 text-cyan-400"></i>
            Sources de Financement ({{ $projet->financements->count() }})
        </h2>

        <div class="space-y-3">
            @foreach($projet->financements as $financement)
            <div class="bg-white/[.03] rounded-xl p-4 hover:bg-white/[.05] transition-colors">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex items-start gap-3 flex-1">
                        <div class="w-10 h-10 rounded-lg bg-cyan-500/10 flex items-center justify-center flex-shrink-0">
                            <i data-lucide="banknote" class="w-5 h-5 text-cyan-400"></i>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-white font-semibold text-sm mb-1">{{ $financement->source }}</h3>
                            <p class="text-cyan-100/50 text-xs">{{ $financement->date_financement->format('d/m/Y') }}</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-cyan-400 font-bold text-lg">{{ number_format($financement->montant, 0, ',', ' ') }}</p>
                        <p class="text-cyan-100/50 text-xs">DT</p>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Timeline -->
    <div class="glass rounded-2xl p-6">
        <h2 class="text-lg font-display font-bold text-white mb-4 flex items-center gap-2">
            <i data-lucide="calendar" class="w-5 h-5 text-cyan-400"></i>
            Calendrier
        </h2>

        <div class="grid sm:grid-cols-2 gap-4">
            <div class="bg-white/[.03] rounded-xl p-4">
                <div class="flex items-center gap-2 mb-2">
                    <i data-lucide="play-circle" class="w-4 h-4 text-cyan-400"></i>
                    <p class="text-cyan-100/60 text-sm font-semibold">Date de début</p>
                </div>
                <p class="text-white font-bold text-xl">{{ $projet->date_debut->format('d/m/Y') }}</p>
                <p class="text-cyan-100/50 text-xs mt-1">{{ $projet->date_debut->diffForHumans() }}</p>
            </div>

            @if($projet->date_fin)
            <div class="bg-white/[.03] rounded-xl p-4">
                <div class="flex items-center gap-2 mb-2">
                    <i data-lucide="flag" class="w-4 h-4 text-cyan-400"></i>
                    <p class="text-cyan-100/60 text-sm font-semibold">Date de fin</p>
                </div>
                <p class="text-white font-bold text-xl">{{ $projet->date_fin->format('d/m/Y') }}</p>
                <p class="text-cyan-100/50 text-xs mt-1">
                    @if($projet->date_fin->isPast())
                        Terminé {{ $projet->date_fin->diffForHumans() }}
                    @else
                        Dans {{ $projet->date_fin->diffForHumans() }}
                    @endif
                </p>
            </div>

            @if($projet->date_debut && $projet->date_fin)
            <div class="bg-white/[.03] rounded-xl p-4 sm:col-span-2">
                <div class="flex items-center gap-2 mb-2">
                    <i data-lucide="clock" class="w-4 h-4 text-cyan-400"></i>
                    <p class="text-cyan-100/60 text-sm font-semibold">Durée du projet</p>
                </div>
                <p class="text-white font-bold text-xl">{{ $projet->date_debut->diffInDays($projet->date_fin) }} jours</p>
            </div>
            @endif
            @else
            <div class="bg-white/[.03] rounded-xl p-4">
                <div class="flex items-center gap-2 mb-2">
                    <i data-lucide="flag" class="w-4 h-4 text-cyan-400"></i>
                    <p class="text-cyan-100/60 text-sm font-semibold">Date de fin</p>
                </div>
                <p class="text-cyan-100/40 text-sm italic">Non définie</p>
            </div>
            @endif
        </div>
    </div>

    <!-- Map -->
    @if($projet->hasGeolocation())
    <div class="glass rounded-2xl p-6">
        <h2 class="text-lg font-display font-bold text-white mb-4 flex items-center gap-2">
            <i data-lucide="map-pin" class="w-5 h-5 text-cyan-400"></i>
            Localisation
        </h2>

        @if($projet->adresse)
        <div class="mb-4">
            <p class="text-cyan-100/60 text-sm mb-1">Adresse</p>
            <p class="text-white font-semibold">{{ $projet->adresse }}</p>
        </div>
        @endif

        <div class="grid grid-cols-2 gap-3 mb-4">
            <div>
                <p class="text-cyan-100/50 text-xs mb-1">Latitude</p>
                <p class="text-white text-sm font-mono">{{ number_format($projet->latitude, 4) }}</p>
            </div>
            <div>
                <p class="text-cyan-100/50 text-xs mb-1">Longitude</p>
                <p class="text-white text-sm font-mono">{{ number_format($projet->longitude, 4) }}</p>
            </div>
        </div>

        <!-- Map Placeholder (will be implemented in Phase 10) -->
        <div id="projet-map" class="h-64 rounded-xl bg-white/5 border border-white/10"></div>
    </div>
    @endif

</div>
@endsection

@push('scripts')
<script>
    // Refresh Lucide icons
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }

    @if($projet->hasGeolocation())
    // Initialize project map for citizen view
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof initProjectShowMap === 'function') {
            initProjectShowMap(
                'projet-map',
                {{ $projet->latitude }},
                {{ $projet->longitude }},
                '{{ addslashes($projet->nom) }}'
            );
        }
    });
    @endif
</script>
@endpush
