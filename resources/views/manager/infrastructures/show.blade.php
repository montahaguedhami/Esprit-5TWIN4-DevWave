@extends('layouts.manager')

@section('title', $infrastructure->nom.' — AquaSecure')

@section('manager-content')
<div class="mx-auto max-w-4xl space-y-5">
    <div class="flex flex-wrap items-start justify-between gap-4 rounded-2xl border border-cyan-400/15 bg-slate-900/40 px-5 py-4 shadow-sm">
        <div>
            <a href="{{ route('manager.infrastructures.index') }}" class="text-sm text-cyan-300 hover:text-cyan-200">← Toutes les infrastructures</a>
            <h1 class="mt-2 text-2xl font-semibold text-white">{{ $infrastructure->nom }}</h1>
            <p class="mt-1 text-sm text-cyan-100/60">{{ $infrastructure->type }}</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('manager.infrastructures.edit', $infrastructure) }}" class="rounded-lg border border-cyan-400/20 px-3.5 py-2 text-sm font-medium text-cyan-100/70 hover:bg-white/5">Modifier</a>
            <form method="POST" action="{{ route('manager.infrastructures.destroy', $infrastructure) }}" onsubmit="return confirm('Supprimer cette infrastructure ?')">
                @csrf @method('DELETE')
                <button type="submit" class="rounded-lg border border-rose-400/30 px-3.5 py-2 text-sm font-medium text-rose-300 hover:bg-rose-500/10">Supprimer</button>
            </form>
        </div>
    </div>
    @include('manager.shared.flash')
    <dl class="grid gap-x-8 gap-y-5 rounded-2xl border border-cyan-400/15 bg-slate-900/60 p-5 shadow-sm sm:grid-cols-2">
        <div><dt class="text-xs font-medium text-cyan-100/60">État</dt><dd class="mt-1 font-medium text-white">{{ $infrastructure->etat }}</dd></div>
        <div><dt class="text-xs font-medium text-cyan-100/60">Zone</dt><dd class="mt-1 font-medium"><a class="text-cyan-300 hover:text-cyan-200" href="{{ route('manager.zones.show', $infrastructure->zone) }}">{{ $infrastructure->zone->nom }}</a></dd></div>
        <div><dt class="text-xs font-medium text-cyan-100/60">Localisation</dt><dd class="mt-1 font-medium text-white">{{ $infrastructure->localisation }}</dd></div>
        <div><dt class="text-xs font-medium text-cyan-100/60">Date d’installation</dt><dd class="mt-1 font-medium text-white">{{ $infrastructure->date_installation->format('d/m/Y') }}</dd></div>
    </dl>
</div>
@endsection
