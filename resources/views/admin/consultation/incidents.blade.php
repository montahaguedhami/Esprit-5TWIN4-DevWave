@extends('layouts.admin')

@section('title', 'Incidents consultables')
@section('page-title', 'Incidents')

@section('admin-content')
<div class="mx-auto max-w-7xl space-y-6 p-4 sm:p-6">
    <header>
        <p class="text-sm font-semibold text-violet-600">Consultation en lecture seule</p>
        <h1 class="mt-1 text-2xl font-bold text-slate-900">Incidents</h1>
        <p class="mt-1 text-sm text-slate-500">Recherche, filtre et tri des incidents signalés.</p>
    </header>

    <form method="GET" action="{{ route('admin.incidents') }}" class="grid gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm md:grid-cols-2 xl:grid-cols-6">
        <div class="xl:col-span-2">
            <label for="incident-q" class="mb-1 block text-xs font-semibold uppercase text-slate-500">Recherche</label>
            <input id="incident-q" name="q" value="{{ request('q') }}" placeholder="Titre, description, localisation" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900">
        </div>
        <div>
            <label for="incident-type" class="mb-1 block text-xs font-semibold uppercase text-slate-500">Type</label>
            <select id="incident-type" name="type" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900">
                <option value="">Tous</option>
                @foreach(['fuite' => 'Fuite', 'pollution' => 'Pollution', 'panne' => 'Panne', 'rupture' => 'Rupture', 'contamination' => 'Contamination'] as $value => $label)
                    <option value="{{ $value }}" @selected(request('type') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="incident-gravite" class="mb-1 block text-xs font-semibold uppercase text-slate-500">Gravité</label>
            <select id="incident-gravite" name="gravite" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900">
                <option value="">Toutes</option>
                @foreach(['faible' => 'Faible', 'moyenne' => 'Moyenne', 'elevee' => 'Élevée', 'critique' => 'Critique'] as $value => $label)
                    <option value="{{ $value }}" @selected(request('gravite') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="incident-sort" class="mb-1 block text-xs font-semibold uppercase text-slate-500">Trier par</label>
            <select id="incident-sort" name="sort_by" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900">
                <option value="date_signalement" @selected(request('sort_by', 'date_signalement') === 'date_signalement')>Date</option>
                <option value="titre" @selected(request('sort_by') === 'titre')>Titre</option>
                <option value="type" @selected(request('sort_by') === 'type')>Type</option>
                <option value="gravite" @selected(request('sort_by') === 'gravite')>Gravité</option>
            </select>
        </div>
        <div>
            <label for="incident-order" class="mb-1 block text-xs font-semibold uppercase text-slate-500">Ordre</label>
            <select id="incident-order" name="sort_dir" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900">
                <option value="desc" @selected(request('sort_dir', 'desc') === 'desc')>Décroissant</option>
                <option value="asc" @selected(request('sort_dir') === 'asc')>Croissant</option>
            </select>
        </div>
        <div class="md:col-span-2 xl:col-span-3">
            <label class="mb-1 block text-xs font-semibold uppercase text-slate-500">Date de signalement</label>
            <div class="flex gap-2">
                <input type="date" name="date_from" value="{{ request('date_from') }}" aria-label="Date de début" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900">
                <input type="date" name="date_to" value="{{ request('date_to') }}" aria-label="Date de fin" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900">
            </div>
        </div>
        <div class="md:col-span-2 xl:col-span-3 flex items-end gap-2">
            <button type="submit" class="rounded-lg bg-cyan-700 px-4 py-2 text-sm font-semibold text-white hover:bg-cyan-800">Rechercher</button>
            <a href="{{ route('admin.incidents') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Réinitialiser</a>
        </div>
    </form>

    <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-sm">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                <tr>
                    <th scope="col" class="px-5 py-3">Incident</th>
                    <th scope="col" class="px-5 py-3">Type</th>
                    <th scope="col" class="px-5 py-3">Gravité</th>
                    <th scope="col" class="px-5 py-3">Zone / infrastructure</th>
                    <th scope="col" class="px-5 py-3">Déclaré par</th>
                    <th scope="col" class="px-5 py-3">Date</th>
                    <th scope="col" class="px-5 py-3">Actions correctives</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-slate-700">
                @forelse($incidents as $incident)
                    <tr>
                        <td class="px-5 py-4">
                            <span class="font-semibold text-slate-900">{{ $incident->titre }}</span>
                            <span class="block max-w-md truncate text-xs text-slate-500">{{ $incident->description }}</span>
                        </td>
                        <td class="px-5 py-4">{{ ucfirst($incident->type) }}</td>
                        <td class="px-5 py-4">{{ ucfirst($incident->gravite) }}</td>
                        <td class="px-5 py-4">{{ $incident->zone?->nom ?? $incident->infrastructure?->nom ?? $incident->localisation }}</td>
                        <td class="px-5 py-4">{{ $incident->user?->name ?? '—' }}</td>
                        <td class="whitespace-nowrap px-5 py-4">{{ $incident->date_signalement?->format('d/m/Y H:i') }}</td>
                        <td class="px-5 py-4">
                            <details>
                                <summary class="cursor-pointer font-semibold text-cyan-700">Actions ({{ $incident->actions->count() }})</summary>
                                <ul class="mt-3 min-w-64 space-y-4">
                                    @forelse($incident->actions as $action)
                                        <li class="border-t border-slate-200 pt-3">
                                            <p class="font-semibold text-slate-900">{{ $action->titre }}</p>
                                            <p class="mt-1 whitespace-pre-line">{{ $action->description }}</p>
                                            <dl class="mt-2 space-y-1 text-xs text-slate-500">
                                                <div><dt class="inline font-semibold">Statut :</dt> <dd class="inline">{{ ['a_faire' => 'À faire', 'en_cours' => 'En cours', 'terminee' => 'Terminée'][$action->statut] ?? $action->statut }}</dd></div>
                                                <div><dt class="inline font-semibold">Responsable :</dt> <dd class="inline">{{ $action->technicien?->nom ?? $action->responsable ?? 'Non assigné' }}</dd></div>
                                                <div><dt class="inline font-semibold">Date prévue :</dt> <dd class="inline">{{ $action->date_prevue?->format('d/m/Y') ?? '—' }}</dd></div>
                                                <div><dt class="inline font-semibold">Réalisation :</dt> <dd class="inline">{{ $action->date_realisation?->format('d/m/Y H:i') ?? '—' }}</dd></div>
                                            </dl>
                                            @if($action->resultat)
                                                <p class="mt-2 whitespace-pre-line text-xs">{{ $action->resultat }}</p>
                                            @endif
                                        </li>
                                    @empty
                                        <li class="text-xs text-slate-500">Aucune action corrective.</li>
                                    @endforelse
                                </ul>
                            </details>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-5 py-10 text-center text-slate-500">Aucun incident ne correspond à ces critères.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $incidents->links() }}
</div>
@endsection