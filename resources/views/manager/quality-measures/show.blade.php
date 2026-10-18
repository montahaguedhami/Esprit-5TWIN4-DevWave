@extends('layouts.manager')
@section('title', $mesure->reference)
@section('manager-content')
<div class="text-slate-800">
    <div class="mx-auto max-w-4xl space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div><h1 class="text-2xl font-bold text-slate-900">Mesure {{ $mesure->reference }}</h1><p class="text-sm text-slate-600">{{ $mesure->pointMesure?->nom }}{{ $mesure->pointMesure?->infrastructure ? ' · '.$mesure->pointMesure->infrastructure->nom : '' }} · {{ $mesure->date_mesure->format('d/m/Y H:i') }}</p></div>
            <div class="flex gap-2">
                @can('validate', $mesure)
                    @unless($mesure->is_verified)
                        <form method="POST" action="{{ route('manager.quality.mesures.validate', $mesure) }}">
                            @csrf
                    <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">Valider</button>
                        </form>
                    @endunless
                @endcan
                @can('update', $mesure)
                    <a href="{{ route('manager.quality.mesures.edit', $mesure) }}" class="inline-flex items-center justify-center rounded-xl bg-cyan-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-cyan-700 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:ring-offset-2">Modifier</a>
                @endcan
                <a href="{{ route('manager.quality') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-cyan-400 hover:bg-cyan-50 hover:text-cyan-800 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:ring-offset-2">Retour à la qualité</a>
            </div>
        </div>
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach([
                'pH' => [$mesure->ph, ''],
                'Turbidité' => [$mesure->turbidite, 'NTU'],
                'Chlore résiduel' => [$mesure->chlore_residuel, 'mg/L'],
                'Plomb' => [$mesure->plomb, 'ppb'],
                'Nitrates' => [$mesure->nitrates, 'mg/L'],
                'Conformité' => [$mesure->overall_compliance, '%'],
            ] as $label => [$value, $unit])
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="text-xs font-medium text-slate-500">{{ $label }}</div>
                    <div class="mt-2 text-xl font-bold text-slate-900">
                        @if($value !== null)
                            {{ $value }} <span class="text-xs font-normal text-slate-500">{{ $unit }}</span>
                        @else
                            <span class="text-sm font-medium text-slate-500">Non renseigné</span>
                        @endif
                        @if($label === 'Conformité')
                            <span class="mt-1 block text-xs font-medium text-slate-600">{{ $mesure->status ?? 'État non calculé' }}</span>
                        @endif
                    </div>
                </div>
            @endforeach
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><div class="text-xs font-medium text-slate-500">Vérification</div><div class="mt-2 font-semibold text-slate-900">{{ $mesure->is_verified ? 'Vérifiée' : 'À vérifier' }}</div><div class="text-sm text-slate-600">{{ $mesure->verifier ?: '—' }}</div></div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><div class="text-xs font-medium text-slate-500">Provenance</div><div class="mt-2 font-semibold text-slate-900">{{ match($mesure->source_donnee ?? 'manuel') { 'laboratoire' => 'Laboratoire', 'simule', 'simulation' => 'Réseau', 'csv' => 'Import CSV', 'data_gov_tn' => 'Données publiques tunisiennes', 'emodnet' => 'EMODnet', default => $mesure->provider ? 'Import '.$mesure->provider : 'Saisie manuelle' } }}</div>@if($mesure->observation_date)<div class="mt-1 text-xs text-slate-600">Observation source : {{ $mesure->observation_date->format('d/m/Y H:i') }}</div>@endif</div>
        </div>

        {{-- ══ ANALYSE INTELLIGENTE DU POINT DE MESURE ═════════════════════════ --}}
        @if($mesure->pointMesure)
            <div class="rounded-2xl border border-violet-200/80 bg-white p-6 shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-100 pb-4">
                    <div class="flex items-center gap-3">
                        <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-violet-100 text-violet-700 shadow-sm">
                            <i data-lucide="sparkles" class="h-6 w-6"></i>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="rounded-md bg-violet-50 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-violet-700 border border-violet-200">
                                    Analyse Intelligente
                                </span>
                                <span class="font-mono text-xs font-semibold text-slate-500">{{ $mesure->pointMesure->code }}</span>
                            </div>
                            <h2 class="mt-1 text-lg font-bold text-slate-900">
                                Analyse du point : {{ $mesure->pointMesure->nom }}
                            </h2>
                            <p class="text-xs text-slate-500">
                                {{ $mesure->pointMesure->infrastructure ? 'Infrastructure : '.$mesure->pointMesure->infrastructure->nom.' · ' : '' }}{{ $mesure->pointMesure->zone ? 'Zone : '.$mesure->pointMesure->zone.' · ' : '' }}Évaluation basée sur l'historique des mesures
                            </p>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <label for="point-analysis-period" class="text-xs font-medium text-slate-600">Période :</label>
                        <select id="point-analysis-period" class="rounded-xl border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 shadow-sm transition hover:border-violet-400 focus:border-violet-500 focus:outline-none focus:ring-1 focus:ring-violet-500">
                            <option value="30" selected>30 derniers jours</option>
                            <option value="7">7 derniers jours</option>
                            <option value="90">90 derniers jours</option>
                            <option value="365">1 an</option>
                            <option value="all">Historique complet</option>
                        </select>
                        <button type="button" id="btn-run-point-analysis" class="inline-flex items-center gap-1.5 rounded-xl bg-violet-600 px-3.5 py-1.5 text-xs font-semibold text-white shadow-sm transition hover:bg-violet-700 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:ring-offset-2">
                            <i data-lucide="refresh-cw" class="h-3.5 w-3.5" id="analysis-spinner-icon"></i>
                            <span id="analysis-btn-text">Actualiser l'analyse</span>
                        </button>
                    </div>
                </div>

                {{-- Conteneur dynamique du résultat d'analyse --}}
                <div id="point-analysis-content" class="mt-5 space-y-5">
                    <div class="flex items-center justify-center py-10 text-slate-400">
                        <div class="flex flex-col items-center gap-2">
                            <div class="h-6 w-6 animate-spin rounded-full border-2 border-violet-600 border-t-transparent"></div>
                            <span class="text-xs font-medium text-slate-500">Chargement de l'analyse intelligente pour ce point...</span>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5 text-sm text-slate-600">
                <i data-lucide="info" class="inline h-4 w-4 mr-1 text-slate-400"></i>
                Cette mesure n'est associée à aucun point de mesure spécifique. L'analyse intelligente de réseau n'est pas applicable.
            </div>
        @endif

        @can('delete', $mesure)
        <form method="POST" action="{{ route('manager.quality.mesures.destroy', $mesure) }}" onsubmit="return confirm('Supprimer définitivement cette mesure ?')">
            @csrf @method('DELETE')
            <button class="inline-flex items-center justify-center rounded-xl border border-red-300 bg-white px-4 py-2 text-sm font-semibold text-red-700 shadow-sm transition hover:border-red-400 hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">Supprimer la mesure</button>
        </form>
        @endcan
    </div>
</div>

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
            let sevBorder = 'border-slate-200 bg-slate-50 text-slate-800';
            if (triage.severity === 'critique') {
                sevBorder = 'border-red-200 bg-red-50/70 text-red-950';
                sevBadge = '<span class="inline-flex items-center gap-1 rounded-md bg-red-100 px-2.5 py-1 text-xs font-bold text-red-700"><i data-lucide="alert-octagon" class="h-3.5 w-3.5"></i> État Critique</span>';
            } else if (triage.severity === 'alerte') {
                sevBorder = 'border-amber-200 bg-amber-50/70 text-amber-950';
                sevBadge = '<span class="inline-flex items-center gap-1 rounded-md bg-amber-100 px-2.5 py-1 text-xs font-bold text-amber-700"><i data-lucide="alert-triangle" class="h-3.5 w-3.5"></i> État Vigilance</span>';
            } else {
                sevBorder = 'border-emerald-200 bg-emerald-50/70 text-emerald-950';
                sevBadge = '<span class="inline-flex items-center gap-1 rounded-md bg-emerald-100 px-2.5 py-1 text-xs font-bold text-emerald-700"><i data-lucide="check-circle-2" class="h-3.5 w-3.5"></i> État Conforme</span>';
            }

            // Cartes paramètres
            const statCardsHtml = Object.entries(paramLabels).map(([key, meta]) => {
                const s = stats[key];
                return `
                    <div class="rounded-xl border border-slate-200 bg-white p-3.5 shadow-xs">
                        <div class="flex items-center justify-between text-xs font-semibold text-slate-500 uppercase">
                            <span>${meta.label}</span>
                            <span class="text-[10px] font-normal text-slate-400">Norme : ${meta.norm}</span>
                        </div>
                        ${s ? `
                            <div class="mt-2 text-lg font-bold text-slate-900">
                                ${escapeHtml(s.avg)} <span class="text-xs font-normal text-slate-500">${meta.unit}</span>
                            </div>
                            <div class="mt-1 flex items-center justify-between text-[11px] text-slate-500">
                                <span>Min: ${escapeHtml(s.min)} · Max: ${escapeHtml(s.max)}</span>
                                <span class="rounded bg-slate-100 px-1.5 py-0.5 font-medium text-slate-600">${escapeHtml(s.count)} obs.</span>
                            </div>
                        ` : `
                            <div class="mt-2 text-xs font-medium text-slate-400">Aucune donnée disponible</div>
                        `}
                    </div>
                `;
            }).join('');

            // Anomalies
            let anomaliesHtml = '';
            if (anomalies.length > 0) {
                anomaliesHtml = `
                    <div class="space-y-2">
                        <h3 class="text-xs font-bold uppercase tracking-wide text-red-700">Anomalies et dépassements détectés</h3>
                        <div class="grid gap-2">
                            ${anomalies.map(a => `
                                <div class="rounded-xl border border-red-200 bg-red-50 p-3 text-xs text-red-900 flex items-start gap-2">
                                    <i data-lucide="alert-circle" class="h-4 w-4 shrink-0 text-red-600 mt-0.5"></i>
                                    <div>
                                        <strong>${escapeHtml(paramLabels[a.param]?.label || a.param)}</strong> : ${escapeHtml(a.detail)}
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                `;
            }

            // Prévisions
            let forecastHtml = '';
            const forecastEntries = Object.entries(forecasts);
            if (forecastEntries.length > 0) {
                forecastHtml = `
                    <div class="rounded-xl border border-slate-200 bg-slate-50/50 p-4">
                        <h3 class="text-xs font-bold uppercase tracking-wide text-slate-700 mb-2">Tendances prévisionnelles</h3>
                        <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                            ${forecastEntries.map(([key, item]) => `
                                <div class="rounded-lg border border-slate-200 bg-white p-2.5 text-xs">
                                    <span class="font-semibold text-slate-700">${paramLabels[key]?.label || key} :</span>
                                    <span class="text-slate-900 font-mono font-medium">${item.predicted === null ? '—' : escapeHtml(item.predicted) + ' ' + (paramLabels[key]?.unit || '')}</span>
                                    <span class="block text-[11px] text-slate-500 mt-0.5">Tendance : <strong>${escapeHtml(item.trend || 'stable')}</strong></span>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                `;
            }

            // Recommandations
            let recommendationsHtml = '';
            if (recommendations.length > 0) {
                recommendationsHtml = `
                    <div class="rounded-xl border border-violet-100 bg-violet-50/40 p-4">
                        <h3 class="text-xs font-bold uppercase tracking-wide text-violet-900 mb-2 flex items-center gap-1.5">
                            <i data-lucide="check-square" class="h-3.5 w-3.5 text-violet-600"></i> Recommandations de traitement & surveillance
                        </h3>
                        <ul class="list-inside list-disc space-y-1 text-xs text-slate-700">
                            ${recommendations.map(r => `<li>${escapeHtml(r)}</li>`).join('')}
                        </ul>
                    </div>
                `;
            }

            // Tableau des dernières mesures pour ce point
            let tableRows = '';
            if (latest.length > 0) {
                tableRows = latest.map(m => `
                    <tr class="border-t border-slate-100 text-xs">
                        <td class="whitespace-nowrap px-3 py-2 font-medium text-slate-700">${escapeHtml(m.date)}</td>
                        <td class="px-3 py-2 font-mono">${m.ph === null ? '—' : escapeHtml(m.ph)}</td>
                        <td class="px-3 py-2 font-mono">${m.turbidite === null ? '—' : escapeHtml(m.turbidite)}</td>
                        <td class="px-3 py-2 font-mono">${m.chlore_residuel === null ? '—' : escapeHtml(m.chlore_residuel)}</td>
                        <td class="px-3 py-2 font-mono">${m.plomb === null ? '—' : escapeHtml(m.plomb)}</td>
                        <td class="px-3 py-2 font-mono">${m.nitrates === null ? '—' : escapeHtml(m.nitrates)}</td>
                        <td class="px-3 py-2 font-medium">${escapeHtml(m.status || 'Évaluée')}</td>
                    </tr>
                `).join('');
            }

            // Assemblage du rendu
            contentBox.innerHTML = `
                {{-- Synthèse et Triage --}}
                <div class="rounded-2xl border ${sevBorder} p-5 space-y-3">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <div class="flex flex-wrap items-center gap-2">
                            ${sevBadge}
                            ${triage.priority ? `<span class="rounded bg-white/80 border border-slate-200 px-2 py-0.5 text-xs font-medium text-slate-700">Priorité : <strong>${escapeHtml(triage.priority)}</strong></span>` : ''}
                            ${triage.deadline_hours ? `<span class="rounded bg-white/80 border border-slate-200 px-2 py-0.5 text-xs font-medium text-slate-700">Intervention : <strong>sous ${escapeHtml(triage.deadline_hours)}h</strong></span>` : ''}
                            ${triage.technician_required !== undefined ? `<span class="rounded bg-white/80 border border-slate-200 px-2 py-0.5 text-xs font-medium text-slate-700">Technicien terrain : <strong>${triage.technician_required ? 'Requis' : 'Non requis'}</strong></span>` : ''}
                        </div>
                        <div class="text-xs text-slate-500">
                            ${escapeHtml(payload.period || '')} · <strong>${escapeHtml(analysis.analyzed_measurements || 0)}</strong> mesure(s) analysée(s)
                        </div>
                    </div>
                    <div class="text-sm leading-relaxed text-slate-800">
                        ${escapeHtml(analysis.executive_summary || analysis.summary || 'Synthèse non disponible.')}
                    </div>
                    ${triage.possible_cause ? `
                        <div class="rounded-lg bg-white/80 border border-slate-200/80 p-2.5 text-xs text-slate-700">
                            <span class="font-bold text-slate-900">Origine probable :</span> ${escapeHtml(triage.possible_cause)}
                        </div>
                    ` : ''}
                    ${triage.recommended_action ? `
                        <div class="rounded-lg bg-white/80 border border-slate-200/80 p-2.5 text-xs text-slate-700">
                            <span class="font-bold text-slate-900">Action préconisée :</span> ${escapeHtml(triage.recommended_action)}
                        </div>
                    ` : ''}
                </div>


                {{-- Statistiques 5 paramètres --}}
                <div>
                    <h3 class="mb-2 text-xs font-bold uppercase tracking-wide text-slate-500">Statistiques moyennes sur la période</h3>
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
                        <h3 class="mb-2 text-xs font-bold uppercase tracking-wide text-slate-500">Dernières observations pour ce point</h3>
                        <div class="overflow-x-auto rounded-xl border border-slate-200">
                            <table class="min-w-full text-left">
                                <thead class="bg-slate-50 text-[11px] font-semibold uppercase text-slate-500">
                                    <tr>
                                        <th class="px-3 py-2">Date</th>
                                        <th class="px-3 py-2">pH</th>
                                        <th class="px-3 py-2">Turbidité</th>
                                        <th class="px-3 py-2">Chlore</th>
                                        <th class="px-3 py-2">Plomb</th>
                                        <th class="px-3 py-2">Nitrates</th>
                                        <th class="px-3 py-2">État</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    ${tableRows}
                                </tbody>
                            </table>
                        </div>
                    </div>
                ` : ''}

                {{-- Traçabilité & Qualité --}}
                <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-3 text-[11px] text-slate-500">
                    <div>
                        Méthode : <strong class="text-slate-700">${escapeHtml(analysis.method || 'déterministe')}</strong>
                        · Données réelles : <strong class="text-slate-700">${escapeHtml(analysis.real_data_percentage ?? 0)}%</strong>
                        ${analysis.confidence ? `· Confiance : <strong class="text-slate-700">${escapeHtml(analysis.confidence)}</strong>` : ''}
                    </div>
                    <div>
                        Période couverte : ${escapeHtml(analysis.first_date || '—')} → ${escapeHtml(analysis.last_date || '—')}
                    </div>
                </div>
            `;

            if (typeof lucide !== 'undefined') lucide.createIcons();
        } catch (err) {
            contentBox.innerHTML = `
                <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-xs text-red-800 flex items-center justify-between">
                    <span>Erreur : ${escapeHtml(err.message)}</span>
                    <button type="button" onclick="window.loadPointAnalysis && window.loadPointAnalysis()" class="rounded-lg bg-red-600 px-3 py-1 font-semibold text-white hover:bg-red-700">Réessayer</button>
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

    // Chargement automatique dès l'affichage de la page
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
