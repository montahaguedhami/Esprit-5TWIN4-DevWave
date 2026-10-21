@csrf

@php
    $statuts = $statuts ?? \App\Enums\ZoneStatut::cases();
    $users   = $users   ?? \App\Models\User::orderBy('name')->get(['id', 'name', 'email']);
    $inventoryRoutePrefix = $inventoryRoutePrefix ?? (request()->routeIs('admin.*') ? 'admin' : 'manager');
@endphp

{{-- ── Bannière récapitulative d'erreurs (affichée seulement après soumission) ── --}}
<div id="zone-error-banner"
     role="alert"
     aria-live="assertive"
     style="display:none;margin-bottom:16px;padding:12px 16px;
            border-radius:10px;border:1px solid rgba(248,113,113,.4);
            background:rgba(248,113,113,.08);font-size:13px;color:#fca5a5;">
    <div style="display:flex;align-items:center;gap:8px;font-weight:600;color:#f87171;margin-bottom:6px;">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
        Veuillez corriger les erreurs suivantes avant d'enregistrer :
    </div>
    <ul id="zone-error-list" style="margin:0;padding-left:18px;line-height:1.7;"></ul>
</div>

<div style="display:flex;flex-direction:column;gap:24px;">

    {{-- ══ Section 1 : Informations générales ══════════════════════════════════ --}}
    <fieldset style="border:none;padding:0;margin:0;">
        <legend style="
            font-size:11px;font-weight:700;text-transform:uppercase;
            letter-spacing:.08em;color:var(--role-accent,#0891b2);
            padding-bottom:8px;border-bottom:1px solid rgba(8,145,178,.15);
            width:100%;margin-bottom:16px;">
            1 — Informations générales
        </legend>
        <p style="font-size:12px;color:var(--text-muted,#94a3b8);margin:-8px 0 16px;">
            Désignation, gouvernance et statut opérationnel de la zone.
        </p>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">

            {{-- Nom --}}
            <div>
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:5px;">
                    <label for="nom" style="font-size:12px;font-weight:600;color:var(--text-secondary,#475569);">
                        Nom de la zone <span style="color:#f87171">*</span>
                    </label>
                    <span id="nom-counter" style="font-size:11px;color:var(--text-muted,#94a3b8);">0 / 255</span>
                </div>
                <input id="nom" name="nom"
                       value="{{ old('nom', $zone?->nom) }}"
                       maxlength="255"
                       placeholder="ex. Grand Tunis Nord"
                       autocomplete="off"
                       class="zone-field"
                       data-field="nom">
                <x-ui.validation-message for="nom" />
                @error('nom')<p style="margin-top:4px;font-size:12px;color:#f87171;">{{ $message }}</p>@enderror
            </div>

            {{-- Statut --}}
            <div>
                <label for="statut" style="display:block;font-size:12px;font-weight:600;color:var(--text-secondary,#475569);margin-bottom:5px;">
                    Statut opérationnel <span style="color:#f87171">*</span>
                </label>
                <select id="statut" name="statut" class="zone-field" data-field="statut">
                    @foreach($statuts as $s)
                        <option value="{{ $s->value }}"
                            @selected(old('statut', $zone?->statut?->value ?? 'active') === $s->value)>
                            {{ $s->label() }}
                        </option>
                    @endforeach
                </select>
                <x-ui.validation-message for="statut" />
                @error('statut')<p style="margin-top:4px;font-size:12px;color:#f87171;">{{ $message }}</p>@enderror
            </div>

            {{-- Responsable --}}
            <div>
                <label for="responsable_id" style="display:block;font-size:12px;font-weight:600;color:var(--text-secondary,#475569);margin-bottom:5px;">
                    Responsable de zone
                </label>
                <select id="responsable_id" name="responsable_id" class="zone-field" data-field="responsable_id">
                    <option value="">Aucun responsable assigné</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}"
                            @selected((string) old('responsable_id', $zone?->responsable_id) === (string) $user->id)>
                            {{ $user->name }} ({{ $user->email }})
                        </option>
                    @endforeach
                </select>
                <x-ui.validation-message for="responsable_id" />
                @error('responsable_id')<p style="margin-top:4px;font-size:12px;color:#f87171;">{{ $message }}</p>@enderror
            </div>

            {{-- Population --}}
            <div>
                <label for="population_desservie" style="display:block;font-size:12px;font-weight:600;color:var(--text-secondary,#475569);margin-bottom:5px;">
                    Population desservie (habitants)
                </label>
                <input id="population_desservie" name="population_desservie"
                       type="number" min="0" step="1"
                       value="{{ old('population_desservie', $zone?->population_desservie) }}"
                       placeholder="ex. 1 250 000"
                       class="zone-field"
                       data-field="population_desservie">
                <x-ui.validation-message for="population_desservie" />
                @error('population_desservie')<p style="margin-top:4px;font-size:12px;color:#f87171;">{{ $message }}</p>@enderror
            </div>

            {{-- Description --}}
            <div style="grid-column:1/-1;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:5px;">
                    <label for="description" style="font-size:12px;font-weight:600;color:var(--text-secondary,#475569);">
                        Description du secteur <span style="font-weight:400;color:var(--text-muted,#94a3b8);">(facultatif)</span>
                    </label>
                    <span id="desc-counter" style="font-size:11px;color:var(--text-muted,#94a3b8);">0 / 1000</span>
                </div>
                <textarea id="description" name="description"
                          rows="3" maxlength="1000"
                          placeholder="Caractéristiques du réseau hydraulique, contraintes géographiques, points de distribution sensibles..."
                          class="zone-field"
                          data-field="description">{{ old('description', $zone?->description) }}</textarea>
                <x-ui.validation-message for="description" />
                @error('description')<p style="margin-top:4px;font-size:12px;color:#f87171;">{{ $message }}</p>@enderror
            </div>

        </div>
    </fieldset>

    {{-- ══ Section 2 : Localisation GPS ═══════════════════════════════════════ --}}
    <fieldset style="border:none;padding:0;margin:0;">
        <legend style="
            font-size:11px;font-weight:700;text-transform:uppercase;
            letter-spacing:.08em;color:var(--role-accent,#0891b2);
            padding-bottom:8px;border-bottom:1px solid rgba(8,145,178,.15);
            width:100%;margin-bottom:16px;">
            2 — Localisation GPS &amp; Territoire
        </legend>
        <p style="font-size:12px;color:var(--text-muted,#94a3b8);margin:-8px 0 16px;">
            Centre géographique et étendue du réseau dans la région.
        </p>

        <div style="display:grid;grid-template-columns:2fr 1fr 1fr 1fr auto;gap:16px;align-items:start;">

            {{-- Chef-lieu --}}
            <div>
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:5px;">
                    <label for="localisation" style="font-size:12px;font-weight:600;color:var(--text-secondary,#475569);">
                        Chef-lieu / Ville principale <span style="color:#f87171">*</span>
                    </label>
                    <span id="loc-counter" style="font-size:11px;color:var(--text-muted,#94a3b8);">0 / 255</span>
                </div>
                <input id="localisation" name="localisation"
                       value="{{ old('localisation', $zone?->localisation) }}"
                       maxlength="255"
                       placeholder="ex. Tunis, Ariana, Sfax..."
                       class="zone-field"
                       data-field="localisation">
                <x-ui.validation-message for="localisation" />
                @error('localisation')<p style="margin-top:4px;font-size:12px;color:#f87171;">{{ $message }}</p>@enderror
            </div>

            {{-- Superficie --}}
            <div>
                <label for="superficie_km2" style="display:block;font-size:12px;font-weight:600;color:var(--text-secondary,#475569);margin-bottom:5px;">
                    Superficie (km²)
                </label>
                <input id="superficie_km2" name="superficie_km2"
                       type="number" step="0.01" min="0"
                       value="{{ old('superficie_km2', $zone?->superficie_km2) }}"
                       placeholder="ex. 346.50"
                       class="zone-field"
                       data-field="superficie_km2">
                <x-ui.validation-message for="superficie_km2" />
                @error('superficie_km2')<p style="margin-top:4px;font-size:12px;color:#f87171;">{{ $message }}</p>@enderror
            </div>

            {{-- Latitude --}}
            <div>
                <label for="latitude_zone" style="display:block;font-size:12px;font-weight:600;color:var(--text-secondary,#475569);margin-bottom:5px;">
                    Latitude (−90 à 90)
                </label>
                <input id="latitude_zone" name="latitude"
                       type="number" step="0.0000001" min="-90" max="90"
                       value="{{ old('latitude', $zone?->latitude) }}"
                       placeholder="ex. 36.8065000"
                       class="zone-field"
                       data-field="latitude">
                <x-ui.validation-message for="latitude" />
                @error('latitude')<p style="margin-top:4px;font-size:12px;color:#f87171;">{{ $message }}</p>@enderror
            </div>

            {{-- Longitude --}}
            <div>
                <label for="longitude_zone" style="display:block;font-size:12px;font-weight:600;color:var(--text-secondary,#475569);margin-bottom:5px;">
                    Longitude (−180 à 180)
                </label>
                <input id="longitude_zone" name="longitude"
                       type="number" step="0.0000001" min="-180" max="180"
                       value="{{ old('longitude', $zone?->longitude) }}"
                       placeholder="ex. 10.1815000"
                       class="zone-field"
                       data-field="longitude">
                <x-ui.validation-message for="longitude" />
                @error('longitude')<p style="margin-top:4px;font-size:12px;color:#f87171;">{{ $message }}</p>@enderror
            </div>

            {{-- Centrer bouton --}}
            <div style="display:flex;align-items:flex-end;padding-bottom:0;">
                <button type="button" id="btn-center-tunisie-zone"
                        style="
                            display:flex;align-items:center;gap:6px;
                            padding:9px 12px;border-radius:8px;
                            border:1px solid rgba(8,145,178,.3);
                            background:rgba(8,145,178,.08);
                            color:#38bdf8;font-size:12px;font-weight:600;
                            cursor:pointer;white-space:nowrap;
                            transition:background .18s;
                        ">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
                    Tunisie
                </button>
            </div>

        </div>

        {{-- Carte Leaflet --}}
        <div style="margin-top:12px;">
            <div id="zone-map-form"
                 style="height:240px;border-radius:10px;border:1px solid var(--border,#e2e8f0);overflow:hidden;"></div>
            <p style="font-size:11px;color:var(--text-muted,#94a3b8);margin-top:6px;">
                💡 Cliquez sur la carte ou déplacez le marqueur pour définir le centre du territoire. Zone Tunisie : 30°–38° N · 7°–12° E.
            </p>
        </div>

    </fieldset>

</div>

{{-- ── Boutons d'action ── --}}
<div style="display:flex;justify-content:flex-end;gap:10px;border-top:1px solid var(--border,#e2e8f0);padding-top:20px;margin-top:24px;">
    <a href="{{ route($inventoryRoutePrefix . '.zones.index') }}" class="btn btn-secondary">
        Annuler
    </a>
    <button type="submit" id="btn-submit-zone" class="btn btn-primary">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/><polyline points="17,21 17,13 7,13 7,21"/><polyline points="7,3 7,8 15,8"/></svg>
        Enregistrer la zone
    </button>
</div>

{{-- ══ Styles locaux ══════════════════════════════════════════════════════════ --}}
<style>
.zone-field {
    width: 100%;
    padding: 8px 12px;
    border-radius: 8px;
    border: 1px solid var(--border, #e2e8f0);
    background: var(--bg-input, #fff);
    color: var(--text-primary, #0f172a);
    font-size: 13px;
    font-family: inherit;
    transition: border-color .18s, box-shadow .18s;
    outline: none;
    box-sizing: border-box;
}
.zone-field:focus {
    border-color: var(--border-focus, #0891b2);
    box-shadow: 0 0 0 3px rgba(8,145,178,.12);
}
.zone-field.field-error {
    border-color: #f87171 !important;
    box-shadow: 0 0 0 3px rgba(248,113,113,.12) !important;
}
.zone-field.field-ok {
    border-color: #34d399;
}
/* Validation message visible state */
p[id$="-client-error"][style*="flex"] {
    display: flex !important;
}
</style>

{{-- ══ Leaflet ════════════════════════════════════════════════════════════════ --}}
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha384-sHL9NAb7lN7rfvG5lfHpm643Xkcjzp4jFvuavGOndn6pjVqS6ny56CAt3nsEVT4H" crossorigin="anonymous"/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha384-cxOPjt7s7Iz04uaHJceBmS+qpjv2JkIHNVcuOrM+YHwZOmJGBXI00mdUXEq65HTH" crossorigin="anonymous"></script>

{{-- ══ Validation JS ══════════════════════════════════════════════════════════ --}}
<script>
(function () {
    'use strict';

    /* ── Helpers ── */
    function el(id) { return document.getElementById(id); }

    function showError(fieldId, message) {
        var input = el(fieldId) || document.querySelector('[name="' + fieldId + '"]') || document.querySelector('[data-field="' + fieldId + '"]');
        var errEl  = el(fieldId + '-client-error');
        if (input)  { input.classList.add('field-error'); input.classList.remove('field-ok'); }
        if (errEl)  {
            errEl.style.display = 'flex';
            var span = errEl.querySelector('span');
            if (span) span.textContent = message;
        }
    }

    function clearError(fieldId) {
        var input = el(fieldId) || document.querySelector('[name="' + fieldId + '"]') || document.querySelector('[data-field="' + fieldId + '"]');
        var errEl  = el(fieldId + '-client-error');
        if (input)  { input.classList.remove('field-error'); input.classList.add('field-ok'); }
        if (errEl)  { errEl.style.display = 'none'; }
    }

    function val(id) {
        var el2 = el(id);
        return el2 ? el2.value.trim() : '';
    }

    /* ── Individual validators  (return error string or null) ── */

    function validateNom() {
        var v = val('nom');
        if (!v)       { showError('nom', 'Le nom de la zone est obligatoire.'); return 'Nom de la zone : champ obligatoire.'; }
        if (v.length < 3) { showError('nom', 'Le nom doit comporter au moins 3 caractères.'); return 'Nom de la zone : minimum 3 caractères.'; }
        clearError('nom'); return null;
    }

    function validateStatut() {
        var v = val('statut');
        if (!v) { showError('statut', 'Veuillez sélectionner un statut.'); return 'Statut opérationnel : champ obligatoire.'; }
        clearError('statut'); return null;
    }

    function validateLocalisation() {
        var v = val('localisation');
        if (!v)       { showError('localisation', 'Le chef-lieu / ville principale est obligatoire.'); return 'Localisation : champ obligatoire.'; }
        if (v.length < 2) { showError('localisation', 'La localisation doit comporter au moins 2 caractères.'); return 'Localisation : minimum 2 caractères.'; }
        clearError('localisation'); return null;
    }

    function validatePopulation() {
        var input = el('population_desservie');
        if (!input || input.value === '') { clearError('population_desservie'); return null; }
        var v = parseFloat(input.value);
        if (isNaN(v) || v < 0 || Math.floor(v) !== v) {
            showError('population_desservie', 'La population doit être un nombre entier positif ou nul.');
            return 'Population desservie : nombre entier positif ou nul attendu.';
        }
        clearError('population_desservie'); return null;
    }

    function validateSuperficie() {
        var input = el('superficie_km2');
        if (!input || input.value === '') { clearError('superficie_km2'); return null; }
        var v = parseFloat(input.value);
        if (isNaN(v) || v < 0) {
            showError('superficie_km2', 'La superficie doit être un nombre positif ou nul (en km²).');
            return 'Superficie : valeur positive ou nulle en km² attendue.';
        }
        clearError('superficie_km2'); return null;
    }

    function validateLatitude() {
        var input = el('latitude_zone');
        if (!input || input.value === '') { clearError('latitude'); return null; }
        var v = parseFloat(input.value);
        if (isNaN(v) || v < -90 || v > 90) {
            showError('latitude', 'La latitude doit être comprise entre −90° et 90°.');
            return 'Latitude : valeur comprise entre −90 et 90 attendue.';
        }
        clearError('latitude'); return null;
    }

    function validateLongitude() {
        var input = el('longitude_zone');
        if (!input || input.value === '') { clearError('longitude'); return null; }
        var v = parseFloat(input.value);
        if (isNaN(v) || v < -180 || v > 180) {
            showError('longitude', 'La longitude doit être comprise entre −180° et 180°.');
            return 'Longitude : valeur comprise entre −180 et 180 attendue.';
        }
        clearError('longitude'); return null;
    }

    function validateDescription() {
        var input = el('description');
        if (!input || input.value.length <= 1000) { clearError('description'); return null; }
        showError('description', 'La description ne peut pas dépasser 1000 caractères.');
        return 'Description : maximum 1000 caractères.';
    }

    /* ── Bind blur events (only show error when leaving field) ── */
    document.addEventListener('DOMContentLoaded', function () {

        var nomInput    = el('nom');
        var locInput    = el('localisation');
        var descInput   = el('description');
        var nomCounter  = el('nom-counter');
        var locCounter  = el('loc-counter');
        var descCounter = el('desc-counter');

        /* Character counters */
        function bindCounter(input, counter, max) {
            if (!input || !counter) return;
            function upd() { counter.textContent = input.value.length + ' / ' + max; }
            input.addEventListener('input', upd);
            upd();
        }
        bindCounter(nomInput,  nomCounter,  255);
        bindCounter(locInput,  locCounter,  255);
        bindCounter(descInput, descCounter, 1000);

        /* Blur-based validation (shows message only when user leaves the field) */
        if (nomInput)               nomInput.addEventListener('blur',   validateNom);
        if (locInput)               locInput.addEventListener('blur',   validateLocalisation);
        if (el('statut'))           el('statut').addEventListener('change', validateStatut);
        if (el('population_desservie')) el('population_desservie').addEventListener('blur', validatePopulation);
        if (el('superficie_km2'))   el('superficie_km2').addEventListener('blur',  validateSuperficie);
        if (el('latitude_zone'))    el('latitude_zone').addEventListener('blur',   validateLatitude);
        if (el('longitude_zone'))   el('longitude_zone').addEventListener('blur',  validateLongitude);
        if (descInput)              descInput.addEventListener('blur',   validateDescription);

        /* Clear error while user is typing */
        [nomInput, locInput, descInput].forEach(function (inp) {
            if (!inp) return;
            inp.addEventListener('input', function () {
                var fieldId = this.dataset.field || this.id;
                var errEl = el(fieldId + '-client-error');
                if (errEl) errEl.style.display = 'none';
                this.classList.remove('field-error');
            });
        });

        /* ── Submit validation ── */
        var form = nomInput ? nomInput.closest('form') : null;
        if (form) {
            form.addEventListener('submit', function (e) {
                var errors = [];
                [validateNom, validateStatut, validateLocalisation,
                 validatePopulation, validateSuperficie,
                 validateLatitude, validateLongitude, validateDescription
                ].forEach(function (fn) {
                    var err = fn();
                    if (err) errors.push(err);
                });

                if (errors.length > 0) {
                    e.preventDefault();
                    var banner = el('zone-error-banner');
                    var list   = el('zone-error-list');
                    if (banner && list) {
                        list.innerHTML = '';
                        errors.forEach(function (msg) {
                            var li = document.createElement('li');
                            li.textContent = msg;
                            list.appendChild(li);
                        });
                        banner.style.display = 'block';
                        banner.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                    }
                    /* Focus first invalid field */
                    var firstErr = form.querySelector('.field-error');
                    if (firstErr) firstErr.focus();
                } else {
                    var banner2 = el('zone-error-banner');
                    if (banner2) banner2.style.display = 'none';
                    /* Loading state */
                    var btn = el('btn-submit-zone');
                    if (btn) {
                        btn.disabled = true;
                        btn.innerHTML = '<svg style="animation:spin 1s linear infinite;width:14px;height:14px;margin-right:6px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12a9 9 0 11-6.219-8.56"/></svg>Enregistrement...';
                    }
                }
            });
        }

        /* ── Leaflet mini-map ── */
        var mapEl = el('zone-map-form');
        var latInput2 = el('latitude_zone');
        var lngInput2 = el('longitude_zone');

        if (mapEl && typeof L !== 'undefined') {
            var initLat = parseFloat(latInput2 ? latInput2.value : '') || 36.8065;
            var initLng = parseFloat(lngInput2 ? lngInput2.value : '') || 10.1815;
            var hasCoords = !isNaN(parseFloat(latInput2 ? latInput2.value : ''))
                         && !isNaN(parseFloat(lngInput2 ? lngInput2.value : ''));

            var map = L.map('zone-map-form', {
                center: [initLat, initLng],
                zoom: hasCoords ? 10 : 6,
                zoomControl: true
            });

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap', maxZoom: 19
            }).addTo(map);

            var marker = null;
            if (hasCoords) {
                marker = L.marker([initLat, initLng], { draggable: true }).addTo(map);
                marker.on('dragend', function (ev) {
                    var p = ev.target.getLatLng();
                    if (latInput2) latInput2.value = p.lat.toFixed(7);
                    if (lngInput2) lngInput2.value = p.lng.toFixed(7);
                    clearError('latitude'); clearError('longitude');
                });
            }

            map.on('click', function (ev) {
                var lat = ev.latlng.lat, lng = ev.latlng.lng;
                if (marker) map.removeLayer(marker);
                marker = L.marker([lat, lng], { draggable: true }).addTo(map);
                if (latInput2) latInput2.value = lat.toFixed(7);
                if (lngInput2) lngInput2.value = lng.toFixed(7);
                clearError('latitude'); clearError('longitude');
                marker.on('dragend', function (ev2) {
                    var p = ev2.target.getLatLng();
                    if (latInput2) latInput2.value = p.lat.toFixed(7);
                    if (lngInput2) lngInput2.value = p.lng.toFixed(7);
                    clearError('latitude'); clearError('longitude');
                });
            });

            function syncMap() {
                var la = parseFloat(latInput2 ? latInput2.value : '');
                var lo = parseFloat(lngInput2 ? lngInput2.value : '');
                if (!isNaN(la) && !isNaN(lo) && la >= -90 && la <= 90 && lo >= -180 && lo <= 180) {
                    if (marker) marker.setLatLng([la, lo]);
                    else        marker = L.marker([la, lo], { draggable: true }).addTo(map);
                    map.setView([la, lo], 11);
                }
            }
            if (latInput2) latInput2.addEventListener('change', syncMap);
            if (lngInput2) lngInput2.addEventListener('change', syncMap);

            var btnCenter = el('btn-center-tunisie-zone');
            if (btnCenter) {
                btnCenter.addEventListener('click', function () {
                    map.setView([34.0, 9.5], 6);
                });
            }
        }

    }); // DOMContentLoaded

    /* Keyframe for spinner */
    var style = document.createElement('style');
    style.textContent = '@keyframes spin{to{transform:rotate(360deg)}}';
    document.head.appendChild(style);

})();
</script>
