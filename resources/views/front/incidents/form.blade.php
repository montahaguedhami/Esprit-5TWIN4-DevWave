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
    <div role="alert" class="p-4 bg-red-50 border border-red-200 text-red-800 rounded-xl">
        <ul class="list-disc pl-5">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
<div>
    <label for="titre" class="block text-sm font-semibold text-slate-700">Titre</label>
    <input id="titre" type="text" name="titre" maxlength="255" value="{{ $fieldValues['titre'] }}" class="mt-1 w-full min-w-0 rounded-xl border border-slate-300 p-3 bg-white text-slate-900 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-100" required>
</div>
<div>
    <label for="description" class="block text-sm font-semibold text-slate-700">Description</label>
    <textarea id="description" name="description" rows="4" maxlength="5000" class="mt-1 w-full rounded-xl border border-slate-300 p-3 bg-white text-slate-900 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-100" required>{{ $fieldValues['description'] }}</textarea>
</div>
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <label for="type" class="block text-sm font-semibold text-slate-700">Type</label>
        <select id="type" name="type" class="mt-1 w-full rounded-xl border border-slate-300 p-3 bg-white text-slate-900 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-100" required>
            <option value="" disabled @selected(old('type', $incident->type ?? '') === '')>Choisir un type</option>
            @foreach(['fuite' => 'Fuite', 'pollution' => 'Pollution', 'panne' => 'Panne', 'rupture' => 'Rupture', 'contamination' => 'Contamination'] as $value => $label)
                <option value="{{ $value }}" @selected(old('type', $incident->type ?? 'fuite') === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label for="gravite" class="block text-sm font-semibold text-slate-700">Gravit&eacute;</label>
        <select id="gravite" name="gravite" class="mt-1 w-full rounded-xl border border-slate-300 p-3 bg-white text-slate-900 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-100" required>
            <option value="" disabled @selected(old('gravite', $incident->gravite ?? '') === '')>Choisir une gravit&eacute;</option>
            @foreach(['faible' => 'Faible', 'moyenne' => 'Moyenne', 'elevee' => 'Elevee', 'critique' => 'Critique'] as $value => $label)
                <option value="{{ $value }}" @selected(old('gravite', $incident->gravite ?? 'faible') === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
</div>
@isset($incident)
    <div>
        <label for="statut" class="block text-sm font-semibold text-slate-700">Statut</label>
        <select id="statut" name="statut" class="mt-1 w-full rounded-xl border border-slate-300 p-3 bg-white text-slate-900 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-100" required>
            <option value="" disabled @selected(old('statut', $incident->statut) === '')>Choisir un statut</option>
            @foreach(['signale' => 'Signale', 'en_cours' => 'En cours', 'resolu' => 'Resolu', 'cloture' => 'Cloture'] as $value => $label)
                <option value="{{ $value }}" @selected(old('statut', $incident->statut) === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
@endisset
<div>
    <label for="date_signalement" class="block text-sm font-semibold text-slate-700">Date de signalement</label>
    <input id="date_signalement" type="datetime-local" name="date_signalement" value="{{ $fieldValues['date_signalement'] }}" class="mt-1 w-full min-w-0 rounded-xl border border-slate-300 p-3 bg-white text-slate-900 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-100" required>
</div>
<div>
    <label for="localisation" class="block text-sm font-semibold text-slate-700">Localisation (adresse / lieu-dit)</label>
    <input id="localisation" type="text" name="localisation" maxlength="255" value="{{ $fieldValues['localisation'] }}" class="mt-1 w-full rounded-xl border border-slate-300 p-3 bg-white text-slate-900 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-100" required>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2 border-t border-slate-200">
    <div>
        <label for="incident_zone_id" class="block text-sm font-semibold text-slate-700">Zone géographique (optionnel)</label>
        <select id="incident_zone_id" name="zone_id" class="mt-1 w-full rounded-xl border border-slate-300 p-3 bg-white text-slate-900 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-100">
            <option value="">Toutes les zones / Non spécifiée</option>
            @if(isset($zones))
                @foreach($zones as $zoneOpt)
                    <option value="{{ $zoneOpt->id }}" @selected((string) old('zone_id', $incident->zone_id ?? '') === (string) $zoneOpt->id)>
                        {{ $zoneOpt->nom }}
                    </option>
                @endforeach
            @endif
        </select>
    </div>
    <div>
        <label for="incident_infrastructure_id" class="block text-sm font-semibold text-slate-700">Équipement concerné (optionnel)</label>
        <select id="incident_infrastructure_id" name="infrastructure_id" class="mt-1 w-full rounded-xl border border-slate-300 p-3 bg-white text-slate-900 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-100">
            <option value="">Aucun équipement spécifique</option>
            @if(isset($infrastructures))
                @foreach($infrastructures as $infraOpt)
                    <option value="{{ $infraOpt->id }}" data-zone-id="{{ $infraOpt->zone_id }}" @selected((string) old('infrastructure_id', $incident->infrastructure_id ?? '') === (string) $infraOpt->id)>
                        {{ $infraOpt->nom }}
                    </option>
                @endforeach
            @endif
        </select>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const zoneSelect = document.getElementById('incident_zone_id');
        const infraSelect = document.getElementById('incident_infrastructure_id');

        if (!zoneSelect || !infraSelect) return;

        function filterInfrastructures() {
            const selectedZone = zoneSelect.value;
            const options = infraSelect.querySelectorAll('option');

            options.forEach(opt => {
                if (!opt.value) return; // Garder l'option vide
                const optZone = opt.getAttribute('data-zone-id');
                if (!selectedZone || optZone === selectedZone) {
                    opt.style.display = '';
                    opt.disabled = false;
                } else {
                    opt.style.display = 'none';
                    opt.disabled = true;
                    if (opt.selected) {
                        infraSelect.value = '';
                    }
                }
            });
        }

        zoneSelect.addEventListener('change', filterInfrastructures);
        filterInfrastructures();
    });
</script>
