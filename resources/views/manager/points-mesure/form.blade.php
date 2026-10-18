@php($editing = isset($point))
<div class="grid gap-4 sm:grid-cols-2">
    <label class="text-sm text-slate-300">Code unique
        <input name="code" required maxlength="50" value="{{ old('code', $point->code ?? '') }}" class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-white">
        @error('code')<span class="text-xs text-red-300">{{ $message }}</span>@enderror
    </label>
    <label class="text-sm text-slate-300">Nom
        <input name="nom" required value="{{ old('nom', $point->nom ?? '') }}" class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-white">
        @error('nom')<span class="text-xs text-red-300">{{ $message }}</span>@enderror
    </label>
    <label class="text-sm text-slate-300">Zone
        <input name="zone" value="{{ old('zone', $point->zone ?? '') }}" class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-white">
    </label>
    <label class="text-sm text-slate-300">Adresse
        <input name="adresse" value="{{ old('adresse', $point->adresse ?? '') }}" class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-white">
    </label>
    <label class="text-sm text-slate-300">Latitude
        <input name="latitude" type="number" step="0.0000001" min="-90" max="90" value="{{ old('latitude', $point->latitude ?? '') }}" class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-white">
        @error('latitude')<span class="text-xs text-red-300">{{ $message }}</span>@enderror
    </label>
    <label class="text-sm text-slate-300">Longitude
        <input name="longitude" type="number" step="0.0000001" min="-180" max="180" value="{{ old('longitude', $point->longitude ?? '') }}" class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-white">
        @error('longitude')<span class="text-xs text-red-300">{{ $message }}</span>@enderror
    </label>
    <label class="text-sm text-slate-300">Type
        <select name="type" required class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-white">
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
