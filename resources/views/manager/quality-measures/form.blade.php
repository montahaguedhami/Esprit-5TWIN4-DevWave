<div class="grid gap-4 sm:grid-cols-2">
    <label class="text-sm text-slate-300">Référence unique
        <input name="reference" required maxlength="50" value="{{ old('reference', $mesure->reference ?? '') }}" class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-white">
        @error('reference')<span class="text-xs text-red-300">{{ $message }}</span>@enderror
    </label>
    <label class="text-sm text-slate-300">Point de prélèvement
        <select name="point_mesure_id" required class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-white">
            @foreach($points as $point)
                <option value="{{ $point->id }}" @selected((string) old('point_mesure_id', request('point_mesure_id', $mesure->point_mesure_id ?? '')) === (string) $point->id)>{{ $point->nom }} ({{ $point->code }})</option>
            @endforeach
        </select>
        @error('point_mesure_id')<span class="text-xs text-red-300">{{ $message }}</span>@enderror
    </label>
    <label class="text-sm text-slate-300">Date de mesure
        <input type="datetime-local" name="date_mesure" required value="{{ old('date_mesure', isset($mesure) ? $mesure->date_mesure->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i')) }}" class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-white">
        @error('date_mesure')<span class="text-xs text-red-300">{{ $message }}</span>@enderror
    </label>
    <label class="text-sm text-slate-300">pH (0–14)
        <input type="number" name="ph" min="0" max="14" step="0.01" required value="{{ old('ph', $mesure->ph ?? '') }}" class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-white">
        @error('ph')<span class="text-xs text-red-300">{{ $message }}</span>@enderror
    </label>
    <label class="text-sm text-slate-300">Turbidité (NTU)
        <input type="number" name="turbidite" min="0" step="0.01" required value="{{ old('turbidite', $mesure->turbidite ?? '') }}" class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-white">
        @error('turbidite')<span class="text-xs text-red-300">{{ $message }}</span>@enderror
    </label>
    <label class="text-sm text-slate-300">Chlore résiduel (mg/L)
        <input type="number" name="chlore_residuel" min="0" step="0.001" required value="{{ old('chlore_residuel', $mesure->chlore_residuel ?? '') }}" class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-white">
        @error('chlore_residuel')<span class="text-xs text-red-300">{{ $message }}</span>@enderror
    </label>
    <label class="text-sm text-slate-300">Plomb (ppb)
        <input type="number" name="plomb" min="0" step="0.001" required value="{{ old('plomb', $mesure->plomb ?? '') }}" class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-white">
        @error('plomb')<span class="text-xs text-red-300">{{ $message }}</span>@enderror
    </label>
    <label class="text-sm text-slate-300">Nitrates (mg/L)
        <input type="number" name="nitrates" min="0" step="0.01" required value="{{ old('nitrates', $mesure->nitrates ?? '') }}" class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-white">
        @error('nitrates')<span class="text-xs text-red-300">{{ $message }}</span>@enderror
    </label>
    <label class="text-sm text-slate-300">Vérificateur / laboratoire
        <input name="verifier" maxlength="255" value="{{ old('verifier', $mesure->verifier ?? '') }}" class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-white">
    </label>
    <div class="sm:col-span-2">
        <input type="hidden" name="is_verified" value="0">
        <label class="inline-flex items-center gap-2 text-sm text-slate-300"><input type="checkbox" name="is_verified" value="1" @checked(old('is_verified', $mesure->is_verified ?? false))> Mesure vérifiée</label>
        @error('is_verified')<span class="ml-2 text-xs text-red-300">{{ $message }}</span>@enderror
    </div>
</div>
