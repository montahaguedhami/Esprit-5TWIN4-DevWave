

<?php $__env->startSection('manager-content'); ?>
<div class="max-w-6xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold">Gestion des incidents</h1>
    </div>

    <?php if(session('success')): ?>
        <div class="mb-4 p-3 bg-green-600 text-white rounded"><?php echo e(session('success')); ?></div>
    <?php endif; ?>

    <div class="overflow-x-auto">
    <table class="w-full min-w-160 text-left border-collapse">
        <thead>
            <tr class="text-sm text-slate-400">
                <th class="pb-3">Titre</th>
                <th class="pb-3">Type</th>
                <th class="pb-3">Gravité</th>
                <th class="pb-3">Statut</th>
                <th class="pb-3">Citoyen</th>
                <th class="pb-3">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $incidents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $incident): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr class="border-t border-white/5">
                <td class="py-3"><?php echo e($incident->titre); ?></td>
                <td class="py-3"><?php echo e($incident->type); ?></td>
                <td class="py-3"><?php echo e($incident->gravite); ?></td>
                <td class="py-3"><?php echo e($incident->statut); ?></td>
                <td class="py-3"><?php echo e($incident->user?->name); ?></td>
                <td class="py-3">
                    <a href="<?php echo e(route('manager.incidents.show', $incident)); ?>" class="inline-flex items-center gap-2 text-cyan-300"><i data-lucide="eye" class="w-4 h-4"></i>Voir (<?php echo e($incident->actions_count); ?>)</a>
                </td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="6" class="py-6 text-slate-300">Aucun incident signal&eacute;.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
    </div>
    <div class="mt-6"><?php echo e($incidents->links()); ?></div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.manager', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Ranim\Documents\GitHub\Esprit-5TWIN4-DevWave\resources\views/manager/incidents/index.blade.php ENDPATH**/ ?>