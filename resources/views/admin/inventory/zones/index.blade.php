@extends('layouts.admin')

@section('title', 'Consultation des zones')
@section('page-title', 'Zones')

@section('admin-content')
<div class="mx-auto max-w-7xl space-y-6 p-4 sm:p-6">
    <header>
        <p class="text-sm font-semibold text-violet-600">Consultation du réseau</p>
        <h1 class="mt-1 text-2xl font-bold text-slate-900">Zones</h1>
        <p class="mt-1 text-sm text-slate-500">Vue en lecture seule des zones et de leurs infrastructures.</p>
    </header>

    <form method="GET" action="{{ route('admin.zones.index') }}" class="flex flex-wrap gap-2">
        <label for="q" class="sr-only">Rechercher une zone</label>
        <input id="q" name="q" value="{{ $filters['q'] }}" placeholder="Rechercher une zone…" class="min-w-60 rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-800">
        <button class="rounded-xl bg-violet-600 px-4 py-2 text-sm font-semibold text-white">Rechercher</button>
        @if($filters['q'] !== '')<a href="{{ route('admin.zones.index') }}" class="rounded-xl border border-slate-200 px-4 py-2 text-sm text-slate-600">Effacer</a>@endif
    </form>

    <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-sm">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500"><tr><th class="px-5 py-3">Zone</th><th class="px-5 py-3">Localisation</th><th class="px-5 py-3">Infrastructures</th><th class="px-5 py-3">Responsable</th><th class="px-5 py-3">Statut</th><th class="px-5 py-3"></th></tr></thead>
            <tbody class="divide-y divide-slate-100 text-slate-700">
                @forelse($zones as $zone)
                <tr class="hover:bg-slate-50"><td class="px-5 py-4 font-semibold text-slate-900">{{ $zone->nom }}</td><td class="px-5 py-4">{{ $zone->localisation ?: '—' }}</td><td class="px-5 py-4">{{ $zone->infrastructures_count }}</td><td class="px-5 py-4">{{ $zone->responsable?->name ?? '—' }}</td><td class="px-5 py-4">{{ $zone->statut?->label() ?? $zone->statut ?? '—' }}</td><td class="px-5 py-4 text-right"><a class="font-semibold text-violet-700 hover:underline" href="{{ route('admin.zones.show', $zone) }}">Consulter</a></td></tr>
                @empty
                <tr><td colspan="6" class="px-5 py-12 text-center text-slate-500">Aucune zone ne correspond à votre recherche.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div>{{ $zones->links() }}</div>
</div>
@endsection
