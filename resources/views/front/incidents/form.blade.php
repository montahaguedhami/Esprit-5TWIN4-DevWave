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
<div>
    <label for="titre" class="block text-sm font-semibold text-slate-700">Titre</label>
    <input id="titre" type="text" name="titre" data-validation-rules="required|max:255" value="{{ $fieldValues['titre'] }}" class="mt-1 w-full min-w-0 rounded-xl border border-slate-300 p-3 bg-white text-slate-900 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-100">
    @error('titre') <p data-laravel-error-for="titre" class="mt-1 text-xs font-normal text-red-600">{{ $message }}</p> @enderror
</div>
<div>
    <label for="description" class="block text-sm font-semibold text-slate-700">Description</label>
    <textarea id="description" name="description" rows="4" data-validation-rules="required|max:5000" class="mt-1 w-full rounded-xl border border-slate-300 p-3 bg-white text-slate-900 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-100">{{ $fieldValues['description'] }}</textarea>
    @error('description') <p data-laravel-error-for="description" class="mt-1 text-xs font-normal text-red-600">{{ $message }}</p> @enderror
</div>
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <label for="type" class="block text-sm font-semibold text-slate-700">Type</label>
        <select id="type" name="type" data-validation-rules="required|in:fuite,pollution,panne,rupture,contamination" class="mt-1 w-full rounded-xl border border-slate-300 p-3 bg-white text-slate-900 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-100">
            <option value="" disabled @selected(old('type', $incident->type ?? '') === '')>Choisir un type</option>
            @foreach(['fuite' => 'Fuite', 'pollution' => 'Pollution', 'panne' => 'Panne', 'rupture' => 'Rupture', 'contamination' => 'Contamination'] as $value => $label)
                <option value="{{ $value }}" @selected(old('type', $incident->type ?? 'fuite') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        @error('type') <p data-laravel-error-for="type" class="mt-1 text-xs font-normal text-red-600">{{ $message }}</p> @enderror
    </div>
    <div>
        <label for="gravite" class="block text-sm font-semibold text-slate-700">Gravit&eacute;</label>
        <select id="gravite" name="gravite" data-validation-rules="required|in:faible,moyenne,elevee,critique" class="mt-1 w-full rounded-xl border border-slate-300 p-3 bg-white text-slate-900 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-100">
            <option value="" disabled @selected(old('gravite', $incident->gravite ?? '') === '')>Choisir une gravit&eacute;</option>
            @foreach(['faible' => 'Faible', 'moyenne' => 'Moyenne', 'elevee' => 'Elevee', 'critique' => 'Critique'] as $value => $label)
                <option value="{{ $value }}" @selected(old('gravite', $incident->gravite ?? 'faible') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        @error('gravite') <p data-laravel-error-for="gravite" class="mt-1 text-xs font-normal text-red-600">{{ $message }}</p> @enderror
    </div>
</div>
<div>
    <label for="date_signalement" class="block text-sm font-semibold text-slate-700">Date de signalement</label>
    <input id="date_signalement" type="datetime-local" name="date_signalement" data-validation-rules="required|date|before-now" value="{{ $fieldValues['date_signalement'] }}" class="mt-1 w-full min-w-0 rounded-xl border border-slate-300 p-3 bg-white text-slate-900 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-100">
    @error('date_signalement') <p data-laravel-error-for="date_signalement" class="mt-1 text-xs font-normal text-red-600">{{ $message }}</p> @enderror
</div>
<div>
    <label for="localisation" class="block text-sm font-semibold text-slate-700">Localisation</label>
    <input id="localisation" type="text" name="localisation" data-validation-rules="required|max:255" value="{{ $fieldValues['localisation'] }}" autocomplete="address-line1" placeholder="Rue, quartier, ville" class="mt-1 w-full rounded-xl border border-slate-300 p-3 bg-white text-slate-900 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-100">
    <input id="incident-latitude" type="hidden" name="latitude" value="{{ old('latitude', $incident->latitude ?? '') }}">
    <input id="incident-longitude" type="hidden" name="longitude" value="{{ old('longitude', $incident->longitude ?? '') }}">
    @error('localisation') <p data-laravel-error-for="localisation" class="mt-1 text-xs font-normal text-red-600">{{ $message }}</p> @enderror
    <div class="mt-3 flex flex-wrap items-center gap-3">
        <button id="incident-locate-address" type="button" class="inline-flex items-center gap-2 rounded-lg border border-cyan-700 px-3 py-2 text-sm font-semibold text-cyan-800 hover:bg-cyan-50">
            <i data-lucide="map-pin" class="h-4 w-4"></i>Afficher sur la carte
        </button>
        <span id="incident-map-status" role="status" class="text-xs text-slate-500">Recherche une adresse ou clique sur la carte pour choisir un point.</span>
    </div>
    <div id="citizen-incident-map" class="mt-3 w-full overflow-hidden rounded-xl border border-slate-200 bg-slate-100" style="height: 360px"></div>
</div>

<div class="pt-2 border-t border-slate-200">
    <label for="preuve" class="block text-sm font-semibold text-slate-700">Preuve jointe (facultatif)</label>
    <input id="preuve" type="file" name="preuve" data-validation-rules="mimes:jpg,jpeg,png,webp,pdf|max:4096" accept=".jpg,.jpeg,.png,.webp,.pdf,image/jpeg,image/png,image/webp,application/pdf" class="mt-1 block w-full text-sm text-slate-600 file:mr-4 file:rounded-xl file:border-0 file:bg-cyan-600 file:px-4 file:py-2.5 file:text-sm file:font-semibold file:text-white hover:file:bg-cyan-500" />
    @error('preuve') <p data-laravel-error-for="preuve" class="mt-1 text-xs font-normal text-red-600">{{ $message }}</p> @enderror
    <p class="mt-1 text-xs text-slate-500">Formats acceptés : JPG, JPEG, PNG, WEBP, PDF. Taille maximale : 4 Mo.</p>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2 border-t border-slate-200">
    <div>
        <label for="incident_zone_id" class="block text-sm font-semibold text-slate-700">Zone géographique (optionnel)</label>
        <select id="incident_zone_id" name="zone_id" data-validation-rules="exists" class="mt-1 w-full rounded-xl border border-slate-300 p-3 bg-white text-slate-900 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-100">
            <option value="">Toutes les zones / Non spécifiée</option>
            @if(isset($zones))
                @foreach($zones as $zoneOpt)
                    <option value="{{ $zoneOpt->id }}" @selected((string) old('zone_id', $incident->zone_id ?? '') === (string) $zoneOpt->id)>
                        {{ $zoneOpt->nom }}
                    </option>
                @endforeach
            @endif
        </select>
        @error('zone_id') <p data-laravel-error-for="zone_id" class="mt-1 text-xs font-normal text-red-600">{{ $message }}</p> @enderror
    </div>
    <div>
        <label for="incident_infrastructure_id" class="block text-sm font-semibold text-slate-700">Équipement concerné (optionnel)</label>
        <select id="incident_infrastructure_id" name="infrastructure_id" data-validation-rules="exists" class="mt-1 w-full rounded-xl border border-slate-300 p-3 bg-white text-slate-900 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-100">
            <option value="">Aucun équipement spécifique</option>
            @if(isset($infrastructures))
                @foreach($infrastructures as $infraOpt)
                    <option value="{{ $infraOpt->id }}" data-zone-id="{{ $infraOpt->zone_id }}" @selected((string) old('infrastructure_id', $incident->infrastructure_id ?? '') === (string) $infraOpt->id)>
                        {{ $infraOpt->nom }}
                    </option>
                @endforeach
            @endif
        </select>
        @error('infrastructure_id') <p data-laravel-error-for="infrastructure_id" class="mt-1 text-xs font-normal text-red-600">{{ $message }}</p> @enderror
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

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha384-sHL9NAb7lN7rfvG5lfHpm643Xkcjzp4jFvuavGOndn6pjVqS6ny56CAt3nsEVT4H" crossorigin="anonymous">
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha384-cxOPjt7s7Iz04uaHJceBmS+qpjv2JkIHNVcuOrM+YHwZOmJGBXI00mdUXEq65HTH" crossorigin="anonymous"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const mapElement = document.getElementById('citizen-incident-map');
        const addressInput = document.getElementById('localisation');
        const latitudeInput = document.getElementById('incident-latitude');
        const longitudeInput = document.getElementById('incident-longitude');
        const locateButton = document.getElementById('incident-locate-address');
        const statusElement = document.getElementById('incident-map-status');
        if (!mapElement || !addressInput || !latitudeInput || !longitudeInput || !locateButton || typeof L === 'undefined') return;

        const savedLatitude = Number(latitudeInput.value);
        const savedLongitude = Number(longitudeInput.value);
        const hasSavedPoint = latitudeInput.value !== '' && longitudeInput.value !== '' && Number.isFinite(savedLatitude) && Number.isFinite(savedLongitude);
        const map = L.map(mapElement).setView(hasSavedPoint ? [savedLatitude, savedLongitude] : [36.8065, 10.1815], hasSavedPoint ? 16 : 11);
        let marker = null;
        let updatingAddressFromMap = false;

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
        }).addTo(map);

        function setPoint(latitude, longitude) {
            const point = [latitude, longitude];
            latitudeInput.value = latitude;
            longitudeInput.value = longitude;
            if (marker) map.removeLayer(marker);
            marker = L.marker(point).addTo(map);
            marker.bindPopup(addressInput.value || 'Localisation de l’incident').openPopup();
            map.setView(point, 16);
            map.invalidateSize();
        }

        async function findAddress() {
            const address = addressInput.value.trim();
            if (!address) {
                statusElement.textContent = 'Saisis une localisation avant de lancer la recherche.';
                return;
            }

            statusElement.textContent = 'Recherche de la localisation…';
            try {
                const url = 'https://nominatim.openstreetmap.org/search?format=jsonv2&limit=1&q=' + encodeURIComponent(address + ', Tunisie');
                const response = await fetch(url, { headers: { 'Accept-Language': 'fr' } });
                if (!response.ok) throw new Error('Recherche indisponible');
                const results = await response.json();
                if (!results.length) throw new Error('Adresse introuvable');
                setPoint(Number(results[0].lat), Number(results[0].lon));
                statusElement.textContent = 'Localisation affichée sur la carte.';
            } catch (error) {
                statusElement.textContent = 'Localisation introuvable. Clique directement sur la carte pour choisir un point.';
            }
        }

        locateButton.addEventListener('click', findAddress);
        addressInput.addEventListener('input', function () {
            if (updatingAddressFromMap) return;
            latitudeInput.value = '';
            longitudeInput.value = '';
            if (marker) {
                map.removeLayer(marker);
                marker = null;
            }
        });
        map.on('click', async function (event) {
            setPoint(event.latlng.lat, event.latlng.lng);
            statusElement.textContent = 'Recherche de l’adresse du point…';
            try {
                const params = new URLSearchParams({
                    format: 'jsonv2',
                    lat: event.latlng.lat,
                    lon: event.latlng.lng,
                });
                const response = await fetch('https://nominatim.openstreetmap.org/reverse?' + params, {
                    headers: { 'Accept-Language': 'fr' },
                });
                if (!response.ok) throw new Error('Adresse indisponible');
                const result = await response.json();
                if (result.display_name) {
                    updatingAddressFromMap = true;
                    addressInput.value = result.display_name;
                    addressInput.dispatchEvent(new Event('input', { bubbles: true }));
                    updatingAddressFromMap = false;
                }
                setPoint(event.latlng.lat, event.latlng.lng);
                statusElement.textContent = 'Localisation sélectionnée.';
            } catch (error) {
                statusElement.textContent = 'Point sélectionné. Complète la localisation dans le champ.';
            }
        });

        if (hasSavedPoint) {
            setPoint(savedLatitude, savedLongitude);
            statusElement.textContent = 'Localisation enregistrée affichée sur la carte.';
        } else if (addressInput.value.trim()) {
            findAddress();
        }
    });
</script>
@endpush
