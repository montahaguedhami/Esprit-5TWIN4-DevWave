<?php $__env->startSection('title', 'AquaSecure — Tableau de bord des infrastructures hydrauliques'); ?>

<?php
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
?>

<?php $__env->startPush('styles'); ?>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
#mgr-map { height: 380px; border-radius: 1rem; }
.leaflet-tile-pane { filter: brightness(.88) contrast(1.1) saturate(0.8); }
.leaflet-control-zoom a { background: #0f172a !important; border-color: rgba(255,255,255,0.1) !important; color: #38bdf8 !important; }
.leaflet-popup-content-wrapper { background: #0f172a !important; border: 1px solid rgba(56,189,248,0.3) !important; border-radius: 12px !important; color: #f8fafc !important; }
.leaflet-popup-tip { background: #0f172a !important; }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="min-h-screen bg-[#070e17] text-slate-100 flex flex-col font-sans">

    
    <header class="sticky top-0 z-50 glass-strong border-b border-white/20 px-4 sm:px-6 py-3">
        <div class="max-w-7xl mx-auto flex items-center justify-between gap-4">
            
            
            <div class="flex items-center gap-3">
                <?php if (isset($component)) { $__componentOriginal5c84f04e4e4c3f6b2afa5416a6776687 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5c84f04e4e4c3f6b2afa5416a6776687 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.back-button','data' => ['fallback' => route('landing')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('back-button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['fallback' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('landing'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5c84f04e4e4c3f6b2afa5416a6776687)): ?>
<?php $attributes = $__attributesOriginal5c84f04e4e4c3f6b2afa5416a6776687; ?>
<?php unset($__attributesOriginal5c84f04e4e4c3f6b2afa5416a6776687); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5c84f04e4e4c3f6b2afa5416a6776687)): ?>
<?php $component = $__componentOriginal5c84f04e4e4c3f6b2afa5416a6776687; ?>
<?php unset($__componentOriginal5c84f04e4e4c3f6b2afa5416a6776687); ?>
<?php endif; ?>
                <a href="<?php echo e(route('manager.dashboard')); ?>" class="flex items-center gap-2.5 group">
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-cyan-400 to-blue-600 flex items-center justify-center shadow-lg shadow-cyan-500/30 group-hover:scale-105 transition-transform">
                        <i data-lucide="droplet" class="w-5 h-5 text-white"></i>
                    </div>
                    <div>
                        <span class="font-display text-lg font-semibold tracking-tight text-white flex items-center gap-2">
                            AquaSecure <span class="liquid-chip text-[11px] py-0 px-2 font-medium">Gestionnaire</span>
                        </span>
                    </div>
                </a>

                
                <div class="hidden md:flex items-center gap-2 glass px-3 py-1.5 rounded-xl text-xs">
                    <i data-lucide="building-2" class="w-4 h-4 text-cyan-300"></i>
                    <span class="text-white/70 font-medium">Secteur:</span>
                    <select id="utility-selector" onchange="switchUtility(this.value)" class="bg-transparent text-white font-semibold outline-none cursor-pointer pr-1">
                        <?php $__currentLoopData = $municipalities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $muni): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($muni['id']); ?>" class="bg-[#04121b] text-white" <?php echo e($muni['active'] ? 'selected' : ''); ?>>
                                <?php echo e($muni['name']); ?> (<?php echo e($muni['assets']); ?> actifs)
                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
            </div>

            
            <div class="hidden lg:flex flex-1 max-w-md mx-4">
                <div class="relative w-full">
                    <i data-lucide="search" class="w-4 h-4 text-white/50 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                    <input type="text" placeholder="Rechercher canalisations, réservoirs, incidents... (Ctrl+K)" 
                           class="w-full glass rounded-xl pl-9 pr-4 py-2 text-xs text-white placeholder-white/40 focus:outline-none focus:border-cyan-300 transition-all">
                </div>
            </div>

            
            <div class="flex items-center gap-3">
                <details class="relative lg:hidden">
                    <summary class="flex h-10 w-10 cursor-pointer list-none items-center justify-center rounded-xl border border-white/10 bg-white/5 text-white/80 hover:bg-white/10 focus-visible:outline focus-visible:outline-2 focus-visible:outline-cyan-300" aria-label="Ouvrir la navigation">
                        <i data-lucide="menu" class="h-5 w-5" aria-hidden="true"></i>
                    </summary>
                    <nav class="absolute right-0 top-12 z-[70] w-64 rounded-2xl border border-white/15 bg-slate-950/95 p-2 shadow-2xl backdrop-blur-xl" aria-label="Navigation gestionnaire">
                        <a class="block rounded-xl px-3 py-2.5 text-sm text-white/85 hover:bg-white/10" href="<?php echo e(route('manager.dashboard')); ?>">Tableau de bord</a>
                        <a class="block rounded-xl px-3 py-2.5 text-sm text-white/85 hover:bg-white/10" href="<?php echo e(route('manager.map')); ?>">Carte du réseau</a>
                        <a class="block rounded-xl px-3 py-2.5 text-sm text-white/85 hover:bg-white/10" href="<?php echo e(route('manager.quality')); ?>">Qualité de l’eau</a>
                        <a class="block rounded-xl px-3 py-2.5 text-sm text-white/85 hover:bg-white/10" href="<?php echo e(route('manager.incidents')); ?>">Incidents</a>
                        <a class="block rounded-xl px-3 py-2.5 text-sm text-white/85 hover:bg-white/10" href="<?php echo e(route('manager.teams')); ?>">Équipes</a>
                        <a class="block rounded-xl px-3 py-2.5 text-sm text-white/85 hover:bg-white/10" href="<?php echo e(route('manager.projets.index')); ?>">Projets</a>
                        <a class="block rounded-xl px-3 py-2.5 text-sm text-white/85 hover:bg-white/10" href="<?php echo e(route('manager.budget')); ?>">Budget</a>
                        <a class="block rounded-xl px-3 py-2.5 text-sm text-white/85 hover:bg-white/10" href="<?php echo e(route('manager.analytics')); ?>">Analyses et rapports</a>
                    </nav>
                </details>
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
        </div>
    </header>

    
    <div class="flex-1 flex max-w-7xl w-full mx-auto">
        
        
        <aside class="w-64 hidden lg:block bg-[#09121f] border-r border-slate-800/80 p-4 space-y-6 shrink-0">
            <div>
                <p class="px-3 text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Navigation principale</p>
                <nav class="space-y-1">
                    <a href="<?php echo e(route('manager.dashboard')); ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold bg-cyan-500/15 text-cyan-300 border border-cyan-500/30">
                        <i data-lucide="layout-dashboard" class="w-4 h-4 text-cyan-400"></i> Tableau de bord
                    </a>
                    <a href="<?php echo e(route('manager.map')); ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold text-slate-300 hover:bg-slate-800/60 hover:text-white transition-all">
                        <i data-lucide="map-pin" class="w-4 h-4 text-blue-400"></i> Carte du réseau
                    </a>
                    <a href="<?php echo e(route('manager.quality')); ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold text-slate-300 hover:bg-slate-800/60 hover:text-white transition-all">
                        <i data-lucide="flask-conical" class="w-4 h-4 text-teal-400"></i> Qualité de l’eau
                    </a>
                    <a href="<?php echo e(route('manager.incidents')); ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold text-slate-300 hover:bg-slate-800/60 hover:text-white transition-all">
                        <i data-lucide="alert-triangle" class="w-4 h-4 text-red-400"></i> Gestion des incidents
                    </a>
                    <a href="<?php echo e(route('manager.projets.index')); ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold text-slate-300 hover:bg-slate-800/60 hover:text-white transition-all">
                        <i data-lucide="briefcase" class="w-4 h-4 text-amber-400"></i> Maintenance et projets
                    </a>
                    <a href="<?php echo e(route('manager.techniciens.index')); ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold text-slate-300 hover:bg-slate-800/60 hover:text-white transition-all">
                        <i data-lucide="wrench" class="w-4 h-4 text-orange-400"></i> Techniciens et interventions
                    </a>
                    <a href="<?php echo e(route('manager.budget')); ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold text-slate-300 hover:bg-slate-800/60 hover:text-white transition-all">
                        <i data-lucide="dollar-sign" class="w-4 h-4 text-emerald-400"></i> Financement et budget
                    </a>
                    <a href="<?php echo e(route('manager.analytics')); ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold text-slate-300 hover:bg-slate-800/60 hover:text-white transition-all">
                        <i data-lucide="line-chart" class="w-4 h-4 text-purple-400"></i> Analyses et rapports
                    </a>
                </nav>
            </div>

            <div>
                <p class="px-3 text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Espace public et administration</p>
                <nav class="space-y-1">
                    <a href="<?php echo e(route('citizen.dashboard')); ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold text-slate-300 hover:bg-slate-800/60 hover:text-white transition-all">
                        <i data-lucide="users" class="w-4 h-4 text-sky-400"></i> Portail de signalement citoyen
                    </a>
                    <a href="<?php echo e(route('admin.roles')); ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold text-slate-300 hover:bg-slate-800/60 hover:text-white transition-all">
                        <i data-lucide="shield-check" class="w-4 h-4 text-indigo-400"></i> Rôles et autorisations
                    </a>
                </nav>
            </div>

            
            <div class="bg-gradient-to-br from-cyan-950/40 to-blue-950/40 border border-cyan-500/20 rounded-2xl p-4 text-xs space-y-2">
                <div class="flex items-center gap-2 text-cyan-300 font-bold">
                    <i data-lucide="shield" class="w-4 h-4"></i> EPA / INNORPI Standard
                </div>
                <p class="text-slate-400 text-[11px]">Suivi de la qualité de l'eau selon les seuils EPA et INNORPI.</p>
            </div>
        </aside>

        
        <main class="flex-1 p-4 sm:p-6 space-y-6 overflow-x-hidden">
            
            
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-display font-bold text-white tracking-tight">
                        Tableau de bord des infrastructures hydrauliques
                    </h1>
                    <p class="text-slate-400 text-sm mt-1">
                        Suivi du réseau, des incidents et du budget pour <span id="current-utility-title" class="text-cyan-300 font-semibold">Régie des eaux du Grand Tunis</span>.
                    </p>
                </div>
                <div class="flex items-center gap-2.5">
                    <button onclick="showToast('Exporting executive PDF summary report...', 'info')" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 text-xs font-semibold flex items-center gap-2 transition-all">
                        <i data-lucide="download" class="w-4 h-4"></i> Exporter le rapport
                    </button>
                    <a href="<?php echo e(route('manager.map')); ?>" class="px-4 py-2 rounded-xl bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 text-white text-xs font-semibold shadow-lg shadow-cyan-500/20 flex items-center gap-2 transition-all">
                        <i data-lucide="map" class="w-4 h-4"></i> Carte du réseau
                    </a>
                </div>
            </div>

            
            <div class="grid grid-cols-2 lg:grid-cols-3 2xl:grid-cols-6 gap-4">
                
                
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

                
                <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-4 relative overflow-hidden group hover:border-slate-700 transition-all">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs text-slate-400 font-semibold">Incidents en cours</span>
                        <div class="w-8 h-8 rounded-lg bg-red-500/10 text-red-400 flex items-center justify-center">
                            <i data-lucide="alert-triangle" class="w-4 h-4"></i>
                        </div>
                    </div>
                    <div class="text-2xl font-display font-bold text-white"><?php echo e($totalInc); ?></div>
                    <p class="text-[11px] text-amber-400 mt-1 flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-red-400 animate-pulse"></span> 2 critiques, <?php echo e($pendingInc); ?> sans affectation
                    </p>
                </div>

                
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

                
                <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-4 relative overflow-hidden group hover:border-slate-700 transition-all">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs text-slate-400 font-semibold">Projets en cours</span>
                        <div class="w-8 h-8 rounded-lg bg-purple-500/10 text-purple-400 flex items-center justify-center">
                            <i data-lucide="briefcase" class="w-4 h-4"></i>
                        </div>
                    </div>
                    <div class="text-2xl font-display font-bold text-white"><?php echo e(count($projects)); ?></div>
                    <p class="text-[11px] text-slate-400 mt-1">Valeur totale : 4,85 M$</p>
                </div>

                
                <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-4 relative overflow-hidden group hover:border-slate-700 transition-all">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs text-slate-400 font-semibold">Budget consommé</span>
                        <div class="w-8 h-8 rounded-lg bg-emerald-500/10 text-emerald-400 flex items-center justify-center">
                            <i data-lucide="dollar-sign" class="w-4 h-4"></i>
                        </div>
                    </div>
                    <div class="text-2xl font-display font-bold text-white">$<?php echo e(number_format($funding['actual_expenditure']/1000000, 2)); ?>M</div>
                    <p class="text-[11px] text-emerald-400 mt-1 font-semibold"><?php echo e($funding['utilization_rate']); ?> % d’un budget de $<?php echo e(number_format($funding['total_approved_funding']/1000000, 2)); ?>M</p>
                </div>
            </div>

            
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
                        <a href="<?php echo e(route('manager.map')); ?>" class="ml-2 px-3 py-1 rounded-lg bg-slate-800 text-cyan-300 hover:text-white font-semibold">Afficher la carte →</a>
                    </div>
                </div>
                <div id="mgr-map"></div>
            </div>

            
            <div class="grid lg:grid-cols-2 gap-6">
                
                
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

            
            <div class="grid lg:grid-cols-3 gap-6">
                
                
                <div class="lg:col-span-2 bg-slate-900/90 border border-slate-800 rounded-2xl overflow-hidden">
                    <div class="p-4 border-b border-slate-800 flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-bold text-white flex items-center gap-2">
                                <i data-lucide="list" class="w-4 h-4 text-cyan-400"></i> Incidents récents
                            </h3>
                            <p class="text-xs text-slate-400">Fuites signalées, alertes qualité et ruptures de canalisation</p>
                        </div>
                        <a href="<?php echo e(route('manager.incidents')); ?>" class="text-xs font-semibold text-cyan-400 hover:text-cyan-300">Voir tous les incidents →</a>
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
                                <?php $__currentLoopData = $reclamations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rec): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php
                                        $ss = $statusStyle[$rec['status']];
                                        $ps = $priorityStyle[$rec['priority']];
                                    ?>
                                    <tr class="hover:bg-slate-800/40 transition-colors">
                                        <td class="px-4 py-3 font-semibold text-white">
                                            <div class="font-mono text-cyan-400 text-[11px]"><?php echo e($rec['id']); ?></div>
                                            <div><?php echo e($rec['type']); ?></div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="text-slate-200 font-medium"><?php echo e($rec['zone']); ?></div>
                                            <div class="text-[10px] text-slate-400 truncate max-w-[140px]"><?php echo e($rec['address']); ?></div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold <?php echo e($ps['bg']); ?> <?php echo e($ps['text']); ?>">
                                                <?php echo e($ps['label']); ?>

                                            </span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold <?php echo e($ss['bg']); ?> <?php echo e($ss['text']); ?> inline-flex items-center gap-1">
                                                <span class="w-1.5 h-1.5 rounded-full <?php echo e($ss['dot']); ?>"></span> <?php echo e($ss['label']); ?>

                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-slate-300">
                                            <?php echo e($rec['technician'] ?? 'Unassigned'); ?>

                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <button onclick="showToast('Dispatching team for <?php echo e($rec['id']); ?>...', 'info')" class="px-2.5 py-1 rounded-lg bg-cyan-500/10 hover:bg-cyan-500/20 text-cyan-300 text-[11px] font-bold border border-cyan-500/20">
                                                Manage
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                
                <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-5">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-sm font-bold text-white flex items-center gap-2">
                            <i data-lucide="pie-chart" class="w-4 h-4 text-emerald-400"></i> Répartition des financements
                        </h3>
                        <a href="<?php echo e(route('manager.budget')); ?>" class="text-xs font-semibold text-emerald-400 hover:text-emerald-300">Détails du budget →</a>
                    </div>
                    <div style="height: 180px;" class="mb-4">
                        <canvas id="fundingDoughnutChart"></canvas>
                    </div>
                    <div class="space-y-2 text-xs">
                        <?php $__currentLoopData = $funding['funding_sources']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $source): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full" style="background-color: <?php echo e($source['color']); ?>"></span>
                                    <span class="text-slate-300"><?php echo e($source['name']); ?></span>
                                </div>
                                <span class="font-bold text-white">$<?php echo e(number_format($source['amount']/1000)); ?>k (<?php echo e($source['pct']); ?>%)</span>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>

            </div>

        </main>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
const MAP_ZONES = <?php echo json_encode($zones, 15, 512) ?>;
const RECLAMATIONS = <?php echo json_encode($reclamations, 15, 512) ?>;

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
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\monta\Downloads\DEVWAVE\Esprit-5TWIN4-DevWave-main (1)\Esprit-5TWIN4-DevWave-main\resources\views/manager/dashboard.blade.php ENDPATH**/ ?>