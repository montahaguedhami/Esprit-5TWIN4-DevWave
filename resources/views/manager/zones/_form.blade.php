@csrf

@php
    $statuts = $statuts ?? \App\Enums\ZoneStatut::cases();
    $users = $users ?? \App\Models\User::orderBy('name')->get(['id', 'name', 'email']);
@endphp

{{-- Bannière d'erreurs récapitulative dynamique --}}
<div id="zone-form-error-summary" class="hidden rounded-2xl border border-rose-500/40 bg-rose-500/10 p-4 text-xs text-rose-200 shadow-sm animate-pulse">
    <div class="flex items-center gap-2 font-semibold text-rose-300">
        <svg class="h-4 w-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
        </svg>
        <span>Veuillez corriger les informations suivantes avant d'enregistrer :</span>
    </div>
    <ul id="zone-form-error-list" class="mt-2 list-disc list-inside space-y-1 text-rose-200/90 pl-1"></ul>
</div>

<div class="space-y-6">
    {{-- Section 1 : Informations générales --}}
    <div class="space-y-4">
        <div class="border-b border-cyan-400/10 pb-2">
            <h2 class="text-sm font-semibold tracking-wide text-cyan-300 uppercase">1. Informations générales</h2>
            <p class="text-xs text-cyan-100/60">Désignation, gouvernance et statut opérationnel de la zone.</p>
        </div>
        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <div class="flex justify-between items-center mb-1.5">
                    <label for="nom" class="block text-sm font-medium text-cyan-100/80">Nom de la zone <span class="text-rose-400">*</span></label>
                    <span id="nom-counter" class="text-[11px] text-cyan-200/50">0 / 255</span>
                </div>
                <input
                    id="nom"
                    name="nom"
                    value="{{ old('nom', $zone?->nom) }}"
                    required
                    minlength="3"
                    maxlength="255"
                    placeholder="ex. Grand Tunis Nord"
                    class="w-full rounded-lg border border-cyan-400/20 bg-slate-950/40 px-3.5 py-2.5 text-sm text-white outline-none transition focus:border-cyan-400/50 focus:ring-2 focus:ring-cyan-400/20"
                >
                <p id="nom-client-error" class="hidden mt-1 text-xs text-rose-400"></p>
                @error('nom')<p class="mt-1 text-sm text-rose-400">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="statut" class="mb-1.5 block text-sm font-medium text-cyan-100/80">Statut opérationnel <span class="text-rose-400">*</span></label>
                <select id="statut" name="statut" required class="w-full rounded-lg border border-cyan-400/20 bg-slate-950/40 px-3.5 py-2.5 text-sm text-white outline-none transition focus:border-cyan-400/50 focus:ring-2 focus:ring-cyan-400/20">
                    @foreach($statuts as $statutOption)
                        <option value="{{ $statutOption->value }}" @selected(old('statut', $zone?->statut?->value ?? 'active') === $statutOption->value)>{{ $statutOption->label() }}</option>
                    @endforeach
                </select>
                @error('statut')<p class="mt-1 text-sm text-rose-400">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="responsable_id" class="mb-1.5 block text-sm font-medium text-cyan-100/80">Responsable de zone</label>
                <select id="responsable_id" name="responsable_id" class="w-full rounded-lg border border-cyan-400/20 bg-slate-950/40 px-3.5 py-2.5 text-sm text-white outline-none transition focus:border-cyan-400/50 focus:ring-2 focus:ring-cyan-400/20">
                    <option value="">Aucun responsable assigné</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" @selected((string) old('responsable_id', $zone?->responsable_id) === (string) $user->id)>{{ $user->name }} ({{ $user->email }})</option>
                    @endforeach
                </select>
                @error('responsable_id')<p class="mt-1 text-sm text-rose-400">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="population_desservie" class="mb-1.5 block text-sm font-medium text-cyan-100/80">Population desservie (habitants)</label>
                <input
                    id="population_desservie"
                    type="number"
                    min="0"
                    step="1"
                    name="population_desservie"
                    value="{{ old('population_desservie', $zone?->population_desservie) }}"
                    placeholder="ex. 1250000"
                    class="w-full rounded-lg border border-cyan-400/20 bg-slate-950/40 px-3.5 py-2.5 text-sm text-white outline-none transition focus:border-cyan-400/50 focus:ring-2 focus:ring-cyan-400/20"
                >
                <p id="population-client-error" class="hidden mt-1 text-xs text-rose-400"></p>
                @error('population_desservie')<p class="mt-1 text-sm text-rose-400">{{ $message }}</p>@enderror
            </div>
            <div class="sm:col-span-2">
                <div class="flex justify-between items-center mb-1.5">
                    <label for="description" class="block text-sm font-medium text-cyan-100/80">Description du secteur <span class="font-normal text-cyan-100/40">(facultatif)</span></label>
                    <span id="desc-counter" class="text-[11px] text-cyan-200/50">0 / 1000</span>
                </div>
                <textarea
                    id="description"
                    name="description"
                    rows="3"
                    maxlength="1000"
                    placeholder="Caractéristiques du réseau hydraulique, contraintes géographiques, points de distribution sensibles..."
                    class="w-full rounded-lg border border-cyan-400/20 bg-slate-950/40 px-3.5 py-2.5 text-sm text-white outline-none transition focus:border-cyan-400/50 focus:ring-2 focus:ring-cyan-400/20"
                >{{ old('description', $zone?->description) }}</textarea>
                @error('description')<p class="mt-1 text-sm text-rose-400">{{ $message }}</p>@enderror
            </div>
        </div>
    </div>

    {{-- Section 2 : Localisation GPS & Délimitation --}}
    <div class="space-y-4">
        <div class="border-b border-cyan-400/10 pb-2">
            <h2 class="text-sm font-semibold tracking-wide text-cyan-300 uppercase">2. Localisation GPS & Territoire</h2>
            <p class="text-xs text-cyan-100/60">Centre géographique et étendue du réseau dans la région.</p>
        </div>
        <div class="grid gap-4 sm:grid-cols-3">
            <div class="sm:col-span-2">
                <div class="flex justify-between items-center mb-1.5">
                    <label for="localisation" class="block text-sm font-medium text-cyan-100/80">Chef-lieu / Ville principale <span class="text-rose-400">*</span></label>
                    <span id="loc-counter" class="text-[11px] text-cyan-200/50">0 / 255</span>
                </div>
                <input
                    id="localisation"
                    name="localisation"
                    value="{{ old('localisation', $zone?->localisation) }}"
                    required
                    minlength="2"
                    maxlength="255"
                    placeholder="ex. Tunis, Ariana, Sfax..."
                    class="w-full rounded-lg border border-cyan-400/20 bg-slate-950/40 px-3.5 py-2.5 text-sm text-white outline-none transition focus:border-cyan-400/50 focus:ring-2 focus:ring-cyan-400/20"
                >
                <p id="localisation-client-error" class="hidden mt-1 text-xs text-rose-400"></p>
                @error('localisation')<p class="mt-1 text-sm text-rose-400">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="superficie_km2" class="mb-1.5 block text-sm font-medium text-cyan-100/80">Superficie (km²)</label>
                <input
                    id="superficie_km2"
                    type="number"
                    step="0.01"
                    min="0"
                    name="superficie_km2"
                    value="{{ old('superficie_km2', $zone?->superficie_km2) }}"
                    placeholder="ex. 346.50"
                    class="w-full rounded-lg border border-cyan-400/20 bg-slate-950/40 px-3.5 py-2.5 text-sm text-white outline-none transition focus:border-cyan-400/50 focus:ring-2 focus:ring-cyan-400/20"
                >
                <p id="superficie-client-error" class="hidden mt-1 text-xs text-rose-400"></p>
                @error('superficie_km2')<p class="mt-1 text-sm text-rose-400">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="latitude_zone" class="mb-1.5 block text-sm font-medium text-cyan-100/80">Latitude (-90 à 90)</label>
                <input
                    id="latitude_zone"
                    type="number"
                    step="0.0000001"
                    min="-90"
                    max="90"
                    name="latitude"
                    value="{{ old('latitude', $zone?->latitude) }}"
                    placeholder="ex. 36.8065000"
                    class="w-full rounded-lg border border-cyan-400/20 bg-slate-950/40 px-3.5 py-2.5 text-sm text-white outline-none transition focus:border-cyan-400/50 focus:ring-2 focus:ring-cyan-400/20"
                >
                <p id="lat-client-error" class="hidden mt-1 text-xs text-rose-400"></p>
                @error('latitude')<p class="mt-1 text-sm text-rose-400">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="longitude_zone" class="mb-1.5 block text-sm font-medium text-cyan-100/80">Longitude (-180 à 180)</label>
                <input
                    id="longitude_zone"
                    type="number"
                    step="0.0000001"
                    min="-180"
                    max="180"
                    name="longitude"
                    value="{{ old('longitude', $zone?->longitude) }}"
                    placeholder="ex. 10.1815000"
                    class="w-full rounded-lg border border-cyan-400/20 bg-slate-950/40 px-3.5 py-2.5 text-sm text-white outline-none transition focus:border-cyan-400/50 focus:ring-2 focus:ring-cyan-400/20"
                >
                <p id="lng-client-error" class="hidden mt-1 text-xs text-rose-400"></p>
                @error('longitude')<p class="mt-1 text-sm text-rose-400">{{ $message }}</p>@enderror
            </div>
            <div class="flex items-end">
                <button
                    type="button"
                    id="btn-center-tunisie-zone"
                    class="w-full rounded-lg border border-cyan-400/30 bg-cyan-500/10 px-3 py-2.5 text-xs font-medium text-cyan-300 hover:bg-cyan-500/20 transition flex items-center justify-center gap-1.5"
                >
                    <svg class="w-3.5 h-3.5 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    Centrer sur la Tunisie
                </button>
            </div>
            <div class="sm:col-span-3">
                <div id="zone-map-form" class="h-64 w-full rounded-xl border border-cyan-400/20 overflow-hidden shadow-inner bg-slate-950/60"></div>
                <div class="mt-1.5 flex flex-wrap items-center justify-between text-xs text-cyan-100/50">
                    <p>💡 Cliquez sur la carte ou déplacez le marqueur pour pointer le centre du territoire.</p>
                    <span class="text-[11px] text-cyan-200/40">Zone Tunisie : 30° à 38° N · 7° à 12° E</span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="flex justify-end gap-2 border-t border-white/10 pt-4 mt-6">
    <a href="{{ route($inventoryRoutePrefix . '.zones.index') }}" class="rounded-lg border border-cyan-400/20 px-4 py-2 text-sm font-medium text-cyan-100/70 hover:bg-white/5 transition">Annuler</a>
    <button
        type="submit"
        id="btn-submit-zone"
        class="rounded-lg bg-cyan-600 px-5 py-2 text-sm font-medium text-white hover:bg-cyan-500 shadow-sm transition flex items-center gap-2"
    >
        <span>Enregistrer la zone</span>
    </button>
</div>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const nomInput = document.getElementById('nom');
        const nomCounter = document.getElementById('nom-counter');
        const nomError = document.getElementById('nom-client-error');

        const locInput = document.getElementById('localisation');
        const locCounter = document.getElementById('loc-counter');
        const locError = document.getElementById('localisation-client-error');

        const descInput = document.getElementById('description');
        const descCounter = document.getElementById('desc-counter');

        const popInput = document.getElementById('population_desservie');
        const popError = document.getElementById('population-client-error');

        const supInput = document.getElementById('superficie_km2');
        const supError = document.getElementById('superficie-client-error');

        const latInput = document.getElementById('latitude_zone');
        const latError = document.getElementById('lat-client-error');
        const lngInput = document.getElementById('longitude_zone');
        const lngError = document.getElementById('lng-client-error');

        const errorSummary = document.getElementById('zone-form-error-summary');
        const errorList = document.getElementById('zone-form-error-list');
        const form = nomInput.closest('form');

        // Gestion des compteurs de caractères
        function updateCounters() {
            if (nomInput && nomCounter) {
                nomCounter.textContent = (nomInput.value ? nomInput.value.length : 0) + ' / 255';
            }
            if (locInput && locCounter) {
                locCounter.textContent = (locInput.value ? locInput.value.length : 0) + ' / 255';
            }
            if (descInput && descCounter) {
                descCounter.textContent = (descInput.value ? descInput.value.length : 0) + ' / 1000';
            }
        }
        if (nomInput) nomInput.addEventListener('input', updateCounters);
        if (locInput) locInput.addEventListener('input', updateCounters);
        if (descInput) descInput.addEventListener('input', updateCounters);
        updateCounters();

        // Contrôles de saisie en temps réel
        function validateNom() {
            const val = nomInput.value.trim();
            if (!val) {
                showFieldState(nomInput, nomError, 'Le nom de la zone est obligatoire.');
                return 'Le nom de la zone est obligatoire.';
            }
            if (val.length < 3) {
                showFieldState(nomInput, nomError, 'Le nom doit comporter au moins 3 caractères.');
                return 'Le nom de la zone doit comporter au moins 3 caractères.';
            }
            clearFieldState(nomInput, nomError);
            return null;
        }

        function validateLoc() {
            const val = locInput.value.trim();
            if (!val) {
                showFieldState(locInput, locError, 'La localisation / ville principale est obligatoire.');
                return 'Le chef-lieu / ville principale est obligatoire.';
            }
            if (val.length < 2) {
                showFieldState(locInput, locError, 'La localisation doit comporter au moins 2 caractères.');
                return 'La localisation doit comporter au moins 2 caractères.';
            }
            clearFieldState(locInput, locError);
            return null;
        }

        function validatePop() {
            if (popInput && popInput.value !== '') {
                const val = parseFloat(popInput.value);
                if (isNaN(val) || val < 0 || !Number.isInteger(val)) {
                    showFieldState(popInput, popError, 'La population doit être un nombre entier positif.');
                    return 'La population desservie doit être un nombre entier positif ou nul.';
                }
            }
            clearFieldState(popInput, popError);
            return null;
        }

        function validateSup() {
            if (supInput && supInput.value !== '') {
                const val = parseFloat(supInput.value);
                if (isNaN(val) || val < 0) {
                    showFieldState(supInput, supError, 'La superficie doit être un nombre supérieur ou égal à 0.');
                    return 'La superficie doit être un nombre positif ou nul.';
                }
            }
            clearFieldState(supInput, supError);
            return null;
        }

        function validateGPS() {
            let err = null;
            if (latInput && latInput.value !== '') {
                const lat = parseFloat(latInput.value);
                if (isNaN(lat) || lat < -90 || lat > 90) {
                    showFieldState(latInput, latError, 'La latitude doit être comprise entre -90 et 90°.');
                    err = 'La latitude doit être comprise entre -90 et 90°.';
                } else {
                    clearFieldState(latInput, latError);
                }
            }
            if (lngInput && lngInput.value !== '') {
                const lng = parseFloat(lngInput.value);
                if (isNaN(lng) || lng < -180 || lng > 180) {
                    showFieldState(lngInput, lngError, 'La longitude doit être comprise entre -180 et 180°.');
                    err = err || 'La longitude doit être comprise entre -180 et 180°.';
                } else {
                    clearFieldState(lngInput, lngError);
                }
            }
            return err;
        }

        function showFieldState(input, errorEl, message) {
            input.classList.remove('border-cyan-400/20', 'border-emerald-500/50');
            input.classList.add('border-rose-500/60', 'ring-1', 'ring-rose-500/20');
            if (errorEl) {
                errorEl.textContent = message;
                errorEl.classList.remove('hidden');
            }
        }

        function clearFieldState(input, errorEl) {
            if (!input) return;
            input.classList.remove('border-rose-500/60', 'ring-1', 'ring-rose-500/20');
            input.classList.add('border-cyan-400/20');
            if (errorEl) {
                errorEl.classList.add('hidden');
            }
        }

        if (nomInput) nomInput.addEventListener('blur', validateNom);
        if (locInput) locInput.addEventListener('blur', validateLoc);
        if (popInput) popInput.addEventListener('input', validatePop);
        if (supInput) supInput.addEventListener('input', validateSup);
        if (latInput) latInput.addEventListener('input', validateGPS);
        if (lngInput) lngInput.addEventListener('input', validateGPS);

        // Validation globale à la soumission
        if (form) {
            form.addEventListener('submit', function (e) {
                const errors = [];
                const errNom = validateNom();
                if (errNom) errors.push(errNom);

                const errLoc = validateLoc();
                if (errLoc) errors.push(errLoc);

                const errPop = validatePop();
                if (errPop) errors.push(errPop);

                const errSup = validateSup();
                if (errSup) errors.push(errSup);

                const errGps = validateGPS();
                if (errGps) errors.push(errGps);

                if (errors.length > 0) {
                    e.preventDefault();
                    errorList.innerHTML = '';
                    errors.forEach(err => {
                        const li = document.createElement('li');
                        li.textContent = err;
                        errorList.appendChild(li);
                    });
                    errorSummary.classList.remove('hidden');
                    errorSummary.scrollIntoView({ behavior: 'smooth', block: 'center' });

                    // Focus premier champ erroné
                    if (errNom) nomInput.focus();
                    else if (errLoc) locInput.focus();
                    else if (errPop) popInput.focus();
                    else if (errSup) supInput.focus();
                    return false;
                } else {
                    errorSummary.classList.add('hidden');
                    const btn = document.getElementById('btn-submit-zone');
                    if (btn) {
                        btn.disabled = true;
                        btn.innerHTML = `<svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg> Enregistrement...`;
                    }
                }
            });
        }

        // Mini sélecteur Leaflet
        const mapElement = document.getElementById('zone-map-form');
        if (mapElement && typeof L !== 'undefined') {
            const initLat = parseFloat(latInput.value) || 36.8065;
            const initLng = parseFloat(lngInput.value) || 10.1815;
            const hasInitial = !isNaN(parseFloat(latInput.value)) && !isNaN(parseFloat(lngInput.value));

            const map = L.map('zone-map-form', {
                center: [initLat, initLng],
                zoom: hasInitial ? 11 : 7,
                zoomControl: true,
            });

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap',
                maxZoom: 19
            }).addTo(map);

            let marker = null;
            if (hasInitial) {
                marker = L.marker([initLat, initLng], { draggable: true }).addTo(map);
                marker.on('dragend', function (e) {
                    const pos = e.target.getLatLng();
                    latInput.value = pos.lat.toFixed(7);
                    lngInput.value = pos.lng.toFixed(7);
                    validateGPS();
                });
            }

            map.on('click', function (e) {
                const { lat, lng } = e.latlng;
                if (marker) {
                    map.removeLayer(marker);
                }
                marker = L.marker([lat, lng], { draggable: true }).addTo(map);
                latInput.value = lat.toFixed(7);
                lngInput.value = lng.toFixed(7);
                validateGPS();
                marker.on('dragend', function (ev) {
                    const pos = ev.target.getLatLng();
                    latInput.value = pos.lat.toFixed(7);
                    lngInput.value = pos.lng.toFixed(7);
                    validateGPS();
                });
            });

            const updateMapFromInputs = function () {
                const lat = parseFloat(latInput.value);
                const lng = parseFloat(lngInput.value);
                if (!isNaN(lat) && !isNaN(lng) && lat >= -90 && lat <= 90 && lng >= -180 && lng <= 180) {
                    if (marker) {
                        marker.setLatLng([lat, lng]);
                    } else {
                        marker = L.marker([lat, lng], { draggable: true }).addTo(map);
                    }
                    map.setView([lat, lng], 11);
                }
            };

            latInput.addEventListener('change', updateMapFromInputs);
            lngInput.addEventListener('change', updateMapFromInputs);

            const btnCenter = document.getElementById('btn-center-tunisie-zone');
            if (btnCenter) {
                btnCenter.addEventListener('click', function () {
                    map.setView([34.0, 9.5], 6);
                });
            }
        }
    });
</script>
