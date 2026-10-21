@extends('layouts.manager')

@section('manager-content')
<div class="max-w-5xl mx-auto">
    <a href="{{ route('manager.incidents.show', $incident) }}" class="text-cyan-300">Retour &agrave; l'incident</a>
    <div class="flex flex-wrap items-center justify-between gap-4 my-6">
        <div class="min-w-0">
            <h1 class="text-2xl font-bold">Actions correctives</h1>
            <p class="mt-2 text-slate-300 wrap-break-word">{{ $incident->titre }}</p>
        </div>
        <a href="{{ route('manager.actions.create', $incident) }}" class="inline-flex items-center gap-2 px-4 py-2 rounded bg-cyan-500 text-white"><i data-lucide="plus" class="w-4 h-4"></i>Ajouter une action</a>
    </div>
    @if(session('success'))
        <div role="status" class="mb-4 p-3 bg-green-600 rounded">{{ session('success') }}</div>
    @endif

    <div class="mb-6 rounded-xl border border-slate-700 bg-slate-900/40 p-4">
        <form method="GET" action="{{ route('manager.actions.index', $incident) }}" class="grid gap-3 md:grid-cols-2 xl:grid-cols-6">
            <div class="xl:col-span-2">
                <label for="q" class="mb-1 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Recherche</label>
                <input id="q" type="text" name="q" value="{{ request('q') }}" placeholder="Titre, description, responsable" class="w-full rounded-lg border border-slate-600 bg-slate-950/60 px-3 py-2 text-sm text-white focus:border-cyan-400 focus:ring-2 focus:ring-cyan-400/20" />
            </div>
            <div>
                <label for="status" class="mb-1 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Statut</label>
                <select id="status" name="status" class="w-full rounded-lg border border-slate-600 bg-slate-950/60 px-3 py-2 text-sm text-white focus:border-cyan-400 focus:ring-2 focus:ring-cyan-400/20">
                    <option value="">Tous</option>
                    @foreach(['a_faire' => 'À faire', 'en_cours' => 'En cours', 'terminee' => 'Terminée'] as $value => $label)
                        <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="responsable" class="mb-1 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Responsable</label>
                <input id="responsable" type="text" name="responsable" value="{{ request('responsable') }}" class="w-full rounded-lg border border-slate-600 bg-slate-950/60 px-3 py-2 text-sm text-white focus:border-cyan-400 focus:ring-2 focus:ring-cyan-400/20" />
            </div>
            <div>
                <label for="sort-by" class="mb-1 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Tri</label>
                <select id="sort-by" name="sort_by" class="w-full rounded-lg border border-slate-600 bg-slate-950/60 px-3 py-2 text-sm text-white focus:border-cyan-400 focus:ring-2 focus:ring-cyan-400/20">
                    <option value="created_at" @selected(request('sort_by', 'created_at') === 'created_at')>Date de création</option>
                    <option value="date_prevue" @selected(request('sort_by') === 'date_prevue')>Échéance</option>
                    <option value="statut" @selected(request('sort_by') === 'statut')>Statut</option>
                    <option value="titre" @selected(request('sort_by') === 'titre')>Titre</option>
                </select>
            </div>
            <div>
                <label for="sort-dir" class="mb-1 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Ordre</label>
                <select id="sort-dir" name="sort_dir" class="w-full rounded-lg border border-slate-600 bg-slate-950/60 px-3 py-2 text-sm text-white focus:border-cyan-400 focus:ring-2 focus:ring-cyan-400/20">
                    <option value="desc" @selected(request('sort_dir', 'desc') === 'desc')>Décroissant</option>
                    <option value="asc" @selected(request('sort_dir') === 'asc')>Croissant</option>
                </select>
            </div>
            <div class="md:col-span-2 xl:col-span-2">
                <label for="date-from" class="mb-1 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Échéance</label>
                <div class="flex gap-2">
                    <input id="date-from" type="date" name="date_from" value="{{ request('date_from') }}" class="w-full rounded-lg border border-slate-600 bg-slate-950/60 px-3 py-2 text-sm text-white focus:border-cyan-400 focus:ring-2 focus:ring-cyan-400/20" />
                    <label for="date-to" class="sr-only">date to</label>
                    <input id="date-to" type="date" name="date_to" value="{{ request('date_to') }}" class="w-full rounded-lg border border-slate-600 bg-slate-950/60 px-3 py-2 text-sm text-white focus:border-cyan-400 focus:ring-2 focus:ring-cyan-400/20" />
                </div>
            </div>
            <div class="md:col-span-2 xl:col-span-2 flex items-end gap-2">
                <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-cyan-500 px-4 py-2 text-sm font-semibold text-white hover:bg-cyan-400">Filtrer</button>
                <a href="{{ route('manager.actions.index', $incident) }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-600 px-4 py-2 text-sm font-semibold text-slate-300 hover:bg-slate-800">Réinitialiser</a>
            </div>
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full min-w-160 text-left">
            <thead>
                <tr class="text-slate-400 text-sm">
                    <th scope="col" class="pb-3 pr-4">Titre</th>
                    <th scope="col" class="pb-3 pr-4">Responsable</th>
                    <th scope="col" class="pb-3 pr-4">Statut</th>
                    <th scope="col" class="pb-3 pr-4">Date pr&eacute;vue</th>
                    <th scope="col" class="pb-3">Commandes</th>
                </tr>
            </thead>
            <tbody>
                @forelse($actions as $action)
                    <tr class="border-t border-white/10">
                        <td class="py-3 pr-4 wrap-break-word">{{ $action->titre }}</td>
                        <td class="py-3 pr-4">{{ $action->technicien?->nom ?? $action->responsable ?? '-' }}</td>
                        <td class="py-3 pr-4"><x-incident-action-status :status="$action->statut" /></td>
                        <td class="py-3 pr-4">{{ $action->date_prevue?->format('d/m/Y') ?? '-' }}</td>
                        <td class="py-3">
                            <div class="flex items-center gap-4">
                                <a href="{{ route('manager.actions.show', $action) }}" class="text-cyan-300" title="Voir l'action" aria-label="Voir l'action"><i data-lucide="eye" class="w-5 h-5"></i></a>
                                <a href="{{ route('manager.actions.edit', $action) }}" class="text-amber-300" title="Modifier l'action" aria-label="Modifier l'action"><i data-lucide="pencil" class="w-5 h-5"></i></a>
                                <form action="{{ route('manager.actions.destroy', $action) }}" method="post">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-red-400" title="Supprimer l'action" aria-label="Supprimer l'action" onclick="return confirm('Supprimer cette action corrective ?')"><i data-lucide="trash-2" class="w-5 h-5"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="py-6 text-slate-300">Aucune action corrective ne correspond aux critères choisis.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-6">{{ $actions->links() }}</div>
</div>
@endsection