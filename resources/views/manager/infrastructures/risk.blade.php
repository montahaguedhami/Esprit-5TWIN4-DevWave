@extends(request()->routeIs('admin.*') ? 'layouts.admin' : 'layouts.manager')

@section('title', 'Risque de panne — ' . $infrastructure->nom . ' — AquaSecure')

@section(request()->routeIs('admin.*') ? 'admin-content' : 'manager-content')
@php
    $inventoryRoutePrefix = request()->routeIs('admin.*') ? 'admin' : 'manager';
@endphp
<div class="mx-auto max-w-5xl space-y-6">
    {{-- En-tête de page --}}
    <div class="flex flex-wrap items-start justify-between gap-4 rounded-2xl border border-cyan-400/15 bg-slate-900/40 p-5 shadow-sm">
        <div class="space-y-1">
            <a href="{{ route($inventoryRoutePrefix . '.infrastructures.show', $infrastructure) }}" class="inline-flex items-center text-sm font-medium text-cyan-300 hover:text-cyan-200 transition">
                ← Retour à la fiche de {{ $infrastructure->nom }}
            </a>
            <h1 class="text-2xl font-bold text-white">Risque de panne</h1>
            <p class="text-sm text-cyan-100/60">
                Estimation logistique indicative sur 30 jours pour <strong>{{ $infrastructure->nom }}</strong> · Zone :
                <a class="text-cyan-300 hover:underline" href="{{ route($inventoryRoutePrefix . '.zones.show', $infrastructure->zone) }}">
                    {{ $infrastructure->zone->nom }}
                </a>
            </p>
        </div>
        <div class="flex items-center gap-2">
            <form method="POST" action="{{ route($inventoryRoutePrefix . '.infrastructures.risk.recompute', $infrastructure) }}">
                @csrf
                <button class="rounded-lg border border-cyan-400/30 bg-cyan-500/10 px-3.5 py-2 text-sm font-medium text-cyan-200 hover:bg-cyan-500/20 transition">
                    <svg class="w-4 h-4 text-cyan-400 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Recalculer
                </button>
            </form>
        </div>
    </div>

    @include('manager.shared.flash')

    @include('components.infrastructure.risk-card', [
        'risk' => $risk,
        'history' => $history,
        'infrastructure' => $infrastructure,
        'routePrefix' => $inventoryRoutePrefix,
        'showDetails' => true,
    ])
</div>
@endsection
