@extends(request()->routeIs('admin.*') ? 'layouts.admin' : 'layouts.manager')

@section('title', $infrastructure->nom.' — AquaSecure')

@section(request()->routeIs('admin.*') ? 'admin-content' : 'manager-content')
@php
    $inventoryRoutePrefix = request()->routeIs('admin.*') ? 'admin' : 'manager';
@endphp
<div class="mx-auto max-w-5xl space-y-6">
    {{-- En-tête de page --}}
    <div class="flex flex-wrap items-start justify-between gap-4 rounded-2xl border border-cyan-400/15 bg-slate-900/40 p-5 shadow-sm">
        <div class="space-y-1">
            <a href="{{ route($inventoryRoutePrefix . '.infrastructures.index') }}" class="inline-flex items-center text-sm font-medium text-cyan-300 hover:text-cyan-200 transition">
                ← Toutes les infrastructures
            </a>
            <div class="flex flex-wrap items-center gap-3 pt-1">
                <h1 class="text-2xl font-bold text-white">{{ $infrastructure->nom }}</h1>
                <x-infrastructure.etat-badge :etat="$infrastructure->etat" />
                <x-infrastructure.criticite-badge :criticite="$infrastructure->niveau_criticite" />
            </div>
            <p class="text-sm text-cyan-100/60">
                {{ $infrastructure->type->label() }} · Zone :
                <a class="text-cyan-300 hover:underline" href="{{ route($inventoryRoutePrefix . '.zones.show', $infrastructure->zone) }}">
                    {{ $infrastructure->zone->nom }}
                </a>
            </p>
        </div>
        <div class="flex items-center gap-2">
            @if($infrastructure->latitude && $infrastructure->longitude)
                <a href="{{ route('manager.map', ['infrastructure' => $infrastructure->id]) }}" class="inline-flex items-center gap-1.5 rounded-lg border border-cyan-400/30 bg-cyan-500/10 px-3.5 py-2 text-sm font-medium text-cyan-200 hover:bg-cyan-500/20 transition">
                    <svg class="w-4 h-4 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    Voir sur la carte
                </a>
            @endif
            <a href="{{ route($inventoryRoutePrefix . '.infrastructures.export.pdf', $infrastructure) }}" class="inline-flex items-center gap-1.5 rounded-lg border border-cyan-400/30 bg-cyan-500/10 px-3.5 py-2 text-sm font-medium text-cyan-200 hover:bg-cyan-500/20 transition">
                <svg class="w-4 h-4 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Fiche PDF
            </a>
            <a href="{{ route($inventoryRoutePrefix . '.infrastructures.edit', $infrastructure) }}" class="rounded-lg border border-cyan-400/20 px-3.5 py-2 text-sm font-medium text-cyan-100/80 hover:bg-white/5 transition">
                Modifier
            </a>
            <form id="delete-infra-form-show" method="POST" action="{{ route($inventoryRoutePrefix . '.infrastructures.destroy', $infrastructure) }}" class="inline">
                @csrf
                @method('DELETE')
                <button
                    type="button"
                    onclick="confirmDeleteInfraShow()"
                    class="rounded-lg border border-rose-400/30 px-3.5 py-2 text-sm font-medium text-rose-300 hover:bg-rose-500/10 transition"
                >
                    Supprimer
                </button>
            </form>
        </div>
    </div>

    @include('manager.shared.flash')

    {{-- Bannière d'alerte : Maintenance en retard --}}
    @if($infrastructure->date_prochaine_maintenance && $infrastructure->date_prochaine_maintenance->isPast())
        <div class="flex items-start gap-3 rounded-2xl border border-rose-500/40 bg-rose-500/10 p-4 text-rose-200 shadow-sm animate-pulse">
            <svg class="h-5 w-5 flex-shrink-0 text-rose-400 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <div class="flex-1 text-sm">
                <p class="font-semibold text-rose-300">Maintenance en retard</p>
                <p class="mt-0.5 text-xs text-rose-200/80">
                    L'intervention prévue le {{ $infrastructure->date_prochaine_maintenance->format('d/m/Y') }} est dépassée depuis {{ $infrastructure->date_prochaine_maintenance->diffForHumans(null, true) }}. Planifiez une intervention urgente pour éviter toute défaillance.
                </p>
            </div>
        </div>
    @endif

    {{-- Alertes opérationnelles actives --}}
    @if(isset($infrastructure->alertes) && $infrastructure->alertes->isNotEmpty())
        <div class="space-y-3">
            @foreach($infrastructure->alertes as $alerte)
                <div class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-amber-400/30 bg-amber-500/10 p-4 text-amber-200 shadow-sm">
                    <div class="flex items-start gap-3">
                        <svg class="h-5 w-5 flex-shrink-0 text-amber-400 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <div>
                            <p class="font-semibold text-amber-300">Alerte technique : {{ ucfirst(str_replace('_', ' ', $alerte->type)) }}</p>
                            <p class="text-xs text-amber-100/80 mt-0.5">{{ $alerte->message }}</p>
                            <span class="text-[11px] text-amber-200/50 block mt-1">Déclenchée {{ $alerte->created_at->diffForHumans() }}</span>
                        </div>
                    </div>
                    <form id="resoudre-alerte-form-{{ $alerte->id }}" method="POST" action="{{ route($inventoryRoutePrefix . '.infrastructures.alertes.resoudre', $alerte) }}">
                        @csrf
                        @method('PATCH')
                        <button
                            type="button"
                            onclick="confirmResoudreAlerte('{{ $alerte->id }}', '{{ addslashes($alerte->message) }}')"
                            class="rounded-lg border border-amber-400/30 bg-amber-500/20 px-3 py-1.5 text-xs font-semibold text-amber-100 hover:bg-amber-500/30 transition shadow-sm"
                        >
                            Marquer comme résolue
                        </button>
                    </form>
                </div>
            @endforeach
        </div>
    @endif

    @include('components.infrastructure.risk-card', [
        'risk' => $risk,
        'history' => $riskHistory,
        'infrastructure' => $infrastructure,
        'routePrefix' => $inventoryRoutePrefix,
    ])

    {{-- Grille Santé & Action Rapide --}}
    <div class="grid gap-6 md:grid-cols-3">
        {{-- Carte Score de Santé --}}
        <div class="rounded-2xl border border-cyan-400/15 bg-slate-900/35 p-5 shadow-sm md:col-span-2 space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-base font-semibold text-white">Score d’état de santé</h2>
                    <p class="text-xs text-cyan-100/60">Évaluation multi-critères (état, âge, maintenance, alertes)</p>
                </div>
                <x-infrastructure.sante-badge :sante="$sante" />
            </div>

            {{-- Jauge de progression --}}
            <div class="space-y-1.5">
                <div class="flex justify-between text-xs text-cyan-100/70">
                    <span>Indice de santé global</span>
                    <span class="font-bold {{ $sante['score'] >= 70 ? 'text-emerald-400' : ($sante['score'] >= 40 ? 'text-amber-400' : 'text-rose-400') }}">
                        {{ $sante['score'] }} / 100
                    </span>
                </div>
                <div class="h-3 w-full rounded-full bg-slate-800 overflow-hidden ring-1 ring-white/10">
                    <div
                        class="h-full rounded-full transition-all duration-500 {{ $sante['score'] >= 70 ? 'bg-gradient-to-r from-emerald-500 to-cyan-400' : ($sante['score'] >= 40 ? 'bg-gradient-to-r from-amber-500 to-amber-400' : 'bg-gradient-to-r from-rose-600 to-rose-400') }}"
                        style="width: {{ $sante['score'] }}%"
                    ></div>
                </div>
            </div>

            {{-- Facteurs d'explication --}}
            <div class="space-y-2 pt-2 border-t border-white/5">
                <p class="text-xs font-semibold text-cyan-100/80">Diagnostic détaillé :</p>
                <ul class="space-y-1.5">
                    @foreach($sante['facteurs'] as $facteur)
                        <li class="flex items-start gap-2 text-xs text-cyan-100/70">
                            <span class="mt-1 h-1.5 w-1.5 flex-shrink-0 rounded-full bg-cyan-400"></span>
                            <span>{{ $facteur }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>

        {{-- Action rapide : Changer l'état --}}
        <div class="rounded-2xl border border-cyan-400/15 bg-slate-900/35 p-5 shadow-sm space-y-4">
            <div>
                <h2 class="text-base font-semibold text-white">Changer l'état</h2>
                <p class="text-xs text-cyan-100/60">Mise à jour rapide avec journalisation automatique</p>
            </div>

            <form id="quick-etat-form" method="POST" action="{{ route($inventoryRoutePrefix . '.infrastructures.etat', $infrastructure) }}" onsubmit="confirmSubmitEtat(event)" class="space-y-3">
                @csrf
                @method('PATCH')

                <div>
                    <label for="action_etat" class="block text-xs font-medium text-cyan-100/80 mb-1">Nouvel état</label>
                    <select id="action_etat" name="etat" class="w-full rounded-lg border border-cyan-400/20 bg-slate-950/60 px-3 py-2 text-xs text-white outline-none transition focus:border-cyan-400/50 focus:ring-2 focus:ring-cyan-400/20">
                        @foreach($etats as $etatOption)
                            <option value="{{ $etatOption->value }}" @selected($infrastructure->etat === $etatOption)>
                                {{ $etatOption->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="action_commentaire" class="block text-xs font-medium text-cyan-100/80 mb-1">Motif / Commentaire</label>
                    <textarea id="action_commentaire" name="commentaire" rows="2" placeholder="Ex. Contrôle visuel réussi..." class="w-full rounded-lg border border-cyan-400/20 bg-slate-950/60 px-3 py-2 text-xs text-white placeholder-slate-500 outline-none transition focus:border-cyan-400/50 focus:ring-2 focus:ring-cyan-400/20"></textarea>
                </div>

                <button type="submit" class="w-full rounded-lg bg-cyan-600 px-3.5 py-2 text-xs font-semibold text-white hover:bg-cyan-500 transition shadow-sm">
                    Enregistrer l'état
                </button>
            </form>
        </div>
    </div>

    {{-- Caractéristiques détaillées --}}
    <div class="rounded-2xl border border-cyan-400/15 bg-slate-900/35 p-5 shadow-sm space-y-4">
        <h2 class="text-base font-semibold text-white">Fiche technique & maintenance</h2>
        <dl class="grid gap-x-8 gap-y-4 sm:grid-cols-2 lg:grid-cols-3 text-sm">
            <div>
                <dt class="text-xs font-medium text-cyan-100/60">Référence équipement</dt>
                <dd class="mt-1 font-semibold text-white">{{ $infrastructure->reference ?: 'Non renseignée' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium text-cyan-100/60">Fabricant</dt>
                <dd class="mt-1 font-semibold text-white">{{ $infrastructure->fabricant ?: 'Non renseigné' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium text-cyan-100/60">Capacité nominale</dt>
                <dd class="mt-1 font-semibold text-white">
                    @if($infrastructure->capacite)
                        {{ number_format((float) $infrastructure->capacite, 2, ',', ' ') }} {{ $infrastructure->unite_capacite }}
                    @else
                        Non renseignée
                    @endif
                </dd>
            </div>
            <div>
                <dt class="text-xs font-medium text-cyan-100/60">Localisation textuelle</dt>
                <dd class="mt-1 font-medium text-white">{{ $infrastructure->localisation }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium text-cyan-100/60">Date d’installation</dt>
                <dd class="mt-1 font-medium text-white">
                    {{ $infrastructure->date_installation->format('d/m/Y') }}
                    <span class="text-xs text-cyan-100/50">({{ $infrastructure->date_installation->age }} ans)</span>
                </dd>
            </div>
            <div>
                <dt class="text-xs font-medium text-cyan-100/60">Durée de vie estimée</dt>
                <dd class="mt-1 font-medium text-white">{{ $infrastructure->duree_vie_annees ? $infrastructure->duree_vie_annees.' ans' : 'Non définie' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium text-cyan-100/60">Dernière maintenance</dt>
                <dd class="mt-1 font-medium text-white">
                    {{ $infrastructure->date_derniere_maintenance ? $infrastructure->date_derniere_maintenance->format('d/m/Y') : 'Aucune maintenance consignée' }}
                </dd>
            </div>
            <div>
                <dt class="text-xs font-medium text-cyan-100/60">Prochaine maintenance</dt>
                <dd class="mt-1 font-medium">
                    @if($infrastructure->date_prochaine_maintenance)
                        <span class="{{ $infrastructure->date_prochaine_maintenance->isPast() ? 'text-rose-400 font-bold' : 'text-white' }}">
                            {{ $infrastructure->date_prochaine_maintenance->format('d/m/Y') }}
                            @if($infrastructure->date_prochaine_maintenance->isPast())
                                (en retard !)
                            @else
                                (dans {{ $infrastructure->date_prochaine_maintenance->diffForHumans(null, true) }})
                            @endif
                        </span>
                    @else
                        <span class="text-cyan-100/50">Non planifiée</span>
                    @endif
                </dd>
            </div>
            <div>
                <dt class="text-xs font-medium text-cyan-100/60">Coordonnées GPS</dt>
                <dd class="mt-1 font-mono text-xs text-cyan-300">
                    @if($infrastructure->latitude && $infrastructure->longitude)
                        {{ $infrastructure->latitude }}, {{ $infrastructure->longitude }}
                    @else
                        <span class="text-cyan-100/50 font-sans">Non renseignées</span>
                    @endif
                </dd>
            </div>
        </dl>
    </div>

    {{-- Mini-carte Leaflet si coordonnées disponibles --}}
    @if($infrastructure->latitude && $infrastructure->longitude)
        <div class="rounded-2xl border border-cyan-400/15 bg-slate-900/35 p-5 shadow-sm space-y-3">
            <div class="flex items-center justify-between">
                <h2 class="text-base font-semibold text-white">Localisation géographique</h2>
                <span class="text-xs text-cyan-300 font-mono">{{ $infrastructure->latitude }}, {{ $infrastructure->longitude }}</span>
            </div>
            <div id="infra-map-show" class="h-64 w-full rounded-xl border border-cyan-400/20 overflow-hidden shadow-inner bg-slate-950/60"></div>
        </div>

        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha384-sHL9NAb7lN7rfvG5lfHpm643Xkcjzp4jFvuavGOndn6pjVqS6ny56CAt3nsEVT4H" crossorigin="anonymous" />
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha384-cxOPjt7s7Iz04uaHJceBmS+qpjv2JkIHNVcuOrM+YHwZOmJGBXI00mdUXEq65HTH" crossorigin="anonymous"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const mapEl = document.getElementById('infra-map-show');
                if (mapEl && typeof L !== 'undefined') {
                    const lat = {{ (float) $infrastructure->latitude }};
                    const lng = {{ (float) $infrastructure->longitude }};
                    const map = L.map('infra-map-show', {
                        center: [lat, lng],
                        zoom: 13,
                        zoomControl: true,
                    });

                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; OpenStreetMap',
                        maxZoom: 19
                    }).addTo(map);

                    const marker = L.marker([lat, lng]).addTo(map);
                    marker.bindPopup(`
                        <div class="p-1 text-slate-900">
                            <strong>{{ addslashes($infrastructure->nom) }}</strong><br>
                            <span class="text-xs">{{ addslashes($infrastructure->type->label()) }}</span><br>
                            <span class="text-xs text-slate-600">{{ addslashes($infrastructure->localisation) }}</span>
                        </div>
                    `).openPopup();
                }
            });
        </script>
    @endif

    {{-- Timeline : Historique des changements d'état --}}
    <div class="rounded-2xl border border-cyan-400/15 bg-slate-900/35 p-5 shadow-sm space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-base font-semibold text-white">Historique des états</h2>
                <p class="text-xs text-cyan-100/60">Traçabilité complète des changements d'état de l'infrastructure</p>
            </div>
            <span class="rounded-full bg-cyan-500/10 px-2.5 py-1 text-xs font-medium text-cyan-300 ring-1 ring-cyan-400/20">
                {{ $infrastructure->historiques->count() }} {{ $infrastructure->historiques->count() > 1 ? 'transitions' : 'transition' }}
            </span>
        </div>

        @if($infrastructure->historiques->isEmpty())
            <p class="py-4 text-center text-sm text-cyan-100/50">Aucun historique enregistré pour cet équipement.</p>
        @else
            <div class="relative pl-6 space-y-6 before:absolute before:left-2.5 before:top-2 before:bottom-2 before:w-0.5 before:bg-cyan-400/20">
                @foreach($infrastructure->historiques as $hist)
                    @php
                        $colorPoint = match ($hist->nouvel_etat?->color()) {
                            'green' => 'bg-emerald-400 ring-emerald-400/30',
                            'amber' => 'bg-amber-400 ring-amber-400/30',
                            'rose' => 'bg-rose-400 ring-rose-400/30',
                            default => 'bg-cyan-400 ring-cyan-400/30',
                        };
                    @endphp
                    <div class="relative group">
                        {{-- Point sur la timeline --}}
                        <div class="absolute -left-6 top-1 h-3 w-3 rounded-full {{ $colorPoint }} ring-4 ring-slate-900"></div>

                        <div class="rounded-xl border border-white/5 bg-slate-950/40 p-4 transition group-hover:border-cyan-400/20">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <div class="flex items-center gap-2">
                                    @if($hist->ancien_etat)
                                        <span class="text-xs text-cyan-100/60">{{ $hist->ancien_etat->label() }}</span>
                                        <span class="text-cyan-400 font-bold text-xs">→</span>
                                        <x-infrastructure.etat-badge :etat="$hist->nouvel_etat" />
                                    @else
                                        <span class="text-xs font-semibold text-cyan-200">Création initiale :</span>
                                        <x-infrastructure.etat-badge :etat="$hist->nouvel_etat" />
                                    @endif
                                </div>
                                <div class="text-xs text-cyan-100/50">
                                    <span>{{ $hist->created_at->format('d/m/Y à H:i') }}</span>
                                    <span class="hidden sm:inline">({{ $hist->created_at->diffForHumans() }})</span>
                                </div>
                            </div>

                            <div class="mt-2 flex flex-wrap items-center justify-between gap-2 text-xs">
                                <p class="text-cyan-100/70">
                                    Modifié par <span class="font-medium text-white">{{ $hist->user_name ?? 'Système' }}</span>
                                </p>
                            </div>

                            @if($hist->commentaire)
                                <div class="mt-2 rounded-lg border border-cyan-400/10 bg-cyan-950/20 px-3 py-2 text-xs italic text-cyan-100/80">
                                    « {{ $hist->commentaire }} »
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Section : Interventions de maintenance & Coût global --}}
    <div class="rounded-2xl border border-cyan-400/15 bg-slate-900/35 p-5 shadow-sm space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-base font-semibold text-white">Interventions de maintenance</h2>
                <p class="text-xs text-cyan-100/60">Historique des travaux techniques réalisés ou programmés</p>
            </div>
            <div class="flex items-center gap-3">
                <div class="rounded-xl border border-emerald-400/20 bg-emerald-500/10 px-3 py-1.5 text-right">
                    <span class="block text-[10px] uppercase font-semibold text-emerald-300">Coût total maintenance</span>
                    <span class="text-sm font-bold text-white">{{ number_format($coutTotalMaintenance ?? 0, 2, ',', ' ') }} DT</span>
                </div>
                <a href="{{ route('manager.interventions.create', ['infrastructure_id' => $infrastructure->id]) }}" class="rounded-lg bg-cyan-600 px-3.5 py-1.5 text-xs font-semibold text-white hover:bg-cyan-500 transition shadow-sm">
                    + Nouvelle intervention
                </a>
            </div>
        </div>

        @if($infrastructure->interventions->isEmpty())
            <div class="rounded-xl border border-white/5 bg-slate-950/40 p-6 text-center text-sm text-cyan-100/50">
                Aucune intervention de maintenance enregistrée pour cet équipement.
            </div>
        @else
            <div class="overflow-x-auto rounded-xl border border-white/5 bg-slate-950/40">
                <table class="w-full text-left text-sm">
                    <thead class="bg-white/5 text-xs font-medium text-cyan-100/70">
                        <tr>
                            <th class="px-4 py-2.5">Date</th>
                            <th class="px-4 py-2.5">Technicien</th>
                            <th class="px-4 py-2.5">Description</th>
                            <th class="px-4 py-2.5">Statut</th>
                            <th class="px-4 py-2.5 text-right">Coût</th>
                            <th class="px-4 py-2.5 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5 text-xs">
                        @foreach($infrastructure->interventions as $intervention)
                            <tr class="hover:bg-white/5 transition text-cyan-100/80">
                                <td class="px-4 py-2.5 font-medium text-white whitespace-nowrap">{{ $intervention->date->format('d/m/Y') }}</td>
                                <td class="px-4 py-2.5 whitespace-nowrap">
                                    <a href="{{ route('manager.techniciens.show', $intervention->technicien) }}" class="text-cyan-300 hover:underline">
                                        {{ $intervention->technicien->nom }}
                                    </a>
                                </td>
                                <td class="px-4 py-2.5 max-w-xs truncate">{{ $intervention->description }}</td>
                                <td class="px-4 py-2.5"><x-maintenance.badge :value="$intervention->statut" /></td>
                                <td class="px-4 py-2.5 text-right font-medium text-white whitespace-nowrap">{{ number_format($intervention->cout, 2, ',', ' ') }} DT</td>
                                <td class="px-4 py-2.5 text-right whitespace-nowrap">
                                    <a href="{{ route('manager.interventions.show', $intervention) }}" class="text-cyan-300 hover:text-cyan-200 font-medium">Détails →</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- Section : Incidents ouverts sur cet équipement --}}
    <div class="rounded-2xl border border-cyan-400/15 bg-slate-900/35 p-5 shadow-sm space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-base font-semibold text-white">Incidents signalés</h2>
                <p class="text-xs text-cyan-100/60">Anomalies et dysfonctionnements signalés sur cet équipement</p>
            </div>
            @if(isset($infrastructure->incidents) && $infrastructure->incidents->isNotEmpty())
                <span class="rounded-full bg-rose-500/10 px-2.5 py-1 text-xs font-semibold text-rose-300 ring-1 ring-rose-400/20">
                    {{ $infrastructure->incidents->count() }} {{ $infrastructure->incidents->count() > 1 ? 'incidents actifs' : 'incident actif' }}
                </span>
            @endif
        </div>

        @if(!isset($infrastructure->incidents) || $infrastructure->incidents->isEmpty())
            <div class="rounded-xl border border-white/5 bg-slate-950/40 p-6 text-center text-sm text-cyan-100/50">
                Aucun incident actif signalé sur cet équipement.
            </div>
        @else
            <div class="overflow-x-auto rounded-xl border border-white/5 bg-slate-950/40">
                <table class="w-full text-left text-sm">
                    <thead class="bg-white/5 text-xs font-medium text-cyan-100/70">
                        <tr>
                            <th class="px-4 py-2.5">Date</th>
                            <th class="px-4 py-2.5">Titre</th>
                            <th class="px-4 py-2.5">Type</th>
                            <th class="px-4 py-2.5">Gravité</th>
                            <th class="px-4 py-2.5">Statut</th>
                            <th class="px-4 py-2.5 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5 text-xs">
                        @foreach($infrastructure->incidents as $incident)
                            <tr class="hover:bg-white/5 transition text-cyan-100/80">
                                <td class="px-4 py-2.5 font-medium text-white whitespace-nowrap">{{ $incident->date_signalement?->format('d/m/Y H:i') ?? '—' }}</td>
                                <td class="px-4 py-2.5 text-white font-semibold">{{ $incident->titre }}</td>
                                <td class="px-4 py-2.5 capitalize">{{ $incident->type }}</td>
                                <td class="px-4 py-2.5">
                                    <span class="rounded px-2 py-0.5 font-semibold uppercase text-[10px] {{ $incident->gravite === 'critique' || $incident->gravite === 'elevee' ? 'bg-rose-500/20 text-rose-300' : 'bg-amber-500/20 text-amber-300' }}">
                                        {{ $incident->gravite }}
                                    </span>
                                </td>
                                <td class="px-4 py-2.5">
                                    <span class="rounded-full bg-cyan-500/15 px-2 py-0.5 text-[11px] font-medium text-cyan-200">
                                        {{ $incident->statut }}
                                    </span>
                                </td>
                                <td class="px-4 py-2.5 text-right whitespace-nowrap">
                                    <a href="{{ $inventoryRoutePrefix === 'admin' ? route('admin.incidents', ['q' => $incident->titre]) : route('manager.incidents.show', $incident) }}" class="font-medium text-cyan-300 hover:text-cyan-200">Consulter →</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
    function confirmDeleteInfraShow() {
        const infraNom = '{{ addslashes($infrastructure->nom) }}';
        const infraRef = @if($infrastructure->reference) '{{ addslashes($infrastructure->reference) }}' @else '' @endif;
        const badgeText = infraRef ? infraNom + ' [' + infraRef + ']' : infraNom;
        const impactHtml = '<div class="flex items-start gap-2 mb-1"><span class="flex-shrink-0 mt-0.5 h-2 w-2 rounded-full bg-emerald-400"></span><span class="text-emerald-200/90 font-medium">Archivage logique (Soft Delete)</span></div><div class="flex items-start gap-2"><span class="flex-shrink-0 mt-0.5 h-2 w-2 rounded-full bg-cyan-400"></span><span class="text-cyan-200/90">Historique d\'audit, interventions et mesures de qualité conservés.</span></div>';
        const hint = 'L\'infrastructure sera archivée et retirée de la supervision active. Aucune donnée n\'est définitivement effacée.';

        if (typeof window.openAquaConfirm === 'function') {
            window.openAquaConfirm({
                variant: 'danger',
                title: 'Supprimer l\'infrastructure',
                message: 'Êtes-vous certain de vouloir supprimer « ' + infraNom + ' » du réseau ?',
                badge: badgeText,
                impact: impactHtml,
                hint: hint,
                confirmText: 'Supprimer l\'équipement',
                cancelText: 'Annuler',
                footerLeft: 'Archivage logique',
                onConfirm: function () {
                    const form = document.getElementById('delete-infra-form-show');
                    if (form) form.submit();
                }
            });
        } else if (confirm('Êtes-vous certain de vouloir supprimer « ' + infraNom + ' » du réseau ?')) {
            const form = document.getElementById('delete-infra-form-show');
            if (form) form.submit();
        }
    }

    function confirmSubmitEtat(e) {
        e.preventDefault();
        const select = document.getElementById('action_etat');
        const newState = select.value;
        const currentState = "{{ $infrastructure->etat->value }}";
        const infraNom = '{{ addslashes($infrastructure->nom) }}';
        const currentLabel = "{{ $infrastructure->etat->label() }}";
        const newLabel = select.options[select.selectedIndex].text.trim();

        if (newState === currentState) {
            window.openAquaConfirm({
                variant: 'info',
                title: 'État inchangé',
                message: 'L\'équipement se trouve déjà à l\'état « ' + currentState + ' ». Choisissez un nouvel état si vous souhaitez modifier son statut.',
                badge: infraNom,
                confirmText: 'Compris',
                cancelText: 'Fermer',
                onConfirm: function () {}
            });
            return;
        }

        let variant = 'warning';
        const lowerNew = newState.toLowerCase();
        if (lowerNew === 'enservice' || lowerNew === 'en_service') variant = 'success';
        else if (lowerNew === 'horsservice' || lowerNew === 'hors_service' || lowerNew === 'à_l\'arret' || lowerNew === 'à_larret') variant = 'danger';

        window.openAquaConfirm({
            variant: variant,
            title: 'Confirmer le changement d\'état',
            message: 'Souhaitez-vous modifier l\'état opérationnel de cette infrastructure ?',
            badge: infraNom + ' : « ' + currentLabel + ' » → « ' + newLabel + ' »',
            impact: '<div class="flex items-start gap-2"><span class="flex-shrink-0 mt-0.5 h-2 w-2 rounded-full bg-cyan-400"></span><span class="text-cyan-200/90">La transition sera enregistrée avec votre compte dans l\'historique d\'audit.</span></div>',
            hint: 'Cette mise à jour mettra à jour le statut opérationnel et pourra déclencher une réévaluation du score de santé.',
            confirmText: 'Enregistrer « ' + newLabel + ' »',
            cancelText: 'Annuler',
            secondaryText: 'Garder « ' + currentLabel + ' »',
            onSecondary: function () {
                select.value = currentState;
            },
            onConfirm: function () {
                document.getElementById('quick-etat-form').submit();
            }
        });
    }

    function confirmResoudreAlerte(id, message) {
        window.openAquaConfirm({
            variant: 'success',
            title: 'Résoudre l\'alerte technique',
            message: 'Confirmez-vous la résolution de cette alerte opérationnelle ?',
            badge: message,
            impact: '<div class="flex items-start gap-2 mb-1"><span class="flex-shrink-0 mt-0.5 h-2 w-2 rounded-full bg-emerald-400"></span><span class="text-emerald-200/90 font-medium">Alerte archivée</span></div><div class="flex items-start gap-2"><span class="flex-shrink-0 mt-0.5 h-2 w-2 rounded-full bg-cyan-400"></span><span class="text-cyan-200/90">Le score d\'état de santé de l\'ouvrage sera réévalué positivement.</span></div>',
            hint: 'L\'alerte sera marquée comme résolue et disparaîtra de la liste des alertes actives.',
            confirmText: 'Marquer comme résolue',
            cancelText: 'Annuler',
            footerLeft: 'Action réversible',
            onConfirm: function () {
                document.getElementById('resoudre-alerte-form-' + id).submit();
            }
        });
    }
</script>
@endpush
    @unless(request()->routeIs('admin.*'))
        <x-infrastructure.risk-card
            :risk="$risk"
            :history="$riskHistory"
            :infrastructure="$infrastructure"
            route-prefix="manager"
        />
    @endunless
@endsection
