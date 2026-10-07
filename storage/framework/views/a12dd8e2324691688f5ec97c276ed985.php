

<?php $__env->startSection('manager-content'); ?>
<div class="max-w-3xl mx-auto py-12">
    <h1 class="text-2xl font-bold mb-4">Modifier l'incident</h1>

    <?php if($errors->any()): ?>
        <div class="mb-4 p-3 bg-red-600 text-white rounded">
            <ul class="list-disc pl-5">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="<?php echo e(route('manager.incidents.update', $incident)); ?>" method="post" class="space-y-4 bg-white/5 p-6 rounded">
        <?php echo csrf_field(); ?>
        <?php echo method_field('PUT'); ?>
        <div>
            <label class="block text-sm">Titre</label>
            <input type="text" name="titre" value="<?php echo e(old('titre', $incident->titre)); ?>" class="mt-1 w-full rounded p-2 bg-black/20" required>
        </div>

        <div>
            <label class="block text-sm">Description</label>
            <textarea name="description" class="mt-1 w-full rounded p-2 bg-black/20"><?php echo e(old('description', $incident->description)); ?></textarea>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm">Type</label>
                <select name="type" class="mt-1 w-full rounded p-2 bg-black/20" required>
                    <?php $__currentLoopData = ['fuite','pollution','panne','rupture','contamination']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($t); ?>" <?php echo e($incident->type === $t ? 'selected' : ''); ?>><?php echo e(ucfirst($t)); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div>
                <label class="block text-sm">Gravité</label>
                <select name="gravite" class="mt-1 w-full rounded p-2 bg-black/20" required>
                    <?php $__currentLoopData = ['faible','moyenne','elevee','critique']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $g): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($g); ?>" <?php echo e($incident->gravite === $g ? 'selected' : ''); ?>><?php echo e(ucfirst($g)); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
        </div>

        <div>
            <label class="block text-sm">Statut</label>
            <select name="statut" class="mt-1 w-full rounded p-2 bg-black/20" required>
                <?php $__currentLoopData = ['signale','en_cours','resolu','cloture']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($s); ?>" <?php echo e($incident->statut === $s ? 'selected' : ''); ?>><?php echo e(ucfirst($s)); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>

        <div>
            <label class="block text-sm">Date de signalement</label>
            <input type="datetime-local" name="date_signalement" value="<?php echo e(old('date_signalement', optional($incident->date_signalement)->format('Y-m-d\TH:i'))); ?>" class="mt-1 w-full rounded p-2 bg-black/20" required>
        </div>

        <div>
            <label class="block text-sm">Localisation</label>
            <input type="text" name="localisation" value="<?php echo e(old('localisation', $incident->localisation)); ?>" class="mt-1 w-full rounded p-2 bg-black/20">
        </div>

        <div>
            <button class="px-4 py-2 rounded bg-cyan-500 text-white">Mettre à jour</button>
        </div>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.manager', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Ranim\Documents\GitHub\Esprit-5TWIN4-DevWave\resources\views/manager/incidents/edit.blade.php ENDPATH**/ ?>