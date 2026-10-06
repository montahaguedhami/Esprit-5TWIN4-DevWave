<?php
    $title = $projet->nom . ' — AquaSecure';
    
    $statusStyle = [
        'planifie'    => ['bg'=>'bg-blue-500/15','text'=>'text-blue-300','label'=>'Planifié','icon'=>'calendar'],
        'en_cours'    => ['bg'=>'bg-amber-500/15','text'=>'text-amber-300','label'=>'En cours','icon'=>'loader'],
        'termine'     => ['bg'=>'bg-teal-500/15','text'=>'text-teal-300','label'=>'Terminé','icon'=>'check-circle'],
        'suspendu'    => ['bg'=>'bg-slate-500/15','text'=>'text-slate-300','label'=>'Suspendu','icon'=>'pause-circle'],
        'annule'      => ['bg'=>'bg-red-500/15','text'=>'text-red-300','label'=>'Annulé','icon'=>'x-circle'],
    ];
    
    $ss = $statusStyle[$projet->statut];
?>

<?php $__env->startSection('frontoffice-content'); ?>
<div class="space-y-6 animate-fade-in-up">

    <!-- Breadcrumb -->
    <div>
        <a href="<?php echo e(route('citizen.projets.index')); ?>" class="text-cyan-400 hover:text-cyan-300 text-sm font-semibold flex items-center gap-1 mb-3">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            Retour aux projets
        </a>
    </div>

    <!-- Project Header -->
    <div class="glass rounded-2xl p-6">
        <div class="flex items-start gap-4 mb-4">
            <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-cyan-500 to-blue-600 flex items-center justify-center flex-shrink-0">
                <i data-lucide="briefcase" class="w-7 h-7 text-white"></i>
            </div>
            <div class="flex-1">
                <div class="flex flex-wrap items-center gap-2 mb-2">
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold <?php echo e($ss['bg']); ?> <?php echo e($ss['text']); ?> flex items-center gap-1">
                        <i data-lucide="<?php echo e($ss['icon']); ?>" class="w-3 h-3"></i>
                        <?php echo e($ss['label']); ?>

                    </span>
                    <?php if($projet->hasGeolocation()): ?>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-cyan-500/15 text-cyan-300 flex items-center gap-1">
                        <i data-lucide="map-pin" class="w-3 h-3"></i>
                        Géolocalisé
                    </span>
                    <?php endif; ?>
                </div>
                <h1 class="text-2xl sm:text-3xl font-display font-bold text-white leading-tight"><?php echo e($projet->nom); ?></h1>
                <?php if($projet->adresse): ?>
                <p class="text-cyan-100/60 text-sm mt-2 flex items-center gap-1">
                    <i data-lucide="map-pin" class="w-4 h-4"></i>
                    <?php echo e($projet->adresse); ?>

                </p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Description -->
    <?php if($projet->description): ?>
    <div class="glass rounded-2xl p-6">
        <h2 class="text-lg font-display font-bold text-white mb-3 flex items-center gap-2">
            <i data-lucide="file-text" class="w-5 h-5 text-cyan-400"></i>
            À propos du projet
        </h2>
        <p class="text-cyan-100/80 leading-relaxed"><?php echo e($projet->description); ?></p>
    </div>
    <?php endif; ?>

    <!-- Financial Overview -->
    <div class="glass rounded-2xl p-6">
        <h2 class="text-lg font-display font-bold text-white mb-4 flex items-center gap-2">
            <i data-lucide="wallet" class="w-5 h-5 text-cyan-400"></i>
            Financement du Projet
        </h2>

        <!-- Budget Cards -->
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 mb-4">
            <div class="bg-white/[.03] rounded-xl px-4 py-3">
                <p class="text-cyan-100/50 text-xs mb-1">Budget Total</p>
                <p class="text-white font-bold text-lg"><?php echo e(number_format($projet->budget, 0, ',', ' ')); ?></p>
                <p class="text-cyan-400 text-xs font-semibold">DT</p>
            </div>
            <div class="bg-white/[.03] rounded-xl px-4 py-3">
                <p class="text-cyan-100/50 text-xs mb-1">Montant Financé</p>
                <p class="text-white font-bold text-lg"><?php echo e(number_format($projet->total_finance, 0, ',', ' ')); ?></p>
                <p class="text-cyan-400 text-xs font-semibold">DT</p>
            </div>
            <div class="bg-white/[.03] rounded-xl px-4 py-3 col-span-2 sm:col-span-1">
                <p class="text-cyan-100/50 text-xs mb-1">Pourcentage Financé</p>
                <p class="text-white font-bold text-lg"><?php echo e(number_format($projet->pourcentage_finance, 1)); ?></p>
                <p class="text-cyan-400 text-xs font-semibold">%</p>
            </div>
        </div>

        <!-- Progress Bar -->
        <div class="mb-4">
            <div class="flex justify-between text-xs text-cyan-100/50 mb-2">
                <span>Progression du financement</span>
                <span class="font-bold text-white"><?php echo e(number_format($projet->pourcentage_finance, 1)); ?>%</span>
            </div>
            <div class="h-3 rounded-full bg-white/5 overflow-hidden">
                <?php
                    $percentage = min($projet->pourcentage_finance, 100);
                    $color = $percentage >= 100 ? 'bg-teal-400' : ($percentage >= 50 ? 'bg-cyan-400' : 'bg-blue-400');
                ?>
                <div class="h-full rounded-full <?php echo e($color); ?> transition-all duration-500" style="width: <?php echo e($percentage); ?>%"></div>
            </div>
        </div>

        <?php if($projet->budget_restant < 0): ?>
        <div class="p-3 rounded-xl bg-teal-500/10 border border-teal-400/20">
            <p class="text-teal-300 text-sm flex items-center gap-2">
                <i data-lucide="check-circle" class="w-4 h-4"></i>
                <strong>Projet entièrement financé !</strong>
            </p>
        </div>
        <?php elseif($projet->budget_restant > 0): ?>
        <div class="p-3 rounded-xl bg-cyan-500/10 border border-cyan-400/20">
            <p class="text-cyan-300 text-sm">
                <strong>Montant restant à financer :</strong> <?php echo e(number_format($projet->budget_restant, 0, ',', ' ')); ?> DT
            </p>
        </div>
        <?php endif; ?>
    </div>

    <!-- Funding Sources -->
    <?php if($projet->financements->count() > 0): ?>
    <div class="glass rounded-2xl p-6">
        <h2 class="text-lg font-display font-bold text-white mb-4 flex items-center gap-2">
            <i data-lucide="banknote" class="w-5 h-5 text-cyan-400"></i>
            Sources de Financement (<?php echo e($projet->financements->count()); ?>)
        </h2>

        <div class="space-y-3">
            <?php $__currentLoopData = $projet->financements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $financement): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="bg-white/[.03] rounded-xl p-4 hover:bg-white/[.05] transition-colors">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex items-start gap-3 flex-1">
                        <div class="w-10 h-10 rounded-lg bg-cyan-500/10 flex items-center justify-center flex-shrink-0">
                            <i data-lucide="banknote" class="w-5 h-5 text-cyan-400"></i>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-white font-semibold text-sm mb-1"><?php echo e($financement->source); ?></h3>
                            <p class="text-cyan-100/50 text-xs"><?php echo e($financement->date_financement->format('d/m/Y')); ?></p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-cyan-400 font-bold text-lg"><?php echo e(number_format($financement->montant, 0, ',', ' ')); ?></p>
                        <p class="text-cyan-100/50 text-xs">DT</p>
                    </div>
                </div>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Timeline -->
    <div class="glass rounded-2xl p-6">
        <h2 class="text-lg font-display font-bold text-white mb-4 flex items-center gap-2">
            <i data-lucide="calendar" class="w-5 h-5 text-cyan-400"></i>
            Calendrier
        </h2>

        <div class="grid sm:grid-cols-2 gap-4">
            <div class="bg-white/[.03] rounded-xl p-4">
                <div class="flex items-center gap-2 mb-2">
                    <i data-lucide="play-circle" class="w-4 h-4 text-cyan-400"></i>
                    <p class="text-cyan-100/60 text-sm font-semibold">Date de début</p>
                </div>
                <p class="text-white font-bold text-xl"><?php echo e($projet->date_debut->format('d/m/Y')); ?></p>
                <p class="text-cyan-100/50 text-xs mt-1"><?php echo e($projet->date_debut->diffForHumans()); ?></p>
            </div>

            <?php if($projet->date_fin): ?>
            <div class="bg-white/[.03] rounded-xl p-4">
                <div class="flex items-center gap-2 mb-2">
                    <i data-lucide="flag" class="w-4 h-4 text-cyan-400"></i>
                    <p class="text-cyan-100/60 text-sm font-semibold">Date de fin</p>
                </div>
                <p class="text-white font-bold text-xl"><?php echo e($projet->date_fin->format('d/m/Y')); ?></p>
                <p class="text-cyan-100/50 text-xs mt-1">
                    <?php if($projet->date_fin->isPast()): ?>
                        Terminé <?php echo e($projet->date_fin->diffForHumans()); ?>

                    <?php else: ?>
                        Dans <?php echo e($projet->date_fin->diffForHumans()); ?>

                    <?php endif; ?>
                </p>
            </div>

            <?php if($projet->date_debut && $projet->date_fin): ?>
            <div class="bg-white/[.03] rounded-xl p-4 sm:col-span-2">
                <div class="flex items-center gap-2 mb-2">
                    <i data-lucide="clock" class="w-4 h-4 text-cyan-400"></i>
                    <p class="text-cyan-100/60 text-sm font-semibold">Durée du projet</p>
                </div>
                <p class="text-white font-bold text-xl"><?php echo e($projet->date_debut->diffInDays($projet->date_fin)); ?> jours</p>
            </div>
            <?php endif; ?>
            <?php else: ?>
            <div class="bg-white/[.03] rounded-xl p-4">
                <div class="flex items-center gap-2 mb-2">
                    <i data-lucide="flag" class="w-4 h-4 text-cyan-400"></i>
                    <p class="text-cyan-100/60 text-sm font-semibold">Date de fin</p>
                </div>
                <p class="text-cyan-100/40 text-sm italic">Non définie</p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Map -->
    <?php if($projet->hasGeolocation()): ?>
    <div class="glass rounded-2xl p-6">
        <h2 class="text-lg font-display font-bold text-white mb-4 flex items-center gap-2">
            <i data-lucide="map-pin" class="w-5 h-5 text-cyan-400"></i>
            Localisation
        </h2>

        <?php if($projet->adresse): ?>
        <div class="mb-4">
            <p class="text-cyan-100/60 text-sm mb-1">Adresse</p>
            <p class="text-white font-semibold"><?php echo e($projet->adresse); ?></p>
        </div>
        <?php endif; ?>

        <div class="grid grid-cols-2 gap-3 mb-4">
            <div>
                <p class="text-cyan-100/50 text-xs mb-1">Latitude</p>
                <p class="text-white text-sm font-mono"><?php echo e(number_format($projet->latitude, 4)); ?></p>
            </div>
            <div>
                <p class="text-cyan-100/50 text-xs mb-1">Longitude</p>
                <p class="text-white text-sm font-mono"><?php echo e(number_format($projet->longitude, 4)); ?></p>
            </div>
        </div>

        <!-- Map Placeholder (will be implemented in Phase 10) -->
        <div id="projet-map" class="h-64 rounded-xl bg-white/5 border border-white/10"></div>
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

    <?php if($projet->hasGeolocation()): ?>
    // Initialize project map for citizen view
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof initProjectShowMap === 'function') {
            initProjectShowMap(
                'projet-map',
                <?php echo e($projet->latitude); ?>,
                <?php echo e($projet->longitude); ?>,
                '<?php echo e(addslashes($projet->nom)); ?>'
            );
        }
    });
    <?php endif; ?>
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.frontoffice', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\ghada\Desktop\5ème\projet_Laravel\aquasecure\resources\views/citizen/projets/show.blade.php ENDPATH**/ ?>