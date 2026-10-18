@extends('layouts.manager')

@section('title', 'AquaSecure — Supervision Qualité de l\'eau')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@include('manager.shared.light-shell')

<style>
/* ── Visual Tokens & Dark/Light Theming ── */
.aq-quality-dashboard {
    color: var(--text-primary, #152b42);
}

.aq-glass-panel {
    background: var(--bg-card, #ffffff);
    border: 1px solid var(--border, #dce6ef);
    border-radius: 1.25rem;
    box-shadow: 0 4px 18px rgba(16, 42, 67, 0.04);
    transition: box-shadow 0.2s ease, border-color 0.2s ease;
}

[data-theme="dark"] .aq-glass-panel {
    background: var(--bg-card, #0c1624);
    border-color: var(--border, #1e293b);
    box-shadow: 0 4px 22px rgba(0, 0, 0, 0.35);
}

/* ── KPI Stat Cards ── */
.aq-kpi-card {
    background: var(--bg-card, #ffffff);
    border: 1px solid var(--border, #dce6ef);
    border-radius: 1.125rem;
    padding: 1.25rem 1.5rem;
    display: flex;
    align-items: center;
    gap: 1.125rem;
    box-shadow: 0 2px 12px rgba(16, 42, 67, 0.04);
    transition: transform 0.15s ease, box-shadow 0.15s ease;
}

.aq-kpi-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(16, 42, 67, 0.08);
}

[data-theme="dark"] .aq-kpi-card {
    background: var(--bg-card, #0f1c2e);
    border-color: var(--border, #1e2e42);
}

[data-theme="dark"] .aq-kpi-card:hover {
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.45);
    border-color: #0284c7;
}

/* ── Parameter Telemetry Cards ── */
.aq-telemetry-box {
    background: var(--bg-card, #ffffff);
    border: 1px solid var(--border, #dce6ef);
    border-radius: 1.125rem;
    padding: 1.125rem 1.25rem;
    position: relative;
    overflow: hidden;
    transition: transform 0.15s ease, box-shadow 0.15s ease;
}

.aq-telemetry-box:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(16, 42, 67, 0.07);
}

[data-theme="dark"] .aq-telemetry-box {
    background: var(--bg-card, #0f1c2e);
    border-color: var(--border, #1e2e42);
}

/* ── Progress Indicators ── */
.aq-mini-bar {
    height: 6px;
    background: rgba(148, 163, 184, 0.2);
    border-radius: 9999px;
    overflow: hidden;
    margin-top: 0.625rem;
}

.aq-mini-fill {
    height: 100%;
    border-radius: 9999px;
    transition: width 0.4s ease;
}

/* ── Live Banner ── */
.aq-hero-banner {
    background: linear-gradient(135deg, rgba(8, 145, 178, 0.08) 0%, rgba(14, 116, 144, 0.04) 50%, rgba(59, 130, 246, 0.08) 100%);
    border: 1px solid rgba(8, 145, 178, 0.25);
    border-radius: 1.25rem;
    padding: 1.25rem 1.5rem;
}

[data-theme="dark"] .aq-hero-banner {
    background: linear-gradient(135deg, rgba(8, 145, 178, 0.16) 0%, rgba(14, 116, 144, 0.08) 50%, rgba(59, 130, 246, 0.12) 100%);
    border-color: rgba(8, 145, 178, 0.35);
}

/* ── Map & Chart container ── */
#quality-map {
    height: 340px;
    border-radius: 0.875rem;
    z-index: 1;
}

.aq-chart-container {
    height: 340px;
    position: relative;
}

.leaflet-tile-pane {
    filter: brightness(0.98) contrast(1.02) saturate(0.95);
}

[data-theme="dark"] .leaflet-tile-pane {
    filter: brightness(0.75) invert(1) contrast(3) hue-rotate(200deg) saturate(0.35);
}

.leaflet-popup-content-wrapper {
    background: var(--bg-card, #ffffff) !important;
    border: 1px solid var(--border, #dce6ef) !important;
    border-radius: 0.875rem !important;
    color: var(--text-primary, #152b42) !important;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15) !important;
}

.leaflet-popup-tip {
    background: var(--bg-card, #ffffff) !important;
}

/* ── Badges ── */
.badge-chip {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.25rem 0.625rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 700;
    line-height: 1;
}

.badge-green {
    background: rgba(16, 185, 129, 0.12);
    color: #059669;
    border: 1px solid rgba(16, 185, 129, 0.25);
}
[data-theme="dark"] .badge-green {
    background: rgba(16, 185, 129, 0.18);
    color: #34d399;
    border-color: rgba(16, 185, 129, 0.35);
}

.badge-amber {
    background: rgba(245, 158, 11, 0.12);
    color: #d97706;
    border: 1px solid rgba(245, 158, 11, 0.25);
}
[data-theme="dark"] .badge-amber {
    background: rgba(245, 158, 11, 0.18);
    color: #fbbf24;
    border-color: rgba(245, 158, 11, 0.35);
}

.badge-red {
    background: rgba(239, 68, 68, 0.12);
    color: #dc2626;
    border: 1px solid rgba(239, 68, 68, 0.25);
}
[data-theme="dark"] .badge-red {
    background: rgba(239, 68, 68, 0.18);
    color: #f87171;
    border-color: rgba(239, 68, 68, 0.35);
}

.badge-blue {
    background: rgba(2, 132, 199, 0.12);
    color: #0284c7;
    border: 1px solid rgba(2, 132, 199, 0.25);
}
[data-theme="dark"] .badge-blue {
    background: rgba(2, 132, 199, 0.18);
    color: #38bdf8;
    border-color: rgba(2, 132, 199, 0.35);
}

.badge-violet {
    background: rgba(139, 92, 246, 0.12);
    color: #7c3aed;
    border: 1px solid rgba(139, 92, 246, 0.25);
}
[data-theme="dark"] .badge-violet {
    background: rgba(139, 92, 246, 0.18);
    color: #a78bfa;
    border-color: rgba(139, 92, 246, 0.35);
}

/* ── Modal blur backdrops ── */
.modal-overlay-blur {
    background-color: rgba(15, 23, 42, 0.65);
    backdrop-filter: blur(5px);
}
</style>
@include('manager.shared.light-shell')
@endpush

@section('manager-content')
<div class="aq-quality-dashboard w-full space-y-6 pb-12">

    {{-- ══ 1. HEADER & GLOBAL ACTIONS ══════════════════════════════════════════ --}}
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">
        <div class="space-y-1.5">
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-cyan-500/10 border border-cyan-500/25 text-cyan-700 dark:text-cyan-300 text-xs font-bold tracking-wide">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                    </span>
                    Supervision Réseau National
                </span>
                <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">· Système actif</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">
                Qualité &amp; Conformité de l'Eau
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">
                Surveillance continue sur <strong class="text-slate-700 dark:text-slate-200">{{ $activePointsCount }} stations</strong> · <strong class="text-slate-700 dark:text-slate-200">{{ $paginator->total() }} relevés</strong> analysés et vérifiés.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5 shrink-0">
            <button type="button" onclick="openThresholdModal()"
                    class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-750 text-slate-700 dark:text-slate-200 text-xs font-bold shadow-xs transition cursor-pointer">
                <i data-lucide="sliders" class="w-4 h-4 text-cyan-600 dark:text-cyan-400"></i>
                <span>Seuils réglementaires</span>
            </button>

            <a href="{{ route('manager.points-mesure.index') }}"
               class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-750 text-slate-700 dark:text-slate-200 text-xs font-bold shadow-xs transition cursor-pointer">
                <i data-lucide="map-pin" class="w-4 h-4 text-cyan-600 dark:text-cyan-400"></i>
                <span>Stations de prélèvement</span>
            </a>

            @can('create', App\Models\MesureQualite::class)
                @if($activePointsCount > 0)
                    <a href="{{ route('manager.quality.mesures.create') }}"
                       class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-cyan-600 hover:bg-cyan-700 text-white text-xs font-bold shadow-md shadow-cyan-600/20 transition cursor-pointer">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                        <span>Nouvelle mesure</span>
                    </a>
                @else
                    <a href="{{ route('manager.points-mesure.create') }}"
                       class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-cyan-600 hover:bg-cyan-700 text-white text-xs font-bold shadow-md shadow-cyan-600/20 transition cursor-pointer">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                        <span>Créer un point</span>
                    </a>
                @endif
            @endcan

            <button type="button" onclick="showToast('Export CSV en cours…', 'success')"
                    class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-750 text-slate-700 dark:text-slate-200 text-xs font-bold shadow-xs transition cursor-pointer">
                <i data-lucide="file-spreadsheet" class="w-4 h-4 text-emerald-600"></i>
                <span>Exporter CSV</span>
            </button>
        </div>
    </div>

    {{-- ══ 2. LIVE TELEMETRY BANNER (DERNIÈRE ANALYSE ENREGISTRÉE) ═════════════ --}}
    @if($latestMeasurement)
        <div class="aq-hero-banner flex flex-col md:flex-row md:items-center justify-between gap-4 shadow-xs">
            <div class="flex items-center gap-4 min-w-0">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-cyan-500 to-blue-600 flex items-center justify-center flex-shrink-0 text-white shadow-md shadow-cyan-500/20">
                    <i data-lucide="activity" class="w-6 h-6"></i>
                </div>
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-xs font-bold uppercase tracking-wider text-cyan-800 dark:text-cyan-300">Dernier relevé télémétrique</span>
                        <a href="{{ route('manager.quality.mesures.show', $latestMeasurement) }}" class="font-mono text-xs font-bold text-cyan-600 dark:text-cyan-400 hover:underline">
                            #{{ $latestMeasurement->reference }}
                        </a>
                    </div>
                    <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-slate-600 dark:text-slate-300 mt-1">
                        <span class="font-semibold text-slate-900 dark:text-white">{{ $latestMeasurement->pointMesure?->nom ?? 'Station réseau' }}</span>
                        <span>·</span>
                        <span>{{ $latestMeasurement->date_mesure->format('d/m/Y à H:i') }}</span>
                        <span>·</span>
                        <span>pH: <strong class="font-mono">{{ $latestMeasurement->ph ?? '—' }}</strong></span>
                        <span>·</span>
                        <span>Turb: <strong class="font-mono">{{ $latestMeasurement->turbidite ?? '—' }} NTU</strong></span>
                        <span>·</span>
                        <span>Chlore: <strong class="font-mono">{{ $latestMeasurement->chlore_residuel ?? '—' }} mg/L</strong></span>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3 shrink-0">
                <div class="text-right hidden sm:block">
                    <div class="text-[11px] text-slate-500 uppercase tracking-wide font-semibold">Conformité globale</div>
                    <div class="text-base font-black font-mono {{ ($latestAssessment['overall_compliance'] ?? 0) >= 80 ? 'text-emerald-600 dark:text-emerald-400' : (($latestAssessment['overall_compliance'] ?? 0) >= 50 ? 'text-amber-600 dark:text-amber-400' : 'text-red-600 dark:text-red-400') }}">
                        {{ $latestAssessment['overall_compliance'] ?? '—' }} %
                    </div>
                </div>

                <a href="{{ route('manager.quality.mesures.show', $latestMeasurement) }}" class="badge-chip
                    {{ ($latestAssessment['status'] ?? '') === 'Compliant'
                        ? 'badge-green'
                        : (($latestAssessment['status'] ?? '') === 'Alert'
                            ? 'badge-amber'
                            : 'badge-red') }}">
                    <i data-lucide="{{ ($latestAssessment['status'] ?? '') === 'Compliant' ? 'check-circle' : 'alert-triangle' }}" class="w-3.5 h-3.5"></i>
                    <span>{{ $latestAssessment['status'] ?? 'Évalué' }}</span>
                </a>
            </div>
        </div>
    @endif

    {{-- ══ 3. 4 KPI STAT CARDS ═════════════════════════════════════════════════ --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Conformité moyenne --}}
        <div class="aq-kpi-card">
            <div class="w-12 h-12 rounded-2xl bg-cyan-500/10 dark:bg-cyan-500/20 text-cyan-600 dark:text-cyan-400 flex items-center justify-center shrink-0">
                <i data-lucide="percent" class="w-6 h-6"></i>
            </div>
            <div class="min-w-0">
                <div class="text-2xl font-black font-mono text-slate-900 dark:text-white">
                    {{ $averageCompliance !== null ? $averageCompliance.'%' : '—' }}
                </div>
                <div class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Conformité moyenne</div>
                <div class="text-[11px] text-slate-400 mt-0.5 truncate">Sur {{ $records->count() }} relevés paginés</div>
            </div>
        </div>

        {{-- Points actifs --}}
        <div class="aq-kpi-card">
            <div class="w-12 h-12 rounded-2xl bg-blue-500/10 dark:bg-blue-500/20 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0">
                <i data-lucide="map-pin" class="w-6 h-6"></i>
            </div>
            <div class="min-w-0">
                <div class="text-2xl font-black font-mono text-slate-900 dark:text-white">
                    {{ $activePointsCount }}
                </div>
                <div class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Stations actives</div>
                <div class="text-[11px] text-slate-400 mt-0.5 truncate">Points de contrôle en service</div>
            </div>
        </div>

        {{-- Mesures vérifiées --}}
        <div class="aq-kpi-card">
            <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                <i data-lucide="shield-check" class="w-6 h-6"></i>
            </div>
            <div class="min-w-0">
                <div class="text-2xl font-black font-mono text-slate-900 dark:text-white">
                    {{ $records->where('is_verified', true)->count() }}
                </div>
                <div class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Analyses validées</div>
                <div class="text-[11px] text-slate-400 mt-0.5 truncate">Sur {{ $records->count() }} sur cette page</div>
            </div>
        </div>

        {{-- Alertes sanitaires --}}
        @php $alerts = $records->whereIn('status', ['Alert', 'Non-Compliant'])->count(); @endphp
        <div class="aq-kpi-card">
            <div class="w-12 h-12 rounded-2xl {{ $alerts > 0 ? 'bg-red-500/10 dark:bg-red-500/20 text-red-600 dark:text-red-400' : 'bg-slate-500/10 text-slate-400' }} flex items-center justify-center shrink-0">
                <i data-lucide="triangle-alert" class="w-6 h-6"></i>
            </div>
            <div class="min-w-0">
                <div class="text-2xl font-black font-mono {{ $alerts > 0 ? 'text-red-600 dark:text-red-400' : 'text-slate-400' }}">
                    {{ $alerts }}
                </div>
                <div class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Alertes &amp; Déviations</div>
                <div class="text-[11px] text-slate-400 mt-0.5 truncate">Nécessitant investigation</div>
            </div>
        </div>
    </div>

    {{-- ══ 4. 5 PARAMETER TELEMETRY CARDS (NORMES NT 09.14) ═══════════════════ --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3.5">

        {{-- 1. pH --}}
        <div class="aq-telemetry-box">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">pH</span>
                <span class="badge-chip badge-violet text-[10px] px-2 py-0.5">6.5–8.5</span>
            </div>
            <div class="mt-2 text-xl font-black font-mono text-slate-900 dark:text-white">
                {{ $latestMeasurement?->ph ?? '—' }} <span class="text-xs font-normal text-slate-400">pH</span>
            </div>
            <div class="aq-mini-bar">
                <div class="aq-mini-fill bg-violet-500" style="width:{{ $latestMeasurement ? min(((float)$latestMeasurement->ph/14)*100, 100) : 0 }}%"></div>
            </div>
            <div class="mt-2 flex items-center justify-between text-[10px] text-slate-400">
                <span>Dernière mesure</span>
                <span class="{{ ($latestAssessment['parameter_statuses']['ph'] ?? '') === 'Compliant' ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600' }} font-bold">
                    {{ $latestAssessment['parameter_statuses']['ph'] ?? '—' }}
                </span>
            </div>
        </div>

        {{-- 2. Turbidité --}}
        <div class="aq-telemetry-box">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Turbidité</span>
                <span class="badge-chip badge-blue text-[10px] px-2 py-0.5">&lt; 1.0 NTU</span>
            </div>
            <div class="mt-2 text-xl font-black font-mono text-slate-900 dark:text-white">
                {{ $latestMeasurement?->turbidite ?? '—' }} <span class="text-xs font-normal text-slate-400">NTU</span>
            </div>
            <div class="aq-mini-bar">
                <div class="aq-mini-fill bg-cyan-500" style="width:{{ $latestMeasurement ? min(((float)$latestMeasurement->turbidite/2.5)*100, 100) : 0 }}%"></div>
            </div>
            <div class="mt-2 flex items-center justify-between text-[10px] text-slate-400">
                <span>Dernière mesure</span>
                <span class="{{ ($latestAssessment['parameter_statuses']['turbidite'] ?? '') === 'Compliant' ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600' }} font-bold">
                    {{ $latestAssessment['parameter_statuses']['turbidite'] ?? '—' }}
                </span>
            </div>
        </div>

        {{-- 3. Chlore résiduel --}}
        <div class="aq-telemetry-box">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Chlore rés.</span>
                <span class="badge-chip badge-green text-[10px] px-2 py-0.5">0.2–2.0 mg/L</span>
            </div>
            <div class="mt-2 text-xl font-black font-mono text-slate-900 dark:text-white">
                @if($latestMeasurement?->chlore_residuel !== null)
                    {{ $latestMeasurement->chlore_residuel }} <span class="text-xs font-normal text-slate-400">mg/L</span>
                @else
                    <span class="text-sm font-medium text-slate-400">Non renseigné</span>
                @endif
            </div>
            <div class="aq-mini-bar">
                <div class="aq-mini-fill bg-emerald-500" style="width:{{ $latestMeasurement?->chlore_residuel !== null ? min(((float)$latestMeasurement->chlore_residuel/2.0)*100, 100) : 0 }}%"></div>
            </div>
            <div class="mt-2 flex items-center justify-between text-[10px] text-slate-400">
                <span>Dernière mesure</span>
                <span class="{{ ($latestAssessment['parameter_statuses']['chlore_residuel'] ?? '') === 'Compliant' ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600' }} font-bold">
                    {{ $latestAssessment['parameter_statuses']['chlore_residuel'] ?? 'Non fourni' }}
                </span>
            </div>
        </div>

        {{-- 4. Plomb --}}
        <div class="aq-telemetry-box">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Plomb (Pb)</span>
                <span class="badge-chip badge-amber text-[10px] px-2 py-0.5">&le; 15 ppb</span>
            </div>
            <div class="mt-2 text-xl font-black font-mono text-slate-900 dark:text-white">
                @if($latestMeasurement?->plomb !== null)
                    {{ $latestMeasurement->plomb }} <span class="text-xs font-normal text-slate-400">ppb</span>
                @else
                    <span class="text-sm font-medium text-slate-400">Non renseigné</span>
                @endif
            </div>
            <div class="aq-mini-bar">
                <div class="aq-mini-fill bg-amber-500" style="width:{{ $latestMeasurement?->plomb !== null ? min(((float)$latestMeasurement->plomb/15.0)*100, 100) : 0 }}%"></div>
            </div>
            <div class="mt-2 flex items-center justify-between text-[10px] text-slate-400">
                <span>Dernière mesure</span>
                <span class="{{ ($latestAssessment['parameter_statuses']['plomb'] ?? '') === 'Compliant' ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600' }} font-bold">
                    {{ $latestAssessment['parameter_statuses']['plomb'] ?? 'Non fourni' }}
                </span>
            </div>
        </div>

        {{-- 5. Nitrates --}}
        <div class="aq-telemetry-box">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Nitrates (NO₃)</span>
                <span class="badge-chip badge-blue text-[10px] px-2 py-0.5">&le; 50 mg/L</span>
            </div>
            <div class="mt-2 text-xl font-black font-mono text-slate-900 dark:text-white">
                {{ $latestMeasurement?->nitrates ?? '—' }} <span class="text-xs font-normal text-slate-400">mg/L</span>
            </div>
            <div class="aq-mini-bar">
                <div class="aq-mini-fill bg-blue-500" style="width:{{ $latestMeasurement ? min(((float)$latestMeasurement->nitrates/60.0)*100, 100) : 0 }}%"></div>
            </div>
            <div class="mt-2 flex items-center justify-between text-[10px] text-slate-400">
                <span>Plafond 50 mg/L</span>
                <span class="{{ ($latestAssessment['parameter_statuses']['nitrates'] ?? '') === 'Compliant' ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600' }} font-bold">
                    {{ $latestAssessment['parameter_statuses']['nitrates'] ?? '—' }}
                </span>
            </div>
        </div>

    </div>

    {{-- ══ 5. TWO COLUMNS : LEAFLET MAP & 30-DAY TREND CHART ══════════════════ --}}
    <div class="grid lg:grid-cols-2 gap-6">

        {{-- Map Card --}}
        <div class="aq-glass-panel p-5 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <i data-lucide="map" class="w-4 h-4 text-cyan-600"></i>
                    Cartographie des stations de prélèvement
                </h3>
                <span class="badge-chip badge-blue text-[10px]">
                    {{ $activePointsCount }} station(s) géolocalisée(s)
                </span>
            </div>
            <div id="quality-map"></div>
        </div>

        {{-- 30-Day Trend Chart Card --}}
        <div class="aq-glass-panel p-5 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <i data-lucide="trending-up" class="w-4 h-4 text-cyan-600"></i>
                    Évolution de la conformité sur 30 jours
                </h3>
                @if($averageCompliance !== null)
                    <span class="badge-chip badge-green text-[10px]">
                        {{ $averageCompliance }}% moyenne
                    </span>
                @endif
            </div>
            <div class="aq-chart-container">
                <canvas id="qualityTrendChart"></canvas>
            </div>
        </div>

    </div>

    {{-- ══ 6. TABLE DES RÉSULTATS D'ANALYSES & FILTRES ═════════════════════════ --}}
    <div class="aq-glass-panel overflow-hidden">
        {{-- Card Header --}}
        <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex flex-wrap items-center justify-between gap-4 bg-slate-50/60 dark:bg-slate-850">
            <div>
                <h2 class="text-base font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                    <i data-lucide="clipboard-list" class="w-4 h-4 text-cyan-600"></i>
                    Résultats d'analyses &amp; Relevés terrain
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                    Historique certifié des mesures de conformité physico-chimique.
                </p>
            </div>
            <span class="font-mono text-xs font-bold px-3 py-1 rounded-lg bg-slate-200/60 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                {{ $paginator->total() }} relevé(s) au total
            </span>
        </div>

        {{-- Filter Bar --}}
        <div class="p-4 bg-white dark:bg-slate-900 border-b border-slate-100 dark:border-slate-800">
            <form method="GET" action="{{ route('manager.quality') }}" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-6 items-end">
                <div class="lg:col-span-2">
                    <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 mb-1">Recherche (Réf., point, zone)</label>
                    <div class="relative">
                        <i data-lucide="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none"></i>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Ex: WQR-2026, Grand Tunis..." class="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-cyan-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 mb-1">Station / Point</label>
                    <select name="point_mesure_id" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-cyan-500 cursor-pointer">
                        <option value="">Tous les points</option>
                        @foreach($allPointsForFilter ?? [] as $pt)
                            <option value="{{ $pt->id }}" @selected((string) request('point_mesure_id') === (string) $pt->id)>
                                {{ $pt->nom }} ({{ $pt->code }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 mb-1">Conformité</label>
                    <select name="status" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-cyan-500 cursor-pointer">
                        <option value="">Tous les états</option>
                        <option value="Compliant"     @selected(request('status') === 'Compliant')>Conforme</option>
                        <option value="Alert"         @selected(request('status') === 'Alert')>Alerte</option>
                        <option value="Non-Compliant" @selected(request('status') === 'Non-Compliant')>Non conforme</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 mb-1">Provenance</label>
                    <select name="source_donnee" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-cyan-500 cursor-pointer">
                        <option value="">Toutes sources</option>
                        <option value="manuel"      @selected(request('source_donnee') === 'manuel')>Manuel (terrain)</option>
                        <option value="laboratoire" @selected(request('source_donnee') === 'laboratoire')>Laboratoire</option>
                        <option value="csv"         @selected(request('source_donnee') === 'csv')>Import CSV</option>
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    <button type="submit" class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl bg-cyan-600 hover:bg-cyan-700 text-white text-xs font-bold shadow-sm transition cursor-pointer">
                        <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                        <span>Filtrer</span>
                    </button>
                    @if(request()->hasAny(['search','point_mesure_id','status','source_donnee']))
                        <a href="{{ route('manager.quality') }}" class="inline-flex items-center justify-center px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-50 text-slate-500 text-xs transition cursor-pointer" title="Réinitialiser">
                            <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Bulk Selection Bar --}}
        @if(auth()->user()?->hasPermission('measurements.delete'))
            <form id="bulk-delete-measurements" method="POST" action="{{ route('manager.quality.mesures.bulk-destroy') }}">
                @csrf
                @method('DELETE')
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 dark:border-slate-800 px-4 py-2.5 bg-slate-50/40 dark:bg-slate-850">
                    <div class="flex items-center gap-3">
                        <button type="button" id="select-all-measurements" class="rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 py-1.5 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-50 cursor-pointer">
                            Tout sélectionner (page)
                        </button>
                        <span id="selected-measurements-count" class="text-xs text-slate-500 font-medium">Aucune mesure sélectionnée</span>
                    </div>
                    <button type="submit" id="delete-selected-measurements" disabled class="inline-flex items-center gap-1.5 rounded-lg bg-red-600 px-3 py-1.5 text-xs font-bold text-white shadow-sm transition hover:bg-red-700 disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer">
                        <i data-lucide="trash-2" class="h-3.5 w-3.5"></i>
                        <span>Supprimer la sélection</span>
                    </button>
                </div>
        @endif

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-850 border-b border-slate-100 dark:border-slate-800 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                    <tr>
                        @if(auth()->user()?->hasPermission('measurements.delete'))
                            <th class="w-10 px-3 py-3"><span class="sr-only">Sélection</span></th>
                        @endif
                        <th class="px-4 py-3">Réf. &amp; Point</th>
                        <th class="px-3 py-3">Zone</th>
                        <th class="px-3 py-3">Date</th>
                        <th class="px-3 py-3">pH</th>
                        <th class="px-3 py-3">Turbidité</th>
                        <th class="px-3 py-3">Chlore</th>
                        <th class="px-3 py-3">Plomb</th>
                        <th class="px-3 py-3">Nitrates</th>
                        <th class="px-3 py-3">Validation</th>
                        <th class="px-3 py-3">Provenance</th>
                        <th class="px-4 py-3 text-right">Conformité</th>
                        <th class="px-3 py-3 text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($records as $rec)
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition">
                            @if(auth()->user()?->hasPermission('measurements.delete'))
                                <td class="px-3 py-3 text-center">
                                    <input type="checkbox" name="mesure_ids[]" value="{{ $rec['measurement_id'] }}" class="measurement-row-checkbox h-4 w-4 rounded border-slate-300 text-cyan-600 focus:ring-cyan-500 cursor-pointer" aria-label="Sélectionner la mesure {{ $rec['id'] }}">
                                </td>
                            @endif

                            {{-- Reference & Point --}}
                            <td class="px-4 py-3">
                                <a href="{{ route('manager.quality.mesures.show', $rec['measurement_id']) }}" class="font-mono text-xs font-bold text-cyan-700 dark:text-cyan-400 hover:underline">
                                    {{ $rec['id'] }}
                                </a>
                                <div class="text-[11px] text-slate-500 mt-0.5 flex items-center gap-1">
                                    <i data-lucide="map-pin" class="w-3 h-3 text-slate-400"></i>
                                    @if($rec['point_mesure_id'])
                                        <a href="{{ route('manager.points-mesure.show', $rec['point_mesure_id']) }}" class="hover:text-cyan-700 dark:hover:text-cyan-300 font-medium">
                                            {{ $rec['sampling_point'] }}
                                        </a>
                                    @else
                                        <span>{{ $rec['sampling_point'] }}</span>
                                    @endif
                                </div>
                                @if($rec['infrastructure'])
                                    <div class="text-[10px] text-cyan-600 dark:text-cyan-400 font-medium">Infra: {{ $rec['infrastructure'] }}</div>
                                @endif
                            </td>

                            {{-- Zone --}}
                            <td class="px-3 py-3 text-slate-600 dark:text-slate-300 font-medium">
                                {{ $rec['zone'] }}
                            </td>

                            {{-- Date --}}
                            <td class="px-3 py-3 text-slate-500 whitespace-nowrap font-mono text-[11px]">
                                {{ $rec['date'] }}
                            </td>

                            {{-- pH --}}
                            <td class="px-3 py-3 font-mono font-bold {{ (float)$rec['pH'] < 6.5 || (float)$rec['pH'] > 8.5 ? 'text-red-600' : 'text-slate-800 dark:text-slate-200' }}">
                                {{ $rec['pH'] }}
                            </td>

                            {{-- Turbidite --}}
                            <td class="px-3 py-3 font-mono font-bold {{ (float)$rec['turbidity'] > 1.0 ? 'text-amber-600' : 'text-slate-800 dark:text-slate-200' }}">
                                {{ $rec['turbidity'] }} <span class="font-normal text-slate-400 text-[10px]">NTU</span>
                            </td>

                            {{-- Chlore --}}
                            <td class="px-3 py-3 font-mono font-bold {{ (float)$rec['residual_chlorine'] < 0.2 ? 'text-red-600' : 'text-slate-800 dark:text-slate-200' }}">
                                {{ $rec['residual_chlorine'] }} <span class="font-normal text-slate-400 text-[10px]">mg/L</span>
                            </td>

                            {{-- Plomb --}}
                            <td class="px-3 py-3 font-mono font-bold {{ (float)$rec['lead_pb'] > 15 ? 'text-red-600' : 'text-slate-800 dark:text-slate-200' }}">
                                {{ $rec['lead_pb'] }} <span class="font-normal text-slate-400 text-[10px]">ppb</span>
                            </td>

                            {{-- Nitrates (Must preserve exact "$rec['nitrates'] mg/L" for tests) --}}
                            <td class="px-3 py-3 font-mono text-slate-800 dark:text-slate-200 font-semibold">{{ $rec['nitrates'] }} mg/L</td>

                            {{-- Validation --}}
                            <td class="px-3 py-3">
                                @if($rec['is_verified'])
                                    <span class="badge-chip badge-green text-[10px]">
                                        <i data-lucide="check-circle-2" class="w-3 h-3"></i> Vérifié
                                    </span>
                                @else
                                    <span class="badge-chip badge-amber text-[10px]">
                                        <i data-lucide="clock" class="w-3 h-3"></i> En attente
                                    </span>
                                @endif
                            </td>

                            {{-- Provenance --}}
                            <td class="px-3 py-3">
                                @php $src = $rec['source_donnee'] ?? 'manuel'; @endphp
                                @if($src === 'laboratoire')
                                    <span class="badge-chip badge-blue text-[10px]">Laboratoire</span>
                                @elseif($src === 'simule' || $src === 'csv')
                                    <span class="badge-chip badge-blue text-[10px]">Import</span>
                                @else
                                    <span class="badge-chip badge-violet text-[10px]">Manuel</span>
                                @endif
                            </td>

                            {{-- Conformite --}}
                            <td class="px-4 py-3 text-right">
                                @php
                                    $compCls = $rec['status'] === 'Compliant'
                                        ? 'badge-green'
                                        : ($rec['status'] === 'Alert' ? 'badge-amber' : 'badge-red');
                                @endphp
                                <span class="badge-chip {{ $compCls }} text-[10px]">
                                    {{ $rec['status'] }} · {{ $rec['overall_compliance'] }}%
                                </span>
                            </td>

                            {{-- Action --}}
                            <td class="px-3 py-3 text-center">
                                <a href="{{ route('manager.quality.mesures.show', $rec['measurement_id']) }}" class="inline-flex items-center justify-center h-7 w-7 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-cyan-600 hover:text-white text-slate-600 dark:text-slate-300 transition" title="Consulter le relevé">
                                    <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ auth()->user()?->hasPermission('measurements.delete') ? 13 : 12 }}" class="text-center py-12 text-slate-400">
                                <i data-lucide="inbox" class="w-8 h-8 mx-auto mb-2 text-slate-300 dark:text-slate-600 block"></i>
                                <span>Aucune mesure enregistrée. Créez un point de prélèvement puis ajoutez une mesure.</span>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if(auth()->user()?->hasPermission('measurements.delete'))
            </form>

            {{-- Bulk delete confirmation modal --}}
            <div id="bulk-delete-confirmation" class="modal-overlay-blur fixed inset-0 z-50 hidden items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="bulk-delete-title">
                <div class="w-full max-w-md rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-2xl">
                    <div class="flex items-start gap-4">
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-red-100 dark:bg-red-900/40 text-red-600 dark:text-red-400">
                            <i data-lucide="trash-2" class="h-6 w-6"></i>
                        </div>
                        <div>
                            <h2 id="bulk-delete-title" class="text-lg font-bold text-slate-900 dark:text-white">Supprimer les mesures sélectionnées ?</h2>
                            <p id="bulk-delete-description" class="mt-1 text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                                Vous allez supprimer définitivement <strong id="bulk-delete-confirm-count" class="text-slate-900 dark:text-white">0 mesure</strong>. Cette action est irréversible.
                            </p>
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end gap-3">
                        <button type="button" id="cancel-bulk-delete" class="rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 px-4 py-2 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-50 cursor-pointer">
                            Annuler
                        </button>
                        <button type="button" id="confirm-bulk-delete" class="inline-flex items-center gap-1.5 rounded-xl bg-red-600 px-4 py-2 text-xs font-bold text-white shadow-md shadow-red-600/20 hover:bg-red-700 cursor-pointer">
                            <i data-lucide="trash-2" class="h-3.5 w-3.5"></i>
                            Supprimer
                        </button>
                    </div>
                </div>
            </div>
        @endif

        @if(isset($paginator) && $paginator->hasPages())
            <div class="px-4 py-3 border-t border-slate-100 dark:border-slate-800 bg-white dark:bg-slate-900">
                {{ $paginator->links('pagination::tailwind') }}
            </div>
        @endif
    </div>

    {{-- ══ 7. COMPOSANT COLLECTE & IMPORT DE DONNÉES ═══════════════════════════ --}}
    <x-manager.data-collection-card :points="$allPointsForFilter ?? []" />

    {{-- ══ 8. MODAL SEUILS RÉGLEMENTAIRES ══════════════════════════════════════ --}}
    <div id="threshold-modal" class="modal-overlay-blur hidden fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl max-w-md w-full p-6 space-y-4 shadow-2xl">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <i data-lucide="sliders" class="w-5 h-5 text-cyan-600"></i> Seuils de contrôle qualité
                </h3>
                <button type="button" onclick="closeThresholdModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-pointer">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <p class="text-xs text-slate-600 dark:text-slate-300 bg-cyan-50 dark:bg-cyan-950/30 border border-cyan-100 dark:border-cyan-900/40 rounded-xl px-3.5 py-2.5 leading-relaxed">
                Seuils configurés selon la norme tunisienne <strong>NT 09.14</strong> relative à la potabilité de l'eau distribuée.
            </p>

            <div class="space-y-2 text-xs">
                @foreach([
                    ['pH admissible',              config('water_quality.ph_min').' – '.config('water_quality.ph_max'), 'Plage standard'],
                    ['Turbidité alerte (NTU)',      '> '.config('water_quality.turbidite_alert'), 'Seuil vigilance'],
                    ['Turbidité critique (NTU)',   '> '.config('water_quality.turbidite_max'), 'Seuil d\'alerte'],
                    ['Chlore résiduel (mg/L)',      config('water_quality.chlore_min').' – '.config('water_quality.chlore_max'), 'Désinfection'],
                    ['Plomb seuil action (ppb)',    '≤ '.config('water_quality.plomb_max'), 'Métaux lourds'],
                ] as [$label, $val, $desc])
                    <div class="flex justify-between items-center py-2 border-b border-slate-100 dark:border-slate-800">
                        <div>
                            <span class="font-semibold text-slate-800 dark:text-slate-200 block">{{ $label }}</span>
                            <span class="text-[10px] text-slate-400">{{ $desc }}</span>
                        </div>
                        <span class="font-mono font-bold text-cyan-700 dark:text-cyan-300 text-xs">{{ $val }}</span>
                    </div>
                @endforeach
                <div class="flex justify-between items-center py-2">
                    <div>
                        <span class="font-semibold text-slate-800 dark:text-slate-200 block">Nitrates (mg/L)</span>
                        <span class="text-[10px] text-slate-400">Plafond sanitaire</span>
                    </div>
                    <span class="font-mono font-bold text-cyan-700 dark:text-cyan-300 text-xs">
                        {{ config('water_quality.nitrates_max') !== null ? '≤ '.config('water_quality.nitrates_max') : '— Non configuré' }}
                    </span>
                </div>
            </div>

            <div class="flex justify-end pt-2 border-t border-slate-100 dark:border-slate-800">
                <button type="button" onclick="closeThresholdModal()"
                        class="px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-bold transition cursor-pointer">
                    Fermer
                </button>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
/* ── Bulk selection logic ── */
(function () {
    const selectAllButton = document.getElementById('select-all-measurements');
    const deleteButton = document.getElementById('delete-selected-measurements');
    const selectedCount = document.getElementById('selected-measurements-count');
    const checkboxes = Array.from(document.querySelectorAll('.measurement-row-checkbox'));
    const form = document.getElementById('bulk-delete-measurements');
    const modal = document.getElementById('bulk-delete-confirmation');
    const cancelButton = document.getElementById('cancel-bulk-delete');
    const confirmButton = document.getElementById('confirm-bulk-delete');
    const confirmCount = document.getElementById('bulk-delete-confirm-count');

    if (!selectAllButton || !deleteButton || !selectedCount || !form || !modal) return;
    let confirmed = false;

    function refreshSelection() {
        const selected = checkboxes.filter(checkbox => checkbox.checked).length;
        selectedCount.textContent = selected === 0
            ? 'Aucune mesure sélectionnée'
            : `${selected} mesure(s) sélectionnée(s)`;
        deleteButton.disabled = selected === 0;
        selectAllButton.textContent = selected === checkboxes.length && checkboxes.length > 0
            ? 'Tout désélectionner'
            : 'Tout sélectionner (page)';
    }

    selectAllButton.addEventListener('click', function () {
        const shouldSelect = checkboxes.some(checkbox => !checkbox.checked);
        checkboxes.forEach(checkbox => { checkbox.checked = shouldSelect; });
        refreshSelection();
    });
    checkboxes.forEach(checkbox => checkbox.addEventListener('change', refreshSelection));
    form.addEventListener('submit', function (event) {
        if (confirmed) return;
        event.preventDefault();
        const count = checkboxes.filter(checkbox => checkbox.checked).length;
        confirmCount.textContent = count === 1 ? '1 mesure' : `${count} mesures`;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        cancelButton.focus();
    });
    function closeConfirmation() {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        deleteButton.focus();
    }
    cancelButton.addEventListener('click', closeConfirmation);
    confirmButton.addEventListener('click', function () {
        confirmed = true;
        form.requestSubmit();
    });
    modal.addEventListener('click', function (event) {
        if (event.target === modal) closeConfirmation();
    });
    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && !modal.classList.contains('hidden')) closeConfirmation();
    });
    refreshSelection();
})();

/* ── Helpers ── */
function openThresholdModal()  { document.getElementById('threshold-modal').classList.remove('hidden'); }
function closeThresholdModal() { document.getElementById('threshold-modal').classList.add('hidden'); }

function openThresholdModal() { document.getElementById('threshold-modal').classList.remove('hidden'); }
function closeThresholdModal() { document.getElementById('threshold-modal').classList.add('hidden'); }

/* ── Leaflet map ── */
(function () {
    const mapElement = document.getElementById('quality-map');
    if (!mapElement) return;

    const POINTS = @json($points);
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

/* ── Quality trend chart ── */
(function () {
    const canvas = document.getElementById('qualityTrendChart');
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    const data = @json($chartRecords);
    const isDark = document.documentElement.getAttribute('data-theme') === 'dark';

    const gridColor  = isDark ? 'rgba(255,255,255,0.06)' : 'rgba(15,42,67,0.06)';
    const labelColor = isDark ? '#94a3b8' : '#64748b';

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

    window.addEventListener('aq:themechange', function (e) {
        const dark = e.detail.theme === 'dark';
        Chart.defaults.color = dark ? '#94a3b8' : '#64748b';
    });
})();
</script>
@endpush
