@extends('layouts.manager')

@section('manager-content')
<div class="max-w-6xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold">Gestion des incidents</h1>
    </div>

    @if(session('success'))
        <div class="mb-4 p-3 bg-green-600 text-white rounded">{{ session('success') }}</div>
    @endif

    <div class="mb-6 rounded-xl border border-slate-700 bg-slate-900/40 p-4">
        <form method="GET" action="{{ route('manager.incidents') }}" class="grid gap-3 md:grid-cols-2 xl:grid-cols-6">
            <div class="xl:col-span-2">
                <label for="incident-search" class="mb-1 block text-xs font-semibold uppercase text-slate-400">Recherche</label>
                <input id="incident-search" type="text" name="q" value="{{ request('q') }}" placeholder="Titre, description ou localisation" class="w-full rounded-lg border border-slate-600 bg-slate-950/60 px-3 py-2 text-sm text-white focus:border-cyan-400 focus:ring-2 focus:ring-cyan-400/20">
            </div>
            <div>
                <label for="incident-type" class="mb-1 block text-xs font-semibold uppercase text-slate-400">Type</label>
                <select id="incident-type" name="type" class="w-full rounded-lg border border-slate-600 bg-slate-950/60 px-3 py-2 text-sm text-white focus:border-cyan-400 focus:ring-2 focus:ring-cyan-400/20">
                    <option value="">Tous</option>
                    @foreach(['fuite' => 'Fuite', 'pollution' => 'Pollution', 'panne' => 'Panne', 'rupture' => 'Rupture', 'contamination' => 'Contamination'] as $value => $label)
                        <option value="{{ $value }}" @selected(request('type') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="incident-severity" class="mb-1 block text-xs font-semibold uppercase text-slate-400">Gravité</label>
                <select id="incident-severity" name="gravite" class="w-full rounded-lg border border-slate-600 bg-slate-950/60 px-3 py-2 text-sm text-white focus:border-cyan-400 focus:ring-2 focus:ring-cyan-400/20">
                    <option value="">Toutes</option>
                    @foreach(['faible' => 'Faible', 'moyenne' => 'Moyenne', 'elevee' => 'Élevée', 'critique' => 'Critique'] as $value => $label)
                        <option value="{{ $value }}" @selected(request('gravite') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="md:col-span-2 xl:col-span-2">
                <label for="date-from" class="mb-1 block text-xs font-semibold uppercase text-slate-400">Date de signalement</label>
                <div class="flex gap-2">
                    <input id="date-from" type="date" name="date_from" value="{{ request('date_from') }}" aria-label="Date de début" class="w-full rounded-lg border border-slate-600 bg-slate-950/60 px-3 py-2 text-sm text-white focus:border-cyan-400 focus:ring-2 focus:ring-cyan-400/20">
                    <label for="date-to" class="sr-only">Date de fin</label>
                    <input id="date-to" type="date" name="date_to" value="{{ request('date_to') }}" aria-label="Date de fin" class="w-full rounded-lg border border-slate-600 bg-slate-950/60 px-3 py-2 text-sm text-white focus:border-cyan-400 focus:ring-2 focus:ring-cyan-400/20">
                </div>
            </div>
            <div class="md:col-span-2 xl:col-span-3 flex items-end gap-2">
                <button type="submit" class="rounded-lg bg-cyan-500 px-4 py-2 text-sm font-semibold text-white hover:bg-cyan-400">Filtrer</button>
                <a href="{{ route('manager.incidents') }}" class="rounded-lg border border-slate-600 px-4 py-2 text-sm font-semibold text-slate-300 hover:bg-slate-800">Réinitialiser</a>
            </div>
        </form>
    </div>

    <div class="overflow-x-auto">
    <table class="w-full min-w-160 text-left border-collapse">
        <thead>
            <tr class="text-sm text-slate-400">
                <th class="pb-3">Titre</th>
                <th class="pb-3">Type</th>
                <th class="pb-3">Gravité</th>
                <th class="pb-3">Citoyen</th>
                <th class="pb-3">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($incidents as $incident)
            <tr class="border-t border-white/5">
                <td class="py-3">{{ $incident->titre }}</td>
                <td class="py-3">{{ $incident->type }}</td>
                <td class="py-3">{{ $incident->gravite }}</td>
                <td class="py-3">{{ $incident->user?->name }}</td>
                <td class="py-3">
                    <a href="{{ route('manager.incidents.show', $incident) }}" class="inline-flex items-center gap-2 text-cyan-300"><i data-lucide="eye" class="w-4 h-4"></i>Voir ({{ $incident->actions_count }})</a>
                </td>
            </tr>
            @empty
                <tr><td colspan="5" class="py-6 text-slate-300">Aucun incident ne correspond aux filtres.</td></tr>
            @endforelse
        </tbody>
    </table>
    </div>
    <div class="mt-6">{{ $incidents->links() }}</div>
</div>
@endsection
