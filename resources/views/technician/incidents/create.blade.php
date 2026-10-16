@extends('layouts.manager')

@section('title', 'Signaler une Anomalie Terrain — Technicien')

@section('manager-content')
<div class="max-w-2xl mx-auto space-y-6">

    {{-- En-tête --}}
    <div class="flex items-center gap-3.5">
        <a href="{{ $selectedInfra ? route('technician.infrastructures.show', $selectedInfra) : route('technician.dashboard') }}"
           class="w-10 h-10 rounded-xl glass border border-white/15 flex items-center justify-center text-cyan-300 hover:text-white transition-colors shrink-0">
            <i data-lucide="arrow-left" class="w-5 h-5"></i>
        </a>
        <div>
            <div class="inline-flex items-center gap-2 px-2.5 py-0.5 rounded-full bg-rose-500/10 border border-rose-400/20 text-rose-400 text-xs font-semibold mb-1">
                <i data-lucide="alert-triangle" class="w-3.5 h-3.5"></i>
                Signalement Immédiat Terrain
            </div>
            <h1 class="text-2xl font-bold font-display text-white">Signaler un Problème Technique</h1>
            <p class="text-cyan-100/60 text-xs">Crée un incident relié au réseau et notifie le centre de supervision.</p>
        </div>
    </div>

    {{-- Formulaire --}}
    <form method="POST" action="{{ route('technician.incidents.store') }}" class="glass rounded-3xl p-6 border border-white/10 space-y-5 shadow-xl">
        @csrf

        {{-- Erreurs de validation éventuelles --}}
        @if($errors->any())
        <div class="p-4 rounded-2xl bg-rose-500/15 border border-rose-500/30 text-rose-300 text-xs space-y-1">
            <div class="font-bold flex items-center gap-2">
                <i data-lucide="alert-circle" class="w-4 h-4"></i>
                Veuillez corriger les informations suivantes :
            </div>
            <ul class="list-disc pl-5 space-y-0.5">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        {{-- Titre de l'anomalie --}}
        <div>
            <label for="titre" class="block text-xs font-semibold text-cyan-100/80 mb-1.5">
                Titre du problème constaté <span class="text-rose-400">*</span>
            </label>
            <input type="text"
                   id="titre"
                   name="titre"
                   required
                   value="{{ old('titre', $selectedInfra ? 'Anomalie détectée sur ' . $selectedInfra->nom : '') }}"
                   placeholder="Ex : Fuite majeure au niveau de la bride amont"
                   class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-white/20 text-white text-xs placeholder-cyan-100/40 focus:ring-2 focus:ring-cyan-400">
        </div>

        {{-- Type d'incident & Gravité --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="type" class="block text-xs font-semibold text-cyan-100/80 mb-1.5">
                    Type d'incident <span class="text-rose-400">*</span>
                </label>
                <select id="type" name="type" required class="w-full px-3 py-2.5 rounded-xl bg-slate-900 border border-white/20 text-white text-xs focus:ring-2 focus:ring-cyan-400">
                    <option value="panne" @selected(old('type') === 'panne')>Panne technique</option>
                    <option value="fuite" @selected(old('type') === 'fuite')>Fuite d'eau</option>
                    <option value="rupture" @selected(old('type') === 'rupture')>Rupture de canalisation</option>
                    <option value="pollution" @selected(old('type') === 'pollution')>Pollution / Eau trouble</option>
                    <option value="contamination" @selected(old('type') === 'contamination')>Contamination</option>
                </select>
            </div>

            <div>
                <label for="gravite" class="block text-xs font-semibold text-cyan-100/80 mb-1.5">
                    Niveau de gravité <span class="text-rose-400">*</span>
                </label>
                <select id="gravite" name="gravite" required class="w-full px-3 py-2.5 rounded-xl bg-slate-900 border border-white/20 text-white text-xs focus:ring-2 focus:ring-cyan-400">
                    <option value="faible" @selected(old('gravite') === 'faible')>Faible (Aucun impact usagers)</option>
                    <option value="moyenne" @selected(old('gravite', 'moyenne') === 'moyenne')>Moyenne (Perturbation locale)</option>
                    <option value="elevee" @selected(old('gravite') === 'elevee')>Élevée (Coupure partielle)</option>
                    <option value="critique" @selected(old('gravite') === 'critique')>Critique (Arrêt d'urgence)</option>
                </select>
            </div>
        </div>

        {{-- Infrastructure liée --}}
        <div>
            <label for="infrastructure_id" class="block text-xs font-semibold text-cyan-100/80 mb-1.5">
                Infrastructure concernée (Optionnel)
            </label>
            <select id="infrastructure_id" name="infrastructure_id" class="w-full px-3 py-2.5 rounded-xl bg-slate-900 border border-white/20 text-white text-xs focus:ring-2 focus:ring-cyan-400">
                <option value="">-- Aucune infrastructure spécifique --</option>
                @foreach($infrastructures as $inf)
                    <option value="{{ $inf->id }}" @selected(old('infrastructure_id', $selectedInfra?->id) == $inf->id)>
                        {{ $inf->nom }} ({{ $inf->localisation }})
                    </option>
                @endforeach
            </select>
            <p class="text-[11px] text-cyan-100/50 mt-1">L'incident sera directement rattaché à l'historique de cette infrastructure.</p>
        </div>

        {{-- Zone géographique & Localisation précise --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="zone_id" class="block text-xs font-semibold text-cyan-100/80 mb-1.5">
                    Zone géographique
                </label>
                <select id="zone_id" name="zone_id" class="w-full px-3 py-2.5 rounded-xl bg-slate-900 border border-white/20 text-white text-xs focus:ring-2 focus:ring-cyan-400">
                    <option value="">-- Sélectionner la zone --</option>
                    @foreach($zones as $z)
                        <option value="{{ $z->id }}" @selected(old('zone_id', $selectedInfra?->zone_id) == $z->id)>
                            {{ $z->nom }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="localisation" class="block text-xs font-semibold text-cyan-100/80 mb-1.5">
                    Localisation précise <span class="text-rose-400">*</span>
                </label>
                <input type="text"
                       id="localisation"
                       name="localisation"
                       required
                       value="{{ old('localisation', $selectedInfra?->localisation ?? '') }}"
                       placeholder="Ex : Rue Farhat Hached, regard n°4"
                       class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-white/20 text-white text-xs placeholder-cyan-100/40 focus:ring-2 focus:ring-cyan-400">
            </div>
        </div>

        {{-- Description détaillée --}}
        <div>
            <label for="description" class="block text-xs font-semibold text-cyan-100/80 mb-1.5">
                Description détaillée des constatations terrain <span class="text-rose-400">*</span>
            </label>
            <textarea id="description"
                      name="description"
                      rows="4"
                      required
                      minlength="5"
                      placeholder="Décrivez précisément les symptômes observés, le débit de fuite estimé, les éléments endommagés..."
                      class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-white/20 text-white text-xs placeholder-cyan-100/40 focus:ring-2 focus:ring-cyan-400">{{ old('description') }}</textarea>
        </div>

        {{-- Boutons d'action --}}
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-white/10">
            <a href="{{ $selectedInfra ? route('technician.infrastructures.show', $selectedInfra) : route('technician.dashboard') }}"
               class="px-4 py-2.5 rounded-xl text-xs font-semibold glass text-cyan-100/70 hover:text-white transition-all">
                Annuler
            </a>
            <button type="submit"
                    class="px-5 py-2.5 rounded-xl text-xs font-semibold bg-rose-600 hover:bg-rose-500 text-white shadow-lg shadow-rose-950/40 transition-all flex items-center gap-2">
                <i data-lucide="send" class="w-4 h-4"></i>
                <span>Transmettre le signalement</span>
            </button>
        </div>
    </form>

</div>
@endsection
