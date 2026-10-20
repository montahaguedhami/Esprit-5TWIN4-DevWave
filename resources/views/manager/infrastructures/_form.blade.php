@csrf

@php
    $niveauxCriticite   = $niveauxCriticite ?? \App\Enums\NiveauCriticite::cases();
    $unitesCapacite     = $unitesCapacite   ?? ['m3', 'm3/h', 'l/s'];
    $inventoryRoutePrefix = $inventoryRoutePrefix ?? (session('user.role') === 'admin' ? 'admin' : 'manager');
    $etatInitialValue   = $infrastructure?->etat?->value ?? '';
@endphp

{{-- ── Bannière récapitulative d'erreurs ── --}}
<div id="infra-error-banner"
     role="alert" aria-live="assertive"
     style="display:none;margin-bottom:16px;padding:12px 16px;
            border-radius:10px;border:1px solid rgba(248,113,113,.4);
            background:rgba(248,113,113,.08);font-size:13px;color:#fca5a5;">
    <div style="display:flex;align-items:center;gap:8px;font-weight:600;color:#f87171;margin-bottom:6px;">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
        Veuillez corriger les erreurs suivantes avant d'enregistrer :
    </div>
    <ul id="infra-error-list" style="margin:0;padding-left:18px;line-height:1.7;"></ul>
</div>

<div style="display:flex;flex-direction:column;gap:24px;">

    {{-- ══ Section 1 : Informations générales ══════════════════════════════════ --}}
    <fieldset style="border:none;padding:0;margin:0;">
        <legend style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;
                       color:var(--role-accent,#0891b2);padding-bottom:8px;
                       border-bottom:1px solid rgba(8,145,178,.15);width:100%;margin-bottom:16px;">
            1 — Informations générales
        </legend>
        <p style="font-size:12px;color:var(--text-muted,#94a3b8);margin:-8px 0 16px;">
            Identité de l'équipement, typologie et rattachement au réseau.
        </p>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">

            {{-- Nom --}}
            <div>
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:5px;">
                    <label for="nom" style="font-size:12px;font-weight:600;color:var(--text-secondary,#475569);">
                        Nom de l'infrastructure <span style="color:#f87171">*</span>
                    </label>
                    <span id="nom-counter" style="font-size:11px;color:var(--text-muted,#94a3b8);">0 / 255</span>
                </div>
                <input id="nom" name="nom"
                       value="{{ old('nom', $infrastructure?->nom) }}"
                       maxlength="255"
                       placeholder="ex. Réservoir Sud RS-04"
                       autocomplete="off"
                       class="infra-field" data-field="nom">
                <x-ui.validation-message for="nom" />
                @error('nom')<p style="margin-top:4px;font-size:12px;color:#f87171;">{{ $message }}</p>@enderror
            </div>

            {{-- Type --}}
            <div>
                <label for="type" style="display:block;font-size:12px;font-weight:600;color:var(--text-secondary,#475569);margin-bottom:5px;">
                    Type d'ouvrage <span style="color:#f87171">*</span>
                </label>
                <select id="type" name="type" class="infra-field" data-field="type">
                    <option value="">Sélectionnez un type</option>
                    @foreach(\App\Enums\InfrastructureType::cases() as $t)
                        <option value="{{ $t->value }}" @selected(old('type', $infrastructure?->type?->value) === $t->value)>
                            {{ $t->label() }}
                        </option>
                    @endforeach
                </select>
                <x-ui.validation-message for="type" />
                @error('type')<p style="margin-top:4px;font-size:12px;color:#f87171;">{{ $message }}</p>@enderror
            </div>

            {{-- Référence --}}
            <div>
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:5px;">
                    <label for="reference" style="font-size:12px;font-weight:600;color:var(--text-secondary,#475569);">
                        Référence interne / code équipement
                    </label>
                    <span id="ref-counter" style="font-size:11px;color:var(--text-muted,#94a3b8);">0 / 255</span>
                </div>
                <input id="reference" name="reference"
                       value="{{ old('reference', $infrastructure?->reference) }}"
                       maxlength="255"
                       placeholder="ex. INF-2026-TUN-042"
                       style="text-transform:uppercase;"
                       class="infra-field" data-field="reference">
                <p style="font-size:11px;color:var(--text-muted,#94a3b8);margin-top:3px;">Converti automatiquement en majuscules.</p>
                <x-ui.validation-message for="reference" />
                @error('reference')<p style="margin-top:4px;font-size:12px;color:#f87171;">{{ $message }}</p>@enderror
            </div>

            {{-- Fabricant --}}
            <div>
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:5px;">
                    <label for="fabricant" style="font-size:12px;font-weight:600;color:var(--text-secondary,#475569);">
                        Fabricant / Constructeur
                    </label>
                    <span id="fab-counter" style="font-size:11px;color:var(--text-muted,#94a3b8);">0 / 255</span>
                </div>
                <input id="fabricant" name="fabricant"
                       value="{{ old('fabricant', $infrastructure?->fabricant) }}"
                       maxlength="255"
                       placeholder="ex. Pont-à-Mousson, Grundfos, SONEDE..."
                       class="infra-field" data-field="fabricant">
                <x-ui.validation-message for="fabricant" />
                @error('fabricant')<p style="margin-top:4px;font-size:12px;color:#f87171;">{{ $message }}</p>@enderror
            </div>

            {{-- Zone --}}
            <div>
                <label for="zone_id" style="display:block;font-size:12px;font-weight:600;color:var(--text-secondary,#475569);margin-bottom:5px;">
                    Zone géographique rattachée <span style="color:#f87171">*</span>
                </label>
                <select id="zone_id" name="zone_id" class="infra-field" data-field="zone_id">
                    <option value="">Sélectionnez une zone</option>
                    @foreach($zones as $zone)
                        <option value="{{ $zone->id }}"
                            @selected((string) old('zone_id', $infrastructure?->zone_id ?? $selectedZoneId ?? '') === (string) $zone->id)>
                            {{ $zone->nom }}
                        </option>
                    @endforeach
                </select>
                <x-ui.validation-message for="zone_id" />
                @error('zone_id')<p style="margin-top:4px;font-size:12px;color:#f87171;">{{ $message }}</p>@enderror
            </div>

            {{-- Niveau de criticité --}}
            <div>
                <label for="niveau_criticite" style="display:block;font-size:12px;font-weight:600;color:var(--text-secondary,#475569);margin-bottom:5px;">
                    Niveau de criticité <span style="color:#f87171">*</span>
                </label>
                <select id="niveau_criticite" name="niveau_criticite" class="infra-field" data-field="niveau_criticite">
                    @foreach($niveauxCriticite as $c)
                        <option value="{{ $c->value }}" @selected(old('niveau_criticite', $infrastructure?->niveau_criticite?->value ?? 'moyen') === $c->value)>
                            {{ $c->label() }}
                        </option>
                    @endforeach
                </select>
                <x-ui.validation-message for="niveau_criticite" />
                @error('niveau_criticite')<p style="margin-top:4px;font-size:12px;color:#f87171;">{{ $message }}</p>@enderror
            </div>

        </div>
    </fieldset>

    {{-- ══ Section 2 : Localisation GPS ═══════════════════════════════════════ --}}
    <fieldset style="border:none;padding:0;margin:0;">
        <legend style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;
                       color:var(--role-accent,#0891b2);padding-bottom:8px;
                       border-bottom:1px solid rgba(8,145,178,.15);width:100%;margin-bottom:16px;">
            2 — Localisation GPS
        </legend>
        <p style="font-size:12px;color:var(--text-muted,#94a3b8);margin:-8px 0 16px;">
            Positionnement géographique de l'ouvrage sur le réseau tunisien.
        </p>

        <div style="display:grid;grid-template-columns:2fr 1fr 1fr auto;gap:16px;align-items:start;">

            {{-- Adresse --}}
            <div style="grid-column:1/-1;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:5px;">
                    <label for="localisation" style="font-size:12px;font-weight:600;color:var(--text-secondary,#475569);">
                        Adresse / description du lieu <span style="color:#f87171">*</span>
                    </label>
                    <span id="loc-counter" style="font-size:11px;color:var(--text-muted,#94a3b8);">0 / 255</span>
                </div>
                <input id="localisation" name="localisation"
                       value="{{ old('localisation', $infrastructure?->localisation) }}"
                       maxlength="255"
                       placeholder="ex. Route de Sidi Mansour km 4, Sfax"
                       class="infra-field" data-field="localisation">
                <x-ui.validation-message for="localisation" />
                @error('localisation')<p style="margin-top:4px;font-size:12px;color:#f87171;">{{ $message }}</p>@enderror
            </div>

            {{-- Latitude --}}
            <div>
                <label for="latitude" style="display:block;font-size:12px;font-weight:600;color:var(--text-secondary,#475569);margin-bottom:5px;">
                    Latitude (−90 à 90)
                </label>
                <input id="latitude" name="latitude"
                       type="number" step="0.0000001" min="-90" max="90"
                       value="{{ old('latitude', $infrastructure?->latitude) }}"
                       placeholder="ex. 34.7405000"
                       class="infra-field" data-field="latitude">
                <x-ui.validation-message for="latitude" />
                @error('latitude')<p style="margin-top:4px;font-size:12px;color:#f87171;">{{ $message }}</p>@enderror
            </div>

            {{-- Longitude --}}
            <div>
                <label for="longitude" style="display:block;font-size:12px;font-weight:600;color:var(--text-secondary,#475569);margin-bottom:5px;">
                    Longitude (−180 à 180)
                </label>
                <input id="longitude" name="longitude"
                       type="number" step="0.0000001" min="-180" max="180"
                       value="{{ old('longitude', $infrastructure?->longitude) }}"
                       placeholder="ex. 10.7603000"
                       class="infra-field" data-field="longitude">
                <x-ui.validation-message for="longitude" />
                @error('longitude')<p style="margin-top:4px;font-size:12px;color:#f87171;">{{ $message }}</p>@enderror
            </div>

            {{-- Centrer bouton --}}
            <div style="display:flex;align-items:flex-end;">
                <button type="button" id="btn-center-tunisie"
                        style="display:flex;align-items:center;gap:6px;padding:9px 12px;border-radius:8px;
                               border:1px solid rgba(8,145,178,.3);background:rgba(8,145,178,.08);
                               color:#38bdf8;font-size:12px;font-weight:600;cursor:pointer;white-space:nowrap;
                               transition:background .18s;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
                    Tunisie
                </button>
            </div>

        </div>

        {{-- Carte --}}
        <div style="margin-top:12px;">
            <div id="infra-map-form"
                 style="height:240px;border-radius:10px;border:1px solid var(--border,#e2e8f0);overflow:hidden;"></div>
            <p style="font-size:11px;color:var(--text-muted,#94a3b8);margin-top:6px;">
                💡 Cliquez sur la carte ou déplacez le marqueur pour caler précisément les coordonnées GPS de l'ouvrage.
            </p>
        </div>
    </fieldset>

    {{-- ══ Section 3 : Capacité ════════════════════════════════════════════════ --}}
    <fieldset style="border:none;padding:0;margin:0;">
        <legend style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;
                       color:var(--role-accent,#0891b2);padding-bottom:8px;
                       border-bottom:1px solid rgba(8,145,178,.15);width:100%;margin-bottom:16px;">
            3 — Capacité &amp; dimensionnement
        </legend>
        <p style="font-size:12px;color:var(--text-muted,#94a3b8);margin:-8px 0 16px;">
            Volume ou débit nominal de l'infrastructure hydraulique.
        </p>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">

            {{-- Capacité --}}
            <div>
                <label for="capacite" style="display:block;font-size:12px;font-weight:600;color:var(--text-secondary,#475569);margin-bottom:5px;">
                    Capacité nominale
                </label>
                <input id="capacite" name="capacite"
                       type="number" step="0.01" min="0"
                       value="{{ old('capacite', $infrastructure?->capacite) }}"
                       placeholder="ex. 5000"
                       class="infra-field" data-field="capacite">
                <x-ui.validation-message for="capacite" />
                @error('capacite')<p style="margin-top:4px;font-size:12px;color:#f87171;">{{ $message }}</p>@enderror
            </div>

            {{-- Unité --}}
            <div>
                <label for="unite_capacite" style="display:block;font-size:12px;font-weight:600;color:var(--text-secondary,#475569);margin-bottom:5px;">
                    Unité de mesure
                </label>
                <select id="unite_capacite" name="unite_capacite" class="infra-field" data-field="unite_capacite">
                    <option value="">Non spécifiée</option>
                    @foreach($unitesCapacite as $unite)
                        <option value="{{ $unite }}" @selected(old('unite_capacite', $infrastructure?->unite_capacite) === $unite)>{{ $unite }}</option>
                    @endforeach
                </select>
                <p id="unite-prompt" style="display:none;margin-top:4px;font-size:12px;color:#fbbf24;">
                    Pensez à sélectionner une unité si vous renseignez une capacité.
                </p>
                <x-ui.validation-message for="unite_capacite" />
                @error('unite_capacite')<p style="margin-top:4px;font-size:12px;color:#f87171;">{{ $message }}</p>@enderror
            </div>

        </div>
    </fieldset>

    {{-- ══ Section 4 : Maintenance & Cycle de vie ══════════════════════════════ --}}
    <fieldset style="border:none;padding:0;margin:0;">
        <legend style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;
                       color:var(--role-accent,#0891b2);padding-bottom:8px;
                       border-bottom:1px solid rgba(8,145,178,.15);width:100%;margin-bottom:16px;">
            4 — Maintenance &amp; cycle de vie
        </legend>
        <p style="font-size:12px;color:var(--text-muted,#94a3b8);margin:-8px 0 16px;">
            État de fonctionnement, planning d'entretien et durabilité.
        </p>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">

            {{-- État --}}
            <div>
                <label for="etat" style="display:block;font-size:12px;font-weight:600;color:var(--text-secondary,#475569);margin-bottom:5px;">
                    État opérationnel <span style="color:#f87171">*</span>
                </label>
                <select id="etat" name="etat" class="infra-field" data-field="etat"
                        data-initial="{{ $etatInitialValue }}">
                    @foreach(\App\Enums\InfrastructureEtat::cases() as $e)
                        <option value="{{ $e->value }}" @selected(old('etat', $infrastructure?->etat?->value ?? 'En service') === $e->value)>
                            {{ $e->label() }}
                        </option>
                    @endforeach
                </select>
                <x-ui.validation-message for="etat" />
                @error('etat')<p style="margin-top:4px;font-size:12px;color:#f87171;">{{ $message }}</p>@enderror
            </div>

            {{-- Date d'installation --}}
            <div>
                <label for="date_installation" style="display:block;font-size:12px;font-weight:600;color:var(--text-secondary,#475569);margin-bottom:5px;">
                    Date de mise en service <span style="color:#f87171">*</span>
                </label>
                <input id="date_installation" name="date_installation"
                       type="date" max="{{ date('Y-m-d') }}"
                       value="{{ old('date_installation', $infrastructure?->date_installation?->format('Y-m-d')) }}"
                       class="infra-field" data-field="date_installation">
                <x-ui.validation-message for="date_installation" />
                @error('date_installation')<p style="margin-top:4px;font-size:12px;color:#f87171;">{{ $message }}</p>@enderror
            </div>

            {{-- Bloc motif changement d'état (affiché dynamiquement) --}}
            <div id="bloc-commentaire-etat" style="grid-column:1/-1;display:none;
                 padding:14px;border-radius:10px;border:1px solid rgba(251,191,36,.3);
                 background:rgba(251,191,36,.06);">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                    <div style="display:flex;align-items:center;gap:6px;">
                        <span style="width:8px;height:8px;border-radius:50%;background:#fbbf24;animation:pulse 2s infinite;display:block;"></span>
                        <label for="commentaire_etat" style="font-size:11px;font-weight:700;color:#fbbf24;text-transform:uppercase;letter-spacing:.06em;">
                            Motif du changement d'état
                        </label>
                    </div>
                    <span id="comm-counter" style="font-size:11px;color:rgba(251,191,36,.6);">0 / 500</span>
                </div>
                <textarea id="commentaire_etat" name="commentaire_etat"
                          rows="2" maxlength="500"
                          placeholder="Expliquez la raison de ce changement (ex. rupture de vanne, révision annuelle programmée…)"
                          class="infra-field" data-field="commentaire_etat"
                          style="border-color:rgba(251,191,36,.3);background:rgba(251,191,36,.04);">{{ old('commentaire_etat') }}</textarea>
                <p style="font-size:11px;color:rgba(251,191,36,.7);margin-top:5px;">
                    Ce commentaire sera consigné dans l'historique d'audit de l'infrastructure.
                </p>
                <x-ui.validation-message for="commentaire_etat" />
            </div>

            {{-- Dernière maintenance --}}
            <div>
                <label for="date_derniere_maintenance" style="display:block;font-size:12px;font-weight:600;color:var(--text-secondary,#475569);margin-bottom:5px;">
                    Dernière maintenance réalisée
                </label>
                <input id="date_derniere_maintenance" name="date_derniere_maintenance"
                       type="date"
                       value="{{ old('date_derniere_maintenance', $infrastructure?->date_derniere_maintenance?->format('Y-m-d')) }}"
                       class="infra-field" data-field="date_derniere_maintenance">
                <x-ui.validation-message for="date_derniere_maintenance" />
                @error('date_derniere_maintenance')<p style="margin-top:4px;font-size:12px;color:#f87171;">{{ $message }}</p>@enderror
            </div>

            {{-- Prochaine maintenance --}}
            <div>
                <label for="date_prochaine_maintenance" style="display:block;font-size:12px;font-weight:600;color:var(--text-secondary,#475569);margin-bottom:5px;">
                    Prochaine maintenance prévue
                </label>
                <input id="date_prochaine_maintenance" name="date_prochaine_maintenance"
                       type="date"
                       value="{{ old('date_prochaine_maintenance', $infrastructure?->date_prochaine_maintenance?->format('Y-m-d')) }}"
                       class="infra-field" data-field="date_prochaine_maintenance">
                <x-ui.validation-message for="date_prochaine_maintenance" />
                @error('date_prochaine_maintenance')<p style="margin-top:4px;font-size:12px;color:#f87171;">{{ $message }}</p>@enderror
            </div>

            {{-- Durée de vie --}}
            <div>
                <label for="duree_vie_annees" style="display:block;font-size:12px;font-weight:600;color:var(--text-secondary,#475569);margin-bottom:5px;">
                    Durée de vie estimée (années)
                </label>
                <input id="duree_vie_annees" name="duree_vie_annees"
                       type="number" min="1" max="150" step="1"
                       value="{{ old('duree_vie_annees', $infrastructure?->duree_vie_annees ?? 25) }}"
                       placeholder="ex. 25"
                       class="infra-field" data-field="duree_vie_annees">
                <x-ui.validation-message for="duree_vie_annees" />
                @error('duree_vie_annees')<p style="margin-top:4px;font-size:12px;color:#f87171;">{{ $message }}</p>@enderror
            </div>

        </div>
    </fieldset>

</div>

{{-- ── Boutons d'action ── --}}
<div style="display:flex;justify-content:flex-end;gap:10px;border-top:1px solid var(--border,#e2e8f0);padding-top:20px;margin-top:24px;">
    <a href="{{ route($inventoryRoutePrefix . '.infrastructures.index') }}" class="btn btn-secondary">
        Annuler
    </a>
    <button type="submit" id="btn-submit-infra" class="btn btn-primary">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/><polyline points="17,21 17,13 7,13 7,21"/><polyline points="7,3 7,8 15,8"/></svg>
        Enregistrer l'infrastructure
    </button>
</div>

{{-- ══ Styles locaux ══════════════════════════════════════════════════════════ --}}
<style>
.infra-field {
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
.infra-field:focus {
    border-color: var(--border-focus, #0891b2);
    box-shadow: 0 0 0 3px rgba(8,145,178,.12);
}
.infra-field.field-error {
    border-color: #f87171 !important;
    box-shadow: 0 0 0 3px rgba(248,113,113,.12) !important;
}
.infra-field.field-ok { border-color: #34d399; }
@keyframes pulse { 0%,100%{opacity:1} 50%{opacity:.5} }
</style>

{{-- ══ Leaflet ════════════════════════════════════════════════════════════════ --}}
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha384-sHL9NAb7lN7rfvG5lfHpm643Xkcjzp4jFvuavGOndn6pjVqS6ny56CAt3nsEVT4H" crossorigin="anonymous"/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha384-cxOPjt7s7Iz04uaHJceBmS+qpjv2JkIHNVcuOrM+YHwZOmJGBXI00mdUXEq65HTH" crossorigin="anonymous"></script>

{{-- ══ Validation JS ══════════════════════════════════════════════════════════ --}}
<script>
(function () {
    'use strict';

    var ETAT_INITIAL = '{{ $etatInitialValue }}';

    /* ── Helpers ── */
    function el(id)  { return document.getElementById(id); }
    function val(id) { var e = el(id); return e ? e.value.trim() : ''; }

    function showError(fieldId, message) {
        var input = el(fieldId);
        var errEl  = el(fieldId + '-client-error');
        if (input)  { input.classList.add('field-error'); input.classList.remove('field-ok'); }
        if (errEl)  {
            errEl.style.display = 'flex';
            var span = errEl.querySelector('span');
            if (span) span.textContent = message;
        }
    }

    function clearError(fieldId) {
        var input = el(fieldId);
        var errEl  = el(fieldId + '-client-error');
        if (input)  { input.classList.remove('field-error'); input.classList.add('field-ok'); }
        if (errEl)  { errEl.style.display = 'none'; }
    }

    /* ── Individual validators ── */

    function validateNom() {
        var v = val('nom');
        if (!v)         { showError('nom', 'Le nom de l\'infrastructure est obligatoire.'); return 'Nom : champ obligatoire.'; }
        if (v.length < 3) { showError('nom', 'Le nom doit comporter au moins 3 caractères.'); return 'Nom : minimum 3 caractères.'; }
        clearError('nom'); return null;
    }

    function validateType() {
        var v = val('type');
        if (!v) { showError('type', 'Veuillez sélectionner un type d\'ouvrage.'); return 'Type d\'ouvrage : champ obligatoire.'; }
        clearError('type'); return null;
    }

    function validateZone() {
        var v = val('zone_id');
        if (!v) { showError('zone_id', 'Veuillez sélectionner une zone.'); return 'Zone : champ obligatoire.'; }
        clearError('zone_id'); return null;
    }

    function validateLocalisation() {
        var v = val('localisation');
        if (!v)         { showError('localisation', 'L\'adresse / description du lieu est obligatoire.'); return 'Localisation : champ obligatoire.'; }
        if (v.length < 3) { showError('localisation', 'La localisation doit comporter au moins 3 caractères.'); return 'Localisation : minimum 3 caractères.'; }
        clearError('localisation'); return null;
    }

    function validateLatitude() {
        var input = el('latitude');
        if (!input || input.value === '') { clearError('latitude'); return null; }
        var v = parseFloat(input.value);
        if (isNaN(v) || v < -90 || v > 90) {
            showError('latitude', 'La latitude doit être comprise entre −90° et 90°.');
            return 'Latitude : valeur comprise entre −90 et 90 attendue.';
        }
        clearError('latitude'); return null;
    }

    function validateLongitude() {
        var input = el('longitude');
        if (!input || input.value === '') { clearError('longitude'); return null; }
        var v = parseFloat(input.value);
        if (isNaN(v) || v < -180 || v > 180) {
            showError('longitude', 'La longitude doit être comprise entre −180° et 180°.');
            return 'Longitude : valeur comprise entre −180 et 180 attendue.';
        }
        clearError('longitude'); return null;
    }

    function validateEtat() {
        var v = val('etat');
        if (!v) { showError('etat', 'Veuillez sélectionner un état opérationnel.'); return 'État opérationnel : champ obligatoire.'; }
        clearError('etat'); return null;
    }

    function validateDateInstall() {
        var input = el('date_installation');
        if (!input || !input.value) {
            showError('date_installation', 'La date de mise en service est obligatoire.');
            return 'Date de mise en service : champ obligatoire.';
        }
        var today = new Date().toISOString().split('T')[0];
        if (input.value > today) {
            showError('date_installation', 'La date de mise en service ne peut pas être dans le futur.');
            return 'Date de mise en service : ne peut pas être dans le futur.';
        }
        clearError('date_installation'); return null;
    }

    function validateMaintenanceDates() {
        var derniere  = el('date_derniere_maintenance');
        var prochaine = el('date_prochaine_maintenance');
        if (!derniere || !prochaine) return null;
        clearError('date_derniere_maintenance');
        if (prochaine.value && derniere.value && prochaine.value < derniere.value) {
            showError('date_prochaine_maintenance', 'La prochaine maintenance doit être postérieure ou égale à la dernière maintenance.');
            return 'Dates maintenance : la date prochaine doit être postérieure à la dernière.';
        }
        clearError('date_prochaine_maintenance'); return null;
    }

    function validateDureeVie() {
        var input = el('duree_vie_annees');
        if (!input || input.value === '') { clearError('duree_vie_annees'); return null; }
        var v = parseInt(input.value, 10);
        if (isNaN(v) || v < 1 || v > 150) {
            showError('duree_vie_annees', 'La durée de vie doit être comprise entre 1 et 150 ans.');
            return 'Durée de vie : valeur entre 1 et 150 ans attendue.';
        }
        clearError('duree_vie_annees'); return null;
    }

    function validateCapacite() {
        var input = el('capacite');
        if (!input || input.value === '') {
            clearError('capacite');
            if (el('unite-prompt')) el('unite-prompt').style.display = 'none';
            return null;
        }
        var v = parseFloat(input.value);
        if (isNaN(v) || v < 0) {
            showError('capacite', 'La capacité doit être un nombre positif ou nul.');
            return 'Capacité : nombre positif ou nul attendu.';
        }
        clearError('capacite');
        /* Hint: suggest selecting unit if not chosen */
        var unite = el('unite_capacite');
        if (unite && !unite.value && el('unite-prompt')) {
            el('unite-prompt').style.display = 'block';
        } else if (el('unite-prompt')) {
            el('unite-prompt').style.display = 'none';
        }
        return null;
    }

    /* ── DOMContentLoaded ── */
    document.addEventListener('DOMContentLoaded', function () {

        /* Character counters */
        function bindCounter(inputId, counterId, max) {
            var inp = el(inputId), ctr = el(counterId);
            if (!inp || !ctr) return;
            function upd() { ctr.textContent = inp.value.length + ' / ' + max; }
            inp.addEventListener('input', upd); upd();
        }
        bindCounter('nom',        'nom-counter', 255);
        bindCounter('fabricant',  'fab-counter', 255);
        bindCounter('localisation', 'loc-counter', 255);
        bindCounter('commentaire_etat', 'comm-counter', 500);

        /* Auto-uppercase reference */
        var refInput = el('reference');
        if (refInput) {
            refInput.addEventListener('input', function () {
                var pos = this.selectionStart;
                this.value = this.value.toUpperCase();
                this.setSelectionRange(pos, pos);
                if (el('ref-counter')) el('ref-counter').textContent = this.value.length + ' / 255';
            });
            if (el('ref-counter')) el('ref-counter').textContent = refInput.value.length + ' / 255';
        }

        /* State change → show/hide comment block */
        var etatSelect = el('etat');
        var blocComm   = el('bloc-commentaire-etat');
        function checkEtatChange() {
            if (!etatSelect || !blocComm) return;
            var changed = (ETAT_INITIAL === '') || (etatSelect.value !== ETAT_INITIAL);
            blocComm.style.display = changed ? 'block' : 'none';
        }
        if (etatSelect) { etatSelect.addEventListener('change', checkEtatChange); checkEtatChange(); }

        /* Sync maintenance dates min */
        var dateDerniere  = el('date_derniere_maintenance');
        var dateProchaine = el('date_prochaine_maintenance');
        if (dateDerniere) {
            dateDerniere.addEventListener('change', function () {
                if (dateProchaine) dateProchaine.min = this.value || '';
                validateMaintenanceDates();
            });
        }
        if (dateProchaine) dateProchaine.addEventListener('change', validateMaintenanceDates);

        /* Unite capacite hint */
        var capInput   = el('capacite');
        var uniteInput = el('unite_capacite');
        if (capInput)   capInput.addEventListener('blur',   validateCapacite);
        if (uniteInput) uniteInput.addEventListener('change', function () {
            if (this.value && el('unite-prompt')) el('unite-prompt').style.display = 'none';
        });

        /* Blur bindings */
        if (el('nom'))                      el('nom').addEventListener('blur',       validateNom);
        if (el('type'))                     el('type').addEventListener('change',    validateType);
        if (el('zone_id'))                  el('zone_id').addEventListener('change', validateZone);
        if (el('localisation'))             el('localisation').addEventListener('blur', validateLocalisation);
        if (el('latitude'))                 el('latitude').addEventListener('blur',  validateLatitude);
        if (el('longitude'))                el('longitude').addEventListener('blur', validateLongitude);
        if (el('etat'))                     el('etat').addEventListener('change',    validateEtat);
        if (el('date_installation'))        el('date_installation').addEventListener('change', validateDateInstall);
        if (el('duree_vie_annees'))         el('duree_vie_annees').addEventListener('blur', validateDureeVie);

        /* Clear error while typing */
        document.querySelectorAll('.infra-field').forEach(function (inp) {
            inp.addEventListener('input', function () {
                var id = this.id || this.name;
                var errEl = el(id + '-client-error');
                if (errEl) errEl.style.display = 'none';
                this.classList.remove('field-error');
            });
        });

        /* ── Submit ── */
        var nomInput = el('nom');
        var form = nomInput ? nomInput.closest('form') : null;
        if (form) {
            form.addEventListener('submit', function (e) {
                var errors = [];
                [validateNom, validateType, validateZone, validateLocalisation,
                 validateLatitude, validateLongitude, validateEtat,
                 validateDateInstall, validateMaintenanceDates, validateDureeVie, validateCapacite
                ].forEach(function (fn) {
                    var err = fn();
                    if (err) errors.push(err);
                });

                if (errors.length > 0) {
                    e.preventDefault();
                    var banner = el('infra-error-banner');
                    var list   = el('infra-error-list');
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
                    var firstErr = form.querySelector('.field-error');
                    if (firstErr) firstErr.focus();
                } else {
                    var banner2 = el('infra-error-banner');
                    if (banner2) banner2.style.display = 'none';
                    var btn = el('btn-submit-infra');
                    if (btn) {
                        btn.disabled = true;
                        btn.innerHTML = '<svg style="animation:spin 1s linear infinite;width:14px;height:14px;margin-right:6px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12a9 9 0 11-6.219-8.56"/></svg>Enregistrement...';
                    }
                }
            });
        }

        /* ── Leaflet map ── */
        var mapEl   = el('infra-map-form');
        var latInp  = el('latitude');
        var lngInp  = el('longitude');

        if (mapEl && typeof L !== 'undefined') {
            var iLat = parseFloat(latInp ? latInp.value : '') || 36.8065;
            var iLng = parseFloat(lngInp ? lngInp.value : '') || 10.1815;
            var hasCo = !isNaN(parseFloat(latInp ? latInp.value : ''))
                     && !isNaN(parseFloat(lngInp ? lngInp.value : ''));

            var map = L.map('infra-map-form', {
                center: [iLat, iLng], zoom: hasCo ? 12 : 6, zoomControl: true
            });
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map);

            var marker = null;
            if (hasCo) {
                marker = L.marker([iLat, iLng], { draggable: true }).addTo(map);
                marker.on('dragend', function (ev) {
                    var p = ev.target.getLatLng();
                    if (latInp) latInp.value = p.lat.toFixed(7);
                    if (lngInp) lngInp.value = p.lng.toFixed(7);
                    clearError('latitude'); clearError('longitude');
                });
            }

            map.on('click', function (ev) {
                if (marker) map.removeLayer(marker);
                marker = L.marker([ev.latlng.lat, ev.latlng.lng], { draggable: true }).addTo(map);
                if (latInp) latInp.value = ev.latlng.lat.toFixed(7);
                if (lngInp) lngInp.value = ev.latlng.lng.toFixed(7);
                clearError('latitude'); clearError('longitude');
                marker.on('dragend', function (ev2) {
                    var p = ev2.target.getLatLng();
                    if (latInp) latInp.value = p.lat.toFixed(7);
                    if (lngInp) lngInp.value = p.lng.toFixed(7);
                    clearError('latitude'); clearError('longitude');
                });
            });

            function syncMap() {
                var la = parseFloat(latInp ? latInp.value : '');
                var lo = parseFloat(lngInp ? lngInp.value : '');
                if (!isNaN(la) && !isNaN(lo) && la >= -90 && la <= 90 && lo >= -180 && lo <= 180) {
                    if (marker) marker.setLatLng([la, lo]);
                    else marker = L.marker([la, lo], { draggable: true }).addTo(map);
                    map.setView([la, lo], 12);
                }
            }
            if (latInp) latInp.addEventListener('change', syncMap);
            if (lngInp) lngInp.addEventListener('change', syncMap);

            var btnC = el('btn-center-tunisie');
            if (btnC) btnC.addEventListener('click', function () { map.setView([34.0, 9.5], 6); });
        }

    }); // DOMContentLoaded

    /* Spinner keyframe */
    var s = document.createElement('style');
    s.textContent = '@keyframes spin{to{transform:rotate(360deg)}}';
    document.head.appendChild(s);

})();
</script>
