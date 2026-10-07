@extends('layouts.manager')

@php
    $title = $projet->nom . ' — AquaSecure';
    
    $statusStyle = [
        'planifie'    => ['bg'=>'bg-blue-500/15','text'=>'text-blue-300','label'=>'Planifié'],
        'en_cours'    => ['bg'=>'bg-amber-500/15','text'=>'text-amber-300','label'=>'En cours'],
        'termine'     => ['bg'=>'bg-teal-500/15','text'=>'text-teal-300','label'=>'Terminé'],
        'suspendu'    => ['bg'=>'bg-slate-500/15','text'=>'text-slate-300','label'=>'Suspendu'],
        'annule'      => ['bg'=>'bg-red-500/15','text'=>'text-red-300','label'=>'Annulé'],
    ];
    
    $ss = $statusStyle[$projet->statut];
@endphp

@section('manager-content')
<div class="space-y-6 animate-fade-in-up">

    <!-- Breadcrumb & Actions -->
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <a href="{{ route('manager.projets.index') }}" class="text-cyan-400 hover:text-cyan-300 text-sm font-semibold flex items-center gap-1 mb-2">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                Retour aux projets
            </a>
            <h1 class="text-2xl sm:text-3xl font-display font-bold text-white">{{ $projet->nom }}</h1>
            <div class="flex items-center gap-2 mt-2">
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $ss['bg'] }} {{ $ss['text'] }}">
                    {{ $ss['label'] }}
                </span>
                @if($projet->hasGeolocation())
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-cyan-500/15 text-cyan-300 flex items-center gap-1">
                    <i data-lucide="map-pin" class="w-3 h-3"></i>
                    Géolocalisé
                </span>
                @endif
            </div>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('manager.projets.edit', $projet) }}" 
               class="glass px-4 py-2 rounded-xl text-sm text-cyan-300 hover:text-white font-semibold flex items-center gap-2">
                <i data-lucide="edit" class="w-4 h-4"></i>
                Modifier
            </a>
            <form action="{{ route('manager.projets.destroy', $projet) }}" 
                  method="POST" 
                  onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce projet ? Tous les financements associés seront également supprimés.')">
                @csrf
                @method('DELETE')
                <button type="submit" 
                        class="glass px-4 py-2 rounded-xl text-sm text-red-300 hover:text-red-200 font-semibold flex items-center gap-2 border-red-400/20">
                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                    Supprimer
                </button>
            </form>
        </div>
    </div>

    <!-- Project Information -->
    <div class="grid lg:grid-cols-3 gap-6">
        
        <!-- Main Info -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Description -->
            <div class="glass rounded-2xl p-6">
                <h2 class="text-lg font-display font-bold text-white mb-4 flex items-center gap-2">
                    <i data-lucide="file-text" class="w-5 h-5 text-cyan-400"></i>
                    Description
                </h2>
                <p class="text-cyan-100/80 leading-relaxed">
                    {{ $projet->description ?? 'Aucune description disponible.' }}
                </p>
            </div>

            <!-- Financial Summary -->
            <div class="glass rounded-2xl p-6">
                <h2 class="text-lg font-display font-bold text-white mb-4 flex items-center gap-2">
                    <i data-lucide="wallet" class="w-5 h-5 text-cyan-400"></i>
                    Résumé Financier
                </h2>
                
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-4">
                    <div class="bg-white/[.03] rounded-xl px-4 py-3">
                        <p class="text-cyan-100/40 text-xs mb-1">Budget Total</p>
                        <p class="text-white font-bold text-lg">{{ number_format($projet->budget, 0, ',', ' ') }}</p>
                        <p class="text-cyan-400 text-xs font-semibold">DT</p>
                    </div>
                    <div class="bg-white/[.03] rounded-xl px-4 py-3">
                        <p class="text-cyan-100/40 text-xs mb-1">Total Financé</p>
                        <p class="text-white font-bold text-lg">{{ number_format($projet->total_finance, 0, ',', ' ') }}</p>
                        <p class="text-cyan-400 text-xs font-semibold">DT</p>
                    </div>
                    <div class="bg-white/[.03] rounded-xl px-4 py-3">
                        <p class="text-cyan-100/40 text-xs mb-1">Budget Restant</p>
                        <p class="text-white font-bold text-lg">{{ number_format($projet->budget_restant, 0, ',', ' ') }}</p>
                        <p class="{{ $projet->budget_restant < 0 ? 'text-red-400' : 'text-cyan-400' }} text-xs font-semibold">DT</p>
                    </div>
                    <div class="bg-white/[.03] rounded-xl px-4 py-3">
                        <p class="text-cyan-100/40 text-xs mb-1">% Financement</p>
                        <p class="text-white font-bold text-lg">{{ number_format($projet->pourcentage_finance, 1) }}</p>
                        <p class="text-cyan-400 text-xs font-semibold">%</p>
                    </div>
                </div>

                <!-- Progression du projet (avancement physique) -->
                <div class="mb-4">
                    <div class="flex justify-between text-xs text-cyan-100/50 mb-1">
                        <span>Progression du projet (avancement physique)</span>
                        <span class="font-bold text-white">{{ $projet->progression }}%</span>
                    </div>
                    <div class="h-3 rounded-full bg-white/5 overflow-hidden">
                        @php
                            $progColor = $projet->progression >= 100 ? 'bg-green-400' : ($projet->progression >= 50 ? 'bg-cyan-400' : 'bg-blue-400');
                        @endphp
                        <div class="h-full rounded-full {{ $progColor }}" style="width: {{ min($projet->progression, 100) }}%"></div>
                    </div>
                </div>

                <!-- Progression du financement -->
                <div class="mb-2">
                    <div class="flex justify-between text-xs text-cyan-100/50 mb-1">
                        <span>Progression du financement</span>
                        <span class="font-bold text-white">{{ number_format($projet->pourcentage_finance, 1) }}%</span>
                    </div>
                    <div class="h-3 rounded-full bg-white/5 overflow-hidden">
                        @php
                            $percentage = min($projet->pourcentage_finance, 100);
                            $color = $percentage >= 100 ? 'bg-teal-400' : ($percentage >= 50 ? 'bg-cyan-400' : 'bg-blue-400');
                        @endphp
                        <div class="h-full rounded-full {{ $color }}" style="width: {{ $percentage }}%"></div>
                    </div>
                </div>

                @if($projet->budget_restant < 0)
                <div class="mt-4 p-3 rounded-xl bg-red-500/10 border border-red-400/20">
                    <p class="text-red-300 text-sm flex items-center gap-2">
                        <i data-lucide="alert-triangle" class="w-4 h-4"></i>
                        <strong>Dépassement de budget :</strong> {{ number_format(abs($projet->budget_restant), 0, ',', ' ') }} DT
                    </p>
                </div>
                @endif
            </div>

            <!-- Financements List -->
            <div class="glass rounded-2xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-display font-bold text-white flex items-center gap-2">
                        <i data-lucide="banknote" class="w-5 h-5 text-cyan-400"></i>
                        Financements ({{ $projet->financements->count() }})
                    </h2>
                    <a href="{{ route('manager.financements.create', ['projet_id' => $projet->id]) }}" 
                       class="text-sm text-cyan-400 hover:text-cyan-300 font-semibold flex items-center gap-1">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                        Ajouter
                    </a>
                </div>

                @if($projet->financements->count() > 0)
                <div class="space-y-3">
                    @foreach($projet->financements as $financement)
                    <div class="bg-white/[.03] rounded-xl p-4 hover:bg-white/[.05] transition-colors">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex-1">
                                <h3 class="text-white font-semibold text-sm mb-1">{{ $financement->source }}</h3>
                                <p class="text-cyan-100/50 text-xs">{{ $financement->date_financement->format('d/m/Y') }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-cyan-400 font-bold text-lg">{{ number_format($financement->montant, 0, ',', ' ') }} DT</p>
                                <div class="flex gap-2 mt-2">
                                    <a href="{{ route('manager.financements.show', $financement) }}" 
                                       class="text-xs text-cyan-400 hover:text-cyan-300">
                                        Voir
                                    </a>
                                    <span class="text-cyan-100/30">·</span>
                                    <a href="{{ route('manager.financements.edit', $financement) }}" 
                                       class="text-xs text-cyan-400 hover:text-cyan-300">
                                        Modifier
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-8">
                    <div class="w-12 h-12 rounded-xl bg-cyan-500/10 flex items-center justify-center mx-auto mb-3">
                        <i data-lucide="banknote" class="w-6 h-6 text-cyan-400"></i>
                    </div>
                    <p class="text-cyan-100/60 text-sm mb-4">Aucun financement pour ce projet</p>
                    <a href="{{ route('manager.financements.create', ['projet_id' => $projet->id]) }}" 
                       class="inline-flex items-center gap-2 text-sm text-cyan-400 hover:text-cyan-300 font-semibold">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                        Ajouter le premier financement
                    </a>
                </div>
                @endif
            </div>

        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            
            <!-- Dates -->
            <div class="glass rounded-2xl p-6">
                <h3 class="text-sm font-bold text-white mb-4 flex items-center gap-2">
                    <i data-lucide="calendar" class="w-4 h-4 text-cyan-400"></i>
                    Dates
                </h3>
                <div class="space-y-3">
                    <div>
                        <p class="text-cyan-100/50 text-xs mb-1">Date de début</p>
                        <p class="text-white font-semibold">{{ $projet->date_debut->format('d/m/Y') }}</p>
                    </div>
                    @if($projet->date_fin)
                    <div>
                        <p class="text-cyan-100/50 text-xs mb-1">Date de fin</p>
                        <p class="text-white font-semibold">{{ $projet->date_fin->format('d/m/Y') }}</p>
                    </div>
                    <div>
                        <p class="text-cyan-100/50 text-xs mb-1">Durée</p>
                        <p class="text-white font-semibold">{{ $projet->date_debut->diffInDays($projet->date_fin) }} jours</p>
                    </div>
                    @else
                    <div>
                        <p class="text-cyan-100/50 text-xs mb-1">Date de fin</p>
                        <p class="text-cyan-100/40 text-sm italic">Non définie</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Location Card -->
            @if($projet->hasGeolocation())
            <div class="glass rounded-2xl p-6">
                <h3 class="text-sm font-bold text-white mb-4 flex items-center gap-2">
                    <i data-lucide="map-pin" class="w-4 h-4 text-cyan-400"></i>
                    Localisation
                </h3>
                <div class="space-y-3 mb-4">
                    @if($projet->adresse)
                    <div>
                        <p class="text-cyan-100/50 text-xs mb-1">Adresse</p>
                        <p class="text-white text-sm">{{ $projet->adresse }}</p>
                    </div>
                    @endif
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <p class="text-cyan-100/50 text-xs mb-1">Latitude</p>
                            <p class="text-white text-sm font-mono">{{ number_format($projet->latitude, 4) }}</p>
                        </div>
                        <div>
                            <p class="text-cyan-100/50 text-xs mb-1">Longitude</p>
                            <p class="text-white text-sm font-mono">{{ number_format($projet->longitude, 4) }}</p>
                        </div>
                    </div>
                </div>
                
                <!-- Map Placeholder (will be implemented in Phase 10) -->
                <div id="projet-map" class="h-48 rounded-xl bg-white/5 border border-white/10"></div>
            </div>
            @else
            <div class="glass rounded-2xl p-6">
                <h3 class="text-sm font-bold text-white mb-4 flex items-center gap-2">
                    <i data-lucide="map-pin" class="w-4 h-4 text-cyan-400"></i>
                    Localisation
                </h3>
                <p class="text-cyan-100/50 text-sm text-center py-4">Aucune localisation définie</p>
            </div>
            @endif

        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    // Refresh Lucide icons
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }

    @if($projet->hasGeolocation())
    // Initialize project map
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
