@props(['risk'])
@php
    $levelColor = match ($risk['level']) {
        'Critique' => 'border-rose-500/30 bg-rose-500/10 text-rose-100',
        'Élevé' => 'border-orange-500/30 bg-orange-500/10 text-orange-100',
        'Moyen' => 'border-amber-500/30 bg-amber-500/10 text-amber-100',
        default => 'border-emerald-500/30 bg-emerald-500/10 text-emerald-100',
    };
@endphp
<section class="glass rounded-2xl border p-5 {{ $levelColor }}">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h2 class="text-sm font-bold">Risque de panne · Estimation indicative</h2>
            <p class="mt-1 text-[11px] opacity-75" title="Modèle logistique estimant le risque sur 30 jours selon l’état, l’âge, la maintenance et l’historique.">Horizon 30 jours · méthode indicative ⓘ</p>
        </div>
        <x-infrastructure.risk-badge :level="$risk['level']" :probability="$risk['probability']" />
    </div>
    <ul class="mt-4 space-y-1.5 text-xs">
        @foreach(array_slice($risk['factors'], 0, 3) as $factor)
            <li class="flex gap-2"><span aria-hidden="true">•</span><span>{{ $factor['explanation'] }}</span></li>
        @endforeach
    </ul>
    <p class="mt-4 border-t border-current/10 pt-3 text-xs"><strong>Recommandation :</strong> {{ $risk['recommendation'] }}</p>
</section>
