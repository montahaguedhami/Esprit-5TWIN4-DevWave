@extends('layouts.manager')

@section('title', $zone->nom.' — AquaSecure')

@section('manager-content')
@php
    $inventoryRoutePrefix = 'manager';
@endphp
<div class="mx-auto max-w-7xl space-y-6">
    <section class="rounded-2xl border border-amber-400/20 bg-slate-900/40 p-5">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="font-bold">Risque moyen</h2>
            <span>{{ $riskAverage !== null ? $riskAverage.' %' : 'Aucune estimation disponible' }}</span>
        </div>
        <h3 class="mt-4 text-sm font-semibold">Top 5 des infrastructures à risque</h3>
        <ul class="mt-2 space-y-2 text-sm">
            @forelse($topRiskInfrastructures as $infrastructure)
                <li class="flex justify-between"><span>{{ $infrastructure->nom }}</span><span>{{ $infrastructure->latestRiskPrediction->level }} · {{ $infrastructure->latestRiskPrediction->probability }} %</span></li>
            @empty
                <li class="text-slate-400">Aucune prédiction disponible.</li>
            @endforelse
        </ul>
    </section>
    {{-- En-tête de la zone --}}
    <div class="flex flex-wrap items-start justify-between gap-4 rounded-2xl border border-cyan-400/15 bg-slate-900/40 p-5 shadow-sm">
        <div class="space-y-1">
            <a href="{{ route($inventoryRoutePrefix . '.zones.index') }}" class="inline-flex items-center text-sm font-medium text-cyan-300 hover:text-cyan-200 transition">
                ← Toutes les zones
            </a>
            <div class="flex flex-wrap items-center gap-3 pt-1">
                <h1 class="text-2xl font-bold text-white">{{ $zone->nom }}</h1>
                <x-zone.statut-badge :statut="$zone->statut" />
            </div>
            <p class="text-sm text-cyan-100/60">{{ $zone->localisation }} · Réseau d’alimentation en eau potable</p>
        </div>
        <div class="flex items-center gap-2">
            @if(!request()->routeIs('admin.*') && $zone->latitude && $zone->longitude)
                <a href="{{ route('manager.map', ['zone' => $zone->id]) }}" class="inline-flex items-center gap-1.5 rounded-lg border border-cyan-400/30 bg-cyan-500/10 px-3.5 py-2 text-sm font-medium text-cyan-200 hover:bg-cyan-500/20 transition">
                    <svg class="w-4 h-4 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    Voir sur la carte
                </a>
            @endif
            <a href="{{ route($inventoryRoutePrefix . '.zones.export.pdf', $zone) }}" class="inline-flex items-center gap-1.5 rounded-lg border border-cyan-400/30 bg-cyan-500/10 px-3.5 py-2 text-sm font-medium text-cyan-200 hover:bg-cyan-500/20 transition">
                <svg class="w-4 h-4 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Fiche PDF
            </a>
            <a href="{{ route($inventoryRoutePrefix . '.zones.edit', $zone) }}" class="rounded-lg border border-cyan-400/20 px-3.5 py-2 text-sm font-medium text-cyan-100/80 hover:bg-white/5 transition">
                Modifier la zone
            </a>
            <form id="delete-zone-form-show" method="POST" action="{{ route($inventoryRoutePrefix . '.zones.destroy', $zone) }}" class="inline">
                @csrf
                @method('DELETE')
                <button
                    type="button"
                    onclick="confirmDeleteZoneShow()"
                    class="rounded-lg border border-rose-400/30 px-3.5 py-2 text-sm font-medium text-rose-300 hover:bg-rose-500/10 transition"
                >
                    Supprimer
                </button>
            </form>
            <a href="{{ route($inventoryRoutePrefix . '.infrastructures.create', ['zone_id' => $zone->id]) }}" class="rounded-lg bg-cyan-600 px-4 py-2 text-sm font-semibold text-white hover:bg-cyan-500 shadow-sm transition">
                + Ajouter équipement
            </a>
        </div>
    </div>

    @include('manager.shared.flash')

    {{-- Cartes KPI de performance & santé --}}
    <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-5">
        <div class="rounded-2xl border border-cyan-400/15 bg-slate-900/60 p-4 shadow-sm">
            <p class="text-xs font-medium text-cyan-100/60">Total équipements</p>
            <p class="mt-2 text-2xl font-bold text-white">{{ $stats['total'] }}</p>
            <p class="mt-1 text-[11px] text-cyan-200/50">Infrastructures actives</p>
        </div>
        <div class="rounded-2xl border border-cyan-400/15 bg-slate-900/60 p-4 shadow-sm">
            <p class="text-xs font-medium text-cyan-100/60">Taux opérationnel</p>
            <p class="mt-2 text-2xl font-bold {{ $stats['pourcentage_en_service'] >= 80 ? 'text-emerald-400' : ($stats['pourcentage_en_service'] >= 50 ? 'text-amber-400' : 'text-rose-400') }}">
                {{ $stats['pourcentage_en_service'] }}%
            </p>
            <p class="mt-1 text-[11px] text-cyan-200/50">En service nominal</p>
        </div>
        <div class="rounded-2xl border border-cyan-400/15 bg-slate-900/60 p-4 shadow-sm">
            <p class="text-xs font-medium text-cyan-100/60">Retard maintenance</p>
            <p class="mt-2 text-2xl font-bold {{ $stats['nombre_en_retard_maintenance'] > 0 ? 'text-rose-400' : 'text-emerald-400' }}">
                {{ $stats['nombre_en_retard_maintenance'] }}
            </p>
            <p class="mt-1 text-[11px] {{ $stats['nombre_en_retard_maintenance'] > 0 ? 'text-rose-300/80 font-medium' : 'text-cyan-200/50' }}">
                {{ $stats['nombre_en_retard_maintenance'] > 0 ? 'Action requise' : 'À jour' }}
            </p>
        </div>
        <div class="rounded-2xl border border-cyan-400/15 bg-slate-900/60 p-4 shadow-sm">
            <p class="text-xs font-medium text-cyan-100/60">Santé moyenne</p>
            <p class="mt-2 text-2xl font-bold {{ $stats['score_sante_moyen'] >= 70 ? 'text-emerald-400' : ($stats['score_sante_moyen'] >= 40 ? 'text-amber-400' : 'text-rose-400') }}">
                {{ $stats['score_sante_moyen'] }}/100
            </p>
            <p class="mt-1 text-[11px] text-cyan-200/50">Calcul multi-critères</p>
        </div>
        <div class="col-span-2 rounded-2xl border border-cyan-400/15 bg-slate-900/60 p-4 shadow-sm sm:col-span-1">
            <p class="text-xs font-medium text-cyan-100/60">Criticité moyenne</p>
            <p class="mt-2 text-2xl font-bold {{ $stats['criticite_moyenne'] === 'Critique' ? 'text-rose-400' : ($stats['criticite_moyenne'] === 'Moyenne' ? 'text-amber-400' : 'text-emerald-400') }}">
                {{ $stats['criticite_moyenne'] }}
            </p>
            <p class="mt-1 text-[11px] text-cyan-200/50">Sensibilité réseau</p>
        </div>
    </div>

    {{-- Informations générales de la zone & Localisation --}}
    <div class="grid gap-6 lg:grid-cols-3">
        <div class="rounded-2xl border border-cyan-400/15 bg-slate-900/60 p-5 shadow-sm space-y-4 lg:col-span-1">
            <h2 class="text-base font-semibold text-white">Fiche de la zone</h2>
            <dl class="space-y-3 text-sm">
                <div>
                    <dt class="text-xs font-medium text-cyan-100/60">Responsable d'exploitation</dt>
                    <dd class="mt-0.5 font-medium text-white">
                        @if($zone->responsable)
                            {{ $zone->responsable->name }}
                            <span class="block text-xs font-normal text-cyan-200/60">{{ $zone->responsable->email }}</span>
                        @else
                            <span class="text-cyan-100/40">Non assigné</span>
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-cyan-100/60">Superficie couverte</dt>
                    <dd class="mt-0.5 font-medium text-white">
                        {{ $zone->superficie_km2 ? number_format((float) $zone->superficie_km2, 2, ',', ' ').' km²' : 'Non renseignée' }}
                    </dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-cyan-100/60">Population desservie</dt>
                    <dd class="mt-0.5 font-medium text-white">
                        {{ $zone->population_desservie ? number_format((int) $zone->population_desservie, 0, ',', ' ').' habitants' : 'Non renseignée' }}
                    </dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-cyan-100/60">Coordonnées centrales</dt>
                    <dd class="mt-0.5 font-mono text-xs text-cyan-300">
                        @if($zone->latitude && $zone->longitude)
                            {{ $zone->latitude }}, {{ $zone->longitude }}
                        @else
                            <span class="font-sans text-cyan-100/40">Non géoréférencée</span>
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-cyan-100/60">Description & périmètre</dt>
                    <dd class="mt-0.5 text-xs text-cyan-100/80 leading-relaxed whitespace-pre-line">
                        {{ $zone->description ?: 'Aucune description spécifique fournie.' }}
                    </dd>
                </div>
            </dl>
        </div>

        {{-- Mini-carte interactive Leaflet avec la zone et ses infrastructures --}}
        <div class="rounded-2xl border border-cyan-400/15 bg-slate-900/60 p-5 shadow-sm space-y-3 lg:col-span-2">
            <div class="flex items-center justify-between">
                <h2 class="text-base font-semibold text-white">Cartographie des équipements</h2>
                <span class="text-xs text-cyan-100/60">Positions GPS des infrastructures de la zone</span>
            </div>
            <div id="zone-leaflet-map" class="h-64 sm:h-72 w-full rounded-xl border border-cyan-400/20 bg-slate-950/60 overflow-hidden shadow-inner"></div>
        </div>
    </div>

    {{-- Graphiques analytiques (Chart.js) --}}
    @if($stats['total'] > 0)
        <div class="grid gap-6 md:grid-cols-2">
            {{-- Répartition par état --}}
            <div class="rounded-2xl border border-cyan-400/15 bg-slate-900/60 p-5 shadow-sm space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-white">Répartition opérationnelle par état</h2>
                    <span class="text-xs text-cyan-100/60">Proportion des équipements</span>
                </div>
                <div class="relative h-56 flex items-center justify-center">
                    <canvas id="chart-etat"></canvas>
                </div>
            </div>

            {{-- Répartition par type --}}
            <div class="rounded-2xl border border-cyan-400/15 bg-slate-900/60 p-5 shadow-sm space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-white">Typologie des infrastructures</h2>
                    <span class="text-xs text-cyan-100/60">Nombre par catégorie</span>
                </div>
                <div class="relative h-56 flex items-center justify-center">
                    <canvas id="chart-type"></canvas>
                </div>
            </div>
        </div>
    @endif

    {{-- Liste filtrable des infrastructures de la zone --}}
    <section class="space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-bold text-white">Infrastructures rattachées</h2>
                <p class="text-xs text-cyan-100/60">Équipements situés dans le périmètre de {{ $zone->nom }}</p>
            </div>
            <form method="GET" action="{{ route($inventoryRoutePrefix . '.zones.show', $zone) }}" class="flex items-center gap-2">
                <label for="etat" class="text-xs font-medium text-cyan-100/70">Filtrer par état :</label>
                <select id="etat" name="etat" onchange="this.form.submit()" class="rounded-lg border border-cyan-400/20 bg-slate-950/60 px-3 py-1.5 text-xs text-white outline-none transition focus:border-cyan-400/50">
                    <option value="">Tous les états</option>
                    @foreach($etats as $etatOption)
                        <option value="{{ $etatOption->value }}" @selected($selectedEtat === $etatOption->value)>{{ $etatOption->label() }}</option>
                    @endforeach
                </select>
                @if($selectedEtat !== '')
                    <a href="{{ route($inventoryRoutePrefix . '.zones.show', $zone) }}" class="rounded-lg border border-cyan-400/20 px-2.5 py-1.5 text-xs font-medium text-cyan-200 hover:bg-white/5 transition">
                        Réinitialiser
                    </a>
                @endif
            </form>
        </div>

        <div class="overflow-x-auto rounded-2xl border border-cyan-400/15 bg-slate-900/60 shadow-sm">
            <table class="w-full text-left text-sm">
                <thead class="bg-white/5 text-xs font-medium text-cyan-100/70">
                    <tr>
                        <th class="px-5 py-3">Nom & Référence</th>
                        <th class="px-5 py-3">Type</th>
                        <th class="px-5 py-3">État</th>
                        <th class="px-5 py-3">Criticité</th>
                        <th class="px-5 py-3">Santé</th>
                        <th class="px-5 py-3">Installation</th>
                        <th class="px-5 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @forelse($zone->infrastructures as $infrastructure)
                    <tr class="text-cyan-100/70 hover:bg-white/5 transition">
                        <td class="px-5 py-3 font-semibold text-white">
                            <a href="{{ route($inventoryRoutePrefix . '.infrastructures.show', $infrastructure) }}" class="hover:text-cyan-300 transition">
                                {{ $infrastructure->nom }}
                            </a>
                            @if($infrastructure->reference)
                                <span class="block font-mono text-xs text-cyan-200/50 font-normal">{{ $infrastructure->reference }}</span>
                            @endif
                        </td>
                        <td class="px-5 py-3">{{ $infrastructure->type->label() }}</td>
                        <td class="px-5 py-3"><x-infrastructure.etat-badge :etat="$infrastructure->etat" /></td>
                        <td class="px-5 py-3"><x-infrastructure.criticite-badge :criticite="$infrastructure->niveau_criticite" /></td>
                        <td class="px-5 py-3"><x-infrastructure.sante-badge :sante="$infrastructure->sante" /></td>
                        <td class="px-5 py-3 text-xs">{{ $infrastructure->date_installation->format('d/m/Y') }}</td>
                        <td class="px-5 py-3 text-right">
                            <a href="{{ route($inventoryRoutePrefix . '.infrastructures.show', $infrastructure) }}" class="text-xs font-medium text-cyan-300 hover:text-cyan-200 transition">
                                Consulter →
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-5 py-8 text-center text-cyan-100/60">
                            @if($selectedEtat !== '')
                                Aucune infrastructure à l’état « {{ $selectedEtat }} » dans cette zone.
                            @else
                                Aucune infrastructure enregistrée dans cette zone.
                            @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    @if(!request()->routeIs('admin.*'))
    {{-- Sections de suivi réservées au gestionnaire --}}
    {{-- Section : Incidents ouverts sur la zone --}}
    <section class="space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-lg font-bold text-white">Incidents en cours sur la zone</h2>
                <p class="text-xs text-cyan-100/60">Signalements citoyens ou opérationnels nécessitant une prise en charge</p>
            </div>
            @if(isset($zone->incidents) && $zone->incidents->isNotEmpty())
                <span class="rounded-full bg-rose-500/10 px-2.5 py-1 text-xs font-semibold text-rose-300 ring-1 ring-rose-400/20">
                    {{ $zone->incidents->count() }} {{ $zone->incidents->count() > 1 ? 'incidents actifs' : 'incident actif' }}
                </span>
            @endif
        </div>

        <div class="overflow-x-auto rounded-2xl border border-cyan-400/15 bg-slate-900/60 shadow-sm">
            @if(!isset($zone->incidents) || $zone->incidents->isEmpty())
                <div class="p-6 text-center text-sm text-cyan-100/50">
                    Aucun incident actif signalé sur cette zone.
                </div>
            @else
                <table class="w-full text-left text-sm">
                    <thead class="bg-white/5 text-xs font-medium text-cyan-100/70">
                        <tr>
                            <th class="px-5 py-3">Date</th>
                            <th class="px-5 py-3">Titre & Lieu</th>
                            <th class="px-5 py-3">Type</th>
                            <th class="px-5 py-3">Gravité</th>
                            <th class="px-5 py-3">Statut</th>
                            <th class="px-5 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5 text-xs">
                        @foreach($zone->incidents as $incident)
                            <tr class="hover:bg-white/5 transition text-cyan-100/80">
                                <td class="px-5 py-3 text-white font-medium whitespace-nowrap">{{ $incident->date_signalement?->format('d/m/Y H:i') ?? '—' }}</td>
                                <td class="px-5 py-3">
                                    <span class="font-semibold text-white">{{ $incident->titre }}</span>
                                    <span class="block text-[11px] text-cyan-200/50">{{ $incident->localisation }}</span>
                                </td>
                                <td class="px-5 py-3 capitalize">{{ $incident->type }}</td>
                                <td class="px-5 py-3">
                                    <span class="rounded px-2 py-0.5 font-semibold uppercase text-[10px] {{ $incident->gravite === 'critique' || $incident->gravite === 'elevee' ? 'bg-rose-500/20 text-rose-300' : 'bg-amber-500/20 text-amber-300' }}">
                                        {{ $incident->gravite }}
                                    </span>
                                </td>
                                <td class="px-5 py-3">
                                    <span class="rounded-full bg-cyan-500/15 px-2 py-0.5 text-[11px] font-medium text-cyan-200">
                                        {{ $incident->statut }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-right whitespace-nowrap">
                                    <a href="{{ route('manager.incidents.show', $incident) }}" class="font-medium text-cyan-300 hover:text-cyan-200">
                                        Consulter →
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </section>

    {{-- Section : Qualité de l'eau & Points de mesure --}}
    <section class="space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-lg font-bold text-white">Qualité de l'eau & Points de prélèvement</h2>
                <p class="text-xs text-cyan-100/60">Dernières analyses physico-chimiques sur les points de mesure de la zone</p>
            </div>
            @if(isset($zone->pointsMesure) && $zone->pointsMesure->isNotEmpty())
                <a href="{{ route('manager.quality') }}" class="text-xs font-medium text-cyan-300 hover:text-cyan-200">
                    Ouvrir le module Qualité →
                </a>
            @endif
        </div>

        <div class="overflow-x-auto rounded-2xl border border-cyan-400/15 bg-slate-900/60 shadow-sm">
            @if(!isset($zone->pointsMesure) || $zone->pointsMesure->isEmpty())
                <div class="p-6 text-center text-sm text-cyan-100/50">
                    Aucun point de mesure rattaché à cette zone.
                </div>
            @else
                <table class="w-full text-left text-sm">
                    <thead class="bg-white/5 text-xs font-medium text-cyan-100/70">
                        <tr>
                            <th class="px-5 py-3">Point de prélèvement</th>
                            <th class="px-5 py-3">Type & Statut</th>
                            <th class="px-5 py-3">Dernière mesure</th>
                            <th class="px-5 py-3">Paramètres clés</th>
                            <th class="px-5 py-3">Conformité</th>
                            <th class="px-5 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5 text-xs">
                        @foreach($zone->pointsMesure as $point)
                            <tr class="hover:bg-white/5 transition text-cyan-100/80">
                                <td class="px-5 py-3">
                                    <span class="font-semibold text-white">{{ $point->nom }}</span>
                                    <span class="block font-mono text-[11px] text-cyan-200/50">{{ $point->code }}</span>
                                </td>
                                <td class="px-5 py-3">
                                    <span class="capitalize">{{ $point->type }}</span>
                                    <span class="ml-1 text-[11px] {{ $point->statut === 'actif' ? 'text-emerald-400' : 'text-slate-400' }}">({{ $point->statut }})</span>
                                </td>
                                <td class="px-5 py-3 whitespace-nowrap">
                                    @if($point->derniereMesure)
                                        <span class="text-white">{{ \Carbon\Carbon::parse($point->derniereMesure->date_mesure)->format('d/m/Y H:i') }}</span>
                                    @else
                                        <span class="text-cyan-100/40">Aucun relevé</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3">
                                    @if($point->derniereMesure)
                                        <div class="flex flex-wrap gap-2 text-[11px]">
                                            <span class="rounded bg-white/5 px-1.5 py-0.5">pH: <strong class="text-white">{{ $point->derniereMesure->ph }}</strong></span>
                                            <span class="rounded bg-white/5 px-1.5 py-0.5">Turb.: <strong class="text-white">{{ $point->derniereMesure->turbidite }} NTU</strong></span>
                                            <span class="rounded bg-white/5 px-1.5 py-0.5">Chlore: <strong class="text-white">{{ $point->derniereMesure->chlore_residuel }} mg/l</strong></span>
                                        </div>
                                    @else
                                        <span class="text-cyan-100/40">—</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3">
                                    @if($point->assessment)
                                        @php
                                            $st = $point->assessment['status'] ?? 'Compliant';
                                            $score = $point->assessment['overall_compliance'] ?? 100;
                                        @endphp
                                        <div class="flex items-center gap-2">
                                            <span class="rounded-full px-2 py-0.5 font-semibold text-[10px] uppercase {{ $st === 'Compliant' ? 'bg-emerald-500/20 text-emerald-300' : ($st === 'Alert' ? 'bg-amber-500/20 text-amber-300' : 'bg-rose-500/20 text-rose-300') }}">
                                                {{ $st === 'Compliant' ? 'Conforme' : ($st === 'Alert' ? 'Vigilance' : 'Non-conforme') }}
                                            </span>
                                            <span class="font-mono text-xs {{ $score >= 80 ? 'text-emerald-400' : ($score >= 60 ? 'text-amber-400' : 'text-rose-400') }}">{{ $score }}%</span>
                                        </div>
                                    @else
                                        <span class="text-cyan-100/40">Non évalué</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-right whitespace-nowrap">
                                    @if($point->derniereMesure)
                                        <a href="{{ route('manager.quality.mesures.show', $point->derniereMesure) }}" class="font-medium text-cyan-300 hover:text-cyan-200">
                                            Détails relevé →
                                        </a>
                                    @else
                                        <a href="{{ route('manager.quality') }}" class="font-medium text-cyan-300 hover:text-cyan-200">
                                            Consulter →
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </section>

    {{-- Section : Projets & Investissements rattachés --}}
    <section class="space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-lg font-bold text-white">Projets & Chantiers d'infrastructure</h2>
                <p class="text-xs text-cyan-100/60">Travaux et programmes de modernisation concernant cette zone</p>
            </div>
            @if(isset($zone->projets) && $zone->projets->isNotEmpty())
                <a href="{{ route('manager.projets.index') }}" class="text-xs font-medium text-cyan-300 hover:text-cyan-200">
                    Tous les projets →
                </a>
            @endif
        </div>

        <div class="overflow-x-auto rounded-2xl border border-cyan-400/15 bg-slate-900/60 shadow-sm">
            @if(!isset($zone->projets) || $zone->projets->isEmpty())
                <div class="p-6 text-center text-sm text-cyan-100/50">
                    Aucun projet d'investissement n'est actuellement rattaché à cette zone.
                </div>
            @else
                <table class="w-full text-left text-sm">
                    <thead class="bg-white/5 text-xs font-medium text-cyan-100/70">
                        <tr>
                            <th class="px-5 py-3">Projet</th>
                            <th class="px-5 py-3">Statut</th>
                            <th class="px-5 py-3">Dates</th>
                            <th class="px-5 py-3">Progression</th>
                            <th class="px-5 py-3">Budget & Financement</th>
                            <th class="px-5 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5 text-xs">
                        @foreach($zone->projets as $projet)
                            <tr class="hover:bg-white/5 transition text-cyan-100/80">
                                <td class="px-5 py-3">
                                    <a href="{{ route('manager.projets.show', $projet) }}" class="font-semibold text-white hover:text-cyan-300 transition">
                                        {{ $projet->nom }}
                                    </a>
                                    @if($projet->adresse)
                                        <span class="block text-[11px] text-cyan-200/50">{{ $projet->adresse }}</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3">
                                    <span class="rounded-full bg-cyan-500/15 px-2.5 py-0.5 text-[11px] font-medium text-cyan-200 capitalize">
                                        {{ $projet->statut }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 whitespace-nowrap">
                                    <span class="text-white">{{ $projet->date_debut?->format('d/m/Y') ?? '—' }}</span>
                                    <span class="text-cyan-100/50"> → {{ $projet->date_fin?->format('d/m/Y') ?? '—' }}</span>
                                </td>
                                <td class="px-5 py-3">
                                    <div class="flex items-center gap-2">
                                        <div class="h-1.5 w-24 rounded-full bg-white/10 overflow-hidden">
                                            <div class="h-full bg-cyan-400 rounded-full" style="width: {{ min(100, max(0, (int) $projet->progression)) }}%;"></div>
                                        </div>
                                        <span class="font-mono text-xs text-white">{{ $projet->progression ?? 0 }}%</span>
                                    </div>
                                </td>
                                <td class="px-5 py-3">
                                    <span class="font-semibold text-white">{{ number_format((float) $projet->budget, 0, ',', ' ') }} TND</span>
                                    <span class="block text-[11px] text-emerald-400">Financé : {{ number_format((float) $projet->total_finance, 0, ',', ' ') }} TND</span>
                                </td>
                                <td class="px-5 py-3 text-right whitespace-nowrap">
                                    <a href="{{ route('manager.projets.show', $projet) }}" class="font-medium text-cyan-300 hover:text-cyan-200">
                                        Consulter →
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </section>
    @endif
</div>

{{-- Scripts Leaflet & Chart.js --}}
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. Initialisation de la carte Leaflet
    const mapEl = document.getElementById('zone-leaflet-map');
    if (mapEl && typeof L !== 'undefined') {
        const zoneLat = {{ (float) ($zone->latitude ?? 36.8065) }};
        const zoneLng = {{ (float) ($zone->longitude ?? 10.1815) }};
        const map = L.map('zone-leaflet-map').setView([zoneLat, zoneLng], 11);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap',
            maxZoom: 19
        }).addTo(map);

        const markers = [];

        // Marqueur central de la zone si défini
        @if($zone->latitude && $zone->longitude)
            const zoneIcon = L.divIcon({
                className: 'custom-zone-marker',
                html: '<div style="background-color:#06b6d4;width:16px;height:16px;border-radius:50%;border:3px solid white;box-shadow:0 0 10px rgba(6,182,212,0.8);"></div>',
                iconSize: [16, 16],
                iconAnchor: [8, 8]
            });
            const zoneMarker = L.marker([{{ (float) $zone->latitude }}, {{ (float) $zone->longitude }}], { icon: zoneIcon })
                .addTo(map)
                .bindPopup('<strong>Centre de la zone : {{ addslashes($zone->nom) }}</strong>');
            markers.push(zoneMarker);
        @endif

        // Marqueurs de chaque infrastructure
        @foreach($zone->infrastructures as $infra)
            @if($infra->latitude && $infra->longitude)
                (function() {
                    const color = "{{ $infra->etat->color() === 'green' ? '#10b981' : ($infra->etat->color() === 'amber' ? '#f59e0b' : '#ef4444') }}";
                    const infraIcon = L.divIcon({
                        className: 'infra-marker',
                        html: `<div style="background-color:${color};width:12px;height:12px;border-radius:50%;border:2px solid #ffffff;box-shadow:0 0 6px ${color};"></div>`,
                        iconSize: [12, 12],
                        iconAnchor: [6, 6]
                    });
                    const marker = L.marker([{{ (float) $infra->latitude }}, {{ (float) $infra->longitude }}], { icon: infraIcon })
                        .addTo(map)
                        .bindPopup(`
                            <div style="color:#0f172a;min-width:140px;">
                                <strong style="font-size:13px;">{{ addslashes($infra->nom) }}</strong><br>
                                <span style="font-size:11px;color:#64748b;">{{ addslashes($infra->type->label()) }}</span><br>
                                <span style="font-size:11px;font-weight:600;color:${color};">{{ addslashes($infra->etat->label()) }}</span><br>
                                <a href="{{ route($inventoryRoutePrefix . '.infrastructures.show', $infra) }}" style="display:inline-block;margin-top:4px;font-size:11px;color:#0284c7;font-weight:600;">Voir la fiche →</a>
                            </div>
                        `);
                    markers.push(marker);
                })();
            @endif
        @endforeach

        if (markers.length > 1) {
            const group = new L.featureGroup(markers);
            map.fitBounds(group.getBounds().pad(0.15));
        }
    }

    // 2. Initialisation des graphiques Chart.js si des équipements existent
    @if($stats['total'] > 0)
        // Configuration globale sombre pour Chart.js
        Chart.defaults.color = '#94a3b8';
        Chart.defaults.font.family = 'inherit';

        // Donut: Répartition par état
        const ctxEtat = document.getElementById('chart-etat');
        if (ctxEtat) {
            const etatLabels = {!! json_encode(array_keys($stats['repartition_etat'])) !!};
            const etatValues = {!! json_encode(array_values($stats['repartition_etat'])) !!};

            const etatColors = etatLabels.map(l => {
                if (l === 'En service') return '#10b981';
                if (l === 'En maintenance') return '#f59e0b';
                return '#ef4444';
            });

            new Chart(ctxEtat, {
                type: 'doughnut',
                data: {
                    labels: etatLabels,
                    datasets: [{
                        data: etatValues,
                        backgroundColor: etatColors,
                        borderWidth: 2,
                        borderColor: '#0f172a',
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom' }
                    },
                    cutout: '65%'
                }
            });
        }

        // Barres: Répartition par type
        const ctxType = document.getElementById('chart-type');
        if (ctxType) {
            const typeLabels = {!! json_encode(array_keys($stats['repartition_type'])) !!};
            const typeValues = {!! json_encode(array_values($stats['repartition_type'])) !!};

            new Chart(ctxType, {
                type: 'bar',
                data: {
                    labels: typeLabels,
                    datasets: [{
                        label: 'Équipements',
                        data: typeValues,
                        backgroundColor: '#06b6d4',
                        borderRadius: 6,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: { font: { size: 10 } }
                        },
                        y: {
                            beginAtZero: true,
                            ticks: { precision: 0 },
                            grid: { color: 'rgba(255, 255, 255, 0.05)' }
                        }
                    }
                }
            });
        }
    @endif
});

function confirmDeleteZoneShow() {
    const infraCount = {{ $zone->infrastructures()->count() }};
    let hint = 'Cette opération archivera la zone dans le système (suppression logique).';
    if (infraCount > 0) {
        hint = 'Attention : cette zone englobe actuellement ' + infraCount + ' équipement(s). Conformément aux règles de gestion, vous ne pourrez pas la supprimer tant que ces infrastructures sont rattachées.';
    }

    window.openAquaConfirm({
        variant: 'danger',
        title: 'Supprimer la zone géographique',
        message: 'Êtes-vous certain de vouloir supprimer cette zone territoriale du réseau ?',
        badge: '{{ addslashes($zone->nom) }} (' + infraCount + ' équipement' + (infraCount > 1 ? 's' : '') + ')',
        hint: hint,
        confirmText: 'Supprimer la zone',
        cancelText: 'Annuler',
        onConfirm: function () {
            document.getElementById('delete-zone-form-show').submit();
        }
    });
}
</script>
@endsection
