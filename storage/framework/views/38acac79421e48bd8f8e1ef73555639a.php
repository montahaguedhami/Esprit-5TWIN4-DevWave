<?php $__env->startSection('title', 'Nouvelle intervention — AquaSecure'); ?>

<?php $__env->startSection('manager-content'); ?>
<div class="max-w-3xl mx-auto">
    <?php if (isset($component)) { $__componentOriginalf2de2158a73822dbd9074b03366e73f1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf2de2158a73822dbd9074b03366e73f1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.maintenance.page-header','data' => ['title' => 'Nouvelle intervention','subtitle' => 'Planifier ou enregistrer une intervention de maintenance','icon' => 'clipboard-plus']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('maintenance.page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Nouvelle intervention','subtitle' => 'Planifier ou enregistrer une intervention de maintenance','icon' => 'clipboard-plus']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf2de2158a73822dbd9074b03366e73f1)): ?>
<?php $attributes = $__attributesOriginalf2de2158a73822dbd9074b03366e73f1; ?>
<?php unset($__attributesOriginalf2de2158a73822dbd9074b03366e73f1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf2de2158a73822dbd9074b03366e73f1)): ?>
<?php $component = $__componentOriginalf2de2158a73822dbd9074b03366e73f1; ?>
<?php unset($__componentOriginalf2de2158a73822dbd9074b03366e73f1); ?>
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
        <?php if($techniciens->isEmpty()): ?>
            <?php if (isset($component)) { $__componentOriginal3607a477fdef7402bc742abad5df9c51 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3607a477fdef7402bc742abad5df9c51 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.empty-state','data' => ['icon' => 'hard-hat','title' => 'Aucun technicien','description' => 'Ajoutez d\'abord un technicien avant de créer une intervention.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'hard-hat','title' => 'Aucun technicien','description' => 'Ajoutez d\'abord un technicien avant de créer une intervention.']); ?>
                <?php if (isset($component)) { $__componentOriginal315d03b0d0695345e2e870d0abb4e102 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal315d03b0d0695345e2e870d0abb4e102 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.maintenance.link-button','data' => ['href' => route('manager.techniciens.create'),'icon' => 'plus']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('maintenance.link-button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('manager.techniciens.create')),'icon' => 'plus']); ?>Nouveau technicien <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal315d03b0d0695345e2e870d0abb4e102)): ?>
<?php $attributes = $__attributesOriginal315d03b0d0695345e2e870d0abb4e102; ?>
<?php unset($__attributesOriginal315d03b0d0695345e2e870d0abb4e102); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal315d03b0d0695345e2e870d0abb4e102)): ?>
<?php $component = $__componentOriginal315d03b0d0695345e2e870d0abb4e102; ?>
<?php unset($__componentOriginal315d03b0d0695345e2e870d0abb4e102); ?>
<?php endif; ?>
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
        <?php else: ?>
            <form method="POST" action="<?php echo e(route('manager.interventions.store')); ?>">
                <?php echo $__env->make('manager.interventions._form', ['submitLabel' => 'Enregistrer'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </form>
        <?php endif; ?>
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
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.manager', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\monta\Downloads\DEVWAVE\Esprit-5TWIN4-DevWave-main (1)\Esprit-5TWIN4-DevWave-main\resources\views\manager\interventions\create.blade.php ENDPATH**/ ?>