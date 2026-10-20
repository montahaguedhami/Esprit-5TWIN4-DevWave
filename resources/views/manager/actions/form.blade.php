@php
    $fieldValues = collect([
        'titre' => $action->titre ?? '',
        'description' => $action->description ?? '',
        'technicien_id' => $action->technicien_id ?? '',
        'date_prevue' => isset($action) ? $action->date_prevue?->format('Y-m-d') : '',
        'date_realisation' => isset($action) ? $action->date_realisation?->format('Y-m-d\TH:i') : '',
        'resultat' => $action->resultat ?? '',
    ])->map(function ($default, $field) {
        $value = old($field, $default);
        return is_scalar($value) ? $value : '';
    });
@endphp
<div>
    <label for="action_titre" class="block text-sm">Titre</label>
    <input id="action_titre" type="text" name="titre" data-validation-rules="required|max:255" value="{{ $fieldValues['titre'] }}" class="mt-1 w-full rounded p-2 bg-black/20">
    @error('titre') <p data-laravel-error-for="titre" class="mt-1 text-xs font-normal text-red-400">{{ $message }}</p> @enderror
</div>
<div>
    <label for="action_description" class="block text-sm">Description</label>
    <textarea id="action_description" name="description" rows="3" data-validation-rules="required|max:5000" class="mt-1 w-full rounded p-2 bg-black/20">{{ $fieldValues['description'] }}</textarea>
    @error('description') <p data-laravel-error-for="description" class="mt-1 text-xs font-normal text-red-400">{{ $message }}</p> @enderror
</div>
<div>
    <label for="technicien_id" class="block text-sm">Technicien responsable</label>
    <select id="technicien_id" name="technicien_id" data-validation-rules="required|exists" class="mt-1 w-full rounded p-2 bg-slate-900">
        <option value="">Choisir un technicien disponible</option>
        @foreach($techniciens ?? collect() as $technicien)
            <option value="{{ $technicien->id }}" @selected((string) old('technicien_id', $fieldValues['technicien_id']) === (string) $technicien->id)>
                {{ $technicien->nom }} · {{ $technicien->specialite }}
            </option>
        @endforeach
    </select>
    @if(($techniciens ?? collect())->isEmpty())
        <p class="mt-1 text-xs text-amber-300">Aucun technicien disponible pour le moment.</p>
    @endif
    @error('technicien_id') <p data-laravel-error-for="technicien_id" class="mt-1 text-xs font-normal text-red-400">{{ $message }}</p> @enderror
</div>
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <label for="date_prevue" class="block text-sm">Date pr&eacute;vue</label>
        <input id="date_prevue" type="date" name="date_prevue" data-validation-rules="required|date" value="{{ $fieldValues['date_prevue'] }}" class="mt-1 w-full min-w-0 rounded p-2 bg-black/20">
        @error('date_prevue') <p data-laravel-error-for="date_prevue" class="mt-1 text-xs font-normal text-red-400">{{ $message }}</p> @enderror
    </div>
    <div>
        <label for="date_realisation" class="block text-sm">Date de r&eacute;alisation (si terminée)</label>
        <input id="date_realisation" type="datetime-local" name="date_realisation" data-validation-rules="date|after-or-equal:date_prevue|required-if-terminee:statut" value="{{ $fieldValues['date_realisation'] }}" class="mt-1 w-full min-w-0 rounded p-2 bg-black/20">
        @error('date_realisation') <p data-laravel-error-for="date_realisation" class="mt-1 text-xs font-normal text-red-400">{{ $message }}</p> @enderror
    </div>
</div>
<div>
    <label for="action_statut" class="block text-sm">Statut</label>
    <select id="action_statut" name="statut" data-validation-rules="required|in:a_faire,en_cours,terminee" class="mt-1 w-full rounded p-2 bg-slate-900">
        <option value="" disabled @selected(old('statut', $action->statut ?? '') === '')>Choisir un statut</option>
        @foreach(['a_faire' => 'A faire', 'en_cours' => 'En cours', 'terminee' => 'Terminee'] as $value => $label)
            <option value="{{ $value }}" @selected(old('statut', $action->statut ?? 'a_faire') === $value)>{{ $label }}</option>
        @endforeach
    </select>
    @error('statut') <p data-laravel-error-for="statut" class="mt-1 text-xs font-normal text-red-400">{{ $message }}</p> @enderror
</div>
<div>
    <label for="resultat" class="block text-sm">R&eacute;sultat</label>
    <textarea id="resultat" name="resultat" rows="3" data-validation-rules="required|max:5000" class="mt-1 w-full rounded p-2 bg-black/20">{{ $fieldValues['resultat'] }}</textarea>
    @error('resultat') <p data-laravel-error-for="resultat" class="mt-1 text-xs font-normal text-red-400">{{ $message }}</p> @enderror
</div>