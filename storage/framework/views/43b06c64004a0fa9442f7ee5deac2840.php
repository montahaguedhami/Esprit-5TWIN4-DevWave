

<?php $__env->startSection('frontoffice-content'); ?>
<div class="max-w-3xl mx-auto py-12">
    <h1 class="text-2xl font-bold mb-4">Signaler un incident</h1>

    <a href="<?php echo e(route('incidents.index')); ?>" class="text-cyan-300">Mes incidents</a>
    <form action="<?php echo e(route('incidents.store')); ?>" method="post" class="space-y-4 mt-6">
        <?php echo csrf_field(); ?>
        <?php echo $__env->make('front.incidents.form', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <button class="inline-flex items-center gap-2 px-4 py-2 rounded bg-cyan-500 text-white"><i data-lucide="send" class="w-4 h-4"></i>Envoyer</button>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.frontoffice', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Ranim\Documents\GitHub\Esprit-5TWIN4-DevWave\resources\views/front/incidents/create.blade.php ENDPATH**/ ?>