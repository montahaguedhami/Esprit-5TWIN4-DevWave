@extends('layouts.manager')
@section('title', $point->nom)
@section('content')
<div class="min-h-screen bg-[#061525] p-4 text-slate-100 sm:p-8">
    <div class="mx-auto max-w-5xl space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div><h1 class="text-2xl font-bold text-white">{{ $point->nom }}</h1><p class="text-sm text-slate-400">{{ $point->code }} · {{ $point->zone }}</p></div>
            <div class="flex gap-2"><a href="{{ route('manager.points-mesure.edit', $point) }}" class="rounded-xl bg-slate-800 px-4 py-2 text-sm">Modifier</a><a href="{{ route('manager.quality.mesures.create', ['point_mesure_id' => $point->id]) }}" class="rounded-xl bg-teal-500 px-4 py-2 text-sm font-bold text-slate-950">Ajouter une mesure</a></div>
        </div>
        @if(session('success'))<div class="rounded-xl border border-teal-500/30 bg-teal-500/10 p-3 text-sm text-teal-200">{{ session('success') }}</div>@endif
        <div class="grid gap-4 rounded-2xl border border-slate-800 bg-slate-900 p-5 sm:grid-cols-3">
            <div><div class="text-xs text-slate-400">Type / statut</div><div>{{ $point->type }} · {{ ucfirst($point->statut) }}</div></div>
            <div><div class="text-xs text-slate-400">Adresse</div><div>{{ $point->adresse ?: '—' }}</div></div>
            <div><div class="text-xs text-slate-400">Coordonnées</div><div>{{ $point->latitude ?? '—' }}, {{ $point->longitude ?? '—' }}</div></div>
            <div class="sm:col-span-3"><div class="text-xs text-slate-400">Description</div><div>{{ $point->description ?: '—' }}</div></div>
        </div>
        <div class="overflow-x-auto rounded-2xl border border-slate-800 bg-slate-900">
            <table class="w-full text-left text-sm"><thead class="bg-slate-950 text-xs uppercase text-slate-400"><tr><th class="p-4">Référence</th><th class="p-4">Date</th><th class="p-4">pH</th><th class="p-4">Turbidité</th><th class="p-4">Chlore</th><th class="p-4">Plomb</th><th class="p-4">Nitrates</th></tr></thead>
                <tbody class="divide-y divide-slate-800">
                @forelse($point->mesuresQualites as $mesure)
                    <tr><td class="p-4"><a class="text-cyan-300 hover:underline" href="{{ route('manager.quality.mesures.show', $mesure) }}">{{ $mesure->reference }}</a></td><td class="p-4">{{ $mesure->date_mesure->format('d/m/Y H:i') }}</td><td class="p-4">{{ $mesure->ph }}</td><td class="p-4">{{ $mesure->turbidite }} NTU</td><td class="p-4">{{ $mesure->chlore_residuel }} mg/L</td><td class="p-4">{{ $mesure->plomb }} ppb</td><td class="p-4">{{ $mesure->nitrates }} mg/L</td></tr>
                @empty
                    <tr><td colspan="7" class="p-8 text-center text-slate-400">Aucune mesure pour ce point.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
