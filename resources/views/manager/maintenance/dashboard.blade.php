@extends('layouts.manager')

@section('title', 'Statistiques maintenance — AquaSecure')

@section('manager-content')
@php
    $formatDt = fn ($v) => $v === null ? '—' : number_format($v, 2, ',', ' ').' DT';
    $couleursStatut = ['Planifiée' => '#0ea5e9', 'En cours' => '#f59e0b', 'Terminée' => '#10b981', 'Annulée' => '#64748b'];
@endphp
<div class="max-w-6xl mx-auto">
    <x-maintenance.page-header title="Statistiques maintenance" subtitle="Indicateurs des interventions sur les 12 derniers mois" icon="bar-chart-3">
        <x-maintenance.link-button :href="route('manager.interventions.index')" variant="secondary" icon="arrow-left">Interventions</x-maintenance.link-button>
        <x-maintenance.link-button :href="route('manager.planning.index')" variant="secondary" icon="calendar-days">Planning</x-maintenance.link-button>
        <x-maintenance.link-button :href="route('manager.techniciens.index')" variant="secondary" icon="hard-hat">Techniciens</x-maintenance.link-button>
    </x-maintenance.page-header>

    @include('manager.maintenance._flash')

    {{-- Indicateurs clés --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <x-ui.card padding="sm">
            <p class="text-xs font-semibold text-cyan-100/50 mb-1">Taux de réalisation</p>
            <p class="text-3xl font-display font-bold text-emerald-400">{{ $indicateurs['taux_realisation'] === null ? '—' : $indicateurs['taux_realisation'].' %' }}</p>
            <p class="text-xs text-cyan-100/50 mt-1">interventions échues terminées</p>
        </x-ui.card>
        <x-ui.card padding="sm">
            <p class="text-xs font-semibold text-cyan-100/50 mb-1">En retard</p>
            <p class="text-3xl font-display font-bold {{ $indicateurs['en_retard'] > 0 ? 'text-rose-400' : 'text-white' }}">{{ $indicateurs['en_retard'] }}</p>
            <p class="text-xs text-cyan-100/50 mt-1">{{ $indicateurs['a_venir'] }} à venir · {{ $indicateurs['ce_mois'] }} ce mois</p>
        </x-ui.card>
        <x-ui.card padding="sm">
            <p class="text-xs font-semibold text-cyan-100/50 mb-1">Coût total (12 mois)</p>
            <p class="text-2xl font-display font-bold text-white">{{ $formatDt($indicateurs['cout_total']) }}</p>
            <p class="text-xs text-cyan-100/50 mt-1">moyenne {{ $formatDt($indicateurs['cout_moyen']) }} / intervention</p>
        </x-ui.card>
        <x-ui.card padding="sm">
            <p class="text-xs font-semibold text-cyan-100/50 mb-1">Part préventive</p>
            <p class="text-3xl font-display font-bold text-violet-300">{{ $indicateurs['part_preventive'] === null ? '—' : $indicateurs['part_preventive'].' %' }}</p>
            <p class="text-xs text-cyan-100/50 mt-1">des interventions planifiées</p>
        </x-ui.card>
    </div>

    {{-- Graphiques --}}
    <div class="grid lg:grid-cols-3 gap-4 mb-6">
        <x-ui.card padding="sm" class="lg:col-span-2">
            <h2 class="font-display font-semibold text-white mb-3 flex items-center gap-2"><i data-lucide="bar-chart-3" class="w-5 h-5 text-cyan-300"></i>Coûts mensuels</h2>
            <div class="h-64"><canvas id="chart-couts" aria-label="Coûts mensuels préventif et correctif"></canvas></div>
        </x-ui.card>
        <x-ui.card padding="sm">
            <h2 class="font-display font-semibold text-white mb-3 flex items-center gap-2"><i data-lucide="pie-chart" class="w-5 h-5 text-cyan-300"></i>Interventions par statut</h2>
            <div class="h-64"><canvas id="chart-statuts" aria-label="Répartition des interventions par statut"></canvas></div>
        </x-ui.card>
    </div>

    {{-- Charge par technicien --}}
    <x-ui.card padding="none" class="overflow-hidden mb-6">
        <div class="px-5 py-4 border-b border-white/10">
            <h2 class="font-display font-semibold text-white flex items-center gap-2"><i data-lucide="hard-hat" class="w-5 h-5 text-cyan-300"></i>Charge des techniciens</h2>
        </div>
        @if($charge->isEmpty())
            <x-ui.empty-state icon="hard-hat" title="Aucun technicien" description="Ajoutez des techniciens pour suivre leur charge." />
        @else
            @php $chargeMax = max(1, $charge->max('a_venir')); @endphp
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-white/10 text-left">
                            <th class="px-5 py-3 text-xs font-semibold text-cyan-100/50">Technicien</th>
                            <th class="px-5 py-3 text-xs font-semibold text-cyan-100/50">Disponibilité</th>
                            <th class="px-5 py-3 text-xs font-semibold text-cyan-100/50">À venir</th>
                            <th class="px-5 py-3 text-xs font-semibold text-cyan-100/50 text-center">En retard</th>
                            <th class="px-5 py-3 text-xs font-semibold text-cyan-100/50 text-center">Terminées (30 j)</th>
                            <th class="px-5 py-3 text-xs font-semibold text-cyan-100/50 text-right">Coût (12 mois)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        @foreach($charge as $ligne)
                            <tr class="hover:bg-white/[.03]">
                                <td class="px-5 py-3">
                                    <a href="{{ route('manager.techniciens.show', $ligne['technicien']) }}" class="font-semibold text-white hover:text-cyan-300">{{ $ligne['technicien']->nom }}</a>
                                    <span class="block text-xs text-cyan-100/50">{{ $ligne['technicien']->specialite }}</span>
                                </td>
                                <td class="px-5 py-3"><x-maintenance.badge :value="$ligne['technicien']->disponibilite" /></td>
                                <td class="px-5 py-3 min-w-[140px]">
                                    <div class="flex items-center gap-2">
                                        <div class="flex-1 h-2 rounded-full bg-white/10 overflow-hidden">
                                            <div class="h-full rounded-full bg-cyan-400" style="width: {{ round($ligne['a_venir'] / $chargeMax * 100) }}%"></div>
                                        </div>
                                        <span class="text-white font-semibold w-6 text-right">{{ $ligne['a_venir'] }}</span>
                                    </div>
                                </td>
                                <td class="px-5 py-3 text-center {{ $ligne['en_retard'] > 0 ? 'text-rose-400 font-semibold' : 'text-cyan-100/60' }}">{{ $ligne['en_retard'] }}</td>
                                <td class="px-5 py-3 text-center text-cyan-100/80">{{ $ligne['terminees_30j'] }}</td>
                                <td class="px-5 py-3 text-right text-cyan-100/80 whitespace-nowrap">{{ $formatDt($ligne['cout_12m']) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-ui.card>

    <div class="grid lg:grid-cols-2 gap-4 mb-6">
        {{-- Équipements à pannes fréquentes --}}
        <x-ui.card padding="sm">
            <h2 class="font-display font-semibold text-white mb-3 flex items-center gap-2"><i data-lucide="alert-octagon" class="w-5 h-5 text-amber-300"></i>Équipements les plus en panne</h2>
            @forelse($pannes as $ligne)
                <div class="flex items-center justify-between gap-3 py-2 border-b border-white/5 last:border-0 text-sm">
                    <div class="min-w-0">
                        <p class="text-white font-medium truncate">{{ $ligne['infrastructure']->nom }}</p>
                        <p class="text-xs text-cyan-100/50">{{ $ligne['infrastructure']->zone?->nom ?? '—' }}</p>
                    </div>
                    <div class="text-right shrink-0">
                        <p class="text-amber-300 font-semibold">{{ $ligne['pannes'] }} panne(s)</p>
                        <p class="text-xs text-cyan-100/50">{{ $formatDt($ligne['cout']) }}</p>
                    </div>
                </div>
            @empty
                <p class="text-sm text-cyan-100/60">Aucune intervention corrective sur 12 mois.</p>
            @endforelse
        </x-ui.card>

        {{-- Interventions en retard --}}
        <x-ui.card padding="sm">
            <h2 class="font-display font-semibold text-white mb-3 flex items-center gap-2"><i data-lucide="clock" class="w-5 h-5 text-rose-300"></i>Interventions en retard</h2>
            @forelse($enRetard as $intervention)
                <a href="{{ route('manager.interventions.show', $intervention) }}" class="flex items-center justify-between gap-3 py-2 border-b border-white/5 last:border-0 text-sm hover:bg-white/[.03] rounded">
                    <div class="min-w-0">
                        <p class="text-white font-medium truncate">#{{ $intervention->id }} · {{ $intervention->infrastructure?->nom ?? Str::limit($intervention->description, 40) }}</p>
                        <p class="text-xs text-cyan-100/50">{{ $intervention->technicien?->nom ?? '—' }}</p>
                    </div>
                    <span class="text-rose-300 text-xs shrink-0">prévue le {{ $intervention->date->format('d/m/Y') }}</span>
                </a>
            @empty
                <p class="text-sm text-cyan-100/60">Aucune intervention en retard.</p>
            @endforelse
        </x-ui.card>
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js" integrity="sha384-NrKB+u6Ts6AtkIhwPixiKTzgSKNblyhlk0Sohlgar9UHUBzai/sgnNNWWd291xqt" crossorigin="anonymous"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof Chart === 'undefined') { return; }

    Chart.defaults.color = 'rgba(207, 250, 254, .7)';
    Chart.defaults.borderColor = 'rgba(255, 255, 255, .08)';

    var couts = @json($couts);
    new Chart(document.getElementById('chart-couts'), {
        type: 'bar',
        data: {
            labels: couts.labels,
            datasets: [
                { label: 'Préventive', data: couts.preventive, backgroundColor: '#8b5cf6', borderRadius: 4 },
                { label: 'Corrective', data: couts.corrective, backgroundColor: '#0ea5e9', borderRadius: 4 }
            ]
        },
        options: {
            maintainAspectRatio: false,
            scales: { x: { stacked: true }, y: { stacked: true, ticks: { callback: function (v) { return v.toLocaleString('fr-FR') + ' DT'; } } } },
            plugins: { tooltip: { callbacks: { label: function (c) { return c.dataset.label + ' : ' + c.parsed.y.toLocaleString('fr-FR') + ' DT'; } } } }
        }
    });

    var statuts = @json($parStatut);
    var couleurs = @json($couleursStatut);
    new Chart(document.getElementById('chart-statuts'), {
        type: 'doughnut',
        data: {
            labels: Object.keys(statuts),
            datasets: [{ data: Object.values(statuts), backgroundColor: Object.keys(statuts).map(function (s) { return couleurs[s]; }), borderWidth: 0 }]
        },
        options: { maintainAspectRatio: false, cutout: '62%', plugins: { legend: { position: 'bottom' } } }
    });
});
</script>
@endpush
