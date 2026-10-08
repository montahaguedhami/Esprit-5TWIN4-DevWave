<?php $__env->startSection('manager-content'); ?>
<div class="max-w-3xl mx-auto py-12">
    <h1 class="text-2xl font-bold mb-4">Modifier l'action corrective</h1>
    <a href="<?php echo e(route('manager.incidents.show', $action->incident_id)); ?>" class="text-cyan-300">Retour &agrave; l'incident</a>
    <form action="<?php echo e(route('manager.actions.update', $action)); ?>" method="post" class="space-y-4 mt-6">
        <?php echo csrf_field(); ?>
        <?php echo method_field('PUT'); ?>
        <?php echo $__env->make('manager.actions.form', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <button class="inline-flex items-center gap-2 px-4 py-2 rounded bg-cyan-500 text-white"><i data-lucide="save" class="w-4 h-4"></i>Enregistrer</button>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.manager', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\monta\Downloads\DEVWAVE\Esprit-5TWIN4-DevWave-main (1)\Esprit-5TWIN4-DevWave-main\resources\views\manager\actions\edit.blade.php ENDPATH**/ ?>