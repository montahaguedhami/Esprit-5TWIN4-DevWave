@csrf
<div class="grid gap-4 sm:grid-cols-2">
    <div>
        <label for="nom" class="mb-1.5 block text-sm font-medium text-cyan-100/80">Nom</label>
        <input id="nom" name="nom" value="{{ old('nom', $infrastructure?->nom) }}" required maxlength="255" class="w-full rounded-lg border border-cyan-400/20 bg-slate-950/40 px-3.5 py-2.5 text-sm text-white outline-none transition focus:border-cyan-400/50 focus:ring-2 focus:ring-cyan-400/20">
        @error('nom')<p class="mt-1 text-sm text-rose-400">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="type" class="mb-1.5 block text-sm font-medium text-cyan-100/80">Type</label>
        <input id="type" name="type" value="{{ old('type', $infrastructure?->type) }}" required maxlength="255" class="w-full rounded-lg border border-cyan-400/20 bg-slate-950/40 px-3.5 py-2.5 text-sm text-white outline-none transition focus:border-cyan-400/50 focus:ring-2 focus:ring-cyan-400/20">
        @error('type')<p class="mt-1 text-sm text-rose-400">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="localisation" class="mb-1.5 block text-sm font-medium text-cyan-100/80">Localisation</label>
        <input id="localisation" name="localisation" value="{{ old('localisation', $infrastructure?->localisation) }}" required maxlength="255" class="w-full rounded-lg border border-cyan-400/20 bg-slate-950/40 px-3.5 py-2.5 text-sm text-white outline-none transition focus:border-cyan-400/50 focus:ring-2 focus:ring-cyan-400/20">
        @error('localisation')<p class="mt-1 text-sm text-rose-400">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="etat" class="mb-1.5 block text-sm font-medium text-cyan-100/80">État</label>
        <input id="etat" name="etat" value="{{ old('etat', $infrastructure?->etat) }}" required maxlength="255" placeholder="En service, maintenance..." class="w-full rounded-lg border border-cyan-400/20 bg-slate-950/40 px-3.5 py-2.5 text-sm text-white outline-none transition focus:border-cyan-400/50 focus:ring-2 focus:ring-cyan-400/20">
        @error('etat')<p class="mt-1 text-sm text-rose-400">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="date_installation" class="mb-1.5 block text-sm font-medium text-cyan-100/80">Date d’installation</label>
        <input id="date_installation" type="date" name="date_installation" value="{{ old('date_installation', $infrastructure?->date_installation?->format('Y-m-d')) }}" required class="w-full rounded-lg border border-cyan-400/20 bg-slate-950/40 px-3.5 py-2.5 text-sm text-white outline-none transition focus:border-cyan-400/50 focus:ring-2 focus:ring-cyan-400/20">
        @error('date_installation')<p class="mt-1 text-sm text-rose-400">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="zone_id" class="mb-1.5 block text-sm font-medium text-cyan-100/80">Zone</label>
        <select id="zone_id" name="zone_id" required class="w-full rounded-lg border border-cyan-400/20 bg-slate-950/40 px-3.5 py-2.5 text-sm text-white outline-none transition focus:border-cyan-400/50 focus:ring-2 focus:ring-cyan-400/20">
            <option value="">Sélectionnez une zone</option>
            @foreach($zones as $zone)
                <option value="{{ $zone->id }}" @selected((string) old('zone_id', $infrastructure?->zone_id ?? $selectedZoneId ?? '') === (string) $zone->id)>{{ $zone->nom }}</option>
            @endforeach
        </select>
        @error('zone_id')<p class="mt-1 text-sm text-rose-400">{{ $message }}</p>@enderror
    </div>
</div>
<div class="flex justify-end gap-2 border-t border-white/10 pt-4 sm:col-span-2">
    <a href="{{ route('manager.infrastructures.index') }}" class="rounded-lg border border-cyan-400/20 px-4 py-2 text-sm font-medium text-cyan-100/70 hover:bg-white/5">Annuler</a>
    <button type="submit" class="rounded-lg bg-cyan-600 px-4 py-2 text-sm font-medium text-white hover:bg-cyan-500">Enregistrer</button>
</div>
