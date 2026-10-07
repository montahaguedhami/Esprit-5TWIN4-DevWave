@extends('layouts.frontoffice')

@section('frontoffice-content')
<div class="max-w-3xl mx-auto py-12">
    <a href="{{ route('incidents.index') }}" class="text-sm text-cyan-300">← Retour aux incidents</a>

    @if(session('success'))
        <div role="status" class="mt-4 p-3 bg-green-600 rounded">{{ session('success') }}</div>
    @endif
    <div class="flex flex-wrap gap-4 mt-4">
        <a href="{{ route('incidents.edit', $incident) }}" class="inline-flex items-center gap-2 text-amber-300"><i data-lucide="pencil" class="w-4 h-4"></i>Modifier mon incident</a>
        <form action="{{ route('incidents.destroy', $incident) }}" method="post">
            @csrf
            @method('DELETE')
            <button class="inline-flex items-center gap-2 text-red-400" onclick="return confirm('Supprimer cet incident et ses actions ?')"><i data-lucide="trash-2" class="w-4 h-4"></i>Supprimer mon incident</button>
        </form>
    </div>
    <div class="mt-4 p-6 bg-white/5 rounded">
        <h1 class="text-2xl font-bold wrap-break-word">{{ $incident->titre }}</h1>
        <div class="text-sm text-slate-300">{{ $incident->type }} · Gravité: {{ $incident->gravite }} · Statut: {{ $incident->statut }}</div>
        <p class="mt-4 whitespace-pre-line wrap-break-word">{{ $incident->description }}</p>
        <div class="text-xs text-slate-400 mt-3">Localisation: {{ $incident->localisation }}</div>
        <div class="text-xs text-slate-400">Signalé: {{ optional($incident->date_signalement)->format('Y-m-d H:i') }}</div>
    </div>

    <div class="mt-6">
        <h2 class="text-lg font-semibold">Actions correctives</h2>
        <div class="space-y-3 mt-3">
            @foreach($incident->actions as $action)
                <div class="p-3 bg-white/5 rounded">
                    <div class="font-semibold">{{ $action->titre }}</div>
                    <div class="text-sm text-slate-300">Responsable: {{ $action->responsable }} · Statut: {{ $action->statut }}</div>
                    <div class="text-xs text-slate-400 mt-2">Prévue: {{ optional($action->date_prevue)->format('Y-m-d') }} · Réalisée: {{ optional($action->date_realisation)->format('Y-m-d H:i') }}</div>
                    <p class="mt-2 text-sm">{{ $action->resultat }}</p>
                </div>
            @endforeach
            @if($incident->actions->isEmpty())
                <div class="p-3 bg-white/5 rounded text-slate-400">Aucune action corrective enregistrée.</div>
            @endif
        </div>
    </div>

</div>
@endsection
