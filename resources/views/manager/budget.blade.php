@extends('layouts.app')

@section('title', 'AquaSecure — Budget et transparence financière')

@php
    use App\Data\PlaceholderData;
    $user     = session('user', ['name' => 'Moncef Triki', 'role' => 'manager']);
    $funding  = PlaceholderData::fundingData();
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
                <div class="text-xs text-slate-400 font-bold uppercase mb-1">Financement total approuvé</div>
                <div class="text-2xl font-display font-bold text-white">${{ number_format($funding['total_approved_funding']) }}</div>
                <p class="text-[11px] text-cyan-400 mt-2">Obligations municipales et subventions</p>
            </div>

            <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-4">
                <div class="text-xs text-slate-400 font-bold uppercase mb-1">Dépenses réelles</div>
                <div class="text-2xl font-display font-bold text-emerald-400">${{ number_format($funding['actual_expenditure']) }}</div>
                <p class="text-[11px] text-slate-400 mt-2">{{ $funding['utilization_rate'] }}% Utilized</p>
            </div>

            <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-4">
                <div class="text-xs text-slate-400 font-bold uppercase mb-1">Budget restant</div>
                <div class="text-2xl font-display font-bold text-teal-300">${{ number_format($funding['remaining_budget']) }}</div>
                <p class="text-[11px] text-teal-400 mt-2">Available for Q4 Renovation</p>
            </div>

            <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-4">
                <div class="text-xs text-slate-400 font-bold uppercase mb-1">Sources de financement</div>
                <div class="text-2xl font-display font-bold text-white">4 Active Grants</div>
                <p class="text-[11px] text-slate-400 mt-2">EU, Federal & Municipal</p>
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

        {{-- EXPENDITURE AUDIT LOG TABLE (Requirement 8) --}}
        <div class="bg-slate-900/90 border border-slate-800 rounded-2xl overflow-hidden shadow-xl">
            <div class="p-4 border-b border-slate-800 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-white flex items-center gap-2">
                        <i data-lucide="receipt" class="w-4 h-4 text-emerald-400"></i> Suivi des dépenses par projet
                    </h3>
                    <p class="text-xs text-slate-400">Transactions vérifiées pour les conduites, les analyses d'eau et la main-d'œuvre.</p>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-300">
                    <thead class="bg-slate-950/80 text-slate-400 uppercase font-bold text-[10px] tracking-wider border-b border-slate-800">
                        <tr>
                            <th class="px-4 py-3">Expense ID</th>
                            <th class="px-4 py-3">Date</th>
                            <th class="px-4 py-3">Project</th>
                            <th class="px-4 py-3">Category</th>
                            <th class="px-4 py-3">Vendor / Contractor</th>
                            <th class="px-4 py-3">Amount</th>
                            <th class="px-4 py-3 text-right">Statut de validation</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60">
                        @foreach($funding['expenditure_history'] as $exp)
                            <tr class="hover:bg-slate-800/40 transition-colors">
                                <td class="px-4 py-3 font-mono font-bold text-emerald-400">{{ $exp['id'] }}</td>
                                <td class="px-4 py-3 text-slate-400">{{ $exp['date'] }}</td>
                                <td class="px-4 py-3 font-semibold text-white">{{ $exp['project'] }}</td>
                                <td class="px-4 py-3 text-slate-300">{{ $exp['category'] }}</td>
                                <td class="px-4 py-3 text-slate-200">{{ $exp['vendor'] }}</td>
                                <td class="px-4 py-3 font-mono font-bold text-white">${{ number_format($exp['amount']) }}</td>
                                <td class="px-4 py-3 text-right">
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/15 text-emerald-300 border border-emerald-500/30">
                                        {{ $exp['status'] }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
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

(function() {
    const ctx = document.getElementById('fundingBreakdownChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['EU Water Fund', 'Federal Grant', 'Municipal Bond', 'AfDB Eco-Fund'],
            datasets: [{
                label: 'Funding Contribution ($)',
                data: [2100000, 1450000, 800000, 500000],
                backgroundColor: ['#0284c7', '#0d9488', '#3b82f6', '#8b5cf6']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { ticks: { color: '#64748b' }, grid: { color: 'rgba(255,255,255,0.05)' } },
                y: { ticks: { color: '#64748b' }, grid: { color: 'rgba(255,255,255,0.05)' } }
            }
        }
    });
})();

(function() {
    const ctx = document.getElementById('expenditureTimelineChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Q1', 'Q2', 'Q3 (Current)', 'Q4 (Target)'],
            datasets: [
                { label: 'Budget prévisionnel ($)', data: [1200000, 2400000, 3600000, 4850000], borderColor: '#64748b', borderDash: [5,5], fill: false },
                { label: 'Dépenses réelles ($)', data: [1150000, 2300000, 3580000, null], borderColor: '#10b981', backgroundColor: 'rgba(16, 185, 129, 0.1)', fill: true, tension: 0.3 }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { labels: { color: '#94a3b8', font: { size: 11 } } } },
            scales: {
                x: { ticks: { color: '#64748b' }, grid: { color: 'rgba(255,255,255,0.05)' } },
                y: { ticks: { color: '#64748b' }, grid: { color: 'rgba(255,255,255,0.05)' } }
            }
        }
    });
})();
</script>
@endpush
