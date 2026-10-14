@extends('layouts.frontoffice')

@section('frontoffice-content')
<div class="max-w-4xl mx-auto py-12">
    <div class="flex flex-wrap gap-3 items-center justify-between mb-6">
        <h1 class="text-2xl font-bold">Mes incidents</h1>
        <a href="{{ route('incidents.create') }}" class="px-4 py-2 rounded bg-cyan-500 text-white">Signaler un incident</a>
    </div>

    @if(session('success'))
        <div class="mb-4 p-3 bg-green-600 text-white rounded">{{ session('success') }}</div>
    @endif

    <div class="space-y-4">
        @forelse($incidents as $incident)
            <div class="p-4 bg-white/5 rounded">
                <a href="{{ route('incidents.show', $incident) }}" class="text-lg font-semibold wrap-break-word">{{ $incident->titre }}</a>
                <div class="text-sm text-slate-300">{{ $incident->type }} · Gravité: {{ $incident->gravite }} · Statut: {{ $incident->statut }}</div>
                <div class="text-xs text-slate-400 mt-2">Signalé: {{ optional($incident->date_signalement)->format('Y-m-d H:i') }}</div>
            </div>
        @empty
            <p class="text-slate-300">Vous n'avez pas encore signal&eacute; d'incident.</p>
        @endforelse
    </div>

    <div class="mt-6">
        {{ $incidents->links() }}
    </div>
</div>
@endsection
