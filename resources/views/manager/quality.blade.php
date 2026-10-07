@extends('layouts.app')

@section('title', 'AquaSecure — Qualité de l’eau et mesures')

@php
    use App\Data\PlaceholderData;
    $user         = session('user', ['name' => 'Dr. Selim Dridi', 'role' => 'manager']);
    $records      = PlaceholderData::waterQualityRecords();
    $municipalities = PlaceholderData::municipalities();
@endphp

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
#quality-map { height: 320px; border-radius: 1rem; }
.leaflet-tile-pane { filter: brightness(.88) contrast(1.1) saturate(0.8); }
</style>
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
                    <div class="w-8 h-8 rounded-xl bg-teal-500/20 border border-teal-500/30 flex items-center justify-center">
                        <i data-lucide="flask-conical" class="w-4 h-4 text-teal-400"></i>
                    </div>
                    <span class="font-display font-bold text-white text-base">Qualité de l’eau et conformité</span>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <button onclick="openThresholdModal()" class="px-3 py-1.5 rounded-xl bg-teal-500/10 border border-teal-500/30 text-teal-300 hover:bg-teal-500/20 text-xs font-semibold flex items-center gap-2">
                    <i data-lucide="sliders" class="w-3.5 h-3.5"></i> Régler les seuils
                </button>
                <x-notification-center />
                <x-user-menu />
            </div>
        </div>
    </header>

    {{-- Main Container --}}
    <div class="flex-1 max-w-7xl w-full mx-auto p-4 sm:p-6 space-y-6">
        
        {{-- Section Header --}}
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-display font-bold text-white tracking-tight">Qualité de l’eau : mesures et prélèvements</h1>
                <p class="text-slate-400 text-xs mt-1">Analyses de laboratoire, seuils de contrôle et alertes de contamination.</p>
            </div>
            <div class="flex items-center gap-2">
                <button onclick="showToast('Exporting Water Quality Compliance Log (CSV)...', 'success')" class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 text-xs font-semibold flex items-center gap-2">
                    <i data-lucide="file-spreadsheet" class="w-4 h-4 text-teal-400"></i> Exporter le rapport CSV
                </button>
            </div>
        </div>

        {{-- 4 PARAMETER CARDS (pH, Turbidity, Chlorine, Lead) --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            
            {{-- 1. pH Parameter --}}
            <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-4 relative">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs text-slate-400 font-bold uppercase">pH Level</span>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/15 text-emerald-300 border border-emerald-500/30">Target 6.5 - 8.5</span>
                </div>
                <div class="text-2xl font-display font-bold text-white">7.45 <span class="text-xs text-slate-400 font-normal">pH</span></div>
                <div class="w-full bg-slate-800 h-2 rounded-full mt-3 overflow-hidden">
                    <div class="bg-emerald-400 h-full rounded-full" style="width: 74.5%"></div>
                </div>
                <p class="text-[11px] text-slate-400 mt-2 flex items-center justify-between">
                    <span>99.2% Compliant</span>
                    <span class="text-emerald-400 font-semibold">Optimal</span>
                </p>
            </div>

            {{-- 2. Turbidity NTU --}}
            <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-4 relative">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs text-slate-400 font-bold uppercase">Turbidity</span>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-cyan-500/15 text-cyan-300 border border-cyan-500/30">Limit &lt; 1.0 NTU</span>
                </div>
                <div class="text-2xl font-display font-bold text-white">0.42 <span class="text-xs text-slate-400 font-normal">NTU</span></div>
                <div class="w-full bg-slate-800 h-2 rounded-full mt-3 overflow-hidden">
                    <div class="bg-cyan-400 h-full rounded-full" style="width: 42%"></div>
                </div>
                <p class="text-[11px] text-slate-400 mt-2 flex items-center justify-between">
                    <span>Clear Clarity</span>
                    <span class="text-cyan-400 font-semibold">Normal</span>
                </p>
            </div>

            {{-- 3. Chlore résiduel --}}
            <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-4 relative">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs text-slate-400 font-bold uppercase">Chlore résiduel</span>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-500/15 text-blue-300 border border-blue-500/30">0.2 - 2.0 mg/L</span>
                </div>
                <div class="text-2xl font-display font-bold text-white">0.85 <span class="text-xs text-slate-400 font-normal">mg/L</span></div>
                <div class="w-full bg-slate-800 h-2 rounded-full mt-3 overflow-hidden">
                    <div class="bg-blue-400 h-full rounded-full" style="width: 55%"></div>
                </div>
                <p class="text-[11px] text-slate-400 mt-2 flex items-center justify-between">
                    <span>Désinfection active</span>
                    <span class="text-blue-400 font-semibold">Safe</span>
                </p>
            </div>

            {{-- 4. Lead (Pb) Level --}}
            <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-4 relative">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs text-slate-400 font-bold uppercase">Lead (Pb) Concentration</span>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-500/15 text-amber-300 border border-amber-500/30">Limit &lt; 15 ppb</span>
                </div>
                <div class="text-2xl font-display font-bold text-white">2.1 <span class="text-xs text-slate-400 font-normal">ppb</span></div>
                <div class="w-full bg-slate-800 h-2 rounded-full mt-3 overflow-hidden">
                    <div class="bg-emerald-400 h-full rounded-full" style="width: 14%"></div>
                </div>
                <p class="text-[11px] text-slate-400 mt-2 flex items-center justify-between">
                    <span>Norme plomb 2026</span>
                    <span class="text-emerald-400 font-semibold">Passed</span>
                </p>
            </div>
        </div>

        {{-- Sampling Map & Trend Charts --}}
        <div class="grid lg:grid-cols-2 gap-6">
            
            {{-- Leaflet Map Sampling Stations --}}
            <div class="bg-slate-900/90 border border-slate-800 rounded-2xl overflow-hidden p-4">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-bold text-white flex items-center gap-2">
                        <i data-lucide="map-pin" class="w-4 h-4 text-teal-400"></i> Carte des stations de prélèvement
                    </h3>
                    <span class="text-xs text-slate-400">4 points de prélèvement actifs</span>
                </div>
                <div id="quality-map"></div>
            </div>

            {{-- Chart: 30-Day Quality Trends --}}
            <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-5">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-bold text-white flex items-center gap-2">
                        <i data-lucide="line-chart" class="w-4 h-4 text-teal-400"></i> Conformité de la qualité de l’eau sur 30 jours
                    </h3>
                    <span class="text-xs text-emerald-400 font-semibold bg-emerald-500/10 px-2 py-0.5 rounded">98.5% Average</span>
                </div>
                <div style="height: 240px;">
                    <canvas id="qualityTrendChart"></canvas>
                </div>
            </div>
        </div>

        {{-- Water Sampling Records Table --}}
        <div class="bg-slate-900/90 border border-slate-800 rounded-2xl overflow-hidden shadow-xl">
            <div class="p-4 border-b border-slate-800 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-white flex items-center gap-2">
                        <i data-lucide="clipboard-list" class="w-4 h-4 text-teal-400"></i> Résultats d'analyses et suivi de la qualité
                    </h3>
                    <p class="text-xs text-slate-400">Consultez les résultats vérifiés par laboratoire et les relevés en attente de validation.</p>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-300">
                    <thead class="bg-slate-950/80 text-slate-400 uppercase font-bold text-[10px] tracking-wider border-b border-slate-800">
                        <tr>
                            <th class="px-4 py-3">Sample ID & Point</th>
                            <th class="px-4 py-3">Zone</th>
                            <th class="px-4 py-3">Date</th>
                            <th class="px-4 py-3">pH</th>
                            <th class="px-4 py-3">Turbidity</th>
                            <th class="px-4 py-3">Chlorine</th>
                            <th class="px-4 py-3">Lead (Pb)</th>
                            <th class="px-4 py-3">Verification</th>
                            <th class="px-4 py-3 text-right">Compliance</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60">
                        @foreach($records as $rec)
                            <tr class="hover:bg-slate-800/40 transition-colors">
                                <td class="px-4 py-3 font-semibold text-white">
                                    <div class="font-mono text-cyan-400 text-[11px]">{{ $rec['id'] }}</div>
                                    <div>{{ $rec['sampling_point'] }}</div>
                                </td>
                                <td class="px-4 py-3 text-slate-200">{{ $rec['zone'] }}</td>
                                <td class="px-4 py-3 text-slate-400">{{ $rec['date'] }}</td>
                                <td class="px-4 py-3 font-mono {{ $rec['pH'] > 8.5 ? 'text-red-400 font-bold' : 'text-slate-200' }}">{{ $rec['pH'] }}</td>
                                <td class="px-4 py-3 font-mono {{ $rec['turbidity'] > 1.0 ? 'text-amber-400 font-bold' : 'text-slate-200' }}">{{ $rec['turbidity'] }} NTU</td>
                                <td class="px-4 py-3 font-mono {{ $rec['residual_chlorine'] < 0.2 ? 'text-red-400 font-bold' : 'text-slate-200' }}">{{ $rec['residual_chlorine'] }} mg/L</td>
                                <td class="px-4 py-3 font-mono {{ $rec['lead_pb'] > 15 ? 'text-red-400 font-bold' : 'text-slate-200' }}">{{ $rec['lead_pb'] }} ppb</td>
                                <td class="px-4 py-3">
                                    @if($rec['is_verified'])
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-teal-500/15 text-teal-300 border border-teal-500/30 inline-flex items-center gap-1">
                                            <i data-lucide="check-circle-2" class="w-3 h-3 text-teal-400"></i> Vérifié en laboratoire
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/15 text-amber-300 border border-amber-500/30 inline-flex items-center gap-1">
                                            <i data-lucide="clipboard-check" class="w-3 h-3 text-amber-400"></i> Relevé à vérifier
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right font-bold {{ $rec['status'] === 'Compliant' ? 'text-emerald-400' : ($rec['status'] === 'Alert' ? 'text-amber-400' : 'text-red-400') }}">
                                    {{ $rec['status'] }} ({{ $rec['overall_compliance'] }}%)
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

{{-- Threshold Configuration Modal --}}
<div id="threshold-modal" class="hidden fixed inset-0 z-50 bg-black/70 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-slate-900 border border-slate-700 rounded-2xl max-w-md w-full p-6 space-y-4 shadow-2xl">
        <div class="flex items-center justify-between">
            <h3 class="text-base font-bold text-white flex items-center gap-2">
                <i data-lucide="sliders" class="w-5 h-5 text-teal-400"></i> Seuils d’alerte pour la qualité de l’eau
            </h3>
            <button onclick="closeThresholdModal()" class="text-slate-400 hover:text-white">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <p class="text-xs text-slate-400">Configure safety trigger limits for automatic SMS & email dispatch to technicians.</p>
        
        <div class="space-y-3 text-xs text-slate-200">
            <div>
                <label class="block mb-1 font-semibold">pH maximal autorisé</label>
                <input type="number" step="0.1" value="8.5" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-white">
            </div>
            <div>
                <label class="block mb-1 font-semibold">Seuil maximal de turbidité (NTU)</label>
                <input type="number" step="0.1" value="1.0" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-white">
            </div>
            <div>
                <label class="block mb-1 font-semibold">Minimum Chlore résiduel (mg/L)</label>
                <input type="number" step="0.05" value="0.20" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-white">
            </div>
            <div>
                <label class="block mb-1 font-semibold">Seuil d’action pour le plomb (ppb)</label>
                <input type="number" value="15" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-white">
            </div>
        </div>

        <div class="flex items-center justify-end gap-2 pt-2">
            <button onclick="closeThresholdModal()" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 text-xs font-semibold">Cancel</button>
            <button onclick="closeThresholdModal(); showToast('Threshold parameters updated successfully!', 'success');" class="px-4 py-2 rounded-xl bg-teal-500 hover:bg-teal-400 text-slate-950 font-bold text-xs">Enregistrer les seuils</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
const RECORDS = @json($records);

function openThresholdModal() { document.getElementById('threshold-modal').classList.remove('hidden'); }
function closeThresholdModal() { document.getElementById('threshold-modal').classList.add('hidden'); }

(function() {
    const map = L.map('quality-map', { center: [36.2, 10.3], zoom: 7, zoomControl: false, attributionControl: false });
    L.control.zoom({ position: 'topright' }).addTo(map);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 18 }).addTo(map);

    RECORDS.forEach(r => {
        const c = r.status === 'Compliant' ? '#2dd4bf' : (r.status === 'Alert' ? '#fbbf24' : '#ef4444');
        const svg = `<svg xmlns="http://www.w3.org/2000/svg" width="30" height="30">
            <circle cx="15" cy="15" r="12" fill="${c}" fill-opacity="0.8" stroke="#ffffff" stroke-width="2"/>
        </svg>`;
        L.marker([r.lat, r.lng], {
            icon: L.divIcon({ html: svg, iconSize:[30,30], iconAnchor:[15,15], className:'' })
        }).addTo(map).bindPopup(`<b>${r.sampling_point}</b><br>Compliance: ${r.overall_compliance}%<br>pH: ${r.pH} | Turbidity: ${r.turbidity} NTU`);
    });
})();

(function() {
    const ctx = document.getElementById('qualityTrendChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
            datasets: [
                { label: 'Taux de conformité (%)', data: [97.2, 98.4, 96.8, 98.5], borderColor: '#2dd4bf', backgroundColor: 'rgba(45, 212, 191, 0.1)', fill: true, tension: 0.3 },
                { label: 'pH Average', data: [7.38, 7.42, 7.50, 7.35], borderColor: '#3b82f6', tension: 0.3 }
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
