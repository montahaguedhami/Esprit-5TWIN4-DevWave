
<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'href',
    'variant' => 'primary', // primary, secondary, danger
    'icon' => null,
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
    'href',
    'variant' => 'primary', // primary, secondary, danger
    'icon' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $variantClasses = match ($variant) {
        'secondary' => 'bg-slate-800/50 hover:bg-slate-700/50 text-cyan-100 border border-cyan-400/15 hover:border-cyan-400/30',
        'danger' => 'bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 border border-rose-400/30',
        default => 'bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 text-white shadow-lg shadow-cyan-500/25',
    };
?>

<a href="<?php echo e($href); ?>" <?php echo e($attributes->merge(['class' => "inline-flex items-center justify-center gap-2 text-sm font-semibold px-4 py-2.5 rounded-xl transition-all duration-300 $variantClasses"])); ?>>
    <?php if($icon): ?>
        <i data-lucide="<?php echo e($icon); ?>" class="w-4 h-4"></i>
    <?php endif; ?>
    <span><?php echo e($slot); ?></span>
</a>
<?php /**PATH C:\Users\Ranim\Documents\GitHub\Esprit-5TWIN4-DevWave\resources\views/components/maintenance/link-button.blade.php ENDPATH**/ ?>