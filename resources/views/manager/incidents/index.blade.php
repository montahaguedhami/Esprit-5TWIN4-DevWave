@extends('layouts.manager')

@section('manager-content')
<div class="max-w-6xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold">Gestion des incidents</h1>
    </div>

    @if(session('success'))
        <div class="mb-4 p-3 bg-green-600 text-white rounded">{{ session('success') }}</div>
    @endif

    <div class="overflow-x-auto">
    <table class="w-full min-w-160 text-left border-collapse">
        <thead>
            <tr class="text-sm text-slate-400">
                <th class="pb-3">Titre</th>
                <th class="pb-3">Type</th>
                <th class="pb-3">Gravité</th>
                <th class="pb-3">Statut</th>
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
                <td class="py-3">{{ $incident->statut }}</td>
                <td class="py-3">{{ $incident->user?->name }}</td>
                <td class="py-3">
                    <a href="{{ route('manager.incidents.show', $incident) }}" class="inline-flex items-center gap-2 text-cyan-300"><i data-lucide="eye" class="w-4 h-4"></i>Voir ({{ $incident->actions_count }})</a>
                </td>
            </tr>
            @empty
                <tr><td colspan="6" class="py-6 text-slate-300">Aucun incident signal&eacute;.</td></tr>
            @endforelse
        </tbody>
    </table>
    </div>
    <div class="mt-6">{{ $incidents->links() }}</div>
</div>
@endsection
