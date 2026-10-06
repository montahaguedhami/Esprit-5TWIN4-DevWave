<?php $__env->startSection('title', 'Tableau de bord Gestionnaire — AquaSecure'); ?>

<?php
    use App\Data\PlaceholderData;
    $user        = session('user', ['name' => 'Ines Mansouri', 'role' => 'manager']);
    $zones       = PlaceholderData::mapZones();
    $stats       = PlaceholderData::stats();
    $technicians = PlaceholderData::adminTechnicians();
    $reclamations= PlaceholderData::adminAllReclamations();
    $monthly     = PlaceholderData::analyticsMonthly();

    $firstName   = explode(' ', $user['name'])[0];

    $zoneNormal  = count(array_filter($zones, fn($z)=>$z['status']==='normal'));
    $zoneAlert   = count(array_filter($zones, fn($z)=>$z['status']==='alert'));
    $zoneCrit    = count(array_filter($zones, fn($z)=>$z['status']==='critical'));

    $totalInc    = count($reclamations);
    $inProgressInc = count(array_filter($reclamations, fn($r)=>$r['status']==='in_progress'));
    $pendingInc  = count(array_filter($reclamations, fn($r)=>$r['status']==='pending'));
    $resolvedInc = count(array_filter($reclamations, fn($r)=>$r['status']==='resolved'));

    $techOnMission = count(array_filter($technicians, fn($t)=>$t['status']==='on_mission'));
    $techAvail     = count(array_filter($technicians, fn($t)=>$t['status']==='available'));

    $statusStyle = [
        'pending'     => ['bg'=>'bg-red-500/15',   'text'=>'text-red-300',   'dot'=>'bg-red-400 animate-pulse','label'=>'Non traité'],
        'in_progress' => ['bg'=>'bg-amber-500/15', 'text'=>'text-amber-300', 'dot'=>'bg-amber-400',            'label'=>'En cours'],
        'resolved'    => ['bg'=>'bg-teal-500/15',  'text'=>'text-teal-300',  'dot'=>'bg-teal-400',             'label'=>'Résolu'],
    ];
    $priorityStyle = [
        'critical' => ['bg'=>'bg-red-500/15',  'text'=>'text-red-300',  'label'=>'Critique'],
        'medium'   => ['bg'=>'bg-amber-500/15','text'=>'text-amber-300','label'=>'Moyenne'],
        'low'      => ['bg'=>'bg-blue-500/15', 'text'=>'text-blue-300', 'label'=>'Faible'],
    ];
?>

<?php $__env->startPush('styles'); ?>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
      integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<style>
@keyframes barSlide { from { width: 0 } }
.bar-grow { animation: barSlide .9s cubic-bezier(.22,1,.36,1) both; }
#mgr-map { height: 340px; }
.leaflet-tile-pane { filter: brightness(.68) saturate(.6) hue-rotate(185deg); }
.leaflet-control-zoom a { background:rgba(6,21,37,.9)!important; border-color:rgba(5,191,219,.25)!important; color:#7ce8f7!important; }
.leaflet-control-attribution { display:none!important; }
.leaflet-popup-content-wrapper { background:rgba(6,21,37,.97)!important; border:1px solid rgba(5,191,219,.3)!important; border-radius:12px!important; color:#f0fdff!important; padding:0!important; }
.leaflet-popup-tip { background:rgba(6,21,37,.97)!important; }
.leaflet-popup-content { margin:0!important; padding:0!important; min-width:200px; }
.leaflet-popup-close-button { color:rgba(156,200,216,.6)!important; }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="min-h-screen">


<nav class="sticky top-0 z-50 glass-strong px-4 sm:px-6 py-3 flex items-center justify-between">
    <div class="flex items-center gap-2">
        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-cyan-400 to-blue-600 flex items-center justify-center">
            <i data-lucide="droplet" class="w-5 h-5 text-white"></i>
        </div>
        <span class="font-display font-bold text-white hidden sm:block">AquaSecure</span>
        <span class="hidden sm:inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-500/15 text-blue-300 border border-blue-400/20">
            Espace Gestionnaire
        </span>
    </div>
    <div class="flex items-center gap-2">
        <button onclick="toggleTheme()"
                class="glass p-2 rounded-lg text-cyan-300 hover:text-white transition-colors">
            <i data-lucide="sun"  class="w-4 h-4 sun-icon  hidden"></i>
            <i data-lucide="moon" class="w-4 h-4 moon-icon"></i>
        </button>
        <?php if (isset($component)) { $__componentOriginal7169a5b356633be5dafc74bf7a8eb300 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal7169a5b356633be5dafc74bf7a8eb300 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.notification-center','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('notification-center'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal7169a5b356633be5dafc74bf7a8eb300)): ?>
<?php $attributes = $__attributesOriginal7169a5b356633be5dafc74bf7a8eb300; ?>
<?php unset($__attributesOriginal7169a5b356633be5dafc74bf7a8eb300); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal7169a5b356633be5dafc74bf7a8eb300)): ?>
<?php $component = $__componentOriginal7169a5b356633be5dafc74bf7a8eb300; ?>
<?php unset($__componentOriginal7169a5b356633be5dafc74bf7a8eb300); ?>
<?php endif; ?>
        <?php if (isset($component)) { $__componentOriginal42edc48abdcb6c65aa0760095ea712dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal42edc48abdcb6c65aa0760095ea712dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.user-menu','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('user-menu'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal42edc48abdcb6c65aa0760095ea712dd)): ?>
<?php $attributes = $__attributesOriginal42edc48abdcb6c65aa0760095ea712dd; ?>
<?php unset($__attributesOriginal42edc48abdcb6c65aa0760095ea712dd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal42edc48abdcb6c65aa0760095ea712dd)): ?>
<?php $component = $__componentOriginal42edc48abdcb6c65aa0760095ea712dd; ?>
<?php unset($__componentOriginal42edc48abdcb6c65aa0760095ea712dd); ?>
<?php endif; ?>
    </div>
</nav>

<div class="container mx-auto px-4 py-6 max-w-7xl space-y-6 animate-fade-in-up">

    
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-display font-bold text-white">
                Bonjour, <?php echo e($firstName); ?> 👋
            </h1>
            <p class="text-cyan-100/55 text-sm mt-0.5">
                Gestionnaire · Supervision opérationnelle du réseau
            </p>
        </div>
        <div class="flex gap-2">
            <a href="<?php echo e(route('manager.analytics')); ?>"
               class="glass px-4 py-2 rounded-xl text-sm text-cyan-300 hover:text-white font-semibold
                      flex items-center gap-2 transition-all hover:border-cyan-400/40">
                <i data-lucide="bar-chart-2" class="w-4 h-4"></i>
                <span class="hidden sm:inline">Analytics</span>
            </a>
            <a href="<?php echo e(route('manager.map')); ?>"
               class="glass px-4 py-2 rounded-xl text-sm text-cyan-300 hover:text-white font-semibold
                      flex items-center gap-2 transition-all hover:border-cyan-400/40">
                <i data-lucide="map" class="w-4 h-4"></i>
                <span class="hidden sm:inline">Carte complète</span>
            </a>
        </div>
    </div>

    
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <?php $__currentLoopData = [
            ['val'=>count($zones),  'label'=>'Zones surveillées','icon'=>'map-pin',      'bg'=>'bg-cyan-500/10',  'ic'=>'text-cyan-400',  'sub'=>$zoneNormal.' normales'],
            ['val'=>$totalInc,      'label'=>'Incidents',        'icon'=>'alert-triangle','bg'=>'bg-red-500/10',   'ic'=>'text-red-400',   'sub'=>$pendingInc.' non traités'],
            ['val'=>$inProgressInc, 'label'=>'Interventions',    'icon'=>'loader',        'bg'=>'bg-amber-500/10', 'ic'=>'text-amber-400', 'sub'=>'en cours'],
            ['val'=>$techOnMission, 'label'=>'Techniciens',      'icon'=>'wrench',        'bg'=>'bg-teal-500/10',  'ic'=>'text-teal-400',  'sub'=>$techAvail.' disponibles'],
        ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $kpi): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="glass rounded-2xl p-4 hover-lift">
            <div class="w-10 h-10 rounded-xl <?php echo e($kpi['bg']); ?> flex items-center justify-center mb-3">
                <i data-lucide="<?php echo e($kpi['icon']); ?>" class="w-5 h-5 <?php echo e($kpi['ic']); ?>"></i>
            </div>
            <p class="text-2xl font-display font-bold text-white"><?php echo e($kpi['val']); ?></p>
            <p class="text-xs font-semibold text-cyan-100/60 mt-0.5"><?php echo e($kpi['label']); ?></p>
            <p class="text-[10px] text-cyan-100/35"><?php echo e($kpi['sub']); ?></p>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    
    <div class="glass rounded-2xl overflow-hidden">
        <div class="px-5 py-4 border-b border-white/5 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-white font-display font-bold">État du réseau en temps réel</h2>
                <p class="text-cyan-100/50 text-xs mt-0.5"><?php echo e(count($zones)); ?> zones · Incidents actifs</p>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                <?php $__currentLoopData = [
                    ['c'=>'bg-teal-400',             't'=>'text-teal-300',  'l'=>'Normal',   'n'=>$zoneNormal],
                    ['c'=>'bg-amber-400',             't'=>'text-amber-300','l'=>'Alerte',   'n'=>$zoneAlert],
                    ['c'=>'bg-red-400 animate-pulse', 't'=>'text-red-300',  'l'=>'Critique', 'n'=>$zoneCrit],
                ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $leg): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-white/5 border border-white/10">
                    <span class="w-2 h-2 rounded-full <?php echo e($leg['c']); ?>"></span>
                    <span class="text-xs font-semibold <?php echo e($leg['t']); ?>"><?php echo e($leg['n']); ?> <?php echo e($leg['l']); ?></span>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <a href="<?php echo e(route('manager.map')); ?>"
                   class="glass px-3 py-1.5 rounded-lg text-xs text-cyan-300 hover:text-white font-semibold
                          flex items-center gap-1 transition-all hover:border-cyan-400/40">
                    <i data-lucide="maximize-2" class="w-3.5 h-3.5"></i>
                    <span class="hidden sm:inline">Plein écran</span>
                </a>
            </div>
        </div>
        <div id="mgr-map"></div>
    </div>

    
    <div class="grid lg:grid-cols-5 gap-6">

        
        <div class="glass rounded-2xl overflow-hidden lg:col-span-3">
            <div class="px-5 py-4 border-b border-white/5 flex items-center justify-between">
                <div>
                    <h2 class="text-white font-display font-bold">Incidents récents</h2>
                    <p class="text-cyan-100/50 text-xs mt-0.5">
                        <span class="text-red-300"><?php echo e($pendingInc); ?></span> non traités ·
                        <span class="text-amber-300"><?php echo e($inProgressInc); ?></span> en cours
                    </p>
                </div>
                <a href="<?php echo e(route('manager.incidents')); ?>"
                   class="text-xs text-cyan-400 hover:text-cyan-300 font-semibold transition-colors">
                    Gérer →
                </a>
            </div>
            <div class="divide-y divide-white/[.04]">
                <?php $__currentLoopData = $reclamations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rec): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $ss = $statusStyle[$rec['status']];
                    $ps = $priorityStyle[$rec['priority']];
                ?>
                <div class="flex items-center gap-3 px-5 py-3.5 hover:bg-white/[.025] transition-colors">
                    <div class="w-2 h-2 rounded-full <?php echo e($ss['dot']); ?> shrink-0"></div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-1.5 mb-0.5 flex-wrap">
                            <span class="text-[10px] font-mono font-bold text-cyan-400"><?php echo e($rec['id']); ?></span>
                            <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold <?php echo e($ps['bg']); ?> <?php echo e($ps['text']); ?>">
                                <?php echo e($ps['label']); ?>

                            </span>
                            <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold <?php echo e($ss['bg']); ?> <?php echo e($ss['text']); ?>">
                                <?php echo e($ss['label']); ?>

                            </span>
                        </div>
                        <p class="text-white text-xs font-semibold truncate"><?php echo e($rec['type']); ?></p>
                        <p class="text-cyan-100/45 text-[11px]"><?php echo e($rec['zone']); ?> · <?php echo e($rec['citizen']); ?></p>
                    </div>
                    <div class="flex items-center gap-1.5 shrink-0">
                        <?php if($rec['status']==='pending'): ?>
                        <button onclick="showToast('Assignation technicien à implémenter', 'info')"
                                class="glass px-2.5 py-1.5 rounded-lg text-[11px] text-teal-300 hover:text-white font-semibold transition-colors"
                                title="Affecter">
                            Affecter
                        </button>
                        <?php endif; ?>
                        <span class="text-[10px] text-cyan-100/30 whitespace-nowrap"><?php echo e($rec['created_at']); ?></span>
                    </div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
            <div class="px-5 py-3 border-t border-white/5">
                <a href="<?php echo e(route('manager.incidents')); ?>"
                   class="text-xs text-cyan-400 hover:text-cyan-300 font-semibold transition-colors">
                    Voir tous les incidents →
                </a>
            </div>
        </div>

        
        <div class="glass rounded-2xl overflow-hidden lg:col-span-2">
            <div class="px-5 py-4 border-b border-white/5 flex items-center justify-between">
                <div>
                    <h2 class="text-white font-display font-bold">Équipes terrain</h2>
                    <p class="text-cyan-100/50 text-xs mt-0.5"><?php echo e($techOnMission); ?> en mission · <?php echo e($techAvail); ?> disponibles</p>
                </div>
                <a href="<?php echo e(route('manager.teams')); ?>"
                   class="text-xs text-cyan-400 hover:text-cyan-300 font-semibold transition-colors">
                    Gérer →
                </a>
            </div>
            <div class="divide-y divide-white/[.04]">
                <?php $__currentLoopData = $technicians; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tech): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $ts = [
                        'on_mission' => ['dot'=>'bg-amber-400 animate-pulse','text'=>'text-amber-300','label'=>'En mission'],
                        'available'  => ['dot'=>'bg-teal-400',               'text'=>'text-teal-300', 'label'=>'Disponible'],
                        'off_duty'   => ['dot'=>'bg-slate-500',              'text'=>'text-slate-400','label'=>'Hors service'],
                    ][$tech['status']];
                ?>
                <div class="flex items-center gap-3 px-5 py-3 hover:bg-white/[.02] transition-colors">
                    <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-cyan-500/20 to-blue-600/20
                                 border border-cyan-400/15 flex items-center justify-center
                                 text-[11px] font-bold text-cyan-300 shrink-0">
                        <?php echo e($tech['initials']); ?>

                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-white text-xs font-semibold truncate"><?php echo e($tech['name']); ?></p>
                        <p class="text-cyan-100/40 text-[11px] truncate"><?php echo e($tech['zone']); ?></p>
                    </div>
                    <div class="flex items-center gap-1.5 shrink-0">
                        <?php if($tech['interventions'] > 0): ?>
                        <span class="w-5 h-5 rounded-full bg-amber-500/15 text-amber-300 text-[10px] font-bold
                                      flex items-center justify-center"><?php echo e($tech['interventions']); ?></span>
                        <?php endif; ?>
                        <span class="w-2 h-2 rounded-full <?php echo e($ts['dot']); ?>"></span>
                    </div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    </div>

    
    <div class="grid sm:grid-cols-3 gap-4">

        
        <div class="glass rounded-2xl p-5 sm:col-span-2">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-white font-display font-bold text-sm">Incidents / résolutions</h3>
                    <p class="text-cyan-100/45 text-xs mt-0.5">12 derniers mois</p>
                </div>
                <div class="flex items-center gap-3 text-xs text-cyan-100/50">
                    <span class="flex items-center gap-1">
                        <span class="w-3 h-1 rounded bg-red-400/70 inline-block"></span>Incidents
                    </span>
                    <span class="flex items-center gap-1">
                        <span class="w-3 h-1 rounded bg-teal-400/70 inline-block"></span>Résolus
                    </span>
                </div>
            </div>
            <div style="height:100px">
                <svg id="mgr-sparkline" width="100%" height="100" viewBox="0 0 600 100"
                     preserveAspectRatio="none" class="overflow-visible"></svg>
            </div>
            <div class="flex justify-between mt-1">
                <?php $__currentLoopData = $monthly['labels']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lbl): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <span class="text-[9px] text-cyan-100/30"><?php echo e($lbl); ?></span>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>

        
        <div class="glass rounded-2xl p-5">
            <h3 class="text-white font-display font-bold text-sm mb-1">Taux de résolution</h3>
            <p class="text-cyan-100/45 text-xs mb-4">Incidents résolus / total</p>

            <?php $resRate = $totalInc > 0 ? round($resolvedInc/$totalInc*100) : 0; ?>

            <div class="flex flex-col items-center">
                <svg width="110" height="110" viewBox="0 0 110 110">
                    <?php
                        $r = 42; $circ = 2*M_PI*$r;
                        $dash = ($resRate/100)*$circ;
                    ?>
                    <circle cx="55" cy="55" r="<?php echo e($r); ?>" fill="none" stroke="rgba(5,191,219,.12)" stroke-width="10"/>
                    <circle cx="55" cy="55" r="<?php echo e($r); ?>" fill="none" stroke="#2dd4bf" stroke-width="10"
                            stroke-dasharray="<?php echo e($dash); ?> <?php echo e($circ - $dash); ?>"
                            stroke-dashoffset="<?php echo e($circ * 0.25); ?>"
                            transform="rotate(-90 55 55)"/>
                    <text x="55" y="51" text-anchor="middle" font-size="20" font-weight="700"
                          fill="#f0fdff" font-family="Space Grotesk"><?php echo e($resRate); ?>%</text>
                    <text x="55" y="64" text-anchor="middle" font-size="9"
                          fill="rgba(156,200,216,.5)" font-family="Plus Jakarta Sans">résolution</text>
                </svg>
            </div>

            <div class="mt-3 space-y-2">
                <?php $__currentLoopData = [
                    ['label'=>'Non traités','val'=>$pendingInc,  'color'=>'#ef4444'],
                    ['label'=>'En cours',   'val'=>$inProgressInc,'color'=>'#f97316'],
                    ['label'=>'Résolus',    'val'=>$resolvedInc, 'color'=>'#2dd4bf'],
                ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="flex items-center justify-between text-xs">
                    <div class="flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full" style="background:<?php echo e($row['color']); ?>"></span>
                        <span class="text-cyan-100/60"><?php echo e($row['label']); ?></span>
                    </div>
                    <span class="font-bold text-white"><?php echo e($row['val']); ?></span>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    </div>

    
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <?php $__currentLoopData = [
            ['route'=>'manager.incidents', 'icon'=>'alert-triangle','label'=>'Incidents',   'color'=>'red'],
            ['route'=>'manager.teams',     'icon'=>'users',          'label'=>'Équipes',     'color'=>'cyan'],
            ['route'=>'manager.projects',  'icon'=>'briefcase',      'label'=>'Projets',     'color'=>'blue'],
            ['route'=>'manager.analytics', 'icon'=>'bar-chart-2',    'label'=>'Analytics',   'color'=>'teal'],
        ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $link): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <a href="<?php echo e(route($link['route'])); ?>"
           class="glass rounded-2xl p-4 flex items-center gap-3 hover-lift group transition-all hover:border-<?php echo e($link['color']); ?>-400/30">
            <div class="w-9 h-9 rounded-xl bg-<?php echo e($link['color']); ?>-500/10 flex items-center justify-center shrink-0">
                <i data-lucide="<?php echo e($link['icon']); ?>" class="w-4.5 h-4.5 text-<?php echo e($link['color']); ?>-400" style="width:18px;height:18px"></i>
            </div>
            <span class="text-sm font-semibold text-cyan-100/70 group-hover:text-white transition-colors"><?php echo e($link['label']); ?></span>
            <i data-lucide="arrow-right" class="w-4 h-4 text-cyan-100/20 group-hover:text-cyan-400 ml-auto transition-colors shrink-0"></i>
        </a>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

</div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV/XN/WLI=" crossorigin=""></script>
<script>
const MAP_ZONES    = <?php echo json_encode($zones, 15, 512) ?>;
const RECLAMATIONS = <?php echo json_encode($reclamations, 15, 512) ?>;

/* ── Carte ──────────────────────────────────────────── */
(function() {
    const map = L.map('mgr-map', {
        center: [34.0, 9.4], zoom: 6,
        zoomControl: false, attributionControl: false, scrollWheelZoom: false
    });
    L.control.zoom({ position: 'topright' }).addTo(map);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 18 }).addTo(map);

    const zoneC = { normal:'#2dd4bf', alert:'#fbbf24', critical:'#ef4444' };
    MAP_ZONES.forEach(z => {
        const c = zoneC[z.status] ?? '#2dd4bf';
        const size = 26, half = 13;
        const svg = `<svg xmlns="http://www.w3.org/2000/svg" width="${size}" height="${size}" viewBox="0 0 ${size} ${size}">
            <circle cx="${half}" cy="${half}" r="${half-2}" fill="${c}" fill-opacity="0.15" stroke="${c}" stroke-width="1.5" stroke-dasharray="3,2"/>
        </svg>`;
        L.marker([z.lat, z.lng], {
            icon: L.divIcon({ html: svg, iconSize:[size,size], iconAnchor:[half,half], className:'' }),
            zIndexOffset: -100
        }).addTo(map).bindTooltip(
            `<b style="color:#f0fdff;font-size:11px">${z.emoji} ${z.name}</b><br>
             <span style="color:${c};font-size:10px">${z.incidents} incident(s)</span>`,
            { sticky: true, className: '' }
        );
    });

    const recC = { pending:'#ef4444', in_progress:'#f97316', resolved:'#2dd4bf' };
    const recI = { pending:'⚠', in_progress:'🔧', resolved:'✓' };
    RECLAMATIONS.forEach(r => {
        const c = recC[r.status] ?? '#9ca3af';
        const pulse = r.status !== 'resolved';
        const size = r.status === 'pending' ? 36 : 30, half = size/2;
        const svg = `<svg xmlns="http://www.w3.org/2000/svg" width="${size}" height="${size}" viewBox="0 0 ${size} ${size}">
            ${pulse ? `<circle cx="${half}" cy="${half}" r="${half}" fill="${c}" opacity="0.18">
                <animate attributeName="r" from="${half}" to="${size}" dur="2s" repeatCount="indefinite"/>
                <animate attributeName="opacity" from="0.25" to="0" dur="2s" repeatCount="indefinite"/>
            </circle>` : ''}
            <circle cx="${half}" cy="${half}" r="${half-3}" fill="${c}" fill-opacity="0.25" stroke="${c}" stroke-width="2"/>
            <circle cx="${half}" cy="${half}" r="${half-9}" fill="${c}" fill-opacity="0.9"/>
            <text x="${half}" y="${half+4}" text-anchor="middle" font-size="10" fill="white">${recI[r.status]}</text>
        </svg>`;
        L.marker([r.lat, r.lng], {
            icon: L.divIcon({ html: svg, iconSize:[size,size], iconAnchor:[half,half], className:'' }),
            zIndexOffset: 100
        }).addTo(map).bindPopup(
            `<div style="padding:12px;font-family:'Plus Jakarta Sans',sans-serif">
                <p style="color:rgba(156,200,216,.6);font-size:10px;font-family:monospace;margin:0 0 3px">${r.id}</p>
                <p style="color:#f0fdff;font-size:13px;font-weight:700;margin:0 0 4px">${r.type}</p>
                <p style="color:rgba(156,200,216,.6);font-size:11px;margin:0 0 8px">${r.zone}</p>
                <p style="color:rgba(156,200,216,.5);font-size:10px;margin:0">Citoyen: ${r.citizen}</p>
                ${r.technician ? `<p style="color:#5ee5f7;font-size:10px;margin:4px 0 0">Technicien: ${r.technician}</p>` : ''}
            </div>`, { maxWidth: 220 }
        );
    });
})();

/* ── Sparkline incidents/résolus ────────────────────── */
(function() {
    const inc = <?php echo json_encode($monthly['incidents'], 15, 512) ?>;
    const res = <?php echo json_encode($monthly['resolved'], 15, 512) ?>;
    const svg = document.getElementById('mgr-sparkline');
    if (!svg) return;
    const W = 600, H = 100, pad = 10;
    const max = Math.max(...inc, ...res) * 1.15;
    const sx = i => pad + (i/(inc.length-1))*(W-pad*2);
    const sy = v => pad + (1-v/max)*(H-pad*2);
    const pts = (arr) => arr.map((v,i) => ({x:sx(i),y:sy(v)}));
    const path = (arr, color) => {
        const p = pts(arr);
        const d = p.map((pt,i)=>`${i===0?'M':'L'}${pt.x},${pt.y}`).join(' ');
        const el = document.createElementNS('http://www.w3.org/2000/svg','path');
        el.setAttribute('d',d); el.setAttribute('fill','none');
        el.setAttribute('stroke',color); el.setAttribute('stroke-width','2');
        el.setAttribute('stroke-linecap','round'); el.setAttribute('opacity','0.8');
        svg.appendChild(el);
    };
    path(inc, '#ef4444');
    path(res, '#2dd4bf');
    // dots
    pts(inc).forEach((p,i) => {
        const c = document.createElementNS('http://www.w3.org/2000/svg','circle');
        c.setAttribute('cx',p.x); c.setAttribute('cy',p.y); c.setAttribute('r',3);
        c.setAttribute('fill','#ef4444');
        svg.appendChild(c);
    });
})();

document.addEventListener('DOMContentLoaded', () => {
    if (typeof lucide !== 'undefined') lucide.createIcons();
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\ghada\Desktop\5ème\projet_Laravel\aquasecure\resources\views/manager/dashboard.blade.php ENDPATH**/ ?>