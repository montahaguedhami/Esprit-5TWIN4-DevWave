
<?php $__env->startSection('title', $mesure->reference); ?>
<?php $__env->startSection('content'); ?>
<div class="min-h-screen bg-[#061525] p-4 text-slate-100 sm:p-8">
    <div class="mx-auto max-w-4xl space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div><h1 class="text-2xl font-bold text-white">Mesure <?php echo e($mesure->reference); ?></h1><p class="text-sm text-slate-400"><?php echo e($mesure->pointMesure?->nom); ?> · <?php echo e($mesure->date_mesure->format('d/m/Y H:i')); ?></p></div>
            <div class="flex gap-2"><a href="<?php echo e(route('manager.quality.mesures.edit', $mesure)); ?>" class="rounded-xl bg-slate-800 px-4 py-2 text-sm">Modifier</a><a href="<?php echo e(route('manager.quality')); ?>" class="rounded-xl bg-teal-500 px-4 py-2 text-sm font-semibold text-slate-950">Retour à la qualité</a></div>
        </div>
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <?php $__currentLoopData = ['pH' => [$mesure->ph, ''], 'Turbidité' => [$mesure->turbidite, 'NTU'], 'Chlore résiduel' => [$mesure->chlore_residuel, 'mg/L'], 'Plomb' => [$mesure->plomb, 'ppb'], 'Nitrates' => [$mesure->nitrates, 'mg/L'], 'Conformité' => [$mesure->overall_compliance.'%', $mesure->status]]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $label => [$value, $unit]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="rounded-2xl border border-slate-800 bg-slate-900 p-5"><div class="text-xs text-slate-400"><?php echo e($label); ?></div><div class="mt-2 text-xl font-bold text-white"><?php echo e($value); ?> <span class="text-xs font-normal text-slate-400"><?php echo e($unit); ?></span></div></div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <div class="rounded-2xl border border-slate-800 bg-slate-900 p-5"><div class="text-xs text-slate-400">Vérification</div><div class="mt-2 font-semibold"><?php echo e($mesure->is_verified ? 'Vérifiée' : 'À vérifier'); ?></div><div class="text-sm text-slate-400"><?php echo e($mesure->verifier ?: '—'); ?></div></div>
        </div>
        <form method="POST" action="<?php echo e(route('manager.quality.mesures.destroy', $mesure)); ?>" onsubmit="return confirm('Supprimer définitivement cette mesure ?')">
            <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
            <button class="rounded-xl border border-red-500/30 px-4 py-2 text-sm text-red-300">Supprimer la mesure</button>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.manager', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\monta\Downloads\DEVWAVE\Esprit-5TWIN4-DevWave-main (1)\Esprit-5TWIN4-DevWave-main\resources\views\manager\quality-measures\show.blade.php ENDPATH**/ ?>