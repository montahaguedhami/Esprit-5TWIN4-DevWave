@extends('layouts.manager')

@section('manager-content')
<div class="max-w-4xl mx-auto">
    <a href="{{ route('manager.incidents') }}" class="text-cyan-300">Retour aux incidents</a>
    @if(session('success'))
        <div role="status" class="mt-4 p-3 bg-green-600 rounded">{{ session('success') }}</div>
    @endif
    <section class="py-6 border-b border-white/15">
        <h1 class="text-2xl font-bold wrap-break-word">{{ $incident->titre }}</h1>
        <p class="mt-2 text-slate-300">{{ $incident->type }} | {{ $incident->gravite }} | {{ $incident->statut }}</p>
        <p class="mt-4 whitespace-pre-line wrap-break-word">{{ $incident->description }}</p>
        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4 text-sm">
            <div><dt class="text-slate-400">Citoyen</dt><dd>{{ $incident->user?->name }}</dd></div>
            <div><dt class="text-slate-400">Date de signalement</dt><dd>{{ $incident->date_signalement?->format('d/m/Y H:i') }}</dd></div>
            <div><dt class="text-slate-400">Localisation</dt><dd class="wrap-break-word">{{ $incident->localisation }}</dd></div>
        </dl>
    </section>
    <section class="py-6 border-b border-white/15">
        <h2 class="text-xl font-semibold mb-4">Actions correctives</h2>
        <a href="{{ route('manager.actions.index', $incident) }}" class="inline-flex items-center gap-2 text-cyan-300 mb-4"><i data-lucide="list" class="w-4 h-4"></i>Liste des actions</a>
        <div class="space-y-4">
            @forelse($incident->actions as $action)
                <article class="p-4 bg-white/5 rounded wrap-break-word">
                    <h3 class="font-semibold">{{ $action->titre }}</h3>
                    <p class="mt-2 whitespace-pre-line">{{ $action->description }}</p>
                    <p class="mt-2 text-sm text-slate-300">Responsable : {{ $action->responsable }} | Statut : {{ $action->statut }}</p>
                    <p class="text-sm text-slate-300">Pr&eacute;vue : {{ $action->date_prevue?->format('d/m/Y') ?? '-' }} | R&eacute;alis&eacute;e : {{ $action->date_realisation?->format('d/m/Y H:i') ?? '-' }}</p>
                    <p class="mt-2 whitespace-pre-line">{{ $action->resultat }}</p>
                    <div class="flex flex-wrap gap-4 mt-4">
                        <a href="{{ route('manager.actions.show', $action) }}" class="inline-flex items-center gap-2 text-cyan-300"><i data-lucide="eye" class="w-4 h-4"></i>Voir le d&eacute;tail</a>
                        <a href="{{ route('manager.actions.edit', $action) }}" class="inline-flex items-center gap-2 text-amber-300"><i data-lucide="pencil" class="w-4 h-4"></i>Modifier l'action</a>
                        <form action="{{ route('manager.actions.destroy', $action) }}" method="post">
                            @csrf
                            @method('DELETE')
                            <button class="inline-flex items-center gap-2 text-red-400" onclick="return confirm('Supprimer cette action corrective ?')"><i data-lucide="trash-2" class="w-4 h-4"></i>Supprimer l'action</button>
                        </form>
                    </div>
                </article>
            @empty
                <p class="text-slate-300">Aucune action corrective enregistr&eacute;e.</p>
            @endforelse
        </div>
    </section>
    <section class="py-6">
        <h2 class="text-xl font-semibold mb-4">Ajouter une action corrective</h2>
        <form action="{{ route('incidents.actions.store', $incident) }}" method="post" class="space-y-4">
            @csrf
            @include('manager.actions.form', ['action' => null])
            <button class="inline-flex items-center gap-2 px-4 py-2 rounded bg-cyan-500 text-white"><i data-lucide="plus" class="w-4 h-4"></i>Ajouter l'action</button>
        </form>
    </section>
</div>
@endsection