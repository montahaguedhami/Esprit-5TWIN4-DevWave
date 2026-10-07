<?php $__env->startSection('title', 'Statistiques détaillées — AquaSecure'); ?>

<?php
    use App\Data\PlaceholderData;
    $kpis        = PlaceholderData::analyticsKPIs();
    $monthly     = PlaceholderData::analyticsMonthly();
    $incTypes    = PlaceholderData::analyticsIncidentTypes();
    $teams       = PlaceholderData::analyticsTeamPerformance();
    $weekly      = PlaceholderData::analyticsWeeklyByZone();
    $zones       = PlaceholderData::mapZones();
    $user        = session('user', ['name' => 'Gestionnaire', 'role' => 'manager']);
?>

<?php $__env->startPush('styles'); ?>
<style>
.chart-container { position: relative; }
.kpi-up   { color: #2dd4bf; }
.kpi-down { color: #fb7185; }

/* Donut chart */
.donut-ring { transition: stroke-dasharray .8s cubic-bezier(.22,1,.36,1); }

/* Animated bars */
@keyframes barGrow {
    from { transform: scaleY(0); transform-origin: bottom; }
    to   { transform: scaleY(1); transform-origin: bottom; }
}
.bar-animate { animation: barGrow .7s cubic-bezier(.22,1,.36,1) both; }

/* Tooltip */
.chart-tooltip {
    position: absolute;
    background: rgba(6,21,37,0.96);
    border: 1px solid rgba(5,191,219,0.3);
    border-radius: 10px;
    padding: 8px 12px;
    font-size: 11px;
    color: #f0fdff;
    pointer-events: none;
    white-space: nowrap;
    z-index: 99;
    backdrop-filter: blur(12px);
    transform: translateX(-50%);
    box-shadow: 0 8px 24px rgba(0,0,0,.4);
}
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="container mx-auto px-4 sm:px-6 py-8 max-w-7xl">

    
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
        <div class="flex items-center gap-3">
            <a href="<?php echo e(route('manager.dashboard')); ?>"
               class="glass p-2 rounded-xl text-cyan-400 hover:text-white transition-colors">
                <i data-lucide="arrow-left" class="w-5 h-5"></i>
            </a>
            <div>
                <h1 class="text-2xl sm:text-3xl font-display font-bold text-white">Statistiques détaillées</h1>
                <p class="text-cyan-100/60 text-sm mt-0.5">Période : Octobre 2025 – Septembre 2026</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            
            <select onchange="showToast('Période mise à jour', 'info')"
                    class="glass px-4 py-2 rounded-xl text-sm text-cyan-200 border border-cyan-400/20 bg-transparent focus:outline-none focus:border-cyan-400/50 cursor-pointer">
                <option>12 derniers mois</option>
                <option>6 derniers mois</option>
                <option>30 derniers jours</option>
                <option>7 derniers jours</option>
            </select>
            <button onclick="window.print()"
                    class="glass px-4 py-2 rounded-xl text-cyan-300 hover:text-white text-sm font-semibold flex items-center gap-2 transition-all hover:border-cyan-400/40">
                <i data-lucide="download" class="w-4 h-4"></i>
                <span class="hidden sm:inline">Exporter PDF</span>
            </button>
        </div>
    </div>

    
    <div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4 mb-8">
        <?php $__currentLoopData = $kpis; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $kpi): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php
            $up      = $kpi['change'] > 0;
            $isGood  = in_array($kpi['icon'], ['check-circle','gauge']) ? $up : !$up;
            $trendCl = $isGood ? 'text-teal-400' : 'text-red-400';
            $bgCl    = "bg-{$kpi['color']}-500/10";
            $iconCl  = "text-{$kpi['color']}-400";
        ?>
        <div class="glass rounded-2xl p-4 hover-lift">
            <div class="flex items-center justify-between mb-3">
                <div class="w-9 h-9 rounded-xl <?php echo e($bgCl); ?> flex items-center justify-center">
                    <i data-lucide="<?php echo e($kpi['icon']); ?>" class="w-4 h-4 <?php echo e($iconCl); ?>"></i>
                </div>
                <span class="text-xs font-semibold <?php echo e($trendCl); ?> flex items-center gap-0.5">
                    <i data-lucide="<?php echo e($up ? 'trending-up' : 'trending-down'); ?>" class="w-3 h-3"></i>
                    <?php echo e(abs($kpi['change'])); ?>%
                </span>
            </div>
            <p class="text-lg font-display font-bold text-white leading-tight"><?php echo e($kpi['value']); ?></p>
            <p class="text-[11px] text-cyan-100/50 mt-1 leading-tight"><?php echo e($kpi['label']); ?></p>
            <p class="text-[10px] text-cyan-100/30 mt-0.5">vs <?php echo e($kpi['prev']); ?></p>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    
    <div class="grid lg:grid-cols-2 gap-6 mb-6">

        
        <div class="glass rounded-2xl p-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-white font-display font-bold text-base">Consommation mensuelle</h2>
                    <p class="text-cyan-100/50 text-xs mt-0.5">m³ / mois sur 12 mois</p>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="w-3 h-3 rounded-full bg-cyan-400"></span>
                    <span class="text-xs text-cyan-100/60">m³</span>
                </div>
            </div>
            <div class="chart-container" style="height:200px">
                <svg id="consumption-chart" width="100%" height="200" viewBox="0 0 600 200"
                     preserveAspectRatio="none" class="overflow-visible">
                </svg>
            </div>
            <div id="consumption-labels" class="flex justify-between mt-2 px-1"></div>
        </div>

        
        <div class="glass rounded-2xl p-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-white font-display font-bold text-base">Incidents & résolutions</h2>
                    <p class="text-cyan-100/50 text-xs mt-0.5">Comparaison mensuelle</p>
                </div>
                <div class="flex items-center gap-3 text-xs text-cyan-100/60">
                    <span class="flex items-center gap-1"><span class="w-3 h-2 rounded-sm inline-block bg-red-400/70"></span> Incidents</span>
                    <span class="flex items-center gap-1"><span class="w-3 h-2 rounded-sm inline-block bg-teal-400/70"></span> Résolus</span>
                </div>
            </div>
            <div class="chart-container" style="height:200px">
                <svg id="incidents-chart" width="100%" height="200" viewBox="0 0 600 200"
                     preserveAspectRatio="none" class="overflow-visible">
                </svg>
            </div>
            <div id="incidents-labels" class="flex justify-between mt-2 px-1"></div>
        </div>
    </div>

    
    <div class="grid lg:grid-cols-3 gap-6 mb-6">

        
        <div class="glass rounded-2xl p-6 lg:col-span-2">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-white font-display font-bold text-base">Qualité de l'eau</h2>
                    <p class="text-cyan-100/50 text-xs mt-0.5">Score moyen par mois (%)</p>
                </div>
                <div class="glass px-3 py-1 rounded-lg">
                    <span class="text-teal-300 text-sm font-bold">
                        <?php echo e($monthly['quality'][count($monthly['quality'])-1]); ?>%
                    </span>
                    <span class="text-cyan-100/40 text-xs ml-1">ce mois</span>
                </div>
            </div>
            <div class="chart-container" style="height:180px">
                <svg id="quality-chart" width="100%" height="180" viewBox="0 0 600 180"
                     preserveAspectRatio="none" class="overflow-visible">
                </svg>
            </div>
            <div id="quality-labels" class="flex justify-between mt-2 px-1"></div>
        </div>

        
        <div class="glass rounded-2xl p-6">
            <div class="mb-5">
                <h2 class="text-white font-display font-bold text-base">Types d'incidents</h2>
                <p class="text-cyan-100/50 text-xs mt-0.5">Répartition annuelle</p>
            </div>
            <div class="flex items-center justify-center mb-4" style="height:160px">
                <div class="relative">
                    <svg width="160" height="160" viewBox="0 0 160 160" id="donut-chart">
                        
                    </svg>
                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                        <span class="text-2xl font-display font-bold text-white">
                            <?php echo e(array_sum(array_column($incTypes, 'count'))); ?>

                        </span>
                        <span class="text-[10px] text-cyan-100/50">incidents</span>
                    </div>
                </div>
            </div>
            <ul class="space-y-2">
                <?php $__currentLoopData = $incTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $it): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full shrink-0" style="background:<?php echo e($it['color']); ?>"></span>
                    <span class="text-xs text-cyan-100/70 flex-1 truncate"><?php echo e($it['type']); ?></span>
                    <span class="text-xs font-bold text-white"><?php echo e($it['pct']); ?>%</span>
                </li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    </div>

    
    <div class="grid lg:grid-cols-2 gap-6 mb-6">

        
        <div class="glass rounded-2xl p-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-white font-display font-bold text-base">Performance des équipes</h2>
                    <p class="text-cyan-100/50 text-xs mt-0.5">Score global & interventions résolues</p>
                </div>
            </div>
            <div class="space-y-4">
                <?php $__currentLoopData = $teams; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $team): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full" style="background:<?php echo e($team['color']); ?>"></span>
                            <span class="text-sm font-semibold text-white"><?php echo e($team['team']); ?></span>
                        </div>
                        <div class="flex items-center gap-4 text-xs text-cyan-100/60">
                            <span><?php echo e($team['interventions']); ?> interv.</span>
                            <span>⏱ <?php echo e($team['avg_time']); ?></span>
                            <span class="font-bold" style="color:<?php echo e($team['color']); ?>"><?php echo e($team['score']); ?>%</span>
                        </div>
                    </div>
                    <div class="h-2.5 bg-slate-950/60 rounded-full overflow-hidden">
                        <div class="h-full rounded-full bar-animate"
                             style="width:<?php echo e($team['score']); ?>%; background: linear-gradient(90deg, <?php echo e($team['color']); ?>99, <?php echo e($team['color']); ?>);
                                    animation-delay: <?php echo e($i * 0.12); ?>s">
                        </div>
                    </div>
                    <div class="flex justify-between text-[10px] text-cyan-100/30 mt-1">
                        <span><?php echo e($team['resolved']); ?>/<?php echo e($team['interventions']); ?> résolus</span>
                        <span>Taux: <?php echo e(round($team['resolved']/$team['interventions']*100)); ?>%</span>
                    </div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>

        
        <div class="glass rounded-2xl p-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-white font-display font-bold text-base">Consommation hebdo. / zone</h2>
                    <p class="text-cyan-100/50 text-xs mt-0.5">Top 5 zones cette semaine (m³/j)</p>
                </div>
            </div>
            <div class="chart-container" style="height:200px">
                <svg id="weekly-chart" width="100%" height="200" viewBox="0 0 600 200"
                     preserveAspectRatio="none" class="overflow-visible">
                </svg>
            </div>
            <div class="flex justify-between mt-2 px-1">
                <?php $__currentLoopData = $weekly['labels']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lbl): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <span class="text-[10px] text-cyan-100/40"><?php echo e($lbl); ?></span>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
            
            <div class="flex flex-wrap gap-3 mt-4">
                <?php $__currentLoopData = $weekly['zones']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $wz): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <span class="flex items-center gap-1.5 text-[10px] text-cyan-100/60">
                    <span class="w-2.5 h-2.5 rounded-sm" style="background:<?php echo e($wz['color']); ?>"></span>
                    <?php echo e($wz['name']); ?>

                </span>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    </div>

    
    <div class="glass rounded-2xl p-6 mb-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-white font-display font-bold text-base">État détaillé par zone</h2>
                <p class="text-cyan-100/50 text-xs mt-0.5"><?php echo e(count($zones)); ?> zones surveillées — mise à jour en temps réel</p>
            </div>
            <a href="<?php echo e(route('manager.map')); ?>"
               class="glass px-3 py-1.5 rounded-xl text-xs text-cyan-300 hover:text-white font-semibold flex items-center gap-1.5 transition-all hover:border-cyan-400/40">
                <i data-lucide="map" class="w-3.5 h-3.5"></i>
                Voir carte
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-white/5 text-left">
                        <th class="pb-3 pr-4 text-xs font-semibold text-cyan-100/50 whitespace-nowrap">Zone</th>
                        <th class="pb-3 pr-4 text-xs font-semibold text-cyan-100/50 text-center">Statut</th>
                        <th class="pb-3 pr-4 text-xs font-semibold text-cyan-100/50 text-right">Qualité</th>
                        <th class="pb-3 pr-4 text-xs font-semibold text-cyan-100/50 text-right">Pression</th>
                        <th class="pb-3 pr-4 text-xs font-semibold text-cyan-100/50 text-right">Débit</th>
                        <th class="pb-3 pr-4 text-xs font-semibold text-cyan-100/50 text-right">Conso.</th>
                        <th class="pb-3 pr-4 text-xs font-semibold text-cyan-100/50 text-center">Incidents</th>
                        <th class="pb-3    text-xs font-semibold text-cyan-100/50 text-right">Capteurs</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    <?php $__currentLoopData = $zones; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $zone): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $sc = match($zone['status']) {
                            'critical' => ['bg' => 'bg-red-500/10',   'text' => 'text-red-300',    'border' => 'border-red-500/30',   'dot' => 'bg-red-400'],
                            'alert'    => ['bg' => 'bg-amber-500/10', 'text' => 'text-amber-300',  'border' => 'border-amber-500/30', 'dot' => 'bg-amber-400'],
                            default    => ['bg' => 'bg-teal-500/10',  'text' => 'text-teal-300',   'border' => 'border-teal-500/30',  'dot' => 'bg-teal-400'],
                        };
                        $qc = $zone['quality'] >= 90 ? 'text-teal-400' : ($zone['quality'] >= 80 ? 'text-amber-400' : 'text-red-400');
                    ?>
                    <tr class="hover:bg-white/[.02] transition-colors">
                        <td class="py-3 pr-4">
                            <div class="flex items-center gap-2">
                                <span><?php echo e($zone['emoji']); ?></span>
                                <div>
                                    <p class="text-white font-semibold text-xs whitespace-nowrap"><?php echo e($zone['name']); ?></p>
                                    <p class="text-cyan-100/40 text-[10px]"><?php echo e(number_format($zone['population'], 0, ',', ' ')); ?> hab.</p>
                                </div>
                            </div>
                        </td>
                        <td class="py-3 pr-4 text-center">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold
                                         <?php echo e($sc['bg']); ?> <?php echo e($sc['text']); ?> border <?php echo e($sc['border']); ?>">
                                <span class="w-1.5 h-1.5 rounded-full <?php echo e($sc['dot']); ?>

                                    <?php echo e($zone['status'] !== 'normal' ? 'animate-pulse' : ''); ?>"></span>
                                <?php echo e($zone['status'] === 'normal' ? 'Normal' : ($zone['status'] === 'alert' ? 'Alerte' : 'Critique')); ?>

                            </span>
                        </td>
                        <td class="py-3 pr-4 text-right">
                            <span class="text-xs font-bold <?php echo e($qc); ?>"><?php echo e($zone['quality']); ?>%</span>
                        </td>
                        <td class="py-3 pr-4 text-right">
                            <span class="text-xs text-white"><?php echo e($zone['pressure']); ?> bar</span>
                        </td>
                        <td class="py-3 pr-4 text-right">
                            <span class="text-xs text-cyan-300"><?php echo e($zone['flowRate']); ?> m³/h</span>
                        </td>
                        <td class="py-3 pr-4 text-right">
                            <span class="text-xs text-cyan-100/70"><?php echo e(number_format($zone['consumption'], 0, ',', ' ')); ?> m³</span>
                        </td>
                        <td class="py-3 pr-4 text-center">
                            <?php if($zone['incidents'] > 0): ?>
                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-red-500/20 text-red-400 text-xs font-bold">
                                    <?php echo e($zone['incidents']); ?>

                                </span>
                            <?php else: ?>
                                <i data-lucide="check" class="w-4 h-4 text-teal-400 mx-auto"></i>
                            <?php endif; ?>
                        </td>
                        <td class="py-3 text-right">
                            <span class="text-xs text-cyan-100/60"><?php echo e($zone['sensors']); ?></span>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
                <tfoot>
                    <tr class="border-t border-white/10">
                        <td class="pt-3 text-xs font-bold text-cyan-300">TOTAL</td>
                        <td class="pt-3"></td>
                        <td class="pt-3 text-right text-xs font-bold text-teal-400">
                            <?php echo e(round(array_sum(array_column($zones,'quality'))/count($zones))); ?>%
                        </td>
                        <td class="pt-3 text-right text-xs font-bold text-white">
                            <?php echo e(round(array_sum(array_column($zones,'pressure'))/count($zones),1)); ?> bar
                        </td>
                        <td class="pt-3 text-right text-xs font-bold text-cyan-300">
                            <?php echo e(number_format(array_sum(array_column($zones,'flowRate')),0,',',' ')); ?> m³/h
                        </td>
                        <td class="pt-3 text-right text-xs font-bold text-cyan-100/70">
                            <?php echo e(number_format(array_sum(array_column($zones,'consumption')),0,',',' ')); ?> m³
                        </td>
                        <td class="pt-3 text-center text-xs font-bold text-red-400">
                            <?php echo e(array_sum(array_column($zones,'incidents'))); ?>

                        </td>
                        <td class="pt-3 text-right text-xs font-bold text-cyan-100/60">
                            <?php echo e(array_sum(array_column($zones,'sensors'))); ?>

                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    
    <div class="glass rounded-2xl p-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-white font-display font-bold text-base">Évolution de la pression réseau</h2>
                <p class="text-cyan-100/50 text-xs mt-0.5">Pression moyenne mensuelle (bar)</p>
            </div>
            <div class="glass px-3 py-1 rounded-lg flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-blue-400"></span>
                <span class="text-blue-300 text-xs font-semibold">Pression (bar)</span>
            </div>
        </div>
        <div class="chart-container" style="height:160px">
            <svg id="pressure-chart" width="100%" height="160" viewBox="0 0 600 160"
                 preserveAspectRatio="none" class="overflow-visible">
            </svg>
        </div>
        <div id="pressure-labels" class="flex justify-between mt-2 px-1"></div>
    </div>

</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
// ── Data from PHP ─────────────────────────────────────────────────────────────
const MONTHLY  = <?php echo json_encode($monthly, 15, 512) ?>;
const INC_TYPES= <?php echo json_encode($incTypes, 15, 512) ?>;
const WEEKLY   = <?php echo json_encode($weekly, 15, 512) ?>;

// ── Tiny SVG chart helpers ─────────────────────────────────────────────────────
const W = 600, H_DEF = 200;

function svgPath(points) {
    if (points.length === 0) return '';
    return points.map((p, i) => (i === 0 ? `M${p.x},${p.y}` : `L${p.x},${p.y}`)).join(' ');
}

function svgSmooth(points) {
    if (points.length < 2) return svgPath(points);
    let d = `M${points[0].x},${points[0].y}`;
    for (let i = 1; i < points.length; i++) {
        const cp1x = points[i-1].x + (points[i].x - points[i-1].x) / 3;
        const cp2x = points[i].x   - (points[i].x - points[i-1].x) / 3;
        d += ` C${cp1x},${points[i-1].y} ${cp2x},${points[i].y} ${points[i].x},${points[i].y}`;
    }
    return d;
}

function scaleY(val, min, max, h, pad = 20) {
    return pad + (1 - (val - min) / (max - min)) * (h - pad * 2);
}

function scaleX(i, n, w, pad = 30) {
    return pad + (i / (n - 1)) * (w - pad * 2);
}

// ── Render grid lines ─────────────────────────────────────────────────────────
function renderGrid(svg, h, n = 4) {
    for (let i = 0; i <= n; i++) {
        const y = 20 + (i / n) * (h - 40);
        const line = document.createElementNS('http://www.w3.org/2000/svg', 'line');
        line.setAttribute('x1', 0); line.setAttribute('x2', W);
        line.setAttribute('y1', y); line.setAttribute('y2', y);
        line.setAttribute('stroke', 'rgba(94,221,247,0.06)');
        line.setAttribute('stroke-width', '1');
        svg.appendChild(line);
    }
}

// ── Tooltip helper ────────────────────────────────────────────────────────────
function makeTooltip(container) {
    const tip = document.createElement('div');
    tip.className = 'chart-tooltip hidden';
    container.style.position = 'relative';
    container.appendChild(tip);
    return tip;
}

// ── 1. Consumption area chart ──────────────────────────────────────────────────
function drawConsumption() {
    const svg = document.getElementById('consumption-chart');
    const H = 200;
    const data = MONTHLY.consumption;
    const labels = MONTHLY.labels;
    const min = Math.min(...data) * 0.9;
    const max = Math.max(...data) * 1.05;

    renderGrid(svg, H);

    const pts = data.map((v, i) => ({ x: scaleX(i, data.length, W), y: scaleY(v, min, max, H) }));

    // Area fill
    const area = document.createElementNS('http://www.w3.org/2000/svg', 'path');
    const closedPath = svgSmooth(pts) + ` L${pts[pts.length-1].x},${H} L${pts[0].x},${H} Z`;
    area.setAttribute('d', closedPath);
    area.setAttribute('fill', 'url(#consumGrad)');
    area.setAttribute('opacity', '0.35');

    // Gradient
    const defs = document.createElementNS('http://www.w3.org/2000/svg', 'defs');
    defs.innerHTML = `<linearGradient id="consumGrad" x1="0" y1="0" x2="0" y2="1">
        <stop offset="0%" stop-color="#05bfdb" stop-opacity="0.8"/>
        <stop offset="100%" stop-color="#05bfdb" stop-opacity="0"/>
    </linearGradient>`;
    svg.appendChild(defs);
    svg.appendChild(area);

    // Line
    const line = document.createElementNS('http://www.w3.org/2000/svg', 'path');
    line.setAttribute('d', svgSmooth(pts));
    line.setAttribute('fill', 'none');
    line.setAttribute('stroke', '#05bfdb');
    line.setAttribute('stroke-width', '2.5');
    line.setAttribute('stroke-linecap', 'round');
    svg.appendChild(line);

    // Dots + tooltips
    const container = svg.closest('.chart-container');
    const tip = makeTooltip(container);
    pts.forEach((p, i) => {
        const circle = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
        circle.setAttribute('cx', p.x); circle.setAttribute('cy', p.y);
        circle.setAttribute('r', 4); circle.setAttribute('fill', '#05bfdb');
        circle.setAttribute('stroke', '#061525'); circle.setAttribute('stroke-width', '2');
        circle.style.cursor = 'pointer';
        circle.addEventListener('mouseenter', (e) => {
            const rect = svg.getBoundingClientRect();
            const svgRect = svg.getBoundingClientRect();
            tip.textContent = `${labels[i]}: ${data[i].toLocaleString('fr-FR')} m³`;
            tip.style.left = (p.x / W * 100) + '%';
            tip.style.top  = (p.y / H * 100) + '%';
            tip.style.transform = 'translateX(-50%) translateY(-130%)';
            tip.classList.remove('hidden');
        });
        circle.addEventListener('mouseleave', () => tip.classList.add('hidden'));
        svg.appendChild(circle);
    });

    // Labels
    const labelsEl = document.getElementById('consumption-labels');
    labels.forEach(l => {
        const span = document.createElement('span');
        span.className = 'text-[10px] text-cyan-100/40';
        span.textContent = l;
        labelsEl.appendChild(span);
    });
}

// ── 2. Incidents bar chart ─────────────────────────────────────────────────────
function drawIncidents() {
    const svg = document.getElementById('incidents-chart');
    const H = 200;
    const inc = MONTHLY.incidents;
    const res = MONTHLY.resolved;
    const labels = MONTHLY.labels;
    const n = inc.length;
    const max = Math.max(...inc, ...res) * 1.15;
    const barW = (W - 60) / n;

    renderGrid(svg, H);

    inc.forEach((v, i) => {
        const x = 30 + i * barW;
        const bh = (v / max) * (H - 40);
        const rh = (res[i] / max) * (H - 40);

        // Incidents bar
        const rInc = document.createElementNS('http://www.w3.org/2000/svg', 'rect');
        rInc.setAttribute('x', x + 2); rInc.setAttribute('width', barW/2 - 3);
        rInc.setAttribute('y', H - 20 - bh); rInc.setAttribute('height', bh);
        rInc.setAttribute('rx', 3); rInc.setAttribute('fill', 'rgba(251,113,133,0.7)');
        rInc.style.transformOrigin = `${x + 2 + (barW/2-3)/2}px ${H-20}px`;
        rInc.classList.add('bar-animate');
        rInc.style.animationDelay = (i * 0.05) + 's';
        svg.appendChild(rInc);

        // Resolved bar
        const rRes = document.createElementNS('http://www.w3.org/2000/svg', 'rect');
        rRes.setAttribute('x', x + barW/2 + 1); rRes.setAttribute('width', barW/2 - 3);
        rRes.setAttribute('y', H - 20 - rh); rRes.setAttribute('height', rh);
        rRes.setAttribute('rx', 3); rRes.setAttribute('fill', 'rgba(45,212,191,0.7)');
        rRes.style.transformOrigin = `${x + barW/2 + 1 + (barW/2-3)/2}px ${H-20}px`;
        rRes.classList.add('bar-animate');
        rRes.style.animationDelay = (i * 0.05 + 0.03) + 's';
        svg.appendChild(rRes);
    });

    const labelsEl = document.getElementById('incidents-labels');
    labels.forEach(l => {
        const span = document.createElement('span');
        span.className = 'text-[10px] text-cyan-100/40';
        span.textContent = l;
        labelsEl.appendChild(span);
    });
}

// ── 3. Quality line chart ──────────────────────────────────────────────────────
function drawQuality() {
    const svg = document.getElementById('quality-chart');
    const H = 180;
    const data = MONTHLY.quality;
    const labels = MONTHLY.labels;
    const min = 85, max = 100;

    renderGrid(svg, H, 3);

    // Threshold line at 90%
    const tY = scaleY(90, min, max, H);
    const threshLine = document.createElementNS('http://www.w3.org/2000/svg', 'line');
    threshLine.setAttribute('x1', 0); threshLine.setAttribute('x2', W);
    threshLine.setAttribute('y1', tY); threshLine.setAttribute('y2', tY);
    threshLine.setAttribute('stroke', 'rgba(251,191,36,0.4)');
    threshLine.setAttribute('stroke-width', '1.5');
    threshLine.setAttribute('stroke-dasharray', '4,3');
    svg.appendChild(threshLine);

    // Label threshold
    const tLabel = document.createElementNS('http://www.w3.org/2000/svg', 'text');
    tLabel.setAttribute('x', W - 5); tLabel.setAttribute('y', tY - 5);
    tLabel.setAttribute('text-anchor', 'end'); tLabel.setAttribute('font-size', '9');
    tLabel.setAttribute('fill', 'rgba(251,191,36,0.7)'); tLabel.textContent = 'Seuil 90%';
    svg.appendChild(tLabel);

    const pts = data.map((v, i) => ({ x: scaleX(i, data.length, W), y: scaleY(v, min, max, H) }));

    // Color segments
    const defs = document.createElementNS('http://www.w3.org/2000/svg', 'defs');
    defs.innerHTML = `<linearGradient id="qualGrad" x1="0" y1="0" x2="0" y2="1">
        <stop offset="0%" stop-color="#2dd4bf" stop-opacity="0.5"/>
        <stop offset="100%" stop-color="#2dd4bf" stop-opacity="0"/>
    </linearGradient>`;
    svg.appendChild(defs);

    const area = document.createElementNS('http://www.w3.org/2000/svg', 'path');
    area.setAttribute('d', svgSmooth(pts) + ` L${pts[pts.length-1].x},${H} L${pts[0].x},${H} Z`);
    area.setAttribute('fill', 'url(#qualGrad)'); area.setAttribute('opacity', '0.4');
    svg.appendChild(area);

    const line = document.createElementNS('http://www.w3.org/2000/svg', 'path');
    line.setAttribute('d', svgSmooth(pts));
    line.setAttribute('fill', 'none'); line.setAttribute('stroke', '#2dd4bf');
    line.setAttribute('stroke-width', '2.5'); line.setAttribute('stroke-linecap', 'round');
    svg.appendChild(line);

    // Dots with colors
    const container = svg.closest('.chart-container');
    const tip = makeTooltip(container);
    pts.forEach((p, i) => {
        const color = data[i] >= 90 ? '#2dd4bf' : '#fbbf24';
        const c = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
        c.setAttribute('cx', p.x); c.setAttribute('cy', p.y);
        c.setAttribute('r', 4.5); c.setAttribute('fill', color);
        c.setAttribute('stroke', '#061525'); c.setAttribute('stroke-width', '2');
        c.style.cursor = 'pointer';
        c.addEventListener('mouseenter', () => {
            tip.textContent = `${labels[i]}: ${data[i]}%`;
            tip.style.left = (p.x / W * 100) + '%';
            tip.style.top  = (p.y / H * 100) + '%';
            tip.style.transform = 'translateX(-50%) translateY(-130%)';
            tip.classList.remove('hidden');
        });
        c.addEventListener('mouseleave', () => tip.classList.add('hidden'));
        svg.appendChild(c);
    });

    const labelsEl = document.getElementById('quality-labels');
    labels.forEach(l => {
        const span = document.createElement('span');
        span.className = 'text-[10px] text-cyan-100/40';
        span.textContent = l;
        labelsEl.appendChild(span);
    });
}

// ── 4. Donut chart ────────────────────────────────────────────────────────────
function drawDonut() {
    const svg = document.getElementById('donut-chart');
    const cx = 80, cy = 80, r = 60, stroke = 22;
    const circ = 2 * Math.PI * r;
    const total = INC_TYPES.reduce((s, t) => s + t.count, 0);
    let offset = 0;

    INC_TYPES.forEach((t, i) => {
        const frac = t.count / total;
        const dash = frac * circ;
        const gap  = circ - dash;

        const circle = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
        circle.setAttribute('cx', cx); circle.setAttribute('cy', cy); circle.setAttribute('r', r);
        circle.setAttribute('fill', 'none');
        circle.setAttribute('stroke', t.color);
        circle.setAttribute('stroke-width', stroke);
        circle.setAttribute('stroke-dasharray', `${dash} ${gap}`);
        circle.setAttribute('stroke-dashoffset', -offset);
        circle.setAttribute('transform', `rotate(-90 ${cx} ${cy})`);
        circle.style.transition = 'stroke-dasharray 0.8s cubic-bezier(.22,1,.36,1)';
        circle.style.transitionDelay = (i * 0.1) + 's';
        svg.appendChild(circle);

        offset += dash;
    });
}

// ── 5. Weekly stacked lines ────────────────────────────────────────────────────
function drawWeekly() {
    const svg = document.getElementById('weekly-chart');
    const H = 200;
    const labels = WEEKLY.labels;
    const allVals = WEEKLY.zones.flatMap(z => z.data);
    const min = Math.min(...allVals) * 0.9;
    const max = Math.max(...allVals) * 1.05;

    renderGrid(svg, H);

    WEEKLY.zones.forEach(zone => {
        const pts = zone.data.map((v, i) => ({
            x: scaleX(i, zone.data.length, W),
            y: scaleY(v, min, max, H),
        }));

        const line = document.createElementNS('http://www.w3.org/2000/svg', 'path');
        line.setAttribute('d', svgSmooth(pts));
        line.setAttribute('fill', 'none');
        line.setAttribute('stroke', zone.color);
        line.setAttribute('stroke-width', '2');
        line.setAttribute('stroke-linecap', 'round');
        line.setAttribute('opacity', '0.85');
        svg.appendChild(line);

        // End dot
        const lastPt = pts[pts.length - 1];
        const dot = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
        dot.setAttribute('cx', lastPt.x); dot.setAttribute('cy', lastPt.y);
        dot.setAttribute('r', 4); dot.setAttribute('fill', zone.color);
        dot.setAttribute('stroke', '#061525'); dot.setAttribute('stroke-width', '2');
        svg.appendChild(dot);
    });
}

// ── 6. Pressure line chart ────────────────────────────────────────────────────
function drawPressure() {
    const svg = document.getElementById('pressure-chart');
    const H = 160;
    const data = MONTHLY.pressure;
    const labels = MONTHLY.labels;
    const min = 3.0, max = 4.5;

    renderGrid(svg, H, 3);

    // Optimal zone band (3.5–4.2)
    const y1 = scaleY(4.2, min, max, H);
    const y2 = scaleY(3.5, min, max, H);
    const band = document.createElementNS('http://www.w3.org/2000/svg', 'rect');
    band.setAttribute('x', 0); band.setAttribute('y', y1);
    band.setAttribute('width', W); band.setAttribute('height', y2 - y1);
    band.setAttribute('fill', 'rgba(56,189,248,0.07)');
    svg.appendChild(band);

    const defs = document.createElementNS('http://www.w3.org/2000/svg', 'defs');
    defs.innerHTML = `<linearGradient id="pressGrad" x1="0" y1="0" x2="0" y2="1">
        <stop offset="0%" stop-color="#38bdf8" stop-opacity="0.5"/>
        <stop offset="100%" stop-color="#38bdf8" stop-opacity="0"/>
    </linearGradient>`;
    svg.appendChild(defs);

    const pts = data.map((v, i) => ({ x: scaleX(i, data.length, W), y: scaleY(v, min, max, H) }));

    const area = document.createElementNS('http://www.w3.org/2000/svg', 'path');
    area.setAttribute('d', svgSmooth(pts) + ` L${pts[pts.length-1].x},${H} L${pts[0].x},${H} Z`);
    area.setAttribute('fill', 'url(#pressGrad)'); area.setAttribute('opacity', '0.3');
    svg.appendChild(area);

    const line = document.createElementNS('http://www.w3.org/2000/svg', 'path');
    line.setAttribute('d', svgSmooth(pts));
    line.setAttribute('fill', 'none'); line.setAttribute('stroke', '#38bdf8');
    line.setAttribute('stroke-width', '2.5'); line.setAttribute('stroke-linecap', 'round');
    svg.appendChild(line);

    // Label "Zone optimale"
    const zLabel = document.createElementNS('http://www.w3.org/2000/svg', 'text');
    zLabel.setAttribute('x', 6); zLabel.setAttribute('y', (y1 + y2) / 2 + 4);
    zLabel.setAttribute('font-size', '9'); zLabel.setAttribute('fill', 'rgba(56,189,248,0.5)');
    zLabel.textContent = 'Zone optimale (3.5–4.2 bar)';
    svg.appendChild(zLabel);

    const container = svg.closest('.chart-container');
    const tip = makeTooltip(container);
    pts.forEach((p, i) => {
        const c = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
        c.setAttribute('cx', p.x); c.setAttribute('cy', p.y);
        c.setAttribute('r', 4); c.setAttribute('fill', '#38bdf8');
        c.setAttribute('stroke', '#061525'); c.setAttribute('stroke-width', '2');
        c.style.cursor = 'pointer';
        c.addEventListener('mouseenter', () => {
            tip.textContent = `${labels[i]}: ${data[i]} bar`;
            tip.style.left = (p.x / W * 100) + '%';
            tip.style.top  = (p.y / H * 100) + '%';
            tip.style.transform = 'translateX(-50%) translateY(-130%)';
            tip.classList.remove('hidden');
        });
        c.addEventListener('mouseleave', () => tip.classList.add('hidden'));
        svg.appendChild(c);
    });

    const labelsEl = document.getElementById('pressure-labels');
    labels.forEach(l => {
        const span = document.createElement('span');
        span.className = 'text-[10px] text-cyan-100/40';
        span.textContent = l;
        labelsEl.appendChild(span);
    });
}

// ── Init all charts after DOM ready ──────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    drawConsumption();
    drawIncidents();
    drawQuality();
    drawDonut();
    drawWeekly();
    drawPressure();
    if (typeof lucide !== 'undefined') lucide.createIcons();
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.manager', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\ghada\Desktop\5ème\projet_Laravel\aquasecure\resources\views/manager/analytics.blade.php ENDPATH**/ ?>