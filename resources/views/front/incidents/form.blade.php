@php
    $fieldValues = collect([
        'titre' => $incident->titre ?? '',
        'description' => $incident->description ?? '',
        'date_signalement' => isset($incident) ? $incident->date_signalement?->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i'),
        'localisation' => $incident->localisation ?? '',
    ])->map(function ($default, $field) {
        $value = old($field, $default);
        return is_scalar($value) ? $value : '';
    });
@endphp
@if($errors->any())
    <div role="alert" class="p-3 bg-red-600 text-white rounded">
        <ul class="list-disc pl-5">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
<div>
    <label for="titre" class="block text-sm">Titre</label>
    <input id="titre" type="text" name="titre" maxlength="255" value="{{ $fieldValues['titre'] }}" class="mt-1 w-full min-w-0 rounded p-2 bg-black/20" required>
</div>
<div>
    <label for="description" class="block text-sm">Description</label>
    <textarea id="description" name="description" rows="4" maxlength="5000" class="mt-1 w-full rounded p-2 bg-black/20" required>{{ $fieldValues['description'] }}</textarea>
</div>
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <label for="type" class="block text-sm">Type</label>
        <select id="type" name="type" class="mt-1 w-full rounded p-2 bg-slate-900" required>
            <option value="" disabled @selected(old('type', $incident->type ?? '') === '')>Choisir un type</option>
            @foreach(['fuite' => 'Fuite', 'pollution' => 'Pollution', 'panne' => 'Panne', 'rupture' => 'Rupture', 'contamination' => 'Contamination'] as $value => $label)
                <option value="{{ $value }}" @selected(old('type', $incident->type ?? 'fuite') === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label for="gravite" class="block text-sm">Gravit&eacute;</label>
        <select id="gravite" name="gravite" class="mt-1 w-full rounded p-2 bg-slate-900" required>
            <option value="" disabled @selected(old('gravite', $incident->gravite ?? '') === '')>Choisir une gravit&eacute;</option>
            @foreach(['faible' => 'Faible', 'moyenne' => 'Moyenne', 'elevee' => 'Elevee', 'critique' => 'Critique'] as $value => $label)
                <option value="{{ $value }}" @selected(old('gravite', $incident->gravite ?? 'faible') === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
</div>
@isset($incident)
    <div>
        <label for="statut" class="block text-sm">Statut</label>
        <select id="statut" name="statut" class="mt-1 w-full rounded p-2 bg-slate-900" required>
            <option value="" disabled @selected(old('statut', $incident->statut) === '')>Choisir un statut</option>
            @foreach(['signale' => 'Signale', 'en_cours' => 'En cours', 'resolu' => 'Resolu', 'cloture' => 'Cloture'] as $value => $label)
                <option value="{{ $value }}" @selected(old('statut', $incident->statut) === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
@endisset
<div>
    <label for="date_signalement" class="block text-sm">Date de signalement</label>
    <input id="date_signalement" type="datetime-local" name="date_signalement" value="{{ $fieldValues['date_signalement'] }}" class="mt-1 w-full min-w-0 rounded p-2 bg-black/20" required>
</div>
<div>
    <label for="localisation" class="block text-sm">Localisation</label>
    <input id="localisation" type="text" name="localisation" maxlength="255" value="{{ $fieldValues['localisation'] }}" class="mt-1 w-full rounded p-2 bg-black/20" required>
</div>