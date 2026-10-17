@extends('layouts.manager')

@section('title', 'Infrastructures — AquaSecure')

@section('manager-content')
@php
    $inventoryRoutePrefix = 'manager';
@endphp
<div class="manager-catalog-page mx-auto max-w-7xl space-y-5">
    {{-- En-tête de la page --}}
    <div class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-cyan-400/15 bg-slate-900/40 px-5 py-4 shadow-sm">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-cyan-300">Réseau d'eau · Exploitation</p>
            <h1 class="mt-1 text-2xl font-bold tracking-tight text-white">Infrastructures</h1>
            <p class="mt-1 text-sm text-cyan-100/60">Surveillance, maintenance et état opérationnel des équipements hydrauliques.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route($inventoryRoutePrefix . '.infrastructures.export.csv') }}" class="inline-flex items-center gap-1.5 rounded-lg border border-cyan-400/30 bg-cyan-500/10 px-3 py-2 text-sm font-medium text-cyan-200 hover:bg-cyan-500/20 transition">
                <svg class="w-4 h-4 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Export CSV
            </a>
            <a href="{{ route($inventoryRoutePrefix . '.zones.index') }}" class="rounded-lg border border-cyan-400/20 px-3.5 py-2 text-sm font-medium text-cyan-100/80 hover:bg-white/5 hover:text-white transition">
                Voir les zones
            </a>
            <a href="{{ route($inventoryRoutePrefix . '.infrastructures.create') }}" class="rounded-lg bg-cyan-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-cyan-500 transition">
                + Nouvelle infrastructure
            </a>
        </div>
    </div>

    @include('manager.shared.flash')

    {{-- Bloc : Alertes de maintenance actives --}}
    @if(isset($alertes) && $alertes->isNotEmpty())
        <div class="rounded-2xl border border-amber-500/30 bg-amber-500/10 p-5 shadow-sm space-y-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="flex h-2.5 w-2.5 rounded-full bg-amber-400 animate-ping"></span>
                    <h2 class="text-sm font-bold text-amber-200">Alertes de maintenance non résolues ({{ $alertes->count() }})</h2>
                </div>
                <span class="text-xs text-amber-300/80">Vérification automatique quotidienne</span>
            </div>

            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($alertes as $alerte)
                    <div class="flex flex-col justify-between rounded-xl border border-amber-500/20 bg-slate-950/60 p-3.5 text-xs text-amber-100/90 shadow-inner">
                        <div class="space-y-1.5">
                            <div class="flex items-start justify-between gap-2">
                                <a href="{{ route($inventoryRoutePrefix . '.infrastructures.show', $alerte->infrastructure) }}" class="font-bold text-white hover:text-cyan-300 transition">
                                    {{ $alerte->infrastructure->nom }}
                                </a>
                                <span class="rounded px-1.5 py-0.5 text-[10px] font-semibold uppercase {{ $alerte->type === 'retard' ? 'bg-rose-500/20 text-rose-300' : 'bg-amber-500/20 text-amber-300' }}">
                                    {{ $alerte->type }}
                                </span>
                            </div>
                            <p class="text-slate-300">{{ $alerte->message }}</p>
                            <p class="text-[11px] text-cyan-200/60">
                                Zone : {{ $alerte->infrastructure->zone->nom }} · Échéance : {{ $alerte->date_echeance ? $alerte->date_echeance->format('d/m/Y') : 'Non définie' }}
                            </p>
                        </div>
                        <div class="mt-3 pt-2 border-t border-white/5 flex justify-end">
                            <form method="POST" action="{{ route($inventoryRoutePrefix . '.infrastructures.alertes.resoudre', $alerte) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="rounded-lg border border-amber-400/30 bg-amber-500/10 px-2.5 py-1 text-xs font-medium text-amber-300 hover:bg-amber-500/20 transition">
                                    ✓ Marquer comme résolue
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Formulaire de recherche et de filtres --}}
    <form method="GET" action="{{ route($inventoryRoutePrefix . '.infrastructures.index') }}" class="rounded-2xl border border-cyan-400/15 bg-slate-900/60 p-5 shadow-sm space-y-4">
        <input type="hidden" name="sort" value="{{ $filters['sort'] }}">
        <input type="hidden" name="direction" value="{{ $filters['direction'] }}">

        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
            <div class="sm:col-span-2 lg:col-span-1">
                <label for="q" class="sr-only">Recherche</label>
                <x-ui.search-input
                    name="q"
                    id="q"
                    :value="$filters['q']"
                    placeholder="Recherche (nom, réf, lieu)..."
                    :dark="true"
                />
            </div>
            <div>
                <label for="zone_id" class="sr-only">Zone</label>
                <select id="zone_id" name="zone_id" class="w-full rounded-xl border border-cyan-400/20 bg-slate-950/40 px-3 py-2.5 text-sm text-white outline-none transition focus:border-cyan-400/50 focus:ring-2 focus:ring-cyan-400/20">
                    <option value="">Toutes les zones</option>
                    @foreach($zones as $zone)
                        <option value="{{ $zone->id }}" @selected((string) $filters['zone_id'] === (string) $zone->id)>{{ $zone->nom }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="type" class="sr-only">Type</label>
                <select id="type" name="type" class="w-full rounded-xl border border-cyan-400/20 bg-slate-950/40 px-3 py-2.5 text-sm text-white outline-none transition focus:border-cyan-400/50 focus:ring-2 focus:ring-cyan-400/20">
                    <option value="">Tous les types</option>
                    @foreach($types as $typeOption)
                        <option value="{{ $typeOption->value }}" @selected($filters['type'] === $typeOption->value)>{{ $typeOption->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="etat" class="sr-only">État</label>
                <select id="etat" name="etat" class="w-full rounded-xl border border-cyan-400/20 bg-slate-950/40 px-3 py-2.5 text-sm text-white outline-none transition focus:border-cyan-400/50 focus:ring-2 focus:ring-cyan-400/20">
                    <option value="">Tous les états</option>
                    @foreach($etats as $etatOption)
                        <option value="{{ $etatOption->value }}" @selected($filters['etat'] === $etatOption->value)>{{ $etatOption->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="sante" class="sr-only">Santé / Maintenance</label>
                <select id="sante" name="sante" class="w-full rounded-xl border border-cyan-400/20 bg-slate-950/40 px-3 py-2.5 text-sm text-white outline-none transition focus:border-cyan-400/50 focus:ring-2 focus:ring-cyan-400/20">
                    <option value="">Toute santé</option>
                    <option value="retard" @selected($filters['sante'] === 'retard')>⚠️ En retard de maintenance</option>
                    <option value="critique" @selected($filters['sante'] === 'critique')>🚨 Santé critique / Hors service</option>
                </select>
            </div>
        </div>

        <div class="flex flex-wrap items-center justify-between gap-3 pt-2 border-t border-white/5">
            <p class="text-xs font-medium text-cyan-100/70">{{ $infrastructures->total() }} {{ $infrastructures->total() > 1 ? 'infrastructures trouvées' : 'infrastructure trouvée' }}</p>
            <div class="flex items-center gap-2">
                @if($filters['q'] !== '' || $filters['zone_id'] !== '' || $filters['type'] !== '' || $filters['etat'] !== '' || $filters['sante'] !== '' || $filters['sort'] !== 'nom' || $filters['direction'] !== 'asc')
                    <a href="{{ route($inventoryRoutePrefix . '.infrastructures.index') }}" class="rounded-lg border border-cyan-400/20 px-3 py-1.5 text-xs font-medium text-cyan-200 hover:bg-white/5 hover:text-white transition">
                        Réinitialiser
                    </a>
                @endif
                <button type="submit" class="rounded-lg bg-cyan-600 px-4 py-1.5 text-xs font-semibold text-white hover:bg-cyan-500 transition shadow-sm">
                    Filtrer
                </button>
            </div>
        </div>
    </form>

    {{-- Tableau des données --}}
    <div class="overflow-hidden rounded-2xl border border-cyan-400/15 bg-slate-900/60 shadow-sm">
        @if($infrastructures->isEmpty())
            <x-ui.empty-state
                icon="filter-x"
                title="Aucune infrastructure trouvée"
                description="Aucun équipement ne correspond à vos critères de recherche ou de filtre."
            >
                @if($filters['q'] !== '' || $filters['zone_id'] !== '' || $filters['type'] !== '' || $filters['etat'] !== '' || $filters['sante'] !== '')
                    <a href="{{ route($inventoryRoutePrefix . '.infrastructures.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-cyan-400/30 bg-cyan-500/10 px-4 py-2 text-sm font-medium text-cyan-300 hover:bg-cyan-500/20 transition">
                        Réinitialiser les filtres
                    </a>
                @else
                    <a href="{{ route($inventoryRoutePrefix . '.infrastructures.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-cyan-600 px-4 py-2 text-sm font-medium text-white hover:bg-cyan-500 transition">
                        + Ajouter une infrastructure
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
                                    <span>Nom & Référence</span>
                                    @if($filters['sort'] === 'nom')
                                        <span class="text-cyan-300 font-bold">{{ $filters['direction'] === 'asc' ? '↑' : '↓' }}</span>
                                    @else
                                        <span class="text-cyan-100/30">↕</span>
                                    @endif
                                </a>
                            </th>
                            <th class="px-4 py-3">
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'type', 'direction' => ($filters['sort'] === 'type' && $filters['direction'] === 'asc') ? 'desc' : 'asc', 'page' => 1]) }}" class="inline-flex items-center gap-1.5 hover:text-white transition">
                                    <span>Type</span>
                                    @if($filters['sort'] === 'type')
                                        <span class="text-cyan-300 font-bold">{{ $filters['direction'] === 'asc' ? '↑' : '↓' }}</span>
                                    @else
                                        <span class="text-cyan-100/30">↕</span>
                                    @endif
                                </a>
                            </th>
                            <th class="px-4 py-3">
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'etat', 'direction' => ($filters['sort'] === 'etat' && $filters['direction'] === 'asc') ? 'desc' : 'asc', 'page' => 1]) }}" class="inline-flex items-center gap-1.5 hover:text-white transition">
                                    <span>État</span>
                                    @if($filters['sort'] === 'etat')
                                        <span class="text-cyan-300 font-bold">{{ $filters['direction'] === 'asc' ? '↑' : '↓' }}</span>
                                    @else
                                        <span class="text-cyan-100/30">↕</span>
                                    @endif
                                </a>
                            </th>
                            <th class="px-4 py-3">
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'zone', 'direction' => ($filters['sort'] === 'zone' && $filters['direction'] === 'asc') ? 'desc' : 'asc', 'page' => 1]) }}" class="inline-flex items-center gap-1.5 hover:text-white transition">
                                    <span>Zone</span>
                                    @if($filters['sort'] === 'zone')
                                        <span class="text-cyan-300 font-bold">{{ $filters['direction'] === 'asc' ? '↑' : '↓' }}</span>
                                    @else
                                        <span class="text-cyan-100/30">↕</span>
                                    @endif
                                </a>
                            </th>
                            <th class="px-4 py-3">Criticité</th>
                            <th class="px-4 py-3">Santé</th>
                            <th class="px-4 py-3">
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'date_installation', 'direction' => ($filters['sort'] === 'date_installation' && $filters['direction'] === 'asc') ? 'desc' : 'asc', 'page' => 1]) }}" class="inline-flex items-center gap-1.5 hover:text-white transition">
                                    <span>Installation</span>
                                    @if($filters['sort'] === 'date_installation')
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
                        @foreach($infrastructures as $infrastructure)
                        <tr class="text-cyan-100/70 hover:bg-white/5 transition">
                            <td class="px-4 py-3">
                                <div>
                                    <a href="{{ route($inventoryRoutePrefix . '.infrastructures.show', $infrastructure) }}" class="font-semibold text-white hover:text-cyan-300 transition">
                                        {{ $infrastructure->nom }}
                                    </a>
                                    @if($infrastructure->reference)
                                        <p class="font-mono text-xs text-cyan-200/50">{{ $infrastructure->reference }}</p>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3">{{ $infrastructure->type->label() }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <x-infrastructure.etat-badge :etat="$infrastructure->etat" />
                                    {{-- Sélecteur rapide d'état avec confirmation modale --}}
                                    <form id="etat-form-{{ $infrastructure->id }}" method="POST" action="{{ route($inventoryRoutePrefix . '.infrastructures.etat', $infrastructure) }}" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <select
                                            id="etat-select-{{ $infrastructure->id }}"
                                            name="etat"
                                            title="Modifier rapidement l'état"
                                            data-initial="{{ $infrastructure->etat->value }}"
                                            onchange="confirmChangeEtat('{{ $infrastructure->id }}', '{{ addslashes($infrastructure->nom) }}', this)"
                                            class="rounded border border-white/10 bg-slate-950/80 px-1 py-0.5 text-[11px] text-cyan-200 outline-none hover:border-cyan-400/40 cursor-pointer"
                                        >
                                            @foreach($etats as $etatOpt)
                                                <option value="{{ $etatOpt->value }}" @selected($infrastructure->etat === $etatOpt)>{{ $etatOpt->label() }}</option>
                                            @endforeach
                                        </select>
                                    </form>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <a class="text-cyan-300 hover:text-cyan-200 font-medium" href="{{ route($inventoryRoutePrefix . '.zones.show', $infrastructure->zone) }}">
                                    {{ $infrastructure->zone->nom }}
                                </a>
                            </td>
                            <td class="px-4 py-3">
                                <x-infrastructure.criticite-badge :criticite="$infrastructure->niveau_criticite" />
                            </td>
                            <td class="px-4 py-3">
                                <x-infrastructure.sante-badge :sante="$infrastructure->sante" />
                            </td>
                            <td class="px-4 py-3 text-xs">{{ $infrastructure->date_installation->format('d/m/Y') }}</td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex justify-end items-center gap-3 whitespace-nowrap text-xs font-medium">
                                    <a class="text-cyan-300 hover:text-cyan-200" href="{{ route($inventoryRoutePrefix . '.infrastructures.show', $infrastructure) }}">Détails</a>
                                    <a class="text-cyan-100/60 hover:text-white" href="{{ route($inventoryRoutePrefix . '.infrastructures.edit', $infrastructure) }}">Modifier</a>
                                    <form id="delete-infra-form-{{ $infrastructure->id }}" method="POST" action="{{ route($inventoryRoutePrefix . '.infrastructures.destroy', $infrastructure) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button
                                            class="text-rose-400 hover:text-rose-300 transition"
                                            type="button"
                                            onclick="confirmDeleteInfra('{{ $infrastructure->id }}', '{{ addslashes($infrastructure->nom) }}', '{{ addslashes($infrastructure->reference ?? '') }}')"
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
            @if($infrastructures->hasPages())
                <div class="border-t border-white/10 px-5 py-3">{{ $infrastructures->links() }}</div>
            @endif
        @endif
    </div>
</div>

@push('scripts')
<script>
    function confirmDeleteInfra(id, nom, ref) {
        const badgeText = ref ? nom + ' [' + ref + ']' : nom;
        window.openAquaConfirm({
            variant: 'danger',
            title: 'Supprimer l\'infrastructure',
            message: 'Êtes-vous certain de vouloir supprimer cet équipement hydraulique ?',
            badge: badgeText,
            hint: 'L\'infrastructure sera archivée (Soft Delete) et retirée de la supervision active. Tout son historique d\'audit et ses interventions restent conservés.',
            confirmText: 'Supprimer l\'équipement',
            cancelText: 'Annuler',
            onConfirm: function () {
                document.getElementById('delete-infra-form-' + id).submit();
            }
        });
    }

    function confirmChangeEtat(id, nom, selectEl) {
        const initialVal = selectEl.getAttribute('data-initial');
        const newVal = selectEl.value;

        if (initialVal === newVal) return;

        window.openAquaConfirm({
            variant: 'warning',
            title: 'Modifier l\'état opérationnel',
            message: 'Confirmez-vous le changement d\'état pour cet ouvrage ?',
            badge: nom + ' : passage à « ' + newVal + ' »',
            hint: 'Cette transition sera immédiatement enregistrée avec votre compte dans l\'historique d\'audit.',
            confirmText: 'Valider la modification',
            cancelText: 'Annuler',
            onConfirm: function () {
                document.getElementById('etat-form-' + id).submit();
            },
            onCancel: function () {
                selectEl.value = initialVal;
            }
        });
    }
</script>
@endpush
@endsection
