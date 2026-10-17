@extends('layouts.technician')

@section('title', 'Mes Zones d\'Intervention — Technicien')

@section('technician-content')
<div class="max-w-6xl mx-auto space-y-6">

    {{-- En-tête mobile-first --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-cyan-500/10 border border-cyan-400/20 text-cyan-400 text-xs font-semibold mb-2">
                <i data-lucide="map-pin" class="w-3.5 h-3.5"></i>
                Secteurs d'Intervention · {{ $technicien->nom }}
            </div>
            <h1 class="text-2xl sm:text-3xl font-display font-bold text-white tracking-tight">Zones d'Intervention</h1>
            <p class="text-cyan-100/60 text-sm mt-0.5">Secteurs géographiques couverts par vos tournées et opérations de maintenance.</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('technician.infrastructures.index') }}"
               class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-semibold bg-cyan-600 hover:bg-cyan-500 text-white shadow-md shadow-cyan-900/30 transition-all">
                <i data-lucide="activity" class="w-4 h-4"></i>
                <span>Mes Infrastructures</span>
            </a>
            <a href="{{ route('technician.tournee') }}"
               class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-semibold bg-slate-800 hover:bg-slate-700 text-white border border-slate-700 transition-all">
                <i data-lucide="map" class="w-4 h-4 text-cyan-400"></i>
                <span>Tournée du jour</span>
            </a>
        </div>
    </div>

    {{-- Barre de recherche --}}
    <div class="glass rounded-2xl p-4 border border-white/10">
        <form method="GET" action="{{ route('technician.zones.index') }}" class="flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <i data-lucide="search" class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-cyan-100/40"></i>
                <input type="text"
                       name="q"
                       value="{{ $search }}"
                       placeholder="Rechercher par nom de zone, code ou type..."
                       class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-900/60 border border-white/10 text-white placeholder-cyan-100/30 text-xs focus:outline-none focus:border-cyan-400 transition-all">
            </div>

            <button type="submit"
                    class="px-5 py-2.5 rounded-xl bg-cyan-600 hover:bg-cyan-500 text-white text-xs font-semibold transition-all shrink-0">
                Filtrer
            </button>
            @if($search)
            <a href="{{ route('technician.zones.index') }}"
               class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold transition-all shrink-0 text-center">
                Réinitialiser
            </a>
            @endif
        </form>
    </div>

    {{-- Grille des Zones --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($zones as $zone)
        @php
            $isActive = ($zone->statut instanceof \App\Enums\ZoneStatut ? $zone->statut->value : $zone->statut) === 'active';
            $statutLabel = $zone->statut instanceof \App\Enums\ZoneStatut ? $zone->statut->label() : ucfirst((string)$zone->statut);
            $zoneCode = $zone->code ?? ('ZN-' . str_pad($zone->id, 3, '0', STR_PAD_LEFT));
        @endphp
        <div class="glass rounded-2xl p-5 border border-white/10 hover:border-cyan-400/40 transition-all flex flex-col justify-between">
            <div>
                <div class="flex items-start justify-between gap-3 mb-3">
                    <div>
                        <span class="text-[10px] font-mono px-2 py-0.5 rounded-md bg-cyan-500/15 text-cyan-300 border border-cyan-400/20">
                            {{ $zoneCode }}
                        </span>
                        <h2 class="text-lg font-bold font-display text-white mt-1">{{ $zone->nom }}</h2>
                    </div>
                    <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold {{ $isActive ? 'bg-emerald-500/15 text-emerald-300 border border-emerald-500/30' : 'bg-slate-800 text-slate-300 border border-white/10' }}">
                        {{ $statutLabel }}
                    </span>
                </div>

                <p class="text-xs text-cyan-100/60 line-clamp-2 mb-4">
                    {{ $zone->description ?? 'Zone de distribution d\'eau potable sous surveillance opérationnelle.' }}
                </p>

                <div class="space-y-2 text-xs border-t border-white/5 pt-3">
                    @if($zone->localisation)
                    <div class="flex items-center justify-between text-cyan-100/70">
                        <span class="flex items-center gap-1.5">
                            <i data-lucide="map-pin" class="w-3.5 h-3.5 text-cyan-400"></i>
                            Localisation
                        </span>
                        <span class="font-medium text-white truncate max-w-[150px]">{{ $zone->localisation }}</span>
                    </div>
                    @endif

                    <div class="flex items-center justify-between text-cyan-100/70">
                        <span class="flex items-center gap-1.5">
                            <i data-lucide="activity" class="w-3.5 h-3.5 text-cyan-400"></i>
                            Infrastructures assignées
                        </span>
                        <span class="font-semibold text-cyan-300">{{ $zone->infrastructures_count }} ouvrage(s)</span>
                    </div>

                    @if($zone->responsable)
                    <div class="flex items-center justify-between text-cyan-100/70">
                        <span class="flex items-center gap-1.5">
                            <i data-lucide="user" class="w-3.5 h-3.5 text-cyan-400"></i>
                            Responsable
                        </span>
                        <span class="font-medium text-white truncate max-w-[150px]">{{ $zone->responsable->name }}</span>
                    </div>
                    @endif
                </div>
            </div>

            <div class="mt-5 pt-3 border-t border-white/5 flex items-center justify-between">
                <a href="{{ route('technician.infrastructures.index', ['zone_id' => $zone->id]) }}"
                   class="text-[11px] text-cyan-400 hover:text-cyan-300 font-semibold inline-flex items-center gap-1">
                    <span>Voir les ouvrages</span>
                    <i data-lucide="chevron-right" class="w-3 h-3"></i>
                </a>
                <a href="{{ route('technician.zones.show', $zone) }}"
                   class="px-3 py-1.5 rounded-lg bg-cyan-600/30 hover:bg-cyan-600 border border-cyan-400/30 text-white text-[11px] font-semibold transition-all">
                    Fiche Zone
                </a>
            </div>
        </div>
        @empty
        <div class="col-span-full glass rounded-2xl p-12 text-center border border-white/10">
            <div class="w-12 h-12 rounded-2xl bg-cyan-500/10 flex items-center justify-center mx-auto text-cyan-400 mb-3">
                <i data-lucide="map" class="w-6 h-6"></i>
            </div>
            <h3 class="text-base font-bold text-white">Aucune zone trouvée</h3>
            <p class="text-xs text-cyan-100/60 max-w-sm mx-auto mt-1">
                Aucune zone ne correspond à vos critères de recherche.
            </p>
        </div>
        @endforelse
    </div>

    @if($zones->hasPages())
    <div class="mt-6">
        {{ $zones->links() }}
    </div>
    @endif

</div>
@endsection
