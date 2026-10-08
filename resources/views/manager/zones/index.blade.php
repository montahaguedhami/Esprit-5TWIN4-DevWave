@extends('layouts.manager')

@section('title', 'Zones — AquaSecure')

@section('manager-content')
<div class="mx-auto max-w-5xl space-y-3">
    <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-cyan-400/15 bg-slate-900/40 px-4 py-3 shadow-sm">
        <div>
            <p class="!text-xs font-medium text-cyan-300">Réseau · Gestion</p>
            <h1 class="!mt-1 !text-2xl !font-semibold !leading-tight tracking-tight text-white">Zones</h1>
            <p class="!mt-1 !text-sm text-cyan-100/60">Zones géographiques et infrastructures associées.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('manager.infrastructures.index') }}" class="rounded-lg px-3 py-1.5 text-sm font-medium text-cyan-100/70 hover:bg-white/5 hover:text-white">Infrastructures</a>
            <a href="{{ route('manager.zones.create') }}" class="rounded-lg bg-cyan-600 px-3.5 py-2 text-sm font-medium text-white shadow-sm hover:bg-cyan-500">+ Nouvelle zone</a>
        </div>
    </div>

    @include('manager.shared.flash')

    <div class="overflow-hidden rounded-2xl border border-cyan-400/15 bg-slate-900/60 shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-white/5 text-xs font-medium text-cyan-100/70">
                    <tr><th class="px-4 py-2.5">Nom</th><th class="px-4 py-2.5">Localisation</th><th class="px-4 py-2.5">Infrastructures</th><th class="px-4 py-2.5 text-right">Actions</th></tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @forelse($zones as $zone)
                    <tr class="text-cyan-100/70 hover:bg-white/5">
                        <td class="px-4 py-2.5 font-medium text-white">{{ $zone->nom }}</td>
                        <td class="px-4 py-2.5">{{ $zone->localisation }}</td>
                        <td class="px-4 py-2.5"><span class="rounded-full bg-white/10 px-2.5 py-1 text-xs font-medium text-cyan-200">{{ $zone->infrastructures_count }}</span></td>
                        <td class="px-4 py-2.5">
                            <div class="flex justify-end gap-3">
                                <a class="text-cyan-300 hover:text-cyan-200" href="{{ route('manager.zones.show', $zone) }}">Voir</a>
                                <a class="text-cyan-100/60 hover:text-white" href="{{ route('manager.zones.edit', $zone) }}">Modifier</a>
                                <form method="POST" action="{{ route('manager.zones.destroy', $zone) }}" onsubmit="return confirm('Supprimer cette zone ?')">
                                    @csrf @method('DELETE')
                                    <button class="text-rose-400 hover:text-rose-300" type="submit">Supprimer</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="px-5 py-10 text-center text-cyan-100/60">Aucune zone enregistrée. Créez une zone pour commencer.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($zones->hasPages())
            <div class="border-t border-white/10 px-5 py-3">{{ $zones->links() }}</div>
        @endif
    </div>
</div>
@endsection
