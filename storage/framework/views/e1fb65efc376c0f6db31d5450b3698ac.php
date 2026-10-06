<?php $__env->startSection('title', 'Espace Technicien — AquaSecure'); ?>

<?php
    use App\Data\PlaceholderData;
    $user          = session('user', ['name' => 'Amira Ben Ali', 'role' => 'technician']);
    $interventions = PlaceholderData::technicianInterventions();
    $equipment     = PlaceholderData::technicianEquipment();
    $zones         = PlaceholderData::technicianZones();

    $firstName     = explode(' ', $user['name'])[0];
    $today         = count($interventions);
    $inProgress    = count(array_filter($interventions, fn($i)=>$i['status']==='in_progress'));
    $scheduled     = count(array_filter($interventions, fn($i)=>$i['status']==='scheduled'));
    $completed     = count(array_filter($interventions, fn($i)=>$i['status']==='completed'));
    $urgent        = count(array_filter($interventions, fn($i)=>$i['priority']==='high'));

    $statusStyle = [
        'in_progress' => ['bg'=>'bg-amber-500/15','text'=>'text-amber-300','dot'=>'bg-amber-400 animate-pulse','label'=>'En cours'],
        'scheduled'   => ['bg'=>'bg-blue-500/15', 'text'=>'text-blue-300', 'dot'=>'bg-blue-400',              'label'=>'Planifiée'],
        'completed'   => ['bg'=>'bg-teal-500/15', 'text'=>'text-teal-300', 'dot'=>'bg-teal-400',              'label'=>'Terminée'],
    ];
    $priorityStyle = [
        'high'   => ['bg'=>'bg-red-500/15',  'text'=>'text-red-300',  'label'=>'Urgent'],
        'medium' => ['bg'=>'bg-amber-500/15','text'=>'text-amber-300','label'=>'Moyen'],
        'low'    => ['bg'=>'bg-blue-500/15', 'text'=>'text-blue-300', 'label'=>'Faible'],
    ];
?>

<?php $__env->startSection('content'); ?>
<div class="min-h-screen">


<nav class="sticky top-0 z-50 glass-strong px-4 sm:px-6 py-3 flex items-center justify-between">
    <div class="flex items-center gap-2">
        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-cyan-400 to-blue-600 flex items-center justify-center">
            <i data-lucide="droplet" class="w-5 h-5 text-white"></i>
        </div>
        <span class="font-display font-bold text-white hidden sm:block">AquaSecure</span>
        <span class="hidden sm:inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold bg-cyan-500/15 text-cyan-300 border border-cyan-400/20">
            Espace Technicien
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

<div class="container mx-auto px-4 py-6 max-w-5xl space-y-6 animate-fade-in-up">

    
    <div class="glass rounded-2xl p-5">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h1 class="text-2xl font-display font-bold text-white">
                    Bonjour, <?php echo e($firstName); ?> 👋
                </h1>
                <p class="text-cyan-100/55 text-sm mt-0.5">Technicien · Interventions terrain</p>
                <p class="text-cyan-100/65 text-sm mt-2">
                    Vous avez <strong class="text-white"><?php echo e($today); ?></strong> interventions
                    <?php if($inProgress > 0): ?>
                        · <strong class="text-amber-300"><?php echo e($inProgress); ?></strong> en cours
                    <?php endif; ?>
                    <?php if($urgent > 0): ?>
                        · <strong class="text-red-300"><?php echo e($urgent); ?></strong> urgente(s)
                    <?php endif; ?>
                </p>
            </div>
            <a href="<?php echo e(route('technician.interventions.index')); ?>"
               class="shrink-0 glass px-4 py-2 rounded-xl text-sm text-cyan-300 hover:text-white font-semibold
                      flex items-center gap-2 transition-all hover:border-cyan-400/40">
                <i data-lucide="list" class="w-4 h-4"></i>
                <span class="hidden sm:inline">Toutes les interventions</span>
            </a>
        </div>
    </div>

    
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <?php $__currentLoopData = [
            ['val'=>$today,    'label'=>'Aujourd\'hui',  'icon'=>'calendar',     'bg'=>'bg-cyan-500/10',  'ic'=>'text-cyan-400'],
            ['val'=>$inProgress,'label'=>'En cours',      'icon'=>'loader',        'bg'=>'bg-amber-500/10', 'ic'=>'text-amber-400'],
            ['val'=>$completed, 'label'=>'Terminées',     'icon'=>'check-circle',  'bg'=>'bg-teal-500/10',  'ic'=>'text-teal-400'],
            ['val'=>$urgent,    'label'=>'Urgentes',      'icon'=>'alert-triangle','bg'=>'bg-red-500/10',   'ic'=>'text-red-400'],
        ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $kpi): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="glass rounded-2xl p-4 hover-lift">
            <div class="w-10 h-10 rounded-xl <?php echo e($kpi['bg']); ?> flex items-center justify-center mb-3">
                <i data-lucide="<?php echo e($kpi['icon']); ?>" class="w-5 h-5 <?php echo e($kpi['ic']); ?>"></i>
            </div>
            <p class="text-2xl font-display font-bold text-white"><?php echo e($kpi['val']); ?></p>
            <p class="text-xs text-cyan-100/55 mt-0.5"><?php echo e($kpi['label']); ?></p>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    
    <?php $urgentInt = array_values(array_filter($interventions, fn($i)=>$i['priority']==='high' && $i['status']==='in_progress'))[0] ?? null; ?>
    <?php if($urgentInt): ?>
    <div class="glass rounded-2xl p-5 border-red-500/30 ring-1 ring-red-500/20">
        <div class="flex items-start justify-between gap-4">
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-xl bg-red-500/15 flex items-center justify-center shrink-0">
                    <i data-lucide="alert-triangle" class="w-5 h-5 text-red-400 animate-pulse"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <span class="text-[10px] font-mono font-bold text-red-400"><?php echo e($urgentInt['id']); ?></span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-500/15 text-red-300">URGENT</span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/15 text-amber-300">En cours</span>
                    </div>
                    <p class="text-white font-bold"><?php echo e($urgentInt['type']); ?></p>
                    <p class="text-cyan-100/55 text-xs mt-0.5">
                        <i data-lucide="map-pin" class="w-3 h-3 inline"></i>
                        <?php echo e($urgentInt['zone']); ?> — <?php echo e($urgentInt['address']); ?>

                    </p>
                </div>
            </div>
            <a href="<?php echo e(route('technician.interventions.show', $urgentInt['id'])); ?>"
               class="shrink-0 bg-gradient-to-r from-red-500/20 to-red-600/20 border border-red-500/30
                      text-red-300 hover:text-white text-xs font-semibold px-4 py-2 rounded-xl transition-all">
                Voir →
            </a>
        </div>
    </div>
    <?php endif; ?>

    
    <div class="glass rounded-2xl overflow-hidden">
        <div class="px-5 py-4 border-b border-white/5 flex items-center justify-between">
            <div>
                <h2 class="text-white font-display font-bold">Mes interventions</h2>
                <p class="text-cyan-100/50 text-xs mt-0.5"><?php echo e($today); ?> assignées · <?php echo e($scheduled); ?> planifiées</p>
            </div>
            <a href="<?php echo e(route('technician.interventions.index')); ?>"
               class="text-xs text-cyan-400 hover:text-cyan-300 font-semibold transition-colors">
                Tout voir →
            </a>
        </div>
        <div class="divide-y divide-white/[.04]">
            <?php $__currentLoopData = $interventions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $int): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
                $ss = $statusStyle[$int['status']] ?? $statusStyle['scheduled'];
                $ps = $priorityStyle[$int['priority']] ?? $priorityStyle['low'];
            ?>
            <div class="flex items-center gap-3 px-5 py-4 hover:bg-white/[.025] transition-colors">
                
                <div class="w-9 h-9 rounded-xl <?php echo e($ps['bg']); ?> flex items-center justify-center shrink-0">
                    <?php if($int['priority']==='high'): ?>
                    <i data-lucide="alert-triangle" class="w-4 h-4 <?php echo e($ps['text']); ?>"></i>
                    <?php elseif($int['status']==='in_progress'): ?>
                    <i data-lucide="loader" class="w-4 h-4 <?php echo e($ss['text']); ?>"></i>
                    <?php else: ?>
                    <i data-lucide="wrench" class="w-4 h-4 text-cyan-400/70"></i>
                    <?php endif; ?>
                </div>
                
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-0.5 flex-wrap">
                        <span class="text-[10px] font-mono font-bold text-cyan-400"><?php echo e($int['id']); ?></span>
                        <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold <?php echo e($ps['bg']); ?> <?php echo e($ps['text']); ?>"><?php echo e($ps['label']); ?></span>
                        <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold <?php echo e($ss['bg']); ?> <?php echo e($ss['text']); ?>"><?php echo e($ss['label']); ?></span>
                    </div>
                    <p class="text-white text-sm font-semibold truncate"><?php echo e($int['type']); ?></p>
                    <p class="text-cyan-100/45 text-xs truncate">
                        <i data-lucide="map-pin" class="w-3 h-3 inline"></i>
                        <?php echo e($int['zone']); ?> · <?php echo e($int['scheduled_time']); ?>

                    </p>
                </div>
                
                <div class="flex gap-1.5 shrink-0">
                    <?php if($int['status']==='scheduled'): ?>
                    <button onclick="showToast('Intervention démarrée', 'success')"
                            class="glass p-2 rounded-lg text-teal-400 hover:text-white transition-colors"
                            title="Démarrer">
                        <i data-lucide="play" class="w-3.5 h-3.5"></i>
                    </button>
                    <?php elseif($int['status']==='in_progress'): ?>
                    <a href="<?php echo e(route('technician.interventions.report', $int['id'])); ?>"
                       class="glass p-2 rounded-lg text-amber-400 hover:text-white transition-colors"
                       title="Rapport">
                        <i data-lucide="file-text" class="w-3.5 h-3.5"></i>
                    </a>
                    <?php endif; ?>
                    <a href="<?php echo e(route('technician.interventions.show', $int['id'])); ?>"
                       class="glass p-2 rounded-lg text-cyan-400/60 hover:text-cyan-300 transition-colors"
                       title="Détails">
                        <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                    </a>
                </div>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>

    
    <div class="grid sm:grid-cols-2 gap-6">

        
        <div class="glass rounded-2xl overflow-hidden">
            <div class="px-5 py-4 border-b border-white/5 flex items-center justify-between">
                <h2 class="text-white font-display font-bold text-sm">Mon équipement</h2>
                <a href="<?php echo e(route('technician.equipment')); ?>"
                   class="text-xs text-cyan-400 hover:text-cyan-300 font-semibold transition-colors">
                    Tout voir →
                </a>
            </div>
            <div class="divide-y divide-white/[.04]">
                <?php $__currentLoopData = array_slice($equipment, 0, 5); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $eq): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $eqStyle = [
                        'available' => ['dot'=>'bg-teal-400','text'=>'text-teal-300','label'=>'OK'],
                        'in_use'    => ['dot'=>'bg-amber-400','text'=>'text-amber-300','label'=>'Utilisé'],
                        'low_stock' => ['dot'=>'bg-red-400 animate-pulse','text'=>'text-red-300','label'=>'Stock bas'],
                        'maintenance'=>['dot'=>'bg-blue-400','text'=>'text-blue-300','label'=>'Maintenance'],
                    ][$eq['status']] ?? ['dot'=>'bg-slate-400','text'=>'text-slate-300','label'=>$eq['status']];
                ?>
                <div class="flex items-center gap-3 px-5 py-3 hover:bg-white/[.02] transition-colors">
                    <span class="w-2 h-2 rounded-full <?php echo e($eqStyle['dot']); ?> shrink-0"></span>
                    <span class="text-sm text-cyan-100/75 flex-1 truncate"><?php echo e($eq['name']); ?></span>
                    <span class="text-xs font-semibold <?php echo e($eqStyle['text']); ?> shrink-0"><?php echo e($eqStyle['label']); ?></span>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>

        
        <div class="space-y-4">
            <div class="glass rounded-2xl overflow-hidden">
                <div class="px-5 py-4 border-b border-white/5">
                    <h2 class="text-white font-display font-bold text-sm">Mes zones</h2>
                </div>
                <div class="divide-y divide-white/[.04]">
                    <?php $__currentLoopData = $zones; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $zone): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="flex items-center gap-3 px-5 py-3 hover:bg-white/[.02] transition-colors">
                        <span class="text-lg shrink-0"><?php echo e($zone['emoji']); ?></span>
                        <div class="flex-1 min-w-0">
                            <p class="text-white text-sm font-semibold"><?php echo e($zone['name']); ?></p>
                            <p class="text-cyan-100/40 text-xs"><?php echo e($zone['sensors']); ?> capteurs · <?php echo e($zone['quality']); ?>% qualité</p>
                        </div>
                        <span class="w-2 h-2 rounded-full shrink-0
                            <?php echo e($zone['status']==='normal' ? 'bg-teal-400' : ($zone['status']==='alert' ? 'bg-amber-400' : 'bg-red-400')); ?>">
                        </span>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>

            
            <div class="glass rounded-2xl p-4 text-center">
                <p class="text-xs text-cyan-100/50 mb-3">Besoin d'aide ?</p>
                <button onclick="showToast('Appel dispatching en cours...', 'info')"
                        class="flex items-center justify-center gap-2 w-full bg-red-500/10 border border-red-500/25
                               text-red-300 hover:text-white hover:bg-red-500/20 text-sm font-semibold
                               py-2.5 rounded-xl transition-all">
                    <i data-lucide="phone" class="w-4 h-4"></i>
                    Appeler le dispatching
                </button>
            </div>
        </div>
    </div>

    
    <?php if($completed > 0): ?>
    <div class="glass rounded-2xl overflow-hidden">
        <div class="px-5 py-4 border-b border-white/5">
            <h2 class="text-white font-display font-bold text-sm">Récemment terminées</h2>
        </div>
        <div class="divide-y divide-white/[.04]">
            <?php $__currentLoopData = array_values(array_filter($interventions, fn($i)=>$i['status']==='completed')); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $int): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="flex items-center gap-3 px-5 py-3.5 hover:bg-white/[.02] transition-colors">
                <div class="w-8 h-8 rounded-lg bg-teal-500/10 flex items-center justify-center shrink-0">
                    <i data-lucide="check" class="w-4 h-4 text-teal-400"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-white text-xs font-semibold truncate"><?php echo e($int['type']); ?></p>
                    <p class="text-cyan-100/40 text-[11px]"><?php echo e($int['zone']); ?></p>
                </div>
                <span class="text-[10px] text-teal-300/70 font-semibold shrink-0">
                    <?php echo e(isset($int['completed_at']) ? $int['completed_at'] : 'Terminée'); ?>

                </span>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
    <?php endif; ?>

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

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\ghada\Desktop\5ème\projet_Laravel\aquasecure\resources\views/technician/dashboard.blade.php ENDPATH**/ ?>