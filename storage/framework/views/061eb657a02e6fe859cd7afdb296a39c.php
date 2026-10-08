
<?php $__env->startSection('title', $point->nom); ?>
<?php $__env->startSection('content'); ?>
<div class="min-h-screen bg-[#061525] p-4 text-slate-100 sm:p-8">
    <div class="mx-auto max-w-5xl space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div><h1 class="text-2xl font-bold text-white"><?php echo e($point->nom); ?></h1><p class="text-sm text-slate-400"><?php echo e($point->code); ?> · <?php echo e($point->zone); ?></p></div>
            <div class="flex gap-2"><a href="<?php echo e(route('manager.points-mesure.edit', $point)); ?>" class="rounded-xl bg-slate-800 px-4 py-2 text-sm">Modifier</a><a href="<?php echo e(route('manager.quality.mesures.create', ['point_mesure_id' => $point->id])); ?>" class="rounded-xl bg-teal-500 px-4 py-2 text-sm font-bold text-slate-950">Ajouter une mesure</a></div>
        </div>
        <?php if(session('success')): ?><div class="rounded-xl border border-teal-500/30 bg-teal-500/10 p-3 text-sm text-teal-200"><?php echo e(session('success')); ?></div><?php endif; ?>
        <div class="grid gap-4 rounded-2xl border border-slate-800 bg-slate-900 p-5 sm:grid-cols-3">
            <div><div class="text-xs text-slate-400">Type / statut</div><div><?php echo e($point->type); ?> · <?php echo e(ucfirst($point->statut)); ?></div></div>
            <div><div class="text-xs text-slate-400">Adresse</div><div><?php echo e($point->adresse ?: '—'); ?></div></div>
            <div><div class="text-xs text-slate-400">Coordonnées</div><div><?php echo e($point->latitude ?? '—'); ?>, <?php echo e($point->longitude ?? '—'); ?></div></div>
            <div class="sm:col-span-3"><div class="text-xs text-slate-400">Description</div><div><?php echo e($point->description ?: '—'); ?></div></div>
        </div>
        <div class="overflow-x-auto rounded-2xl border border-slate-800 bg-slate-900">
            <table class="w-full text-left text-sm"><thead class="bg-slate-950 text-xs uppercase text-slate-400"><tr><th class="p-4">Référence</th><th class="p-4">Date</th><th class="p-4">pH</th><th class="p-4">Turbidité</th><th class="p-4">Chlore</th><th class="p-4">Plomb</th><th class="p-4">Nitrates</th></tr></thead>
                <tbody class="divide-y divide-slate-800">
                <?php $__empty_1 = true; $__currentLoopData = $point->mesuresQualites; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mesure): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr><td class="p-4"><a class="text-cyan-300 hover:underline" href="<?php echo e(route('manager.quality.mesures.show', $mesure)); ?>"><?php echo e($mesure->reference); ?></a></td><td class="p-4"><?php echo e($mesure->date_mesure->format('d/m/Y H:i')); ?></td><td class="p-4"><?php echo e($mesure->ph); ?></td><td class="p-4"><?php echo e($mesure->turbidite); ?> NTU</td><td class="p-4"><?php echo e($mesure->chlore_residuel); ?> mg/L</td><td class="p-4"><?php echo e($mesure->plomb); ?> ppb</td><td class="p-4"><?php echo e($mesure->nitrates); ?> mg/L</td></tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="7" class="p-8 text-center text-slate-400">Aucune mesure pour ce point.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.manager', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\monta\Downloads\DEVWAVE\Esprit-5TWIN4-DevWave-main (1)\Esprit-5TWIN4-DevWave-main\resources\views\manager\points-mesure\show.blade.php ENDPATH**/ ?>