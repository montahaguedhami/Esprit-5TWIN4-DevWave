<?php $__env->startSection('title', 'Carte Réseau — AquaSecure'); ?>

<?php
    use App\Data\PlaceholderData;
    $zones     = PlaceholderData::mapZones();
    $pipelines = PlaceholderData::mapPipelines();
    $stats     = PlaceholderData::stats();
    $user      = session('user', ['name' => 'Gestionnaire', 'role' => 'manager']);

    $normalCount   = count(array_filter($zones, fn($z) => $z['status'] === 'normal'));
    $alertCount    = count(array_filter($zones, fn($z) => $z['status'] === 'alert'));
    $criticalCount = count(array_filter($zones, fn($z) => $z['status'] === 'critical'));
?>

<?php $__env->startPush('styles'); ?>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
      integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<style>
/* ── Map container ── */
#network-map {
    height: 100%;
    width: 100%;
    border-radius: 0;
    background: #061525;
    z-index: 1;
}

/* Force dark Leaflet tiles */
.leaflet-tile-pane { filter: brightness(0.72) saturate(0.65) hue-rotate(185deg); }

/* Custom popup */
.leaflet-popup-content-wrapper {
    background: rgba(6,21,37,0.96) !important;
    border: 1px solid rgba(5,191,219,0.3) !important;
    border-radius: 14px !important;
    box-shadow: 0 12px 40px rgba(0,0,0,0.5) !important;
    backdrop-filter: blur(20px);
    color: #f0fdff !important;
    padding: 0 !important;
}
.leaflet-popup-tip { background: rgba(6,21,37,0.96) !important; }
.leaflet-popup-content { margin: 0 !important; padding: 0 !important; width: auto !important; min-width: 240px; }
.leaflet-popup-close-button { color: rgba(156,200,216,0.7) !important; font-size: 20px !important; padding: 8px 10px !important; }
.leaflet-popup-close-button:hover { color: #fff !important; }

/* Marker pulse ring */
@keyframes markerPulse {
    0%   { transform: scale(1);   opacity: 0.8; }
    70%  { transform: scale(2.4); opacity: 0;   }
    100% { transform: scale(2.4); opacity: 0;   }
}
.pulse-ring {
    position: absolute;
    top: 50%; left: 50%;
    width: 100%; height: 100%;
    border-radius: 50%;
    transform: translate(-50%, -50%) scale(1);
    animation: markerPulse 2s infinite;
}

/* Sidebar */
#map-sidebar {
    transition: transform 0.35s cubic-bezier(0.22,1,0.36,1);
}
#map-sidebar.collapsed { transform: translateX(100%); }

/* Legend */
.legend-dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }

/* Zoom controls */
.leaflet-control-zoom a {
    background: rgba(6,21,37,0.9) !important;
    border-color: rgba(5,191,219,0.25) !important;
    color: #7ce8f7 !important;
}
.leaflet-control-zoom a:hover { background: rgba(5,191,219,0.15) !important; color: #fff !important; }
.leaflet-control-attribution { display: none !important; }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="min-h-screen flex flex-col" style="background: #061525;">

    
    <div class="glass-strong border-b border-cyan-500/10 px-4 sm:px-6 py-4 flex flex-wrap gap-4 items-center justify-between">
        
        <div class="flex items-center gap-3">
            <a href="<?php echo e(route('manager.dashboard')); ?>"
               class="flex items-center gap-1 text-cyan-400 hover:text-cyan-300 transition-colors text-sm font-medium">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span class="hidden sm:inline">Dashboard</span>
            </a>
            <span class="text-white/20">/</span>
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-cyan-500/10 flex items-center justify-center">
                    <i data-lucide="map" class="w-4 h-4 text-cyan-400"></i>
                </div>
                <h1 class="text-white font-display font-bold text-lg">Carte du réseau</h1>
            </div>
        </div>

        
        <div class="flex items-center gap-2 flex-wrap">
            <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-teal-500/10 border border-teal-500/20">
                <span class="legend-dot bg-teal-400"></span>
                <span class="text-teal-300 text-xs font-semibold"><?php echo e($normalCount); ?> Normal</span>
            </div>
            <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-amber-500/10 border border-amber-500/20">
                <span class="legend-dot bg-amber-400"></span>
                <span class="text-amber-300 text-xs font-semibold"><?php echo e($alertCount); ?> Alerte</span>
            </div>
            <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-red-500/10 border border-red-500/20">
                <span class="legend-dot bg-red-400"></span>
                <span class="text-red-300 text-xs font-semibold"><?php echo e($criticalCount); ?> Critique</span>
            </div>
        </div>

        
        <div class="flex items-center gap-2">
            <button onclick="toggleSidebar()"
                    class="glass px-3 py-2 rounded-xl text-cyan-300 hover:text-white text-sm font-medium flex items-center gap-2 transition-all hover:border-cyan-400/40">
                <i data-lucide="layers" class="w-4 h-4"></i>
                <span class="hidden sm:inline">Zones</span>
            </button>
            <button onclick="flyToTunisia()"
                    class="glass px-3 py-2 rounded-xl text-cyan-300 hover:text-white text-sm font-medium flex items-center gap-2 transition-all hover:border-cyan-400/40">
                <i data-lucide="locate" class="w-4 h-4"></i>
                <span class="hidden sm:inline">Recadrer</span>
            </button>
            <button onclick="window.print()"
                    class="glass px-3 py-2 rounded-xl text-cyan-300 hover:text-white text-sm font-medium flex items-center gap-2 transition-all hover:border-cyan-400/40">
                <i data-lucide="download" class="w-4 h-4"></i>
                <span class="hidden sm:inline">Export</span>
            </button>
        </div>
    </div>

    
    <div class="flex-1 relative flex overflow-hidden" style="height: calc(100vh - 130px); min-height: 500px;">

        
        <div id="network-map" class="flex-1"></div>

        
        <aside id="map-sidebar"
               class="absolute top-0 right-0 bottom-0 z-20 w-80 glass-strong border-l border-cyan-500/10 flex flex-col overflow-hidden"
               style="max-height: 100%;">

            
            <div class="px-4 py-3 border-b border-white/5 flex items-center justify-between shrink-0">
                <h2 class="text-white font-display font-bold text-sm">Zones du réseau</h2>
                <button onclick="toggleSidebar()" class="text-cyan-400 hover:text-white transition-colors">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            
            <div class="px-4 py-3 border-b border-white/5 shrink-0">
                <div class="relative">
                    <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-cyan-400/50"></i>
                    <input id="zone-search" type="text" placeholder="Rechercher une zone…"
                           class="w-full bg-slate-950/40 border border-cyan-400/15 rounded-lg pl-9 pr-3 py-2 text-sm text-white placeholder:text-cyan-100/30 focus:outline-none focus:border-cyan-400/50"
                           oninput="filterZones(this.value)">
                </div>
            </div>

            
            <div class="px-4 py-2 flex gap-1 border-b border-white/5 shrink-0">
                <?php $__currentLoopData = ['all' => 'Toutes', 'normal' => 'Normal', 'alert' => 'Alerte', 'critical' => 'Critique']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val => $lbl): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <button onclick="setFilter('<?php echo e($val); ?>')"
                        data-filter="<?php echo e($val); ?>"
                        class="filter-btn px-3 py-1 rounded-lg text-xs font-semibold transition-all <?php echo e($val === 'all' ? 'bg-cyan-500/15 text-cyan-300 border border-cyan-400/30' : 'text-cyan-100/50 hover:text-cyan-100/80'); ?>">
                    <?php echo e($lbl); ?>

                </button>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>

            
            <div id="zone-list" class="flex-1 overflow-y-auto p-3 space-y-2">
                <?php $__currentLoopData = $zones; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $zone): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $statusColor = match($zone['status']) {
                        'critical' => ['bg' => 'bg-red-500/10',    'border' => 'border-red-500/20',    'dot' => 'bg-red-400',    'text' => 'text-red-300'],
                        'alert'    => ['bg' => 'bg-amber-500/10',  'border' => 'border-amber-500/20',  'dot' => 'bg-amber-400',  'text' => 'text-amber-300'],
                        default    => ['bg' => 'bg-teal-500/10',   'border' => 'border-teal-500/20',   'dot' => 'bg-teal-400',   'text' => 'text-teal-300'],
                    };
                ?>
                <div class="zone-card glass <?php echo e($statusColor['bg']); ?> <?php echo e($statusColor['border']); ?> rounded-xl p-3 cursor-pointer hover:border-cyan-400/40 transition-all group"
                     data-status="<?php echo e($zone['status']); ?>"
                     data-name="<?php echo e(strtolower($zone['name'])); ?>"
                     onclick="focusZone('<?php echo e($zone['id']); ?>', <?php echo e($zone['lat']); ?>, <?php echo e($zone['lng']); ?>)">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center gap-2">
                            <span class="text-lg"><?php echo e($zone['emoji']); ?></span>
                            <div>
                                <p class="text-white text-xs font-semibold group-hover:text-cyan-300 transition-colors"><?php echo e($zone['name']); ?></p>
                                <p class="text-cyan-100/40 text-[10px]"><?php echo e($zone['address']); ?></p>
                            </div>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full <?php echo e($statusColor['dot']); ?>

                                <?php echo e($zone['status'] !== 'normal' ? 'animate-pulse' : ''); ?>"></span>
                            <span class="text-[10px] font-semibold <?php echo e($statusColor['text']); ?> capitalize">
                                <?php echo e($zone['status'] === 'normal' ? 'OK' : ($zone['status'] === 'alert' ? 'Alerte' : 'Critique')); ?>

                            </span>
                        </div>
                    </div>
                    <div class="grid grid-cols-3 gap-2 text-center">
                        <div>
                            <p class="text-[10px] text-cyan-100/40">Qualité</p>
                            <p class="text-xs font-bold <?php echo e($zone['quality'] >= 90 ? 'text-teal-400' : ($zone['quality'] >= 80 ? 'text-amber-400' : 'text-red-400')); ?>">
                                <?php echo e($zone['quality']); ?>%
                            </p>
                        </div>
                        <div>
                            <p class="text-[10px] text-cyan-100/40">Pression</p>
                            <p class="text-xs font-bold text-white"><?php echo e($zone['pressure']); ?> bar</p>
                        </div>
                        <div>
                            <p class="text-[10px] text-cyan-100/40">Incidents</p>
                            <p class="text-xs font-bold <?php echo e($zone['incidents'] > 0 ? 'text-red-400' : 'text-teal-400'); ?>">
                                <?php echo e($zone['incidents']); ?>

                            </p>
                        </div>
                    </div>
                    
                    <div class="mt-2 h-1 bg-slate-950/50 rounded-full overflow-hidden">
                        <div class="h-full rounded-full transition-all"
                             style="width:<?php echo e($zone['quality']); ?>%; background: <?php echo e($zone['color']); ?>"></div>
                    </div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>

            
            <div class="px-4 py-3 border-t border-white/5 shrink-0 grid grid-cols-3 gap-3 text-center">
                <div>
                    <p class="text-[10px] text-cyan-100/40 mb-0.5">Capteurs</p>
                    <p class="text-sm font-bold text-white"><?php echo e(array_sum(array_column($zones, 'sensors'))); ?></p>
                </div>
                <div>
                    <p class="text-[10px] text-cyan-100/40 mb-0.5">Incidents</p>
                    <p class="text-sm font-bold text-red-400"><?php echo e(array_sum(array_column($zones, 'incidents'))); ?></p>
                </div>
                <div>
                    <p class="text-[10px] text-cyan-100/40 mb-0.5">Qualité moy.</p>
                    <p class="text-sm font-bold text-teal-400">
                        <?php echo e(round(array_sum(array_column($zones, 'quality')) / count($zones))); ?>%
                    </p>
                </div>
            </div>
        </aside>

        
        <div class="absolute bottom-0 left-0 right-0 z-10 glass-strong border-t border-cyan-500/10 px-4 py-2 flex items-center gap-4 overflow-x-auto">
            <div class="flex items-center gap-1.5 shrink-0">
                <span class="w-2 h-2 rounded-full bg-teal-400 animate-pulse"></span>
                <span class="text-teal-300 text-xs font-semibold">Temps réel</span>
            </div>
            <div class="flex gap-6 text-xs text-cyan-100/60">
                <span>Dernière sync: <strong class="text-white"><?php echo e(now()->format('H:i:s')); ?></strong></span>
                <span class="hidden sm:inline">Débit total: <strong class="text-cyan-300">38 200 m³/j</strong></span>
                <span class="hidden sm:inline">Capteurs actifs: <strong class="text-teal-300"><?php echo e(array_sum(array_column($zones, 'sensors'))); ?>/<?php echo e(array_sum(array_column($zones, 'sensors'))); ?></strong></span>
                <span class="hidden md:inline">Pression moy: <strong class="text-cyan-300">3.42 bar</strong></span>
                <span class="hidden md:inline">Qualité moy: <strong class="text-teal-300"><?php echo e(round(array_sum(array_column($zones, 'quality')) / count($zones))); ?>%</strong></span>
            </div>
            <div class="ml-auto shrink-0">
                <a href="<?php echo e(route('manager.analytics')); ?>"
                   class="text-xs text-cyan-400 hover:text-cyan-300 font-semibold flex items-center gap-1 transition-colors">
                    <i data-lucide="bar-chart-2" class="w-3.5 h-3.5"></i>
                    Voir Analytics
                </a>
            </div>
        </div>
    </div>
</div>


<div id="zone-detail-modal"
     class="hidden fixed inset-0 z-[200] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
     onclick="if(event.target===this) closeZoneDetail()">
    <div class="glass-strong rounded-2xl w-full max-w-md overflow-hidden animate-scale-in" onclick="event.stopPropagation()">
        
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV/XN/WLI=" crossorigin=""></script>

<script>
// ── Zone data from PHP ──────────────────────────────────────────────────────
const ZONES = <?php echo json_encode($zones, 15, 512) ?>;
const PIPELINES = <?php echo json_encode($pipelines, 15, 512) ?>;

// Build a lookup id → zone
const zoneById = {};
ZONES.forEach(z => { zoneById[z.id] = z; });

// ── Map init ────────────────────────────────────────────────────────────────
const map = L.map('network-map', {
    center: [34.0, 9.5],
    zoom: 6,
    zoomControl: false,
    attributionControl: false,
});

// Zoom control top-right
L.control.zoom({ position: 'topleft' }).addTo(map);

// OpenStreetMap tiles (standard)
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 18,
    attribution: '© OpenStreetMap',
}).addTo(map);

// ── Custom Marker icon factory ───────────────────────────────────────────────
function makeIcon(status, incidents) {
    const colors = { normal: '#2dd4bf', alert: '#fbbf24', critical: '#ef4444' };
    const color  = colors[status] ?? '#2dd4bf';
    const pulse  = status !== 'normal';
    const size   = status === 'critical' ? 36 : 30;
    const half   = size / 2;

    const svg = `
        <svg xmlns="http://www.w3.org/2000/svg" width="${size}" height="${size}" viewBox="0 0 ${size} ${size}">
            ${pulse ? `<circle cx="${half}" cy="${half}" r="${half}" fill="${color}" opacity="0.25">
                <animate attributeName="r" from="${half}" to="${size}" dur="1.8s" repeatCount="indefinite" />
                <animate attributeName="opacity" from="0.4" to="0" dur="1.8s" repeatCount="indefinite" />
            </circle>` : ''}
            <circle cx="${half}" cy="${half}" r="${half - 3}" fill="${color}" fill-opacity="0.2" stroke="${color}" stroke-width="2"/>
            <circle cx="${half}" cy="${half}" r="${half - 8}" fill="${color}" fill-opacity="0.85"/>
            ${incidents > 0 ? `<text x="${half}" y="${half + 4}" text-anchor="middle" font-size="10" font-weight="bold" fill="#fff">${incidents}</text>` : `<circle cx="${half}" cy="${half}" r="3" fill="white" opacity="0.9"/>`}
        </svg>`;

    return L.divIcon({
        html: svg,
        iconSize: [size, size],
        iconAnchor: [half, half],
        popupAnchor: [0, -(half + 2)],
        className: '',
    });
}

// ── Add zone markers ─────────────────────────────────────────────────────────
const markerMap = {};

ZONES.forEach(zone => {
    const marker = L.marker([zone.lat, zone.lng], { icon: makeIcon(zone.status, zone.incidents) })
        .addTo(map);

    marker.bindPopup(buildPopup(zone), { maxWidth: 280, minWidth: 240 });
    marker.on('click', () => marker.openPopup());
    markerMap[zone.id] = marker;
});

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

// ── Popup HTML builder ────────────────────────────────────────────────────────
function buildPopup(z) {
    const statusLabel = { normal: 'Normal', alert: 'Alerte', critical: 'Critique' };
    const statusColor = { normal: '#2dd4bf', alert: '#fbbf24', critical: '#ef4444' };
    const qColor = z.quality >= 90 ? '#2dd4bf' : z.quality >= 80 ? '#fbbf24' : '#ef4444';

    return `
    <div style="padding:16px;font-family:'Plus Jakarta Sans',sans-serif;">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:14px;">
            <span style="font-size:28px;line-height:1">${z.emoji}</span>
            <div>
                <h3 style="margin:0;color:#f0fdff;font-size:15px;font-weight:700;letter-spacing:.3px">${z.name}</h3>
                <p style="margin:2px 0 0;color:rgba(156,200,216,.7);font-size:11px">${z.address}</p>
            </div>
            <span style="margin-left:auto;padding:3px 9px;border-radius:20px;font-size:10px;font-weight:700;
                         background:${statusColor[z.status]}20;border:1px solid ${statusColor[z.status]}50;
                         color:${statusColor[z.status]}">
                ${statusLabel[z.status]}
            </span>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:12px;">
            ${kpiBox('💧','Qualité',z.quality+'%',qColor)}
            ${kpiBox('⚡','Pression',z.pressure+' bar','#38bdf8')}
            ${kpiBox('📊','Débit',z.flowRate+' m³/h','#818cf8')}
            ${kpiBox('👥','Population',fmtNum(z.population),'#a78bfa')}
            ${kpiBox('🔍','Capteurs',z.sensors,'#2dd4bf')}
            ${kpiBox('⚠️','Incidents',z.incidents, z.incidents>0?'#ef4444':'#2dd4bf')}
        </div>

        <div style="background:rgba(255,255,255,.04);border-radius:8px;padding:8px 10px;margin-bottom:12px;">
            <div style="display:flex;justify-content:space-between;margin-bottom:6px;">
                <span style="font-size:10px;color:rgba(156,200,216,.6)">Qualité eau</span>
                <span style="font-size:10px;font-weight:700;color:${qColor}">${z.quality}%</span>
            </div>
            <div style="height:5px;background:rgba(2,20,35,.7);border-radius:4px;overflow:hidden">
                <div style="height:100%;width:${z.quality}%;background:${statusColor[z.status]};border-radius:4px;transition:width .6s"></div>
            </div>
        </div>

        <div style="display:flex;justify-content:space-between;align-items:center;">
            <span style="font-size:10px;color:rgba(156,200,216,.5)">
                ⏱ Mis à jour il y a ${z.lastUpdate}
            </span>
            <span style="font-size:10px;color:rgba(156,200,216,.6)">
                🔧 ${z.technician}
            </span>
        </div>
    </div>`;
}

function kpiBox(icon, label, value, color) {
    return `<div style="background:rgba(255,255,255,.04);border-radius:8px;padding:7px 9px;text-align:center;">
        <p style="margin:0 0 2px;font-size:11px;color:rgba(156,200,216,.55)">${icon} ${label}</p>
        <p style="margin:0;font-size:13px;font-weight:700;color:${color}">${value}</p>
    </div>`;
}

function fmtNum(n) { return n.toLocaleString('fr-FR'); }

// ── Sidebar & controls ────────────────────────────────────────────────────────
let sidebarOpen = true;

function toggleSidebar() {
    sidebarOpen = !sidebarOpen;
    const sidebar = document.getElementById('map-sidebar');
    sidebarOpen ? sidebar.classList.remove('collapsed') : sidebar.classList.add('collapsed');
    setTimeout(() => map.invalidateSize(), 360);
}

function flyToTunisia() {
    map.flyTo([34.0, 9.5], 6, { duration: 1.4 });
}

function focusZone(id, lat, lng) {
    map.flyTo([lat, lng], 10, { duration: 1 });
    if (markerMap[id]) markerMap[id].openPopup();
}

// Zone list filter + search
let activeFilter = 'all';

function setFilter(val) {
    activeFilter = val;
    document.querySelectorAll('.filter-btn').forEach(btn => {
        const active = btn.dataset.filter === val;
        btn.className = 'filter-btn px-3 py-1 rounded-lg text-xs font-semibold transition-all '
            + (active ? 'bg-cyan-500/15 text-cyan-300 border border-cyan-400/30' : 'text-cyan-100/50 hover:text-cyan-100/80');
    });
    applyFilter();
}

function filterZones(q) { applyFilter(q); }

function applyFilter(search) {
    const q = (search ?? document.getElementById('zone-search').value ?? '').toLowerCase();
    document.querySelectorAll('.zone-card').forEach(card => {
        const matchStatus = activeFilter === 'all' || card.dataset.status === activeFilter;
        const matchSearch = !q || card.dataset.name.includes(q);
        card.style.display = matchStatus && matchSearch ? '' : 'none';
    });
}

// ── Live clock update ─────────────────────────────────────────────────────────
// Already static; for real-time demo just tick seconds
(function tick() {
    setTimeout(tick, 30000); // refresh page hint every 30 s
})();

// ── Init Lucide after DOMContentLoaded ────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    if (typeof lucide !== 'undefined') lucide.createIcons();
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.manager', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\ghada\Desktop\5ème\projet_Laravel\aquasecure\resources\views/manager/map.blade.php ENDPATH**/ ?>