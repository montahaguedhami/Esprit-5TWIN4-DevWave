
<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['paginator']));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['paginator']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php if($paginator->hasPages()): ?>
<div class="flex flex-col sm:flex-row items-center justify-between gap-3 px-5 py-4 border-t border-white/5">
    <p class="text-sm text-cyan-100/60">
        Affichage de <span class="font-semibold text-white"><?php echo e($paginator->firstItem()); ?></span>
        à <span class="font-semibold text-white"><?php echo e($paginator->lastItem()); ?></span>
        sur <span class="font-semibold text-white"><?php echo e($paginator->total()); ?></span> résultats
    </p>

    <nav class="flex items-center gap-1" aria-label="Pagination">
        <?php if($paginator->onFirstPage()): ?>
            <span class="w-9 h-9 rounded-lg flex items-center justify-center opacity-30"><i data-lucide="chevron-left" class="w-4 h-4 text-cyan-100/60"></i></span>
        <?php else: ?>
            <a href="<?php echo e($paginator->previousPageUrl()); ?>" class="w-9 h-9 rounded-lg flex items-center justify-center hover:bg-white/5" aria-label="Page précédente"><i data-lucide="chevron-left" class="w-4 h-4 text-cyan-100/60"></i></a>
        <?php endif; ?>

        <?php $__currentLoopData = $paginator->getUrlRange(1, $paginator->lastPage()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page => $url): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <a href="<?php echo e($url); ?>"
               class="w-9 h-9 rounded-lg flex items-center justify-center text-sm font-semibold transition-colors <?php echo e($page === $paginator->currentPage() ? 'bg-gradient-to-r from-cyan-500 to-blue-600 text-white' : 'text-cyan-100/60 hover:bg-white/5'); ?>"
               <?php if($page === $paginator->currentPage()): ?> aria-current="page" <?php endif; ?>><?php echo e($page); ?></a>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

        <?php if($paginator->hasMorePages()): ?>
            <a href="<?php echo e($paginator->nextPageUrl()); ?>" class="w-9 h-9 rounded-lg flex items-center justify-center hover:bg-white/5" aria-label="Page suivante"><i data-lucide="chevron-right" class="w-4 h-4 text-cyan-100/60"></i></a>
        <?php else: ?>
            <span class="w-9 h-9 rounded-lg flex items-center justify-center opacity-30"><i data-lucide="chevron-right" class="w-4 h-4 text-cyan-100/60"></i></span>
        <?php endif; ?>
    </nav>
</div>
<?php endif; ?>
<?php /**PATH C:\Users\monta\Downloads\DEVWAVE\Esprit-5TWIN4-DevWave-main (1)\Esprit-5TWIN4-DevWave-main\resources\views\components\maintenance\pagination.blade.php ENDPATH**/ ?>