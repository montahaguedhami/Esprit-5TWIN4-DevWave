<div class="grid gap-4 sm:grid-cols-2">
    <label class="text-sm text-slate-300">Référence unique
        <input name="reference" required maxlength="50" value="<?php echo e(old('reference', $mesure->reference ?? '')); ?>" class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-white">
        <?php $__errorArgs = ['reference'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="text-xs text-red-300"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </label>
    <label class="text-sm text-slate-300">Point de prélèvement
        <select name="point_mesure_id" required class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-white">
            <?php $__currentLoopData = $points; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $point): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($point->id); ?>" <?php if((string) old('point_mesure_id', request('point_mesure_id', $mesure->point_mesure_id ?? '')) === (string) $point->id): echo 'selected'; endif; ?>><?php echo e($point->nom); ?> (<?php echo e($point->code); ?>)</option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
        <?php $__errorArgs = ['point_mesure_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="text-xs text-red-300"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </label>
    <label class="text-sm text-slate-300">Date de mesure
        <input type="datetime-local" name="date_mesure" required value="<?php echo e(old('date_mesure', isset($mesure) ? $mesure->date_mesure->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i'))); ?>" class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-white">
        <?php $__errorArgs = ['date_mesure'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="text-xs text-red-300"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </label>
    <label class="text-sm text-slate-300">pH (0–14)
        <input type="number" name="ph" min="0" max="14" step="0.01" required value="<?php echo e(old('ph', $mesure->ph ?? '')); ?>" class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-white">
        <?php $__errorArgs = ['ph'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="text-xs text-red-300"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </label>
    <label class="text-sm text-slate-300">Turbidité (NTU)
        <input type="number" name="turbidite" min="0" step="0.01" required value="<?php echo e(old('turbidite', $mesure->turbidite ?? '')); ?>" class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-white">
        <?php $__errorArgs = ['turbidite'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="text-xs text-red-300"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </label>
    <label class="text-sm text-slate-300">Chlore résiduel (mg/L)
        <input type="number" name="chlore_residuel" min="0" step="0.001" required value="<?php echo e(old('chlore_residuel', $mesure->chlore_residuel ?? '')); ?>" class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-white">
        <?php $__errorArgs = ['chlore_residuel'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="text-xs text-red-300"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </label>
    <label class="text-sm text-slate-300">Plomb (ppb)
        <input type="number" name="plomb" min="0" step="0.001" required value="<?php echo e(old('plomb', $mesure->plomb ?? '')); ?>" class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-white">
        <?php $__errorArgs = ['plomb'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="text-xs text-red-300"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </label>
    <label class="text-sm text-slate-300">Nitrates (mg/L)
        <input type="number" name="nitrates" min="0" step="0.01" required value="<?php echo e(old('nitrates', $mesure->nitrates ?? '')); ?>" class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-white">
        <?php $__errorArgs = ['nitrates'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="text-xs text-red-300"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </label>
    <label class="text-sm text-slate-300">Vérificateur / laboratoire
        <input name="verifier" maxlength="255" value="<?php echo e(old('verifier', $mesure->verifier ?? '')); ?>" class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-white">
    </label>
    <div class="sm:col-span-2">
        <input type="hidden" name="is_verified" value="0">
        <label class="inline-flex items-center gap-2 text-sm text-slate-300"><input type="checkbox" name="is_verified" value="1" <?php if(old('is_verified', $mesure->is_verified ?? false)): echo 'checked'; endif; ?>> Mesure vérifiée</label>
        <?php $__errorArgs = ['is_verified'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="ml-2 text-xs text-red-300"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>
</div>
<?php /**PATH C:\Users\monta\Downloads\DEVWAVE\Esprit-5TWIN4-DevWave-main (1)\Esprit-5TWIN4-DevWave-main\resources\views\manager\quality-measures\form.blade.php ENDPATH**/ ?>