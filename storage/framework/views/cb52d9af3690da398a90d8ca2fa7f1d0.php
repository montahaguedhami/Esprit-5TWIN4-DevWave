<?php $__env->startSection('manager-content'); ?>
<div class="max-w-3xl mx-auto">
    <a href="<?php echo e(route('manager.actions.index', $incident)); ?>" class="text-cyan-300">Retour aux actions</a>
    <h1 class="text-2xl font-bold mt-6">Ajouter une action corrective</h1>
    <p class="mt-2 text-slate-300 wrap-break-word">Incident : <?php echo e($incident->titre); ?></p>
    <form action="<?php echo e(route('incidents.actions.store', $incident)); ?>" method="post" class="space-y-4 mt-6">
        <?php echo csrf_field(); ?>
        <?php echo $__env->make('manager.actions.form', ['action' => null], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <button class="inline-flex items-center gap-2 px-4 py-2 rounded bg-cyan-500 text-white"><i data-lucide="plus" class="w-4 h-4"></i>Ajouter l'action</button>
    </form>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.manager', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\monta\Downloads\DEVWAVE\Esprit-5TWIN4-DevWave-main (1)\Esprit-5TWIN4-DevWave-main\resources\views\manager\actions\create.blade.php ENDPATH**/ ?>