<?php $__env->startSection('title', 'Mon espace — AquaSecure'); ?>

<?php
    use App\Data\PlaceholderData;
    $user         = session('user', ['name' => 'Yassine Hamdi', 'email' => 'citoyen@aquasecure.tn']);
    $reports      = PlaceholderData::citizenReports();
    $notifs       = PlaceholderData::citizenNotifications();
    $invoices     = PlaceholderData::citizenInvoices();
    $zones        = PlaceholderData::zones();

    $firstName    = explode(' ', $user['name'])[0];
    $activeRep    = count(array_filter($reports, fn($r) => $r['status'] === 'in_progress'));
    $resolvedRep  = count(array_filter($reports, fn($r) => $r['status'] === 'resolved'));
    $pendingRep   = count(array_filter($reports, fn($r) => $r['status'] === 'pending'));
    $unreadNotifs = count(array_filter($notifs, fn($n) => !$n['read']));

    $statusStyle = [
        'in_progress' => ['bg'=>'bg-amber-500/15','text'=>'text-amber-300','dot'=>'bg-amber-400 animate-pulse','label'=>'En cours'],
        'resolved'    => ['bg'=>'bg-teal-500/15', 'text'=>'text-teal-300', 'dot'=>'bg-teal-400', 'label'=>'Résolu'],
        'pending'     => ['bg'=>'bg-blue-500/15', 'text'=>'text-blue-300', 'dot'=>'bg-blue-400', 'label'=>'En attente'],
    ];
?>

<?php $__env->startSection('frontoffice-content'); ?>
<div class="space-y-8 animate-fade-in-up">

    
    <div class="glass rounded-3xl border border-white/15 relative overflow-hidden" style="padding: var(--card-padding-lg);">
        <div class="absolute right-0 top-0 bottom-0 w-1/3 bg-gradient-to-l from-cyan-500/10 to-transparent pointer-events-none"></div>
        <div class="relative z-10 flex flex-col sm:flex-row sm:items-center justify-between gap-6">
            <div style="display:flex;flex-direction:column;gap:var(--spacing-sm);">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-cyan-400/10 border border-cyan-400/20 text-cyan-300 font-semibold" style="font-size:var(--font-size-xs);">
                    <span class="w-2.5 h-2.5 rounded-full bg-cyan-400 animate-pulse"></span>
                    Portail Citoyen AquaSecure
                </div>
                <h1 class="dashboard-page-title text-white tracking-tight font-display">
                    Bonjour, <?php echo e($firstName); ?> 👋
                </h1>
                <p class="text-cyan-100/70 max-w-xl font-medium" style="font-size:var(--font-size-body);">
                    Suivez vos signalements de fuite, consultez l'état du réseau d'eau et vos factures depuis votre espace.
                </p>
            </div>
            
            
            <a href="<?php echo e(route('incidents.create')); ?>"
               class="btn-primary-md shrink-0 rounded-2xl bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 text-white shadow-xl shadow-cyan-500/25 transition-all hover:scale-[1.02] group">
                <i data-lucide="plus-circle" style="width:var(--icon-sm);height:var(--icon-sm);" class="group-hover:rotate-90 transition-transform"></i>
                <span>Signaler un problème</span>
            </a>
        </div>
    </div>

    
    <div class="grid grid-cols-2 lg:grid-cols-4" style="gap:var(--card-gap);">
        <?php $__currentLoopData = [
            ['val'=>count($reports), 'label'=>'Mes signalements', 'icon'=>'file-text',    'bg'=>'bg-cyan-500/15',   'ic'=>'text-cyan-300',   'sub'=>'total déposés'],
            ['val'=>$activeRep,      'label'=>'En cours',          'icon'=>'loader',        'bg'=>'bg-amber-500/15',  'ic'=>'text-amber-300',  'sub'=>'en traitement'],
            ['val'=>$resolvedRep,    'label'=>'Résolus',           'icon'=>'check-circle',  'bg'=>'bg-teal-500/15',   'ic'=>'text-teal-300',   'sub'=>'interventions closes'],
            ['val'=>$unreadNotifs,   'label'=>'Notifications',      'icon'=>'bell',          'bg'=>'bg-sky-500/15',    'ic'=>'text-sky-300',    'sub'=>'non lues'],
        ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $kpi): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="glass rounded-3xl border border-white/10 hover:border-cyan-400/30 transition-all dashboard-kpi-card">
            <div class="flex items-center justify-between" style="margin-bottom:var(--spacing-md);">
                <div class="rounded-2xl <?php echo e($kpi['bg']); ?> flex items-center justify-center" style="width:var(--icon-wrap-md);height:var(--icon-wrap-md);">
                    <i data-lucide="<?php echo e($kpi['icon']); ?>" style="width:var(--icon-md);height:var(--icon-md);" class="<?php echo e($kpi['ic']); ?>"></i>
                </div>
                <span class="text-cyan-100/40 font-mono font-bold" style="font-size:var(--font-size-xs);"><?php echo e($kpi['sub']); ?></span>
            </div>
            <p class="dashboard-kpi-number text-white tracking-tight font-display"><?php echo e($kpi['val']); ?></p>
            <p class="font-bold text-cyan-100/70 mt-1 uppercase tracking-wider" style="font-size:var(--font-size-xs);"><?php echo e($kpi['label']); ?></p>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    
    <div class="grid lg:grid-cols-12 gap-8 items-start">
        
        
        <div class="lg:col-span-8 space-y-6">
            <div class="glass rounded-3xl border border-white/10" style="padding: var(--card-padding-lg); display:flex; flex-direction:column; gap:var(--spacing-md);">
                <div class="flex items-center justify-between border-b border-white/10 pb-4">
                    <div>
                        <h2 class="dashboard-section-title font-display font-extrabold text-white">Mes Signalements Réseau</h2>
                        <p class="text-cyan-100/50 mt-0.5" style="font-size:var(--font-size-xs);">Derniers rapports transmis aux équipes techniques</p>
                    </div>
                    <a href="<?php echo e(route('incidents.create')); ?>" class="text-cyan-300 hover:text-white font-bold flex items-center gap-1" style="font-size:var(--font-size-sm);">
                        <span>+ Nouveau</span>
                    </a>
                </div>

                <?php if(count($reports) === 0): ?>
                <div class="flex flex-col items-center justify-center py-12 text-center px-6">
                    <div class="w-16 h-16 rounded-2xl bg-cyan-500/10 flex items-center justify-center mb-3">
                        <i data-lucide="file-plus" class="w-8 h-8 text-cyan-400/50"></i>
                    </div>
                    <p class="text-white font-semibold text-base mb-1">Aucun signalement actif</p>
                    <p class="text-cyan-100/45 text-xs mb-4">Signalez tout problème de fuite ou baisse de pression dans votre quartier.</p>
                </div>
                <?php else: ?>
                <div class="space-y-3">
                    <?php $__currentLoopData = $reports; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rep): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php $ss = $statusStyle[$rep['status']] ?? $statusStyle['pending']; ?>
                    <a href="<?php echo e(route('citizen.reports.show', $rep['id'])); ?>"
                       class="flex items-center gap-4 p-4 rounded-2xl bg-white/[0.03] border border-white/5 hover:border-cyan-400/30 hover:bg-white/[0.06] transition-all group">
                        
                        
                        <div class="w-11 h-11 rounded-2xl bg-white/5 flex items-center justify-center shrink-0">
                            <span class="w-3.5 h-3.5 rounded-full <?php echo e($ss['dot']); ?>"></span>
                        </div>
                        
                        
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="font-mono font-bold text-cyan-400" style="font-size:var(--font-size-xs);"><?php echo e($rep['id']); ?></span>
                                <span class="px-2.5 py-0.5 rounded-full font-bold <?php echo e($ss['bg']); ?> <?php echo e($ss['text']); ?>" style="font-size:var(--font-size-badge);">
                                    <?php echo e($ss['label']); ?>

                                </span>
                            </div>
                            <h3 class="text-white font-bold truncate group-hover:text-cyan-300 transition-colors" style="font-size:var(--font-size-body);"><?php echo e($rep['type']); ?></h3>
                            <p class="text-cyan-100/50 truncate mt-0.5" style="font-size:var(--font-size-xs);"><?php echo e($rep['zone']); ?> · <?php echo e($rep['created_at']); ?></p>
                        </div>
                        
                        <i data-lucide="chevron-right" class="w-5 h-5 text-cyan-100/30 group-hover:text-cyan-300 group-hover:translate-x-1 transition-all shrink-0"></i>
                    </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        
        <div class="lg:col-span-4 space-y-6">
            
            
            <?php $alertZone = array_values(array_filter($zones, fn($z)=>$z['status']==='critical'))[0] ?? null; ?>
            <?php if($alertZone): ?>
            <div class="glass rounded-3xl p-6 border border-red-500/30 bg-gradient-to-b from-red-500/10 via-transparent to-transparent space-y-3">
                <div class="flex items-center justify-between">
                    <div class="inline-flex items-center gap-2 text-red-300 font-bold text-xs uppercase tracking-wider">
                        <span class="w-2.5 h-2.5 rounded-full bg-red-400 animate-pulse"></span>
                        Alerte Réseau Locale
                    </div>
                    <span class="text-xs text-red-300/60 font-mono">En cours</span>
                </div>
                <h3 class="text-lg font-bold text-white"><?php echo e($alertZone['name']); ?></h3>
                <p class="text-cyan-100/70 text-xs leading-relaxed">
                    Perturbation de pression détectée dans le secteur. Des équipes de maintenance sont en cours d'intervention.
                </p>
                <div class="pt-2 border-t border-red-500/20 flex items-center justify-between text-xs text-cyan-200 font-medium">
                    <span>Qualité : <?php echo e($alertZone['quality']); ?>%</span>
                    <span>Pression : <?php echo e($alertZone['pressure']); ?> bar</span>
                </div>
            </div>
            <?php endif; ?>

            
            <div class="glass rounded-3xl p-6 border border-white/10 space-y-4">
                <div class="flex items-center justify-between border-b border-white/10 pb-3">
                    <h2 class="text-base font-display font-bold text-white">Dernières Notifications</h2>
                    <a href="<?php echo e(route('citizen.notifications')); ?>" class="text-xs text-cyan-400 hover:text-cyan-300 font-semibold">
                        Tout voir →
                    </a>
                </div>

                <div class="space-y-3">
                    <?php $__currentLoopData = array_slice($notifs, 0, 3); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notif): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $nb = match($notif['color']) {
                            'amber'   => ['bg'=>'bg-amber-500/15','ic'=>'text-amber-400'],
                            'emerald' => ['bg'=>'bg-teal-500/15', 'ic'=>'text-teal-400'],
                            'blue'    => ['bg'=>'bg-blue-500/15', 'ic'=>'text-blue-400'],
                            'purple'  => ['bg'=>'bg-violet-500/15','ic'=>'text-violet-400'],
                            default   => ['bg'=>'bg-cyan-500/15', 'ic'=>'text-cyan-400'],
                        };
                    ?>
                    <div class="flex items-start gap-3 p-3 rounded-2xl bg-white/[0.02] border border-white/5">
                        <div class="w-8 h-8 rounded-xl <?php echo e($nb['bg']); ?> flex items-center justify-center shrink-0 mt-0.5">
                            <i data-lucide="<?php echo e($notif['icon']); ?>" class="w-4 h-4 <?php echo e($nb['ic']); ?>"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="text-white text-xs font-bold truncate"><?php echo e($notif['title']); ?></h4>
                            <p class="text-cyan-100/50 text-[11px] mt-0.5 leading-snug"><?php echo e($notif['message']); ?></p>
                        </div>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>

            
            <div class="glass rounded-3xl p-6 border border-white/10 space-y-4">
                <div class="flex items-center justify-between border-b border-white/10 pb-3">
                    <h2 class="text-base font-display font-bold text-white">Ma Consommation</h2>
                    <a href="<?php echo e(route('citizen.invoices.index')); ?>" class="text-xs text-teal-400 hover:text-teal-300 font-semibold">
                        Factures →
                    </a>
                </div>
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-xs text-cyan-100/50">Moyenne mensuelle</span>
                        <div class="text-2xl font-bold text-white font-display">19.6 m³</div>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-teal-500/15 text-teal-400 flex items-center justify-center">
                        <i data-lucide="droplet" class="w-6 h-6"></i>
                    </div>
                </div>
            </div>

        </div>

    </div>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.frontoffice', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\monta\Downloads\DEVWAVE\Esprit-5TWIN4-DevWave-main (1)\Esprit-5TWIN4-DevWave-main\resources\views/citizen/dashboard.blade.php ENDPATH**/ ?>