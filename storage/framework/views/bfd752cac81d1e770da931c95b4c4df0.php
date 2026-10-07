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
        'in_progress' => ['bg'=>'bg-amber-500/15','text'=>'text-amber-300','dot'=>'bg-amber-400','label'=>'En cours'],
        'resolved'    => ['bg'=>'bg-teal-500/15', 'text'=>'text-teal-300', 'dot'=>'bg-teal-400', 'label'=>'Résolu'],
        'pending'     => ['bg'=>'bg-blue-500/15', 'text'=>'text-blue-300', 'dot'=>'bg-blue-400', 'label'=>'En attente'],
    ];
?>

<?php $__env->startSection('frontoffice-content'); ?>
<div class="space-y-6 animate-fade-in-up">

    
    <div class="glass rounded-2xl p-5 sm:p-6">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-display font-bold text-white">
                    Bonjour, <?php echo e($firstName); ?> 👋
                </h1>
                <p class="text-cyan-100/55 text-sm mt-1"><?php echo e($user['email']); ?></p>
                <p class="text-cyan-100/65 text-sm mt-2 max-w-md">
                    Suivez vos signalements, consultez l'état de votre réseau et restez informé.
                </p>
            </div>
            <a href="<?php echo e(route('citizen.reports.create')); ?>"
               class="shrink-0 flex items-center gap-2 bg-gradient-to-r from-cyan-500 to-blue-600
                      hover:from-cyan-400 hover:to-blue-500 text-white text-sm font-semibold
                      px-4 py-2.5 rounded-xl transition-all hover-lift shadow-lg shadow-cyan-500/20">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span class="hidden sm:inline">Signaler</span>
            </a>
        </div>
    </div>

    
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <?php $__currentLoopData = [
            ['val'=>count($reports), 'label'=>'Mes signalements', 'icon'=>'file-text',    'bg'=>'bg-cyan-500/10',   'ic'=>'text-cyan-400',   'sub'=>'total'],
            ['val'=>$activeRep,      'label'=>'En cours',          'icon'=>'loader',        'bg'=>'bg-amber-500/10',  'ic'=>'text-amber-400',  'sub'=>'en traitement'],
            ['val'=>$resolvedRep,    'label'=>'Résolus',           'icon'=>'check-circle',  'bg'=>'bg-teal-500/10',   'ic'=>'text-teal-400',   'sub'=>'clôturés'],
            ['val'=>$unreadNotifs,   'label'=>'Notifications',      'icon'=>'bell',          'bg'=>'bg-blue-500/10',   'ic'=>'text-blue-400',   'sub'=>'non lues'],
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

    
    <?php $alertZone = array_values(array_filter($zones, fn($z)=>$z['status']==='critical'))[0] ?? null; ?>
    <?php if($alertZone): ?>
    <div class="glass rounded-2xl p-4 border-red-500/30 ring-1 ring-red-500/20">
        <div class="flex items-start gap-3">
            <div class="w-9 h-9 rounded-xl bg-red-500/15 flex items-center justify-center shrink-0 mt-0.5">
                <i data-lucide="alert-triangle" class="w-4.5 h-4.5 text-red-400 animate-pulse" style="width:18px;height:18px"></i>
            </div>
            <div class="flex-1">
                <p class="text-red-300 font-semibold text-sm mb-0.5">Alerte dans votre réseau</p>
                <p class="text-cyan-100/60 text-xs">
                    <strong class="text-white"><?php echo e($alertZone['name']); ?></strong> —
                    Qualité : <?php echo e($alertZone['quality']); ?>% · Pression : <?php echo e($alertZone['pressure']); ?> bar ·
                    <?php echo e($alertZone['incidents']); ?> incident(s) actif(s)
                </p>
            </div>
            <a href="<?php echo e(route('citizen.notifications')); ?>"
               class="text-xs text-red-300 hover:text-white font-semibold shrink-0 transition-colors">
                Voir →
            </a>
        </div>
    </div>
    <?php endif; ?>

    
    <div class="glass rounded-2xl overflow-hidden">
        <div class="px-5 py-4 border-b border-white/5 flex items-center justify-between">
            <div>
                <h2 class="text-white font-display font-bold">Mes signalements</h2>
                <p class="text-cyan-100/50 text-xs mt-0.5"><?php echo e(count($reports)); ?> signalements · <?php echo e($activeRep); ?> en cours</p>
            </div>
            <a href="<?php echo e(route('citizen.reports.create')); ?>"
               class="glass px-3 py-1.5 rounded-xl text-xs text-cyan-300 hover:text-white font-semibold
                      flex items-center gap-1.5 transition-all hover:border-cyan-400/40">
                <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                Nouveau
            </a>
        </div>

        <?php if(count($reports) === 0): ?>
        <div class="flex flex-col items-center justify-center py-12 text-center px-6">
            <div class="w-14 h-14 rounded-2xl bg-cyan-500/10 flex items-center justify-center mb-3">
                <i data-lucide="file-plus" class="w-7 h-7 text-cyan-400/50"></i>
            </div>
            <p class="text-white font-semibold text-sm mb-1">Aucun signalement</p>
            <p class="text-cyan-100/45 text-xs mb-4">Signalez un problème pour commencer</p>
            <a href="<?php echo e(route('citizen.reports.create')); ?>"
               class="bg-gradient-to-r from-cyan-500 to-blue-600 text-white text-sm font-semibold px-4 py-2 rounded-xl">
                Créer un signalement
            </a>
        </div>
        <?php else: ?>
        <div class="divide-y divide-white/[.04]">
            <?php $__currentLoopData = $reports; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rep): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php $ss = $statusStyle[$rep['status']] ?? $statusStyle['pending']; ?>
            <a href="<?php echo e(route('citizen.reports.show', $rep['id'])); ?>"
               class="flex items-center gap-3 px-5 py-4 hover:bg-white/[.025] transition-colors group">
                
                <div class="w-9 h-9 rounded-xl bg-white/5 flex items-center justify-center shrink-0">
                    <span class="w-3 h-3 rounded-full <?php echo e($ss['dot']); ?>

                        <?php echo e($rep['status']==='in_progress' ? 'animate-pulse' : ''); ?>"></span>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-0.5">
                        <span class="text-[10px] font-mono font-bold text-cyan-400"><?php echo e($rep['id']); ?></span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold <?php echo e($ss['bg']); ?> <?php echo e($ss['text']); ?>">
                            <?php echo e($ss['label']); ?>

                        </span>
                    </div>
                    <p class="text-white text-sm font-semibold truncate"><?php echo e($rep['type']); ?></p>
                    <p class="text-cyan-100/45 text-xs truncate"><?php echo e($rep['zone']); ?> · <?php echo e($rep['created_at']); ?></p>
                </div>
                <i data-lucide="chevron-right" class="w-4 h-4 text-cyan-100/25 group-hover:text-cyan-400 transition-colors shrink-0"></i>
            </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
        <div class="px-5 py-3 border-t border-white/5">
            <a href="<?php echo e(route('citizen.reports.create')); ?>"
               class="flex items-center justify-center gap-2 w-full py-2.5 bg-gradient-to-r from-cyan-500/10 to-blue-600/10
                      border border-cyan-400/20 rounded-xl text-cyan-300 text-sm font-semibold
                      hover:from-cyan-500/20 hover:to-blue-600/20 transition-all hover:border-cyan-400/40">
                <i data-lucide="plus" class="w-4 h-4"></i>
                Signaler un nouveau problème
            </a>
        </div>
        <?php endif; ?>
    </div>

    
    <div class="glass rounded-2xl overflow-hidden">
        <div class="px-5 py-4 border-b border-white/5 flex items-center justify-between">
            <div>
                <h2 class="text-white font-display font-bold">Notifications</h2>
                <?php if($unreadNotifs > 0): ?>
                <p class="text-cyan-100/50 text-xs mt-0.5">
                    <span class="text-amber-300 font-semibold"><?php echo e($unreadNotifs); ?></span> non lue(s)
                </p>
                <?php else: ?>
                <p class="text-cyan-100/50 text-xs mt-0.5">Tout est lu</p>
                <?php endif; ?>
            </div>
            <a href="<?php echo e(route('citizen.notifications')); ?>"
               class="text-xs text-cyan-400 hover:text-cyan-300 font-semibold transition-colors">
                Tout voir →
            </a>
        </div>
        <div class="divide-y divide-white/[.04]">
            <?php $__currentLoopData = array_slice($notifs, 0, 4); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notif): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
                $nb = match($notif['color']) {
                    'amber'   => ['bg'=>'bg-amber-500/15','ic'=>'text-amber-400'],
                    'emerald' => ['bg'=>'bg-teal-500/15', 'ic'=>'text-teal-400'],
                    'blue'    => ['bg'=>'bg-blue-500/15', 'ic'=>'text-blue-400'],
                    'purple'  => ['bg'=>'bg-violet-500/15','ic'=>'text-violet-400'],
                    default   => ['bg'=>'bg-cyan-500/15', 'ic'=>'text-cyan-400'],
                };
            ?>
            <div class="flex items-start gap-3 px-5 py-3.5 hover:bg-white/[.02] transition-colors
                        <?php echo e(!$notif['read'] ? 'bg-white/[.015]' : ''); ?>">
                <div class="w-8 h-8 rounded-lg <?php echo e($nb['bg']); ?> flex items-center justify-center shrink-0 mt-0.5">
                    <i data-lucide="<?php echo e($notif['icon']); ?>" class="w-3.5 h-3.5 <?php echo e($nb['ic']); ?>"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-1.5 mb-0.5">
                        <?php if(!$notif['read']): ?>
                        <span class="w-1.5 h-1.5 rounded-full bg-cyan-400 shrink-0"></span>
                        <?php endif; ?>
                        <p class="text-white text-xs font-semibold truncate"><?php echo e($notif['title']); ?></p>
                    </div>
                    <p class="text-cyan-100/50 text-[11px] leading-tight"><?php echo e($notif['message']); ?></p>
                </div>
                <span class="text-[10px] text-cyan-100/30 shrink-0 mt-0.5 whitespace-nowrap"><?php echo e($notif['time']); ?></span>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>

    
    <div class="glass rounded-2xl overflow-hidden">
        <div class="px-5 py-4 border-b border-white/5 flex items-center justify-between">
            <h2 class="text-white font-display font-bold">Factures</h2>
            <a href="<?php echo e(route('citizen.invoices.index')); ?>"
               class="text-xs text-cyan-400 hover:text-cyan-300 font-semibold transition-colors">
                Tout voir →
            </a>
        </div>
        <div class="divide-y divide-white/[.04]">
            <?php $__currentLoopData = array_slice($invoices, 0, 3); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $inv): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
                $is = $inv['status'] === 'paid'
                    ? ['bg'=>'bg-teal-500/15','text'=>'text-teal-300','label'=>'Payée']
                    : ['bg'=>'bg-amber-500/15','text'=>'text-amber-300','label'=>'En attente'];
            ?>
            <a href="<?php echo e(route('citizen.invoices.show', $inv['id'])); ?>"
               class="flex items-center gap-3 px-5 py-3.5 hover:bg-white/[.025] transition-colors group">
                <div class="w-9 h-9 rounded-xl bg-cyan-500/10 flex items-center justify-center shrink-0">
                    <i data-lucide="file-text" class="w-4 h-4 text-cyan-400"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-white text-sm font-semibold"><?php echo e($inv['month']); ?></p>
                    <p class="text-cyan-100/45 text-xs">Échéance : <?php echo e($inv['due_date']); ?></p>
                </div>
                <div class="flex items-center gap-3 shrink-0">
                    <span class="text-white font-bold text-sm"><?php echo e($inv['amount']); ?> TND</span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold <?php echo e($is['bg']); ?> <?php echo e($is['text']); ?>">
                        <?php echo e($is['label']); ?>

                    </span>
                </div>
            </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>

    
    <div class="glass rounded-2xl p-6 text-center">
        <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-cyan-500/20 to-blue-600/20 border border-cyan-400/20
                     flex items-center justify-center mx-auto mb-4">
            <i data-lucide="alert-circle" class="w-7 h-7 text-cyan-300"></i>
        </div>
        <h3 class="text-white font-display font-bold text-lg mb-1">Vous constatez un problème ?</h3>
        <p class="text-cyan-100/55 text-sm mb-4">Signalez-le immédiatement pour une intervention rapide.</p>
        <a href="<?php echo e(route('citizen.reports.create')); ?>"
           class="inline-flex items-center gap-2 bg-gradient-to-r from-cyan-500 to-blue-600
                  hover:from-cyan-400 hover:to-blue-500 text-white font-semibold px-6 py-3 rounded-xl
                  transition-all hover-lift shadow-lg shadow-cyan-500/20">
            <i data-lucide="plus" class="w-4 h-4"></i>
            Signaler un incident
        </a>
    </div>

</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', () => {
    if (typeof lucide !== 'undefined') lucide.createIcons();
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.frontoffice', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\ghada\Desktop\5ème\projet_Laravel\aquasecure\resources\views/citizen/dashboard.blade.php ENDPATH**/ ?>