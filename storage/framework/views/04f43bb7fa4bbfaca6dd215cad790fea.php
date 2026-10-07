
<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'title',
    'subtitle' => null,
    'icon' => 'wrench',
]));

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

foreach (array_filter(([
    'title',
    'subtitle' => null,
    'icon' => 'wrench',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div class="flex items-center gap-4">
        <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-cyan-400/20 to-blue-600/20 border border-cyan-400/20 flex items-center justify-center shrink-0">
            <i data-lucide="<?php echo e($icon); ?>" class="w-6 h-6 text-cyan-300"></i>
        </div>
        <div>
            <p class="text-[11px] font-semibold uppercase tracking-wider text-cyan-300/70">Maintenance</p>
            <h1 class="text-2xl sm:text-3xl font-display font-bold text-white tracking-tight"><?php echo e($title); ?></h1>
            <?php if($subtitle): ?>
                <p class="text-cyan-100/60 text-sm mt-1"><?php echo e($subtitle); ?></p>
            <?php endif; ?>
        </div>
    </div>

    <?php if($slot->isNotEmpty()): ?>
        <div class="flex flex-wrap items-center gap-2">
            <?php echo e($slot); ?>

        </div>
    <?php endif; ?>
</div>
<?php /**PATH C:\Users\Ranim\Documents\GitHub\Esprit-5TWIN4-DevWave\resources\views/components/maintenance/page-header.blade.php ENDPATH**/ ?>