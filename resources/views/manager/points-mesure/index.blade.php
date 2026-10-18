@extends('layouts.manager')

@section('title', 'Points de prélèvement — AquaSecure')

@section('manager-content')
<div class="text-slate-100">
    <div class="max-w-6xl mx-auto space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-white">Points de prélèvement</h1>
                <p class="text-sm text-slate-400 mt-1">Gérez les stations et points de mesure de qualité de l’eau.</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('manager.quality') }}" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-200 text-sm">Qualité de l’eau</a>
                <a href="{{ route('manager.points-mesure.create') }}" class="px-4 py-2 rounded-xl bg-teal-500 text-slate-950 font-semibold text-sm">Nouveau point</a>
            </div>
        </div>

        @if(session('success'))
            <div class="rounded-xl border border-teal-500/30 bg-teal-500/10 p-3 text-sm text-teal-200">{{ session('success') }}</div>
        @endif

        <div class="overflow-x-auto rounded-2xl border border-slate-800 bg-slate-900">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-950 text-xs uppercase text-slate-400">
                    <tr><th class="p-4">Code</th><th class="p-4">Point / zone</th><th class="p-4">Type</th><th class="p-4">Statut</th><th class="p-4">Dernière mesure</th><th class="p-4">Actions</th></tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @forelse($points as $point)
                        <tr>
                            <td class="p-4 font-mono text-cyan-300">{{ $point->code }}</td>
                            <td class="p-4"><a class="font-semibold text-white hover:underline" href="{{ route('manager.points-mesure.show', $point) }}">{{ $point->nom }}</a><div class="text-xs text-slate-400">{{ $point->zone ?: '—' }}</div></td>
                            <td class="p-4">{{ $point->type }}</td>
                            <td class="p-4">{{ ucfirst($point->statut) }}</td>
                            <td class="p-4 text-slate-300">{{ $point->derniereMesure?->date_mesure?->format('d/m/Y H:i') ?? 'Aucune' }}</td>
                            <td class="p-4">
                                <div class="flex flex-wrap gap-2">
                                    <a class="text-cyan-300 hover:underline" href="{{ route('manager.points-mesure.edit', $point) }}">Modifier</a>
                                    <form method="POST" action="{{ route('manager.points-mesure.destroy', $point) }}" onsubmit="return confirm('Supprimer ou désactiver ce point ?')">
                                        @csrf @method('DELETE')
                                        <button class="text-red-300 hover:underline">Supprimer</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="p-8 text-center text-slate-400">Aucun point enregistré.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $points->links() }}
    </div>
</div>
@endsection
