@php($editing = isset($point))

@if($errors->any())
    <div class="mb-4 rounded-xl border border-red-500/40 bg-red-500/10 p-4 text-sm text-red-300">
        <div class="flex items-center gap-2 font-semibold text-red-200 mb-1">
            <i data-lucide="alert-circle" class="w-4 h-4"></i> Veuillez corriger les erreurs de saisie :
        </div>
        <ul class="list-disc list-inside space-y-1 text-xs ml-2">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="grid gap-4 sm:grid-cols-2">
    <div>
        <label for="code" class="block text-sm font-medium text-slate-300">
            Code unique <span class="text-teal-400 font-bold">*</span>
        </label>
        <input 
            type="text"
            id="code"
            name="code"
            value="{{ old('code', $point->code ?? $nextCode ?? 'PMS-001') }}" 
            readonly
            title="Le code est généré automatiquement"
            class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-white placeholder-slate-500 focus:outline-none"
        >
        <div class="flex justify-between text-[11px] text-slate-400 mt-1">
            <span>Identifiant officiel unique</span>
            <span>Auto-généré</span>
        </div>
        @error('code')
            <span class="mt-1 block text-xs text-red-400 font-medium">{{ $message }}</span>
        @enderror
    </div>

    <div>
        <label for="nom" class="block text-sm font-medium text-slate-300">
            Nom du point <span class="text-teal-400 font-bold">*</span>
        </label>
        <input 
            type="text"
            id="nom"
            name="nom" 
            value="{{ old('nom', $point->nom ?? '') }}" 
            class="mt-1 w-full rounded-xl border @error('nom') border-red-500/70 focus:border-red-400 @else border-slate-700 focus:border-teal-400 @enderror bg-slate-950 px-3 py-2 text-white placeholder-slate-500 transition-colors focus:outline-none"
        >
        <div class="flex justify-between text-[11px] text-slate-400 mt-1">
            <span>Désignation usuelle</span>
            <span>2–255 car.</span>
        </div>
        @error('nom')
            <span class="mt-1 block text-xs text-red-400 font-medium">{{ $message }}</span>
        @enderror
    </div>

    <div>
        <label for="zone" class="block text-sm font-medium text-slate-300">
            Zone / Commune <span class="text-xs text-slate-500">(optionnel)</span>
        </label>
        @if(isset($zones) && count($zones) > 0)
        <select
            id="zone"
            name="zone"
            class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-white focus:border-teal-400 focus:outline-none"
        >
            <option value="">— Non renseignée —</option>
            @foreach($zones as $zone)
                <option value="{{ $zone }}" @selected(old('zone', $point->zone ?? '') === $zone)>{{ $zone }}</option>
            @endforeach
        </select>
        <div class="flex justify-between text-[11px] text-slate-400 mt-1">
            <span>Liste des zones du réseau</span>
            <span>optionnel</span>
        </div>
        @else
        <input
            type="text"
            id="zone"
            name="zone"
            placeholder="Ex: Tunis Centre"
            value="{{ old('zone', $point->zone ?? '') }}"
            class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-white placeholder-slate-500 focus:border-teal-400 focus:outline-none"
        >
        <div class="flex justify-between text-[11px] text-slate-400 mt-1">
            <span>Zone / Commune</span>
            <span>aucune zone enregistrée</span>
        </div>
        @endif
        @error('zone')
            <span class="mt-1 block text-xs text-red-400 font-medium">{{ $message }}</span>
        @enderror
    </div>

    <div>
        <label for="adresse" class="block text-sm font-medium text-slate-300">
            Adresse physique <span class="text-xs text-slate-500">(optionnel)</span>
        </label>
        <input 
            type="text"
            id="adresse"
            name="adresse" 
            placeholder="Ex: Avenue Habib Bourguiba, Tunis"
            value="{{ old('adresse', $point->adresse ?? '') }}" 
            class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-white placeholder-slate-500 focus:border-teal-400 focus:outline-none"
        >
        @error('adresse')
            <span class="mt-1 block text-xs text-red-400 font-medium">{{ $message }}</span>
        @enderror
    </div>

    <div>
        <label for="latitude" class="block text-sm font-medium text-slate-300">
            Latitude (°N) <span class="text-xs text-slate-500">(-90 à 90)</span>
        </label>
        <input 
            type="number" 
            id="latitude"
            name="latitude" 
            step="0.0000001" 
            placeholder="Ex: 36.8064948"
            value="{{ old('latitude', $point->latitude ?? '') }}" 
            class="mt-1 w-full rounded-xl border @error('latitude') border-red-500/70 focus:border-red-400 @else border-slate-700 focus:border-teal-400 @enderror bg-slate-950 px-3 py-2 text-white placeholder-slate-500 focus:outline-none"
        >
        @error('latitude')
            <span class="mt-1 block text-xs text-red-400 font-medium">{{ $message }}</span>
        @enderror
    </div>

    <div>
        <label for="longitude" class="block text-sm font-medium text-slate-300">
            Longitude (°E) <span class="text-xs text-slate-500">(-180 à 180)</span>
        </label>
        <input 
            type="number" 
            id="longitude"
            name="longitude" 
            step="0.0000001" 
            placeholder="Ex: 10.1815316"
            value="{{ old('longitude', $point->longitude ?? '') }}" 
            class="mt-1 w-full rounded-xl border @error('longitude') border-red-500/70 focus:border-red-400 @else border-slate-700 focus:border-teal-400 @enderror bg-slate-950 px-3 py-2 text-white placeholder-slate-500 focus:outline-none"
        >
        @error('longitude')
            <span class="mt-1 block text-xs text-red-400 font-medium">{{ $message }}</span>
        @enderror
    </div>

    <div>
        <label for="type" class="block text-sm font-medium text-slate-300">
            Type d'infrastructure <span class="text-teal-400 font-bold">*</span>
        </label>
        <select 
            id="type"
            name="type" 
            class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-white focus:border-teal-400 focus:outline-none"
        >
            @foreach(['Station', 'Puits', 'Réservoir', 'Réseau', 'Autre'] as $type)
                <option value="{{ $type }}" @selected(old('type', $point->type ?? 'Station') === $type)>{{ $type }}</option>
            @endforeach
        </select>
        @error('type')
            <span class="mt-1 block text-xs text-red-400 font-medium">{{ $message }}</span>
        @enderror
    </div>

    <div>
        <label for="statut" class="block text-sm font-medium text-slate-300">
            Statut opérationnel <span class="text-teal-400 font-bold">*</span>
        </label>
        <select 
            id="statut"
            name="statut" 
            class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-white focus:border-teal-400 focus:outline-none"
        >
            <option value="actif" @selected(old('statut', $point->statut ?? 'actif') === 'actif')>Actif (surveillance en cours)</option>
            <option value="inactif" @selected(old('statut', $point->statut ?? 'actif') === 'inactif')>Inactif (hors service ou archivé)</option>
        </select>
        @error('statut')
            <span class="mt-1 block text-xs text-red-400 font-medium">{{ $message }}</span>
        @enderror
    </div>

    <div class="sm:col-span-2">
        <label for="description" class="block text-sm font-medium text-slate-300">
            Description / Remarques d'accès <span class="text-xs text-slate-500">(optionnel)</span>
        </label>
        <textarea 
            id="description"
            name="description" 
            rows="3" 
            placeholder="Informations utiles sur la vanne, accès aux capteurs, historique..."
            class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-white placeholder-slate-500 focus:border-teal-400 focus:outline-none"
        >{{ old('description', $point->description ?? '') }}</textarea>
        @error('description')
            <span class="mt-1 block text-xs text-red-400 font-medium">{{ $message }}</span>
        @enderror
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    function showFieldError(input, message) {
        let box = input.parentElement.querySelector('.validation-error');
        if (!box) {
            box = document.createElement('div');
            box.className = 'validation-error mt-1 block text-xs text-red-400';
            input.insertAdjacentElement('afterend', box);
        }
        box.textContent = message;
        box.hidden = false;
    }

    function hideFieldErrors(input) {
        const box = input.parentElement.querySelector('.validation-error');
        if (box) {
            box.hidden = true;
        }
    }

    function validatePointForm() {
        const problèmes = [];

        const nom = document.getElementById('nom');
        hideFieldErrors(nom);
        if (!nom.value.trim() || nom.value.trim().length < 2) {
            showFieldError(nom, 'Le nom du point doit contenir au moins 2 caractères.');
            problèmes.push('nom');
        }

        const latitude = document.getElementById('latitude');
        hideFieldErrors(latitude);
        if (latitude.value !== '' && (Number(latitude.value) < -90 || Number(latitude.value) > 90)) {
            showFieldError(latitude, 'La latitude doit être comprise entre -90 et 90.');
            problèmes.push('latitude');
        }

        const longitude = document.getElementById('longitude');
        hideFieldErrors(longitude);
        if (longitude.value !== '' && (Number(longitude.value) < -180 || Number(longitude.value) > 180)) {
            showFieldError(longitude, 'La longitude doit être comprise entre -180 et 180.');
            problèmes.push('longitude');
        }

        if (problèmes.length > 0) {
            return false;
        }

        return true;
    }

    const container = document.getElementById('nom')?.closest('form');

    if (container) {
        container.addEventListener('submit', function (event) {
            if (!validatePointForm()) {
                event.preventDefault();
            }
        });
    }
});
</script>
