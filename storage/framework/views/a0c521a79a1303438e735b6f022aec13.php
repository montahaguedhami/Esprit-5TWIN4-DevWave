<?php $__env->startSection('title', 'Détail des travaux'); ?>

<?php $__env->startSection('frontoffice-content'); ?>
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center gap-3">
        <a href="<?php echo e(route('citizen.travaux.index')); ?>" class="w-10 h-10 rounded-2xl glass flex items-center justify-center text-cyan-300 hover:text-white hover:bg-white/10 transition-all shadow-sm shrink-0" title="Retour aux travaux">
            <i data-lucide="arrow-left" class="w-5 h-5"></i>
        </a>
        <div>
            <h1 class="text-2xl sm:text-3xl font-display font-bold text-white tracking-tight">Intervention du <?php echo e($intervention->date->format('d/m/Y')); ?></h1>
            <p class="text-cyan-100/60 text-xs sm:text-sm mt-0.5">Équipe <?php echo e($intervention->technicien->specialite); ?></p>
        </div>
    </div>

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
        <div class="flex items-center justify-between gap-3 mb-4">
            <h2 class="font-display font-semibold text-white">Nature des travaux</h2>
            <?php if (isset($component)) { $__componentOriginal613a257712d79f8faaf5703f21bda327 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal613a257712d79f8faaf5703f21bda327 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.maintenance.badge','data' => ['value' => $intervention->statut]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('maintenance.badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($intervention->statut)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal613a257712d79f8faaf5703f21bda327)): ?>
<?php $attributes = $__attributesOriginal613a257712d79f8faaf5703f21bda327; ?>
<?php unset($__attributesOriginal613a257712d79f8faaf5703f21bda327); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal613a257712d79f8faaf5703f21bda327)): ?>
<?php $component = $__componentOriginal613a257712d79f8faaf5703f21bda327; ?>
<?php unset($__componentOriginal613a257712d79f8faaf5703f21bda327); ?>
<?php endif; ?>
        </div>
        <p class="text-cyan-100/80 leading-relaxed whitespace-pre-line"><?php echo e($intervention->description); ?></p>

        <dl class="grid sm:grid-cols-2 gap-4 mt-6 pt-6 border-t border-white/10">
            <div class="flex items-center gap-3">
                <i data-lucide="calendar" class="w-5 h-5 text-cyan-300"></i>
                <div>
                    <dt class="text-xs text-cyan-100/50">Date</dt>
                    <dd class="text-white"><?php echo e($intervention->date->locale('fr')->translatedFormat('l j F Y')); ?></dd>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <i data-lucide="hard-hat" class="w-5 h-5 text-cyan-300"></i>
                <div>
                    <dt class="text-xs text-cyan-100/50">Équipe en charge</dt>
                    <dd class="text-white"><?php echo e($intervention->technicien->specialite); ?></dd>
                </div>
            </div>
        </dl>
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

    <?php if($intervention->statut !== 'Terminée'): ?>
        <?php if (isset($component)) { $__componentOriginal746de018ded8594083eb43be3f1332e1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal746de018ded8594083eb43be3f1332e1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.alert','data' => ['type' => 'info','title' => 'Un problème dans votre quartier ?']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.alert'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'info','title' => 'Un problème dans votre quartier ?']); ?>
            Si vous constatez une fuite ou une coupure prolongée, vous pouvez la signaler.
            <a href="<?php echo e(route('citizen.reports.create')); ?>" class="text-cyan-300 font-semibold hover:underline">Signaler un problème</a>
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
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.frontoffice', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\monta\Downloads\DEVWAVE\Esprit-5TWIN4-DevWave-main (1)\Esprit-5TWIN4-DevWave-main\resources\views\citizen\travaux\show.blade.php ENDPATH**/ ?>