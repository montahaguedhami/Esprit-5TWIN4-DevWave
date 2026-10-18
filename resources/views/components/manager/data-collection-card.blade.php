@props(['points' => []])

@php
    $analysisPoints = is_iterable($points) ? $points : [];
@endphp

<style>
/* ── Data collection card — light shell override ── */
.dc-card {
    background: #fff;
    border: 1px solid #dce6ef;
    border-radius: 1rem;
    padding: 1.5rem;
    box-shadow: 0 2px 16px rgba(16,42,67,.06);
}
.dc-card-header {
    display: flex;
    flex-wrap: wrap;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem;
    margin-bottom: 1.25rem;
}
.dc-card-title {
    font-size: 1rem;
    font-weight: 700;
    color: #152b42;
    display: flex;
    align-items: center;
    gap: .5rem;
}
.dc-card-sub { font-size: .75rem; color: #52677d; margin-top: .25rem; }

/* Status bar */
.dc-status-bar {
    background: #f3f7fb;
    border: 1px solid #dce6ef;
    border-radius: .75rem;
    padding: .75rem 1rem;
    margin-bottom: 1.25rem;
}
.dc-status-bar .status-text { font-size: .75rem; color: #52677d; }
.dc-status-bar .stat-grid { display: grid; grid-template-columns: repeat(4,1fr); gap: .5rem; margin-top: .5rem; }
.dc-status-bar .stat-item { font-size: .75rem; color: #52677d; }
.dc-status-bar .stat-val { font-weight: 700; color: #152b42; }

/* Sub panels */
.dc-panel {
    background: #f8fbfd;
    border: 1px solid #dce6ef;
    border-radius: .75rem;
    padding: 1rem;
}
.dc-panel h3 { font-size: .875rem; font-weight: 700; color: #152b42; display: flex; align-items: center; gap: .375rem; margin-bottom: .5rem; }
.dc-panel p  { font-size: .7rem; color: #52677d; margin-bottom: .75rem; }

/* Form controls */
.dc-input, .dc-select {
    background: #fff;
    border: 1px solid #cbd8e4;
    border-radius: .625rem;
    padding: .4rem .75rem;
    font-size: .75rem;
    color: #152b42;
    outline: none;
    transition: border-color .15s;
}
.dc-input:focus, .dc-select:focus {
    border-color: #0891b2;
    box-shadow: 0 0 0 3px rgba(8,145,178,.1);
}
.dc-label { display: block; font-size: .65rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: #52677d; margin-bottom: .3rem; }

/* File input */
.dc-file-input {
    font-size: .75rem;
    color: #52677d;
}
.dc-file-input::file-selector-button {
    margin-right: .75rem;
    padding: .375rem .875rem;
    background: #e0f2fe;
    border: 1px solid #bae6fd;
    border-radius: .5rem;
    color: #0369a1;
    font-size: .75rem;
    font-weight: 600;
    cursor: pointer;
    transition: background .15s;
}
.dc-file-input::file-selector-button:hover { background: #bae6fd; }

/* Buttons */
.dc-btn-primary {
    display: inline-flex;
    align-items: center;
    gap: .375rem;
    padding: .45rem 1rem;
    background: #0891b2;
    color: #fff;
    border: none;
    border-radius: .625rem;
    font-size: .75rem;
    font-weight: 600;
    cursor: pointer;
    transition: background .15s;
}
.dc-btn-primary:hover { background: #0e7490; }
.dc-btn-primary:disabled { opacity: .6; cursor: not-allowed; }

.dc-btn-secondary {
    display: inline-flex;
    align-items: center;
    gap: .375rem;
    padding: .45rem 1rem;
    background: #fff;
    color: #152b42;
    border: 1px solid #cbd8e4;
    border-radius: .625rem;
    font-size: .75rem;
    font-weight: 600;
    cursor: pointer;
    transition: background .15s, border-color .15s;
}
.dc-btn-secondary:hover { background: #f3f7fb; border-color: #94b4c8; }

.dc-btn-violet {
    display: inline-flex;
    align-items: center;
    gap: .375rem;
    padding: .45rem 1rem;
    background: #ede9fe;
    color: #5b21b6;
    border: 1px solid #ddd6fe;
    border-radius: .625rem;
    font-size: .75rem;
    font-weight: 600;
    cursor: pointer;
    transition: background .15s;
}
.dc-btn-violet:hover { background: #ddd6fe; }

/* Analysis result panel */
.dc-result {
    margin-top: .75rem;
    border: 1px solid #dce6ef;
    background: #f8fbfd;
    border-radius: .625rem;
    padding: .875rem;
    font-size: .75rem;
    color: #152b42;
    line-height: 1.6;
}
.dc-result .result-title   { font-weight: 700; color: #152b42; margin-bottom: .25rem; }
.dc-result .result-critique{ color: #b91c1c; font-weight: 700; text-transform: uppercase; }
.dc-result .result-alerte  { color: #92400e; font-weight: 700; text-transform: uppercase; }
.dc-result .result-ok      { color: #166534; font-weight: 700; text-transform: uppercase; }
.dc-result .result-label   { color: #52677d; }
.dc-result .result-section { margin-top: .5rem; }
.dc-result .result-section-title { font-weight: 700; color: #152b42; margin-bottom: .125rem; }
.dc-result ul { padding-left: 1rem; }
.dc-result li { margin-bottom: .125rem; }
.dc-result .result-warning { color: #92400e; background: #fef9c3; border: 1px solid #fde68a; border-radius: .375rem; padding: .25rem .5rem; margin-top: .375rem; }
.dc-result .result-muted   { color: #718397; font-size: .7rem; }

@media (max-width: 640px) {
    .dc-status-bar .stat-grid { grid-template-columns: 1fr 1fr; }
}
</style>

<section class="dc-card space-y-0">
    <div class="dc-card-header">
        <div>
            <h2 class="dc-card-title">
                <i data-lucide="radar" style="width:1.125rem;height:1.125rem;color:#0891b2;flex-shrink:0"></i>
                Collecte externe et analyse intelligente
            </h2>
            <p class="dc-card-sub">Données provenant des fournisseurs et assistance générée automatiquement.</p>
        </div>
        <button type="button" id="collection-refresh-button" class="dc-btn-secondary">
            <i data-lucide="refresh-cw" style="width:.875rem;height:.875rem;color:#0891b2"></i>
            Actualiser les données
        </button>
    </div>

    {{-- Status bar --}}
    <div class="dc-status-bar">
        <p class="status-text" id="collection-status-text">Statut : en attente de vérification.</p>
        <div class="stat-grid">
            <div class="stat-item">Fournisseurs : <span class="stat-val" id="collection-providers">—</span></div>
            <div class="stat-item">Points actifs : <span class="stat-val" id="collection-points">—</span></div>
            <div class="stat-item">Mesures : <span class="stat-val" id="collection-measures">—</span></div>
            <div class="stat-item">Dernière collecte : <span class="stat-val" id="collection-finished">—</span></div>
        </div>
    </div>

    {{-- Two panels --}}
    <div class="grid gap-4 lg:grid-cols-2">

        {{-- CSV import --}}
        <div class="dc-panel">
            <h3>
                <i data-lucide="file-up" style="width:.875rem;height:.875rem;color:#0891b2"></i>
                Importer un fichier CSV / XLSX
            </h3>
            <form id="csv-import-form" class="space-y-3">
                <div>
                    <label class="dc-label">Fichier source</label>
                    <input type="file" name="file" accept=".csv,.txt,.xlsx,.xls"
                           class="dc-file-input" required>
                    <div class="mt-2 flex flex-wrap items-center justify-between gap-2 text-xs text-slate-500">
                        <span>Colonnes reconnues : code/point, nom, date, pH, turbidité, chlore, plomb, nitrates.</span>
                        <a href="{{ route('manager.data-collection.template-csv') }}" class="inline-flex items-center gap-1 font-semibold text-cyan-700 hover:text-cyan-800 hover:underline">
                            <i data-lucide="download" style="width:.75rem;height:.75rem"></i> Modèle CSV exemple
                        </a>
                    </div>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <button type="submit" class="dc-btn-primary" id="btn-submit-csv-import">
                        <i data-lucide="upload" style="width:.875rem;height:.875rem"></i>
                        <span>Importer</span>
                    </button>
                    <span id="import-result" class="text-xs font-medium text-slate-600"></span>
                </div>
            </form>
        </div>

        {{-- AI analysis --}}
        <div class="dc-panel">
            <h3>
                <i data-lucide="sparkles" style="width:.875rem;height:.875rem;color:#7c3aed"></i>
                Analyse intelligente d'un point
            </h3>
            <p>Contenu généré automatiquement par le service d'analyse. Vérifiez les résultats avant action.</p>
            <div class="flex flex-wrap items-end gap-3">
                <div>
                    <label class="dc-label">Point de mesure</label>
                    <select id="analysis-point" class="dc-select">
                        @forelse($analysisPoints as $pt)
                            <option value="{{ $pt->id ?? $pt['id'] ?? '' }}">
                                {{ $pt->code ?? $pt['code'] ?? 'Point' }}
                            </option>
                        @empty
                            <option value="">Aucun point</option>
                        @endforelse
                    </select>
                </div>
                <div>
                    <label class="dc-label">Période</label>
                    <select id="analysis-days" class="dc-select">
                        <option value="7">7 jours</option>
                        <option value="30" selected>30 jours</option>
                        <option value="90">90 jours</option>
                        <option value="365">365 jours</option>
                    </select>
                </div>
                <button type="button" id="analysis-button" class="dc-btn-violet">
                    <i data-lucide="cpu" style="width:.875rem;height:.875rem"></i>
                    Analyser ce point
                </button>
            </div>
            <div id="analysis-result" class="dc-result hidden"></div>
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

    const statusText    = document.getElementById('collection-status-text');
    const refreshBtn    = document.getElementById('collection-refresh-button');

    /* ── helpers ── */
    function escapeHtml(v) {
        const d = document.createElement('div');
        d.textContent = String(v ?? '');
        return d.innerHTML;
    }

    /* ── status poll ── */
    async function refreshStatus() {
        try {
            const res  = await fetch(statusUrl, { headers: { Accept: 'application/json' } });
            if (!res.ok) throw new Error('status_' + res.status);
            const data = await res.json();
            const job  = data.job   || { status: 'idle' };
            const st   = data.stats || {};
            if (statusText) statusText.textContent = 'Statut : ' + (job.status || 'idle');
            const set = (id, v) => { const el = document.getElementById(id); if (el) el.textContent = v; };
            set('collection-providers', Object.keys(data.providers || {}).length);
            set('collection-points',    st.total_points  ?? '—');
            set('collection-measures',  st.total_measures ?? '—');
            set('collection-finished',  job.finished_at || job.started_at || '—');
        } catch {
            if (statusText) statusText.textContent = 'Statut : indisponible';
        }
    }

    /* ── refresh button ── */
    if (refreshBtn) {
        refreshBtn.addEventListener('click', async () => {
            refreshBtn.disabled = true;
            if (statusText) statusText.textContent = 'Statut : demande envoyée…';
            try {
                const res     = await fetch(triggerUrl, {
                    method: 'POST',
                    headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrf },
                });
                const payload = await res.json();
                if (statusText) statusText.textContent = 'Statut : ' + (payload.status || 'queued');
                setTimeout(refreshStatus, 2000);
            } catch {
                if (statusText) statusText.textContent = 'Statut : erreur lors du déclenchement';
            } finally {
                refreshBtn.disabled = false;
            }
        });
    }

    /* ── CSV import ── */
    const csvForm = document.getElementById('csv-import-form');
    if (csvForm) {
        csvForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const resultEl = document.getElementById('import-result');
            const submitBtn = document.getElementById('btn-submit-csv-import');
            if (resultEl) {
                resultEl.className = 'text-xs font-medium text-slate-500';
                resultEl.textContent = 'Importation en cours…';
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
                    throw new Error(payload.message || 'Une erreur est survenue lors de l’importation.');
                }

                const imported = payload.result?.imported || 0;
                const skipped  = payload.result?.skipped || 0;
                const failed   = payload.result?.failed || 0;

                const rowErrors = payload.result?.errors || [];
                const detail = rowErrors.map((item) => `${item.station_code || item.external_id}: ${item.message}`).join(' ; ');

                if (resultEl) {
                    if (imported > 0) {
                        resultEl.className = 'text-xs font-semibold text-emerald-600';
                        resultEl.textContent = `Succès : ${payload.message || `${imported} mesure(s) importée(s).`} Actualisation…`;
                        setTimeout(() => window.location.reload(), 1500);
                    } else if (skipped > 0 && failed === 0) {
                        resultEl.className = 'text-xs font-medium text-amber-700';
                        resultEl.textContent = `Information : toutes les mesures (${skipped}) sont déjà présentes dans la base de données.`;
                    } else {
                        resultEl.className = 'text-xs font-medium text-slate-700';
                        resultEl.textContent = detail ? `${payload.message} (${detail})` : payload.message;
                    }
                }
            } catch (err) {
                if (resultEl) {
                    resultEl.className = 'text-xs font-semibold text-red-600';
                    resultEl.textContent = 'Erreur : ' + err.message;
                }
            } finally {
                if (submitBtn) submitBtn.disabled = false;
            }
        });
    }

    /* ── render helpers ── */
    function renderParameters(params) {
        if (!params || !Object.keys(params).length) return '';
        const rows = Object.entries(params).map(([p, s]) =>
            `<li>${escapeHtml(p)} : moyenne ${s.avg ?? '—'} (${s.count ?? 0} mesure(s))</li>`
        ).join('');
        return `<div class="result-section"><div class="result-section-title">Paramètres mesurés</div><ul>${rows}</ul></div>`;
    }

    function renderMissing(missing) {
        if (!Array.isArray(missing) || !missing.length) return '';
        const rows = missing.map(p => `<li>${escapeHtml(p)} : non fourni dans cette période</li>`).join('');
        return `<div class="result-section"><div class="result-section-title">Paramètres manquants</div><ul>${rows}</ul></div>`;
    }

    function renderForecast(forecast) {
        if (!forecast || !Object.keys(forecast).length) return '';
        const rows = Object.entries(forecast).map(([p, e]) =>
            `<li>${escapeHtml(p)} : ${e.predicted ?? 'indisponible'} (évolution : ${e.trend ?? 'stable'})</li>`
        ).join('');
        return `<div class="result-section">
            <div class="result-section-title">Prévisions</div>
            <ul>${rows}</ul>
            <p class="result-muted">Extrapolations déterministes — à titre indicatif uniquement.</p>
        </div>`;
    }

    function renderRecommendations(recs) {
        if (!Array.isArray(recs) || !recs.length) return '';
        const items = recs.map(r => `<li>${escapeHtml(String(r))}</li>`).join('');
        return `<div class="result-section"><div class="result-section-title">Recommandations</div><ul>${items}</ul></div>`;
    }

    /* ── AI analysis ── */
    const analysisBtn = document.getElementById('analysis-button');
    if (analysisBtn) {
        analysisBtn.addEventListener('click', async () => {
            const pointId = document.getElementById('analysis-point')?.value;
            const days    = document.getElementById('analysis-days')?.value;
            const panel   = document.getElementById('analysis-result');
            panel.classList.remove('hidden');
            panel.innerHTML = '<span class="result-muted">Analyse en cours…</span>';
            try {
                const res     = await fetch(`/manager/quality/${pointId}/analyse?days=${days}`, {
                    headers: { Accept: 'application/json' },
                });
                const payload = await res.json();
                if (!res.ok) throw new Error(payload.message || 'Erreur analyse');

                const t    = payload.analysis.triage || {};
                const sevCls = t.severity === 'critique' ? 'result-critique'
                             : t.severity === 'alerte'   ? 'result-alerte'
                             :                              'result-ok';

                panel.innerHTML = `
                    <div class="result-title">
                        Point ${escapeHtml(payload.point.code)} — ${escapeHtml(payload.point.nom)}
                    </div>
                    <div class="${sevCls}">${escapeHtml(t.severity_label || t.severity || 'Information')}</div>
                    <div class="result-label">Priorité : ${escapeHtml(t.priority ?? '—')} | Deadline : ${t.deadline_hours ? t.deadline_hours + ' h' : 'aucune urgence'}</div>
                    <div class="result-label">Technicien requis : ${t.technician_required ? 'Oui' : 'Non'}</div>
                    <div class="result-section"><span class="font-semibold">État : </span>${escapeHtml(payload.analysis.executive_summary || payload.analysis.summary || '')}</div>
                    ${t.description       ? `<div class="result-section"><span class="font-semibold">Description : </span>${escapeHtml(t.description)}</div>` : ''}
                    ${t.possible_cause    ? `<div class="result-section"><span class="font-semibold">Cause probable : </span>${escapeHtml(t.possible_cause)}</div>` : ''}
                    ${t.recommended_action? `<div class="result-section"><span class="font-semibold">Action : </span>${escapeHtml(t.recommended_action)}</div>` : ''}
                    ${renderParameters(payload.analysis.parameters)}
                    ${renderMissing(payload.analysis.missing_parameters)}
                    ${renderForecast(payload.analysis.forecast)}
                    ${renderRecommendations(payload.analysis.recommendations)}
                    <div class="result-label">Confiance : ${escapeHtml(payload.analysis.confidence ?? '—')} | Couverture : ${payload.analysis.real_data_percentage ?? 0}%</div>
                    ${payload.analysis.limitations
                        ? `<div class="result-muted">Limites : ${escapeHtml(payload.analysis.limitations)}</div>` : ''}
                `;
            } catch (err) {
                panel.innerHTML = `<span style="color:#b91c1c">Erreur : ${escapeHtml(err.message)}</span>`;
            }
        });
    }

    if (typeof lucide !== 'undefined') lucide.createIcons();
    refreshStatus();
})();
</script>
@endpush
