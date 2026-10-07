<?php
    $fieldValues = collect([
        'titre' => $incident->titre ?? '',
        'description' => $incident->description ?? '',
        'date_signalement' => isset($incident) ? $incident->date_signalement?->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i'),
        'localisation' => $incident->localisation ?? '',
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
    <label for="titre" class="block text-sm">Titre</label>
    <input id="titre" type="text" name="titre" maxlength="255" value="<?php echo e($fieldValues['titre']); ?>" class="mt-1 w-full min-w-0 rounded p-2 bg-black/20" required>
</div>
<div>
    <label for="description" class="block text-sm">Description</label>
    <textarea id="description" name="description" rows="4" maxlength="5000" class="mt-1 w-full rounded p-2 bg-black/20" required><?php echo e($fieldValues['description']); ?></textarea>
</div>
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <label for="type" class="block text-sm">Type</label>
        <select id="type" name="type" class="mt-1 w-full rounded p-2 bg-slate-900" required>
            <option value="" disabled <?php if(old('type', $incident->type ?? '') === ''): echo 'selected'; endif; ?>>Choisir un type</option>
            <?php $__currentLoopData = ['fuite' => 'Fuite', 'pollution' => 'Pollution', 'panne' => 'Panne', 'rupture' => 'Rupture', 'contamination' => 'Contamination']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($value); ?>" <?php if(old('type', $incident->type ?? 'fuite') === $value): echo 'selected'; endif; ?>><?php echo e($label); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </div>
    <div>
        <label for="gravite" class="block text-sm">Gravit&eacute;</label>
        <select id="gravite" name="gravite" class="mt-1 w-full rounded p-2 bg-slate-900" required>
            <option value="" disabled <?php if(old('gravite', $incident->gravite ?? '') === ''): echo 'selected'; endif; ?>>Choisir une gravit&eacute;</option>
            <?php $__currentLoopData = ['faible' => 'Faible', 'moyenne' => 'Moyenne', 'elevee' => 'Elevee', 'critique' => 'Critique']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($value); ?>" <?php if(old('gravite', $incident->gravite ?? 'faible') === $value): echo 'selected'; endif; ?>><?php echo e($label); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </div>
</div>
<?php if(isset($incident)): ?>
    <div>
        <label for="statut" class="block text-sm">Statut</label>
        <select id="statut" name="statut" class="mt-1 w-full rounded p-2 bg-slate-900" required>
            <option value="" disabled <?php if(old('statut', $incident->statut) === ''): echo 'selected'; endif; ?>>Choisir un statut</option>
            <?php $__currentLoopData = ['signale' => 'Signale', 'en_cours' => 'En cours', 'resolu' => 'Resolu', 'cloture' => 'Cloture']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($value); ?>" <?php if(old('statut', $incident->statut) === $value): echo 'selected'; endif; ?>><?php echo e($label); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </div>
<?php endif; ?>
<div>
    <label for="date_signalement" class="block text-sm">Date de signalement</label>
    <input id="date_signalement" type="datetime-local" name="date_signalement" value="<?php echo e($fieldValues['date_signalement']); ?>" class="mt-1 w-full min-w-0 rounded p-2 bg-black/20" required>
</div>
<div>
    <label for="localisation" class="block text-sm">Localisation</label>
    <input id="localisation" type="text" name="localisation" maxlength="255" value="<?php echo e($fieldValues['localisation']); ?>" class="mt-1 w-full rounded p-2 bg-black/20" required>
</div><?php /**PATH C:\Users\Ranim\Documents\GitHub\Esprit-5TWIN4-DevWave\resources\views/front/incidents/form.blade.php ENDPATH**/ ?>