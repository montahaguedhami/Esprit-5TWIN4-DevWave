@extends('layouts.admin')

@section('title', 'Consultation des infrastructures')
@section('page-title', 'Infrastructures')

@section('admin-content')
<div class="mx-auto max-w-7xl space-y-6 p-4 sm:p-6">
    <header><p class="text-sm font-semibold text-violet-600">Consultation du réseau</p><h1 class="mt-1 text-2xl font-bold text-slate-900">Infrastructures</h1><p class="mt-1 text-sm text-slate-500">Vue en lecture seule des équipements et de leur état opérationnel.</p></header>
    <form method="GET" action="{{ route('admin.infrastructures.index') }}" class="grid gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:grid-cols-4">
        <input name="q" value="{{ $filters['q'] }}" placeholder="Rechercher…" class="rounded-xl border border-slate-200 px-3 py-2 text-sm text-slate-800">
        <select name="zone_id" class="rounded-xl border border-slate-200 px-3 py-2 text-sm text-slate-800"><option value="">Toutes les zones</option>@foreach($zones as $zone)<option value="{{ $zone->id }}" @selected($filters['zone_id'] == $zone->id)>{{ $zone->nom }}</option>@endforeach</select>
        <select name="type" class="rounded-xl border border-slate-200 px-3 py-2 text-sm text-slate-800"><option value="">Tous les types</option>@foreach($types as $type)<option value="{{ $type->value }}" @selected($filters['type'] === $type->value)>{{ $type->label() }}</option>@endforeach</select>
        <button class="rounded-xl bg-violet-600 px-4 py-2 text-sm font-semibold text-white">Filtrer</button>
    </form>
    <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-sm"><table class="w-full text-left text-sm">
        <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500"><tr><th class="px-5 py-3">Infrastructure</th><th class="px-5 py-3">Référence</th><th class="px-5 py-3">Zone</th><th class="px-5 py-3">Type</th><th class="px-5 py-3">État</th><th class="px-5 py-3"></th></tr></thead>
        <tbody class="divide-y divide-slate-100 text-slate-700">@forelse($infrastructures as $infrastructure)<tr class="hover:bg-slate-50"><td class="px-5 py-4 font-semibold text-slate-900">{{ $infrastructure->nom }}</td><td class="px-5 py-4">{{ $infrastructure->reference ?? '—' }}</td><td class="px-5 py-4">{{ $infrastructure->zone?->nom ?? '—' }}</td><td class="px-5 py-4">{{ $infrastructure->type?->label() ?? '—' }}</td><td class="px-5 py-4">{{ $infrastructure->etat?->label() ?? $infrastructure->etat ?? '—' }}</td><td class="px-5 py-4 text-right"><a class="font-semibold text-violet-700 hover:underline" href="{{ route('admin.infrastructures.show', $infrastructure) }}">Consulter</a></td></tr>@empty<tr><td colspan="6" class="px-5 py-12 text-center text-slate-500">Aucune infrastructure trouvée.</td></tr>@endforelse</tbody>
    </table></div>
    <div>{{ $infrastructures->links() }}</div>
</div>
@endsection
