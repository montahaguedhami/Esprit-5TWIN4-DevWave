<?php
    $title = 'Financement — AquaSecure';
?>

<?php $__env->startSection('manager-content'); ?>
<div class="max-w-4xl mx-auto space-y-6 animate-fade-in-up">

    <!-- Breadcrumb & Actions -->
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <a href="<?php echo e(route('manager.financements.index')); ?>" class="text-cyan-400 hover:text-cyan-300 text-sm font-semibold flex items-center gap-1 mb-2">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                Retour aux financements
            </a>
            <h1 class="text-2xl sm:text-3xl font-display font-bold text-white">Détails du Financement</h1>
        </div>
        <div class="flex gap-2">
            <a href="<?php echo e(route('manager.financements.edit', $financement)); ?>" 
               class="glass px-4 py-2 rounded-xl text-sm text-cyan-300 hover:text-white font-semibold flex items-center gap-2">
                <i data-lucide="edit" class="w-4 h-4"></i>
                Modifier
            </a>
            <form action="<?php echo e(route('manager.financements.destroy', $financement)); ?>" 
                  method="POST" 
                  onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce financement ?')">
                <?php echo csrf_field(); ?>
                <?php echo method_field('DELETE'); ?>
                <button type="submit" 
                        class="glass px-4 py-2 rounded-xl text-sm text-red-300 hover:text-red-200 font-semibold flex items-center gap-2 border-red-400/20">
                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                    Supprimer
                </button>
            </form>
        </div>
    </div>

    <!-- Main Info Card -->
    <div class="glass rounded-2xl p-8">
        <div class="flex items-start gap-6 mb-8">
            <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-cyan-500 to-blue-600 flex items-center justify-center flex-shrink-0">
                <i data-lucide="banknote" class="w-8 h-8 text-white"></i>
            </div>
            <div class="flex-1">
                <h2 class="text-2xl font-display font-bold text-white mb-2"><?php echo e($financement->source); ?></h2>
                <p class="text-cyan-100/70">Source de financement</p>
            </div>
        </div>

        <div class="grid sm:grid-cols-2 gap-6">
            <!-- Montant -->
            <div class="bg-white/[.03] rounded-xl p-6">
                <div class="flex items-center gap-2 mb-2">
                    <i data-lucide="wallet" class="w-5 h-5 text-cyan-400"></i>
                    <p class="text-cyan-100/60 text-sm font-semibold">Montant</p>
                </div>
                <p class="text-3xl font-display font-bold text-white"><?php echo e(number_format($financement->montant, 2, ',', ' ')); ?></p>
                <p class="text-cyan-400 font-semibold mt-1">DT</p>
            </div>

            <!-- Date -->
            <div class="bg-white/[.03] rounded-xl p-6">
                <div class="flex items-center gap-2 mb-2">
                    <i data-lucide="calendar" class="w-5 h-5 text-cyan-400"></i>
                    <p class="text-cyan-100/60 text-sm font-semibold">Date de Financement</p>
                </div>
                <p class="text-3xl font-display font-bold text-white"><?php echo e($financement->date_financement->format('d/m/Y')); ?></p>
                <p class="text-cyan-400 text-sm font-semibold mt-1"><?php echo e($financement->date_financement->diffForHumans()); ?></p>
            </div>
        </div>
    </div>

    <!-- Associated Project -->
    <div class="glass rounded-2xl p-6">
        <h3 class="text-lg font-display font-bold text-white mb-4 flex items-center gap-2">
            <i data-lucide="briefcase" class="w-5 h-5 text-cyan-400"></i>
            Projet Associé
        </h3>

        <div class="bg-white/[.03] rounded-xl p-5 hover:bg-white/[.05] transition-colors">
            <div class="flex items-start justify-between gap-4 mb-4">
                <div class="flex-1">
                    <h4 class="text-white font-semibold text-lg mb-2"><?php echo e($financement->projet->nom); ?></h4>
                    <?php if($financement->projet->adresse): ?>
                    <p class="text-cyan-100/60 text-sm flex items-center gap-1 mb-2">
                        <i data-lucide="map-pin" class="w-3 h-3"></i>
                        <?php echo e($financement->projet->adresse); ?>

                    </p>
                    <?php endif; ?>
                    <?php if($financement->projet->description): ?>
                    <p class="text-cyan-100/70 text-sm leading-relaxed">
                        <?php echo e(Str::limit($financement->projet->description, 200)); ?>

                    </p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Project Stats -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-4">
                <div class="bg-white/[.03] rounded-lg px-3 py-2">
                    <p class="text-cyan-100/50 text-xs mb-1">Budget Total</p>
                    <p class="text-white font-semibold text-sm"><?php echo e(number_format($financement->projet->budget, 0, ',', ' ')); ?> DT</p>
                </div>
                <div class="bg-white/[.03] rounded-lg px-3 py-2">
                    <p class="text-cyan-100/50 text-xs mb-1">Total Financé</p>
                    <p class="text-white font-semibold text-sm"><?php echo e(number_format($financement->projet->total_finance, 0, ',', ' ')); ?> DT</p>
                </div>
                <div class="bg-white/[.03] rounded-lg px-3 py-2">
                    <p class="text-cyan-100/50 text-xs mb-1">Pourcentage</p>
                    <p class="text-white font-semibold text-sm"><?php echo e(number_format($financement->projet->pourcentage_finance, 1)); ?>%</p>
                </div>
                <div class="bg-white/[.03] rounded-lg px-3 py-2">
                    <p class="text-cyan-100/50 text-xs mb-1">Statut</p>
                    <?php
                        $statusLabels = [
                            'planifie' => 'Planifié',
                            'en_cours' => 'En cours',
                            'termine' => 'Terminé',
                            'suspendu' => 'Suspendu',
                            'annule' => 'Annulé',
                        ];
                    ?>
                    <p class="text-white font-semibold text-sm"><?php echo e($statusLabels[$financement->projet->statut] ?? $financement->projet->statut); ?></p>
                </div>
            </div>

            <!-- Funding Progress -->
            <div class="mb-4">
                <div class="flex justify-between text-xs text-cyan-100/50 mb-1">
                    <span>Progression du financement</span>
                    <span class="font-bold text-white"><?php echo e(number_format($financement->projet->pourcentage_finance, 1)); ?>%</span>
                </div>
                <div class="h-2 rounded-full bg-white/5 overflow-hidden">
                    <?php
                        $percentage = min($financement->projet->pourcentage_finance, 100);
                        $color = $percentage >= 100 ? 'bg-teal-400' : ($percentage >= 50 ? 'bg-cyan-400' : 'bg-blue-400');
                    ?>
                    <div class="h-full rounded-full <?php echo e($color); ?>" style="width: <?php echo e($percentage); ?>%"></div>
                </div>
            </div>

            <!-- Link to Project -->
            <div class="flex justify-end">
                <a href="<?php echo e(route('manager.projets.show', $financement->projet)); ?>" 
                   class="text-sm text-cyan-400 hover:text-cyan-300 font-semibold flex items-center gap-1">
                    Voir le projet complet
                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Metadata -->
    <div class="glass rounded-2xl p-6">
        <h3 class="text-lg font-display font-bold text-white mb-4 flex items-center gap-2">
            <i data-lucide="info" class="w-5 h-5 text-cyan-400"></i>
            Informations Système
        </h3>
        
        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <p class="text-cyan-100/50 text-xs mb-1">Date de création</p>
                <p class="text-white text-sm"><?php echo e($financement->created_at->format('d/m/Y à H:i')); ?></p>
            </div>
            <div>
                <p class="text-cyan-100/50 text-xs mb-1">Dernière modification</p>
                <p class="text-white text-sm"><?php echo e($financement->updated_at->format('d/m/Y à H:i')); ?></p>
            </div>
        </div>
    </div>

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

<?php echo $__env->make('layouts.manager', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\ghada\Desktop\5ème\projet_Laravel\aquasecure\resources\views/manager/financements/show.blade.php ENDPATH**/ ?>