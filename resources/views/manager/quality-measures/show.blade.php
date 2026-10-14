@extends('layouts.manager')
@section('title', $mesure->reference)
@section('manager-content')
<div class="text-slate-100">
    <div class="mx-auto max-w-4xl space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div><h1 class="text-2xl font-bold text-white">Mesure {{ $mesure->reference }}</h1><p class="text-sm text-slate-400">{{ $mesure->pointMesure?->nom }} · {{ $mesure->date_mesure->format('d/m/Y H:i') }}</p></div>
            <div class="flex gap-2"><a href="{{ route('manager.quality.mesures.edit', $mesure) }}" class="rounded-xl bg-slate-800 px-4 py-2 text-sm">Modifier</a><a href="{{ route('manager.quality') }}" class="rounded-xl bg-teal-500 px-4 py-2 text-sm font-semibold text-slate-950">Retour à la qualité</a></div>
        </div>
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach(['pH' => [$mesure->ph, ''], 'Turbidité' => [$mesure->turbidite, 'NTU'], 'Chlore résiduel' => [$mesure->chlore_residuel, 'mg/L'], 'Plomb' => [$mesure->plomb, 'ppb'], 'Nitrates' => [$mesure->nitrates, 'mg/L'], 'Conformité' => [$mesure->overall_compliance.'%', $mesure->status]] as $label => [$value, $unit])
                <div class="rounded-2xl border border-slate-800 bg-slate-900 p-5"><div class="text-xs text-slate-400">{{ $label }}</div><div class="mt-2 text-xl font-bold text-white">{{ $value }} <span class="text-xs font-normal text-slate-400">{{ $unit }}</span></div></div>
            @endforeach
            <div class="rounded-2xl border border-slate-800 bg-slate-900 p-5"><div class="text-xs text-slate-400">Vérification</div><div class="mt-2 font-semibold">{{ $mesure->is_verified ? 'Vérifiée' : 'À vérifier' }}</div><div class="text-sm text-slate-400">{{ $mesure->verifier ?: '—' }}</div></div>
        </div>
        <form method="POST" action="{{ route('manager.quality.mesures.destroy', $mesure) }}" onsubmit="return confirm('Supprimer définitivement cette mesure ?')">
            @csrf @method('DELETE')
            <button class="rounded-xl border border-red-500/30 px-4 py-2 text-sm text-red-300">Supprimer la mesure</button>
        </form>
    </div>
</div>
@endsection
