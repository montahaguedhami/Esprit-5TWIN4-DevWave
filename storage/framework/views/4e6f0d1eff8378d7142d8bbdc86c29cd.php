<?php $__env->startSection('title', 'Administration — AquaSecure'); ?>
<?php $__env->startSection('page-title', 'Administration'); ?>
<?php $__env->startSection('page-subtitle', 'Vue globale de la plateforme AquaSecure'); ?>

<?php
    use App\Data\PlaceholderData;

    // Data
    $accounts     = PlaceholderData::adminAllAccounts();
    $reclamations = PlaceholderData::adminAllReclamations();
    $technicians  = PlaceholderData::adminTechnicians();
    $managers     = PlaceholderData::adminManagers();
    $funcAct      = PlaceholderData::adminFunctionalActivity();
    $resHistory   = PlaceholderData::adminResolutionHistory();
    $mapZones     = PlaceholderData::mapZones();
    $zoneOverview = PlaceholderData::adminZoneOverview();
    $perf         = PlaceholderData::adminPerformanceStats();
    $inc7d        = PlaceholderData::adminIncidents7Days();

    // Counters
    $citizens     = count(array_filter($accounts, fn($a) => $a['role'] === 'citizen'));
    $techCount    = count(array_filter($accounts, fn($a) => $a['role'] === 'technician'));
    $mgrCount     = count(array_filter($accounts, fn($a) => $a['role'] === 'manager'));

    $totalRec     = count($reclamations);
    $pendingRec   = count(array_filter($reclamations, fn($r) => $r['status'] === 'pending'));
    $progressRec  = count(array_filter($reclamations, fn($r) => $r['status'] === 'in_progress'));
    $resolvedRec  = count(array_filter($reclamations, fn($r) => $r['status'] === 'resolved'));
    $criticalRec  = count(array_filter($reclamations, fn($r) => $r['priority'] === 'critical' && $r['status'] !== 'resolved'));

    $techOnMission= count(array_filter($technicians, fn($t) => $t['status'] === 'on_mission'));
    $techAvail    = count(array_filter($technicians, fn($t) => $t['status'] === 'available'));

    // Style helpers
    $statusStyle = [
        'pending'     => ['bg'=>'bg-red-500/15',   'text'=>'text-red-300',   'dot'=>'bg-red-400',   'label'=>'Non traité'],
        'in_progress' => ['bg'=>'bg-amber-500/15', 'text'=>'text-amber-300', 'dot'=>'bg-amber-400', 'label'=>'En cours'],
        'resolved'    => ['bg'=>'bg-teal-500/15',  'text'=>'text-teal-300',  'dot'=>'bg-teal-400',  'label'=>'Résolu'],
    ];
    $priorityStyle = [
        'critical' => ['bg'=>'bg-red-500/15',  'text'=>'text-red-300',  'dot'=>'bg-red-400 animate-pulse','label'=>'Critique'],
        'medium'   => ['bg'=>'bg-amber-500/15','text'=>'text-amber-300','dot'=>'bg-amber-400',             'label'=>'Moyenne'],
        'low'      => ['bg'=>'bg-blue-500/15', 'text'=>'text-blue-300', 'dot'=>'bg-blue-400',              'label'=>'Faible'],
    ];
?>

<?php $__env->startPush('styles'); ?>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
      integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<style>
@keyframes barIn { from { width:0 } }
.bar-grow { animation: barIn .9s cubic-bezier(.22,1,.36,1) both; }
@keyframes donutSpin { from { stroke-dashoffset:300 } }
.donut-seg { animation: donutSpin .9s cubic-bezier(.22,1,.36,1) both; }
@keyframes fadeUp { from { opacity:0; transform:translateY(10px) } to { opacity:1; transform:translateY(0) } }
.fade-up { animation: fadeUp .5s ease both; }

/* Map */
#admin-map { height: 400px; }
.leaflet-tile-pane { filter: brightness(.68) saturate(.6) hue-rotate(185deg); }
.leaflet-control-zoom a { background:rgba(6,21,37,.92)!important; border-color:rgba(5,191,219,.25)!important; color:#7ce8f7!important; }
.leaflet-control-attribution { display:none!important; }
.leaflet-popup-content-wrapper {
    background:rgba(6,21,37,.97)!important; border:1px solid rgba(5,191,219,.3)!important;
    border-radius:14px!important; color:#f0fdff!important; padding:0!important;
    box-shadow:0 16px 48px rgba(0,0,0,.5)!important; backdrop-filter:blur(20px);
}
.leaflet-popup-tip { background:rgba(6,21,37,.97)!important; }
.leaflet-popup-content { margin:0!important; padding:0!important; min-width:220px; }
.leaflet-popup-close-button { color:rgba(156,200,216,.6)!important; font-size:18px!important; padding:8px 10px!important; }
.leaflet-popup-close-button:hover { color:#fff!important; }

/* Tab filter */
.tab-active  { background:rgba(5,191,219,.12); color:#5ee5f7; border-color:rgba(94,221,247,.35); }
.tab-inactive{ color:rgba(156,200,216,.5); border-color:transparent; }
.tab-inactive:hover { color:rgba(156,200,216,.8); background:rgba(255,255,255,.03); }

/* Spark tip */
.spark-tip {
    position:absolute; background:rgba(6,21,37,.96); border:1px solid rgba(5,191,219,.3);
    border-radius:8px; padding:5px 10px; font-size:11px; color:#f0fdff;
    pointer-events:none; white-space:nowrap; z-index:20;
    transform:translateX(-50%) translateY(-130%); backdrop-filter:blur(12px);
}

/* Block btn */
.btn-block   { color:#fca5a5; border-color:rgba(239,68,68,.3); }
.btn-block:hover   { background:rgba(239,68,68,.12); }
.btn-unblock { color:#6ee7b7; border-color:rgba(52,211,153,.3); }
.btn-unblock:hover { background:rgba(52,211,153,.12); }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('admin-content'); ?>
<div class="space-y-6 max-w-7xl mx-auto">


<div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-6 gap-4">
    <?php $__currentLoopData = [
        ['val'=>$citizens,    'label'=>'Citoyens',      'sub'=>'+14 ce mois',                          'icon'=>'users',          'c'=>'#2dd4bf', 'delay'=>0],
        ['val'=>$techCount,   'label'=>'Techniciens',   'sub'=>$techOnMission.' en mission',            'icon'=>'wrench',         'c'=>'#38bdf8', 'delay'=>1],
        ['val'=>$mgrCount,    'label'=>'Gestionnaires', 'sub'=>'actifs sur le réseau',                  'icon'=>'briefcase',      'c'=>'#818cf8', 'delay'=>2],
        ['val'=>$totalRec,    'label'=>'Incidents',     'sub'=>$pendingRec.' non traités',              'icon'=>'alert-triangle', 'c'=>'#ef4444', 'delay'=>3],
        ['val'=>$progressRec, 'label'=>'En cours',      'sub'=>'interventions actives',                 'icon'=>'loader',         'c'=>'#f97316', 'delay'=>4],
        ['val'=>$resolvedRec, 'label'=>'Résolus',       'sub'=>$perf['resolution_rate'].'% taux',       'icon'=>'check-circle',   'c'=>'#34d399', 'delay'=>5],
    ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $kpi): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <div class="glass rounded-2xl p-4 hover-lift fade-up relative overflow-hidden group"
         style="animation-delay:<?php echo e($kpi['delay'] * 0.06); ?>s; --kc:<?php echo e($kpi['c']); ?>">
        <div class="w-10 h-10 rounded-xl flex items-center justify-center mb-3"
             style="background:<?php echo e($kpi['c']); ?>18">
            <i data-lucide="<?php echo e($kpi['icon']); ?>" class="w-5 h-5" style="color:<?php echo e($kpi['c']); ?>"></i>
        </div>
        <p class="text-2xl font-display font-bold text-white leading-tight"><?php echo e($kpi['val']); ?></p>
        <p class="text-[11px] text-cyan-100/55 font-medium mt-0.5"><?php echo e($kpi['label']); ?></p>
        <p class="text-[10px] text-cyan-100/30 mt-0.5"><?php echo e($kpi['sub']); ?></p>
        <div class="absolute bottom-0 left-0 right-0 h-0.5 opacity-0 group-hover:opacity-100 transition-opacity"
             style="background:linear-gradient(90deg,transparent,<?php echo e($kpi['c']); ?>80,transparent)"></div>
    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>


<div class="glass rounded-2xl overflow-hidden fade-up" style="animation-delay:.1s">
    <div class="px-6 py-4 border-b border-white/5 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-white font-display font-bold">Surveillance globale des zones</h2>
            <p class="text-cyan-100/50 text-xs mt-0.5">
                <?php echo e(count($mapZones)); ?> zones · <?php echo e($totalRec); ?> incidents · données en temps réel
            </p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <?php $__currentLoopData = [
                ['c'=>'bg-red-400 animate-pulse','t'=>'text-red-300',  'l'=>'Critique',        'n'=>$criticalRec],
                ['c'=>'bg-amber-400',            't'=>'text-amber-300','l'=>'En cours',         'n'=>$progressRec],
                ['c'=>'bg-teal-400',             't'=>'text-teal-300', 'l'=>'Résolu',           'n'=>$resolvedRec],
                ['c'=>'bg-blue-400/60',          't'=>'text-blue-300', 'l'=>'Non traité',       'n'=>$pendingRec],
            ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $leg): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-white/5 border border-white/10">
                <span class="w-2 h-2 rounded-full <?php echo e($leg['c']); ?>"></span>
                <span class="text-xs font-semibold <?php echo e($leg['t']); ?>"><?php echo e($leg['n']); ?> <?php echo e($leg['l']); ?></span>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <a href="<?php echo e(route('manager.map')); ?>"
               class="glass px-3 py-1.5 rounded-lg text-xs text-cyan-300 hover:text-white font-semibold flex items-center gap-1 transition-all">
                <i data-lucide="maximize-2" class="w-3.5 h-3.5"></i>
                <span class="hidden sm:inline">Plein écran</span>
            </a>
        </div>
    </div>
    <div id="admin-map"></div>
</div>


<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 fade-up" style="animation-delay:.15s">
    <?php $__currentLoopData = [
        ['icon'=>'alert-triangle','c'=>'red',  'val'=>$criticalRec,  'label'=>'Incidents critiques','sub'=>'Nécessitent action immédiate','filter'=>'critical'],
        ['icon'=>'loader',        'c'=>'amber','val'=>$progressRec,  'label'=>'En cours',           'sub'=>'Interventions actives',       'filter'=>'in_progress'],
        ['icon'=>'check-circle',  'c'=>'teal', 'val'=>$resolvedRec,  'label'=>'Résolus',            'sub'=>$perf['resolution_rate'].'% taux résolution','filter'=>'resolved'],
    ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $col): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <?php
        $cs = [
            'red'  =>['ring'=>'ring-red-500/25',  'ib'=>'bg-red-500/15',  'ic'=>'text-red-400',  'vc'=>'text-red-300',  'btn'=>'bg-red-500/10 text-red-300 border-red-500/25 hover:bg-red-500/20'],
            'amber'=>['ring'=>'ring-amber-500/25','ib'=>'bg-amber-500/15','ic'=>'text-amber-400','vc'=>'text-amber-300','btn'=>'bg-amber-500/10 text-amber-300 border-amber-500/25 hover:bg-amber-500/20'],
            'teal' =>['ring'=>'ring-teal-500/25', 'ib'=>'bg-teal-500/15', 'ic'=>'text-teal-400', 'vc'=>'text-teal-300', 'btn'=>'bg-teal-500/10 text-teal-300 border-teal-500/25 hover:bg-teal-500/20'],
        ][$col['c']];
    ?>
    <button onclick="filterRec('<?php echo e($col['filter']); ?>')"
            class="glass rounded-2xl p-6 ring-1 <?php echo e($cs['ring']); ?> flex flex-col items-center text-center hover-lift w-full">
        <div class="w-14 h-14 rounded-2xl <?php echo e($cs['ib']); ?> flex items-center justify-center mb-3">
            <i data-lucide="<?php echo e($col['icon']); ?>" class="w-7 h-7 <?php echo e($cs['ic']); ?>

                <?php echo e($col['c']==='red' ? 'animate-pulse' : ''); ?>"></i>
        </div>
        <p class="text-4xl font-display font-bold <?php echo e($cs['vc']); ?> mb-1"><?php echo e($col['val']); ?></p>
        <p class="text-white text-sm font-semibold mb-0.5"><?php echo e($col['label']); ?></p>
        <p class="text-cyan-100/40 text-xs mb-4"><?php echo e($col['sub']); ?></p>
        <span class="px-4 py-1.5 rounded-xl border text-xs font-semibold <?php echo e($cs['btn']); ?> transition-colors">
            Filtrer →
        </span>
    </button>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>


<div class="grid lg:grid-cols-5 gap-6 fade-up" style="animation-delay:.2s">

    
    <div class="glass rounded-2xl overflow-hidden lg:col-span-3">
        <div class="px-5 py-4 border-b border-white/5 flex flex-wrap items-center gap-3 justify-between">
            <div>
                <h2 class="text-white font-display font-bold">Incidents récents</h2>
                <p class="text-cyan-100/50 text-xs mt-0.5">Tous les signalements de la plateforme</p>
            </div>
            <div class="flex gap-1" id="rec-tabs">
                <?php $__currentLoopData = ['all'=>'Tous','pending'=>'Non traités','in_progress'=>'En cours','resolved'=>'Résolus']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $fv=>$fl): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <button onclick="filterRec('<?php echo e($fv); ?>')" data-f="<?php echo e($fv); ?>"
                        class="rec-tab px-2.5 py-1 rounded-lg text-[11px] font-semibold border transition-all
                               <?php echo e($fv==='all' ? 'tab-active' : 'tab-inactive'); ?>">
                    <?php echo e($fl); ?>

                </button>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-white/5 text-left">
                        <th class="px-4 py-2.5 text-[11px] font-semibold text-cyan-100/40">ID · Type</th>
                        <th class="px-3 py-2.5 text-[11px] font-semibold text-cyan-100/40 hidden sm:table-cell">Zone</th>
                        <th class="px-3 py-2.5 text-[11px] font-semibold text-cyan-100/40 text-center">Priorité</th>
                        <th class="px-3 py-2.5 text-[11px] font-semibold text-cyan-100/40 text-center">Statut</th>
                        <th class="px-3 py-2.5 text-[11px] font-semibold text-cyan-100/40 text-right hidden md:table-cell">Date</th>
                        <th class="px-3 py-2.5 text-[11px] font-semibold text-cyan-100/40 text-right">Action</th>
                    </tr>
                </thead>
                <tbody id="rec-tbody" class="divide-y divide-white/[.04]">
                    <?php $__currentLoopData = $reclamations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rec): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php $ps = $priorityStyle[$rec['priority']]; $ss = $statusStyle[$rec['status']]; ?>
                    <tr class="rec-row hover:bg-white/[.025] transition-colors cursor-pointer"
                        data-status="<?php echo e($rec['status']); ?>" data-priority="<?php echo e($rec['priority']); ?>"
                        onclick="openRecDetail(<?php echo e($loop->index); ?>)">
                        <td class="px-4 py-3">
                            <span class="text-[10px] font-mono font-bold text-cyan-400"><?php echo e($rec['id']); ?></span>
                            <p class="text-white text-xs font-semibold mt-0.5 max-w-[140px] truncate"><?php echo e($rec['type']); ?></p>
                        </td>
                        <td class="px-3 py-3 hidden sm:table-cell">
                            <p class="text-xs text-cyan-100/60 truncate max-w-[90px]"><?php echo e($rec['zone']); ?></p>
                            <p class="text-[10px] text-cyan-100/30 truncate"><?php echo e($rec['citizen']); ?></p>
                        </td>
                        <td class="px-3 py-3 text-center">
                            <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-full text-[10px] font-bold <?php echo e($ps['bg']); ?> <?php echo e($ps['text']); ?>">
                                <span class="w-1.5 h-1.5 rounded-full <?php echo e($ps['dot']); ?>"></span>
                                <?php echo e($ps['label']); ?>

                            </span>
                        </td>
                        <td class="px-3 py-3 text-center">
                            <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-full text-[10px] font-bold <?php echo e($ss['bg']); ?> <?php echo e($ss['text']); ?>">
                                <span class="w-1.5 h-1.5 rounded-full <?php echo e($ss['dot']); ?>"></span>
                                <?php echo e($ss['label']); ?>

                            </span>
                        </td>
                        <td class="px-3 py-3 text-right hidden md:table-cell">
                            <span class="text-[10px] text-cyan-100/30"><?php echo e($rec['created_at']); ?></span>
                        </td>
                        <td class="px-3 py-3 text-right">
                            <button class="glass p-1.5 rounded-lg text-cyan-400/60 hover:text-cyan-300 transition-colors"
                                    onclick="event.stopPropagation(); openRecDetail(<?php echo e($loop->index); ?>)">
                                <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
        <div id="rec-empty" class="hidden py-10 text-center">
            <i data-lucide="check-circle" class="w-10 h-10 text-teal-400/30 mx-auto mb-2"></i>
            <p class="text-cyan-100/40 text-sm">Aucun incident dans cette catégorie</p>
        </div>
        <div class="px-5 py-3 border-t border-white/5 flex items-center justify-between">
            <span class="text-[11px] text-cyan-100/35" id="rec-count"><?php echo e($totalRec); ?> incidents</span>
            <a href="<?php echo e(route('admin.logs')); ?>" class="text-xs text-cyan-400 hover:text-cyan-300 font-semibold transition-colors">
                Historique complet →
            </a>
        </div>
    </div>

    
    <div class="glass rounded-2xl overflow-hidden lg:col-span-2">
        <div class="px-5 py-4 border-b border-white/5">
            <h2 class="text-white font-display font-bold text-sm">Activité récente</h2>
            <p class="text-cyan-100/50 text-xs mt-0.5">Flux plateforme AquaSecure</p>
        </div>
        <div class="relative">
            <div class="absolute left-9 top-2 bottom-2 w-px bg-gradient-to-b from-cyan-500/20 to-transparent pointer-events-none"></div>
            <div class="divide-y divide-white/[.04]">
                <?php $__currentLoopData = array_slice($funcAct, 0, 8); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $act): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $ab = match($act['color']) {
                        'teal'=>'bg-teal-500/15','amber'=>'bg-amber-500/15','blue'=>'bg-blue-500/15',
                        default=>'bg-cyan-500/15'
                    };
                    $ac = match($act['color']) {
                        'teal'=>'text-teal-400','amber'=>'text-amber-400','blue'=>'text-blue-400',
                        default=>'text-cyan-400'
                    };
                    $rb = ['citizen'=>'bg-teal-500/15','manager'=>'bg-blue-500/15','technician'=>'bg-cyan-500/15','admin'=>'bg-violet-500/15'][$act['role']] ?? 'bg-white/5';
                    $rt = ['citizen'=>'text-teal-300', 'manager'=>'text-blue-300', 'technician'=>'text-cyan-300', 'admin'=>'text-violet-300'][$act['role']] ?? 'text-white';
                    $rl = ['citizen'=>'Citoyen','manager'=>'Gestionnaire','technician'=>'Technicien','admin'=>'Admin'][$act['role']] ?? $act['role'];
                ?>
                <div class="flex items-start gap-2.5 px-4 py-2.5 hover:bg-white/[.02] transition-colors">
                    <div class="w-7 h-7 rounded-lg <?php echo e($ab); ?> flex items-center justify-center shrink-0 mt-0.5 relative z-10">
                        <i data-lucide="<?php echo e($act['icon']); ?>" class="w-3 h-3 <?php echo e($ac); ?>"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <span class="text-white text-xs font-semibold truncate"><?php echo e($act['actor']); ?></span>
                            <span class="px-1.5 py-0.5 rounded text-[9px] font-bold <?php echo e($rb); ?> <?php echo e($rt); ?>"><?php echo e($rl); ?></span>
                        </div>
                        <p class="text-cyan-100/50 text-[11px] mt-0.5 leading-tight"><?php echo e($act['event']); ?></p>
                    </div>
                    <span class="text-[10px] text-cyan-100/30 shrink-0 mt-0.5 whitespace-nowrap"><?php echo e($act['time']); ?></span>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    </div>
</div>


<div class="grid lg:grid-cols-2 gap-6 fade-up" style="animation-delay:.25s">

    
    <div class="glass rounded-2xl p-6">
        <div class="flex items-center justify-between mb-5">
            <div>
                <h2 class="text-white font-display font-bold">Utilisateurs de la plateforme</h2>
                <p class="text-cyan-100/50 text-xs mt-0.5"><?php echo e(count($accounts)); ?> comptes enregistrés</p>
            </div>
            <a href="<?php echo e(route('admin.users.index')); ?>"
               class="glass px-3 py-1.5 rounded-xl text-xs text-cyan-300 hover:text-white font-semibold flex items-center gap-1.5 transition-all hover:border-cyan-400/40">
                <i data-lucide="user-plus" class="w-3.5 h-3.5"></i>Gérer
            </a>
        </div>
        <?php
            $distData = [
                ['label'=>'Citoyens',     'count'=>$citizens,  'color'=>'#2dd4bf', 'route'=>'admin.users.index'],
                ['label'=>'Techniciens',  'count'=>$techCount, 'color'=>'#38bdf8', 'route'=>'admin.users.index'],
                ['label'=>'Gestionnaires','count'=>$mgrCount,  'color'=>'#818cf8', 'route'=>'admin.users.index'],
                ['label'=>'Admins',       'count'=>count(array_filter($accounts,fn($a)=>$a['role']==='admin')), 'color'=>'#f472b6', 'route'=>'admin.users.index'],
            ];
            $dTotal = array_sum(array_column($distData,'count'));
            $dCirc  = 2 * M_PI * 50;
            $dOff   = 0;
        ?>
        <div class="flex items-center gap-6 mb-5">
            
            <svg width="130" height="130" viewBox="0 0 130 130" class="shrink-0">
                <?php $__currentLoopData = $distData; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dr): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php $dDash = ($dr['count']/$dTotal)*$dCirc; ?>
                <circle cx="65" cy="65" r="50" fill="none" stroke="<?php echo e($dr['color']); ?>"
                        stroke-width="16"
                        stroke-dasharray="<?php echo e($dDash); ?> <?php echo e($dCirc-$dDash); ?>"
                        stroke-dashoffset="<?php echo e(-$dOff); ?>"
                        transform="rotate(-90 65 65)" class="donut-seg"
                        style="animation-delay:<?php echo e($loop->index*0.1); ?>s"/>
                <?php $dOff += $dDash; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <text x="65" y="60" text-anchor="middle" font-size="20" font-weight="700"
                      fill="#f0fdff" font-family="Space Grotesk"><?php echo e($dTotal); ?></text>
                <text x="65" y="74" text-anchor="middle" font-size="9"
                      fill="rgba(156,200,216,.5)" font-family="Plus Jakarta Sans">utilisateurs</text>
            </svg>
            
            <div class="flex-1 space-y-3">
                <?php $__currentLoopData = $distData; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dr): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full" style="background:<?php echo e($dr['color']); ?>"></span>
                            <span class="text-xs text-cyan-100/65"><?php echo e($dr['label']); ?></span>
                        </div>
                        <span class="text-xs font-bold text-white"><?php echo e(number_format($dr['count'],0,',',' ')); ?></span>
                    </div>
                    <div class="h-1.5 bg-slate-950/60 rounded-full overflow-hidden">
                        <div class="h-full rounded-full bar-grow"
                             style="width:<?php echo e($dTotal>0?max(2,round($dr['count']/$dTotal*100)):0); ?>%;
                                    background:<?php echo e($dr['color']); ?>;animation-delay:<?php echo e($loop->index*0.12); ?>s"></div>
                    </div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
        
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 pt-4 border-t border-white/5">
            <?php $__currentLoopData = [
                ['label'=>'Citoyens',     'color'=>'#2dd4bf'],
                ['label'=>'Techniciens',  'color'=>'#38bdf8'],
                ['label'=>'Gestionnaires','color'=>'#818cf8'],
                ['label'=>'Admins',       'color'=>'#f472b6'],
            ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rb): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <a href="<?php echo e(route('admin.users.index')); ?>"
               class="text-center py-1.5 rounded-lg text-[11px] font-semibold transition-all hover:opacity-80"
               style="background:<?php echo e($rb['color']); ?>15; color:<?php echo e($rb['color']); ?>; border:1px solid <?php echo e($rb['color']); ?>30">
                Voir <?php echo e($rb['label']); ?>

            </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>

    
    <div class="glass rounded-2xl overflow-hidden">
        <div class="px-6 py-4 border-b border-white/5">
            <h2 class="text-white font-display font-bold">Équipe technique</h2>
            <p class="text-cyan-100/50 text-xs mt-0.5">
                <span class="text-amber-300"><?php echo e($techOnMission); ?></span> en mission ·
                <span class="text-teal-300"><?php echo e($techAvail); ?></span> disponibles
            </p>
        </div>
        <div class="divide-y divide-white/[.04]">
            <?php $__currentLoopData = $technicians; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tech): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
                $ts = [
                    'on_mission'=>['dot'=>'bg-amber-400 animate-pulse','text'=>'text-amber-300','label'=>'En mission'],
                    'available' =>['dot'=>'bg-teal-400',               'text'=>'text-teal-300', 'label'=>'Disponible'],
                    'off_duty'  =>['dot'=>'bg-slate-500',              'text'=>'text-slate-400','label'=>'Hors service'],
                ][$tech['status']];
            ?>
            <div class="flex items-center gap-3 px-5 py-3 hover:bg-white/[.02] transition-colors">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-cyan-500/20 to-cyan-700/20
                             border border-cyan-400/15 flex items-center justify-center text-xs font-bold text-cyan-300 shrink-0">
                    <?php echo e($tech['initials']); ?>

                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-white text-sm font-semibold truncate"><?php echo e($tech['name']); ?></p>
                    <p class="text-cyan-100/40 text-xs truncate"><?php echo e($tech['zone']); ?></p>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <?php if($tech['interventions'] > 0): ?>
                    <span class="w-5 h-5 rounded-full bg-amber-500/15 text-amber-300 text-[10px] font-bold
                                  flex items-center justify-center"><?php echo e($tech['interventions']); ?></span>
                    <?php endif; ?>
                    <span class="w-2 h-2 rounded-full <?php echo e($ts['dot']); ?>"></span>
                    <span class="text-xs <?php echo e($ts['text']); ?> font-medium hidden sm:inline"><?php echo e($ts['label']); ?></span>
                </div>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
</div>


<div class="grid lg:grid-cols-2 gap-6 fade-up" style="animation-delay:.3s">

    
    <div class="glass rounded-2xl overflow-hidden">
        <div class="px-6 py-4 border-b border-white/5">
            <h2 class="text-white font-display font-bold">Gestionnaires</h2>
            <p class="text-cyan-100/50 text-xs mt-0.5"><?php echo e(count($managers)); ?> responsables de zones</p>
        </div>
        <div class="divide-y divide-white/[.04]">
            <?php $__currentLoopData = $managers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mgr): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="flex items-center gap-3 px-5 py-3.5 hover:bg-white/[.025] transition-colors">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-blue-500/20 to-blue-700/20
                             border border-blue-400/15 flex items-center justify-center text-xs font-bold text-blue-300 shrink-0">
                    <?php echo e($mgr['initials']); ?>

                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-white text-sm font-semibold truncate"><?php echo e($mgr['name']); ?></p>
                    <p class="text-cyan-100/40 text-xs flex items-center gap-1">
                        <i data-lucide="map-pin" class="w-3 h-3"></i><?php echo e($mgr['zone']); ?>

                    </p>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <?php if($mgr['incidents'] > 0): ?>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/15 text-amber-300">
                        <?php echo e($mgr['incidents']); ?> incidents
                    </span>
                    <?php endif; ?>
                    <span class="w-2 h-2 rounded-full <?php echo e($mgr['status']==='active' ? 'bg-teal-400' : 'bg-slate-500'); ?>"></span>
                </div>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>

    
    <div class="glass rounded-2xl overflow-hidden">
        <div class="px-6 py-4 border-b border-white/5">
            <h2 class="text-white font-display font-bold">État des zones</h2>
            <p class="text-cyan-100/50 text-xs mt-0.5">Incidents actifs par zone</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-white/5">
                        <th class="px-4 py-2.5 text-left text-[11px] font-semibold text-cyan-100/40">Zone</th>
                        <th class="px-3 py-2.5 text-center text-[11px] font-semibold text-cyan-100/40">Total</th>
                        <th class="px-3 py-2.5 text-center text-[11px] font-semibold text-cyan-100/40">En cours</th>
                        <th class="px-3 py-2.5 text-center text-[11px] font-semibold text-cyan-100/40">Résolus</th>
                        <th class="px-3 py-2.5 text-center text-[11px] font-semibold text-cyan-100/40">Statut</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/[.04]">
                    <?php $__currentLoopData = $zoneOverview; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $zov): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $zs = match($zov['status']) {
                            'critical' => ['dot'=>'bg-red-400 animate-pulse', 'text'=>'text-red-300',  'label'=>'Critique'],
                            'alert'    => ['dot'=>'bg-amber-400',             'text'=>'text-amber-300','label'=>'Alerte'],
                            default    => ['dot'=>'bg-teal-400',              'text'=>'text-teal-300', 'label'=>'Stable'],
                        };
                    ?>
                    <tr class="hover:bg-white/[.02] transition-colors">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <span><?php echo e($zov['emoji']); ?></span>
                                <span class="text-white text-xs font-semibold"><?php echo e($zov['zone']); ?></span>
                            </div>
                        </td>
                        <td class="px-3 py-3 text-center text-xs font-bold text-white"><?php echo e($zov['total']); ?></td>
                        <td class="px-3 py-3 text-center">
                            <span class="text-xs font-bold <?php echo e($zov['in_progress']>0 ? 'text-amber-300' : 'text-cyan-100/30'); ?>">
                                <?php echo e($zov['in_progress']); ?>

                            </span>
                        </td>
                        <td class="px-3 py-3 text-center">
                            <span class="text-xs font-bold text-teal-300"><?php echo e($zov['resolved']); ?></span>
                        </td>
                        <td class="px-3 py-3 text-center">
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold
                                         <?php echo e($zov['status']==='critical' ? 'bg-red-500/15' : ($zov['status']==='alert' ? 'bg-amber-500/15' : 'bg-teal-500/15')); ?>

                                         <?php echo e($zs['text']); ?>">
                                <span class="w-1.5 h-1.5 rounded-full <?php echo e($zs['dot']); ?>"></span>
                                <?php echo e($zs['label']); ?>

                            </span>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
    </div>
</div>


<div class="grid lg:grid-cols-3 gap-6 fade-up" style="animation-delay:.35s">

    
    <div class="glass rounded-2xl overflow-hidden lg:col-span-2">
        <div class="px-6 py-4 border-b border-white/5 flex items-center justify-between">
            <div>
                <h2 class="text-white font-display font-bold">Historique des résolutions</h2>
                <p class="text-cyan-100/50 text-xs mt-0.5">Parcours complet citoyen → résolution</p>
            </div>
            <a href="<?php echo e(route('admin.logs')); ?>"
               class="text-xs text-cyan-400 hover:text-cyan-300 font-semibold transition-colors">
                Tout voir →
            </a>
        </div>
        <div class="divide-y divide-white/[.04]">
            <?php $__currentLoopData = $resHistory; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $res): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="px-5 py-4 hover:bg-white/[.02] transition-colors">
                <div class="flex items-start justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <span class="text-[10px] font-mono font-bold text-teal-300"><?php echo e($res['id']); ?></span>
                        <span class="text-white text-xs font-semibold"><?php echo e($res['type']); ?></span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-teal-500/15 text-teal-300">Résolu</span>
                    </div>
                    <span class="text-[11px] font-semibold text-teal-300/70">⏱ <?php echo e($res['duration']); ?></span>
                </div>
                
                <div class="relative flex items-center justify-between mb-3">
                    <div class="absolute top-2.5 left-2.5 right-2.5 h-px bg-gradient-to-r from-cyan-500/20 via-teal-400/40 to-teal-500/20"></div>
                    <?php $__currentLoopData = ['Signalé'=>$res['reported_at'],'Affecté'=>$res['assigned_at'],'Démarré'=>$res['started_at'],'Résolu'=>$res['resolved_at']]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $step=>$time): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="flex flex-col items-center relative z-10">
                        <div class="w-5 h-5 rounded-full flex items-center justify-center mb-1
                                     <?php echo e($step==='Résolu' ? 'bg-teal-500 border-2 border-teal-400' : 'bg-cyan-500/30 border border-cyan-400/40'); ?>">
                            <?php if($step==='Résolu'): ?>
                            <i data-lucide="check" class="w-2.5 h-2.5 text-white"></i>
                            <?php else: ?>
                            <span class="w-1.5 h-1.5 rounded-full bg-cyan-400"></span>
                            <?php endif; ?>
                        </div>
                        <p class="text-[9px] text-cyan-100/45 text-center leading-tight"><?php echo e($step); ?></p>
                        <p class="text-[8px] text-cyan-100/25 text-center"><?php echo e($time); ?></p>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
                <div class="flex items-center gap-4 text-[11px] text-cyan-100/45">
                    <span class="flex items-center gap-1"><i data-lucide="map-pin" class="w-3 h-3"></i><?php echo e($res['zone']); ?></span>
                    <span class="flex items-center gap-1"><i data-lucide="wrench" class="w-3 h-3"></i><?php echo e($res['technician']); ?></span>
                    <span class="flex items-center gap-1"><i data-lucide="briefcase" class="w-3 h-3"></i><?php echo e($res['manager']); ?></span>
                </div>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>

    
    <div class="space-y-4">
        
        <div class="glass rounded-2xl p-5">
            <h3 class="text-white font-display font-bold text-sm mb-4">Performance globale</h3>
            <div class="space-y-3">
                <?php $__currentLoopData = [
                    ['label'=>'Total résolus',       'val'=>$perf['total_resolved'],      'color'=>'text-teal-300'],
                    ['label'=>'Temps moy. résolution','val'=>$perf['avg_resolution_time'], 'color'=>'text-cyan-300'],
                    ['label'=>'Taux de résolution',  'val'=>$perf['resolution_rate'].'%', 'color'=>'text-teal-300'],
                    ['label'=>'Ce mois',             'val'=>$perf['resolved_this_month'].'/'.$perf['incidents_this_month'],'color'=>'text-blue-300'],
                    ['label'=>'Tps moy. réponse',    'val'=>$perf['avg_response_time'],   'color'=>'text-cyan-300'],
                ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pm): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="flex items-center justify-between py-1.5 border-b border-white/5 last:border-0">
                    <span class="text-xs text-cyan-100/55"><?php echo e($pm['label']); ?></span>
                    <span class="text-sm font-bold <?php echo e($pm['color']); ?>"><?php echo e($pm['val']); ?></span>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>

        
        <div class="glass rounded-2xl p-5">
            <h3 class="text-white font-display font-bold text-sm mb-1">Incidents · 7 jours</h3>
            <div class="flex items-center gap-3 mb-3 text-[10px] text-cyan-100/50">
                <span class="flex items-center gap-1"><span class="w-3 h-0.5 rounded bg-red-400/70 inline-block"></span>Créés</span>
                <span class="flex items-center gap-1"><span class="w-3 h-0.5 rounded bg-teal-400/70 inline-block"></span>Résolus</span>
            </div>
            <div class="relative" style="height:64px">
                <svg id="admin-spark" width="100%" height="64" viewBox="0 0 300 64"
                     preserveAspectRatio="none" class="overflow-visible"></svg>
                <div class="flex justify-between mt-1">
                    <?php $__currentLoopData = $inc7d['labels']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lbl): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <span class="text-[9px] text-cyan-100/30"><?php echo e($lbl); ?></span>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="glass rounded-2xl overflow-hidden fade-up" style="animation-delay:.4s">
    <div class="px-6 py-4 border-b border-white/5 flex flex-wrap items-center gap-3 justify-between">
        <div>
            <h2 class="text-white font-display font-bold">Gestion des comptes</h2>
            <p class="text-cyan-100/50 text-xs mt-0.5">
                <?php echo e(count($accounts)); ?> comptes ·
                <span class="text-teal-400"><?php echo e(count(array_filter($accounts,fn($a)=>$a['status']==='active'))); ?> actifs</span> ·
                <span class="text-red-400"><?php echo e(count(array_filter($accounts,fn($a)=>$a['status']==='blocked'))); ?> bloqués</span>
            </p>
        </div>
        <div class="flex gap-1" id="acc-tabs">
            <?php $__currentLoopData = ['all'=>'Tous','citizen'=>'Citoyens','technician'=>'Techniciens','manager'=>'Gestionnaires']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $fv=>$fl): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <button onclick="filterAccounts('<?php echo e($fv); ?>')" data-af="<?php echo e($fv); ?>"
                    class="acc-tab px-2.5 py-1 rounded-lg text-[11px] font-semibold border transition-all
                           <?php echo e($fv==='all' ? 'tab-active' : 'tab-inactive'); ?>">
                <?php echo e($fl); ?>

            </button>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-white/5 text-left">
                    <th class="px-5 py-2.5 text-[11px] font-semibold text-cyan-100/40">Utilisateur</th>
                    <th class="px-3 py-2.5 text-[11px] font-semibold text-cyan-100/40 text-center">Rôle</th>
                    <th class="px-3 py-2.5 text-[11px] font-semibold text-cyan-100/40 hidden md:table-cell">Zone</th>
                    <th class="px-3 py-2.5 text-[11px] font-semibold text-cyan-100/40 text-center">Statut</th>
                    <th class="px-3 py-2.5 text-[11px] font-semibold text-cyan-100/40 text-center hidden sm:table-cell">Signalements</th>
                    <th class="px-3 py-2.5 text-[11px] font-semibold text-cyan-100/40 hidden lg:table-cell">Dernière connexion</th>
                    <th class="px-3 py-2.5 text-[11px] font-semibold text-cyan-100/40 text-right">Actions</th>
                </tr>
            </thead>
            <tbody id="acc-tbody" class="divide-y divide-white/[.04]">
                <?php $__currentLoopData = $accounts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $acc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $rs = [
                        'admin'      => ['bg'=>'bg-violet-500/15','text'=>'text-violet-300','label'=>'Admin',        'av'=>'from-violet-500/30 to-violet-700/30','border'=>'border-violet-400/20'],
                        'manager'    => ['bg'=>'bg-blue-500/15',  'text'=>'text-blue-300',  'label'=>'Gestionnaire', 'av'=>'from-blue-500/30 to-blue-700/30',   'border'=>'border-blue-400/20'],
                        'technician' => ['bg'=>'bg-cyan-500/15',  'text'=>'text-cyan-300',  'label'=>'Technicien',   'av'=>'from-cyan-500/30 to-cyan-700/30',   'border'=>'border-cyan-400/20'],
                        'citizen'    => ['bg'=>'bg-teal-500/15',  'text'=>'text-teal-300',  'label'=>'Citoyen',      'av'=>'from-teal-500/30 to-teal-700/30',   'border'=>'border-teal-400/20'],
                    ][$acc['role']];
                ?>
                <tr class="acc-row hover:bg-white/[.025] transition-colors" data-role="<?php echo e($acc['role']); ?>">
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-gradient-to-br <?php echo e($rs['av']); ?> border <?php echo e($rs['border']); ?>

                                         flex items-center justify-center text-xs font-bold <?php echo e($rs['text']); ?> shrink-0">
                                <?php echo e($acc['initials']); ?>

                            </div>
                            <div class="min-w-0">
                                <p class="text-white text-xs font-semibold truncate"><?php echo e($acc['name']); ?></p>
                                <p class="text-cyan-100/40 text-[10px] truncate"><?php echo e($acc['email']); ?></p>
                            </div>
                        </div>
                    </td>
                    <td class="px-3 py-3 text-center">
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold <?php echo e($rs['bg']); ?> <?php echo e($rs['text']); ?>">
                            <?php echo e($rs['label']); ?>

                        </span>
                    </td>
                    <td class="px-3 py-3 hidden md:table-cell">
                        <span class="text-xs text-cyan-100/55 truncate max-w-[100px] block"><?php echo e($acc['zone']); ?></span>
                    </td>
                    <td class="px-3 py-3 text-center">
                        <span id="sbadge-<?php echo e($acc['id']); ?>"
                              class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold
                                     <?php echo e($acc['status']==='active' ? 'bg-teal-500/15 text-teal-300' : 'bg-red-500/15 text-red-300'); ?>">
                            <span class="w-1.5 h-1.5 rounded-full <?php echo e($acc['status']==='active' ? 'bg-teal-400' : 'bg-red-400'); ?>"></span>
                            <?php echo e($acc['status']==='active' ? 'Actif' : 'Bloqué'); ?>

                        </span>
                    </td>
                    <td class="px-3 py-3 text-center hidden sm:table-cell">
                        <?php if($acc['reports'] > 0): ?>
                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-amber-500/15 text-amber-300 text-[11px] font-bold">
                            <?php echo e($acc['reports']); ?>

                        </span>
                        <?php else: ?>
                        <span class="text-cyan-100/25 text-xs">—</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-3 py-3 hidden lg:table-cell">
                        <span class="text-[11px] text-cyan-100/40"><?php echo e($acc['last_seen']); ?></span>
                    </td>
                    <td class="px-3 py-3">
                        <div class="flex items-center justify-end gap-1.5">
                            <a href="<?php echo e(route('admin.users.index')); ?>"
                               class="glass p-1.5 rounded-lg text-cyan-400/60 hover:text-cyan-300 transition-colors" title="Voir">
                                <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                            </a>
                            <button onclick="showToast('Modifier <?php echo e($acc['name']); ?>', 'info')"
                                    class="glass p-1.5 rounded-lg text-blue-400/60 hover:text-blue-300 transition-colors" title="Modifier">
                                <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                            </button>
                            <?php if($acc['role'] !== 'admin'): ?>
                            <button id="abtn-<?php echo e($acc['id']); ?>"
                                    onclick="toggleBlock(<?php echo e($acc['id']); ?>, '<?php echo e($acc['status']); ?>', '<?php echo e(addslashes($acc['name'])); ?>')"
                                    class="glass p-1.5 rounded-lg border transition-all <?php echo e($acc['status']==='active' ? 'btn-block' : 'btn-unblock'); ?>"
                                    title="<?php echo e($acc['status']==='active' ? 'Bloquer' : 'Débloquer'); ?>">
                                <i data-lucide="<?php echo e($acc['status']==='active' ? 'ban' : 'unlock'); ?>" class="w-3.5 h-3.5"></i>
                            </button>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>
    <div id="acc-empty" class="hidden py-10 text-center">
        <i data-lucide="users" class="w-10 h-10 text-cyan-400/30 mx-auto mb-2"></i>
        <p class="text-cyan-100/40 text-sm">Aucun compte dans cette catégorie</p>
    </div>
    <div class="px-5 py-3 border-t border-white/5 flex items-center justify-between">
        <span class="text-[11px] text-cyan-100/35" id="acc-count"><?php echo e(count($accounts)); ?> comptes</span>
        <a href="<?php echo e(route('admin.users.index')); ?>" class="text-xs text-cyan-400 hover:text-cyan-300 font-semibold transition-colors">
            Gestion complète →
        </a>
    </div>
</div>

</div>


<div id="inc-modal" class="hidden fixed inset-0 z-[200] flex items-end sm:items-center justify-center p-4 bg-black/65 backdrop-blur-sm"
     onclick="if(event.target===this) closeIncModal()">
    <div class="glass-strong rounded-2xl w-full max-w-lg overflow-hidden animate-scale-in" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between px-6 py-4 border-b border-white/5">
            <h3 class="text-white font-display font-bold" id="inc-modal-title">Détail incident</h3>
            <button onclick="closeIncModal()" class="text-cyan-100/50 hover:text-white transition-colors">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <div id="inc-modal-body" class="p-6 space-y-4 overflow-y-auto max-h-[70vh]"></div>
        <div class="px-6 py-4 border-t border-white/5 flex justify-end gap-2">
            <button onclick="closeIncModal()"
                    class="glass px-4 py-2 rounded-xl text-sm text-cyan-100/60 hover:text-white font-semibold transition-colors">
                Fermer
            </button>
            <button onclick="showToast('Assignation technicien — frontend uniquement', 'info'); closeIncModal()"
                    class="bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500
                           text-white text-sm font-semibold px-4 py-2 rounded-xl transition-all">
                Assigner technicien
            </button>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV/XN/WLI=" crossorigin=""></script>
<script>
/* ── Data ────────────────────────────────────────────────── */
const MAP_ZONES    = <?php echo json_encode($mapZones, 15, 512) ?>;
const RECS         = <?php echo json_encode($reclamations, 15, 512) ?>;
const INC7D        = <?php echo json_encode($inc7d, 15, 512) ?>;

/* ── Map ─────────────────────────────────────────────────── */
(function() {
    const map = L.map('admin-map', {
        center:[34.0, 9.4], zoom:6,
        zoomControl:false, attributionControl:false, scrollWheelZoom:false
    });
    L.control.zoom({ position:'topright' }).addTo(map);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom:18 }).addTo(map);

    // Zone background circles
    const zc = { normal:'#2dd4bf', alert:'#fbbf24', critical:'#ef4444' };
    MAP_ZONES.forEach(z => {
        const c = zc[z.status] ?? '#2dd4bf', size=26, half=13;
        const svg=`<svg xmlns="http://www.w3.org/2000/svg" width="${size}" height="${size}" viewBox="0 0 ${size} ${size}">
            <circle cx="${half}" cy="${half}" r="${half-2}" fill="${c}" fill-opacity="0.12" stroke="${c}" stroke-width="1.5" stroke-dasharray="3,2"/>
        </svg>`;
        L.marker([z.lat, z.lng], {
            icon: L.divIcon({ html:svg, iconSize:[size,size], iconAnchor:[half,half], className:'' }),
            zIndexOffset:-100
        }).addTo(map)
         .bindTooltip(`<b style="color:#f0fdff;font-size:11px">${z.emoji} ${z.name}</b><br>
                       <span style="color:${c};font-size:10px">${z.quality}% · ${z.incidents} incident(s)</span>`,
                      { sticky:true });
    });

    // Incident markers: pending=blue, in_progress=orange, resolved=green
    const ic = { pending:'#3b82f6', in_progress:'#f97316', resolved:'#2dd4bf' };
    const ii = { pending:'!', in_progress:'🔧', resolved:'✓' };
    RECS.forEach((r, idx) => {
        const c = ic[r.status] ?? '#9ca3af';
        const pulse = r.status !== 'resolved';
        const size = r.status==='pending'?36:r.status==='in_progress'?34:28, half=size/2;
        const svg=`<svg xmlns="http://www.w3.org/2000/svg" width="${size}" height="${size}" viewBox="0 0 ${size} ${size}">
            ${pulse?`<circle cx="${half}" cy="${half}" r="${half}" fill="${c}" opacity="0.18">
                <animate attributeName="r" from="${half}" to="${size}" dur="2s" repeatCount="indefinite"/>
                <animate attributeName="opacity" from="0.25" to="0" dur="2s" repeatCount="indefinite"/>
            </circle>`:''}
            <circle cx="${half}" cy="${half}" r="${half-3}" fill="${c}" fill-opacity="0.22" stroke="${c}" stroke-width="2"/>
            <circle cx="${half}" cy="${half}" r="${half-9}" fill="${c}" fill-opacity="0.9"/>
            <text x="${half}" y="${half+4}" text-anchor="middle" font-size="10" fill="white">${ii[r.status]??'•'}</text>
        </svg>`;
        L.marker([r.lat, r.lng], {
            icon: L.divIcon({ html:svg, iconSize:[size,size], iconAnchor:[half,half], className:'' }),
            zIndexOffset:100
        }).addTo(map)
         .on('click', () => openRecDetail(idx));
    });
})();

/* ── Sparkline 7 jours ────────────────────────────────────── */
(function() {
    const created  = INC7D.created;
    const resolved = INC7D.resolved;
    const labels   = INC7D.labels;
    const svg = document.getElementById('admin-spark');
    if (!svg) return;
    const W=300, H=64, pad=8;
    const max=Math.max(...created,...resolved)*1.1;
    const sx=i=>pad+(i/(created.length-1))*(W-pad*2);
    const sy=v=>pad+(1-v/max)*(H-pad*2);

    const defs=document.createElementNS('http://www.w3.org/2000/svg','defs');
    defs.innerHTML=`
        <linearGradient id="spR" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="#ef4444" stop-opacity="0.4"/><stop offset="100%" stop-color="#ef4444" stop-opacity="0"/></linearGradient>
        <linearGradient id="spG" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="#2dd4bf" stop-opacity="0.4"/><stop offset="100%" stop-color="#2dd4bf" stop-opacity="0"/></linearGradient>`;
    svg.appendChild(defs);

    const drawLine=(data,color,grad)=>{
        const pts=data.map((v,i)=>({x:sx(i),y:sy(v)}));
        const d=pts.map((p,i)=>`${i===0?'M':'L'}${p.x},${p.y}`).join(' ');
        const area=document.createElementNS('http://www.w3.org/2000/svg','path');
        area.setAttribute('d',d+` L${pts.at(-1).x},${H} L${pts[0].x},${H} Z`);
        area.setAttribute('fill',`url(#${grad})`);
        svg.appendChild(area);
        const line=document.createElementNS('http://www.w3.org/2000/svg','path');
        line.setAttribute('d',d); line.setAttribute('fill','none');
        line.setAttribute('stroke',color); line.setAttribute('stroke-width','2');
        line.setAttribute('stroke-linecap','round'); line.setAttribute('opacity','.9');
        svg.appendChild(line);
    };
    drawLine(created,'#ef4444','spR');
    drawLine(resolved,'#2dd4bf','spG');
})();

/* ── Filter incidents ────────────────────────────────────── */
function filterRec(filter) {
    document.querySelectorAll('.rec-tab').forEach(b=>{
        b.classList.toggle('tab-active',   b.dataset.f===filter);
        b.classList.toggle('tab-inactive', b.dataset.f!==filter);
    });
    let vis=0;
    document.querySelectorAll('.rec-row').forEach(row=>{
        let show=false;
        if(filter==='all') show=true;
        else if(filter==='critical') show=row.dataset.priority==='critical'&&row.dataset.status!=='resolved';
        else show=row.dataset.status===filter;
        row.style.display=show?'':'none';
        if(show)vis++;
    });
    document.getElementById('rec-empty').classList.toggle('hidden',vis>0);
    document.getElementById('rec-count').textContent=vis+' incident'+(vis>1?'s':'');
}

/* ── Filter accounts ─────────────────────────────────────── */
function filterAccounts(f) {
    document.querySelectorAll('.acc-tab').forEach(b=>{
        b.classList.toggle('tab-active',   b.dataset.af===f);
        b.classList.toggle('tab-inactive', b.dataset.af!==f);
    });
    let vis=0;
    document.querySelectorAll('.acc-row').forEach(row=>{
        const show=f==='all'||row.dataset.role===f;
        row.style.display=show?'':'none';
        if(show)vis++;
    });
    document.getElementById('acc-empty').classList.toggle('hidden',vis>0);
    document.getElementById('acc-count').textContent=vis+' compte'+(vis>1?'s':'');
}

/* ── Block/Unblock ──────────────────────────────────────── */
function toggleBlock(id, status, name) {
    if(!confirm(`Voulez-vous ${status==='active'?'bloquer':'débloquer'} le compte de ${name} ?`)) return;
    const badge=document.getElementById('sbadge-'+id);
    const btn  =document.getElementById('abtn-'+id);
    if(status==='active'){
        badge.className='inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-500/15 text-red-300';
        badge.innerHTML='<span class="w-1.5 h-1.5 rounded-full bg-red-400"></span>Bloqué';
        btn.className=btn.className.replace('btn-block','btn-unblock');
        btn.title='Débloquer';
        btn.innerHTML='<i data-lucide="unlock" style="width:14px;height:14px"></i>';
        btn.setAttribute('onclick',`toggleBlock(${id},'blocked','${name.replace(/'/g,"\\'")}') `);
        showToast(`${name} bloqué`, 'error');
    } else {
        badge.className='inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-teal-500/15 text-teal-300';
        badge.innerHTML='<span class="w-1.5 h-1.5 rounded-full bg-teal-400"></span>Actif';
        btn.className=btn.className.replace('btn-unblock','btn-block');
        btn.title='Bloquer';
        btn.innerHTML='<i data-lucide="ban" style="width:14px;height:14px"></i>';
        btn.setAttribute('onclick',`toggleBlock(${id},'active','${name.replace(/'/g,"\\'")}') `);
        showToast(`${name} débloqué`, 'success');
    }
    if(typeof lucide!=='undefined') lucide.createIcons();
}

/* ── Modal incident detail ──────────────────────────────── */
function openRecDetail(idx) {
    const r = RECS[idx]; if(!r) return;
    const sc={'pending':'#3b82f6','in_progress':'#f97316','resolved':'#2dd4bf'};
    const sl={'pending':'Non traité','in_progress':'En cours','resolved':'Résolu'};
    const pc={'critical':'#ef4444','medium':'#fbbf24','low':'#38bdf8'};
    const pl={'critical':'Critique','medium':'Moyenne','low':'Faible'};
    document.getElementById('inc-modal-title').textContent=r.id+' — '+r.type;
    document.getElementById('inc-modal-body').innerHTML=`
        <div style="background:rgba(255,255,255,.03);border-radius:12px;padding:12px 14px;margin-bottom:12px">
            <p style="color:rgba(156,200,216,.6);font-size:11px;margin:0 0 4px">Description</p>
            <p style="color:#f0fdff;font-size:13px;margin:0;line-height:1.5">${r.description}</p>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px">
            ${ib('Zone',         r.zone,                            '#38bdf8')}
            ${ib('Adresse',      r.address,                         'rgba(156,200,216,.7)')}
            ${ib('Citoyen',      r.citizen,                         '#2dd4bf')}
            ${ib('Priorité',     pl[r.priority]??r.priority,        pc[r.priority]??'#9ca3af')}
            ${ib('Statut',       sl[r.status]??r.status,            sc[r.status]??'#9ca3af')}
            ${ib('Technicien',   r.technician??'Non assigné',       r.technician?'#5ee5f7':'rgba(156,200,216,.4)')}
            ${ib('Gestionnaire', r.manager??'Non assigné',          r.manager?'#818cf8':'rgba(156,200,216,.4)')}
            ${ib('Signalé le',   r.created_at,                      'rgba(156,200,216,.6)')}
        </div>`;
    document.getElementById('inc-modal').classList.remove('hidden');
    document.body.style.overflow='hidden';
    if(typeof lucide!=='undefined') lucide.createIcons();
}
function ib(label,value,color) {
    return `<div style="background:rgba(255,255,255,.03);border-radius:10px;padding:10px 12px">
        <p style="color:rgba(156,200,216,.5);font-size:10px;margin:0 0 3px">${label}</p>
        <p style="color:${color};font-size:12px;font-weight:600;margin:0">${value}</p>
    </div>`;
}
function closeIncModal(){
    document.getElementById('inc-modal').classList.add('hidden');
    document.body.style.overflow='';
}
document.addEventListener('keydown', e=>{ if(e.key==='Escape') closeIncModal(); });

/* ── Init Lucide ─────────────────────────────────────────── */
document.addEventListener('DOMContentLoaded', ()=>{
    if(typeof lucide!=='undefined') lucide.createIcons();
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\monta\Downloads\Aqua_Secure-main\Aqua_Secure-main\resources\views/admin/dashboard.blade.php ENDPATH**/ ?>