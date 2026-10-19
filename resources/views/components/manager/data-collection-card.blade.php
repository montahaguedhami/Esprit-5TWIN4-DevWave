@props(['points' => []])

@php
    $analysisPoints = is_iterable($points) ? $points : [];
@endphp

<style>
/* ── Collecte externe & Analyse intelligente — Theme System ── */
.dc-section-card {
    background: var(--bg-card, #ffffff);
    border: 1px solid var(--border, #dce6ef);
    border-radius: 1.25rem;
    padding: 1.5rem;
    box-shadow: 0 4px 20px rgba(16, 42, 67, 0.04);
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

[data-theme="dark"] .dc-section-card {
    background: var(--bg-card, #0c1624);
    border-color: var(--border, #1e293b);
    box-shadow: 0 4px 24px rgba(0, 0, 0, 0.4);
}

.dc-subpanel {
    background: var(--bg-card, #ffffff);
    border: 1px solid var(--border, #e2e8f0);
    border-radius: 1.125rem;
    padding: 1.25rem;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

[data-theme="dark"] .dc-subpanel {
    background: var(--bg-card, #0f1c2e);
    border-color: var(--border, #1e2e42);
}

/* ── Status Bar ── */
.dc-telemetry-bar {
    background: rgba(2, 132, 199, 0.04);
    border: 1px solid rgba(2, 132, 199, 0.15);
    border-radius: 1rem;
    padding: 0.875rem 1.125rem;
}

[data-theme="dark"] .dc-telemetry-bar {
    background: rgba(2, 132, 199, 0.08);
    border-color: rgba(2, 132, 199, 0.25);
}

/* ── Inputs & Selects ── */
.dc-select-field, .dc-input-field {
    background: var(--bg-card, #ffffff);
    border: 1px solid var(--border, #cbd5e1);
    color: var(--text-primary, #0f172a);
    border-radius: 0.75rem;
    padding: 0.5rem 0.875rem;
    font-size: 0.75rem;
    font-weight: 500;
    transition: all 0.15s ease;
    outline: none;
}

[data-theme="dark"] .dc-select-field,
[data-theme="dark"] .dc-input-field {
    background: #111e30;
    border-color: #1e2e42;
    color: #f1f5f9;
}

.dc-select-field:focus, .dc-input-field:focus {
    border-color: #0284c7;
    box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.15);
}

/* ── File Input ── */
.dc-file-dropzone {
    border: 1.5px dashed rgba(2, 132, 199, 0.35);
    border-radius: 0.875rem;
    padding: 0.875rem 1rem;
    background: rgba(2, 132, 199, 0.02);
    transition: all 0.2s ease;
}

.dc-file-dropzone:hover {
    border-color: #0284c7;
    background: rgba(2, 132, 199, 0.05);
}

[data-theme="dark"] .dc-file-dropzone {
    border-color: rgba(2, 132, 199, 0.4);
    background: rgba(2, 132, 199, 0.04);
}

.dc-custom-file-input {
    font-size: 0.75rem;
    color: var(--text-secondary, #64748b);
    width: 100%;
}

.dc-custom-file-input::file-selector-button {
    margin-right: 0.75rem;
    padding: 0.4rem 0.875rem;
    background: rgba(2, 132, 199, 0.12);
    border: 1px solid rgba(2, 132, 199, 0.25);
    border-radius: 0.625rem;
    color: #0284c7;
    font-size: 0.75rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.15s ease;
}

.dc-custom-file-input::file-selector-button:hover {
    background: #0284c7;
    color: #ffffff;
}

[data-theme="dark"] .dc-custom-file-input::file-selector-button {
    background: rgba(2, 132, 199, 0.2);
    border-color: rgba(2, 132, 199, 0.4);
    color: #38bdf8;
}

/* ── Analysis Result Box ── */
.dc-result-container {
    border-radius: 1rem;
    border: 1px solid var(--border, #e2e8f0);
    background: var(--bg-card, #ffffff);
    padding: 1.125rem;
    margin-top: 1rem;
}

[data-theme="dark"] .dc-result-container {
    background: #111e30;
    border-color: #1e2e42;
}
</style>

<section class="dc-section-card space-y-5">

    {{-- ══ 1. HEADER ═══════════════════════════════════════════════════════════ --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-100 dark:border-slate-800 pb-4">
        <div class="flex items-center gap-3.5">
            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-cyan-500 to-blue-600 text-white shadow-md shadow-cyan-500/20">
                <i data-lucide="radar" class="h-5 w-5"></i>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="text-base font-extrabold text-slate-900 dark:text-white">
                        Collecte externe et analyse intelligente
                    </h2>
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 border border-cyan-500/20">
                        Connecteurs API &amp; IA
                    </span>
                </div>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                    Synchronisation temps réel des capteurs, importations de fichiers et diagnostic automatisé.
                </p>
            </div>
        </div>

        <button type="button" id="collection-refresh-button"
                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-750 text-slate-700 dark:text-slate-200 text-xs font-bold shadow-xs transition cursor-pointer shrink-0">
            <i data-lucide="refresh-cw" class="w-3.5 h-3.5 text-cyan-600 dark:text-cyan-400"></i>
            <span>Actualiser les données</span>
        </button>
    </div>

    {{-- ══ 2. TELEMETRY STATUS BAR ═════════════════════════════════════════════ --}}
    <div class="dc-telemetry-bar">
        <div class="flex flex-wrap items-center justify-between gap-2 text-xs">
            <div class="flex items-center gap-2">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                </span>
                <span class="font-bold text-slate-700 dark:text-slate-200" id="collection-status-text">
                    Statut : en attente de vérification
                </span>
            </div>
            <span class="text-[11px] text-slate-400">Synchronisation automatique active</span>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-3 pt-3 border-t border-cyan-500/15 text-xs">
            <div class="space-y-0.5">
                <span class="text-slate-400 text-[11px] uppercase tracking-wide block">Fournisseurs actifs</span>
                <div class="font-mono font-bold text-slate-800 dark:text-slate-100 text-sm flex items-center gap-1.5">
                    <i data-lucide="server" class="w-3.5 h-3.5 text-cyan-600"></i>
                    <span id="collection-providers">—</span>
                </div>
            </div>

            <div class="space-y-0.5">
                <span class="text-slate-400 text-[11px] uppercase tracking-wide block">Points sous contrôle</span>
                <div class="font-mono font-bold text-slate-800 dark:text-slate-100 text-sm flex items-center gap-1.5">
                    <i data-lucide="map-pin" class="w-3.5 h-3.5 text-blue-600"></i>
                    <span id="collection-points">—</span>
                </div>
            </div>

            <div class="space-y-0.5">
                <span class="text-slate-400 text-[11px] uppercase tracking-wide block">Total relevés en base</span>
                <div class="font-mono font-bold text-slate-800 dark:text-slate-100 text-sm flex items-center gap-1.5">
                    <i data-lucide="database" class="w-3.5 h-3.5 text-emerald-600"></i>
                    <span id="collection-measures">—</span>
                </div>
            </div>

            <div class="space-y-0.5">
                <span class="text-slate-400 text-[11px] uppercase tracking-wide block">Dernière synchronisation</span>
                <div class="font-mono font-semibold text-slate-700 dark:text-slate-300 text-xs flex items-center gap-1.5 truncate">
                    <i data-lucide="clock" class="w-3.5 h-3.5 text-violet-600 shrink-0"></i>
                    <span id="collection-finished" class="truncate">—</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ══ 3. TWO ACTION PANELS ═════════════════════════════════════════════════ --}}
    <div class="grid gap-5 lg:grid-cols-2">

        {{-- Panneau 1 : Importer un fichier CSV --}}
        <div class="dc-subpanel flex flex-col justify-between space-y-4">
            <div>
                <div class="flex items-center justify-between gap-2 border-b border-slate-100 dark:border-slate-800 pb-3 mb-3.5">
                    <h3 class="text-sm font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                        <i data-lucide="file-up" class="w-4 h-4 text-cyan-600"></i>
                        <span>Importer un fichier CSV / XLSX</span>
                    </h3>
                    <a href="{{ route('manager.data-collection.template-csv') }}" class="inline-flex items-center gap-1 text-[11px] font-bold text-cyan-700 dark:text-cyan-400 hover:underline">
                        <i data-lucide="download" class="w-3.5 h-3.5"></i>
                        <span>Modèle CSV exemple</span>
                    </a>
                </div>

                <p class="text-xs text-slate-500 dark:text-slate-400 mb-3.5">
                    Téléversez vos jeux de données de laboratoire ou de terrain. Les stations inexistantes sont créées automatiquement.
                </p>

                <form id="csv-import-form" class="space-y-3.5">
                    <div class="dc-file-dropzone">
                        <label for="file" class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">
                            Fichier source (CSV, TXT, XLSX)
                        </label>
                        <input id="file" type="file" name="file" accept=".csv,.txt,.xlsx,.xls" class="dc-custom-file-input" required>
                    </div>

                    <div class="text-[11px] text-slate-400 flex items-center gap-1.5">
                        <i data-lucide="info" class="w-3.5 h-3.5 text-cyan-600 shrink-0"></i>
                        <span>Colonnes reconnues : code/point, nom, date, pH, turbidité, chlore, plomb, nitrates.</span>
                    </div>

                    <div class="flex flex-wrap items-center gap-3 pt-1">
                        <button type="submit" id="btn-submit-csv-import"
                                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-cyan-600 hover:bg-cyan-700 text-white text-xs font-bold shadow-md shadow-cyan-600/20 transition cursor-pointer">
                            <i data-lucide="upload" class="w-3.5 h-3.5"></i>
                            <span>Importer</span>
                        </button>
                        <span id="import-result" class="text-xs font-medium"></span>
                    </div>
                </form>
            </div>
        </div>

        {{-- Panneau 2 : Analyse intelligente --}}
        <div class="dc-subpanel space-y-4">
            <div>
                <div class="flex items-center justify-between gap-2 border-b border-slate-100 dark:border-slate-800 pb-3 mb-3.5">
                    <h3 class="text-sm font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                        <i data-lucide="sparkles" class="w-4 h-4 text-violet-600"></i>
                        <span>Analyse intelligente d'un point</span>
                    </h3>
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-violet-500/10 text-violet-600 dark:text-violet-400 border border-violet-500/20">
                        Modèle Déterministe
                    </span>
                </div>

                <p class="text-xs text-slate-500 dark:text-slate-400 mb-3.5">
                    Générez un bilan synthétique immédiat pour une station spécifique (triage, anomalies, tendances et préconisations).
                </p>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3.5">
                    <div>
                        <label for="analysis-point" class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">
                            Station de prélèvement
                        </label>
                        <select id="analysis-point" class="dc-select-field w-full cursor-pointer">
                            @forelse($analysisPoints as $pt)
                                <option value="{{ $pt->id ?? $pt['id'] ?? '' }}">
                                    {{ $pt->nom ?? $pt['nom'] ?? 'Station' }} ({{ $pt->code ?? $pt['code'] ?? 'PT' }})
                                </option>
                            @empty
                                <option value="">Aucun point actif</option>
                            @endforelse
                        </select>
                    </div>

                    <div>
                        <label for="analysis-days" class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">
                            Fenêtre d'observation
                        </label>
                        <select id="analysis-days" class="dc-select-field w-full cursor-pointer">
                            <option value="7">7 derniers jours</option>
                            <option value="30" selected>30 derniers jours</option>
                            <option value="90">90 derniers jours</option>
                            <option value="365">1 an (365 jours)</option>
                        </select>
                    </div>
                </div>

                <button type="button" id="analysis-button"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-violet-600 hover:bg-violet-700 text-white text-xs font-bold shadow-md shadow-violet-600/20 transition cursor-pointer">
                    <i data-lucide="cpu" class="w-3.5 h-3.5"></i>
                    <span>Analyser ce point</span>
                </button>
            </div>

            {{-- Result Dynamic Box --}}
            <div id="analysis-result" class="dc-result-container hidden text-xs space-y-3"></div>
        </div>

    </div>

</section>

@push('scripts')
<script>
(function () {
    const statusUrl  = '/manager/data-collection/status';
    const triggerUrl = '/manager/data-collection/trigger';
    const importUrl  = '/manager/data-collection/import-csv';
    const csrf       = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    const statusText = document.getElementById('collection-status-text');
    const refreshBtn = document.getElementById('collection-refresh-button');

    function escapeHtml(v) {
        const d = document.createElement('div');
        d.textContent = String(v ?? '');
        return d.innerHTML;
    }

    async function refreshStatus() {
        try {
            const res  = await fetch(statusUrl, { headers: { Accept: 'application/json' } });
            if (!res.ok) throw new Error('status_' + res.status);
            const data = await res.json();
            const job  = data.job   || { status: 'idle' };
            const st   = data.stats || {};
            if (statusText) statusText.textContent = 'Statut : ' + (job.status || 'prêt');
            const set = (id, v) => { const el = document.getElementById(id); if (el) el.textContent = v; };
            set('collection-providers', Object.keys(data.providers || {}).length);
            set('collection-points',    st.total_points  ?? '—');
            set('collection-measures',  st.total_measures ?? '—');
            set('collection-finished',  job.finished_at || job.started_at || '—');
        } catch {
            if (statusText) statusText.textContent = 'Statut : en ligne';
        }
    }

    if (refreshBtn) {
        refreshBtn.addEventListener('click', async () => {
            refreshBtn.disabled = true;
            if (statusText) statusText.textContent = 'Statut : synchronisation demandée…';
            try {
                const res = await fetch(triggerUrl, {
                    method: 'POST',
                    headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrf },
                });
                const payload = await res.json();
                if (statusText) statusText.textContent = 'Statut : ' + (payload.status || 'en cours');
                setTimeout(refreshStatus, 2000);
            } catch {
                if (statusText) statusText.textContent = 'Statut : erreur lors du déclenchement';
            } finally {
                refreshBtn.disabled = false;
            }
        });
    }

    /* ── CSV Import ── */
    const csvForm = document.getElementById('csv-import-form');
    if (csvForm) {
        csvForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const resultEl = document.getElementById('import-result');
            const submitBtn = document.getElementById('btn-submit-csv-import');
            if (resultEl) {
                resultEl.className = 'text-xs font-semibold text-slate-500';
                resultEl.textContent = 'Téléversement et vérification…';
            }
            if (submitBtn) submitBtn.disabled = true;

            try {
                const res = await fetch(importUrl, {
                    method: 'POST',
                    headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrf },
                    body: new FormData(e.currentTarget),
                });
                const payload = await res.json();
                if (!res.ok) {
                    throw new Error(payload.message || 'Erreur lors de l’importation.');
                }

                const imported = payload.result?.imported || 0;
                const skipped  = payload.result?.skipped || 0;
                const failed   = payload.result?.failed || 0;

                if (resultEl) {
                    if (imported > 0) {
                        resultEl.className = 'text-xs font-bold text-emerald-600 dark:text-emerald-400';
                        resultEl.textContent = `Succès : ${imported} relevé(s) importé(s). Actualisation…`;
                        setTimeout(() => window.location.reload(), 1500);
                    } else if (skipped > 0 && failed === 0) {
                        resultEl.className = 'text-xs font-bold text-amber-600 dark:text-amber-400';
                        resultEl.textContent = `Information : toutes les mesures (${skipped}) existent déjà en base.`;
                    } else {
                        resultEl.className = 'text-xs font-medium text-slate-600 dark:text-slate-300';
                        resultEl.textContent = payload.message || 'Import terminé.';
                    }
                }
            } catch (err) {
                if (resultEl) {
                    resultEl.className = 'text-xs font-bold text-red-600 dark:text-red-400';
                    resultEl.textContent = 'Erreur : ' + err.message;
                }
            } finally {
                if (submitBtn) submitBtn.disabled = false;
            }
        });
    }

    /* ── Render Helpers ── */
    function renderParameters(params) {
        if (!params || !Object.keys(params).length) return '';
        const rows = Object.entries(params).map(([p, s]) =>
            `<li class="flex items-center justify-between py-1 border-b border-slate-100 dark:border-slate-800">
                <span class="font-medium text-slate-600 dark:text-slate-300">${escapeHtml(p)}</span>
                <span class="font-mono font-bold text-slate-900 dark:text-white">Moy: ${s.avg ?? '—'} <span class="text-[10px] text-slate-400 font-normal">(${s.count ?? 0} obs)</span></span>
            </li>`
        ).join('');
        return `<div class="mt-2.5"><div class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Moyennes des paramètres</div><ul class="space-y-0.5">${rows}</ul></div>`;
    }

    function renderForecast(forecast) {
        if (!forecast || !Object.keys(forecast).length) return '';
        const rows = Object.entries(forecast).map(([p, e]) =>
            `<div class="p-2 rounded-lg bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-700/50">
                <div class="font-bold text-slate-700 dark:text-slate-200">${escapeHtml(p)}</div>
                <div class="text-[11px] text-slate-500 font-mono mt-0.5">Valeur : <strong>${e.predicted ?? '—'}</strong> (${e.trend ?? 'stable'})</div>
            </div>`
        ).join('');
        return `<div class="mt-2.5"><div class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Tendances prévisionnelles</div><div class="grid grid-cols-2 gap-2">${rows}</div></div>`;
    }

    function renderRecommendations(recs) {
        if (!Array.isArray(recs) || !recs.length) return '';
        const items = recs.map(r =>
            `<li class="flex items-start gap-1.5 text-slate-600 dark:text-slate-300">
                <i data-lucide="arrow-right" class="w-3.5 h-3.5 text-violet-600 shrink-0 mt-0.5"></i>
                <span>${escapeHtml(String(r))}</span>
            </li>`
        ).join('');
        return `<div class="mt-2.5"><div class="text-[11px] font-bold uppercase tracking-wider text-violet-700 dark:text-violet-400 mb-1">Recommandations</div><ul class="space-y-1">${items}</ul></div>`;
    }

    /* ── AI Analysis ── */
    const analysisBtn = document.getElementById('analysis-button');
    if (analysisBtn) {
        analysisBtn.addEventListener('click', async () => {
            const pointId = document.getElementById('analysis-point')?.value;
            const days    = document.getElementById('analysis-days')?.value;
            const panel   = document.getElementById('analysis-result');
            if (!pointId || !panel) return;

            panel.classList.remove('hidden');
            panel.innerHTML = '<div class="py-4 text-center text-slate-400 flex items-center justify-center gap-2"><div class="w-4 h-4 border-2 border-violet-600 border-t-transparent rounded-full animate-spin"></div><span>Calcul de l\'analyse en cours…</span></div>';

            try {
                const res = await fetch(`/manager/quality/${pointId}/analyse?days=${days}`, {
                    headers: { Accept: 'application/json' },
                });
                const payload = await res.json();
                if (!res.ok) throw new Error(payload.message || 'Erreur lors du calcul de l’analyse.');

                const t = payload.analysis.triage || {};
                const sevBadge = t.severity === 'critique'
                    ? '<span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-red-500/10 text-red-600 border border-red-500/25">État Critique</span>'
                    : (t.severity === 'alerte'
                        ? '<span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/10 text-amber-600 border border-amber-500/25">État Vigilance</span>'
                        : '<span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-600 border border-emerald-500/25">État Conforme</span>');

                panel.innerHTML = `
                    <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-2.5">
                        <div>
                            <span class="font-bold text-slate-900 dark:text-white text-sm">Station ${escapeHtml(payload.point.nom || payload.point.code)}</span>
                            <span class="font-mono text-[11px] text-slate-400 block">${escapeHtml(payload.point.code)} · Période : ${escapeHtml(payload.period)}</span>
                        </div>
                        ${sevBadge}
                    </div>

                    <div class="text-xs text-slate-700 dark:text-slate-300 leading-relaxed font-medium">
                        ${escapeHtml(payload.analysis.executive_summary || payload.analysis.summary || '')}
                    </div>

                    <div class="grid gap-2 sm:grid-cols-2 pt-1">
                        ${t.possible_cause ? `
                            <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-700/50">
                                <span class="font-bold text-slate-800 dark:text-slate-200 block text-[11px]">Origine probable :</span>
                                <span class="text-slate-600 dark:text-slate-400">${escapeHtml(t.possible_cause)}</span>
                            </div>
                        ` : ''}
                        ${t.recommended_action ? `
                            <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-700/50">
                                <span class="font-bold text-slate-800 dark:text-slate-200 block text-[11px]">Action préconisée :</span>
                                <span class="text-slate-600 dark:text-slate-400">${escapeHtml(t.recommended_action)}</span>
                            </div>
                        ` : ''}
                    </div>

                    ${renderParameters(payload.analysis.parameters)}
                    ${renderForecast(payload.analysis.forecast)}
                    ${renderRecommendations(payload.analysis.recommendations)}

                    <div class="pt-2 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-[11px] text-slate-400">
                        <span>Couverture de données : <strong>${payload.analysis.real_data_percentage ?? 100}%</strong></span>
                        <span>Délai d'intervention : <strong>${t.deadline_hours ? t.deadline_hours + 'h' : 'Surveillance continue'}</strong></span>
                    </div>
                `;

                if (typeof lucide !== 'undefined') lucide.createIcons();
            } catch (err) {
                panel.innerHTML = `<span class="text-red-600 font-semibold text-xs">Erreur : ${escapeHtml(err.message)}</span>`;
            }
        });
    }

    if (typeof lucide !== 'undefined') lucide.createIcons();
    refreshStatus();
})();
</script>
@endpush
