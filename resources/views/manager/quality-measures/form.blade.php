@if($errors->any())
    <div class="mb-4 rounded-xl border border-red-500/40 bg-red-500/10 p-4 text-sm text-red-300">
        <div class="flex items-center gap-2 font-semibold text-red-200 mb-1">
            <i data-lucide="alert-circle" class="w-4 h-4"></i> Des erreurs de saisie ont été détectées :
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
        <label for="reference" class="block text-sm font-medium text-slate-300">
            Référence unique <span class="text-teal-400 font-bold">*</span>
        </label>
        <input 
            type="text"
            id="reference"
            name="reference" 
            placeholder="Ex: WQR-2026-001"
            value="{{ old('reference', $mesure->reference ?? '') }}" 
            class="mt-1 w-full rounded-xl border @error('reference') border-red-500/70 focus:border-red-400 @else border-slate-700 focus:border-teal-400 @enderror bg-slate-950 px-3 py-2 text-white placeholder-slate-500 focus:outline-none"
        >
        <div class="flex justify-between text-[11px] text-slate-400 mt-1">
            <span>Numéro de prélèvement</span>
            <span>2–50 car.</span>
        </div>
        @error('reference')
            <span class="mt-1 block text-xs text-red-400 font-medium">{{ $message }}</span>
        @enderror
    </div>

    <div>
        <label for="point_mesure_id" class="block text-sm font-medium text-slate-300">
            Point de prélèvement <span class="text-teal-400 font-bold">*</span>
        </label>
        <select 
            id="point_mesure_id"
            name="point_mesure_id" 
            class="mt-1 w-full rounded-xl border @error('point_mesure_id') border-red-500/70 focus:border-red-400 @else border-slate-700 focus:border-teal-400 @enderror bg-slate-950 px-3 py-2 text-white focus:outline-none"
        >
            <option value="" disabled @selected(!old('point_mesure_id', request('point_mesure_id', $mesure->point_mesure_id ?? '')))>Sélectionnez un point de prélèvement...</option>
            @foreach($points as $point)
                <option value="{{ $point->id }}" @selected((string) old('point_mesure_id', request('point_mesure_id', $mesure->point_mesure_id ?? '')) === (string) $point->id)>
                    {{ $point->nom }} ({{ $point->code }}) @if($point->zone) — {{ $point->zone }} @endif
                </option>
            @endforeach
        </select>
        <span class="text-[11px] text-slate-400 mt-1 block">Site géographique où l'eau a été prélevée</span>
        @error('point_mesure_id')
            <span class="mt-1 block text-xs text-red-400 font-medium">{{ $message }}</span>
        @enderror
    </div>

    <div>
        <label for="date_mesure" class="block text-sm font-medium text-slate-300">
            Date et heure de prélèvement <span class="text-teal-400 font-bold">*</span>
        </label>
        <input 
            type="datetime-local" 
            id="date_mesure"
            name="date_mesure" 
            value="{{ old('date_mesure', isset($mesure) ? $mesure->date_mesure->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i')) }}" 
            class="mt-1 w-full rounded-xl border @error('date_mesure') border-red-500/70 focus:border-red-400 @else border-slate-700 focus:border-teal-400 @enderror bg-slate-950 px-3 py-2 text-white focus:outline-none"
        >
        <span class="text-[11px] text-slate-400 mt-1 block">Horodatage précis de l'analyse</span>
        @error('date_mesure')
            <span class="mt-1 block text-xs text-red-400 font-medium">{{ $message }}</span>
        @enderror
    </div>

    <div>
        <label for="source_donnee" class="block text-sm font-medium text-slate-300">
            Provenance des données <span class="text-teal-400 font-bold">*</span>
        </label>
        <select 
            id="source_donnee"
            name="source_donnee" 
            class="mt-1 w-full rounded-xl border @error('source_donnee') border-red-500/70 focus:border-red-400 @else border-slate-700 focus:border-teal-400 @enderror bg-slate-950 px-3 py-2 text-white focus:outline-none"
        >
            <option value="manuel" @selected(old('source_donnee', $mesure->source_donnee ?? 'manuel') === 'manuel')>Manuel (saisie terrain technicien)</option>
            <option value="laboratoire" @selected(old('source_donnee', $mesure->source_donnee ?? 'manuel') === 'laboratoire')>Laboratoire certifié</option>
            <option value="simule" @selected(old('source_donnee', $mesure->source_donnee ?? 'manuel') === 'simule')>Simulé (démonstration / test)</option>
        </select>
        <span class="text-[11px] text-slate-400 mt-1 block">Canal d'acquisition de la mesure</span>
        @error('source_donnee')
            <span class="mt-1 block text-xs text-red-400 font-medium">{{ $message }}</span>
        @enderror
    </div>

    {{-- Parameters Section Header --}}
    <div class="sm:col-span-2 pt-2 border-t border-slate-800">
        <h3 class="text-xs font-bold uppercase tracking-wider text-teal-400">Paramètres physico-chimiques</h3>
        <p class="text-xs text-slate-400 mt-0.5">Valeurs quantitatives issues du capteur ou du rapport d'analyse de laboratoire.</p>
    </div>

    <div>
        <label for="ph" class="block text-sm font-medium text-slate-300">
            pH (Potentiel Hydrogène) <span class="text-teal-400 font-bold">*</span>
        </label>
        <input 
            type="number" 
            id="ph"
            name="ph" 
            step="0.01" 
            placeholder="Ex: 7.2"
            value="{{ old('ph', $mesure->ph ?? '') }}" 
            class="mt-1 w-full rounded-xl border @error('ph') border-red-500/70 focus:border-red-400 @else border-slate-700 focus:border-teal-400 @enderror bg-slate-950 px-3 py-2 text-white focus:outline-none"
        >
        <div class="flex justify-between text-[11px] text-slate-400 mt-1">
            <span>Échelle 0 à 14</span>
            <span class="text-emerald-400">Norme : 6.5 – 8.5</span>
        </div>
        @error('ph')
            <span class="mt-1 block text-xs text-red-400 font-medium">{{ $message }}</span>
        @enderror
    </div>

    <div>
        <label for="turbidite" class="block text-sm font-medium text-slate-300">
            Turbidité (NTU) <span class="text-teal-400 font-bold">*</span>
        </label>
        <input 
            type="number" 
            id="turbidite"
            name="turbidite" 
            step="0.01" 
            placeholder="Ex: 0.45"
            value="{{ old('turbidite', $mesure->turbidite ?? '') }}" 
            class="mt-1 w-full rounded-xl border @error('turbidite') border-red-500/70 focus:border-red-400 @else border-slate-700 focus:border-teal-400 @enderror bg-slate-950 px-3 py-2 text-white focus:outline-none"
        >
        <div class="flex justify-between text-[11px] text-slate-400 mt-1">
            <span>Valeur positive (&ge; 0 NTU)</span>
            <span class="text-emerald-400">Norme : &lt; 1.0 NTU</span>
        </div>
        @error('turbidite')
            <span class="mt-1 block text-xs text-red-400 font-medium">{{ $message }}</span>
        @enderror
    </div>

    <div>
        <label for="chlore_residuel" class="block text-sm font-medium text-slate-300">
            Chlore résiduel (mg/L) <span class="text-teal-400 font-bold">*</span>
        </label>
        <input 
            type="number" 
            id="chlore_residuel"
            name="chlore_residuel" 
            step="0.001" 
            placeholder="Ex: 0.65"
            value="{{ old('chlore_residuel', $mesure->chlore_residuel ?? '') }}" 
            class="mt-1 w-full rounded-xl border @error('chlore_residuel') border-red-500/70 focus:border-red-400 @else border-slate-700 focus:border-teal-400 @enderror bg-slate-950 px-3 py-2 text-white focus:outline-none"
        >
        <div class="flex justify-between text-[11px] text-slate-400 mt-1">
            <span>Désinfection réseau</span>
            <span class="text-emerald-400">Norme : 0.2 – 2.0 mg/L</span>
        </div>
        @error('chlore_residuel')
            <span class="mt-1 block text-xs text-red-400 font-medium">{{ $message }}</span>
        @enderror
    </div>

    <div>
        <label for="plomb" class="block text-sm font-medium text-slate-300">
            Plomb Pb (&micro;g/L ou ppb) <span class="text-teal-400 font-bold">*</span>
        </label>
        <input 
            type="number" 
            id="plomb"
            name="plomb" 
            step="0.001" 
            placeholder="Ex: 1.2"
            value="{{ old('plomb', $mesure->plomb ?? '') }}" 
            class="mt-1 w-full rounded-xl border @error('plomb') border-red-500/70 focus:border-red-400 @else border-slate-700 focus:border-teal-400 @enderror bg-slate-950 px-3 py-2 text-white focus:outline-none"
        >
        <div class="flex justify-between text-[11px] text-slate-400 mt-1">
            <span>Métal lourd toxique</span>
            <span class="text-emerald-400">Seuil d'action : &le; 15 ppb</span>
        </div>
        @error('plomb')
            <span class="mt-1 block text-xs text-red-400 font-medium">{{ $message }}</span>
        @enderror
    </div>

    <div>
        <label for="nitrates" class="block text-sm font-medium text-slate-300">
            Nitrates NO<sub>3</sub> (mg/L) <span class="text-teal-400 font-bold">*</span>
        </label>
        <input 
            type="number" 
            id="nitrates"
            name="nitrates" 
            step="0.01" 
            placeholder="Ex: 8.5"
            value="{{ old('nitrates', $mesure->nitrates ?? '') }}" 
            class="mt-1 w-full rounded-xl border @error('nitrates') border-red-500/70 focus:border-red-400 @else border-slate-700 focus:border-teal-400 @enderror bg-slate-950 px-3 py-2 text-white focus:outline-none"
        >
        <div class="flex justify-between text-[11px] text-slate-400 mt-1">
            <span>Contamination agricole</span>
            <span class="text-emerald-400">Guide : &le; 50 mg/L</span>
        </div>
        @error('nitrates')
            <span class="mt-1 block text-xs text-red-400 font-medium">{{ $message }}</span>
        @enderror
    </div>

    <div>
        <label for="verifier" class="block text-sm font-medium text-slate-300">
            Vérificateur ou Laboratoire <span class="text-xs text-slate-500">(optionnel)</span>
        </label>
        <input 
            type="text"
            id="verifier"
            name="verifier" 
            placeholder="Ex: Labo Central des Eaux Tunis"
            value="{{ old('verifier', $mesure->verifier ?? '') }}" 
            class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-white placeholder-slate-500 focus:border-teal-400 focus:outline-none"
        >
        <span class="text-[11px] text-slate-400 mt-1 block">Nom de l'agent ou de l'organisme certificateur</span>
        @error('verifier')
            <span class="mt-1 block text-xs text-red-400 font-medium">{{ $message }}</span>
        @enderror
    </div>

    <div class="sm:col-span-2 pt-2 border-t border-slate-800">
        <input type="hidden" name="is_verified" value="0">
        <label class="inline-flex items-center gap-2.5 text-sm text-slate-300 cursor-pointer">
            <input 
                type="checkbox" 
                name="is_verified" 
                value="1" 
                @checked(old('is_verified', $mesure->is_verified ?? false))
                class="w-4 h-4 rounded border-slate-700 bg-slate-950 text-teal-500 focus:ring-teal-400 focus:ring-offset-slate-900"
            > 
            <span class="font-medium">Mesure formellement vérifiée et certifiée conforme</span>
        </label>
        <p class="text-xs text-slate-400 ml-6 mt-0.5">Seuls les gestionnaires et administrateurs peuvent certifier une mesure.</p>
        @error('is_verified')
            <span class="ml-6 block text-xs text-red-400 font-medium">{{ $message }}</span>
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

    function validateMesureForm() {
        const problèmes = [];

        const ph = document.getElementById('ph');
        hideFieldErrors(ph);
        if (ph.value !== '' && (Number(ph.value) < 0 || Number(ph.value) > 14)) {
            showFieldError(ph, 'Le pH doit être compris dans l’échelle physico-chimique de 0 à 14.');
            problèmes.push('ph');
        }

        const turbidite = document.getElementById('turbidite');
        hideFieldErrors(turbidite);
        if (turbidite.value !== '' && (Number(turbidite.value) < 0 || Number(turbidite.value) > 1000)) {
            showFieldError(turbidite, 'La turbidité (NTU) ne peut pas dépasser 1000.');
            problèmes.push('turbidite');
        }

        const chloreResiduel = document.getElementById('chlore_residuel');
        hideFieldErrors(chloreResiduel);
        if (chloreResiduel.value !== '' && (Number(chloreResiduel.value) < 0 || Number(chloreResiduel.value) > 100)) {
            showFieldError(chloreResiduel, 'Le chlore résiduel (mg/L) ne peut pas dépasser 100.');
            problèmes.push('chlore_residuel');
        }

        const plomb = document.getElementById('plomb');
        hideFieldErrors(plomb);
        if (plomb.value !== '' && (Number(plomb.value) < 0 || Number(plomb.value) > 10000)) {
            showFieldError(plomb, 'La concentration en plomb (ppb) ne peut pas dépasser 10000.');
            problèmes.push('plomb');
        }

        const nitrates = document.getElementById('nitrates');
        hideFieldErrors(nitrates);
        if (nitrates.value !== '' && (Number(nitrates.value) < 0 || Number(nitrates.value) > 1000)) {
            showFieldError(nitrates, 'Les nitrates (mg/L) ne peuvent pas dépasser 1000.');
            problèmes.push('nitrates');
        }

        if (problèmes.length > 0) {
            return false;
        }

        return true;
    }

    const container = document.getElementById('ph')?.closest('form');

    if (container) {
        container.addEventListener('submit', function (event) {
            if (!validateMesureForm()) {
                event.preventDefault();
            }
        });
    }
});
</script>
