@extends(session('user.role') === 'admin' ? 'layouts.admin' : 'layouts.manager')

@section('title', 'Zones — AquaSecure')

@section(session('user.role') === 'admin' ? 'admin-content' : 'manager-content')
@php
    $inventoryRoutePrefix = session('user.role') === 'admin' ? 'admin' : 'manager';
@endphp
<div class="manager-catalog-page mx-auto max-w-7xl space-y-5">
    {{-- En-tête de la page --}}
    <div class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-cyan-400/15 bg-slate-900/40 px-5 py-4 shadow-sm">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-cyan-300">Réseau d'eau · Territoires</p>
            <h1 class="mt-1 text-2xl font-bold tracking-tight text-white">Zones géographiques</h1>
            <p class="mt-1 text-sm text-cyan-100/60">Gestion territoriale du réseau d'eau potable et supervision des infrastructures locales.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route($inventoryRoutePrefix . '.zones.export.csv') }}" class="inline-flex items-center gap-1.5 rounded-lg border border-cyan-400/30 bg-cyan-500/10 px-3 py-2 text-sm font-medium text-cyan-200 hover:bg-cyan-500/20 transition">
                <svg class="w-4 h-4 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Export CSV
            </a>
            <a href="{{ route($inventoryRoutePrefix . '.infrastructures.index') }}" class="rounded-lg border border-cyan-400/20 px-3.5 py-2 text-sm font-medium text-cyan-100/80 hover:bg-white/5 hover:text-white transition">
                Toutes les infrastructures
            </a>
            <a href="{{ route($inventoryRoutePrefix . '.zones.create') }}" class="rounded-lg bg-cyan-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-cyan-500 transition">
                + Nouvelle zone
            </a>
        </div>
    </div>

    @include('manager.shared.flash')

    {{-- Bandeau des Statistiques Globales du Réseau --}}
    @if(isset($statsGlobales))
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-5">
            <div class="rounded-2xl border border-cyan-400/15 bg-slate-900/60 p-4 shadow-sm">
                <p class="text-xs font-medium text-cyan-100/60">Zones sous gestion</p>
                <p class="mt-2 text-2xl font-bold text-white">{{ $statsGlobales['total_zones'] }}</p>
                <p class="mt-1 text-[11px] text-cyan-200/50">Territoires supervisés</p>
            </div>
            <div class="rounded-2xl border border-cyan-400/15 bg-slate-900/60 p-4 shadow-sm">
                <p class="text-xs font-medium text-cyan-100/60">Total équipements</p>
                <p class="mt-2 text-2xl font-bold text-white">{{ $statsGlobales['total_infrastructures'] }}</p>
                <p class="mt-1 text-[11px] text-cyan-200/50">Sur tout le réseau</p>
            </div>
            <div class="rounded-2xl border border-cyan-400/15 bg-slate-900/60 p-4 shadow-sm">
                <p class="text-xs font-medium text-cyan-100/60">Taux opérationnel global</p>
                <p class="mt-2 text-2xl font-bold {{ $statsGlobales['pourcentage_en_service'] >= 80 ? 'text-emerald-400' : ($statsGlobales['pourcentage_en_service'] >= 50 ? 'text-amber-400' : 'text-rose-400') }}">
                    {{ $statsGlobales['pourcentage_en_service'] }}%
                </p>
                <p class="mt-1 text-[11px] text-cyan-200/50">Équipements en service</p>
            </div>
            <div class="rounded-2xl border border-cyan-400/15 bg-slate-900/60 p-4 shadow-sm">
                <p class="text-xs font-medium text-cyan-100/60">Alertes de maintenance</p>
                <p class="mt-2 text-2xl font-bold {{ $statsGlobales['nombre_en_retard_maintenance'] > 0 ? 'text-rose-400' : 'text-emerald-400' }}">
                    {{ $statsGlobales['nombre_en_retard_maintenance'] }}
                </p>
                <p class="mt-1 text-[11px] {{ $statsGlobales['nombre_en_retard_maintenance'] > 0 ? 'text-rose-300 font-medium' : 'text-cyan-200/50' }}">
                    {{ $statsGlobales['nombre_en_retard_maintenance'] > 0 ? 'En retard d’intervention' : 'À jour' }}
                </p>
            </div>
            <div class="col-span-2 rounded-2xl border border-cyan-400/15 bg-slate-900/60 p-4 shadow-sm sm:col-span-1">
                <p class="text-xs font-medium text-cyan-100/60">Santé réseau globale</p>
                <p class="mt-2 text-2xl font-bold {{ $statsGlobales['score_sante_moyen'] >= 70 ? 'text-emerald-400' : ($statsGlobales['score_sante_moyen'] >= 40 ? 'text-amber-400' : 'text-rose-400') }}">
                    {{ $statsGlobales['score_sante_moyen'] }}/100
                </p>
                <p class="mt-1 text-[11px] text-cyan-200/50">Score moyen pondéré</p>
            </div>
        </div>
    @endif

    {{-- Formulaire de recherche et tri --}}
    <form method="GET" action="{{ route($inventoryRoutePrefix . '.zones.index') }}" class="rounded-2xl border border-cyan-400/15 bg-slate-900/60 p-4 shadow-sm space-y-3">
        <input type="hidden" name="sort" value="{{ $filters['sort'] }}">
        <input type="hidden" name="direction" value="{{ $filters['direction'] }}">

        <div class="flex flex-wrap items-center gap-3">
            <div class="flex-1 min-w-[260px]">
                <label for="q" class="sr-only">Recherche</label>
                <x-ui.search-input
                    name="q"
                    id="q"
                    :value="$filters['q']"
                    placeholder="Recherche zone (nom, ville, description)..."
                    :dark="true"
                />
            </div>
            <div class="flex items-center gap-2">
                @if($filters['q'] !== '' || $filters['sort'] !== 'nom' || $filters['direction'] !== 'asc')
                    <a href="{{ route($inventoryRoutePrefix . '.zones.index') }}" class="rounded-lg border border-cyan-400/20 px-3 py-2 text-xs font-medium text-cyan-200 hover:bg-white/5 hover:text-white transition">
                        Réinitialiser
                    </a>
                @endif
                <button type="submit" class="rounded-lg bg-cyan-600 px-4 py-2 text-xs font-semibold text-white hover:bg-cyan-500 transition shadow-sm">
                    Filtrer
                </button>
            </div>
        </div>

        <div class="flex items-center justify-between pt-1 border-t border-white/5 text-xs text-cyan-100/70">
            <p><span class="font-bold text-white">{{ $zones->total() }}</span> {{ $zones->total() > 1 ? 'zones répertoriées' : 'zone répertoriée' }}</p>
        </div>
    </form>

    {{-- Tableau des zones avec barre de progression en service --}}
    <div class="overflow-hidden rounded-2xl border border-cyan-400/15 bg-slate-900/60 shadow-sm">
        @if($zones->isEmpty())
            <x-ui.empty-state
                icon="map-pin"
                title="Aucune zone trouvée"
                description="Aucune zone géographique ne correspond à votre recherche."
            >
                @if($filters['q'] !== '')
                    <a href="{{ route($inventoryRoutePrefix . '.zones.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-cyan-400/30 bg-cyan-500/10 px-4 py-2 text-sm font-medium text-cyan-300 hover:bg-cyan-500/20 transition">
                        Réinitialiser la recherche
                    </a>
                @else
                    <a href="{{ route($inventoryRoutePrefix . '.zones.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-cyan-600 px-4 py-2 text-sm font-medium text-white hover:bg-cyan-500 transition">
                        + Créer une zone
                    </a>
                @endif
            </x-ui.empty-state>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-white/5 text-xs font-medium text-cyan-100/70">
                        <tr>
                            <th class="px-4 py-3">
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'nom', 'direction' => ($filters['sort'] === 'nom' && $filters['direction'] === 'asc') ? 'desc' : 'asc', 'page' => 1]) }}" class="inline-flex items-center gap-1.5 hover:text-white transition">
                                    <span>Nom</span>
                                    @if($filters['sort'] === 'nom')
                                        <span class="text-cyan-300 font-bold">{{ $filters['direction'] === 'asc' ? '↑' : '↓' }}</span>
                                    @else
                                        <span class="text-cyan-100/30">↕</span>
                                    @endif
                                </a>
                            </th>
                            <th class="px-4 py-3">Chef-lieu</th>
                            <th class="px-4 py-3">Statut</th>
                            <th class="px-4 py-3">Responsable</th>
                            <th class="px-4 py-3">
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'infrastructures_count', 'direction' => ($filters['sort'] === 'infrastructures_count' && $filters['direction'] === 'asc') ? 'desc' : 'asc', 'page' => 1]) }}" class="inline-flex items-center gap-1.5 hover:text-white transition">
                                    <span>Infrastructures & Opérabilité</span>
                                    @if($filters['sort'] === 'infrastructures_count')
                                        <span class="text-cyan-300 font-bold">{{ $filters['direction'] === 'asc' ? '↑' : '↓' }}</span>
                                    @else
                                        <span class="text-cyan-100/30">↕</span>
                                    @endif
                                </a>
                            </th>
                            <th class="px-4 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        @foreach($zones as $zone)
                        @php
                            $totalInfra = $zone->infrastructures_count;
                            $enService = $zone->en_service_count ?? 0;
                            $pctEnService = $totalInfra > 0 ? round(($enService / $totalInfra) * 100) : 0;
                        @endphp
                        <tr class="text-cyan-100/70 hover:bg-white/5 transition">
                            <td class="px-4 py-3 font-semibold text-white">
                                <a href="{{ route($inventoryRoutePrefix . '.zones.show', $zone) }}" class="hover:text-cyan-300 transition">
                                    {{ $zone->nom }}
                                </a>
                            </td>
                            <td class="px-4 py-3 text-cyan-100/90">{{ $zone->localisation }}</td>
                            <td class="px-4 py-3">
                                <x-zone.statut-badge :statut="$zone->statut" />
                            </td>
                            <td class="px-4 py-3 text-xs">
                                @if($zone->responsable)
                                    <span class="text-white font-medium">{{ $zone->responsable->name }}</span>
                                @else
                                    <span class="text-cyan-100/40">Non assigné</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="space-y-1 min-w-[140px]">
                                    <div class="flex items-center justify-between text-xs">
                                        <span class="font-bold text-white">{{ $totalInfra }} {{ $totalInfra > 1 ? 'équipements' : 'équipement' }}</span>
                                        @if($totalInfra > 0)
                                            <span class="text-[11px] {{ $pctEnService >= 80 ? 'text-emerald-400' : ($pctEnService >= 50 ? 'text-amber-400' : 'text-rose-400') }}">
                                                {{ $pctEnService }}% en service
                                            </span>
                                        @endif
                                    </div>
                                    @if($totalInfra > 0)
                                        <div class="h-1.5 w-full rounded-full bg-slate-800 overflow-hidden ring-1 ring-white/5">
                                            <div
                                                class="h-full rounded-full {{ $pctEnService >= 80 ? 'bg-emerald-400' : ($pctEnService >= 50 ? 'bg-amber-400' : 'bg-rose-400') }}"
                                                style="width: {{ $pctEnService }}%"
                                            ></div>
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex justify-end gap-3 whitespace-nowrap text-xs font-medium">
                                    <a class="text-cyan-300 hover:text-cyan-200" href="{{ route($inventoryRoutePrefix . '.zones.show', $zone) }}">Détails</a>
                                    <a class="text-cyan-100/60 hover:text-white" href="{{ route($inventoryRoutePrefix . '.zones.edit', $zone) }}">Modifier</a>
                                    <form id="delete-zone-form-{{ $zone->id }}" method="POST" action="{{ route($inventoryRoutePrefix . '.zones.destroy', $zone) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button
                                            class="text-rose-400 hover:text-rose-300 transition"
                                            type="button"
                                            onclick="confirmDeleteZone('{{ $zone->id }}', '{{ addslashes($zone->nom) }}', {{ $zone->infrastructures_count }})"
                                        >
                                            Supprimer
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($zones->hasPages())
                <div class="border-t border-white/10 px-5 py-3">{{ $zones->links() }}</div>
            @endif
        @endif
    </div>
</div>

@push('scripts')
<script>
    function confirmDeleteZone(id, nom, infraCount) {
        let impactHtml = '';
        let hint = '';

        if (infraCount > 0) {
            impactHtml += '<div class="flex items-start gap-2 mb-1"><span class="flex-shrink-0 mt-0.5 h-2 w-2 rounded-full bg-rose-400"></span><span class="text-rose-200/90 font-medium">Bloqué par ' + infraCount + ' infrastructure(s) rattachée(s)</span></div>';
            impactHtml += '<div class="flex items-start gap-2"><span class="flex-shrink-0 mt-0.5 h-2 w-2 rounded-full bg-amber-400"></span><span class="text-amber-200/90">Détachez d\'abord les équipements avant de pouvoir archiver cette zone.</span></div>';
            hint = 'Opération bloquée : ' + infraCount + ' équipement' + (infraCount > 1 ? 's' : '') + ' dépendent encore de cette zone.';
        } else {
            impactHtml += '<div class="flex items-start gap-2 mb-1"><span class="flex-shrink-0 mt-0.5 h-2 w-2 rounded-full bg-emerald-400"></span><span class="text-emerald-200/90 font-medium">Aucune dépendance — suppression autorisée</span></div>';
            impactHtml += '<div class="flex items-start gap-2"><span class="flex-shrink-0 mt-0.5 h-2 w-2 rounded-full bg-cyan-400"></span><span class="text-cyan-200/90">La zone sera archivée (Soft Delete). Son historique reste conservé.</span></div>';
            hint = 'Cette opération archivera la zone dans le système (suppression logique). Les données associées restent conservées.';
        }

        window.openAquaConfirm({
            variant: 'danger',
            title: 'Supprimer la zone géographique',
            message: 'Êtes-vous certain de vouloir supprimer « ' + nom + ' » du réseau ?',
            badge: nom + ' · ' + infraCount + ' équipement' + (infraCount > 1 ? 's' : ''),
            impact: impactHtml,
            hint: hint,
            confirmText: infraCount > 0 ? 'Supprimer quand même' : 'Supprimer la zone',
            cancelText: 'Annuler',
            footerLeft: 'Action irréversible',
            onConfirm: function () {
                document.getElementById('delete-zone-form-' + id).submit();
            }
        });
    }
</script>
@endpush
@endsection
