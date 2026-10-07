

<?php $__env->startSection('manager-content'); ?>
<div class="max-w-4xl mx-auto">
    <a href="<?php echo e(route('manager.incidents')); ?>" class="text-cyan-300">Retour aux incidents</a>
    <?php if(session('success')): ?>
        <div role="status" class="mt-4 p-3 bg-green-600 rounded"><?php echo e(session('success')); ?></div>
    <?php endif; ?>
    <section class="py-6 border-b border-white/15">
        <h1 class="text-2xl font-bold wrap-break-word"><?php echo e($incident->titre); ?></h1>
        <p class="mt-2 text-slate-300"><?php echo e($incident->type); ?> | <?php echo e($incident->gravite); ?> | <?php echo e($incident->statut); ?></p>
        <p class="mt-4 whitespace-pre-line wrap-break-word"><?php echo e($incident->description); ?></p>
        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4 text-sm">
            <div><dt class="text-slate-400">Citoyen</dt><dd><?php echo e($incident->user?->name); ?></dd></div>
            <div><dt class="text-slate-400">Date de signalement</dt><dd><?php echo e($incident->date_signalement?->format('d/m/Y H:i')); ?></dd></div>
            <div><dt class="text-slate-400">Localisation</dt><dd class="wrap-break-word"><?php echo e($incident->localisation); ?></dd></div>
        </dl>
    </section>
    <section class="py-6 border-b border-white/15">
        <h2 class="text-xl font-semibold mb-4">Actions correctives</h2>
        <a href="<?php echo e(route('manager.actions.index', $incident)); ?>" class="inline-flex items-center gap-2 text-cyan-300 mb-4"><i data-lucide="list" class="w-4 h-4"></i>Liste des actions</a>
        <div class="space-y-4">
            <?php $__empty_1 = true; $__currentLoopData = $incident->actions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $action): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <article class="p-4 bg-white/5 rounded wrap-break-word">
                    <h3 class="font-semibold"><?php echo e($action->titre); ?></h3>
                    <p class="mt-2 whitespace-pre-line"><?php echo e($action->description); ?></p>
                    <p class="mt-2 text-sm text-slate-300">Responsable : <?php echo e($action->responsable); ?> | Statut : <?php echo e($action->statut); ?></p>
                    <p class="text-sm text-slate-300">Pr&eacute;vue : <?php echo e($action->date_prevue?->format('d/m/Y') ?? '-'); ?> | R&eacute;alis&eacute;e : <?php echo e($action->date_realisation?->format('d/m/Y H:i') ?? '-'); ?></p>
                    <p class="mt-2 whitespace-pre-line"><?php echo e($action->resultat); ?></p>
                    <div class="flex flex-wrap gap-4 mt-4">
                        <a href="<?php echo e(route('manager.actions.show', $action)); ?>" class="inline-flex items-center gap-2 text-cyan-300"><i data-lucide="eye" class="w-4 h-4"></i>Voir le d&eacute;tail</a>
                        <a href="<?php echo e(route('manager.actions.edit', $action)); ?>" class="inline-flex items-center gap-2 text-amber-300"><i data-lucide="pencil" class="w-4 h-4"></i>Modifier l'action</a>
                        <form action="<?php echo e(route('manager.actions.destroy', $action)); ?>" method="post">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('DELETE'); ?>
                            <button class="inline-flex items-center gap-2 text-red-400" onclick="return confirm('Supprimer cette action corrective ?')"><i data-lucide="trash-2" class="w-4 h-4"></i>Supprimer l'action</button>
                        </form>
                    </div>
                </article>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <p class="text-slate-300">Aucune action corrective enregistr&eacute;e.</p>
            <?php endif; ?>
        </div>
    </section>
    <section class="py-6">
        <h2 class="text-xl font-semibold mb-4">Ajouter une action corrective</h2>
        <form action="<?php echo e(route('incidents.actions.store', $incident)); ?>" method="post" class="space-y-4">
            <?php echo csrf_field(); ?>
            <?php echo $__env->make('manager.actions.form', ['action' => null], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <button class="inline-flex items-center gap-2 px-4 py-2 rounded bg-cyan-500 text-white"><i data-lucide="plus" class="w-4 h-4"></i>Ajouter l'action</button>
        </form>
    </section>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.manager', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Ranim\Documents\GitHub\Esprit-5TWIN4-DevWave\resources\views/manager/incidents/show.blade.php ENDPATH**/ ?>