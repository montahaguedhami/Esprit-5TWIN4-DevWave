@extends('layouts.manager')

@php
    $title = 'Carte des Projets — AquaSecure';
@endphp

@section('manager-content')
<div class="space-y-6 animate-fade-in-up">

    <!-- Header -->
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <a href="{{ route('manager.projets.index') }}" class="text-cyan-400 hover:text-cyan-300 text-sm font-semibold flex items-center gap-1 mb-2">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                Retour aux projets
            </a>
            <h1 class="text-2xl sm:text-3xl font-display font-bold text-white">Carte des Projets</h1>
            <p class="text-cyan-100/55 text-sm mt-0.5">Visualisation géographique des projets d'infrastructure</p>
        </div>
        <a href="{{ route('manager.projets.create') }}" 
           class="glass px-4 py-2 rounded-xl text-sm text-cyan-300 hover:text-white font-semibold flex items-center gap-2 transition-all hover:border-cyan-400/40">
            <i data-lucide="plus" class="w-4 h-4"></i>
            Nouveau projet
        </a>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        @php
            $statsData = [
                ['count' => $projets->count(), 'label' => 'Projets géolocalisés', 'icon' => 'map-pin', 'color' => 'cyan'],
                ['count' => $projets->where('statut', 'en_cours')->count(), 'label' => 'En cours', 'icon' => 'loader', 'color' => 'amber'],
                ['count' => $projets->where('statut', 'planifie')->count(), 'label' => 'Planifiés', 'icon' => 'calendar', 'color' => 'blue'],
                ['count' => $projets->where('statut', 'termine')->count(), 'label' => 'Terminés', 'icon' => 'check-circle', 'color' => 'teal'],
            ];
        @endphp
        @foreach($statsData as $stat)
        <div class="glass rounded-2xl p-4">
            <div class="w-10 h-10 rounded-xl bg-{{ $stat['color'] }}-500/10 flex items-center justify-center mb-3">
                <i data-lucide="{{ $stat['icon'] }}" class="w-5 h-5 text-{{ $stat['color'] }}-400"></i>
            </div>
            <p class="text-2xl font-display font-bold text-white">{{ $stat['count'] }}</p>
            <p class="text-xs font-semibold text-cyan-100/60 mt-0.5">{{ $stat['label'] }}</p>
        </div>
        @endforeach
    </div>

    <!-- Map Legend -->
    <div class="glass rounded-2xl p-4">
        <div class="flex items-center gap-2 mb-3">
            <i data-lucide="info" class="w-4 h-4 text-cyan-400"></i>
            <span class="text-sm font-semibold text-white">Légende</span>
        </div>
        <div class="flex flex-wrap gap-4 text-sm">
            @php
                $legendItems = [
                    ['color' => '#3b82f6', 'label' => 'Planifié'],
                    ['color' => '#f59e0b', 'label' => 'En cours'],
                    ['color' => '#14b8a6', 'label' => 'Terminé'],
                    ['color' => '#64748b', 'label' => 'Suspendu'],
                    ['color' => '#ef4444', 'label' => 'Annulé'],
                ];
            @endphp
            @foreach($legendItems as $item)
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 rounded-full border-2 border-white" style="background-color: {{ $item['color'] }}"></div>
                <span class="text-cyan-100/70">{{ $item['label'] }}</span>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Map Container -->
    @if($projets->count() > 0)
    <div class="glass rounded-2xl p-6">
        <div id="projets-map" class="h-[600px] rounded-xl overflow-hidden border border-white/10"></div>
    </div>
    @else
    <!-- Empty State -->
    <div class="glass rounded-2xl p-12 text-center">
        <div class="w-16 h-16 rounded-2xl bg-cyan-500/10 flex items-center justify-center mx-auto mb-4">
            <i data-lucide="map-pin" class="w-8 h-8 text-cyan-400"></i>
        </div>
        <h3 class="text-xl font-display font-bold text-white mb-2">Aucun projet géolocalisé</h3>
        <p class="text-cyan-100/60 mb-6">
            Les projets avec des coordonnées GPS s'afficheront ici sur la carte.
        </p>
        <a href="{{ route('manager.projets.create') }}" 
           class="inline-flex items-center gap-2 glass px-4 py-2 rounded-xl text-sm text-cyan-300 hover:text-white font-semibold">
            <i data-lucide="plus" class="w-4 h-4"></i>
            Créer un projet géolocalisé
        </a>
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

    @if($projets->count() > 0)
    // Initialize map with all projects
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof initProjectsMap === 'function') {
            const projects = @json($projets->map(function($projet) {
                return [
                    'id' => $projet->id,
                    'lat' => $projet->latitude,
                    'lng' => $projet->longitude,
                    'nom' => $projet->nom,
                    'statut' => $projet->statut,
                    'budget' => $projet->budget,
                    'finance' => $projet->total_finance,
                    'url' => route('manager.projets.show', $projet),
                ];
            })->values());

            initProjectsMap('projets-map', projects);
        }
    });
    @endif
</script>
@endpush
