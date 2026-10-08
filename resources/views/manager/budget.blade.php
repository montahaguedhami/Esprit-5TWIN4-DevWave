@extends('layouts.app')

@section('title', 'AquaSecure — Budget et transparence financière')

@php
    $user = session('user', ['name' => 'Moncef Triki', 'role' => 'manager']);
@endphp

@push('styles')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endpush

@section('content')
<div class="min-h-screen bg-[#070e17] text-slate-100 flex flex-col font-sans">

    {{-- Top Header --}}
    <header class="sticky top-0 z-50 bg-[#0c1624]/90 backdrop-blur-md border-b border-slate-800/80 px-4 sm:px-6 py-3">
        <div class="max-w-7xl mx-auto flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('manager.dashboard') }}" class="p-2 rounded-xl bg-slate-900 border border-slate-700 text-slate-300 hover:text-white">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                </a>
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-xl bg-emerald-500/20 border border-emerald-500/30 flex items-center justify-center">
                        <i data-lucide="dollar-sign" class="w-4 h-4 text-emerald-400"></i>
                    </div>
                    <span class="font-display font-bold text-white text-base">Finances et transparence budgétaire</span>
                </div>
            </div>
            <div class="flex items-center gap-3">
                
                {{-- Public View vs Internal Audit Toggle --}}
                <div class="flex items-center gap-2 bg-slate-900 border border-slate-700 rounded-xl px-3 py-1 text-xs">
                    <span class="text-slate-400">Mode de vue :</span>
                    <button id="view-toggle-btn" onclick="togglePublicMode()" class="font-bold text-emerald-400 hover:text-emerald-300 flex items-center gap-1">
                        <i data-lucide="eye" class="w-3.5 h-3.5"></i> <span id="view-mode-label">Vue de gestion</span>
                    </button>
                </div>

                <x-notification-center />
                <x-user-menu />
            </div>
        </div>
    </header>

    {{-- Content Body --}}
    <div class="flex-1 max-w-7xl w-full mx-auto p-4 sm:p-6 space-y-6">
        
        {{-- Section Header --}}
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-display font-bold text-white tracking-tight">Financement et dépenses des travaux municipaux</h1>
                <p class="text-slate-400 text-xs mt-1">Ensures financial accountability for public water infrastructure bonds, eco-grants, and decontamination funds.</p>
            </div>
            <div class="flex items-center gap-2">
                <button onclick="showToast('Export du rapport financier PDF...', 'success')" class="px-4 py-2 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-400 hover:to-teal-500 text-slate-950 font-bold text-xs flex items-center gap-2 shadow-lg shadow-emerald-500/20">
                    <i data-lucide="download" class="w-4 h-4"></i> Exporter le rapport (PDF / CSV)
                </button>
            </div>
        </div>

        {{-- 4 FINANCIAL KPI CARDS --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            
            <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-4">
                <div class="text-xs text-slate-400 font-bold uppercase mb-1">Budget total des projets</div>
                <div class="text-2xl font-display font-bold text-white">{{ number_format($totalBudget, 0, ',', ' ') }} DT</div>
                <p class="text-[11px] text-cyan-400 mt-2">{{ $projets->count() }} projet(s) actif(s)</p>
            </div>

            <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-4">
                <div class="text-xs text-slate-400 font-bold uppercase mb-1">Total financé</div>
                <div class="text-2xl font-display font-bold text-emerald-400">{{ number_format($totalFinance, 0, ',', ' ') }} DT</div>
                <p class="text-[11px] text-slate-400 mt-2">{{ number_format($pourcentageUtilise, 1) }}% du budget</p>
            </div>

            <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-4">
                <div class="text-xs text-slate-400 font-bold uppercase mb-1">Budget restant</div>
                <div class="text-2xl font-display font-bold text-teal-300">{{ number_format($budgetRestant, 0, ',', ' ') }} DT</div>
                <p class="text-[11px] text-teal-400 mt-2">Disponible pour nouveaux projets</p>
            </div>

            <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-4">
                <div class="text-xs text-slate-400 font-bold uppercase mb-1">Sources de financement</div>
                <div class="text-2xl font-display font-bold text-white">{{ $sourcesUniques }} source(s)</div>
                <p class="text-[11px] text-slate-400 mt-2">{{ $financements->count() }} financement(s)</p>
            </div>

        </div>

        {{-- CHARTS ROW --}}
        <div class="grid lg:grid-cols-2 gap-6">
            
            {{-- Chart 1: Funding Breakdown by Source --}}
            <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-5">
                <h3 class="text-sm font-bold text-white mb-3 flex items-center gap-2">
                    <i data-lucide="pie-chart" class="w-4 h-4 text-emerald-400"></i> Répartition des financements
                </h3>
                <div style="height: 220px;">
                    <canvas id="fundingBreakdownChart"></canvas>
                </div>
            </div>

            {{-- Chart 2: Expenditure vs Budget Timeline --}}
            <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-5">
                <h3 class="text-sm font-bold text-white mb-3 flex items-center gap-2">
                    <i data-lucide="bar-chart-3" class="w-4 h-4 text-emerald-400"></i> Dépenses mensuelles et budget prévisionnel
                </h3>
                <div style="height: 220px;">
                    <canvas id="expenditureTimelineChart"></canvas>
                </div>
            </div>

        </div>

        {{-- FINANCEMENTS PAR PROJET TABLE --}}
        <div class="bg-slate-900/90 border border-slate-800 rounded-2xl overflow-hidden shadow-xl">
            <div class="p-4 border-b border-slate-800 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-white flex items-center gap-2">
                        <i data-lucide="receipt" class="w-4 h-4 text-emerald-400"></i> Financements par projet
                    </h3>
                    <p class="text-xs text-slate-400">Liste complète des financements reçus pour chaque projet d'infrastructure</p>
                </div>
                <a href="{{ route('manager.financements.create') }}" 
                   class="px-3 py-1.5 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-400 hover:to-teal-500 text-slate-950 font-bold text-xs flex items-center gap-2 shadow-lg shadow-emerald-500/20">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i> Nouveau financement
                </a>
            </div>
            <div class="overflow-x-auto">
                @if($financements->count() > 0)
                <table class="w-full text-left text-xs text-slate-300">
                    <thead class="bg-slate-950/80 text-slate-400 uppercase font-bold text-[10px] tracking-wider border-b border-slate-800">
                        <tr>
                            <th class="px-4 py-3">Date</th>
                            <th class="px-4 py-3">Projet</th>
                            <th class="px-4 py-3">Source de financement</th>
                            <th class="px-4 py-3">Montant</th>
                            <th class="px-4 py-3">Budget projet</th>
                            <th class="px-4 py-3 text-right">% Financé</th>
                            <th class="px-4 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60">
                        @foreach($financements as $financement)
                            <tr class="hover:bg-slate-800/40 transition-colors">
                                <td class="px-4 py-3 text-slate-400">{{ $financement->date_financement->format('d/m/Y') }}</td>
                                <td class="px-4 py-3 font-semibold text-white">
                                    <a href="{{ route('manager.projets.show', $financement->projet) }}" class="hover:text-cyan-400">
                                        {{ Str::limit($financement->projet->nom, 30) }}
                                    </a>
                                </td>
                                <td class="px-4 py-3 text-slate-300">{{ $financement->source }}</td>
                                <td class="px-4 py-3 font-mono font-bold text-emerald-400">{{ number_format($financement->montant, 0, ',', ' ') }} DT</td>
                                <td class="px-4 py-3 font-mono text-slate-300">{{ number_format($financement->projet->budget, 0, ',', ' ') }} DT</td>
                                <td class="px-4 py-3 text-right">
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold 
                                        {{ $financement->projet->pourcentage_finance >= 100 ? 'bg-teal-500/15 text-teal-300 border border-teal-500/30' : 'bg-cyan-500/15 text-cyan-300 border border-cyan-500/30' }}">
                                        {{ number_format($financement->projet->pourcentage_finance, 1) }}%
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <a href="{{ route('manager.financements.show', $financement) }}" 
                                           class="p-1.5 rounded-lg bg-slate-800/60 hover:bg-slate-700 text-cyan-400 hover:text-cyan-300 transition-colors"
                                           title="Voir">
                                            <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                        </a>
                                        <a href="{{ route('manager.financements.edit', $financement) }}" 
                                           class="p-1.5 rounded-lg bg-slate-800/60 hover:bg-slate-700 text-cyan-400 hover:text-cyan-300 transition-colors"
                                           title="Modifier">
                                            <i data-lucide="edit" class="w-3.5 h-3.5"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                @else
                <div class="p-8 text-center">
                    <div class="w-12 h-12 rounded-xl bg-slate-800 flex items-center justify-center mx-auto mb-3">
                        <i data-lucide="banknote" class="w-6 h-6 text-slate-600"></i>
                    </div>
                    <p class="text-slate-400 text-sm mb-4">Aucun financement enregistré</p>
                    <a href="{{ route('manager.financements.create') }}" 
                       class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-400 hover:to-teal-500 text-slate-950 font-bold text-xs">
                        <i data-lucide="plus" class="w-3.5 h-3.5"></i> Créer le premier financement
                    </a>
                </div>
                @endif
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
let isPublicMode = false;

function togglePublicMode() {
    isPublicMode = !isPublicMode;
    const label = document.getElementById('view-mode-label');
    if (isPublicMode) {
        label.textContent = "Vue publique";
        showToast("Vue publique activée (données internes des prestataires masquées)", "info");
    } else {
        label.textContent = "Vue de gestion";
        showToast("Switched to Vue de gestion", "info");
    }
}

// Chart 1: Funding Breakdown by Source
(function() {
    const ctx = document.getElementById('fundingBreakdownChart').getContext('2d');
    
    @php
        $sourcesData = $financements->groupBy('source')->map(function($items) {
            return $items->sum('montant');
        });
    @endphp
    
    const labels = @json($sourcesData->keys());
    const data = @json($sourcesData->values());
    
    const colors = ['#0284c7', '#0d9488', '#3b82f6', '#8b5cf6', '#f59e0b', '#ec4899'];
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Financement (DT)',
                data: data,
                backgroundColor: colors.slice(0, labels.length)
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { 
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.parsed.y.toLocaleString('fr-TN') + ' DT';
                        }
                    }
                }
            },
            scales: {
                x: { ticks: { color: '#64748b' }, grid: { color: 'rgba(255,255,255,0.05)' } },
                y: { 
                    ticks: { 
                        color: '#64748b',
                        callback: function(value) {
                            return value.toLocaleString('fr-TN') + ' DT';
                        }
                    }, 
                    grid: { color: 'rgba(255,255,255,0.05)' } 
                }
            }
        }
    });
})();

// Chart 2: Expenditure vs Budget Timeline
(function() {
    const ctx = document.getElementById('expenditureTimelineChart').getContext('2d');
    
    @php
        // Grouper les financements par mois
        $financementsByMonth = $financements->groupBy(function($item) {
            return $item->date_financement->format('Y-m');
        })->map(function($items) {
            return $items->sum('montant');
        })->sortKeys();
        
        // Calculer le cumul
        $cumulative = 0;
        $cumulativeData = $financementsByMonth->map(function($montant) use (&$cumulative) {
            $cumulative += $montant;
            return $cumulative;
        });
    @endphp
    
    const labels = @json($financementsByMonth->keys()->map(function($date) {
        return \Carbon\Carbon::parse($date)->format('M Y');
    }));
    const cumulativeFinancing = @json($cumulativeData->values());
    const totalBudget = {{ $totalBudget }};
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                { 
                    label: 'Budget total (DT)', 
                    data: Array(labels.length).fill(totalBudget), 
                    borderColor: '#64748b', 
                    borderDash: [5,5], 
                    fill: false,
                    pointRadius: 0
                },
                { 
                    label: 'Financement cumulé (DT)', 
                    data: cumulativeFinancing, 
                    borderColor: '#10b981', 
                    backgroundColor: 'rgba(16, 185, 129, 0.1)', 
                    fill: true, 
                    tension: 0.3 
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { 
                legend: { labels: { color: '#94a3b8', font: { size: 11 } } },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': ' + context.parsed.y.toLocaleString('fr-TN') + ' DT';
                        }
                    }
                }
            },
            scales: {
                x: { ticks: { color: '#64748b' }, grid: { color: 'rgba(255,255,255,0.05)' } },
                y: { 
                    ticks: { 
                        color: '#64748b',
                        callback: function(value) {
                            return value.toLocaleString('fr-TN') + ' DT';
                        }
                    }, 
                    grid: { color: 'rgba(255,255,255,0.05)' } 
                }
            }
        }
    });
})();
</script>
@endpush
