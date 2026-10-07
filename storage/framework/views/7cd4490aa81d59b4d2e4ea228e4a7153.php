

<?php $__env->startSection('frontoffice-content'); ?>
<div class="max-w-4xl mx-auto py-12">
    <div class="flex flex-wrap gap-3 items-center justify-between mb-6">
        <h1 class="text-2xl font-bold">Mes incidents</h1>
        <a href="<?php echo e(route('incidents.create')); ?>" class="px-4 py-2 rounded bg-cyan-500 text-white">Signaler un incident</a>
    </div>

    <?php if(session('success')): ?>
        <div class="mb-4 p-3 bg-green-600 text-white rounded"><?php echo e(session('success')); ?></div>
    <?php endif; ?>

    <div class="space-y-4">
        <?php $__empty_1 = true; $__currentLoopData = $incidents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $incident): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="p-4 bg-white/5 rounded">
                <a href="<?php echo e(route('incidents.show', $incident)); ?>" class="text-lg font-semibold wrap-break-word"><?php echo e($incident->titre); ?></a>
                <div class="text-sm text-slate-300"><?php echo e($incident->type); ?> · Gravité: <?php echo e($incident->gravite); ?> · Statut: <?php echo e($incident->statut); ?></div>
                <div class="text-xs text-slate-400 mt-2">Signalé: <?php echo e(optional($incident->date_signalement)->format('Y-m-d H:i')); ?></div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <p class="text-slate-300">Vous n'avez pas encore signal&eacute; d'incident.</p>
        <?php endif; ?>
    </div>

    <div class="mt-6">
        <?php echo e($incidents->links()); ?>

    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.frontoffice', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Ranim\Documents\GitHub\Esprit-5TWIN4-DevWave\resources\views/front/incidents/index.blade.php ENDPATH**/ ?>