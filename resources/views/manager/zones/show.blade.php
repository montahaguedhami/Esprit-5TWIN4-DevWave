@extends('layouts.manager')

@section('title', $zone->nom.' — AquaSecure')

@section('manager-content')
<div class="mx-auto max-w-6xl space-y-5">
    <div class="flex flex-wrap items-start justify-between gap-4 rounded-2xl border border-[#d5e3e7] bg-[#edf5f6] px-5 py-4 shadow-sm">
        <div>
            <a href="{{ route('manager.zones.index') }}" class="text-sm text-cyan-700 hover:text-cyan-900">← Toutes les zones</a>
            <h1 class="mt-2 text-2xl font-semibold text-slate-900">{{ $zone->nom }}</h1>
            <p class="mt-1 text-sm text-slate-500">{{ $zone->localisation }}</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('manager.zones.edit', $zone) }}" class="rounded-lg border border-slate-300 px-3.5 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">Modifier</a>
            <a href="{{ route('manager.infrastructures.create', ['zone_id' => $zone->id]) }}" class="rounded-lg bg-cyan-700 px-3.5 py-2 text-sm font-medium text-white hover:bg-cyan-800">Ajouter infrastructure</a>
        </div>
    </div>
    @include('manager.shared.flash')
    <section class="rounded-2xl border border-[#d5e3e7] bg-[#f5f9fa] px-5 py-4 shadow-sm">
        <h2 class="text-sm font-semibold text-slate-800">Description</h2>
        <p class="mt-1 whitespace-pre-line text-sm text-slate-600">{{ $zone->description ?: 'Aucune description.' }}</p>
    </section>
    <section class="space-y-3">
        <h2 class="text-lg font-semibold text-white">Infrastructures <span class="text-sm font-normal text-slate-400">({{ $zone->infrastructures->count() }})</span></h2>
        <div class="overflow-x-auto rounded-2xl border border-[#d5e3e7] bg-[#f5f9fa] shadow-sm">
            <table class="w-full text-left text-sm">
                <thead class="bg-[#e8f1f3] text-xs font-medium text-slate-600"><tr><th class="px-5 py-3">Nom</th><th class="px-5 py-3">Type</th><th class="px-5 py-3">État</th><th class="px-5 py-3 text-right">Actions</th></tr></thead>
                <tbody class="divide-y divide-[#e2ecee]">
                    @forelse($zone->infrastructures as $infrastructure)
                    <tr class="text-slate-600 hover:bg-slate-50/80">
                        <td class="px-5 py-3 font-medium text-slate-900">{{ $infrastructure->nom }}</td>
                        <td class="px-5 py-3">{{ $infrastructure->type }}</td>
                        <td class="px-5 py-3">{{ $infrastructure->etat }}</td>
                        <td class="px-5 py-3 text-right"><a href="{{ route('manager.infrastructures.show', $infrastructure) }}" class="text-cyan-700 hover:text-cyan-900">Voir</a></td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="px-5 py-8 text-center text-slate-500">Aucune infrastructure dans cette zone.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
