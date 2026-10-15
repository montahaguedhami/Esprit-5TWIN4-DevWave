@csrf

@php
    $niveauxCriticite = $niveauxCriticite ?? \App\Enums\NiveauCriticite::cases();
    $unitesCapacite = $unitesCapacite ?? ['m3', 'm3/h', 'l/s'];
@endphp

{{-- Bannière d'erreurs récapitulative dynamique --}}
<div id="infra-form-error-summary" class="hidden rounded-2xl border border-rose-500/40 bg-rose-500/10 p-4 text-xs text-rose-200 shadow-sm animate-pulse">
    <div class="flex items-center gap-2 font-semibold text-rose-300">
        <svg class="h-4 w-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
        </svg>
        <span>Veuillez corriger les informations suivantes avant d'enregistrer l'infrastructure :</span>
    </div>
    <ul id="infra-form-error-list" class="mt-2 list-disc list-inside space-y-1 text-rose-200/90 pl-1"></ul>
</div>

<div class="space-y-6">
    {{-- Section 1 : Informations générales --}}
    <div class="space-y-4">
        <div class="border-b border-cyan-400/10 pb-2">
            <h2 class="text-sm font-semibold tracking-wide text-cyan-300 uppercase">1. Informations générales</h2>
            <p class="text-xs text-cyan-100/60">Identité de l’équipement, typologie et rattachement au réseau.</p>
        </div>
        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <div class="flex justify-between items-center mb-1.5">
                    <label for="nom" class="block text-sm font-medium text-cyan-100/80">Nom de l’infrastructure <span class="text-rose-400">*</span></label>
                    <span id="nom-counter" class="text-[11px] text-cyan-200/50">0 / 255</span>
                </div>
                <input
                    id="nom"
                    name="nom"
                    value="{{ old('nom', $infrastructure?->nom) }}"
                    required
                    minlength="3"
                    maxlength="255"
                    placeholder="ex. Réservoir Sud RS-04"
                    class="w-full rounded-lg border border-cyan-400/20 bg-slate-950/40 px-3.5 py-2.5 text-sm text-white outline-none transition focus:border-cyan-400/50 focus:ring-2 focus:ring-cyan-400/20"
                >
                <p id="nom-client-error" class="hidden mt-1 text-xs text-rose-400"></p>
                @error('nom')<p class="mt-1 text-sm text-rose-400">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="type" class="mb-1.5 block text-sm font-medium text-cyan-100/80">Type d'ouvrage <span class="text-rose-400">*</span></label>
                <select id="type" name="type" required class="w-full rounded-lg border border-cyan-400/20 bg-slate-950/40 px-3.5 py-2.5 text-sm text-white outline-none transition focus:border-cyan-400/50 focus:ring-2 focus:ring-cyan-400/20">
                    <option value="">Sélectionnez un type</option>
                    @foreach(\App\Enums\InfrastructureType::cases() as $type)
                        <option value="{{ $type->value }}" @selected(old('type', $infrastructure?->type?->value) === $type->value)>{{ $type->label() }}</option>
                    @endforeach
                </select>
                <p id="type-client-error" class="hidden mt-1 text-xs text-rose-400"></p>
                @error('type')<p class="mt-1 text-sm text-rose-400">{{ $message }}</p>@enderror
            </div>
            <div>
                <div class="flex justify-between items-center mb-1.5">
                    <label for="reference" class="block text-sm font-medium text-cyan-100/80">Référence interne / code équipement</label>
                    <span id="ref-counter" class="text-[11px] text-cyan-200/50">0 / 255</span>
                </div>
                <input
                    id="reference"
                    name="reference"
                    value="{{ old('reference', $infrastructure?->reference) }}"
                    maxlength="255"
                    placeholder="ex. INF-2026-TUN-042"
                    class="w-full rounded-lg border border-cyan-400/20 bg-slate-950/40 px-3.5 py-2.5 text-sm text-white uppercase outline-none transition focus:border-cyan-400/50 focus:ring-2 focus:ring-cyan-400/20"
                >
                <p class="mt-1 text-[11px] text-cyan-200/40">Converti automatiquement en majuscules (ex. RES-TUN-01).</p>
                @error('reference')<p class="mt-1 text-sm text-rose-400">{{ $message }}</p>@enderror
            </div>
            <div>
                <div class="flex justify-between items-center mb-1.5">
                    <label for="fabricant" class="block text-sm font-medium text-cyan-100/80">Fabricant / Constructeur</label>
                    <span id="fab-counter" class="text-[11px] text-cyan-200/50">0 / 255</span>
                </div>
                <input
                    id="fabricant"
                    name="fabricant"
                    value="{{ old('fabricant', $infrastructure?->fabricant) }}"
                    maxlength="255"
                    placeholder="ex. Pont-à-Mousson, Grundfos, SONEDE..."
                    class="w-full rounded-lg border border-cyan-400/20 bg-slate-950/40 px-3.5 py-2.5 text-sm text-white outline-none transition focus:border-cyan-400/50 focus:ring-2 focus:ring-cyan-400/20"
                >
                @error('fabricant')<p class="mt-1 text-sm text-rose-400">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="zone_id" class="mb-1.5 block text-sm font-medium text-cyan-100/80">Zone géographique rattachée <span class="text-rose-400">*</span></label>
                <select id="zone_id" name="zone_id" required class="w-full rounded-lg border border-cyan-400/20 bg-slate-950/40 px-3.5 py-2.5 text-sm text-white outline-none transition focus:border-cyan-400/50 focus:ring-2 focus:ring-cyan-400/20">
                    <option value="">Sélectionnez une zone</option>
                    @foreach($zones as $zone)
                        <option value="{{ $zone->id }}" @selected((string) old('zone_id', $infrastructure?->zone_id ?? $selectedZoneId ?? '') === (string) $zone->id)>{{ $zone->nom }}</option>
                    @endforeach
                </select>
                <p id="zone-client-error" class="hidden mt-1 text-xs text-rose-400"></p>
                @error('zone_id')<p class="mt-1 text-sm text-rose-400">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="niveau_criticite" class="mb-1.5 block text-sm font-medium text-cyan-100/80">Niveau de criticité <span class="text-rose-400">*</span></label>
                <select id="niveau_criticite" name="niveau_criticite" required class="w-full rounded-lg border border-cyan-400/20 bg-slate-950/40 px-3.5 py-2.5 text-sm text-white outline-none transition focus:border-cyan-400/50 focus:ring-2 focus:ring-cyan-400/20">
                    @foreach($niveauxCriticite as $criticite)
                        <option value="{{ $criticite->value }}" @selected(old('niveau_criticite', $infrastructure?->niveau_criticite?->value ?? 'moyen') === $criticite->value)>{{ $criticite->label() }}</option>
                    @endforeach
                </select>
                @error('niveau_criticite')<p class="mt-1 text-sm text-rose-400">{{ $message }}</p>@enderror
            </div>
        </div>
    </div>

    {{-- Section 2 : Localisation GPS --}}
    <div class="space-y-4">
        <div class="border-b border-cyan-400/10 pb-2">
            <h2 class="text-sm font-semibold tracking-wide text-cyan-300 uppercase">2. Localisation GPS</h2>
            <p class="text-xs text-cyan-100/60">Positionnement géographique sur le réseau tunisien.</p>
        </div>
        <div class="grid gap-4 sm:grid-cols-3">
            <div class="sm:col-span-3">
                <div class="flex justify-between items-center mb-1.5">
                    <label for="localisation" class="block text-sm font-medium text-cyan-100/80">Adresse / description du lieu <span class="text-rose-400">*</span></label>
                    <span id="loc-counter" class="text-[11px] text-cyan-200/50">0 / 255</span>
                </div>
                <input
                    id="localisation"
                    name="localisation"
                    value="{{ old('localisation', $infrastructure?->localisation) }}"
                    required
                    minlength="3"
                    maxlength="255"
                    placeholder="ex. Route de Sidi Mansour km 4, Sfax"
                    class="w-full rounded-lg border border-cyan-400/20 bg-slate-950/40 px-3.5 py-2.5 text-sm text-white outline-none transition focus:border-cyan-400/50 focus:ring-2 focus:ring-cyan-400/20"
                >
                <p id="localisation-client-error" class="hidden mt-1 text-xs text-rose-400"></p>
                @error('localisation')<p class="mt-1 text-sm text-rose-400">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="latitude" class="mb-1.5 block text-sm font-medium text-cyan-100/80">Latitude (-90 à 90)</label>
                <input
                    id="latitude"
                    type="number"
                    step="0.0000001"
                    min="-90"
                    max="90"
                    name="latitude"
                    value="{{ old('latitude', $infrastructure?->latitude) }}"
                    placeholder="ex. 34.7405000"
                    class="w-full rounded-lg border border-cyan-400/20 bg-slate-950/40 px-3.5 py-2.5 text-sm text-white outline-none transition focus:border-cyan-400/50 focus:ring-2 focus:ring-cyan-400/20"
                >
                <p id="lat-client-error" class="hidden mt-1 text-xs text-rose-400"></p>
                @error('latitude')<p class="mt-1 text-sm text-rose-400">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="longitude" class="mb-1.5 block text-sm font-medium text-cyan-100/80">Longitude (-180 à 180)</label>
                <input
                    id="longitude"
                    type="number"
                    step="0.0000001"
                    min="-180"
                    max="180"
                    name="longitude"
                    value="{{ old('longitude', $infrastructure?->longitude) }}"
                    placeholder="ex. 10.7603000"
                    class="w-full rounded-lg border border-cyan-400/20 bg-slate-950/40 px-3.5 py-2.5 text-sm text-white outline-none transition focus:border-cyan-400/50 focus:ring-2 focus:ring-cyan-400/20"
                >
                <p id="lng-client-error" class="hidden mt-1 text-xs text-rose-400"></p>
                @error('longitude')<p class="mt-1 text-sm text-rose-400">{{ $message }}</p>@enderror
            </div>
            <div class="flex items-end">
                <button
                    type="button"
                    id="btn-center-tunisie"
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
                <div id="infra-map-form" class="h-64 w-full rounded-xl border border-cyan-400/20 overflow-hidden shadow-inner bg-slate-950/60"></div>
                <div class="mt-1.5 flex flex-wrap items-center justify-between text-xs text-cyan-100/50">
                    <p>💡 Cliquez sur la carte ou déplacez le marqueur pour caler précisément les coordonnées GPS de l'ouvrage.</p>
                    <span class="text-[11px] text-cyan-200/40">Zone Tunisie : 30° à 38° N · 7° à 12° E</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Section 3 : Capacité & Dimensionnement --}}
    <div class="space-y-4">
        <div class="border-b border-cyan-400/10 pb-2">
            <h2 class="text-sm font-semibold tracking-wide text-cyan-300 uppercase">3. Capacité & dimensionnement</h2>
            <p class="text-xs text-cyan-100/60">Volume ou débit nominal de l’infrastructure hydraulique.</p>
        </div>
        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label for="capacite" class="mb-1.5 block text-sm font-medium text-cyan-100/80">Capacité nominale</label>
                <input
                    id="capacite"
                    type="number"
                    step="0.01"
                    min="0"
                    name="capacite"
                    value="{{ old('capacite', $infrastructure?->capacite) }}"
                    placeholder="ex. 5000"
                    class="w-full rounded-lg border border-cyan-400/20 bg-slate-950/40 px-3.5 py-2.5 text-sm text-white outline-none transition focus:border-cyan-400/50 focus:ring-2 focus:ring-cyan-400/20"
                >
                <p id="capacite-client-error" class="hidden mt-1 text-xs text-rose-400"></p>
                @error('capacite')<p class="mt-1 text-sm text-rose-400">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="unite_capacite" class="mb-1.5 block text-sm font-medium text-cyan-100/80">Unité de mesure</label>
                <select id="unite_capacite" name="unite_capacite" class="w-full rounded-lg border border-cyan-400/20 bg-slate-950/40 px-3.5 py-2.5 text-sm text-white outline-none transition focus:border-cyan-400/50 focus:ring-2 focus:ring-cyan-400/20">
                    <option value="">Non spécifiée</option>
                    @foreach($unitesCapacite as $unite)
                        <option value="{{ $unite }}" @selected(old('unite_capacite', $infrastructure?->unite_capacite) === $unite)>{{ $unite }}</option>
                    @endforeach
                </select>
                <p id="unite-client-prompt" class="hidden mt-1 text-xs text-amber-300">Pensez à sélectionner une unité si vous renseignez une capacité.</p>
                @error('unite_capacite')<p class="mt-1 text-sm text-rose-400">{{ $message }}</p>@enderror
            </div>
        </div>
    </div>

    {{-- Section 4 : Maintenance & Cycle de vie --}}
    <div class="space-y-4">
        <div class="border-b border-cyan-400/10 pb-2">
            <h2 class="text-sm font-semibold tracking-wide text-cyan-300 uppercase">4. Maintenance & cycle de vie</h2>
            <p class="text-xs text-cyan-100/60">État de fonctionnement, planning d’entretien et durabilité.</p>
        </div>
        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label for="etat" class="mb-1.5 block text-sm font-medium text-cyan-100/80">État opérationnel <span class="text-rose-400">*</span></label>
                <select id="etat" name="etat" required class="w-full rounded-lg border border-cyan-400/20 bg-slate-950/40 px-3.5 py-2.5 text-sm text-white outline-none transition focus:border-cyan-400/50 focus:ring-2 focus:ring-cyan-400/20">
                    @foreach(\App\Enums\InfrastructureEtat::cases() as $etat)
                        <option value="{{ $etat->value }}" @selected(old('etat', $infrastructure?->etat?->value ?? 'En service') === $etat->value)>{{ $etat->label() }}</option>
                    @endforeach
                </select>
                @error('etat')<p class="mt-1 text-sm text-rose-400">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="date_installation" class="mb-1.5 block text-sm font-medium text-cyan-100/80">Date de mise en service <span class="text-rose-400">*</span></label>
                <input
                    id="date_installation"
                    type="date"
                    max="{{ date('Y-m-d') }}"
                    name="date_installation"
                    value="{{ old('date_installation', $infrastructure?->date_installation?->format('Y-m-d')) }}"
                    required
                    class="w-full rounded-lg border border-cyan-400/20 bg-slate-950/40 px-3.5 py-2.5 text-sm text-white outline-none transition focus:border-cyan-400/50 focus:ring-2 focus:ring-cyan-400/20"
                >
                <p id="date-install-client-error" class="hidden mt-1 text-xs text-rose-400"></p>
                @error('date_installation')<p class="mt-1 text-sm text-rose-400">{{ $message }}</p>@enderror
            </div>

            {{-- Commentaire visible si l'état est modifié --}}
            <div id="bloc-commentaire-etat" class="sm:col-span-2 hidden rounded-xl border border-amber-400/30 bg-amber-500/10 p-3.5 space-y-2">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="inline-block h-2 w-2 rounded-full bg-amber-400 animate-pulse"></span>
                        <label for="commentaire_etat" class="text-xs font-semibold text-amber-200 uppercase tracking-wide">
                            Motif du changement d’état
                        </label>
                    </div>
                    <span id="comm-counter" class="text-[11px] text-amber-200/60">0 / 500</span>
                </div>
                <textarea
                    id="commentaire_etat"
                    name="commentaire_etat"
                    rows="2"
                    maxlength="500"
                    placeholder="Expliquez la raison de ce changement d'état (ex: Rupture de vanne, révision annuelle programmée...)"
                    class="w-full rounded-lg border border-amber-400/30 bg-slate-950/60 p-2.5 text-sm text-white placeholder:text-amber-100/40 outline-none focus:border-amber-400/60"
                >{{ old('commentaire_etat') }}</textarea>
                <p class="text-[11px] text-amber-100/70">Ce commentaire sera consigné dans l'historique d'audit de l'infrastructure.</p>
            </div>

            <div>
                <label for="date_derniere_maintenance" class="mb-1.5 block text-sm font-medium text-cyan-100/80">Dernière maintenance réalisée</label>
                <input
                    id="date_derniere_maintenance"
                    type="date"
                    name="date_derniere_maintenance"
                    value="{{ old('date_derniere_maintenance', $infrastructure?->date_derniere_maintenance?->format('Y-m-d')) }}"
                    class="w-full rounded-lg border border-cyan-400/20 bg-slate-950/40 px-3.5 py-2.5 text-sm text-white outline-none transition focus:border-cyan-400/50 focus:ring-2 focus:ring-cyan-400/20"
                >
                @error('date_derniere_maintenance')<p class="mt-1 text-sm text-rose-400">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="date_prochaine_maintenance" class="mb-1.5 block text-sm font-medium text-cyan-100/80">Prochaine maintenance prévue</label>
                <input
                    id="date_prochaine_maintenance"
                    type="date"
                    name="date_prochaine_maintenance"
                    value="{{ old('date_prochaine_maintenance', $infrastructure?->date_prochaine_maintenance?->format('Y-m-d')) }}"
                    class="w-full rounded-lg border border-cyan-400/20 bg-slate-950/40 px-3.5 py-2.5 text-sm text-white outline-none transition focus:border-cyan-400/50 focus:ring-2 focus:ring-cyan-400/20"
                >
                <p id="date-prochaine-client-error" class="hidden mt-1 text-xs text-rose-400"></p>
                @error('date_prochaine_maintenance')<p class="mt-1 text-sm text-rose-400">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="duree_vie_annees" class="mb-1.5 block text-sm font-medium text-cyan-100/80">Durée de vie estimée (années)</label>
                <input
                    id="duree_vie_annees"
                    type="number"
                    min="1"
                    max="150"
                    step="1"
                    name="duree_vie_annees"
                    value="{{ old('duree_vie_annees', $infrastructure?->duree_vie_annees ?? 25) }}"
                    placeholder="ex. 25"
                    class="w-full rounded-lg border border-cyan-400/20 bg-slate-950/40 px-3.5 py-2.5 text-sm text-white outline-none transition focus:border-cyan-400/50 focus:ring-2 focus:ring-cyan-400/20"
                >
                <p id="duree-client-error" class="hidden mt-1 text-xs text-rose-400"></p>
                @error('duree_vie_annees')<p class="mt-1 text-sm text-rose-400">{{ $message }}</p>@enderror
            </div>
        </div>
    </div>
</div>

<div class="flex justify-end gap-2 border-t border-white/10 pt-4 mt-6">
    <a href="{{ route($inventoryRoutePrefix . '.infrastructures.index') }}" class="rounded-lg border border-cyan-400/20 px-4 py-2 text-sm font-medium text-cyan-100/70 hover:bg-white/5 transition">Annuler</a>
    <button
        type="submit"
        id="btn-submit-infra"
        class="rounded-lg bg-cyan-600 px-5 py-2 text-sm font-medium text-white hover:bg-cyan-500 shadow-sm transition flex items-center gap-2"
    >
        <span>Enregistrer l'infrastructure</span>
    </button>
</div>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const nomInput = document.getElementById('nom');
        const nomCounter = document.getElementById('nom-counter');
        const nomError = document.getElementById('nom-client-error');

        const typeSelect = document.getElementById('type');
        const typeError = document.getElementById('type-client-error');

        const refInput = document.getElementById('reference');
        const refCounter = document.getElementById('ref-counter');

        const fabInput = document.getElementById('fabricant');
        const fabCounter = document.getElementById('fab-counter');

        const zoneSelect = document.getElementById('zone_id');
        const zoneError = document.getElementById('zone-client-error');

        const locInput = document.getElementById('localisation');
        const locCounter = document.getElementById('loc-counter');
        const locError = document.getElementById('localisation-client-error');

        const capInput = document.getElementById('capacite');
        const capError = document.getElementById('capacite-client-error');
        const uniteSelect = document.getElementById('unite_capacite');
        const unitePrompt = document.getElementById('unite-client-prompt');

        const etatSelect = document.getElementById('etat');
        const blocCommentaire = document.getElementById('bloc-commentaire-etat');
        const commInput = document.getElementById('commentaire_etat');
        const commCounter = document.getElementById('comm-counter');
        const etatInitial = "{{ $infrastructure?->etat?->value ?? '' }}";

        const dateInstall = document.getElementById('date_installation');
        const dateInstallError = document.getElementById('date-install-client-error');

        const dateDerniere = document.getElementById('date_derniere_maintenance');
        const dateProchaine = document.getElementById('date_prochaine_maintenance');
        const dateProchaineError = document.getElementById('date-prochaine-client-error');

        const dureeInput = document.getElementById('duree_vie_annees');
        const dureeError = document.getElementById('duree-client-error');

        const latInput = document.getElementById('latitude');
        const latError = document.getElementById('lat-client-error');
        const lngInput = document.getElementById('longitude');
        const lngError = document.getElementById('lng-client-error');

        const errorSummary = document.getElementById('infra-form-error-summary');
        const errorList = document.getElementById('infra-form-error-list');
        const form = nomInput ? nomInput.closest('form') : null;

        // Auto uppercase pour la référence
        if (refInput) {
            refInput.addEventListener('input', function () {
                this.value = this.value.toUpperCase();
                if (refCounter) refCounter.textContent = this.value.length + ' / 255';
            });
        }

        // Compteurs de texte
        function updateCounters() {
            if (nomInput && nomCounter) nomCounter.textContent = (nomInput.value ? nomInput.value.length : 0) + ' / 255';
            if (locInput && locCounter) locCounter.textContent = (locInput.value ? locInput.value.length : 0) + ' / 255';
            if (fabInput && fabCounter) fabCounter.textContent = (fabInput.value ? fabInput.value.length : 0) + ' / 255';
            if (commInput && commCounter) commCounter.textContent = (commInput.value ? commInput.value.length : 0) + ' / 500';
        }
        if (nomInput) nomInput.addEventListener('input', updateCounters);
        if (locInput) locInput.addEventListener('input', updateCounters);
        if (fabInput) fabInput.addEventListener('input', updateCounters);
        if (commInput) commInput.addEventListener('input', updateCounters);
        updateCounters();

        // Affichage dynamique motif de changement d'état
        function checkEtatChange() {
            if (etatSelect && blocCommentaire) {
                if (etatInitial !== '' && etatSelect.value !== etatInitial) {
                    blocCommentaire.classList.remove('hidden');
                } else if (etatInitial === '') {
                    blocCommentaire.classList.remove('hidden');
                } else {
                    blocCommentaire.classList.add('hidden');
                }
            }
        }
        if (etatSelect) {
            etatSelect.addEventListener('change', checkEtatChange);
            checkEtatChange();
        }

        // Synchronisation des dates de maintenance
        function syncMaintenanceDates() {
            if (dateDerniere && dateProchaine) {
                if (dateDerniere.value) {
                    dateProchaine.min = dateDerniere.value;
                }
                if (dateDerniere.value && dateProchaine.value) {
                    if (dateProchaine.value < dateDerniere.value) {
                        showFieldState(dateProchaine, dateProchaineError, 'La prochaine maintenance doit être postérieure ou égale à la dernière maintenance.');
                        return 'La date de prochaine maintenance doit être postérieure ou égale à la date de dernière maintenance.';
                    } else {
                        clearFieldState(dateProchaine, dateProchaineError);
                    }
                } else {
                    clearFieldState(dateProchaine, dateProchaineError);
                }
            }
            return null;
        }
        if (dateDerniere) dateDerniere.addEventListener('change', syncMaintenanceDates);
        if (dateProchaine) dateProchaine.addEventListener('change', syncMaintenanceDates);

        // Validation en temps réel de la date d'installation
        function validateDateInstall() {
            if (!dateInstall || !dateInstall.value) {
                showFieldState(dateInstall, dateInstallError, 'La date de mise en service est obligatoire.');
                return 'La date de mise en service est obligatoire.';
            }
            const today = new Date().toISOString().split('T')[0];
            if (dateInstall.value > today) {
                showFieldState(dateInstall, dateInstallError, 'La date de mise en service ne peut pas être dans le futur.');
                return 'La date de mise en service ne peut pas être postérieure à aujourd’hui.';
            }
            clearFieldState(dateInstall, dateInstallError);
            return null;
        }
        if (dateInstall) dateInstall.addEventListener('change', validateDateInstall);

        // Validation capacité & unité
        function validateCapacite() {
            if (capInput && capInput.value !== '') {
                const val = parseFloat(capInput.value);
                if (isNaN(val) || val < 0) {
                    showFieldState(capInput, capError, 'La capacité doit être un nombre positif ou nul.');
                    return 'La capacité doit être un nombre positif.';
                } else {
                    clearFieldState(capInput, capError);
                }
                if (val > 0 && uniteSelect && !uniteSelect.value) {
                    unitePrompt.classList.remove('hidden');
                } else if (unitePrompt) {
                    unitePrompt.classList.add('hidden');
                }
            } else {
                clearFieldState(capInput, capError);
                if (unitePrompt) unitePrompt.classList.add('hidden');
            }
            return null;
        }
        if (capInput) capInput.addEventListener('input', validateCapacite);
        if (uniteSelect) uniteSelect.addEventListener('change', validateCapacite);

        // Validation durée de vie
        function validateDuree() {
            if (dureeInput && dureeInput.value !== '') {
                const val = parseInt(dureeInput.value, 10);
                if (isNaN(val) || val < 1 || val > 150) {
                    showFieldState(dureeInput, dureeError, 'La durée de vie doit être comprise entre 1 et 150 ans.');
                    return 'La durée de vie doit être comprise entre 1 et 150 ans.';
                }
            }
            clearFieldState(dureeInput, dureeError);
            return null;
        }
        if (dureeInput) dureeInput.addEventListener('input', validateDuree);

        // Validation GPS
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
        if (latInput) latInput.addEventListener('input', validateGPS);
        if (lngInput) lngInput.addEventListener('input', validateGPS);

        function validateNom() {
            const val = nomInput ? nomInput.value.trim() : '';
            if (!val) {
                showFieldState(nomInput, nomError, 'Le nom de l’infrastructure est obligatoire.');
                return 'Le nom de l’infrastructure est obligatoire.';
            }
            if (val.length < 3) {
                showFieldState(nomInput, nomError, 'Le nom doit comporter au moins 3 caractères.');
                return 'Le nom de l’infrastructure doit comporter au moins 3 caractères.';
            }
            clearFieldState(nomInput, nomError);
            return null;
        }

        function validateType() {
            if (typeSelect && !typeSelect.value) {
                showFieldState(typeSelect, typeError, 'Veuillez sélectionner un type d’infrastructure.');
                return 'Le type d’infrastructure est obligatoire.';
            }
            clearFieldState(typeSelect, typeError);
            return null;
        }

        function validateZone() {
            if (zoneSelect && !zoneSelect.value) {
                showFieldState(zoneSelect, zoneError, 'Veuillez sélectionner la zone de rattachement.');
                return 'La zone géographique est obligatoire.';
            }
            clearFieldState(zoneSelect, zoneError);
            return null;
        }

        function validateLoc() {
            const val = locInput ? locInput.value.trim() : '';
            if (!val) {
                showFieldState(locInput, locError, 'L’adresse / localisation est obligatoire.');
                return 'La localisation de l’infrastructure est obligatoire.';
            }
            if (val.length < 3) {
                showFieldState(locInput, locError, 'La localisation doit comporter au moins 3 caractères.');
                return 'La localisation doit comporter au moins 3 caractères.';
            }
            clearFieldState(locInput, locError);
            return null;
        }

        function showFieldState(input, errorEl, message) {
            if (!input) return;
            input.classList.remove('border-cyan-400/20');
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
        if (typeSelect) typeSelect.addEventListener('change', validateType);
        if (zoneSelect) zoneSelect.addEventListener('change', validateZone);
        if (locInput) locInput.addEventListener('blur', validateLoc);

        // Validation globale à la soumission
        if (form) {
            form.addEventListener('submit', function (e) {
                const errors = [];
                const errNom = validateNom();
                if (errNom) errors.push(errNom);

                const errType = validateType();
                if (errType) errors.push(errType);

                const errZone = validateZone();
                if (errZone) errors.push(errZone);

                const errLoc = validateLoc();
                if (errLoc) errors.push(errLoc);

                const errDateInstall = validateDateInstall();
                if (errDateInstall) errors.push(errDateInstall);

                const errMaint = syncMaintenanceDates();
                if (errMaint) errors.push(errMaint);

                const errCap = validateCapacite();
                if (errCap) errors.push(errCap);

                const errDuree = validateDuree();
                if (errDuree) errors.push(errDuree);

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

                    if (errNom) nomInput.focus();
                    else if (errType) typeSelect.focus();
                    else if (errZone) zoneSelect.focus();
                    else if (errLoc) locInput.focus();
                    else if (errDateInstall) dateInstall.focus();
                    return false;
                } else {
                    errorSummary.classList.add('hidden');
                    const btn = document.getElementById('btn-submit-infra');
                    if (btn) {
                        btn.disabled = true;
                        btn.innerHTML = `<svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg> Enregistrement...`;
                    }
                }
            });
        }

        // Mini sélecteur Leaflet
        const mapElement = document.getElementById('infra-map-form');
        if (mapElement && typeof L !== 'undefined') {
            const initLat = parseFloat(latInput.value) || 36.8065;
            const initLng = parseFloat(lngInput.value) || 10.1815;
            const hasInitial = !isNaN(parseFloat(latInput.value)) && !isNaN(parseFloat(lngInput.value));

            const map = L.map('infra-map-form', {
                center: [initLat, initLng],
                zoom: hasInitial ? 13 : 7,
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
                    map.setView([lat, lng], 13);
                }
            };

            latInput.addEventListener('change', updateMapFromInputs);
            lngInput.addEventListener('change', updateMapFromInputs);

            const btnCenter = document.getElementById('btn-center-tunisie');
            if (btnCenter) {
                btnCenter.addEventListener('click', function () {
                    map.setView([34.0, 9.5], 6);
                });
            }
        }
    });
</script>
