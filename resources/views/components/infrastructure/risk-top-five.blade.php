@props(['infrastructures', 'routePrefix' => 'manager'])
<section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-slate-900/70">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="font-bold text-slate-900 dark:text-white">Top 5 des infrastructures à risque</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">Estimation indicative de panne dans les 30 jours</p>
        </div>
        <span class="cursor-help text-slate-500 dark:text-slate-400" title="Classement des dernières estimations enregistrées à partir du modèle logistique.">ⓘ</span>
    </div>
    @forelse($infrastructures as $infrastructure)
        <a href="{{ route($routePrefix.'.infrastructures.show', $infrastructure) }}" class="mt-3 flex items-center justify-between gap-3 rounded-xl border border-slate-100 p-3 hover:border-cyan-300 dark:border-white/5 dark:hover:border-cyan-500/30">
            <span class="min-w-0">
                <span class="block truncate text-sm font-semibold text-slate-900 dark:text-white">{{ $infrastructure->nom }}</span>
                <span class="block text-xs text-slate-500 dark:text-slate-400">{{ $infrastructure->zone?->nom }}</span>
            </span>
            <x-infrastructure.risk-badge :level="$infrastructure->latestRiskPrediction->level" :probability="$infrastructure->latestRiskPrediction->probability" />
        </a>
    @empty
        <p class="mt-4 text-sm text-slate-500 dark:text-slate-400">Aucune estimation disponible. Lancez le calcul quotidien pour alimenter ce classement.</p>
    @endforelse
</section>
