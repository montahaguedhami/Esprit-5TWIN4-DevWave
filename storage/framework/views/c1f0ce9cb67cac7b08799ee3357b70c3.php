
<?php $__env->startSection('title', 'Nouvelle mesure de qualité'); ?>
<?php $__env->startSection('content'); ?>
<div class="min-h-screen bg-[#061525] p-4 text-slate-100 sm:p-8">
    <div class="mx-auto max-w-3xl space-y-5">
        <h1 class="text-2xl font-bold text-white">Enregistrer une mesure de qualité</h1>
        <form method="POST" action="<?php echo e(route('manager.quality.mesures.store')); ?>" class="space-y-5 rounded-2xl border border-slate-800 bg-slate-900 p-6">
            <?php echo csrf_field(); ?>
            <?php echo $__env->make('manager.quality-measures.form', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <div class="flex justify-end gap-3"><a href="<?php echo e(route('manager.quality')); ?>" class="rounded-xl bg-slate-800 px-4 py-2 text-sm">Annuler</a><button class="rounded-xl bg-teal-500 px-4 py-2 text-sm font-bold text-slate-950">Enregistrer</button></div>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.manager', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\monta\Downloads\DEVWAVE\Esprit-5TWIN4-DevWave-main (1)\Esprit-5TWIN4-DevWave-main\resources\views/manager/quality-measures/create.blade.php ENDPATH**/ ?>