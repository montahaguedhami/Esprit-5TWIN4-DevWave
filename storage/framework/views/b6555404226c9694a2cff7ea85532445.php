<?php $__env->startSection('admin-content'); ?>
<div class="min-h-screen bg-gradient-to-br from-slate-900 via-cyan-950 to-slate-900">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-white mb-2 flex items-center gap-3">
                    <i data-lucide="activity" class="w-8 h-8 text-emerald-400"></i>
                    Santé du Système
                </h1>
                <p class="text-slate-400">Surveillance en temps réel des performances</p>
            </div>
            <div class="flex items-center gap-3">
                <div class="flex items-center gap-2 px-4 py-2 rounded-lg glass">
                    <div class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></div>
                    <span class="text-sm text-white font-medium">Système opérationnel</span>
                </div>
                <button class="btn btn-secondary">
                    <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                    Actualiser
                </button>
            </div>
        </div>

        <?php
            $metrics = \App\Data\PlaceholderData::adminSystemMetrics();
        ?>

        <!-- System Uptime -->
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
            <div class="text-center py-6">
                <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-emerald-500/20 mb-4">
                    <i data-lucide="zap" class="w-10 h-10 text-emerald-400"></i>
                </div>
                <div class="text-5xl font-bold text-white mb-2"><?php echo e($metrics['uptime']); ?></div>
                <div class="text-slate-400">Disponibilité du système (30 derniers jours)</div>
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

        <!-- Performance Metrics -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- CPU Usage -->
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
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-blue-500/20 flex items-center justify-center">
                            <i data-lucide="cpu" class="w-5 h-5 text-blue-400"></i>
                        </div>
                        <div>
                            <div class="text-sm text-slate-400">CPU</div>
                            <div class="text-2xl font-bold text-white"><?php echo e($metrics['cpu_usage']); ?>%</div>
                        </div>
                    </div>
                </div>
                <div class="w-full bg-slate-700 rounded-full h-2">
                    <div class="bg-blue-500 h-2 rounded-full transition-all duration-500" style="width: <?php echo e($metrics['cpu_usage']); ?>%"></div>
                </div>
                <div class="mt-2 text-xs text-slate-400">
                    <?php if($metrics['cpu_usage'] < 60): ?>
                    <span class="text-emerald-400">● Normal</span>
                    <?php elseif($metrics['cpu_usage'] < 80): ?>
                    <span class="text-amber-400">● Élevé</span>
                    <?php else: ?>
                    <span class="text-red-400">● Critique</span>
                    <?php endif; ?>
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

            <!-- Memory Usage -->
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
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-purple-500/20 flex items-center justify-center">
                            <i data-lucide="hard-drive" class="w-5 h-5 text-purple-400"></i>
                        </div>
                        <div>
                            <div class="text-sm text-slate-400">Mémoire</div>
                            <div class="text-2xl font-bold text-white"><?php echo e($metrics['memory_usage']); ?>%</div>
                        </div>
                    </div>
                </div>
                <div class="w-full bg-slate-700 rounded-full h-2">
                    <div class="bg-purple-500 h-2 rounded-full transition-all duration-500" style="width: <?php echo e($metrics['memory_usage']); ?>%"></div>
                </div>
                <div class="mt-2 text-xs text-slate-400">
                    <?php if($metrics['memory_usage'] < 70): ?>
                    <span class="text-emerald-400">● Normal</span>
                    <?php elseif($metrics['memory_usage'] < 85): ?>
                    <span class="text-amber-400">● Élevé</span>
                    <?php else: ?>
                    <span class="text-red-400">● Critique</span>
                    <?php endif; ?>
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

            <!-- Disk Usage -->
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
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-cyan-500/20 flex items-center justify-center">
                            <i data-lucide="database" class="w-5 h-5 text-cyan-400"></i>
                        </div>
                        <div>
                            <div class="text-sm text-slate-400">Disque</div>
                            <div class="text-2xl font-bold text-white"><?php echo e($metrics['disk_usage']); ?>%</div>
                        </div>
                    </div>
                </div>
                <div class="w-full bg-slate-700 rounded-full h-2">
                    <div class="bg-cyan-500 h-2 rounded-full transition-all duration-500" style="width: <?php echo e($metrics['disk_usage']); ?>%"></div>
                </div>
                <div class="mt-2 text-xs text-slate-400">
                    <span class="text-emerald-400">● Normal</span>
                    <span class="ml-2"><?php echo e($metrics['database_size']); ?></span>
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

            <!-- Active Sessions -->
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
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-emerald-500/20 flex items-center justify-center">
                            <i data-lucide="users" class="w-5 h-5 text-emerald-400"></i>
                        </div>
                        <div>
                            <div class="text-sm text-slate-400">Sessions</div>
                            <div class="text-2xl font-bold text-white"><?php echo e($metrics['active_sessions']); ?></div>
                        </div>
                    </div>
                </div>
                <div class="mt-2 text-xs text-slate-400">
                    Utilisateurs actifs en ce moment
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

        <!-- API & Database Stats -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
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
                    <i data-lucide="zap" class="w-5 h-5 text-cyan-400"></i>
                    Performance API
                </h3>
                
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400">Requêtes aujourd'hui</span>
                        <span class="text-xl font-bold text-white"><?php echo e(number_format($metrics['api_requests_today'])); ?></span>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400">Temps de réponse moyen</span>
                        <span class="text-xl font-bold text-emerald-400"><?php echo e($metrics['avg_response_time']); ?></span>
                    </div>

                    <div class="pt-4 border-t border-slate-700">
                        <div class="text-sm text-slate-400 mb-2">Répartition des requêtes (24h)</div>
                        <div class="space-y-2">
                            <div class="flex items-center gap-2">
                                <div class="flex-1">
                                    <div class="flex items-center justify-between text-xs mb-1">
                                        <span class="text-slate-400">GET</span>
                                        <span class="text-white">65%</span>
                                    </div>
                                    <div class="w-full bg-slate-700 rounded-full h-1.5">
                                        <div class="bg-cyan-500 h-1.5 rounded-full" style="width: 65%"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="flex-1">
                                    <div class="flex items-center justify-between text-xs mb-1">
                                        <span class="text-slate-400">POST</span>
                                        <span class="text-white">25%</span>
                                    </div>
                                    <div class="w-full bg-slate-700 rounded-full h-1.5">
                                        <div class="bg-blue-500 h-1.5 rounded-full" style="width: 25%"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="flex-1">
                                    <div class="flex items-center justify-between text-xs mb-1">
                                        <span class="text-slate-400">PUT/DELETE</span>
                                        <span class="text-white">10%</span>
                                    </div>
                                    <div class="w-full bg-slate-700 rounded-full h-1.5">
                                        <div class="bg-purple-500 h-1.5 rounded-full" style="width: 10%"></div>
                                    </div>
                                </div>
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
                    <i data-lucide="database" class="w-5 h-5 text-purple-400"></i>
                    Base de données
                </h3>
                
                <div class="space-y-4">
                    <div class="p-4 rounded-lg bg-slate-800/50">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-slate-400 text-sm">Taille de la base</span>
                            <span class="text-lg font-bold text-white"><?php echo e($metrics['database_size']); ?></span>
                        </div>
                        <div class="w-full bg-slate-700 rounded-full h-2">
                            <div class="bg-purple-500 h-2 rounded-full" style="width: 38%"></div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="p-4 rounded-lg bg-slate-800/50 text-center">
                            <div class="text-2xl font-bold text-cyan-400">1,247</div>
                            <div class="text-xs text-slate-400 mt-1">Utilisateurs</div>
                        </div>
                        <div class="p-4 rounded-lg bg-slate-800/50 text-center">
                            <div class="text-2xl font-bold text-blue-400">3,582</div>
                            <div class="text-xs text-slate-400 mt-1">Incidents</div>
                        </div>
                        <div class="p-4 rounded-lg bg-slate-800/50 text-center">
                            <div class="text-2xl font-bold text-emerald-400">8,921</div>
                            <div class="text-xs text-slate-400 mt-1">Interventions</div>
                        </div>
                        <div class="p-4 rounded-lg bg-slate-800/50 text-center">
                            <div class="text-2xl font-bold text-purple-400">12</div>
                            <div class="text-xs text-slate-400 mt-1">Zones</div>
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
        </div>

        <!-- Services Status -->
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
                <i data-lucide="server" class="w-5 h-5 text-cyan-400"></i>
                État des Services
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <?php
                    $services = [
                        ['name' => 'API Principal', 'status' => 'operational', 'uptime' => '99.9%', 'latency' => '45ms'],
                        ['name' => 'Base de données', 'status' => 'operational', 'uptime' => '100%', 'latency' => '12ms'],
                        ['name' => 'File d\'attente', 'status' => 'operational', 'uptime' => '99.7%', 'latency' => '8ms'],
                        ['name' => 'Stockage', 'status' => 'operational', 'uptime' => '99.8%', 'latency' => '23ms'],
                    ];
                ?>

                <?php $__currentLoopData = $services; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $service): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="p-4 rounded-lg glass-strong">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-sm font-medium text-white"><?php echo e($service['name']); ?></span>
                        <?php if (isset($component)) { $__componentOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.status-badge','data' => ['status' => 'success','label' => 'Opérationnel']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => 'success','label' => 'Opérationnel']); ?>
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
                    <div class="space-y-2 text-xs">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-400">Disponibilité</span>
                            <span class="text-emerald-400 font-medium"><?php echo e($service['uptime']); ?></span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-400">Latence</span>
                            <span class="text-cyan-400 font-medium"><?php echo e($service['latency']); ?></span>
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

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\monta\Downloads\DEVWAVE\Esprit-5TWIN4-DevWave-main (1)\Esprit-5TWIN4-DevWave-main\resources\views\admin\system.blade.php ENDPATH**/ ?>