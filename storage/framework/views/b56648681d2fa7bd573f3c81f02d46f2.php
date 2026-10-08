<?php
    $fieldValues = collect([
        'titre' => $action->titre ?? '',
        'description' => $action->description ?? '',
        'responsable' => $action->responsable ?? '',
        'date_prevue' => isset($action) ? $action->date_prevue?->format('Y-m-d') : '',
        'date_realisation' => isset($action) ? $action->date_realisation?->format('Y-m-d\TH:i') : '',
        'resultat' => $action->resultat ?? '',
    ])->map(function ($default, $field) {
        $value = old($field, $default);
        return is_scalar($value) ? $value : '';
    });
?>
<?php if($errors->any()): ?>
    <div role="alert" class="p-3 bg-red-600 text-white rounded">
        <ul class="list-disc pl-5">
            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li><?php echo e($error); ?></li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
    </div>
<?php endif; ?>
<div>
    <label for="action_titre" class="block text-sm">Titre</label>
    <input id="action_titre" type="text" name="titre" maxlength="255" value="<?php echo e($fieldValues['titre']); ?>" class="mt-1 w-full rounded p-2 bg-black/20" required>
</div>
<div>
    <label for="action_description" class="block text-sm">Description</label>
    <textarea id="action_description" name="description" rows="3" maxlength="5000" class="mt-1 w-full rounded p-2 bg-black/20" required><?php echo e($fieldValues['description']); ?></textarea>
</div>
<div>
    <label for="responsable" class="block text-sm">Responsable</label>
    <input id="responsable" type="text" name="responsable" maxlength="255" value="<?php echo e($fieldValues['responsable']); ?>" class="mt-1 w-full rounded p-2 bg-black/20" required>
</div>
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <label for="date_prevue" class="block text-sm">Date pr&eacute;vue</label>
        <input id="date_prevue" type="date" name="date_prevue" value="<?php echo e($fieldValues['date_prevue']); ?>" class="mt-1 w-full min-w-0 rounded p-2 bg-black/20" required>
    </div>
    <div>
        <label for="date_realisation" class="block text-sm">Date de r&eacute;alisation</label>
        <input id="date_realisation" type="datetime-local" name="date_realisation" value="<?php echo e($fieldValues['date_realisation']); ?>" class="mt-1 w-full min-w-0 rounded p-2 bg-black/20" required>
    </div>
</div>
<div>
    <label for="action_statut" class="block text-sm">Statut</label>
    <select id="action_statut" name="statut" class="mt-1 w-full rounded p-2 bg-slate-900" required>
        <option value="" disabled <?php if(old('statut', $action->statut ?? '') === ''): echo 'selected'; endif; ?>>Choisir un statut</option>
        <?php $__currentLoopData = ['a_faire' => 'A faire', 'en_cours' => 'En cours', 'terminee' => 'Terminee']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($value); ?>" <?php if(old('statut', $action->statut ?? 'a_faire') === $value): echo 'selected'; endif; ?>><?php echo e($label); ?></option>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </select>
</div>
<div>
    <label for="resultat" class="block text-sm">R&eacute;sultat</label>
    <textarea id="resultat" name="resultat" rows="3" maxlength="5000" class="mt-1 w-full rounded p-2 bg-black/20" required><?php echo e($fieldValues['resultat']); ?></textarea>
</div><?php /**PATH C:\Users\monta\Downloads\DEVWAVE\Esprit-5TWIN4-DevWave-main (1)\Esprit-5TWIN4-DevWave-main\resources\views\manager\actions\form.blade.php ENDPATH**/ ?>