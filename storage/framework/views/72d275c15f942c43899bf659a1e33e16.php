<?php
    $title = 'Projets — AquaSecure';
    
    $statusStyle = [
        'planifie'    => ['bg'=>'bg-blue-500/15','text'=>'text-blue-300','label'=>'Planifié','icon'=>'calendar'],
        'en_cours'    => ['bg'=>'bg-amber-500/15','text'=>'text-amber-300','label'=>'En cours','icon'=>'loader'],
        'termine'     => ['bg'=>'bg-teal-500/15','text'=>'text-teal-300','label'=>'Terminé','icon'=>'check-circle'],
        'suspendu'    => ['bg'=>'bg-slate-500/15','text'=>'text-slate-300','label'=>'Suspendu','icon'=>'pause-circle'],
        'annule'      => ['bg'=>'bg-red-500/15','text'=>'text-red-300','label'=>'Annulé','icon'=>'x-circle'],
    ];
?>

<?php $__env->startSection('manager-content'); ?>
<div class="space-y-6 animate-fade-in-up">

    <!-- Header -->
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-display font-bold text-white">Gestion des Projets</h1>
            <p class="text-cyan-100/55 text-sm mt-0.5">Suivi des projets d'infrastructure et leurs financements</p>
        </div>
        <div class="flex gap-2">
            <a href="<?php echo e(route('manager.projets.map')); ?>" 
               class="glass px-4 py-2 rounded-xl text-sm text-cyan-300 hover:text-white font-semibold flex items-center gap-2 transition-all hover:border-cyan-400/40">
                <i data-lucide="map" class="w-4 h-4"></i>
                <span class="hidden sm:inline">Carte</span>
            </a>
            <a href="<?php echo e(route('manager.projets.create')); ?>" 
               class="glass px-4 py-2 rounded-xl text-sm text-cyan-300 hover:text-white font-semibold flex items-center gap-2 transition-all hover:border-cyan-400/40">
                <i data-lucide="plus" class="w-4 h-4"></i>
                Nouveau projet
            </a>
        </div>
    </div>

    <!-- Statistics -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="glass rounded-2xl p-4 hover-lift">
            <div class="w-10 h-10 rounded-xl bg-blue-500/10 flex items-center justify-center mb-3">
                <i data-lucide="briefcase" class="w-5 h-5 text-blue-400"></i>
            </div>
            <p class="text-2xl font-display font-bold text-white"><?php echo e($stats['total']); ?></p>
            <p class="text-xs font-semibold text-cyan-100/60 mt-0.5">Total Projets</p>
        </div>
        <div class="glass rounded-2xl p-4 hover-lift">
            <div class="w-10 h-10 rounded-xl bg-amber-500/10 flex items-center justify-center mb-3">
                <i data-lucide="loader" class="w-5 h-5 text-amber-400"></i>
            </div>
            <p class="text-2xl font-display font-bold text-white"><?php echo e($stats['en_cours']); ?></p>
            <p class="text-xs font-semibold text-cyan-100/60 mt-0.5">En cours</p>
        </div>
        <div class="glass rounded-2xl p-4 hover-lift">
            <div class="w-10 h-10 rounded-xl bg-cyan-500/10 flex items-center justify-center mb-3">
                <i data-lucide="calendar" class="w-5 h-5 text-cyan-400"></i>
            </div>
            <p class="text-2xl font-display font-bold text-white"><?php echo e($stats['planifie']); ?></p>
            <p class="text-xs font-semibold text-cyan-100/60 mt-0.5">Planifiés</p>
        </div>
        <div class="glass rounded-2xl p-4 hover-lift">
            <div class="w-10 h-10 rounded-xl bg-teal-500/10 flex items-center justify-center mb-3">
                <i data-lucide="check-circle" class="w-5 h-5 text-teal-400"></i>
            </div>
            <p class="text-2xl font-display font-bold text-white"><?php echo e($stats['termine']); ?></p>
            <p class="text-xs font-semibold text-cyan-100/60 mt-0.5">Terminés</p>
        </div>
    </div>

    <!-- Filters & Search -->
    <div class="glass rounded-2xl p-4">
        <form method="GET" action="<?php echo e(route('manager.projets.index')); ?>" class="flex flex-wrap gap-3">
            <!-- Search -->
            <div class="flex-1 min-w-[200px]">
                <input type="text" 
                       name="search" 
                       value="<?php echo e(request('search')); ?>"
                       placeholder="Rechercher un projet..."
                       class="w-full px-4 py-2 rounded-xl bg-white/5 border border-white/10 text-white placeholder-cyan-100/40 focus:border-cyan-400/50 focus:outline-none transition-colors">
            </div>
            
            <!-- Status Filter -->
            <select name="statut" 
                    class="px-4 py-2 rounded-xl bg-white/5 border border-white/10 text-white focus:border-cyan-400/50 focus:outline-none transition-colors">
                <option value="">Tous les statuts</option>
                <option value="planifie" <?php echo e(request('statut') === 'planifie' ? 'selected' : ''); ?>>Planifié</option>
                <option value="en_cours" <?php echo e(request('statut') === 'en_cours' ? 'selected' : ''); ?>>En cours</option>
                <option value="termine" <?php echo e(request('statut') === 'termine' ? 'selected' : ''); ?>>Terminé</option>
                <option value="suspendu" <?php echo e(request('statut') === 'suspendu' ? 'selected' : ''); ?>>Suspendu</option>
                <option value="annule" <?php echo e(request('statut') === 'annule' ? 'selected' : ''); ?>>Annulé</option>
            </select>
            
            <!-- Buttons -->
            <button type="submit" 
                    class="px-4 py-2 rounded-xl bg-cyan-500/15 text-cyan-300 hover:text-white font-semibold border border-cyan-400/25 transition-colors">
                Filtrer
            </button>
            <?php if(request('search') || request('statut')): ?>
            <a href="<?php echo e(route('manager.projets.index')); ?>" 
               class="px-4 py-2 rounded-xl bg-white/5 text-cyan-100/60 hover:text-white font-semibold transition-colors">
                Réinitialiser
            </a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Projects List -->
    <?php if($projets->count() > 0): ?>
    <div class="grid sm:grid-cols-2 gap-4">
        <?php $__currentLoopData = $projets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $projet): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php $ss = $statusStyle[$projet->statut]; ?>
        <article class="glass rounded-2xl p-5 hover-lift">
            <div class="flex items-start justify-between gap-3 mb-3">
                <div class="flex-1">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold <?php echo e($ss['bg']); ?> <?php echo e($ss['text']); ?>">
                            <?php echo e($ss['label']); ?>

                        </span>
                        <?php if($projet->hasGeolocation()): ?>
                        <i data-lucide="map-pin" class="w-3 h-3 text-cyan-400" title="Géolocalisé"></i>
                        <?php endif; ?>
                    </div>
                    <h2 class="text-white font-display font-bold text-base leading-snug"><?php echo e($projet->nom); ?></h2>
                    <?php if($projet->adresse): ?>
                    <p class="text-cyan-100/45 text-xs mt-1 flex items-center gap-1">
                        <i data-lucide="map-pin" class="w-3 h-3"></i>
                        <?php echo e($projet->adresse); ?>

                    </p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Budget & Progress Info -->
            <div class="mb-3 space-y-2">
                <!-- Progression Physique -->
                <div>
                    <div class="flex justify-between text-[11px] text-cyan-100/50 mb-1">
                        <span>Progression physique</span>
                        <span class="font-bold text-white"><?php echo e($projet->progression); ?>%</span>
                    </div>
                    <div class="h-2 rounded-full bg-white/5 overflow-hidden">
                        <?php
                            $progColor = $projet->progression >= 100 ? 'bg-green-400' : ($projet->progression >= 50 ? 'bg-cyan-400' : 'bg-blue-400');
                        ?>
                        <div class="h-full rounded-full <?php echo e($progColor); ?>" style="width: <?php echo e(min($projet->progression, 100)); ?>%"></div>
                    </div>
                </div>

                <!-- Progression Financement -->
                <div>
                    <div class="flex justify-between text-[11px] text-cyan-100/50 mb-1">
                        <span>Budget / Financé</span>
                        <span class="font-bold text-white"><?php echo e(number_format($projet->pourcentage_finance, 1)); ?>%</span>
                    </div>
                    <div class="h-2 rounded-full bg-white/5 overflow-hidden">
                        <?php
                            $percentage = min($projet->pourcentage_finance, 100);
                            $color = $percentage >= 100 ? 'bg-teal-400' : ($percentage >= 50 ? 'bg-cyan-400' : 'bg-blue-400');
                        ?>
                        <div class="h-full rounded-full <?php echo e($color); ?>" style="width: <?php echo e($percentage); ?>%"></div>
                    </div>
                </div>
            </div>

            <!-- Financial Details -->
            <div class="grid grid-cols-2 gap-2 text-xs mb-4">
                <div class="bg-white/[.03] rounded-xl px-3 py-2">
                    <p class="text-cyan-100/40">Budget</p>
                    <p class="text-white font-semibold mt-0.5"><?php echo e(number_format($projet->budget, 0, ',', ' ')); ?> DT</p>
                </div>
                <div class="bg-white/[.03] rounded-xl px-3 py-2">
                    <p class="text-cyan-100/40">Financé</p>
                    <p class="text-white font-semibold mt-0.5"><?php echo e(number_format($projet->total_finance, 0, ',', ' ')); ?> DT</p>
                </div>
                <div class="bg-white/[.03] rounded-xl px-3 py-2">
                    <p class="text-cyan-100/40">Début</p>
                    <p class="text-white font-semibold mt-0.5"><?php echo e($projet->date_debut->format('d/m/Y')); ?></p>
                </div>
                <div class="bg-white/[.03] rounded-xl px-3 py-2">
                    <p class="text-cyan-100/40"><?php echo e($projet->date_fin ? 'Fin' : 'Échéance'); ?></p>
                    <p class="text-white font-semibold mt-0.5"><?php echo e($projet->date_fin ? $projet->date_fin->format('d/m/Y') : 'N/A'); ?></p>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-between">
                <p class="text-[11px] text-cyan-100/45">
                    <?php echo e($projet->financements->count()); ?> financement(s)
                </p>
                <a href="<?php echo e(route('manager.projets.show', $projet)); ?>" 
                   class="text-xs text-cyan-400 hover:text-cyan-300 font-semibold flex items-center gap-1">
                    Voir détails
                    <i data-lucide="arrow-right" class="w-3 h-3"></i>
                </a>
            </div>
        </article>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    <!-- Pagination -->
    <div class="glass rounded-2xl p-4">
        <?php echo e($projets->links()); ?>

    </div>
    <?php else: ?>
    <!-- Empty State -->
    <div class="glass rounded-2xl p-12 text-center">
        <div class="w-16 h-16 rounded-2xl bg-cyan-500/10 flex items-center justify-center mx-auto mb-4">
            <i data-lucide="briefcase" class="w-8 h-8 text-cyan-400"></i>
        </div>
        <h3 class="text-xl font-display font-bold text-white mb-2">Aucun projet trouvé</h3>
        <p class="text-cyan-100/60 mb-6">
            <?php if(request('search') || request('statut')): ?>
                Aucun projet ne correspond à vos critères de recherche.
            <?php else: ?>
                Commencez par créer votre premier projet.
            <?php endif; ?>
        </p>
        <?php if(request('search') || request('statut')): ?>
        <a href="<?php echo e(route('manager.projets.index')); ?>" 
           class="inline-flex items-center gap-2 glass px-4 py-2 rounded-xl text-sm text-cyan-300 hover:text-white font-semibold">
            Voir tous les projets
        </a>
        <?php else: ?>
        <a href="<?php echo e(route('manager.projets.create')); ?>" 
           class="inline-flex items-center gap-2 glass px-4 py-2 rounded-xl text-sm text-cyan-300 hover:text-white font-semibold">
            <i data-lucide="plus" class="w-4 h-4"></i>
            Créer un projet
        </a>
        <?php endif; ?>
    </div>
    <?php endif; ?>

</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
    // Refresh Lucide icons after content load
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.manager', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\monta\Downloads\DEVWAVE\Esprit-5TWIN4-DevWave-main (1)\Esprit-5TWIN4-DevWave-main\resources\views\manager\projets\index.blade.php ENDPATH**/ ?>