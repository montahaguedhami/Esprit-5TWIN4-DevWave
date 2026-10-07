<?php $__env->startSection('title', 'Mes factures'); ?>

<?php
    use App\Data\PlaceholderData;
    $invoices = PlaceholderData::citizenInvoices();
    $pendingCount = count(array_filter($invoices, fn($i) => $i['status'] === 'pending'));
    $totalPending = array_sum(array_map(fn($i) => $i['status'] === 'pending' ? $i['amount'] : 0, $invoices));
?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6">
    <!-- Header with Back Navigation -->
    <div class="flex items-center gap-3 mb-6">
        <a href="<?php echo e(route('citizen.dashboard')); ?>" class="w-10 h-10 rounded-2xl glass flex items-center justify-center text-cyan-300 hover:text-white hover:bg-white/10 transition-all shadow-sm shrink-0" title="Retour au tableau de bord">
            <i data-lucide="arrow-left" class="w-5 h-5"></i>
        </a>
        <div>
            <h1 class="text-2xl sm:text-3xl font-display font-bold text-white tracking-tight">Mes factures & Consommation</h1>
            <p class="text-cyan-100/60 text-xs sm:text-sm mt-0.5">Consultez votre historique de consommation et réglez vos factures d'eau</p>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <?php if (isset($component)) { $__componentOriginal73e8909751d40929eb36b5991fae6a61 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal73e8909751d40929eb36b5991fae6a61 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard.kpi-card','data' => ['icon' => 'receipt','title' => 'Factures en attente','value' => $pendingCount,'color' => 'amber','subtitle' => ''.e($totalPending).' TND']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard.kpi-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'receipt','title' => 'Factures en attente','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($pendingCount),'color' => 'amber','subtitle' => ''.e($totalPending).' TND']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal73e8909751d40929eb36b5991fae6a61)): ?>
<?php $attributes = $__attributesOriginal73e8909751d40929eb36b5991fae6a61; ?>
<?php unset($__attributesOriginal73e8909751d40929eb36b5991fae6a61); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal73e8909751d40929eb36b5991fae6a61)): ?>
<?php $component = $__componentOriginal73e8909751d40929eb36b5991fae6a61; ?>
<?php unset($__componentOriginal73e8909751d40929eb36b5991fae6a61); ?>
<?php endif; ?>
        
        <?php if (isset($component)) { $__componentOriginal73e8909751d40929eb36b5991fae6a61 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal73e8909751d40929eb36b5991fae6a61 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard.kpi-card','data' => ['icon' => 'check-circle','title' => 'Factures payées','value' => count($invoices) - $pendingCount,'color' => 'emerald']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard.kpi-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'check-circle','title' => 'Factures payées','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(count($invoices) - $pendingCount),'color' => 'emerald']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal73e8909751d40929eb36b5991fae6a61)): ?>
<?php $attributes = $__attributesOriginal73e8909751d40929eb36b5991fae6a61; ?>
<?php unset($__attributesOriginal73e8909751d40929eb36b5991fae6a61); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal73e8909751d40929eb36b5991fae6a61)): ?>
<?php $component = $__componentOriginal73e8909751d40929eb36b5991fae6a61; ?>
<?php unset($__componentOriginal73e8909751d40929eb36b5991fae6a61); ?>
<?php endif; ?>
        
        <?php if (isset($component)) { $__componentOriginal73e8909751d40929eb36b5991fae6a61 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal73e8909751d40929eb36b5991fae6a61 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard.kpi-card','data' => ['icon' => 'droplet','title' => 'Consommation moy.','value' => '19.6 m³','color' => 'cyan','subtitle' => 'Par mois']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard.kpi-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'droplet','title' => 'Consommation moy.','value' => '19.6 m³','color' => 'cyan','subtitle' => 'Par mois']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal73e8909751d40929eb36b5991fae6a61)): ?>
<?php $attributes = $__attributesOriginal73e8909751d40929eb36b5991fae6a61; ?>
<?php unset($__attributesOriginal73e8909751d40929eb36b5991fae6a61); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal73e8909751d40929eb36b5991fae6a61)): ?>
<?php $component = $__componentOriginal73e8909751d40929eb36b5991fae6a61; ?>
<?php unset($__componentOriginal73e8909751d40929eb36b5991fae6a61); ?>
<?php endif; ?>
        
        <?php if (isset($component)) { $__componentOriginal73e8909751d40929eb36b5991fae6a61 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal73e8909751d40929eb36b5991fae6a61 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard.kpi-card','data' => ['icon' => 'trending-down','title' => 'Économie','value' => '-5%','color' => 'teal','subtitle' => 'vs mois dernier','trend' => 'down','trendValue' => '-5%']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard.kpi-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'trending-down','title' => 'Économie','value' => '-5%','color' => 'teal','subtitle' => 'vs mois dernier','trend' => 'down','trend-value' => '-5%']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal73e8909751d40929eb36b5991fae6a61)): ?>
<?php $attributes = $__attributesOriginal73e8909751d40929eb36b5991fae6a61; ?>
<?php unset($__attributesOriginal73e8909751d40929eb36b5991fae6a61); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal73e8909751d40929eb36b5991fae6a61)): ?>
<?php $component = $__componentOriginal73e8909751d40929eb36b5991fae6a61; ?>
<?php unset($__componentOriginal73e8909751d40929eb36b5991fae6a61); ?>
<?php endif; ?>
    </div>

    <!-- Actions & Filters -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
        <div class="flex items-center gap-2 flex-wrap">
            <button class="status-filter px-5 py-2.5 rounded-lg font-semibold transition-colors bg-cyan-500/10 text-white border border-cyan-400/20" data-status="all" style="font-size:var(--font-size-sm);">
                Toutes
            </button>
            <button class="status-filter px-5 py-2.5 rounded-lg font-semibold transition-colors text-cyan-100/60 hover:bg-white/5" data-status="pending" style="font-size:var(--font-size-sm);">
                En attente
            </button>
            <button class="status-filter px-5 py-2.5 rounded-lg font-semibold transition-colors text-cyan-100/60 hover:bg-white/5" data-status="paid" style="font-size:var(--font-size-sm);">
                Payées
            </button>
        </div>
        
        <div class="flex items-center gap-2">
            <?php if (isset($component)) { $__componentOriginala8bb031a483a05f647cb99ed3a469847 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala8bb031a483a05f647cb99ed3a469847 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.button','data' => ['variant' => 'outline','size' => 'sm','icon' => 'download']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'outline','size' => 'sm','icon' => 'download']); ?>
                Télécharger tout
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
            <?php if (isset($component)) { $__componentOriginala8bb031a483a05f647cb99ed3a469847 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala8bb031a483a05f647cb99ed3a469847 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.button','data' => ['size' => 'sm','icon' => 'credit-card']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['size' => 'sm','icon' => 'credit-card']); ?>
                Payer maintenant
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

    <!-- Invoices List -->
    <div class="space-y-4">
        <?php $__empty_1 = true; $__currentLoopData = $invoices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $invoice): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <?php if (isset($component)) { $__componentOriginaldae4cd48acb67888a4631e1ba48f2f93 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.card','data' => ['hover' => true,'class' => 'invoice-item','dataStatus' => ''.e($invoice['status']).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['hover' => true,'class' => 'invoice-item','data-status' => ''.e($invoice['status']).'']); ?>
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                <!-- Invoice Info -->
                <div class="flex items-start gap-4 flex-1">
                    <!-- Icon -->
                    <div class="w-14 h-14 rounded-xl bg-gradient-to-br <?php echo e($invoice['status'] === 'pending' ? 'from-amber-500/20 to-orange-600/20 border border-amber-400/25' : 'from-emerald-500/20 to-teal-600/20 border border-emerald-400/25'); ?> flex items-center justify-center flex-shrink-0">
                        <i data-lucide="receipt" class="w-7 h-7 <?php echo e($invoice['status'] === 'pending' ? 'text-amber-400' : 'text-emerald-400'); ?>"></i>
                    </div>
                    
                    <!-- Details -->
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-2">
                            <h3 class="text-lg font-display font-bold text-white"><?php echo e($invoice['id']); ?></h3>
                            <?php if (isset($component)) { $__componentOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.status-badge','data' => ['status' => $invoice['status'] === 'pending' ? 'warning' : 'success']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($invoice['status'] === 'pending' ? 'warning' : 'success')]); ?>
                                <?php echo e($invoice['status'] === 'pending' ? 'À payer' : 'Payée'); ?>

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
                        
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-x-6 gap-y-2 text-sm">
                            <div>
                                <span class="text-cyan-100/50 block">Période</span>
                                <span class="text-white font-medium"><?php echo e($invoice['month']); ?></span>
                            </div>
                            <div>
                                <span class="text-cyan-100/50 block">Consommation</span>
                                <span class="text-white font-medium"><?php echo e($invoice['consumption']); ?> m³</span>
                            </div>
                            <div>
                                <span class="text-cyan-100/50 block">Date d'émission</span>
                                <span class="text-white font-medium"><?php echo e(\Carbon\Carbon::parse($invoice['issue_date'])->format('d/m/Y')); ?></span>
                            </div>
                            <div>
                                <span class="text-cyan-100/50 block">
                                    <?php echo e($invoice['status'] === 'pending' ? 'Date limite' : 'Payée le'); ?>

                                </span>
                                <span class="text-white font-medium">
                                    <?php if($invoice['status'] === 'pending'): ?>
                                        <?php echo e(\Carbon\Carbon::parse($invoice['due_date'])->format('d/m/Y')); ?>

                                    <?php else: ?>
                                        <?php echo e(\Carbon\Carbon::parse($invoice['paid_date'])->format('d/m/Y')); ?>

                                    <?php endif; ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Amount & Actions -->
                <div class="flex flex-row lg:flex-col items-center lg:items-end justify-between lg:justify-start gap-4">
                    <div class="text-right">
                        <div class="text-sm text-cyan-100/50 mb-1">Montant</div>
                        <div class="text-2xl font-display font-bold text-white"><?php echo e(number_format($invoice['amount'], 2)); ?> <span class="text-lg text-cyan-100/50">TND</span></div>
                    </div>
                    
                    <div class="flex items-center gap-2">
                        <a href="<?php echo e(route('citizen.invoices.show', ['id' => $invoice['id']])); ?>">
                            <?php if (isset($component)) { $__componentOriginala8bb031a483a05f647cb99ed3a469847 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala8bb031a483a05f647cb99ed3a469847 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.button','data' => ['variant' => 'outline','size' => 'sm','icon' => 'eye']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'outline','size' => 'sm','icon' => 'eye']); ?>
                                Voir
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
                        </a>
                        <?php if($invoice['status'] === 'pending'): ?>
                        <?php if (isset($component)) { $__componentOriginala8bb031a483a05f647cb99ed3a469847 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala8bb031a483a05f647cb99ed3a469847 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.button','data' => ['size' => 'sm','icon' => 'credit-card']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['size' => 'sm','icon' => 'credit-card']); ?>
                            Payer
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
                        <?php else: ?>
                        <?php if (isset($component)) { $__componentOriginala8bb031a483a05f647cb99ed3a469847 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala8bb031a483a05f647cb99ed3a469847 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.button','data' => ['variant' => 'outline','size' => 'sm','icon' => 'download']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'outline','size' => 'sm','icon' => 'download']); ?>
                            PDF
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.empty-state','data' => ['icon' => 'receipt','title' => 'Aucune facture','description' => 'Vous n\'avez pas encore de factures']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'receipt','title' => 'Aucune facture','description' => 'Vous n\'avez pas encore de factures']); ?>
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

    <!-- Help Section -->
    <?php if (isset($component)) { $__componentOriginaldae4cd48acb67888a4631e1ba48f2f93 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.card','data' => ['class' => 'mt-8']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mt-8']); ?>
        <div class="flex items-start gap-4">
            <div class="w-12 h-12 rounded-xl bg-cyan-500/10 flex items-center justify-center flex-shrink-0">
                <i data-lucide="help-circle" class="w-6 h-6 text-cyan-400"></i>
            </div>
            <div class="flex-1">
                <h3 class="text-lg font-display font-bold text-white mb-2">Besoin d'aide ?</h3>
                <p class="text-sm text-cyan-100/70 mb-4">
                    Vous avez des questions sur vos factures ou sur votre consommation ?
                </p>
                <div class="flex flex-wrap gap-3">
                    <?php if (isset($component)) { $__componentOriginala8bb031a483a05f647cb99ed3a469847 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala8bb031a483a05f647cb99ed3a469847 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.button','data' => ['variant' => 'outline','size' => 'sm','icon' => 'phone']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'outline','size' => 'sm','icon' => 'phone']); ?>
                        Contacter le support
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
                    <?php if (isset($component)) { $__componentOriginala8bb031a483a05f647cb99ed3a469847 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala8bb031a483a05f647cb99ed3a469847 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.button','data' => ['variant' => 'outline','size' => 'sm','icon' => 'file-text']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'outline','size' => 'sm','icon' => 'file-text']); ?>
                        Guide de facturation
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
                    <?php if (isset($component)) { $__componentOriginala8bb031a483a05f647cb99ed3a469847 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala8bb031a483a05f647cb99ed3a469847 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.button','data' => ['variant' => 'outline','size' => 'sm','icon' => 'calculator']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'outline','size' => 'sm','icon' => 'calculator']); ?>
                        Simuler ma facture
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

<?php $__env->startPush('scripts'); ?>
<script>
// Filter invoices by status
document.querySelectorAll('.status-filter').forEach(btn => {
    btn.addEventListener('click', function() {
        const status = this.dataset.status;
        
        // Update active state
        document.querySelectorAll('.status-filter').forEach(b => {
            b.classList.remove('bg-cyan-500/10', 'text-white', 'border-cyan-400/20');
            b.classList.add('text-cyan-100/60');
        });
        this.classList.add('bg-cyan-500/10', 'text-white', 'border-cyan-400/20');
        this.classList.remove('text-cyan-100/60');
        
        // Filter invoices
        document.querySelectorAll('.invoice-item').forEach(item => {
            const itemStatus = item.dataset.status;
            
            if (status === 'all') {
                item.classList.remove('hidden');
            } else {
                item.classList.toggle('hidden', itemStatus !== status);
            }
        });
    });
});
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.frontoffice', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Ranim\Documents\GitHub\Esprit-5TWIN4-DevWave\resources\views/citizen/invoices/index.blade.php ENDPATH**/ ?>