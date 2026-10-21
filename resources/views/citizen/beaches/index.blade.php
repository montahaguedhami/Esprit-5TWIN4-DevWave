@extends('layouts.frontoffice')

@section('title', 'Plages avec interdiction signal&eacute;e &mdash; AquaSecure')

@push('styles')
<style>
    .beach-page { max-width: 1180px; margin: 0 auto; padding: 1.5rem; color: var(--text-primary); }
    .beach-header { margin-bottom: 1.25rem; }
    .beach-header h1 { margin: .25rem 0; font-size: clamp(1.5rem, 3vw, 2rem); font-weight: 750; }
    .beach-header p { max-width: 70ch; color: var(--text-secondary); }
    .beach-kicker { color: var(--role-accent); font-size: .72rem; font-weight: 750; letter-spacing: .1em; text-transform: uppercase; }
    .beach-panel { overflow: hidden; background: var(--bg-card); border: 1px solid var(--border); border-radius: 1rem; box-shadow: var(--shadow-sm); }
    .beach-heading { padding: 1rem 1.1rem; border-bottom: 1px solid var(--border); }
    .beach-heading h2 { font-size: 1rem; font-weight: 750; }
    .beach-filters { display: grid; grid-template-columns: minmax(200px, 1fr) minmax(180px, .7fr) auto; gap: .75rem; padding: 1rem; }
    .beach-filters input, .beach-filters select { width: 100%; min-height: 42px; padding: .55rem .75rem; color: var(--text-primary); background: var(--bg-input); border: 1px solid var(--border); border-radius: .7rem; }
    .beach-button { padding: .65rem 1rem; color: white; font-weight: 700; background: var(--role-accent); border: 0; border-radius: .7rem; cursor: pointer; }
    .beach-results { max-height: 560px; overflow-y: auto; padding: .5rem; }
    .beach-result { display: block; width: 100%; padding: .8rem; color: var(--text-primary); border-bottom: 1px solid var(--border); }
    .beach-result small { display: block; margin-top: .2rem; color: var(--text-secondary); }
    .beach-status { display: inline-block; margin-top: .45rem; padding: .25rem .55rem; color: #9f1239; font-size: .75rem; font-weight: 700; background: #ffe4e6; border-radius: 999px; }
    .beach-message { padding: 1rem; color: var(--text-secondary); font-size: .875rem; }
    .beach-load-more { display: block; margin: .75rem auto; color: var(--role-accent); font-weight: 700; background: transparent; border: 0; cursor: pointer; }
    @media (max-width: 800px) { .beach-filters { grid-template-columns: 1fr 1fr; } }
    @media (max-width: 600px) { .beach-page { padding: 1rem; } .beach-filters { grid-template-columns: 1fr; } }
</style>
@endpush

@section('frontoffice-content')
<section class="beach-page" aria-labelledby="beach-page-title">
    <header class="beach-header">
        <span class="beach-kicker">Information publique</span>
        <h1 id="beach-page-title">Interdictions de baignade signal&eacute;es</h1>
        <p>Consultez les points signal&eacute;s comme interdits et recherchez par nom ou gouvernorat.</p>
    </header>

    <section class="beach-panel" aria-labelledby="beach-list-title">
        <div class="beach-heading">
            <h2 id="beach-list-title">Points de baignade avec restriction signal&eacute;e</h2>
            <small id="beach-summary" aria-live="polite"></small>
        </div>
        <form id="beach-filters" class="beach-filters" role="search">
            <label>
                <span class="sr-only">Rechercher un point</span>
                <input id="beach-search" type="search" maxlength="120" placeholder="Rechercher par nom">
            </label>
            <label>
                <span class="sr-only">Filtrer par gouvernorat</span>
                <select id="governorate-filter"><option value="">Tous les gouvernorats</option></select>
            </label>
            <button class="beach-button" type="submit">Rechercher</button>
        </form>
        <p class="beach-message">Relev&eacute; historique dat&eacute; du 20/06/2026. Il ne confirme pas l&rsquo;&eacute;tat actuel de la baignade ; v&eacute;rifiez les consignes locales avant de vous baigner.</p>
        <div id="beach-results" class="beach-results" aria-live="polite"><p class="beach-message">Chargement des points signal&eacute;s...</p></div>
        <button id="load-more" class="beach-load-more" type="button" hidden>Charger la suite</button>
    </section>
</section>
@endsection

@push('scripts')
<script>
(() => {
    const list = document.getElementById('beach-results');
    const summary = document.getElementById('beach-summary');
    const governorateFilter = document.getElementById('governorate-filter');
    const loadMore = document.getElementById('load-more');
    let page = 1;
    let lastPage = 1;

    const api = (path) => `/api/v1/${path}`;

    async function getJson(url) {
        const response = await fetch(url, { headers: { Accept: 'application/json' } });
        if (!response.ok) throw new Error('Le service est temporairement indisponible.');
        return response.json();
    }

    function render(points, append) {
        if (!append) list.replaceChildren();
        if (!points.length && !append) {
            list.innerHTML = '<p class="beach-message">Aucun point avec une restriction signal&eacute;e ne correspond a cette recherche.</p>';
            return;
        }
        points.forEach((point) => {
            const row = document.createElement('article');
            row.className = 'beach-result';
            const name = document.createElement('strong');
            name.textContent = point.name;
            const location = document.createElement('small');
            location.textContent = point.governorate;
            const status = document.createElement('span');
            status.className = 'beach-status';
            status.textContent = 'Interdiction signal\u00e9e (historique)';
            row.append(name, location, status);
            const reportedAt = document.createElement('small');
            reportedAt.textContent = `Relev\u00e9 du ${new Date(`${point.reported_at}T00:00:00`).toLocaleDateString('fr-FR')}`;
            row.append(reportedAt);
            if (point.source?.name) {
                const source = document.createElement('small');
                source.textContent = `Source : ${point.source.name}`;
                row.append(source);
                if (point.source.url) {
                    const link = document.createElement('a');
                    link.href = point.source.url;
                    link.target = '_blank';
                    link.rel = 'noopener noreferrer';
                    link.textContent = 'Consulter la source';
                    row.append(link);
                }
            }
            list.append(row);
        });
    }

    async function loadPoints(append = false) {
        if (!append) page = 1;
        list.setAttribute('aria-busy', 'true');
        const params = new URLSearchParams({ page: String(page), per_page: '100', status: 'restricted' });
        const query = document.getElementById('beach-search').value.trim();
        if (query) params.set('q', query);
        if (governorateFilter.value) params.set('governorate', governorateFilter.value);
        try {
            const result = await getJson(api(`historical-advisories?${params.toString()}`));
            render(result.data, append);
            lastPage = result.meta.last_page;
            summary.textContent = `${result.meta.total} point(s) signal\u00e9(s) trouv\u00e9(s).`;
            loadMore.hidden = page >= lastPage;
        } catch (error) {
            if (!append) list.innerHTML = `<p class="beach-message">${error.message}</p>`;
            loadMore.hidden = true;
        } finally {
            list.removeAttribute('aria-busy');
        }
    }

    async function loadGovernorates() {
        try {
            const result = await getJson(api('governorates?per_page=100'));
            result.data.forEach((governorate) => {
                const option = document.createElement('option');
                option.value = governorate.slug;
                option.textContent = governorate.name;
                governorateFilter.append(option);
            });
        } catch (_) {
            // La recherche par nom reste disponible si la liste des gouvernorats ne charge pas.
        }
    }

    document.getElementById('beach-filters').addEventListener('submit', (event) => {
        event.preventDefault();
        loadPoints(false);
    });
    loadMore.addEventListener('click', () => { page += 1; loadPoints(true); });
    loadGovernorates();
    loadPoints();
})();
</script>
@endpush
