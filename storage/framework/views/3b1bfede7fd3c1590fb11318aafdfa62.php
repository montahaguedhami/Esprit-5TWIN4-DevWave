<?php $__env->startSection('content'); ?>
<div class="min-h-screen bg-gradient-to-br from-slate-900 via-cyan-950 to-slate-900">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="flex items-center gap-4 mb-8">
            <a href="<?php echo e(route('settings.index')); ?>" class="p-2 rounded-lg glass hover:glass-strong transition-all">
                <i data-lucide="arrow-left" class="w-5 h-5 text-cyan-400"></i>
            </a>
            <div>
                <h1 class="text-3xl font-bold text-white flex items-center gap-3">
                    <i data-lucide="bell" class="w-8 h-8 text-purple-400"></i>
                    Paramètres de Notifications
                </h1>
                <p class="text-slate-400">Gérez comment et quand vous recevez les notifications</p>
            </div>
        </div>

        <div class="max-w-4xl">
            <!-- Notification Channels -->
            <?php if (isset($component)) { $__componentOriginaldae4cd48acb67888a4631e1ba48f2f93 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.card','data' => ['class' => 'mb-6']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mb-6']); ?>
                <h3 class="text-xl font-bold text-white mb-6 flex items-center gap-2">
                    <i data-lucide="send" class="w-6 h-6 text-cyan-400"></i>
                    Canaux de Notification
                </h3>

                <div class="space-y-4">
                    <?php $__currentLoopData = [
                        ['channel' => 'email', 'label' => 'Email', 'icon' => 'mail', 'color' => 'cyan', 'desc' => 'Recevoir les notifications par email', 'enabled' => true],
                        ['channel' => 'push', 'label' => 'Notifications Push', 'icon' => 'smartphone', 'color' => 'blue', 'desc' => 'Notifications dans le navigateur', 'enabled' => true],
                        ['channel' => 'sms', 'label' => 'SMS', 'icon' => 'message-square', 'color' => 'purple', 'desc' => 'Recevoir les alertes critiques par SMS', 'enabled' => false],
                    ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $channel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="p-5 rounded-lg bg-slate-800/50 border border-slate-700">
                        <div class="flex items-start justify-between">
                            <div class="flex items-start gap-4">
                                <div class="w-12 h-12 rounded-lg bg-<?php echo e($channel['color']); ?>-500/20 flex items-center justify-center">
                                    <i data-lucide="<?php echo e($channel['icon']); ?>" class="w-6 h-6 text-<?php echo e($channel['color']); ?>-400"></i>
                                </div>
                                <div>
                                    <h4 class="text-base font-semibold text-white mb-1"><?php echo e($channel['label']); ?></h4>
                                    <p class="text-sm text-slate-400"><?php echo e($channel['desc']); ?></p>
                                </div>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" <?php echo e($channel['enabled'] ? 'checked' : ''); ?> class="sr-only peer">
                                <div class="w-11 h-6 bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-cyan-500"></div>
                            </label>
                        </div>
                    </div>
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

            <!-- Notification Types -->
            <?php if (isset($component)) { $__componentOriginaldae4cd48acb67888a4631e1ba48f2f93 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.card','data' => ['class' => 'mb-6']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mb-6']); ?>
                <h3 class="text-xl font-bold text-white mb-6 flex items-center gap-2">
                    <i data-lucide="list" class="w-6 h-6 text-blue-400"></i>
                    Types de Notifications
                </h3>

                <div class="space-y-3">
                    <?php $__currentLoopData = [
                        ['type' => 'Alertes de qualité', 'desc' => 'Problèmes de qualité de l\'eau détectés', 'email' => true, 'push' => true, 'sms' => true, 'priority' => 'high'],
                        ['type' => 'Incidents critiques', 'desc' => 'Fuites importantes, pannes majeures', 'email' => true, 'push' => true, 'sms' => true, 'priority' => 'high'],
                        ['type' => 'Maintenance programmée', 'desc' => 'Informations sur les interventions planifiées', 'email' => true, 'push' => true, 'sms' => false, 'priority' => 'medium'],
                        ['type' => 'Mises à jour de déclaration', 'desc' => 'Changements de statut de vos rapports', 'email' => true, 'push' => true, 'sms' => false, 'priority' => 'medium'],
                        ['type' => 'Factures', 'desc' => 'Nouvelles factures et rappels de paiement', 'email' => true, 'push' => false, 'sms' => false, 'priority' => 'low'],
                        ['type' => 'Rapports mensuels', 'desc' => 'Résumés mensuels et statistiques', 'email' => true, 'push' => false, 'sms' => false, 'priority' => 'low'],
                        ['type' => 'Newsletter', 'desc' => 'Actualités et nouvelles fonctionnalités', 'email' => false, 'push' => false, 'sms' => false, 'priority' => 'low'],
                    ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notif): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="p-4 rounded-lg bg-slate-800/50">
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-1">
                                    <h4 class="text-sm font-semibold text-white"><?php echo e($notif['type']); ?></h4>
                                    <?php if (isset($component)) { $__componentOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.status-badge','data' => ['status' => $notif['priority'] === 'high' ? 'danger' : ($notif['priority'] === 'medium' ? 'warning' : 'info'),'label' => strtoupper($notif['priority'])]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($notif['priority'] === 'high' ? 'danger' : ($notif['priority'] === 'medium' ? 'warning' : 'info')),'label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(strtoupper($notif['priority']))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8)): ?>
<?php $attributes = $__attributesOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8; ?>
<?php unset($__attributesOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8)): ?>
<?php $component = $__componentOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8; ?>
<?php unset($__componentOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8); ?>
<?php endif; ?>
                                </div>
                                <p class="text-xs text-slate-400"><?php echo e($notif['desc']); ?></p>
                            </div>
                        </div>
                        
                        <div class="flex items-center gap-4 text-xs">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" <?php echo e($notif['email'] ? 'checked' : ''); ?> class="rounded bg-slate-700 border-slate-600 text-cyan-500 focus:ring-cyan-500">
                                <span class="text-slate-400">Email</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" <?php echo e($notif['push'] ? 'checked' : ''); ?> class="rounded bg-slate-700 border-slate-600 text-cyan-500 focus:ring-cyan-500">
                                <span class="text-slate-400">Push</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" <?php echo e($notif['sms'] ? 'checked' : ''); ?> class="rounded bg-slate-700 border-slate-600 text-cyan-500 focus:ring-cyan-500">
                                <span class="text-slate-400">SMS</span>
                            </label>
                        </div>
                    </div>
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

            <!-- Quiet Hours -->
            <?php if (isset($component)) { $__componentOriginaldae4cd48acb67888a4631e1ba48f2f93 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.card','data' => ['class' => 'mb-6']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mb-6']); ?>
                <h3 class="text-xl font-bold text-white mb-6 flex items-center gap-2">
                    <i data-lucide="moon" class="w-6 h-6 text-purple-400"></i>
                    Heures de silence
                </h3>

                <div class="p-5 rounded-lg bg-slate-800/50 mb-4">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h4 class="text-base font-semibold text-white mb-1">Activer les heures de silence</h4>
                            <p class="text-sm text-slate-400">Ne pas recevoir de notifications pendant ces heures (sauf alertes critiques)</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" checked class="sr-only peer">
                            <div class="w-11 h-6 bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-cyan-500"></div>
                        </label>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-400 mb-2">De</label>
                            <?php if (isset($component)) { $__componentOriginal4fb6044c7ed6b655352043ff774efcd0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4fb6044c7ed6b655352043ff774efcd0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.forms.input','data' => ['type' => 'time','value' => '22:00','name' => 'quiet_start']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('forms.input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'time','value' => '22:00','name' => 'quiet_start']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal4fb6044c7ed6b655352043ff774efcd0)): ?>
<?php $attributes = $__attributesOriginal4fb6044c7ed6b655352043ff774efcd0; ?>
<?php unset($__attributesOriginal4fb6044c7ed6b655352043ff774efcd0); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal4fb6044c7ed6b655352043ff774efcd0)): ?>
<?php $component = $__componentOriginal4fb6044c7ed6b655352043ff774efcd0; ?>
<?php unset($__componentOriginal4fb6044c7ed6b655352043ff774efcd0); ?>
<?php endif; ?>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-400 mb-2">À</label>
                            <?php if (isset($component)) { $__componentOriginal4fb6044c7ed6b655352043ff774efcd0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4fb6044c7ed6b655352043ff774efcd0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.forms.input','data' => ['type' => 'time','value' => '08:00','name' => 'quiet_end']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('forms.input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'time','value' => '08:00','name' => 'quiet_end']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal4fb6044c7ed6b655352043ff774efcd0)): ?>
<?php $attributes = $__attributesOriginal4fb6044c7ed6b655352043ff774efcd0; ?>
<?php unset($__attributesOriginal4fb6044c7ed6b655352043ff774efcd0); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal4fb6044c7ed6b655352043ff774efcd0)): ?>
<?php $component = $__componentOriginal4fb6044c7ed6b655352043ff774efcd0; ?>
<?php unset($__componentOriginal4fb6044c7ed6b655352043ff774efcd0); ?>
<?php endif; ?>
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

            <!-- Save Button -->
            <div class="flex items-center justify-between">
                <button class="btn btn-secondary" onclick="history.back()">
                    Annuler
                </button>
                <button class="btn btn-primary" onclick="showToast('Paramètres de notifications sauvegardés', 'success')">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    Enregistrer les modifications
                </button>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\monta\Downloads\Aqua_Secure-main\Aqua_Secure-main\resources\views/settings/notifications.blade.php ENDPATH**/ ?>