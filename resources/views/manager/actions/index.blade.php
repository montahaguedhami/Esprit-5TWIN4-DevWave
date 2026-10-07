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
                        <td class="py-3 pr-4">{{ $action->responsable ?? '-' }}</td>
                        <td class="py-3 pr-4">{{ $action->statut }}</td>
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
                    <tr><td colspan="5" class="py-6 text-slate-300">Aucune action corrective enregistr&eacute;e pour cet incident.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-6">{{ $actions->links() }}</div>
</div>
@endsection