<?php
    $title = 'Financements — AquaSecure';
?>

<?php $__env->startSection('manager-content'); ?>
<div class="space-y-6 animate-fade-in-up">

    <!-- Header -->
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-display font-bold text-white">Gestion des Financements</h1>
            <p class="text-cyan-100/55 text-sm mt-0.5">Suivi des sources de financement des projets</p>
        </div>
        <a href="<?php echo e(route('manager.financements.create')); ?>" 
           class="glass px-4 py-2 rounded-xl text-sm text-cyan-300 hover:text-white font-semibold flex items-center gap-2 transition-all hover:border-cyan-400/40">
            <i data-lucide="plus" class="w-4 h-4"></i>
            Nouveau financement
        </a>
    </div>

    <!-- Filters -->
    <div class="glass rounded-2xl p-4">
        <form method="GET" action="<?php echo e(route('manager.financements.index')); ?>" class="flex flex-wrap gap-3">
            <!-- Project Filter -->
            <select name="projet_id" 
                    class="flex-1 min-w-[200px] px-4 py-2 rounded-xl bg-white/5 border border-white/10 text-white focus:border-cyan-400/50 focus:outline-none transition-colors">
                <option value="">Tous les projets</option>
                <?php $__currentLoopData = $projets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $projet): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($projet->id); ?>" <?php echo e(request('projet_id') == $projet->id ? 'selected' : ''); ?>>
                    <?php echo e($projet->nom); ?>

                </option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            
            <!-- Buttons -->
            <button type="submit" 
                    class="px-4 py-2 rounded-xl bg-cyan-500/15 text-cyan-300 hover:text-white font-semibold border border-cyan-400/25 transition-colors">
                Filtrer
            </button>
            <?php if(request('projet_id')): ?>
            <a href="<?php echo e(route('manager.financements.index')); ?>" 
               class="px-4 py-2 rounded-xl bg-white/5 text-cyan-100/60 hover:text-white font-semibold transition-colors">
                Réinitialiser
            </a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Financements List -->
    <?php if($financements->count() > 0): ?>
    <div class="glass rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-white/[.03] border-b border-white/10">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-cyan-100/60 uppercase tracking-wider">
                            Source
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-cyan-100/60 uppercase tracking-wider">
                            Projet
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-cyan-100/60 uppercase tracking-wider">
                            Montant
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-cyan-100/60 uppercase tracking-wider">
                            Date
                        </th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-cyan-100/60 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    <?php $__currentLoopData = $financements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $financement): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr class="hover:bg-white/[.02] transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-lg bg-cyan-500/10 flex items-center justify-center flex-shrink-0">
                                    <i data-lucide="banknote" class="w-4 h-4 text-cyan-400"></i>
                                </div>
                                <div>
                                    <p class="text-white font-semibold text-sm"><?php echo e($financement->source); ?></p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <a href="<?php echo e(route('manager.projets.show', $financement->projet)); ?>" 
                               class="text-cyan-400 hover:text-cyan-300 text-sm font-medium flex items-center gap-1">
                                <?php echo e(Str::limit($financement->projet->nom, 40)); ?>

                                <i data-lucide="external-link" class="w-3 h-3"></i>
                            </a>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-white font-bold text-sm"><?php echo e(number_format($financement->montant, 0, ',', ' ')); ?> DT</p>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-cyan-100/70 text-sm"><?php echo e($financement->date_financement->format('d/m/Y')); ?></p>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-end gap-2">
                                <a href="<?php echo e(route('manager.financements.show', $financement)); ?>" 
                                   class="p-2 rounded-lg bg-white/5 hover:bg-white/10 text-cyan-400 hover:text-cyan-300 transition-colors"
                                   title="Voir">
                                    <i data-lucide="eye" class="w-4 h-4"></i>
                                </a>
                                <a href="<?php echo e(route('manager.financements.edit', $financement)); ?>" 
                                   class="p-2 rounded-lg bg-white/5 hover:bg-white/10 text-cyan-400 hover:text-cyan-300 transition-colors"
                                   title="Modifier">
                                    <i data-lucide="edit" class="w-4 h-4"></i>
                                </a>
                                <form action="<?php echo e(route('manager.financements.destroy', $financement)); ?>" 
                                      method="POST" 
                                      class="inline"
                                      onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce financement ?')">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button type="submit" 
                                            class="p-2 rounded-lg bg-white/5 hover:bg-red-500/10 text-red-400 hover:text-red-300 transition-colors"
                                            title="Supprimer">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <div class="glass rounded-2xl p-4">
        <?php echo e($financements->links()); ?>

    </div>
    <?php else: ?>
    <!-- Empty State -->
    <div class="glass rounded-2xl p-12 text-center">
        <div class="w-16 h-16 rounded-2xl bg-cyan-500/10 flex items-center justify-center mx-auto mb-4">
            <i data-lucide="banknote" class="w-8 h-8 text-cyan-400"></i>
        </div>
        <h3 class="text-xl font-display font-bold text-white mb-2">Aucun financement trouvé</h3>
        <p class="text-cyan-100/60 mb-6">
            <?php if(request('projet_id')): ?>
                Aucun financement pour ce projet.
            <?php else: ?>
                Commencez par créer un premier financement.
            <?php endif; ?>
        </p>
        <?php if(request('projet_id')): ?>
        <a href="<?php echo e(route('manager.financements.index')); ?>" 
           class="inline-flex items-center gap-2 glass px-4 py-2 rounded-xl text-sm text-cyan-300 hover:text-white font-semibold">
            Voir tous les financements
        </a>
        <?php else: ?>
        <a href="<?php echo e(route('manager.financements.create')); ?>" 
           class="inline-flex items-center gap-2 glass px-4 py-2 rounded-xl text-sm text-cyan-300 hover:text-white font-semibold">
            <i data-lucide="plus" class="w-4 h-4"></i>
            Créer un financement
        </a>
        <?php endif; ?>
    </div>
    <?php endif; ?>

</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
    // Refresh Lucide icons
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.manager', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\monta\Downloads\DEVWAVE\Esprit-5TWIN4-DevWave-main (1)\Esprit-5TWIN4-DevWave-main\resources\views\manager\financements\index.blade.php ENDPATH**/ ?>