{{-- Manager AI assistant. Proposals only pre-fill the action form; saving goes through the normal validated store route. --}}
<section id="ai-manager" class="my-6 rounded-2xl border border-slate-700 bg-slate-950 p-5 sm:p-6 text-white shadow-lg" aria-labelledby="ai-manager-title"
         data-endpoint="{{ route('manager.incidents.ai.analysis', $incident) }}">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 id="ai-manager-title" class="flex items-center gap-2 text-xl font-semibold">
                <i data-lucide="sparkles" class="h-5 w-5 text-cyan-300"></i>Assistant IA du gestionnaire
            </h2>
            <p class="mt-1 text-sm text-slate-300">Analyse technique et propositions d’actions correctives (Google Gemini). Toute décision reste humaine.</p>
        </div>
        <div class="flex gap-2">
            <button type="button" data-ai-run class="inline-flex items-center gap-2 rounded bg-cyan-500 px-4 py-2 text-sm font-semibold text-white disabled:opacity-60">
                <i data-lucide="brain" class="h-4 w-4"></i>Analyser avec l’IA
            </button>
            <button type="button" data-ai-refresh class="hidden items-center gap-2 rounded border border-white/20 px-3 py-2 text-sm disabled:opacity-60">
                <i data-lucide="refresh-cw" class="h-4 w-4"></i>Régénérer
            </button>
        </div>
    </div>
    <p data-ai-status role="status" class="mt-3 hidden text-sm"></p>
    <div data-ai-result class="mt-4 hidden space-y-4" aria-live="polite"></div>
    <p class="mt-4 text-xs text-slate-400">
        Suggestions générées automatiquement : à vérifier. L’IA ne modifie ni le statut de l’incident ni les actions, et n’affirme jamais qu’une réparation est terminée.
        Seules les données techniques de l’incident (sans identité du citoyen) sont transmises.
    </p>
</section>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const root = document.getElementById('ai-manager');
    if (!root) return;
    const runBtn = root.querySelector('[data-ai-run]');
    const refreshBtn = root.querySelector('[data-ai-refresh]');
    const status = root.querySelector('[data-ai-status]');
    const result = root.querySelector('[data-ai-result]');
    const labels = {
        faible: 'Faible', moyenne: 'Moyenne', elevee: 'Élevée', critique: 'Critique',
        fuite: 'Fuite', pollution: 'Pollution', panne: 'Panne', rupture: 'Rupture', contamination: 'Contamination',
    };
    const current = { type: @js($incident->type), gravite: @js($incident->gravite) };

    function el(tag, className, text) {
        const node = document.createElement(tag);
        if (className) node.className = className;
        if (text !== undefined && text !== null) node.textContent = text;
        return node;
    }

    function setStatus(text, isError) {
        status.textContent = text;
        status.className = 'mt-3 text-sm ' + (isError ? 'text-red-400' : 'text-slate-300');
        status.classList.toggle('hidden', !text);
    }

    function card(title, icon) {
        const box = el('div', 'rounded bg-white/5 p-4');
        const h = el('h3', 'font-semibold flex items-center gap-2');
        const i = el('i', 'h-4 w-4 text-cyan-300');
        i.setAttribute('data-lucide', icon);
        h.append(i, document.createTextNode(title));
        box.appendChild(h);
        return box;
    }

    function listCard(title, icon, items, ordered, extraClass) {
        if (!items || !items.length) return null;
        const box = card(title, icon);
        if (extraClass) box.className += ' ' + extraClass;
        const list = el(ordered ? 'ol' : 'ul', 'mt-2 space-y-1 pl-5 text-sm text-slate-200 ' + (ordered ? 'list-decimal' : 'list-disc'));
        items.forEach(function (item) { list.appendChild(el('li', '', item)); });
        box.appendChild(list);
        return box;
    }

    function setField(id, value) {
        const field = document.getElementById(id);
        if (!field) return;
        field.value = value;
        field.dispatchEvent(new Event('input', { bubbles: true }));
        field.dispatchEvent(new Event('change', { bubbles: true }));
    }

    function prepareAction(action) {
        const form = document.getElementById('action_titre')?.closest('form');
        if (!form) return;
        const date = new Date();
        date.setDate(date.getDate() + (action.delai_jours || 0));
        setField('action_titre', action.titre);
        setField('action_description', action.description);
        setField('resultat', 'Résultat attendu (à confirmer après intervention) : ' + action.resultat_attendu);
        setField('date_prevue', date.toISOString().slice(0, 10));
        setField('action_statut', 'a_faire');
        setField('date_realisation', '');

        let notice = document.getElementById('ai-prefill-notice');
        if (!notice) {
            notice = el('p', 'rounded bg-amber-500/20 border border-amber-400/40 p-3 text-sm text-amber-100');
            notice.id = 'ai-prefill-notice';
            notice.setAttribute('role', 'status');
            form.prepend(notice);
        }
        notice.textContent = 'Formulaire pré-rempli par l’IA. Vérifiez et ajustez les champs, choisissez un technicien, puis validez vous-même l’enregistrement.';
        form.scrollIntoView({ behavior: 'smooth', block: 'start' });
        document.getElementById('technicien_id')?.focus({ preventScroll: true });
    }

    function render(d) {
        result.replaceChildren();

        const summary = card('Résumé technique', 'file-text');
        summary.appendChild(el('p', 'mt-2 text-sm text-slate-200 whitespace-pre-line', d.resume_technique));
        result.appendChild(summary);

        const triage = card('Catégorie et priorité suggérées (à confirmer)', 'gauge');
        const grid = el('dl', 'mt-2 grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm');
        [['Catégorie', d.categorie_suggeree, current.type], ['Priorité', d.priorite_suggeree, current.gravite]].forEach(function (row) {
            const wrap = el('div');
            wrap.appendChild(el('dt', 'text-slate-400', row[0] + ' suggérée'));
            const dd = el('dd', 'font-semibold', labels[row[1]] || row[1] || '—');
            if (row[1] && row[2] && row[1] !== row[2]) {
                dd.appendChild(el('span', 'ml-2 text-xs font-normal text-amber-300', '(déclarée : ' + (labels[row[2]] || row[2]) + ')'));
            }
            wrap.appendChild(dd);
            grid.appendChild(wrap);
        });
        triage.appendChild(grid);
        if (d.justification_priorite) triage.appendChild(el('p', 'mt-2 text-sm text-slate-300', d.justification_priorite));
        result.appendChild(triage);

        const diag = el('div', 'grid grid-cols-1 md:grid-cols-2 gap-4');
        const facts = listCard('Faits établis', 'check-circle', d.faits, false);
        if (facts) diag.appendChild(facts);
        if (d.hypotheses && d.hypotheses.length) {
            const hyp = card('Hypothèses (non vérifiées)', 'help-circle');
            const ul = el('ul', 'mt-2 space-y-1 pl-5 text-sm text-slate-200 list-disc');
            d.hypotheses.forEach(function (h) {
                const li = el('li', '', h.hypothese);
                li.appendChild(el('span', 'ml-2 text-xs text-slate-400', '(confiance : ' + (labels[h.confiance] || h.confiance) + ')'));
                ul.appendChild(li);
            });
            hyp.appendChild(ul);
            diag.appendChild(hyp);
        }
        if (diag.children.length) result.appendChild(diag);

        [
            listCard('Vérifications à effectuer (dans l’ordre)', 'list-checks', d.verifications, true),
            listCard('Outils et matériel', 'wrench', d.outils, false),
            listCard('Équipements de protection (EPI)', 'hard-hat', d.epi, false),
            listCard('Étapes de résolution', 'route', d.etapes_resolution, true),
        ].forEach(function (c) { if (c) result.appendChild(c); });

        if (d.actions_proposees && d.actions_proposees.length) {
            const box = card('Actions correctives proposées', 'clipboard-list');
            const wrap = el('div', 'mt-3 space-y-3');
            d.actions_proposees.forEach(function (a) {
                const item = el('article', 'rounded border border-white/10 p-3');
                item.appendChild(el('h4', 'font-semibold', a.titre));
                item.appendChild(el('p', 'mt-1 text-sm text-slate-200 whitespace-pre-line', a.description));
                item.appendChild(el('p', 'mt-1 text-xs text-slate-400', 'Résultat attendu : ' + a.resultat_attendu + ' · Délai conseillé : ' + a.delai_jours + ' jour(s)'));
                const btn = el('button', 'mt-2 inline-flex items-center gap-2 rounded bg-cyan-600 px-3 py-1.5 text-sm text-white', 'Préparer cette action');
                btn.type = 'button';
                btn.addEventListener('click', function () { prepareAction(a); });
                item.appendChild(btn);
                wrap.appendChild(item);
            });
            box.appendChild(wrap);
            result.appendChild(box);
        }

        [
            listCard('Prévention', 'shield-check', d.prevention, false),
            listCard('Arrêter l’intervention et appeler un spécialiste si…', 'octagon-alert', d.arret_specialiste, false, 'border border-red-400/40'),
        ].forEach(function (c) { if (c) result.appendChild(c); });

        result.classList.remove('hidden');
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }

    function run(refresh) {
        runBtn.disabled = refreshBtn.disabled = true;
        setStatus('Analyse en cours…', false);
        fetch(root.dataset.endpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': @js(csrf_token()),
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ refresh: refresh }),
        })
            .then(function (response) {
                return response.json().catch(function () { return {}; }).then(function (json) {
                    if (response.status === 429) throw new Error('Trop de demandes. Patientez une minute avant de réessayer.');
                    if (!response.ok) throw new Error(json.message || 'Assistant IA indisponible pour le moment.');
                    return json.data;
                });
            })
            .then(function (data) {
                render(data);
                setStatus('', false);
                refreshBtn.classList.remove('hidden');
                refreshBtn.classList.add('inline-flex');
            })
            .catch(function (error) { setStatus(error.message, true); })
            .finally(function () { runBtn.disabled = refreshBtn.disabled = false; });
    }

    runBtn.addEventListener('click', function () { run(false); });
    refreshBtn.addEventListener('click', function () { run(true); });
});
</script>
@endpush
