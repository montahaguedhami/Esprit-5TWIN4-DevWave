@extends('layouts.manager')

@section('title', 'Points de prélèvement — AquaSecure')

@section('manager-content')
<div class="text-slate-100">
    <div class="max-w-6xl mx-auto space-y-6">
        {{-- Header --}}
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-white flex items-center gap-2">
                    <i data-lucide="map-pin" class="w-6 h-6 text-teal-400"></i>
                    Points de prélèvement
                </h1>
                <p class="text-sm text-slate-400 mt-1">Gérez les stations, puits et réservoirs de surveillance du réseau d'eau potable.</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('manager.quality') }}" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-sm border border-slate-700 flex items-center gap-2">
                    <i data-lucide="flask-conical" class="w-4 h-4 text-teal-400"></i> Qualité de l’eau
                </a>
                <a href="{{ route('manager.points-mesure.create') }}" class="px-4 py-2 rounded-xl bg-teal-500 hover:bg-teal-400 text-slate-950 font-bold text-sm flex items-center gap-2 shadow-lg shadow-teal-500/10">
                    <i data-lucide="plus" class="w-4 h-4"></i> Nouveau point
                </a>
            </div>
        </div>

        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="rounded-xl border border-teal-500/30 bg-teal-500/10 p-4 text-sm text-teal-200 flex items-center gap-3">
                <i data-lucide="check-circle" class="w-5 h-5 text-teal-400"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        {{-- Search & Filters Card --}}
        <div class="rounded-2xl border border-slate-800 bg-slate-900/90 p-4 backdrop-blur-sm">
            <form method="GET" action="{{ route('manager.points-mesure.index') }}" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-5 items-end">
                {{-- Search Keyword --}}
                <div class="lg:col-span-2">
                    <label for="search" class="block text-xs font-semibold text-slate-400 mb-1">Recherche (nom, code, zone...)</label>
                    <div class="relative">
                        <i data-lucide="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-500"></i>
                        <input id="search" 
                            type="text" 
                            name="search" 
                            value="{{ request('search') }}" 
                            placeholder="Ex: TUN-01 ou Belvédère" 
                            class="w-full rounded-xl border border-slate-700 bg-slate-950 pl-9 pr-3 py-2 text-sm text-white placeholder-slate-500 focus:border-teal-400 focus:outline-none"
                        >
                    </div>
                </div>

                {{-- Filter Statut --}}
                <div>
                    <label for="statut" class="block text-xs font-semibold text-slate-400 mb-1">Statut</label>
                    <select id="statut" name="statut" class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-white focus:border-teal-400 focus:outline-none">
                        <option value="">Tous les statuts</option>
                        <option value="actif" @selected(request('statut') === 'actif')>Actif</option>
                        <option value="inactif" @selected(request('statut') === 'inactif')>Inactif</option>
                    </select>
                </div>

                {{-- Filter Type --}}
                <div>
                    <label for="type" class="block text-xs font-semibold text-slate-400 mb-1">Type d'ouvrage</label>
                    <select id="type" name="type" class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-white focus:border-teal-400 focus:outline-none">
                        <option value="">Tous les types</option>
                        @foreach(['Station', 'Puits', 'Réservoir', 'Réseau', 'Autre'] as $t)
                            <option value="{{ $t }}" @selected(request('type') === $t)>{{ $t }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Buttons --}}
                <div class="flex items-center gap-2">
                    <button type="submit" class="flex-1 rounded-xl bg-teal-500/20 hover:bg-teal-500/30 text-teal-300 border border-teal-500/40 px-3 py-2 text-sm font-semibold flex items-center justify-center gap-2">
                        <i data-lucide="filter" class="w-4 h-4"></i> Filtrer
                    </button>
                    @if(request()->hasAny(['search', 'statut', 'type', 'sort']))
                        <a href="{{ route('manager.points-mesure.index') }}" class="rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white px-3 py-2 text-sm" title="Réinitialiser">
                            <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto rounded-2xl border border-slate-800 bg-slate-900 shadow-xl">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-950 text-xs uppercase text-slate-400 border-b border-slate-800">
                    <tr>
                        <th class="p-4">Code</th>
                        <th class="p-4">Point / Zone</th>
                        <th class="p-4">Type</th>
                        <th class="p-4">Statut</th>
                        <th class="p-4">Coordonnées</th>
                        <th class="p-4">Dernière mesure</th>
                        <th class="p-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @forelse($points as $point)
                        <tr class="hover:bg-slate-800/40 transition-colors">
                            <td class="p-4 font-mono text-cyan-300 font-semibold">{{ $point->code }}</td>
                            <td class="p-4">
                                <a class="font-semibold text-white hover:text-teal-300 hover:underline transition-colors" href="{{ route('manager.points-mesure.show', $point) }}">
                                    {{ $point->nom }}
                                </a>
                                <div class="text-xs text-slate-400">{{ $point->zone ?: 'Zone non définie' }}</div>
                                @if($point->infrastructure)
                                    <div class="mt-1 text-[11px] text-cyan-300">Infrastructure : {{ $point->infrastructure->nom }}</div>
                                @endif
                            </td>
                            <td class="p-4">
                                <span class="px-2 py-0.5 rounded text-xs bg-slate-800 border border-slate-700 text-slate-300 font-medium">
                                    {{ $point->type }}
                                </span>
                            </td>
                            <td class="p-4">
                                @if($point->statut === 'actif')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-500/15 text-emerald-300 border border-emerald-500/30">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> Actif
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-700/50 text-slate-400 border border-slate-700">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-500"></span> Inactif
                                    </span>
                                @endif
                            </td>
                            <td class="p-4 font-mono text-xs text-slate-400">
                                @if($point->latitude && $point->longitude)
                                    {{ number_format((float) $point->latitude, 4) }}, {{ number_format((float) $point->longitude, 4) }}
                                @else
                                    <span class="text-slate-600 italic">Non géolocalisé</span>
                                @endif
                            </td>
                            <td class="p-4 text-slate-300">
                                @if($point->derniereMesure)
                                    <div class="text-xs font-mono text-slate-200">{{ $point->derniereMesure->date_mesure->format('d/m/Y H:i') }}</div>
                                    <div class="text-[11px] {{ $point->derniereMesure->status === 'Compliant' ? 'text-emerald-400' : ($point->derniereMesure->status === 'Alert' ? 'text-amber-400' : 'text-red-400') }}">
                                        {{ $point->derniereMesure->status }} ({{ $point->derniereMesure->overall_compliance }}%)
                                    </div>
                                @else
                                    <span class="text-xs text-slate-500 italic">Aucune mesure</span>
                                @endif
                            </td>
                            <td class="p-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a class="px-2.5 py-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-cyan-300 hover:text-white text-xs border border-slate-700 transition-colors" href="{{ route('manager.points-mesure.edit', $point) }}">
                                        Modifier
                                    </a>
                                    <form method="POST" action="{{ route('manager.points-mesure.destroy', $point) }}" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ou désactiver ce point de prélèvement ? Si le point contient des mesures historiques, il sera désactivé sans perte de données.')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="px-2.5 py-1 rounded-lg bg-red-500/10 hover:bg-red-500/20 text-red-400 hover:text-red-300 text-xs border border-red-500/30 transition-colors">
                                            Supprimer
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-12 text-center text-slate-400">
                                <i data-lucide="inbox" class="w-8 h-8 mx-auto text-slate-600 mb-2"></i>
                                <p class="text-sm">Aucun point de prélèvement trouvé avec ces critères.</p>
                                @if(request()->hasAny(['search', 'statut', 'type']))
                                    <a href="{{ route('manager.points-mesure.index') }}" class="text-xs text-teal-400 hover:underline mt-2 inline-block">
                                        Effacer les filtres
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($points->hasPages())
            <div class="mt-4">
                {{ $points->links('pagination::tailwind') }}
            </div>
        @endif
    </div>
</div>
@endsection
