@extends($layout ?? 'layouts.manager')

@section('title', 'Carte Réseau — AquaSecure')

@php
    $normalCount   = count(array_filter($zones, fn($z) => ($z['status'] ?? 'normal') === 'normal'));
    $alertCount    = count(array_filter($zones, fn($z) => ($z['status'] ?? 'normal') === 'alert'));
    $criticalCount = count(array_filter($zones, fn($z) => ($z['status'] ?? 'normal') === 'critical'));
@endphp

@push('styles')
{{-- Leaflet CSS & MarkerCluster CSS --}}
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha384-sHL9NAb7lN7rfvG5lfHpm643Xkcjzp4jFvuavGOndn6pjVqS6ny56CAt3nsEVT4H" crossorigin="anonymous"/>
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css" integrity="sha384-pmjIAcz2bAn0xukfxADbZIb3t8oRT9Sv0rvO+BR5Csr6Dhqq+nZs59P0pPKQJkEV" crossorigin="anonymous" />
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css" integrity="sha384-wgw+aLYNQ7dlhK47ZPK7FRACiq7ROZwgFNg0m04avm4CaXS+Z9Y7nMu8yNjBKYC+" crossorigin="anonymous" />

<style>
/* ── Map container ── */
#network-map {
    height: 100%;
    width: 100%;
    border-radius: 0;
    background: #090e17;
    z-index: 1;
}

/* Dark themed tiles */
.leaflet-tile-pane {
    filter: brightness(0.75) invert(1) contrast(3) hue-rotate(200deg) saturate(0.3) brightness(0.65);
}

/* Custom dark popup */
.leaflet-popup-content-wrapper {
    background: rgba(15, 23, 42, 0.95) !important;
    border: 1px solid rgba(6, 182, 212, 0.3) !important;
    border-radius: 16px !important;
    box-shadow: 0 16px 40px rgba(0, 0, 0, 0.6) !important;
    backdrop-filter: blur(20px);
    color: #f1f5f9 !important;
    padding: 0 !important;
}
.leaflet-popup-tip { background: #0f172a !important; }
.leaflet-popup-content { margin: 0 !important; padding: 0 !important; width: auto !important; min-width: 260px; max-width: 320px; }
.leaflet-popup-close-button { color: #94a3b8 !important; font-size: 20px !important; padding: 8px 10px !important; }
.leaflet-popup-close-button:hover { color: #38bdf8 !important; }

/* MarkerCluster custom styling for dark mode */
.marker-cluster-small {
    background-color: rgba(6, 182, 212, 0.35) !important;
}
.marker-cluster-small div {
    background-color: rgba(6, 182, 212, 0.85) !important;
    color: #ffffff !important;
    font-weight: bold !important;
}
.marker-cluster-medium {
    background-color: rgba(245, 158, 11, 0.35) !important;
}
.marker-cluster-medium div {
    background-color: rgba(245, 158, 11, 0.85) !important;
    color: #ffffff !important;
    font-weight: bold !important;
}
.marker-cluster-large {
    background-color: rgba(239, 68, 68, 0.35) !important;
}
.marker-cluster-large div {
    background-color: rgba(239, 68, 68, 0.85) !important;
    color: #ffffff !important;
    font-weight: bold !important;
}

/* Sidebar */
#map-sidebar {
    transition: transform 0.35s cubic-bezier(0.22,1,0.36,1);
}
#map-sidebar.collapsed { transform: translateX(100%); }

.map-filter-panel {
    background: #eff9fb;
    border-color: #d8edf1;
    color: #334155;
}
[data-theme="dark"] .map-filter-panel {
    background: #263343;
    border-color: #3a4a5d;
    color: #d7e1ec;
}
.map-filter-panel label { color: inherit; }
.map-filter-panel label:hover { color: var(--role-accent); }

.map-sidebar-header,
.map-sidebar-tabs {
    background: #e8f4f7;
}
.map-sidebar-footer {
    background: #e3f0f4;
}
[data-theme="dark"] .map-sidebar-header,
[data-theme="dark"] .map-sidebar-tabs {
    background: #293849;
}
[data-theme="dark"] .map-sidebar-footer {
    background: #243244;
}

.legend-dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }

.leaflet-control-zoom a {
    background: #0f172a !important;
    border-color: rgba(6, 182, 212, 0.3) !important;
    color: #38bdf8 !important;
}
.leaflet-control-zoom a:hover { background: #1e293b !important; color: #7dd3fc !important; }
.leaflet-control-attribution { display: none !important; }
</style>
@endpush

@section($contentSection ?? 'manager-content')
<div class="flex h-full flex-col bg-slate-950">

    {{-- ── TOP BAR ── --}}
    <div class="glass-strong border-b border-cyan-500/10 px-4 sm:px-6 py-3 flex flex-wrap gap-4 items-center justify-between">
        {{-- Left: breadcrumb + title --}}
        <div class="flex items-center gap-3">
            <a href="{{ route($dashboardRoute ?? 'manager.dashboard') }}"
               class="flex items-center gap-1 text-cyan-400 hover:text-cyan-300 transition-colors text-sm font-medium">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span class="hidden sm:inline">Dashboard</span>
            </a>
            <span class="text-white/20">/</span>
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-cyan-500/10 flex items-center justify-center">
                    <i data-lucide="map" class="w-4 h-4 text-cyan-400"></i>
                </div>
                <div>
                    <h1 class="text-white font-display font-bold text-base sm:text-lg">Carte interactive du réseau</h1>
                    <p class="text-[11px] text-cyan-200/50">Surveillance SIG temps réel · Zones & Équipements</p>
                </div>
            </div>
        </div>

        {{-- Center: KPI pills --}}
        <div class="flex items-center gap-2 flex-wrap">
            <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-cyan-500/10 border border-cyan-500/20">
                <span class="w-2 h-2 rounded-full bg-cyan-400"></span>
                <span class="text-cyan-300 text-xs font-semibold">{{ count($zones) }} Zones</span>
            </div>
            <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-emerald-500/10 border border-emerald-500/20">
                <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                <span class="text-emerald-300 text-xs font-semibold">{{ count($infrastructures) }} Équipements</span>
            </div>
            <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-indigo-500/10 border border-indigo-500/20">
                <span class="w-2 h-2 rounded-full bg-indigo-400"></span>
                <span class="text-indigo-300 text-xs font-semibold">{{ $measurementPoints->count() }} Points mesure</span>
            </div>
        </div>

        {{-- Right: actions --}}
        <div class="flex items-center gap-2">
            <button onclick="toggleSidebar()"
                    class="glass px-3 py-1.5 rounded-xl text-cyan-300 hover:text-white text-xs sm:text-sm font-medium flex items-center gap-2 transition-all hover:border-cyan-400/40">
                <i data-lucide="layers" class="w-4 h-4"></i>
                <span>Filtres & Couches</span>
            </button>
            <span class="glass px-3 py-2 rounded-xl text-teal-300 text-sm font-medium">
                {{ $measurementPoints->count() }} points de mesure
            </span>
            <button onclick="flyToTunisia()"
                    class="glass px-3 py-1.5 rounded-xl text-cyan-300 hover:text-white text-xs sm:text-sm font-medium flex items-center gap-2 transition-all hover:border-cyan-400/40">
                <i data-lucide="locate" class="w-4 h-4"></i>
                <span class="hidden sm:inline">Recadrer Tunisie</span>
            </button>
        </div>
    </div>

    {{-- ── MAP + SIDEBAR ── --}}
    <div class="flex-1 relative flex overflow-hidden" style="height: calc(100vh - 180px); min-height: 500px;">

        {{-- ── LEAFLET MAP ── --}}
        <div id="network-map" class="flex-1"></div>

        {{-- ── SIDEBAR (Filtres, couches, inventaire) ── --}}
        <aside id="map-sidebar"
               class="absolute top-0 right-0 bottom-0 z-20 w-84 sm:w-96 glass-strong border-l border-cyan-500/10 flex flex-col overflow-hidden"
               style="max-height: 100%;">

            {{-- Sidebar header --}}
            <div class="map-sidebar-header px-4 py-3 border-b border-white/5 flex items-center justify-between shrink-0">
                <div class="flex items-center gap-2">
                    <i data-lucide="sliders" class="w-4 h-4 text-cyan-400"></i>
                    <h2 class="text-white font-display font-bold text-sm">Gestion des couches & filtres</h2>
                </div>
                <button onclick="toggleSidebar()" class="text-cyan-400 hover:text-white transition-colors">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            {{-- Tab selector --}}
            <div class="map-sidebar-tabs px-4 py-2 border-b border-white/5 flex gap-2 shrink-0 text-xs">
                <button onclick="switchSidebarTab('filters')" id="tab-btn-filters" class="flex-1 rounded-lg py-1.5 font-semibold text-center bg-cyan-500/20 text-cyan-300 border border-cyan-400/30">
                    Filtres SIG
                </button>
                <button onclick="switchSidebarTab('zones')" id="tab-btn-zones" class="flex-1 rounded-lg py-1.5 font-semibold text-center text-cyan-100/60 hover:text-cyan-100">
                    Zones ({{ count($zones) }})
                </button>
                <button onclick="switchSidebarTab('infras')" id="tab-btn-infras" class="flex-1 rounded-lg py-1.5 font-semibold text-center text-cyan-100/60 hover:text-cyan-100">
                    Équipements ({{ count($infrastructures) }})
                </button>
            </div>

            {{-- Tab Content: FILTRES --}}
            <div id="tab-content-filters" class="flex-1 overflow-y-auto p-4 space-y-5">
                {{-- 1. Couches principales --}}
                <div class="space-y-2">
                    <p class="text-xs font-semibold text-white flex items-center gap-1.5 uppercase tracking-wider">
                        <i data-lucide="layers" class="w-3.5 h-3.5 text-cyan-400"></i>
                        Couches cartographiques
                    </p>
                    <div class="map-filter-panel space-y-2 rounded-xl p-3 border text-xs">
                        <label class="flex items-center gap-2.5 cursor-pointer">
                            <input type="checkbox" id="layer-zones" checked onchange="toggleLayer('zones', this.checked)"
                                   class="rounded border-cyan-400/30 bg-slate-900 text-cyan-500 focus:ring-0">
                            <span>Zones du réseau</span>
                            <span class="ml-auto text-[10px] rounded bg-cyan-500/20 px-1.5 py-0.5 text-cyan-300 font-mono">{{ count($zones) }}</span>
                        </label>
                        <label class="flex items-center gap-2.5 cursor-pointer">
                            <input type="checkbox" id="layer-infras" checked onchange="toggleLayer('infras', this.checked)"
                                   class="rounded border-cyan-400/30 bg-slate-900 text-cyan-500 focus:ring-0">
                            <span>Infrastructures & Équipements</span>
                            <span class="ml-auto text-[10px] rounded bg-emerald-500/20 px-1.5 py-0.5 text-emerald-300 font-mono">{{ count($infrastructures) }}</span>
                        </label>
                        <label class="flex items-center gap-2.5 cursor-pointer">
                            <input type="checkbox" id="layer-points" checked onchange="toggleLayer('points', this.checked)"
                                   class="rounded border-cyan-400/30 bg-slate-900 text-cyan-500 focus:ring-0">
                            <span>Points de contrôle de qualité</span>
                            <span class="ml-auto text-[10px] rounded bg-indigo-500/20 px-1.5 py-0.5 text-indigo-300 font-mono">{{ $measurementPoints->count() }}</span>
                        </label>
                    </div>
                </div>

                {{-- 2. Filtre par type d'équipement --}}
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-semibold text-white uppercase tracking-wider">Type d'équipement</p>
                        <button onclick="toggleAllTypes(true)" class="text-[10px] text-cyan-400 hover:underline">Tous</button>
                    </div>
                    <div class="map-filter-panel grid grid-cols-1 gap-1.5 rounded-xl p-3 border text-xs">
                        @foreach($infrastructureTypes as $type)
                            <label class="flex items-center gap-2 cursor-pointer hover:text-white transition">
                                <input type="checkbox" name="infra_type" value="{{ $type->value }}" checked onchange="applyInfrastructureFilters()"
                                       class="rounded border-cyan-400/30 bg-slate-900 text-cyan-500 focus:ring-0">
                                <span>{{ $type->label() }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- 3. Filtre par état opérationnel --}}
                <div class="space-y-2">
                    <p class="text-xs font-semibold text-white uppercase tracking-wider">État opérationnel</p>
                    <div class="map-filter-panel space-y-1.5 rounded-xl p-3 border text-xs">
                        @foreach($infrastructureEtats as $etat)
                            <label class="flex items-center gap-2.5 cursor-pointer hover:text-white transition">
                                <input type="checkbox" name="infra_etat" value="{{ $etat->value }}" checked onchange="applyInfrastructureFilters()"
                                       class="rounded border-cyan-400/30 bg-slate-900 text-cyan-500 focus:ring-0">
                                <span class="w-2 h-2 rounded-full {{ $etat->color() === 'green' ? 'bg-emerald-400' : ($etat->color() === 'amber' ? 'bg-amber-400' : 'bg-rose-400') }}"></span>
                                <span>{{ $etat->label() }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Tab Content: LISTE DES ZONES --}}
            <div id="tab-content-zones" class="hidden flex-1 overflow-y-auto p-3 space-y-2">
                <input id="zone-search" type="text" placeholder="Rechercher une zone…"
                       class="w-full bg-slate-950/40 border border-cyan-400/15 rounded-lg px-3 py-1.5 text-xs text-white placeholder:text-cyan-100/30 focus:outline-none focus:border-cyan-400/50 mb-2"
                       oninput="filterZoneList(this.value)">

                @foreach($zones as $zone)
                <div class="zone-item rounded-xl border border-white/5 bg-slate-950/50 p-3 hover:border-cyan-400/40 cursor-pointer transition"
                     data-name="{{ strtolower($zone['name']) }}"
                     onclick="focusZone('{{ $zone['id'] }}', {{ $zone['lat'] }}, {{ $zone['lng'] }})">
                    <div class="flex items-center justify-between">
                        <h3 class="font-semibold text-white text-xs">{{ $zone['name'] }}</h3>
                        <span class="text-[10px] rounded-full px-2 py-0.5 {{ ($zone['statut'] ?? 'active') === 'active' ? 'bg-emerald-500/20 text-emerald-300' : 'bg-slate-500/20 text-slate-300' }}">
                            {{ $zone['statut'] ?? 'active' }}
                        </span>
                    </div>
                    <p class="text-[11px] text-cyan-200/50 mt-0.5">{{ $zone['address'] }}</p>
                    <div class="mt-2 flex items-center justify-between text-[10px] text-cyan-100/70">
                        <span>Équipements : <strong class="text-white">{{ $zone['infrastructures_count'] }}</strong></span>
                        <a href="{{ $zone['show_url'] }}" class="text-cyan-400 hover:underline" onclick="event.stopPropagation()">Fiche →</a>
                    </div>
                </div>
                @endforeach
            </div>

            {{-- Tab Content: LISTE DES ÉQUIPEMENTS --}}
            <div id="tab-content-infras" class="hidden flex-1 overflow-y-auto p-3 space-y-2">
                <input id="infra-search" type="text" placeholder="Rechercher un équipement…"
                       class="w-full bg-slate-950/40 border border-cyan-400/15 rounded-lg px-3 py-1.5 text-xs text-white placeholder:text-cyan-100/30 focus:outline-none focus:border-cyan-400/50 mb-2"
                       oninput="filterInfraList(this.value)">

                @foreach($infrastructures as $infra)
                <div class="infra-item rounded-xl border border-white/5 bg-slate-950/50 p-3 hover:border-cyan-400/40 cursor-pointer transition"
                     data-name="{{ strtolower($infra['nom']) }}"
                     data-type="{{ $infra['type_key'] }}"
                     data-etat="{{ $infra['etat_key'] }}"
                     onclick="focusInfrastructure('{{ $infra['id'] }}', {{ $infra['lat'] }}, {{ $infra['lng'] }})">
                    <div class="flex items-center justify-between">
                        <h3 class="font-semibold text-white text-xs">{{ $infra['nom'] }}</h3>
                        <span class="text-[10px] rounded px-1.5 py-0.5 font-medium {{ $infra['etat_color'] === 'green' ? 'bg-emerald-500/20 text-emerald-300' : ($infra['etat_color'] === 'amber' ? 'bg-amber-500/20 text-amber-300' : 'bg-rose-500/20 text-rose-300') }}">
                            {{ $infra['etat_label'] }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between text-[11px] text-cyan-200/50 mt-1">
                        <span>{{ $infra['type_label'] }}</span>
                        <span>{{ $infra['zone_nom'] }}</span>
                    </div>
                    <div class="mt-2 flex items-center justify-between text-[10px] text-cyan-100/70 border-t border-white/5 pt-1.5">
                        <span>Santé : <strong class="text-cyan-300">{{ $infra['sante_score'] }}/100</strong></span>
                        <a href="{{ $infra['show_url'] }}" class="text-cyan-400 hover:underline" onclick="event.stopPropagation()">Consulter →</a>
                    </div>
                </div>
                @endforeach
            </div>

            {{-- Sidebar footer --}}
            <div class="map-sidebar-footer px-4 py-3 border-t border-white/5 shrink-0 text-center">
                <p class="text-[11px] text-cyan-100/60">AquaSecure SIG · Données géographiques certifiées</p>
            </div>
        </aside>
    </div>
</div>
@endsection

@push('scripts')
{{-- Leaflet JS & MarkerCluster JS --}}
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha384-cxOPjt7s7Iz04uaHJceBmS+qpjv2JkIHNVcuOrM+YHwZOmJGBXI00mdUXEq65HTH" crossorigin="anonymous"></script>
<script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js" integrity="sha384-eXVCORTRlv4FUUgS/xmOyr66XBVraen8ATNLMESp92FKXLAMiKkerixTiBvXriZr" crossorigin="anonymous"></script>

<script>
// ── Données injectées depuis PHP ─────────────────────────────────────────────
const ZONES = @json($zones);
const PIPELINES = @json($pipelines);
const MEASUREMENT_POINTS = @json($measurementPoints);
const INFRASTRUCTURES = @json($infrastructures);
const FOCUS_ZONE_ID = @json($focusZone);
const FOCUS_INFRA_ID = @json($focusInfrastructure);

// ── Initialisation de la carte ──────────────────────────────────────────────
const map = L.map('network-map', {
    center: [34.5, 9.8],
    zoom: 7,
    zoomControl: false,
    attributionControl: false,
});

L.control.zoom({ position: 'topleft' }).addTo(map);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '© OpenStreetMap',
}).addTo(map);

// ── Groupes de couches ───────────────────────────────────────────────────────
const zonesLayerGroup = L.featureGroup().addTo(map);
const infrasClusterGroup = L.markerClusterGroup({
    disableClusteringAtZoom: 15,
    maxClusterRadius: 45,
    spiderfyOnMaxZoom: true,
    showCoverageOnHover: false,
}).addTo(map);
const measurementLayerGroup = L.featureGroup().addTo(map);

const zoneMarkers = {};
const infraMarkers = {};

// ── 1. Création des marqueurs de Zones ───────────────────────────────────────
ZONES.forEach(zone => {
    if (!zone.lat || !zone.lng) return;

    const icon = L.divIcon({
        className: 'zone-marker',
        html: `
            <div style="background-color:#06b6d4;width:24px;height:24px;border-radius:50%;border:3px solid #ffffff;box-shadow:0 0 12px rgba(6,182,212,0.8);display:flex;align-items:center;justify-content:center;color:#0f172a;font-size:11px;font-weight:bold;">
                ⚑
            </div>`,
        iconSize: [24, 24],
        iconAnchor: [12, 12],
    });

    const marker = L.marker([zone.lat, zone.lng], { icon: icon });
    marker.bindPopup(buildZonePopup(zone));
    marker.addTo(zonesLayerGroup);
    zoneMarkers[zone.id] = marker;
});

function buildZonePopup(zone) {
    return `
    <div style="padding:14px;font-family:'Plus Jakarta Sans',sans-serif;color:#f8fafc;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
            <span style="font-size:10px;text-transform:uppercase;font-weight:700;letter-spacing:0.5px;color:#38bdf8;">Zone d'exploitation</span>
            <span style="font-size:10px;padding:2px 8px;border-radius:12px;background:rgba(6,182,212,0.2);color:#38bdf8;font-weight:600;">${zone.statut}</span>
        </div>
        <h3 style="margin:0 0 4px;font-size:15px;font-weight:700;color:#ffffff;">${escapeHtml(zone.name)}</h3>
        <p style="margin:0 0 10px;font-size:11px;color:#94a3b8;">${escapeHtml(zone.address)}</p>
        <div style="background:rgba(255,255,255,0.05);border-radius:8px;padding:8px;margin-bottom:10px;font-size:11px;display:grid;grid-template-columns:1fr 1fr;gap:6px;">
            <div><span style="color:#64748b;">Responsable:</span><br><strong style="color:#f1f5f9;">${escapeHtml(zone.responsable)}</strong></div>
            <div><span style="color:#64748b;">Équipements:</span><br><strong style="color:#f1f5f9;">${zone.infrastructures_count} unités</strong></div>
        </div>
        <div style="background:rgba(255,255,255,0.04);border-radius:8px;padding:8px;margin-bottom:10px;font-size:11px;display:grid;grid-template-columns:1fr 1fr;gap:6px;">
            <div><span style="color:#64748b;">Qualité:</span><br><strong style="color:#f1f5f9;">${escapeHtml(zone.quality)}%</strong></div>
            <div><span style="color:#64748b;">Pression:</span><br><strong style="color:#f1f5f9;">${escapeHtml(zone.pressure)} bar</strong></div>
            <div><span style="color:#64748b;">Débit:</span><br><strong style="color:#f1f5f9;">${escapeHtml(zone.flowRate)} m³/h</strong></div>
            <div><span style="color:#64748b;">Population:</span><br><strong style="color:#f1f5f9;">${escapeHtml(zone.population)}</strong></div>
            <div><span style="color:#64748b;">Incidents:</span><br><strong style="color:#f1f5f9;">${escapeHtml(zone.incidents)}</strong></div>
            <div><span style="color:#64748b;">Technicien:</span><br><strong style="color:#f1f5f9;">${escapeHtml(zone.technician)}</strong></div>
        </div>
        <div style="text-align:right;">
            <a href="${zone.show_url}" style="font-size:11px;font-weight:700;color:#38bdf8;text-decoration:none;">Ouvrir la fiche zone →</a>
        </div>
    </div>`;
}

// ── 2. Création des marqueurs d'Infrastructures ─────────────────────────────
INFRASTRUCTURES.forEach(infra => {
    if (!infra.lat || !infra.lng) return;

    const color = infra.etat_color === 'green' ? '#10b981' : (infra.etat_color === 'amber' ? '#f59e0b' : '#ef4444');

    const icon = L.divIcon({
        className: 'infra-marker',
        html: `
            <div style="background-color:${color};width:16px;height:16px;border-radius:50%;border:2px solid #ffffff;box-shadow:0 0 8px ${color};"></div>
        `,
        iconSize: [16, 16],
        iconAnchor: [8, 8],
    });

    const marker = L.marker([infra.lat, infra.lng], { icon: icon });
    marker.infraData = infra;
    marker.bindPopup(buildInfraPopup(infra));
    infrasClusterGroup.addLayer(marker);
    infraMarkers[infra.id] = marker;
});

function buildInfraPopup(infra) {
    const etatColor = infra.etat_color === 'green' ? '#10b981' : (infra.etat_color === 'amber' ? '#f59e0b' : '#ef4444');
    const critColor = infra.criticite_color === 'green' ? '#10b981' : (infra.criticite_color === 'amber' ? '#f59e0b' : '#ef4444');

    return `
    <div style="padding:14px;font-family:'Plus Jakarta Sans',sans-serif;color:#f8fafc;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px;">
            <span style="font-size:10px;text-transform:uppercase;font-weight:700;color:#38bdf8;">${escapeHtml(infra.type_label)}</span>
            <span style="font-size:10px;padding:2px 7px;border-radius:10px;background:${etatColor}25;color:${etatColor};font-weight:700;">${escapeHtml(infra.etat_label)}</span>
        </div>
        <h3 style="margin:0 0 2px;font-size:15px;font-weight:700;color:#ffffff;">${escapeHtml(infra.nom)}</h3>
        <p style="margin:0 0 10px;font-size:11px;color:#94a3b8;">Zone : <strong style="color:#e2e8f0;">${escapeHtml(infra.zone_nom)}</strong></p>

        <div style="background:rgba(255,255,255,0.05);border-radius:8px;padding:8px;margin-bottom:8px;font-size:11px;display:grid;grid-template-columns:1fr 1fr;gap:6px;">
            <div>
                <span style="color:#64748b;">Santé réseau:</span><br>
                <strong style="color:${infra.sante_color === 'green' ? '#10b981' : (infra.sante_color === 'amber' ? '#f59e0b' : '#ef4444')};">${infra.sante_score}/100 · ${escapeHtml(infra.sante_label)}</strong>
            </div>
            <div>
                <span style="color:#64748b;">Criticité:</span><br>
                <strong style="color:${critColor};">${escapeHtml(infra.criticite_label)}</strong>
            </div>
        </div>

        <div style="font-size:10px;color:#94a3b8;margin-bottom:10px;">
            Dernière maintenance : <strong style="color:#e2e8f0;">${escapeHtml(infra.date_derniere_maintenance)}</strong><br>
            Prochaine maintenance : <strong style="color:#e2e8f0;">${escapeHtml(infra.date_prochaine_maintenance)}</strong>
            ${infra.is_maintenance_retard ? '<br><span style="color:#ef4444;font-weight:700;">⚠️ Maintenance en retard !</span>' : ''}
        </div>

        <div style="text-align:right;">
            <a href="${infra.show_url}" style="font-size:11px;font-weight:700;color:#38bdf8;text-decoration:none;">Voir la fiche équipement →</a>
        </div>
    </div>`;
}

// ── 3. Création des marqueurs Points de mesure ──────────────────────────────
MEASUREMENT_POINTS.forEach(point => {
    if (!point.lat || !point.lng) return;

    const colors = { normal: '#10b981', alert: '#f59e0b', critical: '#ef4444' };
    const color = colors[point.status] ?? '#10b981';

    const icon = L.divIcon({
        className: 'measure-marker',
        html: `
            <div style="background-color:${color};width:12px;height:12px;transform:rotate(45deg);border:2px solid #ffffff;box-shadow:0 0 6px ${color};"></div>
        `,
        iconSize: [12, 12],
        iconAnchor: [6, 6],
    });

    const marker = L.marker([point.lat, point.lng], { icon: icon });
    marker.bindPopup(buildMeasurementPopup(point));
    marker.addTo(measurementLayerGroup);
});

function buildMeasurementPopup(point) {
    const colors = { normal: '#10b981', alert: '#f59e0b', critical: '#ef4444' };
    const color = colors[point.status] ?? '#10b981';
    const measure = point.measurement;

    return `
    <div style="padding:14px;font-family:'Plus Jakarta Sans',sans-serif;color:#f8fafc;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px;">
            <span style="font-size:10px;text-transform:uppercase;font-weight:700;color:#818cf8;">Point de prélèvement</span>
            <span style="font-size:10px;padding:2px 7px;border-radius:10px;background:${color}25;color:${color};font-weight:700;">${escapeHtml(point.measurement_status)}</span>
        </div>
        <h3 style="margin:0 0 2px;font-size:14px;font-weight:700;color:#ffffff;">${escapeHtml(point.name)}</h3>
        <p style="margin:0 0 8px;font-size:11px;color:#94a3b8;">${escapeHtml(point.code)} · ${escapeHtml(point.address || point.zone || '')}</p>

        ${measure ? `
            <div style="background:rgba(255,255,255,0.05);border-radius:8px;padding:8px;font-size:11px;color:#cbd5e1;line-height:1.5;">
                pH : <strong style="color:#fff;">${measure.ph}</strong> · Turbidité : <strong style="color:#fff;">${measure.turbidite} NTU</strong><br>
                Chlore résiduel : <strong style="color:#fff;">${measure.chlore_residuel} mg/L</strong><br>
                Plomb : <strong style="color:#fff;">${measure.plomb} ppb</strong> · Nitrates : <strong style="color:#fff;">${measure.nitrates} mg/L</strong>
            </div>
            <div style="margin-top:6px;font-size:10px;color:#64748b;">Mesure : ${escapeHtml(measure.reference)} (${escapeHtml(measure.date)})</div>
        ` : '<p style="font-size:11px;color:#94a3b8;">Aucun relevé disponible</p>'}
    </div>`;
}

function escapeHtml(val) {
    return String(val ?? '').replace(/[&<>"']/g, c => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
    })[c]);
}

// ── Gestion des filtres et couches ──────────────────────────────────────────
function toggleLayer(layerName, isVisible) {
    if (layerName === 'zones') {
        isVisible ? map.addLayer(zonesLayerGroup) : map.removeLayer(zonesLayerGroup);
    } else if (layerName === 'infras') {
        isVisible ? map.addLayer(infrasClusterGroup) : map.removeLayer(infrasClusterGroup);
    } else if (layerName === 'points') {
        isVisible ? map.addLayer(measurementLayerGroup) : map.removeLayer(measurementLayerGroup);
    }
}

function applyInfrastructureFilters() {
    const selectedTypes = Array.from(document.querySelectorAll('input[name="infra_type"]:checked')).map(el => el.value);
    const selectedEtats = Array.from(document.querySelectorAll('input[name="infra_etat"]:checked')).map(el => el.value);

    infrasClusterGroup.clearLayers();

    Object.values(infraMarkers).forEach(marker => {
        const d = marker.infraData;
        const matchType = selectedTypes.includes(d.type_key);
        const matchEtat = selectedEtats.includes(d.etat_key);

        if (matchType && matchEtat) {
            infrasClusterGroup.addLayer(marker);
        }
    });
}

// ── Pipeline polylines ────────────────────────────────────────────────────────
const pipeColors = { normal: '#2dd4bf', alert: '#fbbf24', critical: '#ef4444' };

PIPELINES.forEach(pipe => {
    const from = zoneById[pipe.from];
    const to   = zoneById[pipe.to];
    if (!from || !to) return;

    L.polyline(
        [[from.lat, from.lng], [to.lat, to.lng]],
        {
            color: pipeColors[pipe.status] ?? '#2dd4bf',
            weight: pipe.status === 'critical' ? 3 : 2,
            opacity: 0.55,
            dashArray: pipe.status === 'normal' ? null : '6,4',
        }
    ).addTo(map).bindTooltip(
        `<span style="font-size:11px;color:#f0fdff">
            ${from.name} → ${to.name}<br>
            Ø${pipe.diameter}mm · ${pipe.pressure} bar · ${pipe.flow} m³/h
        </span>`,
        { sticky: true, className: '' }
    );
});

function toggleAllTypes(checkAll) {
    document.querySelectorAll('input[name="infra_type"]').forEach(cb => cb.checked = checkAll);
    applyInfrastructureFilters();
}

// ── Navigation et focus automatique via Query Params ────────────────────────
function flyToTunisia() {
    map.flyTo([34.5, 9.8], 7, { duration: 1.2 });
}

function focusZone(id, lat, lng) {
    map.flyTo([lat, lng], 13, { duration: 1 });
    if (zoneMarkers[id]) {
        setTimeout(() => zoneMarkers[id].openPopup(), 1100);
    }
}

function focusInfrastructure(id, lat, lng) {
    map.flyTo([lat, lng], 16, { duration: 1 });
    if (infraMarkers[id]) {
        setTimeout(() => infraMarkers[id].openPopup(), 1100);
    }
}

// Sidebar handling
let sidebarOpen = true;
function toggleSidebar() {
    sidebarOpen = !sidebarOpen;
    const sidebar = document.getElementById('map-sidebar');
    sidebarOpen ? sidebar.classList.remove('collapsed') : sidebar.classList.add('collapsed');
    setTimeout(() => map.invalidateSize(), 360);
}

function switchSidebarTab(tab) {
    document.getElementById('tab-content-filters').classList.toggle('hidden', tab !== 'filters');
    document.getElementById('tab-content-zones').classList.toggle('hidden', tab !== 'zones');
    document.getElementById('tab-content-infras').classList.toggle('hidden', tab !== 'infras');

    document.querySelectorAll('[id^="tab-btn-"]').forEach(btn => {
        btn.className = 'flex-1 rounded-lg py-1.5 font-semibold text-center text-cyan-100/60 hover:text-cyan-100';
    });

    const activeBtn = document.getElementById('tab-btn-' + tab);
    if (activeBtn) {
        activeBtn.className = 'flex-1 rounded-lg py-1.5 font-semibold text-center bg-cyan-500/20 text-cyan-300 border border-cyan-400/30';
    }
}

function filterZoneList(q) {
    q = (q || '').toLowerCase();
    document.querySelectorAll('.zone-item').forEach(el => {
        el.style.display = !q || el.dataset.name.includes(q) ? '' : 'none';
    });
}

function filterInfraList(q) {
    q = (q || '').toLowerCase();
    document.querySelectorAll('.infra-item').forEach(el => {
        el.style.display = !q || el.dataset.name.includes(q) ? '' : 'none';
    });
}

// Exécution du focus query param au chargement
document.addEventListener('DOMContentLoaded', () => {
    if (typeof lucide !== 'undefined') lucide.createIcons();

    if (FOCUS_INFRA_ID && infraMarkers[FOCUS_INFRA_ID]) {
        const marker = infraMarkers[FOCUS_INFRA_ID];
        map.flyTo(marker.getLatLng(), 16, { duration: 1 });
        setTimeout(() => marker.openPopup(), 1100);
    } else if (FOCUS_ZONE_ID && zoneMarkers[FOCUS_ZONE_ID]) {
        const marker = zoneMarkers[FOCUS_ZONE_ID];
        map.flyTo(marker.getLatLng(), 13, { duration: 1 });
        setTimeout(() => marker.openPopup(), 1100);
    }
});
</script>
@endpush
