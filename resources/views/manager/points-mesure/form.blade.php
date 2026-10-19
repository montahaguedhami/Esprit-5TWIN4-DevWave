@php($editing = isset($point))
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

    <div class="sm:col-span-2">
        <label for="infrastructure_id" class="block text-sm font-medium text-slate-300">
            Infrastructure associée <span class="text-xs text-slate-500">(optionnelle pour les stations externes)</span>
        </label>
        <select
            id="infrastructure_id"
            name="infrastructure_id"
            class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-white focus:border-teal-400 focus:outline-none"
        >
            <option value="">Aucune infrastructure liée</option>
            @foreach($infrastructures ?? [] as $infrastructure)
                <option value="{{ $infrastructure->id }}" @selected((string) old('infrastructure_id', $point->infrastructure_id ?? '') === (string) $infrastructure->id)>
                    {{ $infrastructure->nom }} — {{ $infrastructure->type->label() }}{{ $infrastructure->zone ? ' · '.$infrastructure->zone->nom : '' }}
                </option>
            @endforeach
        </select>
        <p class="mt-1 text-[11px] text-slate-400">Les points internes peuvent être rattachés à un équipement du réseau ; les mesures resteront historisées sur le point.</p>
        @error('infrastructure_id')
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
    </label>
    <label class="text-sm text-slate-300">Statut
        <select name="statut" required class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-white">
            @foreach(['actif', 'inactif'] as $statut)
                <option value="{{ $statut }}" @selected(old('statut', $point->statut ?? 'actif') === $statut)>{{ ucfirst($statut) }}</option>
            @endforeach
        </select>
    </label>
    <label class="text-sm text-slate-300 sm:col-span-2">Description
        <textarea name="description" rows="3" class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-white">{{ old('description', $point->description ?? '') }}</textarea>
    </label>
</div>
