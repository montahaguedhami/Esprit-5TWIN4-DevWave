@extends('layouts.manager')
@section('title', 'Mesure ' . $mesure->reference . ' — Qualité de l\'eau')

@push('styles')
<style>
/* ── Design System Variables & Tokens ── */
.aq-measure-detail {
    color: var(--text-primary, #152b42);
}

.aq-panel {
    background: var(--bg-card, #ffffff);
    border: 1px solid var(--border, #dce6ef);
    border-radius: 1.25rem;
    box-shadow: 0 4px 16px rgba(16, 42, 67, 0.04);
    transition: box-shadow 0.2s ease, border-color 0.2s ease;
}

[data-theme="dark"] .aq-panel {
    background: var(--bg-card, #0c1624);
    border-color: var(--border, #1e293b);
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.35);
}

.aq-telemetry-card {
    background: var(--bg-card, #ffffff);
    border: 1px solid var(--border, #dce6ef);
    border-radius: 1.125rem;
    padding: 1.25rem;
    position: relative;
    overflow: hidden;
    transition: transform 0.15s ease, box-shadow 0.15s ease, border-color 0.15s ease;
}

.aq-telemetry-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(16, 42, 67, 0.08);
}

[data-theme="dark"] .aq-telemetry-card {
    background: var(--bg-card, #0f1c2e);
    border-color: var(--border, #1e2e42);
}

[data-theme="dark"] .aq-telemetry-card:hover {
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.45);
    border-color: #0284c7;
}

/* ── Badges ── */
.aq-chip {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.25rem 0.625rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 700;
    line-height: 1;
    letter-spacing: 0.02em;
}

.aq-chip-green {
    background: rgba(16, 185, 129, 0.12);
    color: #059669;
    border: 1px solid rgba(16, 185, 129, 0.25);
}

[data-theme="dark"] .aq-chip-green {
    background: rgba(16, 185, 129, 0.18);
    color: #34d399;
    border-color: rgba(16, 185, 129, 0.35);
}

.aq-chip-amber {
    background: rgba(245, 158, 11, 0.12);
    color: #d97706;
    border: 1px solid rgba(245, 158, 11, 0.25);
}

[data-theme="dark"] .aq-chip-amber {
    background: rgba(245, 158, 11, 0.18);
    color: #fbbf24;
    border-color: rgba(245, 158, 11, 0.35);
}

.aq-chip-red {
    background: rgba(239, 68, 68, 0.12);
    color: #dc2626;
    border: 1px solid rgba(239, 68, 68, 0.25);
}

[data-theme="dark"] .aq-chip-red {
    background: rgba(239, 68, 68, 0.18);
    color: #f87171;
    border-color: rgba(239, 68, 68, 0.35);
}

.aq-chip-blue {
    background: rgba(2, 132, 199, 0.12);
    color: #0284c7;
    border: 1px solid rgba(2, 132, 199, 0.25);
}

[data-theme="dark"] .aq-chip-blue {
    background: rgba(2, 132, 199, 0.18);
    color: #38bdf8;
    border-color: rgba(2, 132, 199, 0.35);
}

.aq-chip-violet {
    background: rgba(139, 92, 246, 0.12);
    color: #7c3aed;
    border: 1px solid rgba(139, 92, 246, 0.25);
}

[data-theme="dark"] .aq-chip-violet {
    background: rgba(139, 92, 246, 0.18);
    color: #a78bfa;
    border-color: rgba(139, 92, 246, 0.35);
}

/* ── Progress bar indicator ── */
.aq-range-indicator {
    height: 6px;
    background: rgba(148, 163, 184, 0.2);
    border-radius: 9999px;
    overflow: hidden;
    position: relative;
    margin-top: 0.75rem;
}

.aq-range-fill {
    height: 100%;
    border-radius: 9999px;
    transition: width 0.5s ease-out;
}

/* ── Breadcrumb links ── */
.aq-crumb-link {
    color: var(--text-secondary, #64748b);
    transition: color 0.15s ease;
}
.aq-crumb-link:hover {
    color: #0284c7;
}

/* ── Delete Modal Backdrop ── */
.modal-backdrop-blur {
    background-color: rgba(15, 23, 42, 0.6);
    backdrop-filter: blur(6px);
}
</style>
@endpush

@section('manager-content')
@php
    $status = $mesure->status ?? 'Compliant';
    $isCompliant = $status === 'Compliant';
    $isAlert = $status === 'Alert';
    $isNonCompliant = $status === 'Non-Compliant';

    $provenance = match($mesure->source_donnee ?? 'manuel') {
        'laboratoire'           => 'Laboratoire certifié',
        'simule', 'simulation' => 'Réseau télémétrique',
        'csv'                   => 'Import de données (CSV/XLSX)',
        'data_gov_tn'           => 'Données publiques tunisiennes',
        'emodnet'               => 'EMODnet',
        default                 => $mesure->provider ? 'Import ' . $mesure->provider : 'Saisie terrain technicien',
    };

    // Calculate parameter statuses according to standard Tunisian norms
    // pH : 6.5 - 8.5
    $phState = 'normal';
    if ($mesure->ph !== null) {
        if ($mesure->ph < 6.5 || $mesure->ph > 8.5) $phState = 'danger';
        elseif ($mesure->ph < 6.8 || $mesure->ph > 8.2) $phState = 'warning';
    }

    // Turbidité : < 1.0 NTU (Alerte > 1.0, Max 2.0)
    $turbState = 'normal';
    if ($mesure->turbidite !== null) {
        if ($mesure->turbidite > 2.0) $turbState = 'danger';
        elseif ($mesure->turbidite > 1.0) $turbState = 'warning';
    }

    // Chlore résiduel : 0.2 - 2.0 mg/L
    $clState = 'normal';
    if ($mesure->chlore_residuel !== null) {
        if ($mesure->chlore_residuel < 0.2 || $mesure->chlore_residuel > 2.0) $clState = 'danger';
        elseif ($mesure->chlore_residuel < 0.3) $clState = 'warning';
    }

    // Plomb : <= 15 µg/L
    $pbState = 'normal';
    if ($mesure->plomb !== null) {
        if ($mesure->plomb > 15.0) $pbState = 'danger';
        elseif ($mesure->plomb > 10.0) $pbState = 'warning';
    }

    // Nitrates : <= 50 mg/L
    $no3State = 'normal';
    if ($mesure->nitrates !== null) {
        if ($mesure->nitrates > 50.0) $no3State = 'danger';
        elseif ($mesure->nitrates > 40.0) $no3State = 'warning';
    }
@endphp

<div class="aq-measure-detail mx-auto max-w-7xl space-y-6 pb-12">

    {{-- ══ 1. BREADCRUMBS ═════════════════════════════════════════════════════ --}}
    <nav class="flex items-center gap-2 text-xs font-medium text-slate-500" aria-label="Fil d'Ariane">
        <a href="{{ route('manager.dashboard') }}" class="aq-crumb-link flex items-center gap-1">
            <i data-lucide="home" class="h-3.5 w-3.5"></i>
            <span>Accueil</span>
        </a>
        <i data-lucide="chevron-right" class="h-3 w-3 text-slate-400"></i>
        <a href="{{ route('manager.quality') }}" class="aq-crumb-link flex items-center gap-1">
            <i data-lucide="droplets" class="h-3.5 w-3.5"></i>
            <span>Qualité de l'eau</span>
        </a>
        <i data-lucide="chevron-right" class="h-3 w-3 text-slate-400"></i>
        <span class="flex items-center gap-1 font-semibold text-slate-700 dark:text-slate-200">
            <i data-lucide="activity" class="h-3.5 w-3.5 text-cyan-500"></i>
            <span>Relevé {{ $mesure->reference }}</span>
        </span>
    </nav>

    {{-- Flash message --}}
    @if(session('success'))
        <div class="rounded-2xl border border-emerald-500/30 bg-emerald-500/10 p-4 text-sm font-medium text-emerald-800 dark:text-emerald-300 flex items-center gap-3 shadow-xs">
            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-emerald-500 text-white">
                <i data-lucide="check-circle" class="h-5 w-5"></i>
            </div>
            <div>{{ session('success') }}</div>
        </div>
    @endif

    {{-- ══ 2. HERO HEADER PANEL ═══════════════════════════════════════════════ --}}
    <div class="aq-panel p-6 sm:p-7 relative overflow-hidden">
        {{-- Decorative accent gradient circle --}}
        <div class="pointer-events-none absolute -right-16 -top-16 h-64 w-64 rounded-full bg-gradient-to-br from-cyan-500/10 via-blue-500/5 to-transparent blur-2xl"></div>

        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6 relative z-10">
            {{-- Left column : Identity & Statuses --}}
            <div class="space-y-3">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="aq-chip aq-chip-blue">
                        <i data-lucide="radio" class="h-3.5 w-3.5"></i>
                        Relevé physico-chimique
                    </span>
                    <span class="font-mono text-xs font-semibold px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                        ID #{{ $mesure->id }}
                    </span>
                </div>

                <div class="flex flex-wrap items-baseline gap-3">
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">
                        Mesure <span class="font-mono text-cyan-600 dark:text-cyan-400">{{ $mesure->reference }}</span>
                    </h1>
                </div>

                {{-- Metadata chips row --}}
                <div class="flex flex-wrap items-center gap-y-2 gap-x-4 text-xs text-slate-500 dark:text-slate-400">
                    @if($mesure->pointMesure)
                        <a href="{{ route('manager.points-mesure.show', $mesure->pointMesure) }}" class="inline-flex items-center gap-1.5 font-semibold text-cyan-700 dark:text-cyan-300 hover:underline">
                            <i data-lucide="map-pin" class="h-3.5 w-3.5 text-cyan-500"></i>
                            <span>{{ $mesure->pointMesure->nom }}</span>
                            <span class="font-mono text-[11px] font-normal text-slate-400">({{ $mesure->pointMesure->code }})</span>
                        </a>
                    @else
                        <span class="inline-flex items-center gap-1.5 text-slate-400">
                            <i data-lucide="map-pin" class="h-3.5 w-3.5"></i> Station non assignée
                        </span>
                    @endif

                    @if($mesure->pointMesure?->zone)
                        <span class="inline-flex items-center gap-1.5">
                            <i data-lucide="layers" class="h-3.5 w-3.5 text-slate-400"></i>
                            <span>Zone {{ $mesure->pointMesure->zone }}</span>
                        </span>
                    @endif

                    <span class="inline-flex items-center gap-1.5">
                        <i data-lucide="calendar" class="h-3.5 w-3.5 text-slate-400"></i>
                        <span>{{ $mesure->date_mesure->format('d/m/Y à H:i') }}</span>
                    </span>
                </div>

                {{-- Status indicators row --}}
                <div class="flex flex-wrap items-center gap-2 pt-1">
                    {{-- Conformity badge --}}
                    @if($isCompliant)
                        <span class="aq-chip aq-chip-green">
                            <i data-lucide="check-circle-2" class="h-3.5 w-3.5"></i>
                            Conforme · {{ number_format((float)$mesure->overall_compliance, 1) }}%
                        </span>
                    @elseif($isAlert)
                        <span class="aq-chip aq-chip-amber">
                            <i data-lucide="alert-triangle" class="h-3.5 w-3.5"></i>
                            Vigilance / Alerte · {{ number_format((float)$mesure->overall_compliance, 1) }}%
                        </span>
                    @else
                        <span class="aq-chip aq-chip-red">
                            <i data-lucide="alert-octagon" class="h-3.5 w-3.5"></i>
                            Non conforme · {{ number_format((float)$mesure->overall_compliance, 1) }}%
                        </span>
                    @endif

                    {{-- Verification badge --}}
                    @if($mesure->is_verified)
                        <span class="aq-chip aq-chip-green">
                            <i data-lucide="shield-check" class="h-3.5 w-3.5"></i>
                            Vérifiée{{ $mesure->verifier ? ' par '.$mesure->verifier : '' }}
                        </span>
                    @else
                        <span class="aq-chip aq-chip-amber">
                            <i data-lucide="clock" class="h-3.5 w-3.5"></i>
                            En attente de validation
                        </span>
                    @endif

                    {{-- Source provenance badge --}}
                    <span class="aq-chip aq-chip-violet">
                        <i data-lucide="database" class="h-3.5 w-3.5"></i>
                        {{ $provenance }}
                    </span>
                </div>
            </div>

            {{-- Right column : Action Buttons --}}
            <div class="flex flex-wrap items-center gap-2.5 shrink-0">
                @can('validate', $mesure)
                    @unless($mesure->is_verified)
                        <form method="POST" action="{{ route('manager.quality.mesures.validate', $mesure) }}" class="inline">
                            @csrf
                            <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-bold text-white shadow-md shadow-emerald-600/20 transition hover:bg-emerald-700 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 cursor-pointer">
                                <i data-lucide="check" class="h-4 w-4"></i>
                                <span>Valider</span>
                            </button>
                        </form>
                    @endunless
                @endcan

                @can('update', $mesure)
                    <a href="{{ route('manager.quality.mesures.edit', $mesure) }}" class="inline-flex items-center gap-2 rounded-xl bg-cyan-600 px-4 py-2.5 text-sm font-semibold text-white shadow-md shadow-cyan-600/20 transition hover:bg-cyan-700 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:ring-offset-2">
                        <i data-lucide="pencil" class="h-4 w-4"></i>
                        <span>Modifier</span>
                    </a>
                @endcan

                <a href="{{ route('manager.quality') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 px-4 py-2.5 text-sm font-semibold text-slate-700 dark:text-slate-200 shadow-xs transition hover:border-cyan-500 hover:text-cyan-700 dark:hover:text-cyan-300 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:ring-offset-2">
                    <i data-lucide="arrow-left" class="h-4 w-4"></i>
                    <span>Retour à la qualité</span>
                </a>
            </div>
        </div>
    </div>

    {{-- ══ 3. PARAMÈTRES PHYSICO-CHIMIQUES (GRID TELEMETRY CARDS) ════════════ --}}
    <div>
        <div class="flex items-center justify-between mb-3 px-1">
            <h2 class="text-sm font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 flex items-center gap-2">
                <i data-lucide="sliders" class="h-4 w-4 text-cyan-600"></i>
                Paramètres d'analyse & Conformité unitaire
            </h2>
            <span class="text-xs text-slate-400">Normes NT 09.14 (Eau de boisson)</span>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">

            {{-- 1. pH --}}
            <div class="aq-telemetry-card">
                <div class="flex items-start justify-between">
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Potentiel Hydrogène</span>
                        <div class="text-base font-extrabold text-slate-900 dark:text-white">pH</div>
                    </div>
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-violet-100 dark:bg-violet-900/40 text-violet-600 dark:text-violet-300">
                        <i data-lucide="flask-conical" class="h-5 w-5"></i>
                    </div>
                </div>
                <div class="mt-3 flex items-baseline justify-between">
                    <div class="text-2xl font-black font-mono text-slate-900 dark:text-white">
                        {{ $mesure->ph !== null ? number_format((float)$mesure->ph, 2) : '—' }}
                    </div>
                    @if($phState === 'normal')
                        <span class="aq-chip aq-chip-green text-[10px]">Conforme</span>
                    @elseif($phState === 'warning')
                        <span class="aq-chip aq-chip-amber text-[10px]">Vigilance</span>
                    @else
                        <span class="aq-chip aq-chip-red text-[10px]">Hors norme</span>
                    @endif
                </div>
                <div class="aq-range-indicator">
                    @php $phPct = $mesure->ph ? max(0, min(100, (($mesure->ph - 5) / 5) * 100)) : 0; @endphp
                    <div class="aq-range-fill {{ $phState === 'normal' ? 'bg-emerald-500' : ($phState === 'warning' ? 'bg-amber-500' : 'bg-red-500') }}" style="width: {{ $phPct }}%"></div>
                </div>
                <div class="mt-2 flex items-center justify-between text-[11px] text-slate-500">
                    <span>Norme : <strong>6.5 – 8.5</strong></span>
                    <span>Neutre: 7.0</span>
                </div>
            </div>

            {{-- 2. Turbidité --}}
            <div class="aq-telemetry-card">
                <div class="flex items-start justify-between">
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Clarté de l'eau</span>
                        <div class="text-base font-extrabold text-slate-900 dark:text-white">Turbidité</div>
                    </div>
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-cyan-100 dark:bg-cyan-900/40 text-cyan-600 dark:text-cyan-300">
                        <i data-lucide="droplets" class="h-5 w-5"></i>
                    </div>
                </div>
                <div class="mt-3 flex items-baseline justify-between">
                    <div class="text-2xl font-black font-mono text-slate-900 dark:text-white">
                        {{ $mesure->turbidite !== null ? number_format((float)$mesure->turbidite, 2) : '—' }}
                        <span class="text-xs font-normal text-slate-500">NTU</span>
                    </div>
                    @if($turbState === 'normal')
                        <span class="aq-chip aq-chip-green text-[10px]">Excellente</span>
                    @elseif($turbState === 'warning')
                        <span class="aq-chip aq-chip-amber text-[10px]">Alerte seuil</span>
                    @else
                        <span class="aq-chip aq-chip-red text-[10px]">Trouble</span>
                    @endif
                </div>
                <div class="aq-range-indicator">
                    @php $turbPct = $mesure->turbidite ? max(0, min(100, ($mesure->turbidite / 3) * 100)) : 0; @endphp
                    <div class="aq-range-fill {{ $turbState === 'normal' ? 'bg-emerald-500' : ($turbState === 'warning' ? 'bg-amber-500' : 'bg-red-500') }}" style="width: {{ $turbPct }}%"></div>
                </div>
                <div class="mt-2 flex items-center justify-between text-[11px] text-slate-500">
                    <span>Cible : <strong>&lt; 1.0 NTU</strong></span>
                    <span>Seuil critique: 2.0</span>
                </div>
            </div>

            {{-- 3. Chlore résiduel --}}
            <div class="aq-telemetry-card">
                <div class="flex items-start justify-between">
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Protection bactériologique</span>
                        <div class="text-base font-extrabold text-slate-900 dark:text-white">Chlore résiduel</div>
                    </div>
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-teal-100 dark:bg-teal-900/40 text-teal-600 dark:text-teal-300">
                        <i data-lucide="shield-check" class="h-5 w-5"></i>
                    </div>
                </div>
                <div class="mt-3 flex items-baseline justify-between">
                    <div class="text-2xl font-black font-mono text-slate-900 dark:text-white">
                        {{ $mesure->chlore_residuel !== null ? number_format((float)$mesure->chlore_residuel, 2) : '—' }}
                        <span class="text-xs font-normal text-slate-500">mg/L</span>
                    </div>
                    @if($clState === 'normal')
                        <span class="aq-chip aq-chip-green text-[10px]">Optimal</span>
                    @elseif($clState === 'warning')
                        <span class="aq-chip aq-chip-amber text-[10px]">Chlore faible</span>
                    @else
                        <span class="aq-chip aq-chip-red text-[10px]">Hors plage</span>
                    @endif
                </div>
                <div class="aq-range-indicator">
                    @php $clPct = $mesure->chlore_residuel ? max(0, min(100, ($mesure->chlore_residuel / 2.5) * 100)) : 0; @endphp
                    <div class="aq-range-fill {{ $clState === 'normal' ? 'bg-emerald-500' : ($clState === 'warning' ? 'bg-amber-500' : 'bg-red-500') }}" style="width: {{ $clPct }}%"></div>
                </div>
                <div class="mt-2 flex items-center justify-between text-[11px] text-slate-500">
                    <span>Norme : <strong>0.20 – 2.00 mg/L</strong></span>
                    <span>Sécurité sanitaire</span>
                </div>
            </div>

            {{-- 4. Plomb (Pb) --}}
            <div class="aq-telemetry-card">
                <div class="flex items-start justify-between">
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Métaux lourds</span>
                        <div class="text-base font-extrabold text-slate-900 dark:text-white">Plomb (Pb)</div>
                    </div>
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-rose-100 dark:bg-rose-900/40 text-rose-600 dark:text-rose-300">
                        <i data-lucide="shield-alert" class="h-5 w-5"></i>
                    </div>
                </div>
                <div class="mt-3 flex items-baseline justify-between">
                    <div class="text-2xl font-black font-mono text-slate-900 dark:text-white">
                        {{ $mesure->plomb !== null ? number_format((float)$mesure->plomb, 3) : '—' }}
                        <span class="text-xs font-normal text-slate-500">µg/L</span>
                    </div>
                    @if($pbState === 'normal')
                        <span class="aq-chip aq-chip-green text-[10px]">Inoffensif</span>
                    @elseif($pbState === 'warning')
                        <span class="aq-chip aq-chip-amber text-[10px]">Surveillance</span>
                    @else
                        <span class="aq-chip aq-chip-red text-[10px]">Toxique</span>
                    @endif
                </div>
                <div class="aq-range-indicator">
                    @php $pbPct = $mesure->plomb ? max(0, min(100, ($mesure->plomb / 20) * 100)) : 0; @endphp
                    <div class="aq-range-fill {{ $pbState === 'normal' ? 'bg-emerald-500' : ($pbState === 'warning' ? 'bg-amber-500' : 'bg-red-500') }}" style="width: {{ $pbPct }}%"></div>
                </div>
                <div class="mt-2 flex items-center justify-between text-[11px] text-slate-500">
                    <span>Plafond : <strong>&lt; 15.0 µg/L</strong></span>
                    <span>Teneur en traces</span>
                </div>
            </div>

            {{-- 5. Nitrates (NO3) --}}
            <div class="aq-telemetry-card">
                <div class="flex items-start justify-between">
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Pollution agricole</span>
                        <div class="text-base font-extrabold text-slate-900 dark:text-white">Nitrates (NO₃)</div>
                    </div>
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-100 dark:bg-blue-900/40 text-blue-600 dark:text-blue-300">
                        <i data-lucide="test-tube-2" class="h-5 w-5"></i>
                    </div>
                </div>
                <div class="mt-3 flex items-baseline justify-between">
                    <div class="text-2xl font-black font-mono text-slate-900 dark:text-white">
                        {{ $mesure->nitrates !== null ? number_format((float)$mesure->nitrates, 2) : '—' }}
                        <span class="text-xs font-normal text-slate-500">mg/L</span>
                    </div>
                    @if($no3State === 'normal')
                        <span class="aq-chip aq-chip-green text-[10px]">Conforme</span>
                    @elseif($no3State === 'warning')
                        <span class="aq-chip aq-chip-amber text-[10px]">Élevé</span>
                    @else
                        <span class="aq-chip aq-chip-red text-[10px]">Dépassement</span>
                    @endif
                </div>
                <div class="aq-range-indicator">
                    @php $no3Pct = $mesure->nitrates ? max(0, min(100, ($mesure->nitrates / 60) * 100)) : 0; @endphp
                    <div class="aq-range-fill {{ $no3State === 'normal' ? 'bg-emerald-500' : ($no3State === 'warning' ? 'bg-amber-500' : 'bg-red-500') }}" style="width: {{ $no3Pct }}%"></div>
                </div>
                <div class="mt-2 flex items-center justify-between text-[11px] text-slate-500">
                    <span>Plafond : <strong>&lt; 50.0 mg/L</strong></span>
                    <span>Seuil OMS / SONEDE</span>
                </div>
            </div>

            {{-- 6. Conformité globale --}}
            <div class="aq-telemetry-card bg-gradient-to-br from-slate-50 to-white dark:from-slate-900 dark:to-slate-800/90 border-cyan-500/20">
                <div class="flex items-start justify-between">
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-cyan-600 dark:text-cyan-400">Indicateur global</span>
                        <div class="text-base font-extrabold text-slate-900 dark:text-white">Score de conformité</div>
                    </div>
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-cyan-100 dark:bg-cyan-900/40 text-cyan-600 dark:text-cyan-300">
                        <i data-lucide="gauge" class="h-5 w-5"></i>
                    </div>
                </div>
                <div class="mt-3 flex items-baseline justify-between">
                    <div class="text-2xl font-black font-mono text-slate-900 dark:text-white">
                        {{ number_format((float)$mesure->overall_compliance, 1) }} %
                    </div>
                    @if($isCompliant)
                        <span class="aq-chip aq-chip-green text-[10px]">Eau Conforme</span>
                    @elseif($isAlert)
                        <span class="aq-chip aq-chip-amber text-[10px]">Alerte Qualité</span>
                    @else
                        <span class="aq-chip aq-chip-red text-[10px]">Non conforme</span>
                    @endif
                </div>
                <div class="aq-range-indicator">
                    <div class="aq-range-fill {{ $isCompliant ? 'bg-emerald-500' : ($isAlert ? 'bg-amber-500' : 'bg-red-500') }}" style="width: {{ min(100, (float)$mesure->overall_compliance) }}%"></div>
                </div>
                <div class="mt-2 flex items-center justify-between text-[11px] text-slate-500">
                    <span>État : <strong>{{ $mesure->status ?? 'Évalué' }}</strong></span>
                    <span>Agrégation pondérée</span>
                </div>
            </div>

        </div>
    </div>

    {{-- ══ 4. TWO-COLUMN STATION & TRACEABILITY METADATA ══════════════════════ --}}
    <div class="grid gap-6 lg:grid-cols-2">

        {{-- Station & Infrastructure context --}}
        <div class="aq-panel p-6">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3 mb-4">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <i data-lucide="map-pin" class="h-4 w-4 text-cyan-600"></i>
                    Point de prélèvement & Ouvrage
                </h3>
                @if($mesure->pointMesure)
                    <a href="{{ route('manager.points-mesure.show', $mesure->pointMesure) }}" class="text-xs font-semibold text-cyan-600 dark:text-cyan-400 hover:underline inline-flex items-center gap-1">
                        Fiche point <i data-lucide="external-link" class="h-3 w-3"></i>
                    </a>
                @endif
            </div>

            @if($mesure->pointMesure)
                <div class="grid grid-cols-2 gap-4 text-xs">
                    <div>
                        <span class="text-slate-400 block text-[11px] uppercase tracking-wide">Nom de la station</span>
                        <span class="font-semibold text-slate-800 dark:text-slate-100 text-sm mt-0.5 block">{{ $mesure->pointMesure->nom }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[11px] uppercase tracking-wide">Code officiel</span>
                        <span class="font-mono font-semibold text-cyan-700 dark:text-cyan-300 text-sm mt-0.5 block">{{ $mesure->pointMesure->code }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[11px] uppercase tracking-wide">Zone hydrographique</span>
                        <span class="font-medium text-slate-700 dark:text-slate-200 mt-0.5 block">{{ $mesure->pointMesure->zone ?: 'Non renseignée' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[11px] uppercase tracking-wide">Type d'ouvrage</span>
                        <span class="font-medium text-slate-700 dark:text-slate-200 mt-0.5 block">{{ $mesure->pointMesure->type ?: 'Station de surveillance' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[11px] uppercase tracking-wide">Infrastructure rattachée</span>
                        <span class="font-medium text-slate-700 dark:text-slate-200 mt-0.5 block">
                            {{ $mesure->pointMesure->infrastructure?->nom ?? 'Réseau public direct' }}
                        </span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[11px] uppercase tracking-wide">Coordonnées GPS</span>
                        <span class="font-mono text-slate-600 dark:text-slate-300 mt-0.5 block">
                            @if($mesure->pointMesure->latitude && $mesure->pointMesure->longitude)
                                {{ number_format($mesure->pointMesure->latitude, 4) }}, {{ number_format($mesure->pointMesure->longitude, 4) }}
                            @else
                                Position standard
                            @endif
                        </span>
                    </div>
                </div>
            @else
                <p class="text-xs text-slate-500 py-3">Aucun point de mesure spécifique n'est relié à cette observation.</p>
            @endif
        </div>

        {{-- Traçabilité & Acquisition context --}}
        <div class="aq-panel p-6">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3 mb-4">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <i data-lucide="clipboard-check" class="h-4 w-4 text-cyan-600"></i>
                    Audit, Traçabilité & Acquisition
                </h3>
                <span class="text-[11px] font-mono text-slate-400">REF: {{ $mesure->reference }}</span>
            </div>

            <div class="grid grid-cols-2 gap-4 text-xs">
                <div>
                    <span class="text-slate-400 block text-[11px] uppercase tracking-wide">Date & heure du relevé</span>
                    <span class="font-semibold text-slate-800 dark:text-slate-100 mt-0.5 block">{{ $mesure->date_mesure->format('d/m/Y à H:i:s') }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block text-[11px] uppercase tracking-wide">Provenance des données</span>
                    <span class="font-semibold text-slate-800 dark:text-slate-100 mt-0.5 block">{{ $provenance }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block text-[11px] uppercase tracking-wide">Statut de vérification</span>
                    <span class="mt-0.5 block">
                        @if($mesure->is_verified)
                            <span class="text-emerald-600 dark:text-emerald-400 font-semibold inline-flex items-center gap-1">
                                <i data-lucide="check" class="h-3 w-3"></i> Vérifiée
                            </span>
                        @else
                            <span class="text-amber-600 dark:text-amber-400 font-semibold inline-flex items-center gap-1">
                                <i data-lucide="clock" class="h-3 w-3"></i> À valider
                            </span>
                        @endif
                    </span>
                </div>
                <div>
                    <span class="text-slate-400 block text-[11px] uppercase tracking-wide">Vérificateur responsable</span>
                    <span class="font-medium text-slate-700 dark:text-slate-200 mt-0.5 block">{{ $mesure->verifier ?: '—' }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block text-[11px] uppercase tracking-wide">Identifiant externe</span>
                    <span class="font-mono text-slate-600 dark:text-slate-400 mt-0.5 block truncate" title="{{ $mesure->external_id }}">
                        {{ $mesure->external_id ?: 'Création manuelle' }}
                    </span>
                </div>
                <div>
                    <span class="text-slate-400 block text-[11px] uppercase tracking-wide">Enregistrement système</span>
                    <span class="font-mono text-slate-600 dark:text-slate-400 mt-0.5 block">
                        {{ $mesure->created_at ? $mesure->created_at->format('d/m/Y H:i') : '—' }}
                    </span>
                </div>
            </div>
        </div>

    </div>

    {{-- ══ 5. ANALYSE INTELLIGENTE DU POINT DE MESURE ═════════════════════════ --}}
    @if($mesure->pointMesure)
        <div class="aq-panel p-6 sm:p-7 border-violet-200/80 dark:border-violet-900/60 relative">
            <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-100 dark:border-slate-800 pb-5">
                <div class="flex items-center gap-3.5">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-violet-500 to-indigo-700 text-white shadow-md shadow-violet-500/20">
                        <i data-lucide="sparkles" class="h-6 w-6"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="aq-chip aq-chip-violet text-[10px]">
                                IA &amp; RAG Prédictif
                            </span>
                            <span class="font-mono text-xs font-semibold text-slate-500">{{ $mesure->pointMesure->code }}</span>
                        </div>
                        <h2 class="mt-1 text-lg sm:text-xl font-extrabold text-slate-900 dark:text-white">
                            Analyse globale du point : {{ $mesure->pointMesure->nom }}
                        </h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            {{ $mesure->pointMesure->zone ? 'Zone '.$mesure->pointMesure->zone.' · ' : '' }}Diagnostic continu, détection d'anomalies et recommandations opérationnelles
                        </p>
                    </div>
                </div>

                {{-- Interactive Period Selector & Refresh Button --}}
                <div class="flex flex-wrap items-center gap-2.5">
                    <div class="flex items-center gap-1.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/80 px-3 py-1.5 shadow-xs">
                        <i data-lucide="calendar" class="h-3.5 w-3.5 text-slate-400"></i>
                        <label for="point-analysis-period" class="text-xs font-semibold text-slate-600 dark:text-slate-300">Période :</label>
                        <select id="point-analysis-period" class="bg-transparent text-xs font-bold text-slate-800 dark:text-slate-100 focus:outline-none cursor-pointer">
                            <option value="30" class="dark:bg-slate-800" selected>30 derniers jours</option>
                            <option value="7" class="dark:bg-slate-800">7 derniers jours</option>
                            <option value="90" class="dark:bg-slate-800">90 derniers jours</option>
                            <option value="365" class="dark:bg-slate-800">1 an</option>
                            <option value="all" class="dark:bg-slate-800">Historique complet</option>
                        </select>
                    </div>

                    <button type="button" id="btn-run-point-analysis" class="inline-flex items-center gap-2 rounded-xl bg-violet-600 px-4 py-2 text-xs font-bold text-white shadow-md shadow-violet-600/20 transition hover:bg-violet-700 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-violet-500 focus:ring-offset-2 cursor-pointer">
                        <i data-lucide="refresh-cw" class="h-3.5 w-3.5" id="analysis-spinner-icon"></i>
                        <span id="analysis-btn-text">Actualiser l'analyse</span>
                    </button>
                </div>
            </div>

            {{-- Dynamic analysis content container --}}
            <div id="point-analysis-content" class="mt-6 space-y-6">
                <div class="flex items-center justify-center py-12 text-slate-400">
                    <div class="flex flex-col items-center gap-3">
                        <div class="h-8 w-8 animate-spin rounded-full border-3 border-violet-600 border-t-transparent"></div>
                        <span class="text-xs font-semibold text-slate-600 dark:text-slate-400">Chargement du diagnostic réseau pour ce point de mesure...</span>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="aq-panel p-6 text-sm text-slate-600 dark:text-slate-300 flex items-center gap-3">
            <i data-lucide="info" class="h-5 w-5 text-slate-400 shrink-0"></i>
            <div>Cette mesure n'est rattachée à aucun point de mesure fixe du réseau. L'analyse temporelle et prédictive n'est pas applicable.</div>
        </div>
    @endif

    {{-- ══ 6. ZONE DE DANGER / SUPPRESSION ════════════════════════════════════ --}}
    @can('delete', $mesure)
        <div class="aq-panel p-5 border-red-200/80 dark:border-red-900/40 bg-red-50/30 dark:bg-red-950/20 flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-red-100 dark:bg-red-900/40 text-red-600 dark:text-red-400">
                    <i data-lucide="alert-triangle" class="h-5 w-5"></i>
                </div>
                <div>
                    <h4 class="text-sm font-bold text-slate-900 dark:text-white">Zone d'administration de la mesure</h4>
                    <p class="text-xs text-slate-500 dark:text-slate-400">La suppression effacera définitivement ce relevé de l'historique et recalculera les métriques du point.</p>
                </div>
            </div>

            <button type="button" onclick="document.getElementById('delete-measure-modal').classList.remove('hidden')" class="inline-flex items-center gap-2 rounded-xl border border-red-300 dark:border-red-800 bg-white dark:bg-slate-900 px-4 py-2 text-xs font-bold text-red-700 dark:text-red-400 shadow-xs transition hover:bg-red-600 hover:text-white hover:border-red-600 cursor-pointer">
                <i data-lucide="trash-2" class="h-3.5 w-3.5"></i>
                <span>Supprimer la mesure</span>
            </button>
        </div>

        {{-- Modal de confirmation de suppression --}}
        <div id="delete-measure-modal" class="modal-backdrop-blur fixed inset-0 z-50 hidden flex items-center justify-center p-4">
            <div class="w-full max-w-md rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-2xl">
                <div class="flex items-start gap-4">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-red-100 dark:bg-red-900/40 text-red-600 dark:text-red-400">
                        <i data-lucide="trash-2" class="h-6 w-6"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white">Supprimer cette mesure ?</h3>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                            Êtes-vous sûr de vouloir supprimer définitivement la mesure <strong class="text-slate-900 dark:text-white font-mono">{{ $mesure->reference }}</strong> ? Cette action est irréversible.
                        </p>
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('delete-measure-modal').classList.add('hidden')" class="rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 px-4 py-2 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 cursor-pointer">
                        Annuler
                    </button>
                    <form method="POST" action="{{ route('manager.quality.mesures.destroy', $mesure) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="inline-flex items-center gap-1.5 rounded-xl bg-red-600 px-4 py-2 text-xs font-bold text-white shadow-md shadow-red-600/20 hover:bg-red-700 cursor-pointer">
                            <i data-lucide="trash-2" class="h-3.5 w-3.5"></i>
                            Confirmer la suppression
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @endcan

</div>

{{-- ══ 7. JAVASCRIPT ANALYSE INTELLIGENTE DU POINT ══════════════════════════ --}}
@if($mesure->pointMesure)
@push('scripts')
<script>
(function () {
    const pointId = @json($mesure->pointMesure->id);
    const contentBox = document.getElementById('point-analysis-content');
    const periodSelect = document.getElementById('point-analysis-period');
    const refreshBtn = document.getElementById('btn-run-point-analysis');
    const spinnerIcon = document.getElementById('analysis-spinner-icon');
    const btnText = document.getElementById('analysis-btn-text');

    const paramLabels = {
        ph: { label: 'pH', unit: '', norm: '6.5 – 8.5' },
        turbidite: { label: 'Turbidité', unit: 'NTU', norm: '< 1.0 NTU' },
        chlore_residuel: { label: 'Chlore résiduel', unit: 'mg/L', norm: '0.2 – 2.0 mg/L' },
        plomb: { label: 'Plomb', unit: 'µg/L', norm: '< 15 µg/L' },
        nitrates: { label: 'Nitrates', unit: 'mg/L', norm: '< 50 mg/L' }
    };

    function escapeHtml(v) {
        return String(v ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'})[c]);
    }

    async function loadAnalysis() {
        if (!pointId || !contentBox) return;

        const periodVal = periodSelect ? periodSelect.value : '30';
        const isAll = periodVal === 'all';
        const url = `/manager/quality/${encodeURIComponent(pointId)}/analyse?${isAll ? 'all=1' : `days=${encodeURIComponent(periodVal)}`}`;

        if (spinnerIcon) spinnerIcon.classList.add('animate-spin');
        if (btnText) btnText.textContent = 'Calcul en cours…';
        if (refreshBtn) refreshBtn.disabled = true;

        try {
            const res = await fetch(url, { headers: { Accept: 'application/json' } });
            const payload = await res.json();
            if (!res.ok) throw new Error(payload.message || 'Impossible de générer l’analyse.');

            const analysis = payload.analysis || {};
            const triage = analysis.triage || {};
            const stats = analysis.parameters || {};
            const anomalies = analysis.anomalies || [];
            const forecasts = analysis.forecast || {};
            const recommendations = analysis.recommendations || [];
            const latest = analysis.latest_measurements || [];

            // Sévérité & Triage
            let sevBadge = '';
            let sevBorder = 'border-slate-200 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-900/60';
            if (triage.severity === 'critique') {
                sevBorder = 'border-red-200 dark:border-red-900/60 bg-red-50/80 dark:bg-red-950/30';
                sevBadge = '<span class="aq-chip aq-chip-red"><i data-lucide="alert-octagon" class="h-3.5 w-3.5"></i> État Critique</span>';
            } else if (triage.severity === 'alerte') {
                sevBorder = 'border-amber-200 dark:border-amber-900/60 bg-amber-50/80 dark:bg-amber-950/30';
                sevBadge = '<span class="aq-chip aq-chip-amber"><i data-lucide="alert-triangle" class="h-3.5 w-3.5"></i> État Vigilance</span>';
            } else {
                sevBorder = 'border-emerald-200 dark:border-emerald-900/60 bg-emerald-50/80 dark:bg-emerald-950/30';
                sevBadge = '<span class="aq-chip aq-chip-green"><i data-lucide="check-circle-2" class="h-3.5 w-3.5"></i> État Conforme</span>';
            }

            // Cartes paramètres moyennes
            const statCardsHtml = Object.entries(paramLabels).map(([key, meta]) => {
                const s = stats[key];
                return `
                    <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-3.5 shadow-xs">
                        <div class="flex items-center justify-between text-xs font-bold text-slate-500 uppercase">
                            <span>${meta.label}</span>
                            <span class="text-[10px] font-normal text-slate-400">Norme : ${meta.norm}</span>
                        </div>
                        ${s ? `
                            <div class="mt-2 text-xl font-black font-mono text-slate-900 dark:text-white">
                                ${escapeHtml(s.avg)} <span class="text-xs font-normal text-slate-500">${meta.unit}</span>
                            </div>
                            <div class="mt-1 flex items-center justify-between text-[11px] text-slate-500">
                                <span>Min: <strong>${escapeHtml(s.min)}</strong> · Max: <strong>${escapeHtml(s.max)}</strong></span>
                                <span class="rounded bg-slate-100 dark:bg-slate-800 px-1.5 py-0.5 font-semibold text-slate-600 dark:text-slate-300">${escapeHtml(s.count)} obs.</span>
                            </div>
                        ` : `
                            <div class="mt-2 text-xs font-medium text-slate-400">Aucune donnée sur la période</div>
                        `}
                    </div>
                `;
            }).join('');

            // Anomalies
            let anomaliesHtml = '';
            if (anomalies.length > 0) {
                anomaliesHtml = `
                    <div class="space-y-2">
                        <h3 class="text-xs font-bold uppercase tracking-wide text-red-600 dark:text-red-400 flex items-center gap-1.5">
                            <i data-lucide="alert-circle" class="h-4 w-4"></i> Anomalies et dépassements détectés sur le point
                        </h3>
                        <div class="grid gap-2 sm:grid-cols-2">
                            ${anomalies.map(a => `
                                <div class="rounded-xl border border-red-200 dark:border-red-900/50 bg-red-50/80 dark:bg-red-950/30 p-3 text-xs text-red-950 dark:text-red-200 flex items-start gap-2.5">
                                    <div class="flex h-6 w-6 shrink-0 items-center justify-center rounded-lg bg-red-200/80 dark:bg-red-900/60 text-red-700 dark:text-red-300 mt-0.5">
                                        <i data-lucide="alert-circle" class="h-3.5 w-3.5"></i>
                                    </div>
                                    <div>
                                        <strong class="font-bold text-red-900 dark:text-red-100">${escapeHtml(paramLabels[a.param]?.label || a.param)}</strong> : ${escapeHtml(a.detail)}
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                `;
            }

            // Tendances prévisionnelles
            let forecastHtml = '';
            const forecastEntries = Object.entries(forecasts);
            if (forecastEntries.length > 0) {
                forecastHtml = `
                    <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 p-4">
                        <h3 class="text-xs font-bold uppercase tracking-wide text-slate-700 dark:text-slate-300 mb-2.5 flex items-center gap-1.5">
                            <i data-lucide="trending-up" class="h-3.5 w-3.5 text-cyan-600"></i> Tendances prévisionnelles calculées
                        </h3>
                        <div class="grid gap-2.5 sm:grid-cols-2 lg:grid-cols-3">
                            ${forecastEntries.map(([key, item]) => {
                                const trend = item.trend || 'stable';
                                const isUp = trend === 'hausse';
                                const isDown = trend === 'baisse';
                                const trendIcon = isUp ? 'trending-up' : (isDown ? 'trending-down' : 'minus');
                                const trendColor = isUp ? 'text-amber-600' : (isDown ? 'text-blue-600' : 'text-slate-500');
                                return `
                                    <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-3 text-xs">
                                        <div class="flex items-center justify-between">
                                            <span class="font-bold text-slate-800 dark:text-slate-200">${paramLabels[key]?.label || key}</span>
                                            <span class="font-mono font-bold text-slate-900 dark:text-white">${item.predicted === null ? '—' : escapeHtml(item.predicted) + ' ' + (paramLabels[key]?.unit || '')}</span>
                                        </div>
                                        <div class="flex items-center gap-1 mt-1.5 text-[11px] ${trendColor} font-semibold">
                                            <i data-lucide="${trendIcon}" class="h-3 w-3"></i>
                                            <span>Tendance : ${escapeHtml(trend)}</span>
                                        </div>
                                    </div>
                                `;
                            }).join('')}
                        </div>
                    </div>
                `;
            }

            // Recommandations
            let recommendationsHtml = '';
            if (recommendations.length > 0) {
                recommendationsHtml = `
                    <div class="rounded-2xl border border-violet-100 dark:border-violet-900/40 bg-violet-50/50 dark:bg-violet-950/20 p-4">
                        <h3 class="text-xs font-bold uppercase tracking-wide text-violet-900 dark:text-violet-300 mb-2 flex items-center gap-2">
                            <i data-lucide="check-square" class="h-4 w-4 text-violet-600"></i>
                            Recommandations opérationnelles de traitement &amp; surveillance
                        </h3>
                        <ul class="space-y-1.5 text-xs text-slate-700 dark:text-slate-300">
                            ${recommendations.map(r => `
                                <li class="flex items-start gap-2">
                                    <i data-lucide="arrow-right-circle" class="h-3.5 w-3.5 text-violet-600 shrink-0 mt-0.5"></i>
                                    <span>${escapeHtml(r)}</span>
                                </li>
                            `).join('')}
                        </ul>
                    </div>
                `;
            }

            // Tableau des dernières mesures
            let tableRows = '';
            if (latest.length > 0) {
                tableRows = latest.map(m => {
                    const statusClass = (m.status === 'Compliant' || m.status === 'Conforme')
                        ? 'aq-chip-green'
                        : ((m.status === 'Alert' || m.status === 'Alerte') ? 'aq-chip-amber' : 'aq-chip-red');
                    return `
                        <tr class="border-t border-slate-100 dark:border-slate-800 text-xs hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition">
                            <td class="whitespace-nowrap px-3.5 py-2.5 font-medium text-slate-700 dark:text-slate-300">${escapeHtml(m.date)}</td>
                            <td class="px-3 py-2 font-mono font-semibold">${m.ph === null ? '—' : escapeHtml(m.ph)}</td>
                            <td class="px-3 py-2 font-mono font-semibold">${m.turbidite === null ? '—' : escapeHtml(m.turbidite)}</td>
                            <td class="px-3 py-2 font-mono font-semibold">${m.chlore_residuel === null ? '—' : escapeHtml(m.chlore_residuel)}</td>
                            <td class="px-3 py-2 font-mono font-semibold">${m.plomb === null ? '—' : escapeHtml(m.plomb)}</td>
                            <td class="px-3 py-2 font-mono font-semibold">${m.nitrates === null ? '—' : escapeHtml(m.nitrates)}</td>
                            <td class="px-3 py-2">
                                <span class="aq-chip ${statusClass} text-[10px] px-2 py-0.5">${escapeHtml(m.status || 'Évaluée')}</span>
                            </td>
                        </tr>
                    `;
                }).join('');
            }

            // Assemblage complet
            contentBox.innerHTML = `
                {{-- Synthèse et Triage --}}
                <div class="rounded-2xl border ${sevBorder} p-5 space-y-3.5">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div class="flex flex-wrap items-center gap-2">
                            ${sevBadge}
                            ${triage.priority ? `<span class="rounded-lg bg-white/90 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 px-2.5 py-1 text-xs font-semibold text-slate-700 dark:text-slate-300">Priorité : <strong>${escapeHtml(triage.priority)}</strong></span>` : ''}
                            ${triage.deadline_hours ? `<span class="rounded-lg bg-white/90 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 px-2.5 py-1 text-xs font-semibold text-slate-700 dark:text-slate-300">Intervention : <strong>sous ${escapeHtml(triage.deadline_hours)}h</strong></span>` : ''}
                            ${triage.technician_required !== undefined ? `<span class="rounded-lg bg-white/90 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 px-2.5 py-1 text-xs font-semibold text-slate-700 dark:text-slate-300">Technicien terrain : <strong>${triage.technician_required ? 'Requis' : 'Non requis'}</strong></span>` : ''}
                        </div>
                        <div class="text-xs text-slate-500 font-medium">
                            ${escapeHtml(payload.period || '')} · <strong>${escapeHtml(analysis.analyzed_measurements || 0)}</strong> relevé(s) analysé(s)
                        </div>
                    </div>

                    <div class="text-sm leading-relaxed text-slate-800 dark:text-slate-200 font-medium">
                        ${escapeHtml(analysis.executive_summary || analysis.summary || 'Synthèse non disponible.')}
                    </div>

                    <div class="grid gap-3 sm:grid-cols-2 pt-1">
                        ${triage.possible_cause ? `
                            <div class="rounded-xl bg-white/90 dark:bg-slate-800 border border-slate-200/90 dark:border-slate-700 p-3 text-xs text-slate-700 dark:text-slate-300">
                                <span class="font-bold text-slate-900 dark:text-white block mb-0.5">Origine probable :</span>
                                ${escapeHtml(triage.possible_cause)}
                            </div>
                        ` : ''}
                        ${triage.recommended_action ? `
                            <div class="rounded-xl bg-white/90 dark:bg-slate-800 border border-slate-200/90 dark:border-slate-700 p-3 text-xs text-slate-700 dark:text-slate-300">
                                <span class="font-bold text-slate-900 dark:text-white block mb-0.5">Action préconisée :</span>
                                ${escapeHtml(triage.recommended_action)}
                            </div>
                        ` : ''}
                    </div>
                </div>

                {{-- Statistiques 5 paramètres --}}
                <div>
                    <h3 class="mb-2.5 text-xs font-bold uppercase tracking-wide text-slate-500 flex items-center gap-1.5">
                        <i data-lucide="bar-chart-2" class="h-3.5 w-3.5 text-cyan-600"></i> Statistiques moyennes sur la période sélectionnée
                    </h3>
                    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
                        ${statCardsHtml}
                    </div>
                </div>

                ${anomaliesHtml}
                ${forecastHtml}
                ${recommendationsHtml}

                {{-- Tableau des dernières mesures --}}
                ${latest.length > 0 ? `
                    <div>
                        <h3 class="mb-2.5 text-xs font-bold uppercase tracking-wide text-slate-500 flex items-center gap-1.5">
                            <i data-lucide="history" class="h-3.5 w-3.5 text-cyan-600"></i> Historique récent des observations pour cette station
                        </h3>
                        <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-xs">
                            <table class="min-w-full text-left">
                                <thead class="bg-slate-50 dark:bg-slate-800/60 text-[11px] font-bold uppercase text-slate-500">
                                    <tr>
                                        <th class="px-3.5 py-2.5">Date</th>
                                        <th class="px-3 py-2.5">pH</th>
                                        <th class="px-3 py-2.5">Turbidité</th>
                                        <th class="px-3 py-2.5">Chlore</th>
                                        <th class="px-3 py-2.5">Plomb</th>
                                        <th class="px-3 py-2.5">Nitrates</th>
                                        <th class="px-3 py-2.5">Statut</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                    ${tableRows}
                                </tbody>
                            </table>
                        </div>
                    </div>
                ` : ''}

                {{-- Traçabilité & Qualité --}}
                <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 dark:border-slate-800 pt-3.5 text-[11px] text-slate-500">
                    <div>
                        Moteur : <strong class="text-slate-700 dark:text-slate-300">${escapeHtml(analysis.method || 'déterministe')}</strong>
                        · Taux de données vérifiées : <strong class="text-slate-700 dark:text-slate-300">${escapeHtml(analysis.real_data_percentage ?? 100)}%</strong>
                        ${analysis.confidence ? `· Indice de confiance : <strong class="text-slate-700 dark:text-slate-300">${escapeHtml(analysis.confidence)}</strong>` : ''}
                    </div>
                    <div>
                        Couverture temporelle : ${escapeHtml(analysis.first_date || '—')} → ${escapeHtml(analysis.last_date || '—')}
                    </div>
                </div>
            `;

            if (typeof lucide !== 'undefined') lucide.createIcons();
        } catch (err) {
            contentBox.innerHTML = `
                <div class="rounded-xl border border-red-200 bg-red-50 dark:bg-red-950/30 p-4 text-xs text-red-800 dark:text-red-300 flex items-center justify-between">
                    <span>Erreur de chargement de l'analyse : ${escapeHtml(err.message)}</span>
                    <button type="button" onclick="window.loadPointAnalysis && window.loadPointAnalysis()" class="rounded-lg bg-red-600 px-3 py-1 font-semibold text-white hover:bg-red-700 cursor-pointer">Réessayer</button>
                </div>
            `;
        } finally {
            if (spinnerIcon) spinnerIcon.classList.remove('animate-spin');
            if (btnText) btnText.textContent = 'Actualiser l\'analyse';
            if (refreshBtn) refreshBtn.disabled = false;
        }
    }

    window.loadPointAnalysis = loadAnalysis;

    if (refreshBtn) refreshBtn.addEventListener('click', loadAnalysis);
    if (periodSelect) periodSelect.addEventListener('change', loadAnalysis);

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', loadAnalysis);
    } else {
        loadAnalysis();
    }
})();
</script>
@endpush
@endif
@endsection
