@extends('layouts.manager')

@php
    $title = 'Modifier ' . $projet->nom . ' — AquaSecure';
@endphp

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    #projet-map-form {
        height: 24rem;
    }
    .leaflet-container {
        border-radius: 0.75rem;
    }
</style>
@endpush

@section('manager-content')
<div class="max-w-4xl mx-auto space-y-6 animate-fade-in-up">

    <!-- Header -->
    <div>
        <a href="{{ route('manager.projets.show', $projet) }}" class="text-cyan-400 hover:text-cyan-300 text-sm font-semibold flex items-center gap-1 mb-2">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            Retour au projet
        </a>
        <h1 class="text-2xl sm:text-3xl font-display font-bold text-white">Modifier le Projet</h1>
        <p class="text-cyan-100/55 text-sm mt-1">{{ $projet->nom }}</p>
    </div>

    <!-- Form -->
    <form action="{{ route('manager.projets.update', $projet) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Basic Information -->
        <div class="glass rounded-2xl p-6">
            <h2 class="text-lg font-display font-bold text-white mb-4 flex items-center gap-2">
                <i data-lucide="file-text" class="w-5 h-5 text-cyan-400"></i>
                Informations Générales
            </h2>

            <div class="space-y-4">
                <!-- Nom -->
                <div>
                    <label for="nom" class="block text-sm font-semibold text-cyan-100/80 mb-2">
                        Nom du projet <span class="text-red-400">*</span>
                    </label>
                    <input type="text" 
                           id="nom" 
                           name="nom" 
                           value="{{ old('nom', $projet->nom) }}"
                           class="w-full px-4 py-2.5 rounded-xl bg-white/5 border border-white/10 text-white placeholder-cyan-100/40 focus:border-cyan-400/50 focus:outline-none transition-colors @error('nom') border-red-400/50 @enderror"
                           required>
                    @error('nom')
                    <p class="mt-1 text-sm text-red-400 flex items-center gap-1">
                        <i data-lucide="alert-circle" class="w-3 h-3"></i>
                        {{ $message }}
                    </p>
                    @enderror
                </div>

                <!-- Description -->
                <div>
                    <label for="description" class="block text-sm font-semibold text-cyan-100/80 mb-2">
                        Description
                    </label>
                    <textarea id="description" 
                              name="description" 
                              rows="4"
                              class="w-full px-4 py-2.5 rounded-xl bg-white/5 border border-white/10 text-white placeholder-cyan-100/40 focus:border-cyan-400/50 focus:outline-none transition-colors resize-none @error('description') border-red-400/50 @enderror">{{ old('description', $projet->description) }}</textarea>
                    @error('description')
                    <p class="mt-1 text-sm text-red-400 flex items-center gap-1">
                        <i data-lucide="alert-circle" class="w-3 h-3"></i>
                        {{ $message }}
                    </p>
                    @enderror
                </div>

                <!-- Dates -->
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label for="date_debut" class="block text-sm font-semibold text-cyan-100/80 mb-2">
                            Date de début <span class="text-red-400">*</span>
                        </label>
                        <input type="date" 
                               id="date_debut" 
                               name="date_debut" 
                               value="{{ old('date_debut', $projet->date_debut->format('Y-m-d')) }}"
                               class="w-full px-4 py-2.5 rounded-xl bg-white/5 border border-white/10 text-white focus:border-cyan-400/50 focus:outline-none transition-colors @error('date_debut') border-red-400/50 @enderror"
                               required>
                        @error('date_debut')
                        <p class="mt-1 text-sm text-red-400 flex items-center gap-1">
                            <i data-lucide="alert-circle" class="w-3 h-3"></i>
                            {{ $message }}
                        </p>
                        @enderror
                    </div>
                    <div>
                        <label for="date_fin" class="block text-sm font-semibold text-cyan-100/80 mb-2">
                            Date de fin (optionnel)
                        </label>
                        <input type="date" 
                               id="date_fin" 
                               name="date_fin" 
                               value="{{ old('date_fin', $projet->date_fin ? $projet->date_fin->format('Y-m-d') : '') }}"
                               class="w-full px-4 py-2.5 rounded-xl bg-white/5 border border-white/10 text-white focus:border-cyan-400/50 focus:outline-none transition-colors @error('date_fin') border-red-400/50 @enderror">
                        @error('date_fin')
                        <p class="mt-1 text-sm text-red-400 flex items-center gap-1">
                            <i data-lucide="alert-circle" class="w-3 h-3"></i>
                            {{ $message }}
                        </p>
                        @enderror
                    </div>
                </div>

                <!-- Budget & Progression -->
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label for="budget" class="block text-sm font-semibold text-cyan-100/80 mb-2">
                            Budget (DT) <span class="text-red-400">*</span>
                        </label>
                        <input type="number" 
                               id="budget" 
                               name="budget" 
                               value="{{ old('budget', $projet->budget) }}"
                               step="0.01"
                               min="0"
                               class="w-full px-4 py-2.5 rounded-xl bg-white/5 border border-white/10 text-white placeholder-cyan-100/40 focus:border-cyan-400/50 focus:outline-none transition-colors @error('budget') border-red-400/50 @enderror"
                               required>
                        @error('budget')
                        <p class="mt-1 text-sm text-red-400 flex items-center gap-1">
                            <i data-lucide="alert-circle" class="w-3 h-3"></i>
                            {{ $message }}
                        </p>
                        @enderror
                    </div>
                    <div>
                        <label for="progression" class="block text-sm font-semibold text-cyan-100/80 mb-2">
                            Progression (%) <span class="text-red-400">*</span>
                        </label>
                        <input type="number" 
                               id="progression" 
                               name="progression" 
                               value="{{ old('progression', $projet->progression) }}"
                               min="0"
                               max="100"
                               class="w-full px-4 py-2.5 rounded-xl bg-white/5 border border-white/10 text-white placeholder-cyan-100/40 focus:border-cyan-400/50 focus:outline-none transition-colors @error('progression') border-red-400/50 @enderror"
                               required>
                        @error('progression')
                        <p class="mt-1 text-sm text-red-400 flex items-center gap-1">
                            <i data-lucide="alert-circle" class="w-3 h-3"></i>
                            {{ $message }}
                        </p>
                        @enderror
                    </div>
                </div>

                <!-- Statut -->
                <div>
                    <label for="statut" class="block text-sm font-semibold text-cyan-100/80 mb-2">
                        Statut <span class="text-red-400">*</span>
                    </label>
                    <select id="statut" 
                            name="statut"
                            class="w-full px-4 py-2.5 rounded-xl bg-white/5 border border-white/10 text-white focus:border-cyan-400/50 focus:outline-none transition-colors @error('statut') border-red-400/50 @enderror"
                            required>
                        <option value="planifie" {{ old('statut', $projet->statut) === 'planifie' ? 'selected' : '' }}>Planifié</option>
                        <option value="en_cours" {{ old('statut', $projet->statut) === 'en_cours' ? 'selected' : '' }}>En cours</option>
                        <option value="termine" {{ old('statut', $projet->statut) === 'termine' ? 'selected' : '' }}>Terminé</option>
                        <option value="suspendu" {{ old('statut', $projet->statut) === 'suspendu' ? 'selected' : '' }}>Suspendu</option>
                        <option value="annule" {{ old('statut', $projet->statut) === 'annule' ? 'selected' : '' }}>Annulé</option>
                    </select>
                    @error('statut')
                    <p class="mt-1 text-sm text-red-400 flex items-center gap-1">
                        <i data-lucide="alert-circle" class="w-3 h-3"></i>
                        {{ $message }}
                    </p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Geolocation -->
        <div class="glass rounded-2xl p-6">
            <h2 class="text-lg font-display font-bold text-white mb-4 flex items-center gap-2">
                <i data-lucide="map-pin" class="w-5 h-5 text-cyan-400"></i>
                Localisation (optionnel)
            </h2>

            <div class="space-y-4">
                <!-- Adresse -->
                <div>
                    <label for="adresse" class="block text-sm font-semibold text-cyan-100/80 mb-2">
                        Adresse
                    </label>
                    <input type="text" 
                           id="adresse" 
                           name="adresse" 
                           value="{{ old('adresse', $projet->adresse) }}"
                           class="w-full px-4 py-2.5 rounded-xl bg-white/5 border border-white/10 text-white placeholder-cyan-100/40 focus:border-cyan-400/50 focus:outline-none transition-colors @error('adresse') border-red-400/50 @enderror">
                    @error('adresse')
                    <p class="mt-1 text-sm text-red-400 flex items-center gap-1">
                        <i data-lucide="alert-circle" class="w-3 h-3"></i>
                        {{ $message }}
                    </p>
                    @enderror
                </div>

                <!-- Coordinates -->
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label for="latitude" class="block text-sm font-semibold text-cyan-100/80 mb-2">
                            Latitude
                        </label>
                        <input type="number" 
                               id="latitude" 
                               name="latitude" 
                               value="{{ old('latitude', $projet->latitude) }}"
                               step="0.0000001"
                               min="-90"
                               max="90"
                               class="w-full px-4 py-2.5 rounded-xl bg-white/5 border border-white/10 text-white placeholder-cyan-100/40 focus:border-cyan-400/50 focus:outline-none transition-colors @error('latitude') border-red-400/50 @enderror">
                        @error('latitude')
                        <p class="mt-1 text-sm text-red-400 flex items-center gap-1">
                            <i data-lucide="alert-circle" class="w-3 h-3"></i>
                            {{ $message }}
                        </p>
                        @enderror
                    </div>
                    <div>
                        <label for="longitude" class="block text-sm font-semibold text-cyan-100/80 mb-2">
                            Longitude
                        </label>
                        <input type="number" 
                               id="longitude" 
                               name="longitude" 
                               value="{{ old('longitude', $projet->longitude) }}"
                               step="0.0000001"
                               min="-180"
                               max="180"
                               class="w-full px-4 py-2.5 rounded-xl bg-white/5 border border-white/10 text-white placeholder-cyan-100/40 focus:border-cyan-400/50 focus:outline-none transition-colors @error('longitude') border-red-400/50 @enderror">
                        @error('longitude')
                        <p class="mt-1 text-sm text-red-400 flex items-center gap-1">
                            <i data-lucide="alert-circle" class="w-3 h-3"></i>
                            {{ $message }}
                        </p>
                        @enderror
                    </div>
                </div>

                <!-- Map Interactive -->
                <div id="projet-map-form" class="h-96 rounded-xl overflow-hidden border border-white/10"></div>
                <p class="text-cyan-100/50 text-xs mt-2">
                    <i data-lucide="info" class="w-3 h-3 inline"></i>
                    Cliquez sur la carte pour placer le marqueur, ou déplacez-le en le faisant glisser
                </p>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('manager.projets.show', $projet) }}" 
               class="px-6 py-2.5 rounded-xl bg-white/5 text-cyan-100/80 hover:text-white font-semibold transition-colors">
                Annuler
            </a>
            <button type="submit" 
                    class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-cyan-500 to-blue-600 text-white font-semibold hover:shadow-lg hover:shadow-cyan-500/25 transition-all flex items-center gap-2">
                <i data-lucide="check" class="w-4 h-4"></i>
                Enregistrer les modifications
            </button>
        </div>
    </form>

</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    // Refresh Lucide icons
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }

    // Initialize editable map for project editing
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof initProjectFormMap === 'function') {
            initProjectFormMap(
                'projet-map-form',
                'latitude',
                'longitude',
                {{ old('latitude', $projet->latitude) ?? 'null' }},
                {{ old('longitude', $projet->longitude) ?? 'null' }}
            );
        }
    });
</script>
@endpush
