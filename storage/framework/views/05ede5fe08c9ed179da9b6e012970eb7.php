<?php $__env->startSection('frontoffice-content'); ?>
<div class="max-w-3xl mx-auto py-12">
    <a href="<?php echo e(route('incidents.index')); ?>" class="text-sm text-cyan-300">← Retour aux incidents</a>

    <?php if(session('success')): ?>
        <div role="status" class="mt-4 p-3 bg-green-600 rounded"><?php echo e(session('success')); ?></div>
    <?php endif; ?>
    <div class="flex flex-wrap gap-4 mt-4">
        <a href="<?php echo e(route('incidents.edit', $incident)); ?>" class="inline-flex items-center gap-2 text-amber-300"><i data-lucide="pencil" class="w-4 h-4"></i>Modifier mon incident</a>
        <form action="<?php echo e(route('incidents.destroy', $incident)); ?>" method="post">
            <?php echo csrf_field(); ?>
            <?php echo method_field('DELETE'); ?>
            <button class="inline-flex items-center gap-2 text-red-400" onclick="return confirm('Supprimer cet incident et ses actions ?')"><i data-lucide="trash-2" class="w-4 h-4"></i>Supprimer mon incident</button>
        </form>
    </div>
    <div class="mt-4 p-6 bg-white/5 rounded">
        <h1 class="text-2xl font-bold wrap-break-word"><?php echo e($incident->titre); ?></h1>
        <div class="text-sm text-slate-300"><?php echo e($incident->type); ?> · Gravité: <?php echo e($incident->gravite); ?> · Statut: <?php echo e($incident->statut); ?></div>
        <p class="mt-4 whitespace-pre-line wrap-break-word"><?php echo e($incident->description); ?></p>
        <div class="text-xs text-slate-400 mt-3">Localisation: <?php echo e($incident->localisation); ?></div>
        <div class="text-xs text-slate-400">Signalé: <?php echo e(optional($incident->date_signalement)->format('Y-m-d H:i')); ?></div>
    </div>

    <div class="mt-6">
        <h2 class="text-lg font-semibold">Actions correctives</h2>
        <div class="space-y-3 mt-3">
            <?php $__currentLoopData = $incident->actions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $action): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="p-3 bg-white/5 rounded">
                    <div class="font-semibold"><?php echo e($action->titre); ?></div>
                    <div class="text-sm text-slate-300">Responsable: <?php echo e($action->responsable); ?> · Statut: <?php echo e($action->statut); ?></div>
                    <div class="text-xs text-slate-400 mt-2">Prévue: <?php echo e(optional($action->date_prevue)->format('Y-m-d')); ?> · Réalisée: <?php echo e(optional($action->date_realisation)->format('Y-m-d H:i')); ?></div>
                    <p class="mt-2 text-sm"><?php echo e($action->resultat); ?></p>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php if($incident->actions->isEmpty()): ?>
                <div class="p-3 bg-white/5 rounded text-slate-400">Aucune action corrective enregistrée.</div>
            <?php endif; ?>
        </div>
    </div>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.frontoffice', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\monta\Downloads\DEVWAVE\Esprit-5TWIN4-DevWave-main (1)\Esprit-5TWIN4-DevWave-main\resources\views\front\incidents\show.blade.php ENDPATH**/ ?>