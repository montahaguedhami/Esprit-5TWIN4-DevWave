@props(['risk', 'history', 'infrastructure', 'routePrefix' => 'manager', 'showDetails' => true])
@php
    $riskColor = match ($risk['level']) {
        'Critique' => '#e11d48',
        'Élevé' => '#ea580c',
        'Moyen' => '#d97706',
        default => '#059669',
    };
    $maxImpact = max(1, ...array_map(static fn ($factor) => abs($factor['contribution']), $risk['factors']));
    $points = $history->values()->map(function ($item, $index) use ($history) {
        $x = $history->count() > 1 ? 5 + ($index / ($history->count() - 1)) * 90 : 50;
        $y = 95 - ($item->probability / 100) * 85;
        return round($x, 1).','.round($y, 1);
    })->implode(' ');
@endphp
<section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-slate-900/70">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <h2 class="text-base font-bold text-slate-900 dark:text-white">Risque de panne</h2>
                <span class="cursor-help text-slate-500 dark:text-slate-400" title="Estimation logistique indicative sur 30 jours à partir de l’état, de l’âge, de la maintenance et de l’historique technique." aria-label="Estimation indicative : modèle logistique sur 30 jours.">ⓘ</span>
            </div>
            <p class="mt-1 text-xs font-semibold uppercase tracking-wide text-cyan-700 dark:text-cyan-300">Estimation indicative · horizon 30 jours</p>
        </div>
        @if($showDetails)
        <form method="POST" action="{{ route($routePrefix.'.infrastructures.risk.recompute', $infrastructure) }}">
            @csrf
            <button class="rounded-lg border border-cyan-300 px-3 py-2 text-xs font-semibold text-cyan-800 hover:bg-cyan-50 dark:border-cyan-400/30 dark:text-cyan-200 dark:hover:bg-cyan-500/10" type="submit">Recalculer</button>
        </form>
        @endif
    </div>

    <div class="mt-5 grid gap-5 lg:grid-cols-3">
        <div class="flex flex-col items-center justify-center gap-3 rounded-xl bg-slate-50 p-4 dark:bg-slate-800/60">
            <div class="relative grid h-36 w-36 place-items-center rounded-full" style="background: conic-gradient({{ $riskColor }} {{ $risk['probability'] }}%, #e2e8f0 0);">
                <div class="grid h-28 w-28 place-items-center rounded-full bg-white text-center dark:bg-slate-900">
                    <div>
                        <p class="text-3xl font-bold text-slate-900 dark:text-white">{{ $risk['probability'] }}%</p>
                        <p class="text-[10px] text-slate-500 dark:text-slate-400">probabilité</p>
                    </div>
                </div>
            </div>
            <x-infrastructure.risk-badge :level="$risk['level']" />
        </div>

        <div class="space-y-4 lg:col-span-2">
            <div class="rounded-xl bg-cyan-50 p-4 text-sm text-slate-700 dark:bg-cyan-950/30 dark:text-cyan-100">
                {{ $risk['explanation'] }}
            </div>
            <div>
                <h3 class="mb-2 text-xs font-bold uppercase tracking-wide text-slate-600 dark:text-slate-300">Facteurs déterminants</h3>
                <ul class="space-y-3">
                    @foreach($risk['factors'] as $factor)
                        <li>
                            <div class="flex justify-between gap-3 text-xs">
                                <span class="font-medium text-slate-800 dark:text-slate-100">{{ $factor['explanation'] }}</span>
                                <span class="shrink-0 text-slate-500 dark:text-slate-400">poids {{ number_format($factor['weight'], 2, ',', ' ') }} · {{ $factor['contribution'] > 0 ? '+' : '' }}{{ number_format($factor['contribution'], 1, ',', ' ') }} pts</span>
                            </div>
                            <div class="mt-1 h-1.5 overflow-hidden rounded-full bg-slate-200 dark:bg-slate-700">
                                <div class="h-full rounded-full bg-cyan-600 dark:bg-cyan-400" style="width: {{ min(100, abs($factor['contribution']) / $maxImpact * 100) }}%"></div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
            <p class="rounded-xl border border-amber-200 bg-amber-50 p-3 text-sm text-amber-900 dark:border-amber-500/20 dark:bg-amber-500/10 dark:text-amber-100">
                <strong>Recommandation :</strong> {{ $risk['recommendation'] }}
            </p>
        </div>
    </div>

    @if($showDetails)
    <div class="mt-5 border-t border-slate-200 pt-4 dark:border-white/10">
        <div class="flex items-center justify-between">
            <h3 class="text-xs font-bold uppercase tracking-wide text-slate-600 dark:text-slate-300">Tendance historique</h3>
            <span class="text-[11px] text-slate-500 dark:text-slate-400">{{ $history->count() }} calcul(s)</span>
        </div>
        @if($history->isNotEmpty())
            <svg class="mt-2 h-20 w-full" viewBox="0 0 100 100" preserveAspectRatio="none" role="img" aria-label="Évolution historique du risque en pourcentage">
                <polyline points="{{ $points }}" fill="none" stroke="{{ $riskColor }}" stroke-width="2.5" vector-effect="non-scaling-stroke" />
            </svg>
            <div class="flex justify-between text-[10px] text-slate-500 dark:text-slate-400">
                <span>{{ $history->first()->computed_at->format('d/m/Y') }} · {{ $history->first()->probability }}%</span>
                <span>{{ $history->last()->computed_at->format('d/m/Y') }} · {{ $history->last()->probability }}%</span>
            </div>
        @else
            <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">L’historique s’affichera après les prochains calculs planifiés.</p>
        @endif
    </div>
    @endif
</section>
