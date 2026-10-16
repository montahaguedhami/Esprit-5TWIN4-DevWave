@props(['points' => []])

@php
    $analysisPoints = is_iterable($points) ? $points : [];
@endphp

<section class="rounded-2xl border border-slate-800 bg-slate-900/70 p-5 space-y-5 shadow-xl">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <h2 class="text-lg font-display font-semibold text-white flex items-center gap-2">
                <i data-lucide="radar" class="w-5 h-5 text-teal-400"></i>
                Collecte externe et analyse intelligente
            </h2>
            <p class="text-xs text-slate-400 mt-1">
                Données provenant des fournisseurs et assistance générée automatiquement.
            </p>
        </div>
        <div class="flex flex-wrap gap-2">
            <button type="button" id="collection-refresh-button"
                class="px-4 py-2 rounded-xl bg-teal-500/20 hover:bg-teal-500/30 text-teal-300 border border-teal-500/40 text-xs font-semibold transition-colors">
                Actualiser les données
            </button>
        </div>
    </div>

    <div class="rounded-xl border border-slate-800 bg-slate-950/60 p-3">
        <p class="text-xs text-slate-400" id="collection-status-text">Statut : en attente de vérification.</p>
        <div class="mt-2 grid gap-2 sm:grid-cols-4 text-xs text-slate-400">
            <div>Fournisseurs : <span id="collection-providers" class="text-slate-200">-</span></div>
            <div>Points actifs : <span id="collection-points" class="text-slate-200">-</span></div>
            <div>Mesures : <span id="collection-measures" class="text-slate-200">-</span></div>
            <div>Dernière collecte : <span id="collection-finished" class="text-slate-200">-</span></div>
        </div>
    </div>

    <div class="grid gap-5 lg:grid-cols-2">
        <div class="rounded-xl border border-slate-800 bg-slate-950/60 p-4 space-y-3">
            <h3 class="text-sm font-semibold text-white">Importer un fichier CSV / XLSX</h3>
            <form id="csv-import-form" class="flex flex-wrap items-end gap-3">
                <input type="file" name="file" accept=".csv,.txt,.xlsx,.xls"
                    class="text-xs text-slate-400 file:mr-3 file:rounded-lg file:border-0 file:bg-teal-500/20 file:px-3 file:py-1.5 file:text-teal-300" required>
                <button type="submit"
                    class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 text-xs font-semibold transition-colors">
                    Importer
                </button>
                <span id="import-result" class="text-xs text-slate-400"></span>
            </form>
        </div>

        <div class="rounded-xl border border-slate-800 bg-slate-950/60 p-4 space-y-3">
            <h3 class="text-sm font-semibold text-white flex items-center gap-2">
                <i data-lucide="sparkles" class="w-4 h-4 text-violet-400"></i>
                Analyse intelligente d’un point
            </h3>
            <p class="text-[11px] text-slate-500">
                Contenu généré automatiquement par le service d’analyse. Vérifiez les résultats avant action.
            </p>
            <div class="flex flex-wrap items-end gap-3">
                <div>
                    <label class="block text-xs text-slate-400 mb-1">Point de mesure</label>
                    <select id="analysis-point"
                        class="rounded-xl bg-slate-950 border border-slate-700 text-white text-xs px-3 py-2 focus:border-teal-400 focus:outline-none">
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
                    <label class="block text-xs text-slate-400 mb-1">Période</label>
                    <select id="analysis-days"
                        class="rounded-xl bg-slate-950 border border-slate-700 text-white text-xs px-3 py-2 focus:border-teal-400 focus:outline-none">
                        <option value="7">7 jours</option>
                        <option value="30" selected>30 jours</option>
                        <option value="90">90 jours</option>
                        <option value="365">365 jours</option>
                    </select>
                </div>
                <button type="button" id="analysis-button"
                    class="px-3.5 py-2 rounded-xl bg-teal-500/20 hover:bg-teal-500/30 text-teal-300 border border-teal-500/40 text-xs font-semibold transition-colors">
                    Analyser ce point
                </button>
            </div>
            <div id="analysis-result"
                class="mt-3 rounded-xl border border-slate-700 bg-slate-950/80 p-3 text-xs text-slate-300 hidden"></div>
        </div>
    </div>
</section>

@push('scripts')
<script>
    const collectionStatusUrl = '/manager/data-collection/status';
    const collectionTriggerUrl = '/manager/data-collection/trigger';
    const collectionImportUrl = '/manager/data-collection/import-csv';
    const collectionCsrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const collectionStatusText = document.getElementById('collection-status-text');
    const collectionRefreshButton = document.getElementById('collection-refresh-button');

    async function refreshCollectionStatus() {
        try {
            const response = await fetch(collectionStatusUrl, { headers: { 'Accept': 'application/json' } });
            if (!response.ok) throw new Error('status_' + response.status);
            const payload = await response.json();
            const job = payload.job || { status: 'idle' };
            const stats = payload.stats || {};
            if (collectionStatusText) collectionStatusText.textContent = 'Statut : ' + (job.status || 'idle');
            const providers = document.getElementById('collection-providers');
            const points = document.getElementById('collection-points');
            const measures = document.getElementById('collection-measures');
            const finished = document.getElementById('collection-finished');
            if (providers) providers.textContent = Object.keys(payload.providers || {}).length;
            if (points) points.textContent = stats.total_points ?? '-';
            if (measures) measures.textContent = stats.total_measures ?? '-';
            if (finished) finished.textContent = job.finished_at || job.started_at || '-';
        } catch (error) {
            if (collectionStatusText) collectionStatusText.textContent = 'Statut : indisponible';
        }
    }

    if (collectionRefreshButton) {
        collectionRefreshButton.addEventListener('click', async () => {
            collectionRefreshButton.disabled = true;
            if (collectionStatusText) collectionStatusText.textContent = 'Statut : demande envoyée...';
            try {
                const response = await fetch(collectionTriggerUrl, {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': collectionCsrfToken },
                });
                const payload = await response.json();
                if (collectionStatusText) collectionStatusText.textContent = 'Statut : ' + (payload.status || 'queued');
                setTimeout(refreshCollectionStatus, 2000);
            } catch (error) {
                if (collectionStatusText) collectionStatusText.textContent = 'Statut : erreur lors du déclenchement';
            } finally {
                collectionRefreshButton.disabled = false;
            }
        });
    }

    const csvImportForm = document.getElementById('csv-import-form');
    if (csvImportForm) {
        csvImportForm.addEventListener('submit', async (event) => {
            event.preventDefault();
            const formData = new FormData(event.currentTarget);
            const importResult = document.getElementById('import-result');
            if (importResult) importResult.textContent = 'Import en cours...';
            try {
                const response = await fetch(collectionImportUrl, {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': collectionCsrfToken },
                    body: formData,
                });
                const payload = await response.json();
                if (!response.ok) throw new Error(payload.message || 'Erreur import');
                if (importResult) importResult.textContent = payload.message;
                setTimeout(() => window.location.reload(), 1200);
            } catch (error) {
                if (importResult) importResult.textContent = 'Erreur : ' + error.message;
            }
        });
    }

    function escapeHtml(value) {
        const holder = document.createElement('div');
        holder.textContent = String(value);
        return holder.innerHTML;
    }

    function renderParameters(parameters) {
        if (!parameters || Object.keys(parameters).length === 0) {
            return '';
        }
        const rows = Object.keys(parameters).map(function (param) {
            const stats = parameters[param] || {};
            const average = stats.avg === undefined ? '-' : stats.avg;
            const count = stats.count ?? 0;
            return `<div>· ${param} : moyenne ${average} (${count} mesure(s))</div>`;
        }).join('');
        return `
            <div>
                <div class="font-semibold text-white">Paramètres mesurés</div>
                <div class="text-slate-300">${rows}</div>
            </div>`;
    }

    function renderMissingParameters(missing) {
        if (!Array.isArray(missing) || missing.length === 0) {
            return '';
        }
        const items = missing.map(function (param) {
            return `<div>· ${param} : non fourni dans cette période</div>`;
        }).join('');
        return `
            <div>
                <div class="font-semibold text-white">Paramètres manquants</div>
                <div class="text-slate-300">${items}</div>
            </div>`;
    }

    function renderForecast(forecast) {
        if (!forecast || Object.keys(forecast).length === 0) {
            return '';
        }
        const rows = Object.keys(forecast).map(function (param) {
            const entry = forecast[param] || {};
            const predicted = entry.predicted === null || entry.predicted === undefined
                ? 'indisponible'
                : entry.predicted;
            const trend = entry.trend === 'hausse' ? 'hausse'
                : (entry.trend === 'baisse' ? 'baisse' : 'stable');
            return `<div>· ${param} : ${predicted} (évolution : ${trend})</div>`;
        }).join('');
        return `
            <div>
                <div class="font-semibold text-white">Prévisions</div>
                <div class="text-slate-300">${rows}</div>
                <div class="text-slate-500">Les prévisions sont des extrapolations déterministes, à titre indicatif uniquement.</div>
            </div>`;
    }

    function renderRecommendations(recommendations) {
        if (!Array.isArray(recommendations) || recommendations.length === 0) {
            return '';
        }
        const items = recommendations.map(function (recommendation) {
            return `<li>${escapeHtml(String(recommendation))}</li>`;
        }).join('');
        return `
            <div>
                <div class="font-semibold text-white">Recommandations</div>
                <ul class="list-disc list-inside text-slate-300 space-y-1">
                    ${items}
                </ul>
            </div>`;
    }

    const analysisButton = document.getElementById('analysis-button');
    if (analysisButton) {
        analysisButton.addEventListener('click', async () => {
            const pointId = document.getElementById('analysis-point').value;
            const days = document.getElementById('analysis-days').value;
            const panel = document.getElementById('analysis-result');
            panel.classList.remove('hidden');
            panel.textContent = 'Analyse en cours...';
            try {
                const response = await fetch(`/manager/quality/${pointId}/analyse?days=${days}`, {
                    headers: { 'Accept': 'application/json' },
                });
                const payload = await response.json();
                if (!response.ok) throw new Error(payload.message || 'Erreur analyse');
                const t = payload.analysis.triage || {};
                const color = t.severity === 'critique' ? 'text-red-400' : (t.severity === 'alerte' ? 'text-amber-300' : 'text-green-400');
                panel.innerHTML = `
                    <div class="space-y-2">
                        <div class="text-white font-semibold">Point ${payload.point.code} — ${payload.point.nom}</div>
                        <div class="${color} font-bold uppercase">${t.severity_label || t.severity || 'Information'}</div>
                        <div>Priorité : ${t.priority || '-'} | Deadline : ${t.deadline_hours ? t.deadline_hours + ' h' : 'aucune urgence'}</div>
                        <div>Technicien requis : ${t.technician_required ? 'oui' : 'non'}</div>
                        <div><strong>État :</strong> ${payload.analysis.executive_summary || payload.analysis.summary || ''}</div>
                        <div><strong>Description :</strong> ${t.description || ''}</div>
                        <div><strong>Cause probable :</strong> ${t.possible_cause || ''}</div>
                        <div><strong>Action recommandée :</strong> ${t.recommended_action || ''}</div>
                        ${renderParameters(payload.analysis.parameters)}
                        ${renderMissingParameters(payload.analysis.missing_parameters)}
                        ${renderForecast(payload.analysis.forecast)}
                        ${renderRecommendations(payload.analysis.recommendations)}
                        <div><strong>Confiance :</strong> ${payload.analysis.confidence || '-'}</div>
                        <div><strong>Couverture réelle :</strong> ${payload.analysis.real_data_percentage || 0}%</div>
                        ${payload.analysis.simulated_data_warning ? '<div class="text-amber-300">Attention : données simulées incluses dans l’analyse.</div>' : ''}
                        <div class="text-slate-500"><strong>Limites :</strong> ${payload.analysis.limitations || ''}</div>
                    </div>
                `;
            } catch (error) {
                panel.textContent = 'Erreur : ' + error.message;
            }
        });
    }

    if (typeof lucide !== 'undefined') lucide.createIcons();
    refreshCollectionStatus();
</script>
@endpush