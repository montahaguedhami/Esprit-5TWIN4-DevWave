@extends('layouts.manager')

@section('title', 'Tableau de bord — Gestionnaire')

@php
    use App\Data\PlaceholderData;
    $user         = session('user', ['name' => 'Ines Mansouri', 'role' => 'manager']);
    $municipalities = PlaceholderData::municipalities();
    $zones        = PlaceholderData::mapZones();
    $stats        = PlaceholderData::stats();
    $reclamations = PlaceholderData::adminAllReclamations();
    $monthly      = PlaceholderData::analyticsMonthly();
    $projects     = PlaceholderData::projects();
    $qualityData  = PlaceholderData::waterQualityRecords();
    $funding      = PlaceholderData::fundingData();

    $firstName    = explode(' ', $user['name'])[0];

    $totalInc     = count($reclamations);
    $pendingInc   = count(array_filter($reclamations, fn($r)=>$r['status']==='pending'));
    $inProgressInc = count(array_filter($reclamations, fn($r)=>$r['status']==='in_progress'));
    $resolvedInc  = count(array_filter($reclamations, fn($r)=>$r['status']==='resolved'));

    $statusStyle = [
        'pending'     => ['bg'=>'bg-red-500/15',   'text'=>'text-red-300',   'dot'=>'bg-red-400 animate-pulse','label'=>'À affecter / ouverte'],
        'in_progress' => ['bg'=>'bg-amber-500/15', 'text'=>'text-amber-300', 'dot'=>'bg-amber-400',            'label'=>'En cours'],
        'resolved'    => ['bg'=>'bg-teal-500/15',  'text'=>'text-teal-300',  'dot'=>'bg-teal-400',             'label'=>'Résolu'],
    ];
    $priorityStyle = [
        'critical' => ['bg'=>'bg-red-500/15',  'text'=>'text-red-300',  'label'=>'Critique'],
        'medium'   => ['bg'=>'bg-amber-500/15','text'=>'text-amber-300','label'=>'Moyenne'],
        'low'      => ['bg'=>'bg-blue-500/15', 'text'=>'text-blue-300', 'label'=>'Faible'],
    ];
@endphp

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
#mgr-map { height: 320px; border-radius: var(--radius-lg); }
.leaflet-tile-pane { filter: brightness(.98) contrast(1.02) saturate(.9); }
.leaflet-control-zoom a { background: var(--bg-card) !important; border-color: var(--border) !important; color: var(--role-accent) !important; }
.leaflet-popup-content-wrapper { background: var(--bg-card) !important; border: 1px solid var(--border) !important; border-radius: var(--radius-md) !important; color: var(--text-primary) !important; }
.leaflet-popup-tip { background: var(--bg-card) !important; }
</style>
@endpush

@section('manager-content')
<div class="space-y-6 manager-dashboard">

    {{-- Welcome & Subtitle --}}
    <div class="glass rounded-xl p-5 border border-slate-200 bg-white shadow-sm flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="inline-flex items-center gap-2 px-2.5 py-0.5 rounded-full bg-cyan-50 border border-cyan-200 text-cyan-800 text-[11px] font-semibold mb-1.5">
                <i data-lucide="activity" class="w-3.5 h-3.5 text-cyan-600"></i>
                Supervision Réseau & Patrimoine
            </div>
            <h1 class="text-xl sm:text-2xl font-display font-bold text-slate-900 tracking-tight">
                Tableau de bord des infrastructures
            </h1>
            <p class="text-slate-500 text-xs mt-0.5">
                Suivi opérationnel du réseau et des budgets pour <span id="current-utility-title" class="text-cyan-700 font-semibold">Régie des eaux du Grand Tunis</span>.
            </p>
            <label for="utility-selector" class="sr-only">Secteur</label>
            <select id="utility-selector" onchange="switchUtility(this.value)" class="mt-3 max-w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-700">
                @foreach($municipalities as $muni)
                    <option value="{{ $muni['id'] }}" {{ $muni['active'] ? 'selected' : '' }}>
                        {{ $muni['name'] }} ({{ $muni['assets'] }} actifs)
                    </option>
                @endforeach
            </select>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('manager.map') }}" class="px-3.5 py-2 rounded-xl bg-cyan-600 hover:bg-cyan-700 text-white text-xs font-semibold shadow-sm flex items-center gap-1.5 transition-all">
                <i data-lucide="map" class="w-4 h-4"></i>
                <span>Carte réseau</span>
            </a>
            <a href="{{ route('manager.infrastructures.index') }}" class="px-3.5 py-2 rounded-xl bg-white hover:bg-slate-50 text-slate-700 border border-slate-300 text-xs font-semibold shadow-sm flex items-center gap-1.5 transition-all">
                <i data-lucide="layers" class="w-4 h-4 text-slate-500"></i>
                <span>Infrastructures</span>
            </a>
        </div>
    </div>

            {{-- ══ 6 DASHBOARD SUMMARY CARDS (Requirement 3) ═════════════════════ --}}
            <div class="grid grid-cols-2 lg:grid-cols-3 2xl:grid-cols-6 gap-4 dashboard-kpis">
                
                {{-- 1. Registered Assets --}}
                <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-4 relative overflow-hidden group hover:border-slate-700 transition-all">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs text-slate-400 font-semibold">Équipements recensés</span>
                        <div class="w-8 h-8 rounded-lg bg-cyan-500/10 text-cyan-400 flex items-center justify-center">
                            <i data-lucide="database" class="w-4 h-4"></i>
                        </div>
                    </div>
                    <div class="text-2xl font-display font-bold text-white">1,482</div>
                    <p class="text-[11px] text-emerald-400 mt-1 flex items-center gap-1">
                        <i data-lucide="trending-up" class="w-3 h-3"></i> +12 ce mois-ci
                    </p>
                </div>

                {{-- 2. Active Incidents --}}
                <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-4 relative overflow-hidden group hover:border-slate-700 transition-all">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs text-slate-400 font-semibold">Incidents en cours</span>
                        <div class="w-8 h-8 rounded-lg bg-red-500/10 text-red-400 flex items-center justify-center">
                            <i data-lucide="alert-triangle" class="w-4 h-4"></i>
                        </div>
                    </div>
                    <div class="text-2xl font-display font-bold text-white">{{ $totalInc }}</div>
                    <p class="text-[11px] text-amber-400 mt-1 flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-red-400 animate-pulse"></span> 2 critiques, {{ $pendingInc }} sans affectation
                    </p>
                </div>

                {{-- 3. Open Leak Reports --}}
                <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-4 relative overflow-hidden group hover:border-slate-700 transition-all">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs text-slate-400 font-semibold">Fuites signalées</span>
                        <div class="w-8 h-8 rounded-lg bg-amber-500/10 text-amber-400 flex items-center justify-center">
                            <i data-lucide="droplet-off" class="w-4 h-4"></i>
                        </div>
                    </div>
                    <div class="text-2xl font-display font-bold text-white">14</div>
                    <p class="text-[11px] text-slate-400 mt-1">Délai moyen de réparation : 2,4 h</p>
                </div>

                {{-- 4. Water Quality Alerts --}}
                <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-4 relative overflow-hidden group hover:border-slate-700 transition-all">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs text-slate-400 font-semibold">Alertes qualité</span>
                        <div class="w-8 h-8 rounded-lg bg-teal-500/10 text-teal-400 flex items-center justify-center">
                            <i data-lucide="flask-conical" class="w-4 h-4"></i>
                        </div>
                    </div>
                    <div class="text-2xl font-display font-bold text-white">3</div>
                    <p class="text-[11px] text-teal-300 mt-1">98,5 % de conformité</p>
                </div>

                {{-- 5. Ongoing Maintenance Projects --}}
                <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-4 relative overflow-hidden group hover:border-slate-700 transition-all">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs text-slate-400 font-semibold">Projets en cours</span>
                        <div class="w-8 h-8 rounded-lg bg-purple-500/10 text-purple-400 flex items-center justify-center">
                            <i data-lucide="briefcase" class="w-4 h-4"></i>
                        </div>
                    </div>
                    <div class="text-2xl font-display font-bold text-white">{{ count($projects) }}</div>
                    <p class="text-[11px] text-slate-400 mt-1">Valeur totale : 4,85 M$</p>
                </div>

                {{-- 6. Renovation Budget & Utilization --}}
                <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-4 relative overflow-hidden group hover:border-slate-700 transition-all">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs text-slate-400 font-semibold">Budget consommé</span>
                        <div class="w-8 h-8 rounded-lg bg-emerald-500/10 text-emerald-400 flex items-center justify-center">
                            <i data-lucide="dollar-sign" class="w-4 h-4"></i>
                        </div>
                    </div>
                    <div class="text-2xl font-display font-bold text-white">${{ number_format($funding['actual_expenditure']/1000000, 2) }}M</div>
                    <p class="text-[11px] text-emerald-400 mt-1 font-semibold">{{ $funding['utilization_rate'] }} % d’un budget de ${{ number_format($funding['total_approved_funding']/1000000, 2) }}M</p>
                </div>
            </div>

            {{-- ══ INTERACTIVE INFRASTRUCTURE MAP (Requirement 3 & 4) ════════════════ --}}
            <div class="bg-slate-900/90 border border-slate-800 rounded-2xl overflow-hidden shadow-xl">
                <div class="p-4 border-b border-slate-800 flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h2 class="text-sm font-bold text-white flex items-center gap-2">
                            <i data-lucide="map-pin" class="w-4 h-4 text-cyan-400"></i> Carte du réseau et des incidents
                        </h2>
                        <p class="text-slate-400 text-xs mt-0.5">Canalisations, réservoirs, stations de traitement et fuites signalées</p>
                    </div>
                    <div class="flex items-center gap-2 text-xs">
                        <span class="flex items-center gap-1 text-teal-400"><span class="w-2.5 h-2.5 rounded-full bg-teal-400"></span> Normal</span>
                        <span class="flex items-center gap-1 text-amber-400"><span class="w-2.5 h-2.5 rounded-full bg-amber-400"></span> Maintenance</span>
                        <span class="flex items-center gap-1 text-red-400"><span class="w-2.5 h-2.5 rounded-full bg-red-400 animate-pulse"></span> Critique / fuite</span>
                        <a href="{{ route('manager.map') }}" class="ml-2 px-3 py-1 rounded-lg bg-slate-800 text-cyan-300 hover:text-white font-semibold">Afficher la carte →</a>
                    </div>
                </div>
                <div id="mgr-map"></div>
            </div>

            {{-- ══ INTERACTIVE CHARTS ROW (Requirement 3) ════════════════════════ --}}
            <div class="grid lg:grid-cols-2 gap-6">
                
                {{-- Chart 1: Incident Trends & Response --}}
                <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-5">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-sm font-bold text-white flex items-center gap-2">
                                <i data-lucide="line-chart" class="w-4 h-4 text-cyan-400"></i> Évolution des incidents et réparations
                            </h3>
                            <p class="text-xs text-slate-400">Fuites signalées et incidents résolus par mois</p>
                        </div>
                        <span class="text-xs font-semibold text-emerald-400 bg-emerald-500/10 border border-emerald-500/20 px-2.5 py-1 rounded-lg">-14 % de fuites</span>
                    </div>
                    <div style="height: 220px;">
                        <canvas id="incidentTrendsChart"></canvas>
                    </div>
                </div>

                {{-- Chart 2: Historique de la qualité de l'eau (pH, Turbidité, Chlore) --}}
                <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-5">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-sm font-bold text-white flex items-center gap-2">
                                <i data-lucide="flask-conical" class="w-4 h-4 text-teal-400"></i> Indicateurs de qualité de l’eau
                            </h3>
                            <p class="text-xs text-slate-400">Résultats de qualité de l'eau et seuils EPA / INNORPI</p>
                        </div>
                        <span class="text-xs font-semibold text-cyan-300 bg-cyan-500/10 border border-cyan-500/20 px-2.5 py-1 rounded-lg">Plage optimale</span>
                    </div>
                    <div style="height: 220px;">
                        <canvas id="qualityHistoryChart"></canvas>
                    </div>
                </div>
            </div>

            {{-- ══ RECENT INCIDENTS TABLE & BUDGET ALLOCATION ════════════════════ --}}
            <div class="grid lg:grid-cols-3 gap-6">
                
                {{-- Recent Incidents Table (2/3) --}}
                <div class="lg:col-span-2 bg-slate-900/90 border border-slate-800 rounded-2xl overflow-hidden">
                    <div class="p-4 border-b border-slate-800 flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-bold text-white flex items-center gap-2">
                                <i data-lucide="list" class="w-4 h-4 text-cyan-400"></i> Incidents récents
                            </h3>
                            <p class="text-xs text-slate-400">Fuites signalées, alertes qualité et ruptures de canalisation</p>
                        </div>
                        <a href="{{ route('manager.incidents') }}" class="text-xs font-semibold text-cyan-400 hover:text-cyan-300">Voir tous les incidents →</a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs text-slate-300">
                            <thead class="bg-slate-950/60 text-slate-400 uppercase font-bold text-[10px] tracking-wider border-b border-slate-800">
                                <tr>
                                    <th class="px-4 py-3">ID & Type</th>
                                    <th class="px-4 py-3">Lieu</th>
                                    <th class="px-4 py-3">Priorité</th>
                                    <th class="px-4 py-3">Statut</th>
                                    <th class="px-4 py-3">Équipe affectée</th>
                                    <th class="px-4 py-3 text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-800/60">
                                @foreach($reclamations as $rec)
                                    @php
                                        $ss = $statusStyle[$rec['status']];
                                        $ps = $priorityStyle[$rec['priority']];
                                    @endphp
                                    <tr class="hover:bg-slate-800/40 transition-colors">
                                        <td class="px-4 py-3 font-semibold text-white">
                                            <div class="font-mono text-cyan-400 text-[11px]">{{ $rec['id'] }}</div>
                                            <div>{{ $rec['type'] }}</div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="text-slate-200 font-medium">{{ $rec['zone'] }}</div>
                                            <div class="text-[10px] text-slate-400 truncate max-w-[140px]">{{ $rec['address'] }}</div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $ps['bg'] }} {{ $ps['text'] }}">
                                                {{ $ps['label'] }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $ss['bg'] }} {{ $ss['text'] }} inline-flex items-center gap-1">
                                                <span class="w-1.5 h-1.5 rounded-full {{ $ss['dot'] }}"></span> {{ $ss['label'] }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-slate-300">
                                            {{ $rec['technician'] ?? 'Unassigned' }}
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <button onclick="showToast('Dispatching team for {{ $rec['id'] }}...', 'info')" class="px-2.5 py-1 rounded-lg bg-cyan-500/10 hover:bg-cyan-500/20 text-cyan-300 text-[11px] font-bold border border-cyan-500/20">
                                                Manage
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Project Budget Allocation (1/3) --}}
                <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-5">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-sm font-bold text-white flex items-center gap-2">
                            <i data-lucide="pie-chart" class="w-4 h-4 text-emerald-400"></i> Répartition des financements
                        </h3>
                        <a href="{{ route('manager.budget') }}" class="text-xs font-semibold text-emerald-400 hover:text-emerald-300">Détails du budget →</a>
                    </div>
                    <div style="height: 180px;" class="mb-4">
                        <canvas id="fundingDoughnutChart"></canvas>
                    </div>
                    <div class="space-y-2 text-xs">
                        @foreach($funding['funding_sources'] as $source)
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full" style="background-color: {{ $source['color'] }}"></span>
                                    <span class="text-slate-300">{{ $source['name'] }}</span>
                                </div>
                                <span class="font-bold text-white">${{ number_format($source['amount']/1000) }}k ({{ $source['pct'] }}%)</span>
                            </div>
                        @endforeach
                    </div>
                </div>

            </div>

</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
const MAP_ZONES = @json($zones);
const RECLAMATIONS = @json($reclamations);

function switchUtility(id) {
    const selector = document.getElementById('utility-selector');
    const selectedText = selector.options[selector.selectedIndex].text.split(' (')[0];
    document.getElementById('current-utility-title').textContent = selectedText;
    showToast('Switched utility scope to ' + selectedText, 'info');
}

/* ── Leaflet Map Setup ──────────────────────────── */
(function() {
    const map = L.map('mgr-map', {
        center: [35.8, 10.2], zoom: 7,
        zoomControl: false, attributionControl: false
    });
    L.control.zoom({ position: 'topright' }).addTo(map);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 18 }).addTo(map);

    const zoneC = { normal:'#2dd4bf', alert:'#fbbf24', critical:'#ef4444' };
    MAP_ZONES.forEach(z => {
        const c = zoneC[z.status] ?? '#2dd4bf';
        const size = 28, half = 14;
        const svg = `<svg xmlns="http://www.w3.org/2000/svg" width="${size}" height="${size}">
            <circle cx="${half}" cy="${half}" r="${half-2}" fill="${c}" fill-opacity="0.2" stroke="${c}" stroke-width="2"/>
        </svg>`;
        L.marker([z.lat, z.lng], {
            icon: L.divIcon({ html: svg, iconSize:[size,size], iconAnchor:[half,half], className:'' })
        }).addTo(map).bindTooltip(`<b>${z.emoji} ${z.name} Zone</b><br>Quality: ${z.quality}% | Pressure: ${z.pressure} bar`, { sticky: true });
    });

    const recC = { pending:'#ef4444', in_progress:'#f97316', resolved:'#2dd4bf' };
    RECLAMATIONS.forEach(r => {
        const c = recC[r.status] ?? '#9ca3af';
        const size = 32, half = 16;
        const svg = `<svg xmlns="http://www.w3.org/2000/svg" width="${size}" height="${size}">
            <circle cx="${half}" cy="${half}" r="${half-3}" fill="${c}" fill-opacity="0.9" stroke="#ffffff" stroke-width="2"/>
        </svg>`;
        L.marker([r.lat, r.lng], {
            icon: L.divIcon({ html: svg, iconSize:[size,size], iconAnchor:[half,half], className:'' })
        }).addTo(map).bindPopup(`
            <div style="font-family:sans-serif;padding:6px">
                <div style="color:#38bdf8;font-weight:bold;font-size:12px">${r.id} - ${r.type}</div>
                <div style="color:#e2e8f0;font-size:11px;margin-top:2px">${r.description}</div>
                <div style="color:#94a3b8;font-size:10px;margin-top:4px">Location: ${r.address}</div>
            </div>
        `);
    });
})();

/* ── Chart 1: Incident Trends Chart ────────────────────────── */
(function() {
    const ctx = document.getElementById('incidentTrendsChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct'],
            datasets: [
                {
                    label: 'Leaks Reported',
                    data: [18, 24, 21, 15, 14, 11],
                    borderColor: '#ef4444',
                    backgroundColor: 'rgba(239, 68, 68, 0.1)',
                    fill: true,
                    tension: 0.4
                },
                {
                    label: 'Repairs Completed',
                    data: [16, 22, 20, 15, 14, 10],
                    borderColor: '#2dd4bf',
                    backgroundColor: 'rgba(45, 212, 191, 0.1)',
                    fill: true,
                    tension: 0.4
                }
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

/* ── Chart 2: Water Quality History ──────────────────────── */
(function() {
    const ctx = document.getElementById('qualityHistoryChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Tunis Nord', 'Ariana', 'Ben Arous', 'Sousse', 'Sfax'],
            datasets: [
                {
                    label: 'pH Level (Optimal 6.5-8.5)',
                    data: [7.42, 7.82, 8.45, 7.20, 7.30],
                    backgroundColor: '#0284c7'
                },
                {
                    label: 'Turbidity (NTU <1.0)',
                    data: [0.45, 1.25, 2.45, 0.22, 0.41],
                    backgroundColor: '#0d9488'
                },
                {
                    label: 'Chlore résiduel (mg/L)',
                    data: [0.85, 0.35, 0.12, 1.10, 0.95],
                    backgroundColor: '#3b82f6'
                }
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

/* ── Chart 3: Funding Allocation Doughnut Chart ───────────── */
(function() {
    const ctx = document.getElementById('fundingDoughnutChart').getContext('2d');
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['EU Water Fund', 'Federal Grant', 'Municipal Bond', 'AfDB Eco-Fund'],
            datasets: [{
                data: [2100000, 1450000, 800000, 500000],
                backgroundColor: ['#0284c7', '#0d9488', '#3b82f6', '#8b5cf6'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            cutout: '70%'
        }
    });
})();

document.addEventListener('DOMContentLoaded', () => {
    if (typeof lucide !== 'undefined') lucide.createIcons();
});
</script>
@endpush
