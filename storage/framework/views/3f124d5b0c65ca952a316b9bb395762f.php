<?php $__env->startSection('admin-content'); ?>
<div class="min-h-screen bg-gradient-to-br from-slate-900 via-cyan-950 to-slate-900">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-white mb-2 flex items-center gap-3">
                    <i data-lucide="shield-alert" class="w-8 h-8 text-red-400"></i>
                    Sécurité & Alertes
                </h1>
                <p class="text-slate-400">Surveillance des menaces et incidents de sécurité</p>
            </div>
            <div class="flex items-center gap-3">
                <div class="flex items-center gap-2 px-4 py-2 rounded-lg glass">
                    <div class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></div>
                    <span class="text-sm text-white font-medium">Protection active</span>
                </div>
            </div>
        </div>

        <!-- Security Score -->
        <?php if (isset($component)) { $__componentOriginaldae4cd48acb67888a4631e1ba48f2f93 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.card','data' => ['class' => 'mb-8']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mb-8']); ?>
            <div class="text-center py-6">
                <div class="inline-flex items-center justify-center w-24 h-24 rounded-full bg-emerald-500/20 mb-4 relative">
                    <div class="absolute inset-0 rounded-full border-4 border-emerald-500/30"></div>
                    <div class="absolute inset-0 rounded-full border-4 border-emerald-500 border-t-transparent animate-spin" style="animation-duration: 3s;"></div>
                    <span class="text-3xl font-bold text-emerald-400">87</span>
                </div>
                <div class="text-2xl font-bold text-white mb-2">Score de Sécurité</div>
                <div class="text-slate-400">Bon niveau de protection - Quelques améliorations possibles</div>
                
                <div class="flex items-center justify-center gap-6 mt-6">
                    <div class="text-center">
                        <div class="text-lg font-bold text-emerald-400">12</div>
                        <div class="text-xs text-slate-400">Protections actives</div>
                    </div>
                    <div class="w-px h-8 bg-slate-700"></div>
                    <div class="text-center">
                        <div class="text-lg font-bold text-amber-400">3</div>
                        <div class="text-xs text-slate-400">Alertes actives</div>
                    </div>
                    <div class="w-px h-8 bg-slate-700"></div>
                    <div class="text-center">
                        <div class="text-lg font-bold text-cyan-400">45</div>
                        <div class="text-xs text-slate-400">Tentatives bloquées (24h)</div>
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

        <!-- Security Alerts -->
        <?php
            $alerts = \App\Data\PlaceholderData::adminSecurityAlerts();
        ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <div class="lg:col-span-2">
                <?php if (isset($component)) { $__componentOriginaldae4cd48acb67888a4631e1ba48f2f93 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.card','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
                    <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                        <i data-lucide="alert-triangle" class="w-5 h-5 text-amber-400"></i>
                        Alertes de Sécurité
                    </h3>

                    <div class="space-y-3">
                        <?php $__currentLoopData = $alerts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $alert): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="p-4 rounded-lg border-l-4 
                            <?php if($alert['severity'] === 'high'): ?> border-red-500 bg-red-500/10
                            <?php elseif($alert['severity'] === 'medium'): ?> border-amber-500 bg-amber-500/10
                            <?php else: ?> border-blue-500 bg-blue-500/10
                            <?php endif; ?>">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-2">
                                        <?php if (isset($component)) { $__componentOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.status-badge','data' => ['status' => $alert['severity'] === 'high' ? 'danger' : ($alert['severity'] === 'medium' ? 'warning' : 'info'),'label' => strtoupper($alert['severity'])]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($alert['severity'] === 'high' ? 'danger' : ($alert['severity'] === 'medium' ? 'warning' : 'info')),'label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(strtoupper($alert['severity']))]); ?>
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
                                        <span class="text-xs px-2 py-1 rounded bg-slate-700 text-slate-300">
                                            <?php echo e(str_replace('_', ' ', $alert['type'])); ?>

                                        </span>
                                    </div>
                                    <p class="text-white font-medium mb-1"><?php echo e($alert['message']); ?></p>
                                    <div class="flex items-center gap-3 text-xs text-slate-400">
                                        <span class="flex items-center gap-1">
                                            <i data-lucide="clock" class="w-3 h-3"></i>
                                            <?php echo e($alert['timestamp']); ?>

                                        </span>
                                        <span class="flex items-center gap-1">
                                            <i data-lucide="tag" class="w-3 h-3"></i>
                                            <?php echo e(ucfirst($alert['status'])); ?>

                                        </span>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <?php if($alert['status'] === 'active'): ?>
                                    <button class="text-emerald-400 hover:text-emerald-300 transition-colors" title="Résoudre">
                                        <i data-lucide="check-circle" class="w-5 h-5"></i>
                                    </button>
                                    <?php endif; ?>
                                    <button class="text-cyan-400 hover:text-cyan-300 transition-colors" title="Détails">
                                        <i data-lucide="eye" class="w-5 h-5"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                        <?php if(empty($alerts)): ?>
                        <?php if (isset($component)) { $__componentOriginal3607a477fdef7402bc742abad5df9c51 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3607a477fdef7402bc742abad5df9c51 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.empty-state','data' => ['icon' => 'shield-check','title' => 'Aucune alerte active','message' => 'Aucune menace détectée - Le système est sécurisé']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'shield-check','title' => 'Aucune alerte active','message' => 'Aucune menace détectée - Le système est sécurisé']); ?>
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

                    <div class="mt-4 pt-4 border-t border-slate-700">
                        <a href="#" class="text-sm text-cyan-400 hover:text-cyan-300 flex items-center gap-1">
                            Voir toutes les alertes
                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </a>
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

            <div>
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
                    <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                        <i data-lucide="activity" class="w-5 h-5 text-cyan-400"></i>
                        Activité Suspecte
                    </h3>

                    <div class="space-y-4">
                        <div class="p-3 rounded-lg bg-red-500/10 border border-red-500/20">
                            <div class="flex items-center gap-2 mb-2">
                                <i data-lucide="user-x" class="w-4 h-4 text-red-400"></i>
                                <span class="text-sm font-medium text-white">Connexions échouées</span>
                            </div>
                            <div class="text-2xl font-bold text-red-400 mb-1">15</div>
                            <div class="text-xs text-slate-400">Dernières 24h</div>
                        </div>

                        <div class="p-3 rounded-lg bg-amber-500/10 border border-amber-500/20">
                            <div class="flex items-center gap-2 mb-2">
                                <i data-lucide="globe" class="w-4 h-4 text-amber-400"></i>
                                <span class="text-sm font-medium text-white">IP bloquées</span>
                            </div>
                            <div class="text-2xl font-bold text-amber-400 mb-1">8</div>
                            <div class="text-xs text-slate-400">Actives</div>
                        </div>

                        <div class="p-3 rounded-lg bg-blue-500/10 border border-blue-500/20">
                            <div class="flex items-center gap-2 mb-2">
                                <i data-lucide="zap" class="w-4 h-4 text-blue-400"></i>
                                <span class="text-sm font-medium text-white">Limite API dépassée</span>
                            </div>
                            <div class="text-2xl font-bold text-blue-400 mb-1">3</div>
                            <div class="text-xs text-slate-400">Aujourd'hui</div>
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

                <?php if (isset($component)) { $__componentOriginaldae4cd48acb67888a4631e1ba48f2f93 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.card','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
                    <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                        <i data-lucide="lock" class="w-5 h-5 text-emerald-400"></i>
                        Actions Rapides
                    </h3>

                    <div class="space-y-2">
                        <button class="w-full btn btn-secondary text-sm justify-start">
                            <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                            Forcer renouvellement sessions
                        </button>
                        <button class="w-full btn btn-secondary text-sm justify-start">
                            <i data-lucide="shield-off" class="w-4 h-4"></i>
                            Bloquer IP suspecte
                        </button>
                        <button class="w-full btn btn-secondary text-sm justify-start">
                            <i data-lucide="key" class="w-4 h-4"></i>
                            Révoquer token API
                        </button>
                        <button class="w-full btn btn-secondary text-sm justify-start">
                            <i data-lucide="user-minus" class="w-4 h-4"></i>
                            Désactiver utilisateur
                        </button>
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
        </div>

        <!-- Security Features -->
        <?php if (isset($component)) { $__componentOriginaldae4cd48acb67888a4631e1ba48f2f93 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.card','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
            <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                <i data-lucide="shield-check" class="w-5 h-5 text-emerald-400"></i>
                Protections Actives
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <?php
                    $features = [
                        ['name' => 'Authentification 2FA', 'status' => true, 'icon' => 'smartphone'],
                        ['name' => 'SSL/TLS', 'status' => true, 'icon' => 'lock'],
                        ['name' => 'Pare-feu applicatif', 'status' => true, 'icon' => 'shield'],
                        ['name' => 'Protection DDoS', 'status' => true, 'icon' => 'shield-alert'],
                        ['name' => 'Détection d\'intrusion', 'status' => true, 'icon' => 'radar'],
                        ['name' => 'Chiffrement base de données', 'status' => true, 'icon' => 'database'],
                        ['name' => 'Logs d\'audit', 'status' => true, 'icon' => 'file-text'],
                        ['name' => 'Rate limiting API', 'status' => true, 'icon' => 'zap'],
                        ['name' => 'Validation CSRF', 'status' => true, 'icon' => 'check-square'],
                    ];
                ?>

                <?php $__currentLoopData = $features; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $feature): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="p-4 rounded-lg glass-strong">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-emerald-500/20 flex items-center justify-center">
                                <i data-lucide="<?php echo e($feature['icon']); ?>" class="w-5 h-5 text-emerald-400"></i>
                            </div>
                            <span class="text-sm font-medium text-white"><?php echo e($feature['name']); ?></span>
                        </div>
                        <div class="flex items-center gap-2">
                            <?php if($feature['status']): ?>
                            <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                            <span class="text-xs text-emerald-400">Actif</span>
                            <?php else: ?>
                            <span class="w-2 h-2 rounded-full bg-slate-600"></span>
                            <span class="text-xs text-slate-400">Inactif</span>
                            <?php endif; ?>
                        </div>
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
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\monta\Downloads\DEVWAVE\Esprit-5TWIN4-DevWave-main (1)\Esprit-5TWIN4-DevWave-main\resources\views\admin\security.blade.php ENDPATH**/ ?>