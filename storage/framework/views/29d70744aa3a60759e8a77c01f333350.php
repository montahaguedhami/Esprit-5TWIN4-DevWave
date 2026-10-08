
<?php $__env->startSection('title', 'Nouveau point de prélèvement'); ?>
<?php $__env->startSection('content'); ?>
<div class="min-h-screen bg-[#061525] p-4 text-slate-100 sm:p-8">
    <div class="mx-auto max-w-3xl space-y-5">
        <h1 class="text-2xl font-bold text-white">Créer un point de prélèvement</h1>
        <form method="POST" action="<?php echo e(route('manager.points-mesure.store')); ?>" class="space-y-5 rounded-2xl border border-slate-800 bg-slate-900 p-6">
            <?php echo csrf_field(); ?>
            <?php echo $__env->make('manager.points-mesure.form', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <div class="flex justify-end gap-3"><a href="<?php echo e(route('manager.points-mesure.index')); ?>" class="rounded-xl bg-slate-800 px-4 py-2 text-sm">Annuler</a><button class="rounded-xl bg-teal-500 px-4 py-2 text-sm font-bold text-slate-950">Créer</button></div>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.manager', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\monta\Downloads\DEVWAVE\Esprit-5TWIN4-DevWave-main (1)\Esprit-5TWIN4-DevWave-main\resources\views/manager/points-mesure/create.blade.php ENDPATH**/ ?>