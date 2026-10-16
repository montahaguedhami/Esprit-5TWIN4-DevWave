@extends('layouts.manager')

@section('title', 'AquaSecure — Qualité de l\'eau')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@include('manager.shared.light-shell')
<style>
#quality-map { height: 300px; border-radius: .75rem; }
.leaflet-tile-pane { filter: brightness(.98) contrast(1.02) saturate(.9); }
.leaflet-control-zoom a {
    background: #fff !important;
    border-color: #dce6ef !important;
    color: #087e9b !important;
}
.leaflet-popup-content-wrapper {
    background: #fff !important;
    border: 1px solid #dce6ef !important;
    border-radius: .75rem !important;
    color: #152b42 !important;
    box-shadow: 0 8px 24px rgba(16,42,67,.10) !important;
}
.leaflet-popup-tip { background: #fff !important; }

/* ── Stat cards ── */
.aq-stat-card {
    background: #fff;
    border: 1px solid #dce6ef;
    border-radius: 1rem;
    padding: 1.25rem 1.5rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    box-shadow: 0 2px 12px rgba(16,42,67,.05);
    transition: box-shadow .15s;
}
.aq-stat-card:hover { box-shadow: 0 6px 20px rgba(16,42,67,.09); }
.aq-stat-icon {
    width: 3rem;
    height: 3rem;
    border-radius: .875rem;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.aq-stat-value { font-size: 1.5rem; font-weight: 700; color: #152b42; line-height: 1.1; }
.aq-stat-label { font-size: .7rem; color: #52677d; text-transform: uppercase; letter-spacing: .05em; font-weight: 600; margin-top: .125rem; }
.aq-stat-sub { font-size: .75rem; color: #52677d; margin-top: .25rem; }

/* ── Parameter card ── */
.aq-param-card {
    background: #fff;
    border: 1px solid #dce6ef;
    border-radius: 1rem;
    padding: 1rem 1.25rem;
    box-shadow: 0 2px 10px rgba(16,42,67,.04);
}
.aq-param-card .param-name { font-size: .65rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: #52677d; }
.aq-param-card .param-value { font-size: 1.35rem; font-weight: 800; color: #152b42; margin: .3rem 0 .15rem; }
.aq-param-card .param-unit { font-size: .7rem; font-weight: 400; color: #718397; }
.aq-progress { height: 6px; background: #e8eef4; border-radius: 99px; overflow: hidden; margin-top: .6rem; }
.aq-progress-bar { height: 100%; border-radius: 99px; transition: width .4s ease; }
.aq-param-badge { font-size: .6rem; font-weight: 700; border-radius: .375rem; padding: .15rem .5rem; }

/* ── Section card ── */
.aq-card {
    background: #fff;
    border: 1px solid #dce6ef;
    border-radius: 1rem;
    box-shadow: 0 2px 12px rgba(16,42,67,.04);
    overflow: hidden;
}
.aq-card-header {
    padding: 1rem 1.5rem;
    border-bottom: 1px solid #dce6ef;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .75rem;
    flex-wrap: wrap;
    background: #f8fbfd;
}
.aq-card-title { font-size: .875rem; font-weight: 700; color: #152b42; display: flex; align-items: center; gap: .5rem; }

/* ── Banner gradient (maquette top banner) ── */
.aq-banner {
    background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 40%, #dbeafe 100%);
    border: 1px solid #bae6fd;
    border-radius: 1rem;
    padding: 1.25rem 1.5rem;
    display: flex;
    align-items: center;
    gap: 1rem;
}

/* ── Table ── */
.aq-table { width: 100%; border-collapse: separate; border-spacing: 0; font-size: .75rem; color: #152b42; }
.aq-table thead th { background: #f3f7fb; padding: .625rem 1rem; text-align: left; font-size: .65rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: #52677d; border-bottom: 1px solid #dce6ef; white-space: nowrap; }
.aq-table tbody td { padding: .75rem 1rem; border-bottom: 1px solid #f0f4f8; vertical-align: middle; }
.aq-table tbody tr:last-child td { border-bottom: none; }
.aq-table tbody tr:hover td { background: #f4f9fb; }

/* ── Status badges ── */
.badge { display: inline-flex; align-items: center; gap: .3rem; font-size: .625rem; font-weight: 700; border-radius: 99px; padding: .2rem .6rem; white-space: nowrap; border-width: 1px; border-style: solid; }
.badge-green  { background: #dcfce7; color: #166534; border-color: #bbf7d0; }
.badge-amber  { background: #fef9c3; color: #854d0e; border-color: #fde68a; }
.badge-red    { background: #fee2e2; color: #991b1b; border-color: #fecaca; }
.badge-blue   { background: #dbeafe; color: #1e40af; border-color: #bfdbfe; }
.badge-slate  { background: #f1f5f9; color: #475569; border-color: #e2e8f0; }
.badge-violet { background: #ede9fe; color: #5b21b6; border-color: #ddd6fe; }

/* ── Chart.js in light mode ── */
.aq-chart-wrap { position: relative; height: 240px; }

/* ── Filter bar ── */
.aq-filter-bar { padding: 1rem 1.5rem; background: #f8fbfd; border-bottom: 1px solid #dce6ef; }
.aq-filter-bar label { display: block; font-size: .65rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: #52677d; margin-bottom: .3rem; }
.aq-filter-bar input,
.aq-filter-bar select {
    width: 100%;
    border: 1px solid #cbd8e4;
    border-radius: .625rem;
    padding: .375rem .75rem;
    font-size: .75rem;
    color: #152b42;
    background: #fff;
    outline: none;
    transition: border-color .15s;
}
.aq-filter-bar input:focus,
.aq-filter-bar select:focus { border-color: #0891b2; box-shadow: 0 0 0 3px rgba(8,145,178,.1); }
.aq-filter-bar .search-wrap { position: relative; }
.aq-filter-bar .search-wrap svg { position: absolute; left: .625rem; top: 50%; transform: translateY(-50%); color: #94a3b8; pointer-events: none; }
.aq-filter-bar .search-wrap input { padding-left: 2rem; }

/* ── Compliance score ring (donut text) ── */
.compliance-ring-wrap { position: relative; width: 7rem; height: 7rem; margin: 0 auto; }
.compliance-ring-wrap canvas { width: 100% !important; height: 100% !important; }
.compliance-ring-label { position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; pointer-events: none; }
.compliance-ring-label .val { font-size: 1.25rem; font-weight: 800; color: #152b42; line-height: 1; }
.compliance-ring-label .sub { font-size: .6rem; color: #52677d; font-weight: 600; }
</style>
@endpush

@section('manager-content')
<div class="aqua-manager-standalone w-full space-y-5">

    {{-- ── Page header ── --}}
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-cyan-50 border border-cyan-200 text-cyan-700 text-[11px] font-semibold mb-1.5">
                <i data-lucide="droplets" class="w-3 h-3"></i>
                Surveillance qualité
            </div>
            <h1 class="text-2xl font-display font-bold text-slate-900 tracking-tight">Qualité de l'eau</h1>
            <p class="text-slate-500 text-xs mt-0.5">Analyses, seuils de contrôle et alertes de contamination.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <button onclick="openThresholdModal()"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold shadow-sm transition-all">
                <i data-lucide="sliders" class="w-3.5 h-3.5 text-cyan-600"></i> Seuils
            </button>
            <a href="{{ route('manager.points-mesure.index') }}"
               class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold shadow-sm transition-all">
                <i data-lucide="map-pin" class="w-3.5 h-3.5 text-cyan-600"></i> Points
            </a>
            @can('create', App\Models\MesureQualite::class)
                @if($activePointsCount > 0)
                    <a href="{{ route('manager.quality.mesures.create') }}"
                       class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-cyan-600 hover:bg-cyan-700 text-white text-xs font-semibold shadow-sm transition-all">
                        <i data-lucide="plus" class="w-3.5 h-3.5"></i> Nouvelle mesure
                    </a>
                @else
                    <a href="{{ route('manager.points-mesure.create') }}"
                       class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-cyan-600 hover:bg-cyan-700 text-white text-xs font-semibold shadow-sm transition-all">
                        <i data-lucide="plus" class="w-3.5 h-3.5"></i> Créer un point
                    </a>
                @endif
            @endcan
            <button onclick="showToast('Export CSV en cours…', 'success')"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold shadow-sm transition-all">
                <i data-lucide="file-spreadsheet" class="w-3.5 h-3.5 text-emerald-600"></i> Exporter CSV
            </button>
        </div>
    </div>

    {{-- ── Banner (maquette-style) ── --}}
    @if($latestMeasurement)
    <div class="aq-banner">
        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-cyan-400 to-blue-500 flex items-center justify-center flex-shrink-0 shadow-md">
            <i data-lucide="flask-conical" class="w-6 h-6 text-white"></i>
        </div>
        <div class="flex-1 min-w-0">
            <p class="text-sm font-bold text-cyan-900">Dernière analyse — {{ $latestMeasurement->reference }}</p>
            <p class="text-xs text-cyan-700 mt-0.5">
                {{ $latestMeasurement->pointMesure?->nom ?? '—' }} ·
                {{ $latestMeasurement->date_mesure->format('d/m/Y H:i') }} ·
                Conformité globale :
                <strong class="{{ ($latestAssessment['overall_compliance'] ?? 0) >= 80 ? 'text-emerald-700' : (($latestAssessment['overall_compliance'] ?? 0) >= 50 ? 'text-amber-700' : 'text-red-700') }}">
                    {{ $latestAssessment['overall_compliance'] ?? '—' }}%
                </strong>
            </p>
        </div>
        <span class="px-3 py-1 rounded-full text-xs font-bold
            {{ ($latestAssessment['status'] ?? '') === 'Compliant'
                ? 'bg-emerald-100 text-emerald-700 border border-emerald-200'
                : (($latestAssessment['status'] ?? '') === 'Alert'
                    ? 'bg-amber-100 text-amber-700 border border-amber-200'
                    : 'bg-red-100 text-red-700 border border-red-200') }}">
            {{ $latestAssessment['status'] ?? 'N/A' }}
        </span>
    </div>
    @endif

    {{-- ── 4 summary stat cards ── --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Conformité moyenne --}}
        <div class="aq-stat-card">
            <div class="aq-stat-icon bg-cyan-50">
                <i data-lucide="percent" class="w-5 h-5 text-cyan-600"></i>
            </div>
            <div>
                <div class="aq-stat-value text-cyan-700">{{ $averageCompliance !== null ? $averageCompliance.'%' : '—' }}</div>
                <div class="aq-stat-label">Conformité moy.</div>
                <div class="aq-stat-sub">Sur {{ $records->count() }} mesures</div>
            </div>
        </div>
        {{-- Points actifs --}}
        <div class="aq-stat-card">
            <div class="aq-stat-icon bg-blue-50">
                <i data-lucide="map-pin" class="w-5 h-5 text-blue-600"></i>
            </div>
            <div>
                <div class="aq-stat-value text-blue-700">{{ $activePointsCount }}</div>
                <div class="aq-stat-label">Points actifs</div>
                <div class="aq-stat-sub">de prélèvement</div>
            </div>
        </div>
        {{-- Mesures vérifiées --}}
        <div class="aq-stat-card">
            <div class="aq-stat-icon bg-emerald-50">
                <i data-lucide="badge-check" class="w-5 h-5 text-emerald-600"></i>
            </div>
            <div>
                <div class="aq-stat-value text-emerald-700">{{ $records->where('is_verified', true)->count() }}</div>
                <div class="aq-stat-label">Vérifiées</div>
                <div class="aq-stat-sub">Sur {{ $records->count() }} au total</div>
            </div>
        </div>
        {{-- Alertes --}}
        @php $alerts = $records->whereIn('status', ['Alert', 'Non-Compliant'])->count(); @endphp
        <div class="aq-stat-card">
            <div class="aq-stat-icon {{ $alerts > 0 ? 'bg-red-50' : 'bg-slate-50' }}">
                <i data-lucide="triangle-alert" class="w-5 h-5 {{ $alerts > 0 ? 'text-red-500' : 'text-slate-400' }}"></i>
            </div>
            <div>
                <div class="aq-stat-value {{ $alerts > 0 ? 'text-red-600' : 'text-slate-400' }}">{{ $alerts }}</div>
                <div class="aq-stat-label">Alertes / NC</div>
                <div class="aq-stat-sub">Nécessitent attention</div>
            </div>
        </div>
    </div>

    {{-- ── 5 parameter cards (maquette Water Quality Metrics) ── --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">

        {{-- pH --}}
        <div class="aq-param-card">
            <div class="flex items-center justify-between mb-1">
                <span class="param-name">pH</span>
                <span class="aq-param-badge bg-cyan-50 text-cyan-700 border border-cyan-100">6.5–8.5</span>
            </div>
            <div class="param-value">{{ $latestMeasurement?->ph ?? '—' }}<span class="param-unit"> pH</span></div>
            <div class="aq-progress">
                <div class="aq-progress-bar bg-cyan-400" style="width:{{ $latestMeasurement ? min(((float)$latestMeasurement->ph/14)*100,100) : 0 }}%"></div>
            </div>
            <p class="text-[10px] text-slate-400 mt-1.5 flex justify-between">
                <span>Dernière mesure</span>
                <span class="{{ ($latestAssessment['parameter_statuses']['ph'] ?? '') === 'Compliant' ? 'text-emerald-600' : 'text-amber-600' }} font-semibold">
                    {{ $latestAssessment['parameter_statuses']['ph'] ?? '—' }}
                </span>
            </p>
        </div>

        {{-- Turbidité --}}
        <div class="aq-param-card">
            <div class="flex items-center justify-between mb-1">
                <span class="param-name">Turbidité</span>
                <span class="aq-param-badge bg-blue-50 text-blue-700 border border-blue-100">&lt;1.0 NTU</span>
            </div>
            <div class="param-value">{{ $latestMeasurement?->turbidite ?? '—' }}<span class="param-unit"> NTU</span></div>
            <div class="aq-progress">
                <div class="aq-progress-bar bg-blue-400" style="width:{{ $latestMeasurement ? min(((float)$latestMeasurement->turbidite/2)*100,100) : 0 }}%"></div>
            </div>
            <p class="text-[10px] text-slate-400 mt-1.5 flex justify-between">
                <span>Dernière mesure</span>
                <span class="{{ ($latestAssessment['parameter_statuses']['turbidite'] ?? '') === 'Compliant' ? 'text-emerald-600' : 'text-amber-600' }} font-semibold">
                    {{ $latestAssessment['parameter_statuses']['turbidite'] ?? '—' }}
                </span>
            </p>
        </div>

        {{-- Chlore --}}
        <div class="aq-param-card">
            <div class="flex items-center justify-between mb-1">
                <span class="param-name">Chlore rés.</span>
                <span class="aq-param-badge bg-indigo-50 text-indigo-700 border border-indigo-100">0.2–2.0 mg/L</span>
            </div>
            <div class="param-value">{{ $latestMeasurement?->chlore_residuel ?? '—' }}<span class="param-unit"> mg/L</span></div>
            <div class="aq-progress">
                <div class="aq-progress-bar bg-indigo-400" style="width:{{ $latestMeasurement ? min(((float)$latestMeasurement->chlore_residuel/2)*100,100) : 0 }}%"></div>
            </div>
            <p class="text-[10px] text-slate-400 mt-1.5 flex justify-between">
                <span>Dernière mesure</span>
                <span class="{{ ($latestAssessment['parameter_statuses']['chlore_residuel'] ?? '') === 'Compliant' ? 'text-emerald-600' : 'text-amber-600' }} font-semibold">
                    {{ $latestAssessment['parameter_statuses']['chlore_residuel'] ?? '—' }}
                </span>
            </p>
        </div>

        {{-- Plomb --}}
        <div class="aq-param-card">
            <div class="flex items-center justify-between mb-1">
                <span class="param-name">Plomb (Pb)</span>
                <span class="aq-param-badge bg-amber-50 text-amber-700 border border-amber-100">&lt;15 ppb</span>
            </div>
            <div class="param-value">{{ $latestMeasurement?->plomb ?? '—' }}<span class="param-unit"> ppb</span></div>
            <div class="aq-progress">
                <div class="aq-progress-bar bg-amber-400" style="width:{{ $latestMeasurement ? min(((float)$latestMeasurement->plomb/15)*100,100) : 0 }}%"></div>
            </div>
            <p class="text-[10px] text-slate-400 mt-1.5 flex justify-between">
                <span>Dernière mesure</span>
                <span class="{{ ($latestAssessment['parameter_statuses']['plomb'] ?? '') === 'Compliant' ? 'text-emerald-600' : 'text-red-600' }} font-semibold">
                    {{ $latestAssessment['parameter_statuses']['plomb'] ?? '—' }}
                </span>
            </p>
        </div>

        {{-- Nitrates --}}
        <div class="aq-param-card">
            <div class="flex items-center justify-between mb-1">
                <span class="param-name">Nitrates</span>
                @if(config('water_quality.nitrates_max') !== null)
                    <span class="aq-param-badge bg-violet-50 text-violet-700 border border-violet-100">&le;{{ config('water_quality.nitrates_max') }}</span>
                @else
                    <span class="aq-param-badge bg-slate-50 text-slate-500 border border-slate-200">—</span>
                @endif
            </div>
            <div class="param-value">{{ $latestMeasurement?->nitrates ?? '—' }}<span class="param-unit"> mg/L</span></div>
            <div class="aq-progress">
                <div class="aq-progress-bar bg-violet-400" style="width:{{ $latestMeasurement ? min(((float)$latestMeasurement->nitrates/100)*100,100) : 0 }}%"></div>
            </div>
            <p class="text-[10px] text-slate-400 mt-1.5 flex justify-between">
                <span>{{ config('water_quality.nitrates_max') !== null ? 'Max '.config('water_quality.nitrates_max').' mg/L' : 'Non configuré' }}</span>
                <span class="text-violet-600 font-semibold">
                    {{ $latestAssessment['parameter_statuses']['nitrates'] ?? ($latestMeasurement ? '—' : '—') }}
                </span>
            </p>
        </div>
    </div>

    {{-- ── Map + Trend chart (maquette: graph section) ── --}}
    <div class="grid lg:grid-cols-2 gap-5">

        {{-- Leaflet map --}}
        <div class="aq-card">
            <div class="aq-card-header">
                <h3 class="aq-card-title">
                    <i data-lucide="map-pin" class="w-4 h-4 text-cyan-600"></i>
                    Carte des stations de prélèvement
                </h3>
                <span class="text-xs text-slate-500 font-medium">{{ $activePointsCount }} point(s) actif(s)</span>
            </div>
            <div class="p-4">
                <div id="quality-map"></div>
            </div>
        </div>

        {{-- Trend chart (maquette: "Your Ward - Water Usage" style) ── --}}
        <div class="aq-card">
            <div class="aq-card-header">
                <h3 class="aq-card-title">
                    <i data-lucide="line-chart" class="w-4 h-4 text-cyan-600"></i>
                    Conformité sur 30 jours
                </h3>
                @if($averageCompliance !== null)
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-cyan-50 border border-cyan-200 text-cyan-700 text-[11px] font-semibold">
                        <i data-lucide="trending-up" class="w-3 h-3"></i>
                        {{ $averageCompliance }}% moy.
                    </span>
                @endif
            </div>
            <div class="p-4">
                <div class="aq-chart-wrap">
                    <canvas id="qualityTrendChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Measurements table ── --}}
    <div class="aq-card">
        <div class="aq-card-header">
            <div>
                <h3 class="aq-card-title">
                    <i data-lucide="clipboard-list" class="w-4 h-4 text-cyan-600"></i>
                    Résultats d'analyses
                </h3>
                <p class="text-[11px] text-slate-400 mt-0.5">Relevés vérifiés et en attente de validation.</p>
            </div>
            <span class="text-xs text-slate-500 font-medium font-mono">
                {{ $paginator->total() }} mesure(s)
            </span>
        </div>

        {{-- Filters --}}
        <div class="aq-filter-bar">
            <form method="GET" action="{{ route('manager.quality') }}" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-6 items-end">
                <div class="lg:col-span-2 search-wrap">
                    <label>Recherche (Réf., point, zone)</label>
                    <i data-lucide="search" class="w-3.5 h-3.5" style="position:absolute;left:.625rem;top:calc(50% + .7rem);transform:translateY(-50%);color:#94a3b8;pointer-events:none"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Ex: WQR-2026 ou Belvédère" style="padding-left:2rem">
                </div>
                <div>
                    <label>Point de prélèvement</label>
                    <select name="point_mesure_id">
                        <option value="">Tous les points</option>
                        @foreach($allPointsForFilter ?? [] as $pt)
                            <option value="{{ $pt->id }}" @selected((string) request('point_mesure_id') === (string) $pt->id)>{{ $pt->nom }} ({{ $pt->code }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label>Conformité</label>
                    <select name="status">
                        <option value="">Tous les états</option>
                        <option value="Compliant"     @selected(request('status') === 'Compliant')>Conforme</option>
                        <option value="Alert"         @selected(request('status') === 'Alert')>Alerte</option>
                        <option value="Non-Compliant" @selected(request('status') === 'Non-Compliant')>Non conforme</option>
                    </select>
                </div>
                <div>
                    <label>Provenance</label>
                    <select name="source_donnee">
                        <option value="">Toutes sources</option>
                        <option value="manuel"      @selected(request('source_donnee') === 'manuel')>Manuel (terrain)</option>
                        <option value="laboratoire" @selected(request('source_donnee') === 'laboratoire')>Laboratoire</option>
                        <option value="simule"      @selected(request('source_donnee') === 'simule')>Simulé</option>
                    </select>
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit"
                            class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl bg-cyan-600 hover:bg-cyan-700 text-white text-xs font-semibold shadow-sm transition-all">
                        <i data-lucide="filter" class="w-3.5 h-3.5"></i> Filtrer
                    </button>
                    @if(request()->hasAny(['search','point_mesure_id','status','source_donnee']))
                        <a href="{{ route('manager.quality') }}"
                           class="inline-flex items-center justify-center px-2.5 py-2 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-500 text-xs transition-all"
                           title="Réinitialiser">
                            <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="aq-table">
                <thead>
                    <tr>
                        <th>Réf. &amp; Point</th>
                        <th>Zone</th>
                        <th>Date</th>
                        <th>pH</th>
                        <th>Turbidité</th>
                        <th>Chlore</th>
                        <th>Plomb</th>
                        <th>Nitrates</th>
                        <th>Validation</th>
                        <th>Provenance</th>
                        <th style="text-align:right">Conformité</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($records as $rec)
                        <tr>
                            <td>
                                <a href="{{ route('manager.quality.mesures.show', $rec['measurement_id']) }}"
                                   class="font-mono text-[11px] text-cyan-700 hover:underline font-semibold">{{ $rec['id'] }}</a>
                                <div class="text-[11px] text-slate-500 mt-0.5">
                                    <a href="{{ route('manager.points-mesure.index') }}" class="hover:text-cyan-700">{{ $rec['sampling_point'] }}</a>
                                </div>
                            </td>
                            <td class="text-slate-600">{{ $rec['zone'] }}</td>
                            <td class="text-slate-500 whitespace-nowrap">{{ $rec['date'] }}</td>
                            <td class="font-mono font-semibold {{ (float)$rec['pH'] < 6.5 || (float)$rec['pH'] > 8.5 ? 'text-red-600' : 'text-slate-700' }}">
                                {{ $rec['pH'] }}
                            </td>
                            <td class="font-mono font-semibold {{ (float)$rec['turbidity'] > 1.0 ? 'text-amber-600' : 'text-slate-700' }}">
                                {{ $rec['turbidity'] }} <span class="font-normal text-slate-400">NTU</span>
                            </td>
                            <td class="font-mono font-semibold {{ (float)$rec['residual_chlorine'] < 0.2 ? 'text-red-600' : 'text-slate-700' }}">
                                {{ $rec['residual_chlorine'] }} <span class="font-normal text-slate-400">mg/L</span>
                            </td>
                            <td class="font-mono font-semibold {{ (float)$rec['lead_pb'] > 15 ? 'text-red-600' : 'text-slate-700' }}">
                                {{ $rec['lead_pb'] }} <span class="font-normal text-slate-400">ppb</span>
                            </td>
                            <td class="font-mono text-slate-600">{{ $rec['nitrates'] }} <span class="text-slate-400">mg/L</span></td>
                            <td>
                                @if($rec['is_verified'])
                                    <span class="badge badge-green">
                                        <i data-lucide="check-circle-2" class="w-3 h-3"></i> Vérifié
                                    </span>
                                @else
                                    <span class="badge badge-amber">
                                        <i data-lucide="clock" class="w-3 h-3"></i> En attente
                                    </span>
                                @endif
                            </td>
                            <td>
                                @php $src = $rec['source_donnee'] ?? 'manuel'; @endphp
                                @if($src === 'laboratoire')
                                    <span class="badge badge-blue">Laboratoire</span>
                                @elseif($src === 'simule')
                                    <span class="badge badge-slate">Simulé</span>
                                @else
                                    <span class="badge badge-violet">Manuel</span>
                                @endif
                            </td>
                            <td style="text-align:right">
                                @php
                                    $compCls = $rec['status'] === 'Compliant'
                                        ? 'badge-green'
                                        : ($rec['status'] === 'Alert' ? 'badge-amber' : 'badge-red');
                                @endphp
                                <span class="badge {{ $compCls }}">
                                    {{ $rec['status'] }} · {{ $rec['overall_compliance'] }}%
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" style="text-align:center;padding:3rem 1rem;color:#52677d">
                                <i data-lucide="inbox" class="w-8 h-8 mx-auto mb-2 text-slate-300" style="display:block"></i>
                                Aucune mesure enregistrée. Créez un point de prélèvement puis ajoutez une mesure.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($paginator->hasPages())
            <div class="px-4 py-3 border-t border-slate-100">
                {{ $paginator->links('pagination::tailwind') }}
            </div>
        @endif
    </div>

</div>

{{-- ── Data collection component ── --}}
<x-manager.data-collection-card :points="$allPointsForFilter ?? []" />

{{-- ── Threshold info modal ── --}}
<div id="threshold-modal" class="hidden fixed inset-0 z-50 bg-black/40 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white border border-slate-200 rounded-2xl max-w-md w-full p-6 space-y-4 shadow-2xl">
        <div class="flex items-center justify-between">
            <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                <i data-lucide="sliders" class="w-5 h-5 text-cyan-600"></i> Seuils appliqués
            </h3>
            <button onclick="closeThresholdModal()" class="text-slate-400 hover:text-slate-600 transition-colors">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>
        <p class="text-xs text-slate-600 bg-cyan-50 border border-cyan-100 rounded-xl px-3 py-2">
            Seuils configurés pour ce projet — non certifiés comme normes officielles.
        </p>
        <p class="text-xs text-amber-700 bg-amber-50 border border-amber-200 rounded-xl px-3 py-2">
            Pour modifier les seuils, mettez à jour les variables <code class="font-mono bg-amber-100 rounded px-1">WATER_QUALITY_*</code> dans <code class="font-mono bg-amber-100 rounded px-1">.env</code> puis redémarrez.
        </p>
        <div class="space-y-2 text-xs">
            @foreach([
                ['pH admissible',              config('water_quality.ph_min').' – '.config('water_quality.ph_max')],
                ['Turbidité alerte (NTU)',      '> '.config('water_quality.turbidite_alert')],
                ['Turbidité non conforme (NTU)','> '.config('water_quality.turbidite_max')],
                ['Chlore résiduel (mg/L)',      config('water_quality.chlore_min').' – '.config('water_quality.chlore_max')],
                ['Plomb seuil action (ppb)',    '≤ '.config('water_quality.plomb_max')],
            ] as [$label, $val])
                <div class="flex justify-between items-center py-1.5 border-b border-slate-100">
                    <span class="text-slate-500">{{ $label }}</span>
                    <span class="font-mono font-bold text-slate-800">{{ $val }}</span>
                </div>
            @endforeach
            <div class="flex justify-between items-center py-1.5">
                <span class="text-slate-500">Nitrates (mg/L)</span>
                <span class="font-mono font-bold text-slate-800">
                    {{ config('water_quality.nitrates_max') !== null ? '≤ '.config('water_quality.nitrates_max') : '— Non configuré' }}
                </span>
            </div>
        </div>
        <div class="flex justify-end pt-1">
            <button onclick="closeThresholdModal()"
                    class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition-colors">
                Fermer
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
/* ── Helpers ── */
function openThresholdModal()  { document.getElementById('threshold-modal').classList.remove('hidden'); }
function closeThresholdModal() { document.getElementById('threshold-modal').classList.add('hidden'); }

function escapeHtml(v) {
    return String(v ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'})[c]);
}

/* ── Leaflet map ── */
(function () {
    const POINTS = @json($points);
    const map = L.map('quality-map', { center: [36.2, 10.3], zoom: 7, zoomControl: false, attributionControl: false });
    L.control.zoom({ position: 'topright' }).addTo(map);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 18 }).addTo(map);

    POINTS.forEach(function (pt) {
        const r = pt.measurement;
        const c = pt.status === 'normal' ? '#0891b2'
                : pt.status === 'alert'  ? '#f59e0b'
                :                          '#ef4444';
        const svg = `<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32">
            <circle cx="16" cy="16" r="11" fill="${c}" fill-opacity="0.9" stroke="#fff" stroke-width="2.5"/>
        </svg>`;
        const details = r
            ? `Réf. ${escapeHtml(r.reference)}<br>Conformité : <b>${pt.overall_compliance}%</b><br>pH : ${r.ph} | Turbidité : ${r.turbidite} NTU<br>Chlore : ${r.chlore_residuel} mg/L | Plomb : ${r.plomb} ppb`
            : '<em>Aucune mesure</em>';
        L.marker([pt.lat, pt.lng], {
            icon: L.divIcon({ html: svg, iconSize: [32, 32], iconAnchor: [16, 16], className: '' })
        }).addTo(map).bindPopup(`<b>${escapeHtml(pt.name)}</b><br><small>${escapeHtml(pt.zone ?? '')}</small><br>${details}`);
    });
})();

/* ── Quality trend chart (light theme) ── */
(function () {
    const ctx    = document.getElementById('qualityTrendChart').getContext('2d');
    const data   = @json($chartRecords);
    const isDark = document.documentElement.getAttribute('data-theme') === 'dark';

    const gridColor  = isDark ? 'rgba(255,255,255,0.06)' : 'rgba(15,42,67,0.06)';
    const labelColor = isDark ? '#94a3b8' : '#64748b';

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.map(r => r.date.slice(0, 10)),
            datasets: [
                {
                    label: 'Conformité (%)',
                    data: data.map(r => r.overall_compliance),
                    borderColor: '#0891b2',
                    backgroundColor: 'rgba(8,145,178,0.08)',
                    fill: true,
                    tension: 0.35,
                    pointRadius: 3,
                    pointBackgroundColor: '#0891b2',
                    borderWidth: 2,
                },
                {
                    label: 'pH',
                    data: data.map(r => r.pH),
                    borderColor: '#6366f1',
                    backgroundColor: 'transparent',
                    tension: 0.35,
                    pointRadius: 2,
                    borderWidth: 1.5,
                    borderDash: [4, 3],
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: {
                    labels: { color: labelColor, font: { size: 11 }, boxWidth: 14, padding: 12 }
                },
                tooltip: {
                    backgroundColor: isDark ? '#1e293b' : '#fff',
                    titleColor: isDark ? '#f1f5f9' : '#152b42',
                    bodyColor:  isDark ? '#94a3b8' : '#52677d',
                    borderColor: isDark ? '#334155' : '#dce6ef',
                    borderWidth: 1,
                }
            },
            scales: {
                x: { ticks: { color: labelColor, font: { size: 10 } }, grid: { color: gridColor } },
                y: { ticks: { color: labelColor, font: { size: 10 } }, grid: { color: gridColor }, min: 0, max: 100 }
            }
        }
    });

    /* Update on theme change */
    window.addEventListener('aq:themechange', function (e) {
        const dark = e.detail.theme === 'dark';
        Chart.defaults.color = dark ? '#94a3b8' : '#64748b';
    });
})();
</script>
@endpush
