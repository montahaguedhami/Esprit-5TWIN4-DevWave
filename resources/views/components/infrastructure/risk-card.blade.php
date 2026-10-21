@props(['risk', 'history' => [], 'infrastructure', 'routePrefix' => 'manager', 'showDetails' => false])
@php($levelColor = match ($risk['level'] ?? 'Faible') { 'Critique' => 'border-rose-500/30 bg-rose-500/10 text-rose-100', 'Élevé' => 'border-orange-500/30 bg-orange-500/10 text-orange-100', 'Moyen' => 'border-amber-500/30 bg-amber-500/10 text-amber-100', default => 'border-emerald-500/30 bg-emerald-500/10 text-emerald-100' })
<section class="rounded-2xl border p-5 {{ $levelColor }}">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h2 class="text-sm font-bold">Risque de panne · Estimation indicative</h2>
            <p class="mt-1 text-xs opacity-75">Probabilité estimée sur 30 jours pour {{ $infrastructure->nom }}.</p>
        </div>
        <x-infrastructure.risk-badge :level="$risk['level'] ?? 'Faible'" :probability="$risk['probability'] ?? 0" />
    </div>
    @if($showDetails)
        <ul class="mt-4 space-y-2 text-sm">
            @foreach(array_slice($risk['factors'] ?? [], 0, 4) as $factor)
                <li>{{ $factor['explanation'] ?? $factor['name'] ?? '' }}</li>
            @endforeach
        </ul>
        <p class="mt-4 border-t border-current/10 pt-3 text-sm"><strong>Recommandation :</strong> {{ $risk['recommendation'] ?? 'Poursuivre le suivi régulier.' }}</p>
        @if(count($history))
            <h3 class="mt-5 text-sm font-semibold">Historique des estimations</h3>
            <div class="mt-2 flex flex-wrap gap-2">@foreach($history as $item)<span class="rounded-lg bg-black/10 px-3 py-2 text-xs">{{ $item->computed_at?->format('d/m/Y') }} · {{ number_format($item->probability * 100, 0) }}%</span>@endforeach</div>
        @endif
    @endif
</section>
