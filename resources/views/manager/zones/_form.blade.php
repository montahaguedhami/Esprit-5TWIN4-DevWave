@csrf
<div>
    <label for="nom" class="mb-1.5 block text-sm font-medium text-slate-700">Nom</label>
    <input id="nom" name="nom" value="{{ old('nom', $zone?->nom) }}" required maxlength="255" class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 outline-none transition focus:border-cyan-600 focus:ring-2 focus:ring-cyan-100">
    @error('nom')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
</div>
<div>
    <label for="localisation" class="mb-1.5 block text-sm font-medium text-slate-700">Localisation</label>
    <input id="localisation" name="localisation" value="{{ old('localisation', $zone?->localisation) }}" required maxlength="255" class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 outline-none transition focus:border-cyan-600 focus:ring-2 focus:ring-cyan-100">
    @error('localisation')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
</div>
<div>
    <label for="description" class="mb-1.5 block text-sm font-medium text-slate-700">Description <span class="font-normal text-slate-400">(facultative)</span></label>
    <textarea id="description" name="description" rows="3" class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 outline-none transition focus:border-cyan-600 focus:ring-2 focus:ring-cyan-100">{{ old('description', $zone?->description) }}</textarea>
    @error('description')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
</div>
<div class="flex justify-end gap-2 border-t border-slate-100 pt-4">
    <a href="{{ route('manager.zones.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">Annuler</a>
    <button type="submit" class="rounded-lg bg-cyan-700 px-4 py-2 text-sm font-medium text-white hover:bg-cyan-800">Enregistrer</button>
</div>
