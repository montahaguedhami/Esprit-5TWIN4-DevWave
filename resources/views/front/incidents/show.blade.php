@extends('layouts.frontoffice')

@section('frontoffice-content')
<div class="max-w-4xl mx-auto py-8 sm:py-10 space-y-6">
    <a href="{{ route('incidents.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-cyan-700 hover:text-cyan-900">
        <i data-lucide="arrow-left" class="w-4 h-4"></i>Retour aux incidents
    </a>

    @if(session('success'))
        <div role="status" class="mt-4 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl">{{ session('success') }}</div>
    @endif
    <div class="flex flex-wrap gap-4 mt-4">
        <a href="{{ route('incidents.edit', $incident) }}" class="inline-flex items-center gap-2 text-amber-700 hover:text-amber-900 font-semibold"><i data-lucide="pencil" class="w-4 h-4"></i>Modifier mon incident</a>
        <form action="{{ route('incidents.destroy', $incident) }}" method="post">
            @csrf
            @method('DELETE')
            <button class="inline-flex items-center gap-2 text-red-700 hover:text-red-900 font-semibold" onclick="return confirm('Supprimer cet incident et ses actions ?')"><i data-lucide="trash-2" class="w-4 h-4"></i>Supprimer mon incident</button>
        </form>
    </div>
    <div class="mt-4 p-5 sm:p-7 bg-white border border-slate-200 rounded-2xl shadow-sm">
        <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 wrap-break-word">{{ $incident->titre }}</h1>
        <div class="mt-3 flex flex-wrap gap-x-3 gap-y-1 text-sm text-slate-600">
            <span>{{ $incident->type }}</span><span>Gravité : {{ $incident->gravite }}</span><span>Statut : {{ $incident->statut }}</span>
        </div>
        <p class="mt-5 whitespace-pre-line wrap-break-word text-slate-700 leading-relaxed">{{ $incident->description }}</p>
        <div class="text-sm text-slate-600 mt-4">Localisation : {{ $incident->localisation }}</div>
        @if($incident->zone)
            <div class="text-sm text-slate-600 mt-1">Zone : {{ $incident->zone->nom }}</div>
        @endif
        @if($incident->infrastructure)
            <div class="text-sm text-slate-600 mt-1">
                Équipement concerné :
                <a href="{{ route('citizen.infrastructures.show', $incident->infrastructure) }}" class="font-semibold text-cyan-700 hover:text-cyan-900">
                    {{ $incident->infrastructure->nom }}
                </a>
            </div>
        @endif
        <div class="text-xs text-slate-500 mt-1">Signalé : {{ optional($incident->date_signalement)->format('Y-m-d H:i') }}</div>
    </div>

    <div class="mt-6">
        <h2 class="text-lg font-semibold text-slate-900">Actions correctives</h2>
        <div class="space-y-3 mt-3">
            @foreach($incident->actions as $action)
                <div class="p-4 sm:p-5 bg-white border border-slate-200 rounded-xl shadow-sm">
                    <div class="font-semibold text-slate-900">{{ $action->titre }}</div>
                    <div class="text-sm text-slate-600 mt-1">Responsable : {{ $action->responsable }} · Statut : {{ $action->statut }}</div>
                    <div class="text-xs text-slate-500 mt-2">Prévue : {{ optional($action->date_prevue)->format('Y-m-d') }} · Réalisée : {{ optional($action->date_realisation)->format('Y-m-d H:i') }}</div>
                    <p class="mt-2 text-sm text-slate-700">{{ $action->resultat }}</p>
                </div>
            @endforeach
            @if($incident->actions->isEmpty())
                <div class="p-4 bg-white border border-slate-200 rounded-xl text-slate-500">Aucune action corrective enregistrée.</div>
            @endif
        </div>
    </div>

</div>
@endsection
