<?php $__env->startSection('title', 'Travaux de maintenance'); ?>

<?php $__env->startSection('frontoffice-content'); ?>
<div class="space-y-8">
    
    <div class="flex items-center gap-3">
        <a href="<?php echo e(route('citizen.dashboard')); ?>" class="w-10 h-10 rounded-2xl glass flex items-center justify-center text-cyan-300 hover:text-white hover:bg-white/10 transition-all shadow-sm shrink-0" title="Retour au tableau de bord">
            <i data-lucide="arrow-left" class="w-5 h-5"></i>
        </a>
        <div>
            <h1 class="text-2xl sm:text-3xl font-display font-bold text-white tracking-tight">Travaux de maintenance</h1>
            <p class="text-cyan-100/60 text-xs sm:text-sm mt-0.5">Suivez les interventions de nos équipes sur le réseau d'eau potable</p>
        </div>
    </div>

    
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <?php if (isset($component)) { $__componentOriginaldae4cd48acb67888a4631e1ba48f2f93 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.card','data' => ['padding' => 'sm','class' => 'flex items-center gap-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['padding' => 'sm','class' => 'flex items-center gap-4']); ?>
            <div class="w-12 h-12 rounded-xl bg-amber-500/10 border border-amber-400/20 flex items-center justify-center"><i data-lucide="construction" class="w-6 h-6 text-amber-400"></i></div>
            <div>
                <p class="text-3xl font-display font-bold text-white"><?php echo e($enCours->count()); ?></p>
                <p class="text-xs text-cyan-100/60">Travaux en cours</p>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.card','data' => ['padding' => 'sm','class' => 'flex items-center gap-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['padding' => 'sm','class' => 'flex items-center gap-4']); ?>
            <div class="w-12 h-12 rounded-xl bg-cyan-500/10 border border-cyan-400/20 flex items-center justify-center"><i data-lucide="calendar-clock" class="w-6 h-6 text-cyan-400"></i></div>
            <div>
                <p class="text-3xl font-display font-bold text-white"><?php echo e($aVenir->count()); ?></p>
                <p class="text-xs text-cyan-100/60">Interventions à venir</p>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.card','data' => ['padding' => 'sm','class' => 'flex items-center gap-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['padding' => 'sm','class' => 'flex items-center gap-4']); ?>
            <div class="w-12 h-12 rounded-xl bg-emerald-500/10 border border-emerald-400/20 flex items-center justify-center"><i data-lucide="check-circle-2" class="w-6 h-6 text-emerald-400"></i></div>
            <div>
                <p class="text-3xl font-display font-bold text-white"><?php echo e($termineesCeMois); ?></p>
                <p class="text-xs text-cyan-100/60">Terminées ce mois-ci</p>
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

    <?php if($enCours->isNotEmpty() || $aVenir->isNotEmpty()): ?>
        <?php if (isset($component)) { $__componentOriginal746de018ded8594083eb43be3f1332e1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal746de018ded8594083eb43be3f1332e1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.alert','data' => ['type' => 'warning','title' => 'Perturbations possibles']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.alert'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'warning','title' => 'Perturbations possibles']); ?>
            Pendant certains travaux, des baisses de pression ou de courtes coupures d'eau peuvent survenir. Merci de votre compréhension.
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal746de018ded8594083eb43be3f1332e1)): ?>
<?php $attributes = $__attributesOriginal746de018ded8594083eb43be3f1332e1; ?>
<?php unset($__attributesOriginal746de018ded8594083eb43be3f1332e1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal746de018ded8594083eb43be3f1332e1)): ?>
<?php $component = $__componentOriginal746de018ded8594083eb43be3f1332e1; ?>
<?php unset($__componentOriginal746de018ded8594083eb43be3f1332e1); ?>
<?php endif; ?>
    <?php endif; ?>

    
    <?php $__currentLoopData = [
        ['titre' => 'En cours', 'icone' => 'construction', 'items' => $enCours, 'vide' => 'Aucun travail en cours actuellement.'],
        ['titre' => 'À venir', 'icone' => 'calendar-clock', 'items' => $aVenir, 'vide' => 'Aucune intervention planifiée.'],
        ['titre' => 'Récemment terminées', 'icone' => 'check-circle-2', 'items' => $terminees, 'vide' => 'Aucune intervention terminée récemment.'],
    ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $section): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <section>
            <h2 class="text-lg font-display font-bold text-white mb-4 flex items-center gap-2">
                <i data-lucide="<?php echo e($section['icone']); ?>" class="w-5 h-5 text-cyan-300"></i>
                <?php echo e($section['titre']); ?>

                <span class="text-xs font-semibold text-cyan-100/50">(<?php echo e($section['items']->count()); ?>)</span>
            </h2>

            <?php if($section['items']->isEmpty()): ?>
                <?php if (isset($component)) { $__componentOriginaldae4cd48acb67888a4631e1ba48f2f93 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.card','data' => ['padding' => 'sm']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['padding' => 'sm']); ?><p class="text-sm text-cyan-100/50 text-center py-4"><?php echo e($section['vide']); ?></p> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93)): ?>
<?php $attributes = $__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93; ?>
<?php unset($__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaldae4cd48acb67888a4631e1ba48f2f93)): ?>
<?php $component = $__componentOriginaldae4cd48acb67888a4631e1ba48f2f93; ?>
<?php unset($__componentOriginaldae4cd48acb67888a4631e1ba48f2f93); ?>
<?php endif; ?>
            <?php else: ?>
                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <?php $__currentLoopData = $section['items']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $intervention): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php if (isset($component)) { $__componentOriginal20773148f4862e81ead45e2dd3ae2573 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal20773148f4862e81ead45e2dd3ae2573 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.maintenance.travail-card','data' => ['intervention' => $intervention]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('maintenance.travail-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['intervention' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($intervention)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal20773148f4862e81ead45e2dd3ae2573)): ?>
<?php $attributes = $__attributesOriginal20773148f4862e81ead45e2dd3ae2573; ?>
<?php unset($__attributesOriginal20773148f4862e81ead45e2dd3ae2573); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal20773148f4862e81ead45e2dd3ae2573)): ?>
<?php $component = $__componentOriginal20773148f4862e81ead45e2dd3ae2573; ?>
<?php unset($__componentOriginal20773148f4862e81ead45e2dd3ae2573); ?>
<?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php endif; ?>
        </section>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.frontoffice', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\monta\Downloads\DEVWAVE\Esprit-5TWIN4-DevWave-main (1)\Esprit-5TWIN4-DevWave-main\resources\views/citizen/travaux/index.blade.php ENDPATH**/ ?>