@extends('layouts.manager')

@section('title', 'Infrastructures — AquaSecure')

@section('manager-content')
<div class="mx-auto max-w-5xl space-y-3">
    <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-[#d5e3e7] bg-[#edf5f6] px-4 py-3 shadow-sm">
        <div>
            <p class="!text-xs font-medium text-cyan-700">Réseau · Gestion</p>
            <h1 class="!mt-1 !text-2xl !font-semibold !leading-tight tracking-tight text-slate-900">Infrastructures</h1>
            <p class="!mt-1 !text-sm text-slate-500">Équipements et rattachement à leur zone.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('manager.zones.index') }}" class="rounded-lg px-3 py-1.5 text-sm font-medium text-slate-600 hover:bg-white/70 hover:text-slate-900">Zones</a>
            <a href="{{ route('manager.infrastructures.create') }}" class="rounded-lg bg-cyan-700 px-3.5 py-2 text-sm font-medium text-white shadow-sm hover:bg-cyan-800">Nouvelle infrastructure</a>
        </div>
    </div>

    @include('manager.shared.flash')

    <div class="overflow-hidden rounded-2xl border border-[#d5e3e7] bg-[#f5f9fa] shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-[#e8f1f3] text-xs font-medium text-slate-600">
                    <tr><th class="px-3.5 py-2.5">Nom</th><th class="px-3.5 py-2.5">Type</th><th class="px-3.5 py-2.5">État</th><th class="px-3.5 py-2.5">Zone</th><th class="px-3.5 py-2.5">Installation</th><th class="px-3.5 py-2.5 text-right">Actions</th></tr>
                </thead>
                <tbody class="divide-y divide-[#e2ecee]">
                    @forelse($infrastructures as $infrastructure)
                    <tr class="text-slate-600 hover:bg-white/70">
                        <td class="px-3.5 py-2.5 font-medium text-slate-900">{{ $infrastructure->nom }}</td>
                        <td class="px-3.5 py-2.5">{{ $infrastructure->type }}</td>
                        <td class="px-3.5 py-2.5"><span class="rounded-full bg-[#e2eef0] px-2.5 py-1 text-xs font-medium text-cyan-900">{{ $infrastructure->etat }}</span></td>
                        <td class="px-3.5 py-2.5"><a class="text-cyan-700 hover:text-cyan-900" href="{{ route('manager.zones.show', $infrastructure->zone) }}">{{ $infrastructure->zone->nom }}</a></td>
                        <td class="px-3.5 py-2.5">{{ $infrastructure->date_installation->format('d/m/Y') }}</td>
                        <td class="px-3.5 py-2.5">
                            <div class="flex justify-end gap-3 whitespace-nowrap">
                                <a class="text-cyan-700 hover:text-cyan-900" href="{{ route('manager.infrastructures.show', $infrastructure) }}">Voir</a>
                                <a class="text-slate-500 hover:text-slate-900" href="{{ route('manager.infrastructures.edit', $infrastructure) }}">Modifier</a>
                                <form method="POST" action="{{ route('manager.infrastructures.destroy', $infrastructure) }}" onsubmit="return confirm('Supprimer cette infrastructure ?')">
                                    @csrf @method('DELETE')
                                    <button class="text-rose-600 hover:text-rose-800" type="submit">Supprimer</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-5 py-10 text-center text-slate-500">Aucune infrastructure enregistrée.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($infrastructures->hasPages())
            <div class="border-t border-[#e2ecee] px-5 py-3">{{ $infrastructures->links() }}</div>
        @endif
    </div>
</div>
@endsection
