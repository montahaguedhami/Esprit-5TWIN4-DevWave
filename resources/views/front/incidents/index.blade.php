@extends('layouts.frontoffice')

@section('frontoffice-content')
<div class="citizen-incidents-page">
    <header class="citizen-incidents-heading">
        <div class="citizen-incidents-title">
            <span class="citizen-incidents-icon"><i data-lucide="clipboard-list"></i></span>
            <div>
                <p class="citizen-incidents-eyebrow">Portail citoyen <span>•</span> Suivi de vos demandes</p>
                <h1>Mes incidents</h1>
                <p class="citizen-incidents-description">Consultez l’avancement de vos signalements et les interventions associées.</p>
            </div>
        </div>
        <a href="{{ route('incidents.create') }}" class="citizen-incidents-action">
            <i data-lucide="plus"></i><span>Signaler un incident</span>
        </a>
    </header>

    @if(session('success'))
        <div role="status" class="citizen-incidents-success">
            <i data-lucide="circle-check"></i><span>{{ session('success') }}</span>
        </div>
    @endif

    <div class="mb-6 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
        <form method="GET" action="{{ route('incidents.index') }}" class="grid gap-3 md:grid-cols-2 xl:grid-cols-6">
            <div class="xl:col-span-2">
                <label class="mb-1 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">Recherche</label>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Titre, description, localisation" class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm text-slate-900 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-100" />
            </div>
            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">Type</label>
                <select name="type" class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm text-slate-900 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-100">
                    <option value="">Tous</option>
                    @foreach(['fuite' => 'Fuite', 'pollution' => 'Pollution', 'panne' => 'Panne', 'rupture' => 'Rupture', 'contamination' => 'Contamination'] as $value => $label)
                        <option value="{{ $value }}" @selected(request('type') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">Gravité</label>
                <select name="gravite" class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm text-slate-900 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-100">
                    <option value="">Toutes</option>
                    @foreach(['faible' => 'Faible', 'moyenne' => 'Moyenne', 'elevee' => 'Élevée', 'critique' => 'Critique'] as $value => $label)
                        <option value="{{ $value }}" @selected(request('gravite') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="md:col-span-2 xl:col-span-2">
                <label class="mb-1 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">Période</label>
                <div class="flex gap-2">
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm text-slate-900 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-100" />
                    <input type="date" name="date_to" value="{{ request('date_to') }}" class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm text-slate-900 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-100" />
                </div>
            </div>
            <div class="md:col-span-2 xl:col-span-2 flex items-end gap-2">
                <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-cyan-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-cyan-500">Filtrer</button>
                <a href="{{ route('incidents.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-100">Réinitialiser</a>
            </div>
        </form>
    </div>

    <div class="citizen-incident-list">
        @forelse($incidents as $incident)
            @php
                $incidentSeverity = strtolower((string) $incident->gravite);
                $incidentSeverityLabel = ['elevee' => 'Élevée', 'faible' => 'Faible', 'moyenne' => 'Moyenne'][$incidentSeverity] ?? ucfirst(str_replace('_', ' ', (string) $incident->gravite));
                $latestAction = $incident->actions->sortByDesc('created_at')->first();
                $hasNewAction = $latestAction && $latestAction->created_at && $latestAction->created_at->diffInDays(now()) <= 30;
            @endphp
            <article class="citizen-incident-card" data-severity="{{ $incidentSeverity }}">
                <span class="citizen-incident-type-icon"><i data-lucide="{{ strtolower((string) $incident->type) === 'contamination' ? 'flask-conical' : 'droplets' }}"></i></span>
                <div class="citizen-incident-content">
                    <div class="citizen-incident-card-top">
                        <div class="citizen-incident-name">
                            <span class="citizen-incident-type">{{ ucfirst((string) $incident->type) }}</span>
                            <a href="{{ route('incidents.show', $incident) }}" class="citizen-incident-title">{{ $incident->titre }}</a>
                        </div>
                        <time class="citizen-incident-date">
                            <i data-lucide="calendar-days"></i>
                            <span>Signalé le {{ optional($incident->date_signalement)->format('d/m/Y à H:i') }}</span>
                        </time>
                    </div>
                    <div class="citizen-incident-meta">
                        @if($hasNewAction)
                            <span class="citizen-incident-badge bg-amber-100 text-amber-800 border border-amber-200">Nouvelle action</span>
                        @endif
                        <span class="citizen-incident-badge citizen-incident-severity">
                            <i data-lucide="triangle-alert"></i>Gravité {{ $incidentSeverityLabel }}
                        </span>
                    </div>
                    <p class="mt-2 text-xs text-slate-600">
                        <i data-lucide="map-pin" class="mr-1 inline h-3.5 w-3.5"></i>{{ $incident->localisation }}
                        @if($incident->infrastructure)
                            <span class="mx-1">·</span>{{ $incident->infrastructure->nom }}
                        @endif
                    </p>
                </div>
                <a href="{{ route('incidents.show', $incident) }}" class="citizen-incident-open" aria-label="Consulter l’incident : {{ $incident->titre }}">
                    <i data-lucide="arrow-up-right"></i>
                </a>
            </article>
        @empty
            <div class="citizen-incidents-empty">
                <span class="citizen-incidents-empty-icon"><i data-lucide="clipboard-check"></i></span>
                <h2>Aucun résultat</h2>
                <p>Aucun incident ne correspond à vos filtres actuels. Essayez d’élargir votre recherche ou réinitialisez les filtres.</p>
                <a href="{{ route('incidents.index') }}" class="citizen-incidents-action">
                    <i data-lucide="rotate-ccw"></i><span>Réinitialiser</span>
                </a>
            </div>
        @endforelse
    </div>

    <div class="citizen-incidents-pagination">
        {{ $incidents->links() }}
    </div>
</div>
@endsection
