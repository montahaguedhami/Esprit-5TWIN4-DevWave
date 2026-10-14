@csrf
<div>
    <label for="nom" class="mb-1.5 block text-sm font-medium text-cyan-100/80">Nom</label>
    <input id="nom" name="nom" value="{{ old('nom', $zone?->nom) }}" required maxlength="255" class="w-full rounded-lg border border-cyan-400/20 bg-slate-950/40 px-3.5 py-2.5 text-sm text-white outline-none transition focus:border-cyan-400/50 focus:ring-2 focus:ring-cyan-400/20">
    @error('nom')<p class="mt-1 text-sm text-rose-400">{{ $message }}</p>@enderror
</div>
<div>
    <label for="localisation" class="mb-1.5 block text-sm font-medium text-cyan-100/80">Localisation</label>
    <input id="localisation" name="localisation" value="{{ old('localisation', $zone?->localisation) }}" required maxlength="255" class="w-full rounded-lg border border-cyan-400/20 bg-slate-950/40 px-3.5 py-2.5 text-sm text-white outline-none transition focus:border-cyan-400/50 focus:ring-2 focus:ring-cyan-400/20">
    @error('localisation')<p class="mt-1 text-sm text-rose-400">{{ $message }}</p>@enderror
</div>
<div>
    <label for="description" class="mb-1.5 block text-sm font-medium text-cyan-100/80">Description <span class="font-normal text-cyan-100/40">(facultative)</span></label>
    <textarea id="description" name="description" rows="3" class="w-full rounded-lg border border-cyan-400/20 bg-slate-950/40 px-3.5 py-2.5 text-sm text-white outline-none transition focus:border-cyan-400/50 focus:ring-2 focus:ring-cyan-400/20">{{ old('description', $zone?->description) }}</textarea>
    @error('description')<p class="mt-1 text-sm text-rose-400">{{ $message }}</p>@enderror
</div>
<div class="flex justify-end gap-2 border-t border-white/10 pt-4">
    <a href="{{ route('manager.zones.index') }}" class="rounded-lg border border-cyan-400/20 px-4 py-2 text-sm font-medium text-cyan-100/70 hover:bg-white/5">Annuler</a>
    <button type="submit" class="rounded-lg bg-cyan-600 px-4 py-2 text-sm font-medium text-white hover:bg-cyan-500">Enregistrer</button>
</div>
