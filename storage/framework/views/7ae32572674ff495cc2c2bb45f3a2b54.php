<?php
    $title = 'Nouveau Financement — AquaSecure';
?>

<?php $__env->startSection('manager-content'); ?>
<div class="max-w-3xl mx-auto space-y-6 animate-fade-in-up">

    <!-- Header -->
    <div>
        <a href="<?php echo e(route('manager.financements.index')); ?>" class="text-cyan-400 hover:text-cyan-300 text-sm font-semibold flex items-center gap-1 mb-2">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            Retour aux financements
        </a>
        <h1 class="text-2xl sm:text-3xl font-display font-bold text-white">Créer un Nouveau Financement</h1>
        <p class="text-cyan-100/55 text-sm mt-1">Enregistrez une source de financement pour un projet</p>
    </div>

    <!-- Form -->
    <form action="<?php echo e(route('manager.financements.store')); ?>" method="POST" class="space-y-6">
        <?php echo csrf_field(); ?>

        <!-- Main Information -->
        <div class="glass rounded-2xl p-6">
            <h2 class="text-lg font-display font-bold text-white mb-4 flex items-center gap-2">
                <i data-lucide="banknote" class="w-5 h-5 text-cyan-400"></i>
                Informations du Financement
            </h2>

            <div class="space-y-4">
                <!-- Projet -->
                <div>
                    <label for="projet_id" class="block text-sm font-semibold text-cyan-100/80 mb-2">
                        Projet <span class="text-red-400">*</span>
                    </label>
                    <select id="projet_id" 
                            name="projet_id"
                            class="w-full px-4 py-2.5 rounded-xl bg-white/5 border border-white/10 text-white focus:border-cyan-400/50 focus:outline-none transition-colors <?php $__errorArgs = ['projet_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-400/50 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                            required>
                        <option value="" style="background-color: #1e293b; color: #94a3b8;">Sélectionner un projet</option>
                        <?php $__currentLoopData = $projets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $projet): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($projet->id); ?>" 
                                <?php echo e(old('projet_id', $projetId ?? '') == $projet->id ? 'selected' : ''); ?>

                                data-budget="<?php echo e($projet->budget); ?>"
                                data-finance="<?php echo e($projet->total_finance); ?>"
                                style="background-color: #1e293b; color: #f8fafc;">
                            <?php echo e($projet->nom); ?> (Budget: <?php echo e(number_format($projet->budget, 0, ',', ' ')); ?> DT)
                        </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <?php $__errorArgs = ['projet_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <p class="mt-1 text-sm text-red-400 flex items-center gap-1">
                        <i data-lucide="alert-circle" class="w-3 h-3"></i>
                        <?php echo e($message); ?>

                    </p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    
                    <!-- Project Info Display -->
                    <div id="projet-info" class="hidden mt-3 p-3 rounded-xl bg-cyan-500/5 border border-cyan-400/20">
                        <div class="grid grid-cols-3 gap-3 text-sm">
                            <div>
                                <p class="text-cyan-100/50 text-xs mb-1">Budget</p>
                                <p class="text-white font-semibold" id="projet-budget">-</p>
                            </div>
                            <div>
                                <p class="text-cyan-100/50 text-xs mb-1">Déjà financé</p>
                                <p class="text-white font-semibold" id="projet-finance">-</p>
                            </div>
                            <div>
                                <p class="text-cyan-100/50 text-xs mb-1">Restant</p>
                                <p class="text-white font-semibold" id="projet-restant">-</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Source -->
                <div>
                    <label for="source" class="block text-sm font-semibold text-cyan-100/80 mb-2">
                        Source de financement <span class="text-red-400">*</span>
                    </label>
                    <input type="text" 
                           id="source" 
                           name="source" 
                           value="<?php echo e(old('source')); ?>"
                           class="w-full px-4 py-2.5 rounded-xl bg-white/5 border border-white/10 text-white placeholder-cyan-100/40 focus:border-cyan-400/50 focus:outline-none transition-colors <?php $__errorArgs = ['source'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-400/50 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                           placeholder="Ex: Banque Mondiale, Ministère de l'Équipement..."
                           required>
                    <?php $__errorArgs = ['source'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <p class="mt-1 text-sm text-red-400 flex items-center gap-1">
                        <i data-lucide="alert-circle" class="w-3 h-3"></i>
                        <?php echo e($message); ?>

                    </p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    
                    <!-- Common Sources Suggestions -->
                    <div class="mt-2 flex flex-wrap gap-2">
                        <p class="text-xs text-cyan-100/50 w-full mb-1">Suggestions :</p>
                        <?php $__currentLoopData = ['Banque Mondiale', 'Banque Africaine de Développement', 'Union Européenne', 'Budget de l\'État Tunisien', 'SONEDE']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $suggestion): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <button type="button" 
                                onclick="document.getElementById('source').value = '<?php echo e($suggestion); ?>'"
                                class="px-2 py-1 rounded-lg bg-white/5 hover:bg-white/10 text-cyan-300 hover:text-white text-xs transition-colors">
                            <?php echo e($suggestion); ?>

                        </button>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>

                <!-- Montant & Date -->
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label for="montant" class="block text-sm font-semibold text-cyan-100/80 mb-2">
                            Montant (DT) <span class="text-red-400">*</span>
                        </label>
                        <input type="number" 
                               id="montant" 
                               name="montant" 
                               value="<?php echo e(old('montant')); ?>"
                               step="0.01"
                               min="0"
                               class="w-full px-4 py-2.5 rounded-xl bg-white/5 border border-white/10 text-white placeholder-cyan-100/40 focus:border-cyan-400/50 focus:outline-none transition-colors <?php $__errorArgs = ['montant'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-400/50 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                               placeholder="Ex: 250000"
                               required>
                        <?php $__errorArgs = ['montant'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <p class="mt-1 text-sm text-red-400 flex items-center gap-1">
                            <i data-lucide="alert-circle" class="w-3 h-3"></i>
                            <?php echo e($message); ?>

                        </p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div>
                        <label for="date_financement" class="block text-sm font-semibold text-cyan-100/80 mb-2">
                            Date de financement <span class="text-red-400">*</span>
                        </label>
                        <input type="date" 
                               id="date_financement" 
                               name="date_financement" 
                               value="<?php echo e(old('date_financement', date('Y-m-d'))); ?>"
                               class="w-full px-4 py-2.5 rounded-xl bg-white/5 border border-white/10 text-white focus:border-cyan-400/50 focus:outline-none transition-colors <?php $__errorArgs = ['date_financement'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-400/50 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                               required>
                        <?php $__errorArgs = ['date_financement'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <p class="mt-1 text-sm text-red-400 flex items-center gap-1">
                            <i data-lucide="alert-circle" class="w-3 h-3"></i>
                            <?php echo e($message); ?>

                        </p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-end gap-3">
            <a href="<?php echo e(route('manager.financements.index')); ?>" 
               class="px-6 py-2.5 rounded-xl bg-white/5 text-cyan-100/80 hover:text-white font-semibold transition-colors">
                Annuler
            </a>
            <button type="submit" 
                    class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-cyan-500 to-blue-600 text-white font-semibold hover:shadow-lg hover:shadow-cyan-500/25 transition-all flex items-center gap-2">
                <i data-lucide="check" class="w-4 h-4"></i>
                Créer le financement
            </button>
        </div>
    </form>

</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
    // Refresh Lucide icons
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }

    // Show project budget info when project is selected
    const projetSelect = document.getElementById('projet_id');
    const projetInfo = document.getElementById('projet-info');
    
    projetSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        
        if (this.value) {
            const budget = parseFloat(selectedOption.dataset.budget);
            const finance = parseFloat(selectedOption.dataset.finance);
            const restant = budget - finance;
            
            document.getElementById('projet-budget').textContent = 
                new Intl.NumberFormat('fr-TN', { style: 'decimal' }).format(budget) + ' DT';
            document.getElementById('projet-finance').textContent = 
                new Intl.NumberFormat('fr-TN', { style: 'decimal' }).format(finance) + ' DT';
            document.getElementById('projet-restant').textContent = 
                new Intl.NumberFormat('fr-TN', { style: 'decimal' }).format(restant) + ' DT';
            
            projetInfo.classList.remove('hidden');
        } else {
            projetInfo.classList.add('hidden');
        }
    });
    
    // Trigger on page load if project is pre-selected
    if (projetSelect.value) {
        projetSelect.dispatchEvent(new Event('change'));
    }
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.manager', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\monta\Downloads\DEVWAVE\Esprit-5TWIN4-DevWave-main (1)\Esprit-5TWIN4-DevWave-main\resources\views\manager\financements\create.blade.php ENDPATH**/ ?>