@extends('layouts.manager')

@section('title', 'AquaSecure — Qualité de l’eau et mesures')

@php
    $user = session('user', ['name' => 'Dr. Selim Dridi', 'role' => 'manager']);
@endphp

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
#quality-map { height: 320px; border-radius: 1rem; }
.leaflet-tile-pane { filter: brightness(.98) contrast(1.02) saturate(.9); }
</style>
@include('manager.shared.light-shell')
@endpush

@section('manager-content')
<div class="aqua-manager-standalone w-full space-y-6">
        
        {{-- Section Header --}}
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-display font-bold text-white tracking-tight">Qualité de l’eau : mesures et prélèvements</h1>
                <p class="text-slate-400 text-xs mt-1">Analyses de laboratoire, seuils de contrôle et alertes de contamination.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <button onclick="openThresholdModal()" class="px-3.5 py-2 rounded-xl bg-teal-500/10 border border-teal-500/30 text-teal-300 hover:bg-teal-500/20 text-xs font-semibold flex items-center gap-2">
                    <i data-lucide="sliders" class="w-3.5 h-3.5"></i> Régler les seuils
                </button>
                <a href="{{ route('manager.points-mesure.index') }}" class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 text-xs font-semibold">Points de prélèvement</a>
                @can('create', App\Models\MesureQualite::class)
                    @if($activePointsCount > 0)
                        <a href="{{ route('manager.quality.mesures.create') }}" class="px-3.5 py-2 rounded-xl bg-teal-500 hover:bg-teal-400 text-slate-950 text-xs font-bold">Nouvelle mesure</a>
                    @else
                        <a href="{{ route('manager.points-mesure.create') }}" class="px-3.5 py-2 rounded-xl bg-teal-500 hover:bg-teal-400 text-slate-950 text-xs font-bold">Créer un point</a>
                    @endif
                @endcan
                <button onclick="showToast('Exporting Water Quality Compliance Log (CSV)...', 'success')" class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 text-xs font-semibold flex items-center gap-2">
                    <i data-lucide="file-spreadsheet" class="w-4 h-4 text-teal-400"></i> Exporter le rapport CSV
                </button>
            </div>
        </div>

        {{-- 4 PARAMETER CARDS (pH, Turbidity, Chlorine, Lead) --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            
            {{-- 1. pH Parameter --}}
            <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-4 relative">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs text-slate-400 font-bold uppercase">pH Level</span>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/15 text-emerald-300 border border-emerald-500/30">Target 6.5 - 8.5</span>
                </div>
                <div class="text-2xl font-display font-bold text-white">{{ $latestMeasurement?->ph ?? '—' }} <span class="text-xs text-slate-400 font-normal">pH</span></div>
                <div class="w-full bg-slate-800 h-2 rounded-full mt-3 overflow-hidden">
                    <div class="bg-emerald-400 h-full rounded-full" style="width: {{ $latestMeasurement ? min(((float) $latestMeasurement->ph / 14) * 100, 100) : 0 }}%"></div>
                </div>
                <p class="text-[11px] text-slate-400 mt-2 flex items-center justify-between">
                    <span>Dernière mesure</span>
                    <span class="text-emerald-400 font-semibold">{{ $latestAssessment['parameter_statuses']['ph'] ?? 'Aucune donnée' }}</span>
                </p>
            </div>

            {{-- 2. Turbidity NTU --}}
            <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-4 relative">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs text-slate-400 font-bold uppercase">Turbidity</span>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-cyan-500/15 text-cyan-300 border border-cyan-500/30">Limit &lt; 1.0 NTU</span>
                </div>
                <div class="text-2xl font-display font-bold text-white">{{ $latestMeasurement?->turbidite ?? '—' }} <span class="text-xs text-slate-400 font-normal">NTU</span></div>
                <div class="w-full bg-slate-800 h-2 rounded-full mt-3 overflow-hidden">
                    <div class="bg-cyan-400 h-full rounded-full" style="width: {{ $latestMeasurement ? min(((float) $latestMeasurement->turbidite / 2) * 100, 100) : 0 }}%"></div>
                </div>
                <p class="text-[11px] text-slate-400 mt-2 flex items-center justify-between">
                    <span>Dernière mesure</span>
                    <span class="text-cyan-400 font-semibold">{{ $latestAssessment['parameter_statuses']['turbidite'] ?? 'Aucune donnée' }}</span>
                </p>
            </div>

            {{-- 3. Chlore résiduel --}}
            <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-4 relative">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs text-slate-400 font-bold uppercase">Chlore résiduel</span>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-500/15 text-blue-300 border border-blue-500/30">0.2 - 2.0 mg/L</span>
                </div>
                <div class="text-2xl font-display font-bold text-white">{{ $latestMeasurement?->chlore_residuel ?? '—' }} <span class="text-xs text-slate-400 font-normal">mg/L</span></div>
                <div class="w-full bg-slate-800 h-2 rounded-full mt-3 overflow-hidden">
                    <div class="bg-blue-400 h-full rounded-full" style="width: {{ $latestMeasurement ? min(((float) $latestMeasurement->chlore_residuel / 2) * 100, 100) : 0 }}%"></div>
                </div>
                <p class="text-[11px] text-slate-400 mt-2 flex items-center justify-between">
                    <span>Dernière mesure</span>
                    <span class="text-blue-400 font-semibold">{{ $latestAssessment['parameter_statuses']['chlore_residuel'] ?? 'Aucune donnée' }}</span>
                </p>
            </div>

            {{-- 4. Lead (Pb) Level --}}
            <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-4 relative">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs text-slate-400 font-bold uppercase">Lead (Pb) Concentration</span>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-500/15 text-amber-300 border border-amber-500/30">Limit &lt; 15 ppb</span>
                </div>
                <div class="text-2xl font-display font-bold text-white">{{ $latestMeasurement?->plomb ?? '—' }} <span class="text-xs text-slate-400 font-normal">ppb</span></div>
                <div class="w-full bg-slate-800 h-2 rounded-full mt-3 overflow-hidden">
                    <div class="bg-emerald-400 h-full rounded-full" style="width: {{ $latestMeasurement ? min(((float) $latestMeasurement->plomb / 15) * 100, 100) : 0 }}%"></div>
                </div>
                <p class="text-[11px] text-slate-400 mt-2 flex items-center justify-between">
                    <span>Dernière mesure</span>
                    <span class="text-emerald-400 font-semibold">{{ $latestAssessment['parameter_statuses']['plomb'] ?? 'Aucune donnée' }}</span>
                </p>
            </div>

            <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-4 relative">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs text-slate-400 font-bold uppercase">Nitrates</span>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-700/60 text-slate-300 border border-slate-600">Seuil configurable</span>
                </div>
                <div class="text-2xl font-display font-bold text-white">{{ $latestMeasurement?->nitrates ?? '—' }} <span class="text-xs text-slate-400 font-normal">mg/L</span></div>
                <div class="w-full bg-slate-800 h-2 rounded-full mt-3 overflow-hidden">
                    <div class="bg-violet-400 h-full rounded-full" style="width: {{ $latestMeasurement ? min(((float) $latestMeasurement->nitrates / 100) * 100, 100) : 0 }}%"></div>
                </div>
                <p class="text-[11px] text-slate-400 mt-2 flex items-center justify-between">
                    <span>{{ config('water_quality.nitrates_max') !== null ? 'Maximum '.config('water_quality.nitrates_max').' mg/L' : 'Aucune limite configurée' }}</span>
                    <span class="text-violet-300 font-semibold">{{ $latestAssessment['parameter_statuses']['nitrates'] ?? ($latestMeasurement ? 'Non évalué' : 'Aucune donnée') }}</span>
                </p>
            </div>
        </div>

        {{-- Sampling Map & Trend Charts --}}
        <div class="grid lg:grid-cols-2 gap-6">
            
            {{-- Leaflet Map Sampling Stations --}}
            <div class="bg-slate-900/90 border border-slate-800 rounded-2xl overflow-hidden p-4">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-bold text-white flex items-center gap-2">
                        <i data-lucide="map-pin" class="w-3.5 h-3.5 text-teal-400"></i> Carte des stations de prélèvement
                    </h3>
                    <span class="text-xs text-slate-400">{{ $activePointsCount }} points de prélèvement actifs</span>
                </div>
                <div id="quality-map"></div>
            </div>

            {{-- Chart: 30-Day Quality Trends --}}
            <div class="bg-slate-900/90 border border-slate-800 rounded-2xl overflow-hidden p-4">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-bold text-white flex items-center gap-2">
                        <i data-lucide="line-chart" class="w-3.5 h-3.5 text-teal-400"></i> Conformité de la qualité de l’eau sur 30 jours
                    </h3>
                    <span class="text-xs text-emerald-400 font-semibold bg-emerald-500/10 px-2 py-0.5 rounded">{{ $averageCompliance !== null ? $averageCompliance.'% moyenne' : 'Aucune mesure' }}</span>
                </div>
                <div style="height: 240px;">
                    <canvas id="qualityTrendChart"></canvas>
                </div>
            </div>
        </div>

        {{-- Water Sampling Records Table --}}
        <div class="bg-slate-900/90 border border-slate-800 rounded-2xl overflow-hidden shadow-xl">
            <div class="p-4 border-b border-slate-800 flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h3 class="text-sm font-bold text-white flex items-center gap-2">
                        <i data-lucide="clipboard-list" class="w-3.5 h-3.5 text-teal-400"></i> Résultats d'analyses et suivi de la qualité
                    </h3>
                    <p class="text-xs text-slate-400">Consultez les résultats vérifiés par laboratoire et les relevés en attente de validation.</p>
                </div>
                <div class="text-xs text-slate-400 font-mono">
                    {{ $paginator->total() }} mesure(s) au total
                </div>
            </div>

            {{-- Search & Filters Form --}}
            <div class="p-4 bg-slate-950/60 border-b border-slate-800">
                <form method="GET" action="{{ route('manager.quality') }}" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-6 items-end">
                    {{-- Keyword --}}
                    <div class="lg:col-span-2">
                        <label class="block text-[11px] font-semibold text-slate-400 mb-1">Recherche (Réf., point, zone)</label>
                        <div class="relative">
                            <i data-lucide="search" class="w-3.5 h-3.5 absolute left-3 top-1/2 -translate-y-1/2 text-slate-500"></i>
                            <input 
                                type="text" 
                                name="search" 
                                value="{{ request('search') }}" 
                                placeholder="Ex: WQR-2026 ou Belvédère" 
                                class="w-full rounded-xl border border-slate-700 bg-slate-950 pl-8 pr-3 py-1.5 text-xs text-white placeholder-slate-500 focus:border-teal-400 focus:outline-none"
                            >
                        </div>
                    </div>

                    {{-- Point de mesure --}}
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-400 mb-1">Point de prélèvement</label>
                        <select name="point_mesure_id" class="w-full rounded-xl border border-slate-700 bg-slate-950 px-2.5 py-1.5 text-xs text-white focus:border-teal-400 focus:outline-none">
                            <option value="">Tous les points</option>
                            @foreach($allPointsForFilter ?? [] as $pt)
                                <option value="{{ $pt->id }}" @selected((string) request('point_mesure_id') === (string) $pt->id)>{{ $pt->nom }} ({{ $pt->code }})</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Statut conformité --}}
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-400 mb-1">Conformité</label>
                        <select name="status" class="w-full rounded-xl border border-slate-700 bg-slate-950 px-2.5 py-1.5 text-xs text-white focus:border-teal-400 focus:outline-none">
                            <option value="">Tous les états</option>
                            <option value="Compliant" @selected(request('status') === 'Compliant')>Conforme (Compliant)</option>
                            <option value="Alert" @selected(request('status') === 'Alert')>Alerte (Alert)</option>
                            <option value="Non-Compliant" @selected(request('status') === 'Non-Compliant')>Non conforme</option>
                        </select>
                    </div>

                    {{-- Provenance --}}
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-400 mb-1">Provenance</label>
                        <select name="source_donnee" class="w-full rounded-xl border border-slate-700 bg-slate-950 px-2.5 py-1.5 text-xs text-white focus:border-teal-400 focus:outline-none">
                            <option value="">Toutes sources</option>
                            <option value="manuel" @selected(request('source_donnee') === 'manuel')>Manuel (terrain)</option>
                            <option value="laboratoire" @selected(request('source_donnee') === 'laboratoire')>Laboratoire</option>
                            <option value="simule" @selected(request('source_donnee') === 'simule')>Simulé</option>
                        </select>
                    </div>

                    {{-- Actions --}}
                    <div class="flex items-center gap-2">
                        <button type="submit" class="flex-1 rounded-xl bg-teal-500/20 hover:bg-teal-500/30 text-teal-300 border border-teal-500/40 px-3 py-1.5 text-xs font-semibold flex items-center justify-center gap-1.5 transition-colors">
                            <i data-lucide="filter" class="w-3.5 h-3.5"></i> Filtrer
                        </button>
                        @if(request()->hasAny(['search', 'point_mesure_id', 'status', 'source_donnee', 'is_verified', 'date_from', 'date_to']))
                            <a href="{{ route('manager.quality') }}" class="rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white px-2.5 py-1.5 text-xs border border-slate-700" title="Réinitialiser les filtres">
                                <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i> Réinitialiser
                            </a>
                        @endif
                    </div>
                </form>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-300">
                    <thead class="bg-slate-950/80 text-slate-400 uppercase font-bold text-[10px] tracking-wider border-b border-slate-800">
                        <tr>
                            <th class="px-4 py-3">Sample ID &amp; Point</th>
                            <th class="px-4 py-3">Zone</th>
                            <th class="px-4 py-3">Date</th>
                            <th class="px-4 py-3">pH</th>
                            <th class="px-4 py-3">Turbidity</th>
                            <th class="px-4 py-3">Chlorine</th>
                            <th class="px-4 py-3">Lead (Pb)</th>
                            <th class="px-4 py-3">Nitrates</th>
                            <th class="px-4 py-3">Verification</th>
                            <th class="px-4 py-3">Provenance</th>
                            <th class="px-4 py-3 text-right">Compliance</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60">
                        @forelse($records as $rec)
                            <tr class="hover:bg-slate-800/40 transition-colors">
                                <td class="px-4 py-3 font-semibold text-white">
                                    <a href="{{ route('manager.quality.mesures.show', $rec['measurement_id']) }}" class="font-mono text-cyan-400 text-[11px] hover:underline">{{ $rec['id'] }}</a>
                                    <div><a href="{{ route('manager.points-mesure.index') }}">{{ $rec['sampling_point'] }}</a></div>
                                </td>
                                <td class="px-4 py-3 text-slate-200">{{ $rec['zone'] }}</td>
                                <td class="px-4 py-3 text-slate-400">{{ $rec['date'] }}</td>
                                <td class="px-4 py-3 font-mono {{ $rec['pH'] > 8.5 ? 'text-red-400 font-bold' : 'text-slate-200' }}">{{ $rec['pH'] }}</td>
                                <td class="px-4 py-3 font-mono {{ $rec['turbidity'] > 1.0 ? 'text-amber-400 font-bold' : 'text-slate-200' }}">{{ $rec['turbidity'] }} NTU</td>
                                <td class="px-4 py-3 font-mono {{ $rec['residual_chlorine'] < 0.2 ? 'text-red-400 font-bold' : 'text-slate-200' }}">{{ $rec['residual_chlorine'] }} mg/L</td>
                                <td class="px-4 py-3 font-mono {{ $rec['lead_pb'] > 15 ? 'text-red-400 font-bold' : 'text-slate-200' }}">{{ $rec['lead_pb'] }} ppb</td>
                                <td class="px-4 py-3 text-slate-200">{{ $rec['nitrates'] }} mg/L</td>
                                <td class="px-4 py-3">
                                    @if($rec['is_verified'])
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-teal-500/15 text-teal-300 border border-teal-500/30 inline-flex items-center gap-2">
                                            <i data-lucide="check-circle-2" class="w-3 h-3 text-teal-400"></i> Vérifié en laboratoire
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/15 text-amber-300 border border-amber-500/30 inline-flex items-center gap-2">
                                            <i data-lucide="clipboard-check" class="w-3 h-3 text-amber-400"></i> Relevé à vérifier
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    @php $src = $rec['source_donnee'] ?? 'manuel'; @endphp
                                    @if($src === 'laboratoire')
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-500/15 text-blue-300 border border-blue-500/30">Laboratoire</span>
                                    @elseif($src === 'simule')
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-500/15 text-slate-400 border border-slate-600">Simulé</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-violet-500/15 text-violet-300 border border-violet-600">Manuel</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right font-bold {{ $rec['status'] === 'Compliant' ? 'text-emerald-400' : ($rec['status'] === 'Alert' ? 'text-amber-400' : 'text-red-400') }}">
                                    {{ $rec['status'] }} ({{ $rec['overall_compliance'] }}%)
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="11" class="px-4 py-10 text-center text-slate-400">Aucune mesure enregistrée. Créez un point de prélèvement puis ajoutez une mesure.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($paginator->hasPages())
                <div class="px-4 py-3 border-t border-slate-800">
                    {{ $paginator->links('pagination::tailwind') }}
                </div>
            @endif
        </div>

    </div>
</div>

{{-- Threshold Information Modal --}}
<div id="threshold-modal" class="hidden fixed inset-0 z-50 bg-black/70 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-slate-900 border border-slate-700 rounded-2xl max-w-md w-full p-6 space-y-4 shadow-2xl">
        <div class="flex items-center justify-between">
            <h3 class="text-base font-bold text-white flex items-center gap-2">
                <i data-lucide="sliders" class="w-5 h-5 text-teal-400"></i> Seuils appliqués
            </h3>
            <button onclick="closeThresholdModal()" class="text-slate-400 hover:text-white">
                <i data-lucide="x" class="w-3.5 h-3.5"></i>
            </button>
        </div>
        <p class="text-xs text-slate-300 bg-slate-800/60 border border-slate-600 rounded-xl px-3 py-2 italic">
            Seuils configurés pour ce projet — non certifiés comme normes officielles.
        </p>
        <p class="text-xs text-amber-300 bg-amber-500/10 border border-amber-500/30 rounded-xl px-3 py-2">
            Ces seuils sont configurés dans <code>config/water_quality.php</code>.
            Pour les modifier, mettez à jour les variables <code>WATER_QUALITY_*</code> dans .env et redémarrez l'application.
        </p>
        <div class="space-y-2 text-xs text-slate-200">
            <div class="flex justify-between border-b border-slate-800 pb-2">
                <span class="text-slate-400">pH admissible</span>
                <span class="font-mono font-bold">{{ config('water_quality.ph_min') }} &ndash; {{ config('water_quality.ph_max') }}</span>
            </div>
            <div class="flex justify-between border-b border-slate-800 pb-2">
                <span class="text-slate-400">Turbidite alerte (NTU)</span>
                <span class="font-mono font-bold">&gt; {{ config('water_quality.turbidite_alert') }}</span>
            </div>
            <div class="flex justify-between border-b border-slate-800 pb-2">
                <span class="text-slate-400">Turbidite non conforme (NTU)</span>
                <span class="font-mono font-bold">&gt; {{ config('water_quality.turbidite_max') }}</span>
            </div>
            <div class="flex justify-between border-b border-slate-800 pb-2">
                <span class="text-slate-400">Chlore residuel (mg/L)</span>
                <span class="font-mono font-bold">{{ config('water_quality.chlore_min') }} &ndash; {{ config('water_quality.chlore_max') }}</span>
            </div>
            <div class="flex justify-between border-b border-slate-800 pb-2">
                <span class="text-slate-400">Plomb seuil d'action (ppb)</span>
                <span class="font-mono font-bold">&le; {{ config('water_quality.plomb_max') }}</span>
            </div>
            <div class="flex justify-between pb-2">
                <span class="text-slate-400">Nitrates (mg/L)</span>
                <span class="font-mono font-bold">
                    @if(config('water_quality.nitrates_max') !== null)
                        &le; {{ config('water_quality.nitrates_max') }}
                    @else
                        <span class="text-slate-500 italic">Non configure</span>
                    @endif
                </span>
            </div>
        </div>
        <div class="flex items-center justify-end pt-2">
            <button onclick="closeThresholdModal()" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 text-xs font-semibold">Fermer</button>
        </div>
    </div>
</div>@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
const POINTS = @json($points);

function openThresholdModal() { document.getElementById('threshold-modal').classList.remove('hidden'); }
function closeThresholdModal() { document.getElementById('threshold-modal').classList.add('hidden'); }

(function() {
    const map = L.map('quality-map', { center: [36.2, 10.3], zoom: 7, zoomControl: false, attributionControl: false });
    L.control.zoom({ position: 'topright' }).addTo(map);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 18 }).addTo(map);

    POINTS.forEach(point => {
        const r = point.measurement;
        const c = point.status === 'normal' ? '#2dd4bf' : (point.status === 'alert' ? '#fbbf24' : '#ef4444');
        const svg = `<svg xmlns="http://www.w3.org/2000/svg" width="30" height="30">
            <circle cx="15" cy="15" r="12" fill="${c}" fill-opacity="0.8" stroke="#ffffff" stroke-width="2"/>
        </svg>`;
        const details = r
            ? `Réf. ${escapeHtml(r.reference)}<br>Conformité: ${point.overall_compliance}%<br>pH: ${r.ph} | Turbidité: ${r.turbidite} NTU<br>Chlore: ${r.chlore_residuel} mg/L | Plomb: ${r.plomb} ppb | Nitrates: ${r.nitrates} mg/L`
            : 'Aucune mesure';
        L.marker([point.lat, point.lng], {
            icon: L.divIcon({ html: svg, iconSize:[30,30], iconAnchor:[15,15], className:'' })
        }).addTo(map).bindPopup(`<b>${escapeHtml(point.name)}</b><br>${details}`);
    });
})();

function escapeHtml(value) {
    return String(value ?? '').replace(/[&<>"']/g, char => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    })[char]);
}

(function() {
    const ctx = document.getElementById('qualityTrendChart').getContext('2d');
    const chartRecords = @json($chartRecords);
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: chartRecords.map(record => record.date),
            datasets: [
                { label: 'Taux de conformité (%)', data: chartRecords.map(record => record.overall_compliance), borderColor: '#2dd4bf', backgroundColor: 'rgba(45, 212, 191, 0.1)', fill: true, tension: 0.3 },
                { label: 'pH', data: chartRecords.map(record => record.pH), borderColor: '#3b82f6', tension: 0.3 }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { labels: { color: '#94a3b8', font: { size: 11 } } } },
            scales: {
                x: { ticks: { color: '#64748b' }, grid: { color: 'rgba(255,255,255,0.05)' } },
                y: { ticks: { color: '#64748b' }, grid: { color: 'rgba(255,255,255,0.05)' } }
            }
        }
    });
})();
</script>
@endpush
