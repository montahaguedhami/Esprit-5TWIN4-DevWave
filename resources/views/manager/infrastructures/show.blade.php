@extends('layouts.manager')

@section('title', $infrastructure->nom.' — AquaSecure')

@section('manager-content')
<div class="mx-auto max-w-4xl space-y-5">
    <div class="flex flex-wrap items-start justify-between gap-4 rounded-2xl border border-[#d5e3e7] bg-[#edf5f6] px-5 py-4 shadow-sm">
        <div>
            <a href="{{ route('manager.infrastructures.index') }}" class="text-sm text-cyan-700 hover:text-cyan-900">← Toutes les infrastructures</a>
            <h1 class="mt-2 text-2xl font-semibold text-slate-900">{{ $infrastructure->nom }}</h1>
            <p class="mt-1 text-sm text-slate-500">{{ $infrastructure->type }}</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('manager.infrastructures.edit', $infrastructure) }}" class="rounded-lg border border-slate-300 px-3.5 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">Modifier</a>
            <form method="POST" action="{{ route('manager.infrastructures.destroy', $infrastructure) }}" onsubmit="return confirm('Supprimer cette infrastructure ?')">
                @csrf @method('DELETE')
                <button type="submit" class="rounded-lg border border-rose-200 px-3.5 py-2 text-sm font-medium text-rose-700 hover:bg-rose-50">Supprimer</button>
            </form>
        </div>
    </div>
    @include('manager.shared.flash')
    <dl class="grid gap-x-8 gap-y-5 rounded-2xl border border-[#d5e3e7] bg-[#f5f9fa] p-5 shadow-sm sm:grid-cols-2">
        <div><dt class="text-xs font-medium text-slate-500">État</dt><dd class="mt-1 font-medium text-slate-900">{{ $infrastructure->etat }}</dd></div>
        <div><dt class="text-xs font-medium text-slate-500">Zone</dt><dd class="mt-1 font-medium"><a class="text-cyan-700 hover:text-cyan-900" href="{{ route('manager.zones.show', $infrastructure->zone) }}">{{ $infrastructure->zone->nom }}</a></dd></div>
        <div><dt class="text-xs font-medium text-slate-500">Localisation</dt><dd class="mt-1 font-medium text-slate-900">{{ $infrastructure->localisation }}</dd></div>
        <div><dt class="text-xs font-medium text-slate-500">Date d’installation</dt><dd class="mt-1 font-medium text-slate-900">{{ $infrastructure->date_installation->format('d/m/Y') }}</dd></div>
    </dl>
</div>
@endsection
