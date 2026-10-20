{{-- Citizen AI advice panel. $endpoint: POST URL; $fromForm: read type/description from the form being filled. --}}
@php $fromForm = $fromForm ?? false; @endphp
<section id="ai-advice" class="rounded-2xl border border-slate-700 bg-slate-950 p-5 sm:p-6 text-white shadow-lg" aria-labelledby="ai-advice-title"
         data-endpoint="{{ $endpoint }}" data-from-form="{{ $fromForm ? '1' : '0' }}">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 id="ai-advice-title" class="flex items-center gap-2 text-lg font-semibold text-white">
                <i data-lucide="sparkles" class="h-5 w-5 text-cyan-400"></i>Assistant IA : que faire en attendant ?
            </h2>
            <p class="mt-1 text-sm text-slate-300">Conseils simples et temporaires en attendant l’intervention du service responsable.</p>
        </div>
        <button type="button" data-ai-advice-btn class="inline-flex items-center gap-2 rounded-xl bg-cyan-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-cyan-700 disabled:opacity-60">
            <i data-lucide="lightbulb" class="h-4 w-4"></i><span>Obtenir des conseils IA</span>
        </button>
    </div>
    <p data-ai-advice-status role="status" class="mt-3 hidden text-sm"></p>
    <div data-ai-advice-result class="mt-4 hidden space-y-4" aria-live="polite"></div>
    <p class="mt-4 text-xs text-slate-400">
        Ces conseils sont générés par une intelligence artificielle (Google Gemini) à titre indicatif. Ils ne remplacent pas les consignes officielles et ne modifient pas le traitement de votre demande.
        En cas de danger immédiat, contactez les services d’urgence.
    </p>
</section>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const root = document.getElementById('ai-advice');
    if (!root) return;
    const btn = root.querySelector('[data-ai-advice-btn]');
    const status = root.querySelector('[data-ai-advice-status]');
    const result = root.querySelector('[data-ai-advice-result]');
    const fromForm = root.dataset.fromForm === '1';

    function setStatus(text, isError) {
        status.textContent = text;
        status.className = 'mt-3 text-sm ' + (isError ? 'text-red-400' : 'text-slate-300');
        status.classList.toggle('hidden', !text);
    }

    function block(title, items, tone) {
        if (!items || !items.length) return null;
        const tones = {
            ok: 'border-emerald-500/40 bg-white/5',
            warn: 'border-amber-500/40 bg-amber-500/10',
            alert: 'border-red-500/40 bg-red-500/10',
        };
        const box = document.createElement('div');
        box.className = 'rounded-xl border p-4 ' + tones[tone];
        const h = document.createElement('h3');
        h.className = 'text-sm font-semibold text-white';
        h.textContent = title;
        const ul = document.createElement('ul');
        ul.className = 'mt-2 list-disc space-y-1 pl-5 text-sm text-slate-100';
        items.forEach(function (item) {
            const li = document.createElement('li');
            li.textContent = item;
            ul.appendChild(li);
        });
        box.append(h, ul);
        return box;
    }

    function render(data) {
        result.replaceChildren();
        if (data.resume) {
            const p = document.createElement('p');
            p.className = 'text-sm text-white';
            p.textContent = data.resume;
            result.appendChild(p);
        }
        [
            block('Ce que vous pouvez faire', data.conseils, 'ok'),
            block('À éviter', data.a_eviter, 'warn'),
            block('Contactez rapidement le service responsable si…', data.contacter_rapidement_si, 'alert'),
        ].forEach(function (el) { if (el) result.appendChild(el); });
        if (data.message_rassurant) {
            const p = document.createElement('p');
            p.className = 'text-sm font-medium text-cyan-300';
            p.textContent = data.message_rassurant;
            result.appendChild(p);
        }
        result.classList.remove('hidden');
    }

    function payload() {
        if (!fromForm) return {};
        const val = function (id) { const el = document.getElementById(id); return el ? el.value : ''; };
        return { type: val('type'), description: val('description'), titre: val('titre'), gravite: val('gravite') };
    }

    btn.addEventListener('click', function () {
        btn.disabled = true;
        setStatus('L’assistant analyse votre signalement…', false);
        fetch(root.dataset.endpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': @js(csrf_token()),
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify(payload()),
        })
            .then(function (response) {
                return response.json().catch(function () { return {}; }).then(function (json) {
                    if (response.status === 429) throw new Error('Trop de demandes. Patientez une minute avant de réessayer.');
                    if (!response.ok) {
                        const errors = json.errors ? Object.values(json.errors).flat() : [];
                        throw new Error(errors[0] || json.message || 'Assistant IA indisponible pour le moment.');
                    }
                    return json.data;
                });
            })
            .then(function (data) { render(data); setStatus('', false); })
            .catch(function (error) { setStatus(error.message, true); })
            .finally(function () { btn.disabled = false; });
    });
});
</script>
@endpush
