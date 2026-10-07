<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'variant' => 'glass', // glass, glass-strong, solid, bordered
    'padding' => 'default', // none, sm, default, lg
    'hover' => false,
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
    'variant' => 'glass', // glass, glass-strong, solid, bordered
    'padding' => 'default', // none, sm, default, lg
    'hover' => false,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $baseClasses = 'rounded-2xl';
    
    $variantClasses = match($variant) {
        'glass' => 'glass',
        'glass-strong' => 'glass-strong',
        'solid' => 'bg-slate-900/90 backdrop-blur-xl',
        'bordered' => 'bg-slate-950/40 border border-cyan-400/15',
        default => 'glass',
    };
    
    $paddingClasses = match($padding) {
        'none' => '',
        'sm' => 'p-4',
        'default' => 'p-6',
        'lg' => 'p-8',
        default => 'p-6',
    };
    
    $hoverClasses = $hover ? 'hover-lift cursor-pointer' : '';
?>

<div <?php echo e($attributes->merge(['class' => trim("$baseClasses $variantClasses $paddingClasses $hoverClasses")])); ?>>
    <?php echo e($slot); ?>

</div>
<?php /**PATH C:\Users\ghada\Desktop\5ème\projet_Laravel\aquasecure\resources\views/components/ui/card.blade.php ENDPATH**/ ?>