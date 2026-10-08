<?php $__env->startSection('manager-content'); ?>
<div class="max-w-3xl mx-auto">
    <a href="<?php echo e(route('manager.actions.index', $action->incident_id)); ?>" class="text-cyan-300">Retour aux actions</a>
    <?php if(session('success')): ?>
        <div role="status" class="mt-4 p-3 bg-green-600 rounded"><?php echo e(session('success')); ?></div>
    <?php endif; ?>
    <h1 class="text-2xl font-bold mt-6 wrap-break-word"><?php echo e($action->titre); ?></h1>
    <a href="<?php echo e(route('manager.incidents.show', $action->incident_id)); ?>" class="inline-block mt-2 text-cyan-300 wrap-break-word">Incident : <?php echo e($action->incident->titre); ?></a>
    <p class="mt-6 whitespace-pre-line wrap-break-word"><?php echo e($action->description); ?></p>
    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 py-6 border-b border-white/15">
        <div><dt class="text-slate-400">Responsable</dt><dd class="wrap-break-word"><?php echo e($action->responsable ?? '-'); ?></dd></div>
        <div><dt class="text-slate-400">Statut</dt><dd><?php echo e($action->statut); ?></dd></div>
        <div><dt class="text-slate-400">Date pr&eacute;vue</dt><dd><?php echo e($action->date_prevue?->format('d/m/Y') ?? '-'); ?></dd></div>
        <div><dt class="text-slate-400">Date de r&eacute;alisation</dt><dd><?php echo e($action->date_realisation?->format('d/m/Y H:i') ?? '-'); ?></dd></div>
    </dl>
    <h2 class="text-lg font-semibold mt-6">R&eacute;sultat</h2>
    <p class="mt-2 whitespace-pre-line wrap-break-word"><?php echo e($action->resultat ?? '-'); ?></p>
    <div class="flex flex-wrap gap-4 mt-6">
        <a href="<?php echo e(route('manager.actions.edit', $action)); ?>" class="inline-flex items-center gap-2 text-amber-300"><i data-lucide="pencil" class="w-4 h-4"></i>Modifier l'action</a>
        <form action="<?php echo e(route('manager.actions.destroy', $action)); ?>" method="post">
            <?php echo csrf_field(); ?>
            <?php echo method_field('DELETE'); ?>
            <button class="inline-flex items-center gap-2 text-red-400" onclick="return confirm('Supprimer cette action corrective ?')"><i data-lucide="trash-2" class="w-4 h-4"></i>Supprimer l'action</button>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.manager', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\monta\Downloads\DEVWAVE\Esprit-5TWIN4-DevWave-main (1)\Esprit-5TWIN4-DevWave-main\resources\views\manager\actions\show.blade.php ENDPATH**/ ?>