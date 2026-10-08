<?php ($editing = isset($point)); ?>
<div class="grid gap-4 sm:grid-cols-2">
    <label class="text-sm text-slate-300">Code unique
        <input name="code" required maxlength="50" value="<?php echo e(old('code', $point->code ?? '')); ?>" class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-white">
        <?php $__errorArgs = ['code'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="text-xs text-red-300"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </label>
    <label class="text-sm text-slate-300">Nom
        <input name="nom" required value="<?php echo e(old('nom', $point->nom ?? '')); ?>" class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-white">
        <?php $__errorArgs = ['nom'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="text-xs text-red-300"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </label>
    <label class="text-sm text-slate-300">Zone
        <input name="zone" value="<?php echo e(old('zone', $point->zone ?? '')); ?>" class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-white">
    </label>
    <label class="text-sm text-slate-300">Adresse
        <input name="adresse" value="<?php echo e(old('adresse', $point->adresse ?? '')); ?>" class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-white">
    </label>
    <label class="text-sm text-slate-300">Latitude
        <input name="latitude" type="number" step="0.0000001" min="-90" max="90" value="<?php echo e(old('latitude', $point->latitude ?? '')); ?>" class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-white">
        <?php $__errorArgs = ['latitude'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="text-xs text-red-300"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </label>
    <label class="text-sm text-slate-300">Longitude
        <input name="longitude" type="number" step="0.0000001" min="-180" max="180" value="<?php echo e(old('longitude', $point->longitude ?? '')); ?>" class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-white">
        <?php $__errorArgs = ['longitude'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="text-xs text-red-300"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </label>
    <label class="text-sm text-slate-300">Type
        <select name="type" required class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-white">
            <?php $__currentLoopData = ['Station', 'Puits', 'Réservoir', 'Réseau', 'Autre']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($type); ?>" <?php if(old('type', $point->type ?? 'Station') === $type): echo 'selected'; endif; ?>><?php echo e($type); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </label>
    <label class="text-sm text-slate-300">Statut
        <select name="statut" required class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-white">
            <?php $__currentLoopData = ['actif', 'inactif']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $statut): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($statut); ?>" <?php if(old('statut', $point->statut ?? 'actif') === $statut): echo 'selected'; endif; ?>><?php echo e(ucfirst($statut)); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </label>
    <label class="text-sm text-slate-300 sm:col-span-2">Description
        <textarea name="description" rows="3" class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-white"><?php echo e(old('description', $point->description ?? '')); ?></textarea>
    </label>
</div>
<?php /**PATH C:\Users\monta\Downloads\DEVWAVE\Esprit-5TWIN4-DevWave-main (1)\Esprit-5TWIN4-DevWave-main\resources\views\manager\points-mesure\form.blade.php ENDPATH**/ ?>