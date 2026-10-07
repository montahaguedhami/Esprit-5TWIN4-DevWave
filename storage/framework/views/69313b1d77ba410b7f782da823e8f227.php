
<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'fallback',
    'iconClass' => 'w-4 h-4',
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
    'fallback',
    'iconClass' => 'w-4 h-4',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<a href="<?php echo e($fallback); ?>"
   onclick="if (document.referrer.startsWith(window.location.origin + '/') && window.history.length > 1) { window.history.back(); return false; }"
   title="Retour" aria-label="Retour"
   <?php echo e($attributes->merge(['class' => 'liquid-tool text-white/80 hover:text-white'])); ?>>
    <i data-lucide="arrow-left" class="<?php echo e($iconClass); ?>"></i>
</a>
<?php /**PATH C:\Users\Ranim\Documents\GitHub\Esprit-5TWIN4-DevWave\resources\views/components/back-button.blade.php ENDPATH**/ ?>