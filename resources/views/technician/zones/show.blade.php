@extends('layouts.manager')

@section('title', 'Zone ' . $zone->nom . ' — Technicien')

@section('manager-content')
<div class="max-w-6xl mx-auto space-y-6">

    @php
        $zoneCode = $zone->code ?? ('ZN-' . str_pad($zone->id, 3, '0', STR_PAD_LEFT));
        $statutLabel = $zone->statut instanceof \App\Enums\ZoneStatut ? $zone->statut->label() : ucfirst((string)$zone->statut);
        $isActive = ($zone->statut instanceof \App\Enums\ZoneStatut ? $zone->statut->value : $zone->statut) === 'active';
    @endphp

    {{-- Fil d'Ariane & En-tête --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-cyan-100/60 mb-2">
                <a href="{{ route('technician.zones.index') }}" class="hover:text-cyan-300">Zones</a>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                <span class="text-white">{{ $zone->nom }}</span>
            </div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl sm:text-3xl font-display font-bold text-white tracking-tight">{{ $zone->nom }}</h1>
                <span class="text-xs font-mono px-2.5 py-0.5 rounded-full bg-cyan-500/15 text-cyan-300 border border-cyan-400/20">
                    {{ $zoneCode }}
                </span>
            </div>
            <p class="text-cyan-100/60 text-sm mt-0.5">{{ $zone->description ?? 'Zone de desserte opérationnelle d\'eau potable.' }}</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('technician.infrastructures.index', ['zone_id' => $zone->id]) }}"
               class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-semibold bg-cyan-600 hover:bg-cyan-500 text-white shadow-md shadow-cyan-900/30 transition-all">
                <i data-lucide="activity" class="w-4 h-4"></i>
                <span>Ouvrages de la zone</span>
            </a>
            <a href="{{ route('technician.tournee') }}"
               class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-semibold bg-slate-800 hover:bg-slate-700 text-white border border-slate-700 transition-all">
                <i data-lucide="map" class="w-4 h-4 text-cyan-400"></i>
                <span>Tournée du jour</span>
            </a>
        </div>
    </div>

    {{-- Cartes d'informations terrain --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        {{-- Caractéristiques terrain --}}
        <div class="glass rounded-2xl p-5 border border-white/10 space-y-3">
            <h2 class="text-xs font-bold text-cyan-400 uppercase tracking-wider flex items-center gap-2">
                <i data-lucide="info" class="w-3.5 h-3.5"></i>
                Caractéristiques du Secteur
            </h2>
            <div class="space-y-2 text-xs">
                @if($zone->localisation)
                <div class="flex justify-between py-1 border-b border-white/5">
                    <span class="text-cyan-100/60">Localisation</span>
                    <span class="text-white font-semibold">{{ $zone->localisation }}</span>
                </div>
                @endif
                <div class="flex justify-between py-1 border-b border-white/5">
                    <span class="text-cyan-100/60">Population desservie</span>
                    <span class="text-white font-semibold">{{ number_format($zone->population_desservie ?? 0, 0, ',', ' ') }} hab.</span>
                </div>
                <div class="flex justify-between py-1 border-b border-white/5">
                    <span class="text-cyan-100/60">Superficie</span>
                    <span class="text-white font-semibold">{{ $zone->superficie_km2 ? $zone->superficie_km2 . ' km²' : 'Non renseignée' }}</span>
                </div>
                <div class="flex justify-between py-1">
                    <span class="text-cyan-100/60">Statut réseau</span>
                    <span class="font-semibold flex items-center gap-1.5 {{ $isActive ? 'text-emerald-300' : 'text-slate-300' }}">
                        <span class="w-2 h-2 rounded-full {{ $isActive ? 'bg-emerald-400' : 'bg-slate-400' }}"></span>
                        {{ $statutLabel }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Contact Responsable Terrain --}}
        <div class="glass rounded-2xl p-5 border border-white/10 space-y-3">
            <h2 class="text-xs font-bold text-cyan-400 uppercase tracking-wider flex items-center gap-2">
                <i data-lucide="phone" class="w-3.5 h-3.5"></i>
                Contact Responsable
            </h2>
            <div class="space-y-2 text-xs">
                <div>
                    <span class="text-cyan-100/60 block text-[11px]">Nom</span>
                    <span class="text-white font-semibold text-sm">{{ $zone->responsable?->name ?? 'Responsable d\'exploitation' }}</span>
                </div>
                @if($zone->responsable?->email)
                <div class="pt-1">
                    <span class="text-cyan-100/60 block text-[11px]">Email</span>
                    <a href="mailto:{{ $zone->responsable->email }}" class="text-cyan-300 hover:text-white truncate block mt-0.5">
                        {{ $zone->responsable->email }}
                    </a>
                </div>
                @endif
            </div>
        </div>

        {{-- Activité opérationnelle --}}
        <div class="glass rounded-2xl p-5 border border-white/10 space-y-3">
            <h2 class="text-xs font-bold text-cyan-400 uppercase tracking-wider flex items-center gap-2">
                <i data-lucide="wrench" class="w-3.5 h-3.5"></i>
                Mon Affectation
            </h2>
            <div class="space-y-3 text-xs">
                <div class="p-3 rounded-xl bg-cyan-500/10 border border-cyan-400/20 flex items-center justify-between">
                    <span class="text-cyan-200">Ouvrages sous ma charge</span>
                    <span class="text-lg font-bold text-white">{{ $infrastructures->count() }}</span>
                </div>
                <div class="p-3 rounded-xl bg-amber-500/10 border border-amber-400/20 flex items-center justify-between">
                    <span class="text-amber-200">Interventions planifiées</span>
                    <span class="text-lg font-bold text-white">{{ $interventions->count() }}</span>
                </div>
            </div>
        </div>
    </div>

    <section class="glass rounded-2xl border border-white/10 overflow-hidden">
        <div class="p-4 sm:p-5 border-b border-white/10 flex items-center justify-between gap-4">
            <div><h2 class="text-base font-bold text-white">Points de mesure et qualité de l’eau</h2><p class="text-xs text-cyan-100/60 mt-1">Les mesures saisies restent en attente jusqu’à validation du gestionnaire.</p></div>
            <a href="{{ route('technician.quality.create', $zone) }}" class="rounded-lg bg-cyan-600 px-3 py-2 text-xs font-semibold text-white">Nouvelle mesure</a>
        </div>
        @forelse($qualityPoints as $qualityPoint)
            <div class="p-4 border-b border-white/5 last:border-0 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div><div class="font-semibold text-white">{{ $qualityPoint->nom }} <span class="text-xs font-mono text-cyan-300">{{ $qualityPoint->code }}</span></div><div class="text-xs text-cyan-100/60">{{ $qualityPoint->adresse ?? $qualityPoint->zone }} · {{ $qualityPoint->type }}{{ $qualityPoint->infrastructure ? ' · '.$qualityPoint->infrastructure->nom : '' }}</div></div>
                <div class="text-xs text-cyan-100/70">@if($qualityPoint->derniereMesure)Dernière saisie : {{ $qualityPoint->derniereMesure->date_mesure?->format('d/m/Y H:i') }} · {{ $qualityPoint->derniereMesure->status }}{{ $qualityPoint->derniereMesure->is_verified ? ' · Validée' : ' · En attente de validation' }}@else Aucune mesure enregistrée @endif</div>
            </div>
            @if($qualityPoint->derniereMesure)
                <div class="grid grid-cols-2 gap-2 px-4 pb-3 text-[11px] text-cyan-100/75 sm:grid-cols-5">
                    <span>pH : {{ $qualityPoint->derniereMesure->ph ?? '—' }}</span>
                    <span>Turbidité : {{ $qualityPoint->derniereMesure->turbidite ?? '—' }} NTU</span>
                    <span>Chlore : {{ $qualityPoint->derniereMesure->chlore_residuel ?? '—' }} mg/L</span>
                    <span>Plomb : {{ $qualityPoint->derniereMesure->plomb ?? '—' }} ppb</span>
                    <span>Nitrates : {{ $qualityPoint->derniereMesure->nitrates ?? '—' }} mg/L</span>
                </div>
            @endif
            @if($qualityPoint->mesuresQualites->isNotEmpty())
                <div class="px-4 pb-3 text-[11px] text-cyan-100/60">Historique récent :
                    @foreach($qualityPoint->mesuresQualites as $historicalMeasurement)
                        <span class="ml-2">{{ $historicalMeasurement->date_mesure?->format('d/m/Y') }} · {{ $historicalMeasurement->status }}{{ $historicalMeasurement->is_verified ? '' : ' (en attente)' }}</span>
                    @endforeach
                </div>
            @endif
        @empty
            <p class="p-6 text-sm text-cyan-100/60">Aucun point de mesure actif n’est associé à cette zone.</p>
        @endforelse
    </section>

    {{-- Liste de mes infrastructures dans cette zone --}}
    <div class="glass rounded-2xl border border-white/10 overflow-hidden">
        <div class="p-4 sm:p-5 border-b border-white/10 flex items-center justify-between">
            <div>
                <h2 class="text-base font-bold font-display text-white">Mes Ouvrages dans cette Zone</h2>
                <p class="text-xs text-cyan-100/60 mt-0.5">Infrastructures reliées à vos interventions de maintenance.</p>
            </div>
            <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-cyan-500/15 text-cyan-300">
                {{ $infrastructures->count() }} ouvrage(s)
            </span>
        </div>

        @if($infrastructures->isEmpty())
        <div class="p-8 text-center text-xs text-cyan-100/60">
            Aucun ouvrage n'est actuellement assigné à vos interventions dans cette zone.
        </div>
        @else
        <div class="divide-y divide-white/5">
            @foreach($infrastructures as $infra)
            <div class="p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-3 hover:bg-white/[0.02] transition-colors">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-mono text-cyan-300">{{ $infra->code }}</span>
                        <h3 class="text-sm font-bold text-white">{{ $infra->nom }}</h3>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $infra->etat->badgeClass() }}">
                            {{ $infra->etat->label() }}
                        </span>
                    </div>
                    <div class="flex flex-wrap items-center gap-3 text-xs text-cyan-100/60">
                        <span><strong class="text-white/80">Type:</strong> {{ $infra->type->label() }}</span>
                        @if($infra->localisation)
                        <span><strong class="text-white/80">Localisation:</strong> {{ $infra->localisation }}</span>
                        @endif
                        @if($infra->date_derniere_maintenance)
                        <span><strong class="text-white/80">Dernière maint.:</strong> {{ $infra->date_derniere_maintenance->format('d/m/Y') }}</span>
                        @endif
                    </div>
                </div>

                <div class="flex items-center gap-2 self-end sm:self-center">
                    <a href="{{ route('technician.infrastructures.show', $infra) }}"
                       class="px-3 py-1.5 rounded-xl bg-cyan-600 hover:bg-cyan-500 text-white text-xs font-semibold shadow-sm transition-all flex items-center gap-1.5">
                        <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                        <span>Fiche Technique</span>
                    </a>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

</div>
@endsection
