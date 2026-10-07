@extends('layouts.manager')

@section('manager-content')
<div class="max-w-3xl mx-auto">
    <a href="{{ route('manager.actions.index', $action->incident_id) }}" class="text-cyan-300">Retour aux actions</a>
    @if(session('success'))
        <div role="status" class="mt-4 p-3 bg-green-600 rounded">{{ session('success') }}</div>
    @endif
    <h1 class="text-2xl font-bold mt-6 wrap-break-word">{{ $action->titre }}</h1>
    <a href="{{ route('manager.incidents.show', $action->incident_id) }}" class="inline-block mt-2 text-cyan-300 wrap-break-word">Incident : {{ $action->incident->titre }}</a>
    <p class="mt-6 whitespace-pre-line wrap-break-word">{{ $action->description }}</p>
    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 py-6 border-b border-white/15">
        <div><dt class="text-slate-400">Responsable</dt><dd class="wrap-break-word">{{ $action->responsable ?? '-' }}</dd></div>
        <div><dt class="text-slate-400">Statut</dt><dd>{{ $action->statut }}</dd></div>
        <div><dt class="text-slate-400">Date pr&eacute;vue</dt><dd>{{ $action->date_prevue?->format('d/m/Y') ?? '-' }}</dd></div>
        <div><dt class="text-slate-400">Date de r&eacute;alisation</dt><dd>{{ $action->date_realisation?->format('d/m/Y H:i') ?? '-' }}</dd></div>
    </dl>
    <h2 class="text-lg font-semibold mt-6">R&eacute;sultat</h2>
    <p class="mt-2 whitespace-pre-line wrap-break-word">{{ $action->resultat ?? '-' }}</p>
    <div class="flex flex-wrap gap-4 mt-6">
        <a href="{{ route('manager.actions.edit', $action) }}" class="inline-flex items-center gap-2 text-amber-300"><i data-lucide="pencil" class="w-4 h-4"></i>Modifier l'action</a>
        <form action="{{ route('manager.actions.destroy', $action) }}" method="post">
            @csrf
            @method('DELETE')
            <button class="inline-flex items-center gap-2 text-red-400" onclick="return confirm('Supprimer cette action corrective ?')"><i data-lucide="trash-2" class="w-4 h-4"></i>Supprimer l'action</button>
        </form>
    </div>
</div>
@endsection