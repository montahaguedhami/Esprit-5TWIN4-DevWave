<?php
    $title = 'Nouveau Projet — AquaSecure';
?>

<?php $__env->startSection('manager-content'); ?>
<div class="max-w-4xl mx-auto space-y-6 animate-fade-in-up">

    <!-- Header -->
    <div>
        <a href="<?php echo e(route('manager.projets.index')); ?>" class="text-cyan-400 hover:text-cyan-300 text-sm font-semibold flex items-center gap-1 mb-2">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            Retour aux projets
        </a>
        <h1 class="text-2xl sm:text-3xl font-display font-bold text-white">Créer un Nouveau Projet</h1>
        <p class="text-cyan-100/55 text-sm mt-1">Remplissez les informations du projet d'infrastructure</p>
    </div>

    <!-- Form -->
    <form action="<?php echo e(route('manager.projets.store')); ?>" method="POST" class="space-y-6">
        <?php echo csrf_field(); ?>

        <!-- Basic Information -->
        <div class="glass rounded-2xl p-6">
            <h2 class="text-lg font-display font-bold text-white mb-4 flex items-center gap-2">
                <i data-lucide="file-text" class="w-5 h-5 text-cyan-400"></i>
                Informations Générales
            </h2>

            <div class="space-y-4">
                <!-- Nom -->
                <div>
                    <label for="nom" class="block text-sm font-semibold text-cyan-100/80 mb-2">
                        Nom du projet <span class="text-red-400">*</span>
                    </label>
                    <input type="text" 
                           id="nom" 
                           name="nom" 
                           value="<?php echo e(old('nom')); ?>"
                           class="w-full px-4 py-2.5 rounded-xl bg-white/5 border border-white/10 text-white placeholder-cyan-100/40 focus:border-cyan-400/50 focus:outline-none transition-colors <?php $__errorArgs = ['nom'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-400/50 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                           placeholder="Ex: Réhabilitation du réseau d'eau de Houmt Souk"
                           required>
                    <?php $__errorArgs = ['nom'];
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

                <!-- Description -->
                <div>
                    <label for="description" class="block text-sm font-semibold text-cyan-100/80 mb-2">
                        Description
                    </label>
                    <textarea id="description" 
                              name="description" 
                              rows="4"
                              class="w-full px-4 py-2.5 rounded-xl bg-white/5 border border-white/10 text-white placeholder-cyan-100/40 focus:border-cyan-400/50 focus:outline-none transition-colors resize-none <?php $__errorArgs = ['description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-400/50 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                              placeholder="Description détaillée du projet..."><?php echo e(old('description')); ?></textarea>
                    <?php $__errorArgs = ['description'];
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

                <!-- Dates -->
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label for="date_debut" class="block text-sm font-semibold text-cyan-100/80 mb-2">
                            Date de début <span class="text-red-400">*</span>
                        </label>
                        <input type="date" 
                               id="date_debut" 
                               name="date_debut" 
                               value="<?php echo e(old('date_debut')); ?>"
                               class="w-full px-4 py-2.5 rounded-xl bg-white/5 border border-white/10 text-white focus:border-cyan-400/50 focus:outline-none transition-colors <?php $__errorArgs = ['date_debut'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-400/50 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                               required>
                        <?php $__errorArgs = ['date_debut'];
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
                        <label for="date_fin" class="block text-sm font-semibold text-cyan-100/80 mb-2">
                            Date de fin (optionnel)
                        </label>
                        <input type="date" 
                               id="date_fin" 
                               name="date_fin" 
                               value="<?php echo e(old('date_fin')); ?>"
                               class="w-full px-4 py-2.5 rounded-xl bg-white/5 border border-white/10 text-white focus:border-cyan-400/50 focus:outline-none transition-colors <?php $__errorArgs = ['date_fin'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-400/50 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                        <?php $__errorArgs = ['date_fin'];
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

                <!-- Budget & Statut -->
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label for="budget" class="block text-sm font-semibold text-cyan-100/80 mb-2">
                            Budget (DT) <span class="text-red-400">*</span>
                        </label>
                        <input type="number" 
                               id="budget" 
                               name="budget" 
                               value="<?php echo e(old('budget')); ?>"
                               step="0.01"
                               min="0"
                               class="w-full px-4 py-2.5 rounded-xl bg-white/5 border border-white/10 text-white placeholder-cyan-100/40 focus:border-cyan-400/50 focus:outline-none transition-colors <?php $__errorArgs = ['budget'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-400/50 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                               placeholder="Ex: 500000"
                               required>
                        <?php $__errorArgs = ['budget'];
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
                        <label for="statut" class="block text-sm font-semibold text-cyan-100/80 mb-2">
                            Statut <span class="text-red-400">*</span>
                        </label>
                        <select id="statut" 
                                name="statut"
                                class="w-full px-4 py-2.5 rounded-xl bg-white/5 border border-white/10 text-white focus:border-cyan-400/50 focus:outline-none transition-colors <?php $__errorArgs = ['statut'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-400/50 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                required>
                            <option value="">Sélectionner un statut</option>
                            <option value="planifie" <?php echo e(old('statut') === 'planifie' ? 'selected' : ''); ?>>Planifié</option>
                            <option value="en_cours" <?php echo e(old('statut') === 'en_cours' ? 'selected' : ''); ?>>En cours</option>
                            <option value="termine" <?php echo e(old('statut') === 'termine' ? 'selected' : ''); ?>>Terminé</option>
                            <option value="suspendu" <?php echo e(old('statut') === 'suspendu' ? 'selected' : ''); ?>>Suspendu</option>
                            <option value="annule" <?php echo e(old('statut') === 'annule' ? 'selected' : ''); ?>>Annulé</option>
                        </select>
                        <?php $__errorArgs = ['statut'];
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

        <!-- Geolocation -->
        <div class="glass rounded-2xl p-6">
            <h2 class="text-lg font-display font-bold text-white mb-4 flex items-center gap-2">
                <i data-lucide="map-pin" class="w-5 h-5 text-cyan-400"></i>
                Localisation (optionnel)
            </h2>

            <div class="space-y-4">
                <!-- Adresse -->
                <div>
                    <label for="adresse" class="block text-sm font-semibold text-cyan-100/80 mb-2">
                        Adresse
                    </label>
                    <input type="text" 
                           id="adresse" 
                           name="adresse" 
                           value="<?php echo e(old('adresse')); ?>"
                           class="w-full px-4 py-2.5 rounded-xl bg-white/5 border border-white/10 text-white placeholder-cyan-100/40 focus:border-cyan-400/50 focus:outline-none transition-colors <?php $__errorArgs = ['adresse'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-400/50 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                           placeholder="Ex: Houmt Souk, Djerba, Médenine">
                    <?php $__errorArgs = ['adresse'];
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

                <!-- Coordinates -->
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label for="latitude" class="block text-sm font-semibold text-cyan-100/80 mb-2">
                            Latitude
                        </label>
                        <input type="number" 
                               id="latitude" 
                               name="latitude" 
                               value="<?php echo e(old('latitude')); ?>"
                               step="0.0000001"
                               min="-90"
                               max="90"
                               class="w-full px-4 py-2.5 rounded-xl bg-white/5 border border-white/10 text-white placeholder-cyan-100/40 focus:border-cyan-400/50 focus:outline-none transition-colors <?php $__errorArgs = ['latitude'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-400/50 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                               placeholder="Ex: 33.8751">
                        <?php $__errorArgs = ['latitude'];
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
                        <label for="longitude" class="block text-sm font-semibold text-cyan-100/80 mb-2">
                            Longitude
                        </label>
                        <input type="number" 
                               id="longitude" 
                               name="longitude" 
                               value="<?php echo e(old('longitude')); ?>"
                               step="0.0000001"
                               min="-180"
                               max="180"
                               class="w-full px-4 py-2.5 rounded-xl bg-white/5 border border-white/10 text-white placeholder-cyan-100/40 focus:border-cyan-400/50 focus:outline-none transition-colors <?php $__errorArgs = ['longitude'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-400/50 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                               placeholder="Ex: 10.8576">
                        <?php $__errorArgs = ['longitude'];
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

                <!-- Map Interactive -->
                <div id="projet-map-form" class="h-96 rounded-xl overflow-hidden border border-white/10"></div>
                <p class="text-cyan-100/50 text-xs mt-2">
                    <i data-lucide="info" class="w-3 h-3 inline"></i>
                    Cliquez sur la carte pour placer le marqueur, ou déplacez-le en le faisant glisser
                </p>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-end gap-3">
            <a href="<?php echo e(route('manager.projets.index')); ?>" 
               class="px-6 py-2.5 rounded-xl bg-white/5 text-cyan-100/80 hover:text-white font-semibold transition-colors">
                Annuler
            </a>
            <button type="submit" 
                    class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-cyan-500 to-blue-600 text-white font-semibold hover:shadow-lg hover:shadow-cyan-500/25 transition-all flex items-center gap-2">
                <i data-lucide="check" class="w-4 h-4"></i>
                Créer le projet
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

    // Initialize editable map for project creation
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof initProjectFormMap === 'function') {
            initProjectFormMap(
                'projet-map-form',
                'latitude',
                'longitude',
                <?php echo e(old('latitude') ?? 'null'); ?>,
                <?php echo e(old('longitude') ?? 'null'); ?>

            );
        }
    });
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.manager', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\ghada\Desktop\5ème\projet_Laravel\aquasecure\resources\views/manager/projets/create.blade.php ENDPATH**/ ?>