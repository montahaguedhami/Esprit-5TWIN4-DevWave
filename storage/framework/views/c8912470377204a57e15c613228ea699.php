<?php $__env->startSection('title', 'Notifications'); ?>

<?php
    use App\Data\PlaceholderData;
    $notifications = PlaceholderData::citizenNotifications();
    $unreadCount = count(array_filter($notifications, fn($n) => !$n['read']));
?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6">
    <!-- Header with Back Navigation -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div class="flex items-center gap-3">
            <a href="<?php echo e(route('citizen.dashboard')); ?>" class="w-10 h-10 rounded-2xl glass flex items-center justify-center text-cyan-300 hover:text-white hover:bg-white/10 transition-all shadow-sm shrink-0" title="Retour au tableau de bord">
                <i data-lucide="arrow-left" class="w-5 h-5"></i>
            </a>
            <div>
                <h1 class="text-2xl sm:text-3xl font-display font-bold text-white tracking-tight">Notifications & Coupures</h1>
                <p class="text-cyan-100/60 text-xs sm:text-sm mt-0.5">
                    <?php if($unreadCount > 0): ?>
                    Vous avez <span class="text-cyan-300 font-semibold"><?php echo e($unreadCount); ?> notification(s) non lue(s)</span>
                    <?php else: ?>
                    Toutes vos notifications sont à jour
                    <?php endif; ?>
                </p>
            </div>
        </div>
        
        <div class="flex items-center gap-2">
            <?php if($unreadCount > 0): ?>
            <?php if (isset($component)) { $__componentOriginala8bb031a483a05f647cb99ed3a469847 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala8bb031a483a05f647cb99ed3a469847 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.button','data' => ['variant' => 'outline','size' => 'sm','icon' => 'check-check']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'outline','size' => 'sm','icon' => 'check-check']); ?>
                Tout marquer comme lu
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala8bb031a483a05f647cb99ed3a469847)): ?>
<?php $attributes = $__attributesOriginala8bb031a483a05f647cb99ed3a469847; ?>
<?php unset($__attributesOriginala8bb031a483a05f647cb99ed3a469847); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala8bb031a483a05f647cb99ed3a469847)): ?>
<?php $component = $__componentOriginala8bb031a483a05f647cb99ed3a469847; ?>
<?php unset($__componentOriginala8bb031a483a05f647cb99ed3a469847); ?>
<?php endif; ?>
            <?php endif; ?>
            <?php if (isset($component)) { $__componentOriginala8bb031a483a05f647cb99ed3a469847 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala8bb031a483a05f647cb99ed3a469847 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.button','data' => ['variant' => 'outline','size' => 'sm','icon' => 'settings']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'outline','size' => 'sm','icon' => 'settings']); ?>
                Paramètres
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala8bb031a483a05f647cb99ed3a469847)): ?>
<?php $attributes = $__attributesOriginala8bb031a483a05f647cb99ed3a469847; ?>
<?php unset($__attributesOriginala8bb031a483a05f647cb99ed3a469847); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala8bb031a483a05f647cb99ed3a469847)): ?>
<?php $component = $__componentOriginala8bb031a483a05f647cb99ed3a469847; ?>
<?php unset($__componentOriginala8bb031a483a05f647cb99ed3a469847); ?>
<?php endif; ?>
        </div>
    </div>

    <div class="grid lg:grid-cols-4 gap-6">
        <!-- Filters Sidebar -->
        <div class="lg:col-span-1">
            <?php if (isset($component)) { $__componentOriginaldae4cd48acb67888a4631e1ba48f2f93 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.card','data' => ['padding' => 'sm']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['padding' => 'sm']); ?>
                <h3 class="text-sm font-display font-bold text-white mb-4 px-2">Filtrer par type</h3>
                
                <div class="space-y-1">
                    <?php $__currentLoopData = [
                        ['value' => 'all', 'label' => 'Toutes', 'icon' => 'inbox', 'count' => count($notifications)],
                        ['value' => 'unread', 'label' => 'Non lues', 'icon' => 'mail', 'count' => $unreadCount],
                        ['value' => 'report_update', 'label' => 'Déclarations', 'icon' => 'alert-circle', 'count' => 2],
                        ['value' => 'water_quality', 'label' => 'Qualité eau', 'icon' => 'flask', 'count' => 1],
                        ['value' => 'maintenance', 'label' => 'Maintenance', 'icon' => 'wrench', 'count' => 1],
                        ['value' => 'invoice', 'label' => 'Factures', 'icon' => 'receipt', 'count' => 1],
                    ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $filter): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <button class="filter-btn w-full flex items-center justify-between px-3 py-2.5 rounded-lg text-left transition-colors <?php echo e($filter['value'] === 'all' ? 'bg-cyan-500/10 text-white' : 'text-cyan-100/60 hover:bg-white/5'); ?>" data-filter="<?php echo e($filter['value']); ?>">
                        <div class="flex items-center gap-3">
                            <i data-lucide="<?php echo e($filter['icon']); ?>" class="w-4 h-4"></i>
                            <span class="text-sm font-medium"><?php echo e($filter['label']); ?></span>
                        </div>
                        <span class="text-xs <?php echo e($filter['value'] === 'all' ? 'text-cyan-300' : 'text-cyan-100/40'); ?>"><?php echo e($filter['count']); ?></span>
                    </button>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93)): ?>
<?php $attributes = $__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93; ?>
<?php unset($__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaldae4cd48acb67888a4631e1ba48f2f93)): ?>
<?php $component = $__componentOriginaldae4cd48acb67888a4631e1ba48f2f93; ?>
<?php unset($__componentOriginaldae4cd48acb67888a4631e1ba48f2f93); ?>
<?php endif; ?>
        </div>

        <!-- Notifications List -->
        <div class="lg:col-span-3">
            <!-- Search -->
            <div class="mb-6">
                <?php if (isset($component)) { $__componentOriginalf6ee3670073e124e2f361de392ee6597 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf6ee3670073e124e2f361de392ee6597 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.search-input','data' => ['placeholder' => 'Rechercher dans les notifications...']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.search-input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['placeholder' => 'Rechercher dans les notifications...']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf6ee3670073e124e2f361de392ee6597)): ?>
<?php $attributes = $__attributesOriginalf6ee3670073e124e2f361de392ee6597; ?>
<?php unset($__attributesOriginalf6ee3670073e124e2f361de392ee6597); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf6ee3670073e124e2f361de392ee6597)): ?>
<?php $component = $__componentOriginalf6ee3670073e124e2f361de392ee6597; ?>
<?php unset($__componentOriginalf6ee3670073e124e2f361de392ee6597); ?>
<?php endif; ?>
            </div>

            <!-- Notifications -->
            <div class="space-y-3">
                <?php $__empty_1 = true; $__currentLoopData = $notifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notification): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php if (isset($component)) { $__componentOriginaldae4cd48acb67888a4631e1ba48f2f93 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.card','data' => ['hover' => true,'padding' => 'none','class' => 'notification-item '.e($notification['read'] ? 'opacity-75' : '').'','dataType' => ''.e($notification['type']).'','dataRead' => ''.e($notification['read'] ? 'true' : 'false').'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['hover' => true,'padding' => 'none','class' => 'notification-item '.e($notification['read'] ? 'opacity-75' : '').'','data-type' => ''.e($notification['type']).'','data-read' => ''.e($notification['read'] ? 'true' : 'false').'']); ?>
                    <div class="flex gap-4 p-5">
                        <!-- Icon -->
                        <div class="w-12 h-12 rounded-xl bg-<?php echo e($notification['color']); ?>-500/10 flex items-center justify-center flex-shrink-0">
                            <i data-lucide="<?php echo e($notification['icon']); ?>" class="w-6 h-6 text-<?php echo e($notification['color']); ?>-400"></i>
                        </div>
                        
                        <!-- Content -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-3 mb-2">
                                <h4 class="font-semibold text-white"><?php echo e($notification['title']); ?></h4>
                                <?php if(!$notification['read']): ?>
                                <span class="w-2 h-2 rounded-full bg-cyan-400 flex-shrink-0 mt-1.5"></span>
                                <?php endif; ?>
                            </div>
                            <p class="text-sm text-cyan-100/70 mb-3"><?php echo e($notification['message']); ?></p>
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-cyan-100/40">
                                    <i data-lucide="clock" class="w-3.5 h-3.5 inline mr-1"></i>
                                    Il y a <?php echo e($notification['time']); ?>

                                </span>
                                <div class="flex items-center gap-2">
                                    <?php if(!$notification['read']): ?>
                                    <button class="text-xs text-cyan-400 hover:text-cyan-300 transition-colors font-medium">
                                        Marquer comme lu
                                    </button>
                                    <?php endif; ?>
                                    <button class="text-xs text-cyan-100/50 hover:text-cyan-100/80 transition-colors">
                                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93)): ?>
<?php $attributes = $__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93; ?>
<?php unset($__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaldae4cd48acb67888a4631e1ba48f2f93)): ?>
<?php $component = $__componentOriginaldae4cd48acb67888a4631e1ba48f2f93; ?>
<?php unset($__componentOriginaldae4cd48acb67888a4631e1ba48f2f93); ?>
<?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <?php if (isset($component)) { $__componentOriginal3607a477fdef7402bc742abad5df9c51 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3607a477fdef7402bc742abad5df9c51 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.empty-state','data' => ['icon' => 'inbox','title' => 'Aucune notification','description' => 'Vous n\'avez pas encore de notifications']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'inbox','title' => 'Aucune notification','description' => 'Vous n\'avez pas encore de notifications']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3607a477fdef7402bc742abad5df9c51)): ?>
<?php $attributes = $__attributesOriginal3607a477fdef7402bc742abad5df9c51; ?>
<?php unset($__attributesOriginal3607a477fdef7402bc742abad5df9c51); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3607a477fdef7402bc742abad5df9c51)): ?>
<?php $component = $__componentOriginal3607a477fdef7402bc742abad5df9c51; ?>
<?php unset($__componentOriginal3607a477fdef7402bc742abad5df9c51); ?>
<?php endif; ?>
                <?php endif; ?>
            </div>

            <!-- Load More -->
            <?php if(count($notifications) > 0): ?>
            <div class="mt-6 text-center">
                <?php if (isset($component)) { $__componentOriginala8bb031a483a05f647cb99ed3a469847 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala8bb031a483a05f647cb99ed3a469847 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.button','data' => ['variant' => 'outline','icon' => 'refresh-cw']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'outline','icon' => 'refresh-cw']); ?>
                    Charger plus
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala8bb031a483a05f647cb99ed3a469847)): ?>
<?php $attributes = $__attributesOriginala8bb031a483a05f647cb99ed3a469847; ?>
<?php unset($__attributesOriginala8bb031a483a05f647cb99ed3a469847); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala8bb031a483a05f647cb99ed3a469847)): ?>
<?php $component = $__componentOriginala8bb031a483a05f647cb99ed3a469847; ?>
<?php unset($__componentOriginala8bb031a483a05f647cb99ed3a469847); ?>
<?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
// Filter functionality
document.querySelectorAll('.filter-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const filter = this.dataset.filter;
        
        // Update active state
        document.querySelectorAll('.filter-btn').forEach(b => {
            b.classList.remove('bg-cyan-500/10', 'text-white');
            b.classList.add('text-cyan-100/60');
        });
        this.classList.add('bg-cyan-500/10', 'text-white');
        this.classList.remove('text-cyan-100/60');
        
        // Filter notifications
        document.querySelectorAll('.notification-item').forEach(item => {
            const type = item.dataset.type;
            const read = item.dataset.read === 'true';
            
            if (filter === 'all') {
                item.classList.remove('hidden');
            } else if (filter === 'unread') {
                item.classList.toggle('hidden', read);
            } else {
                item.classList.toggle('hidden', type !== filter);
            }
        });
    });
});
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.frontoffice', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\monta\Downloads\Aqua_Secure-main\Aqua_Secure-main\resources\views/citizen/notifications.blade.php ENDPATH**/ ?>