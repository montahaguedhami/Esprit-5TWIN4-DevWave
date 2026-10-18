@extends('layouts.admin')

@section('title', 'Consultation de la zone')
@section('page-title', 'Zones')

@section('admin-content')
<div class="mx-auto max-w-7xl space-y-6 p-4 sm:p-6">
    <a href="{{ route('admin.zones.index') }}" class="text-sm font-semibold text-violet-700 hover:underline">← Retour aux zones</a>
    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <p class="text-sm font-semibold text-violet-600">Fiche consultative</p>
        <h1 class="mt-1 text-2xl font-bold text-slate-900">{{ $zone->nom }}</h1>
        <p class="mt-2 text-slate-600">{{ $zone->localisation ?: 'Localisation non renseignée' }}</p>
        <dl class="mt-6 grid gap-4 sm:grid-cols-3">
            <div class="rounded-xl bg-slate-50 p-4"><dt class="text-xs text-slate-500">Statut</dt><dd class="mt-1 font-semibold text-slate-900">{{ $zone->statut?->label() ?? $zone->statut ?? '—' }}</dd></div>
            <div class="rounded-xl bg-slate-50 p-4"><dt class="text-xs text-slate-500">Responsable</dt><dd class="mt-1 font-semibold text-slate-900">{{ $zone->responsable?->name ?? '—' }}</dd></div>
            <div class="rounded-xl bg-slate-50 p-4"><dt class="text-xs text-slate-500">Infrastructures</dt><dd class="mt-1 font-semibold text-slate-900">{{ $zone->infrastructures->count() }}</dd></div>
        </dl>
    </section>
    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-5 py-4"><h2 class="font-bold text-slate-900">Infrastructures de la zone</h2></div>
        <div class="divide-y divide-slate-100">
            @forelse($zone->infrastructures as $infrastructure)
                <a href="{{ route('admin.infrastructures.show', $infrastructure) }}" class="flex flex-wrap items-center justify-between gap-3 px-5 py-4 hover:bg-slate-50"><span><span class="block font-semibold text-slate-900">{{ $infrastructure->nom }}</span><span class="text-sm text-slate-500">{{ $infrastructure->reference ?? 'Sans référence' }} · {{ $infrastructure->type?->label() ?? 'Type non précisé' }}</span></span><span class="text-sm font-semibold text-violet-700">Consulter →</span></a>
            @empty
                <p class="px-5 py-8 text-center text-sm text-slate-500">Aucune infrastructure rattachée à cette zone.</p>
            @endforelse
        </div>
    </section>
    <div class="grid gap-6 lg:grid-cols-2">
        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-5 py-4"><h2 class="font-bold text-slate-900">Incidents récents</h2></div>
            <div class="divide-y divide-slate-100">@forelse($zone->incidents as $incident)<div class="px-5 py-3"><p class="font-semibold text-slate-800">{{ $incident->titre }}</p><p class="mt-1 text-xs text-slate-500">{{ $incident->date_signalement?->format('d/m/Y H:i') ?? 'Date inconnue' }} · {{ $incident->statut }}</p></div>@empty<p class="px-5 py-6 text-sm text-slate-500">Aucun incident ouvert.</p>@endforelse</div>
        </section>
        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-5 py-4"><h2 class="font-bold text-slate-900">Points de mesure et dernières mesures</h2></div>
            <div class="divide-y divide-slate-100">@forelse($zone->pointsMesure as $point)<div class="px-5 py-3"><p class="font-semibold text-slate-800">{{ $point->nom }}</p>@if($point->derniereMesure)<p class="mt-1 text-xs text-slate-500">{{ \Carbon\Carbon::parse($point->derniereMesure->date_mesure)->format('d/m/Y H:i') }} · pH {{ $point->derniereMesure->ph ?? '—' }} · Turbidité {{ $point->derniereMesure->turbidite ?? '—' }} NTU · Chlore {{ $point->derniereMesure->chlore_residuel ?? '—' }} mg/l</p>@else<p class="mt-1 text-xs text-slate-500">Aucune mesure enregistrée</p>@endif</div>@empty<p class="px-5 py-6 text-sm text-slate-500">Aucun point de mesure dans cette zone.</p>@endforelse</div>
        </section>
    </div>
    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-5 py-4"><h2 class="font-bold text-slate-900">Projets et budgets consultables</h2></div>
        <div class="divide-y divide-slate-100">@forelse($zone->projets as $projet)<div class="flex flex-wrap justify-between gap-2 px-5 py-3"><span class="font-semibold text-slate-800">{{ $projet->nom }}</span><span class="text-sm text-slate-600">{{ isset($projet->budget) ? number_format((float) $projet->budget, 2, ',', ' ').' DT' : 'Budget non renseigné' }}</span></div>@empty<p class="px-5 py-6 text-sm text-slate-500">Aucun projet rattaché à cette zone.</p>@endforelse</div>
    </section>
</div>
@endsection
