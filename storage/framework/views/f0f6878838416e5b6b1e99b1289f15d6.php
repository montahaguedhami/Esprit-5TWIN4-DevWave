<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'status' => 'normal', // normal, alert, critical, success, warning, danger, info, pending
    'size' => 'md', // sm, md, lg
    'dot' => false,
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
    'status' => 'normal', // normal, alert, critical, success, warning, danger, info, pending
    'size' => 'md', // sm, md, lg
    'dot' => false,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $statusConfig = [
        'normal' => ['bg' => 'bg-emerald-500/10', 'border' => 'border-emerald-400/30', 'text' => 'text-emerald-400', 'label' => 'Normal'],
        'alert' => ['bg' => 'bg-amber-500/10', 'border' => 'border-amber-400/30', 'text' => 'text-amber-400', 'label' => 'Alerte'],
        'critical' => ['bg' => 'bg-rose-500/10', 'border' => 'border-rose-400/30', 'text' => 'text-rose-400', 'label' => 'Critique'],
        'success' => ['bg' => 'bg-teal-500/10', 'border' => 'border-teal-400/30', 'text' => 'text-teal-400', 'label' => 'Succès'],
        'warning' => ['bg' => 'bg-orange-500/10', 'border' => 'border-orange-400/30', 'text' => 'text-orange-400', 'label' => 'Attention'],
        'danger' => ['bg' => 'bg-red-500/10', 'border' => 'border-red-400/30', 'text' => 'text-red-400', 'label' => 'Danger'],
        'info' => ['bg' => 'bg-cyan-500/10', 'border' => 'border-cyan-400/30', 'text' => 'text-cyan-400', 'label' => 'Info'],
        'pending' => ['bg' => 'bg-slate-500/10', 'border' => 'border-slate-400/30', 'text' => 'text-slate-400', 'label' => 'En attente'],
    ];
    
    $config = $statusConfig[$status] ?? $statusConfig['info'];
    
    $sizeClasses = match($size) {
        'sm' => 'text-xs px-2 py-0.5',
        'md' => 'text-xs px-3 py-1',
        'lg' => 'text-sm px-4 py-1.5',
        default => 'text-xs px-3 py-1',
    };
?>

<span <?php echo e($attributes->merge(['class' => "inline-flex items-center gap-1.5 rounded-full font-semibold border {$config['bg']} {$config['border']} {$config['text']} {$sizeClasses}"])); ?>>
    <?php if($dot): ?>
    <span class="w-1.5 h-1.5 rounded-full <?php echo e(str_replace('/10', '', $config['bg'])); ?> animate-pulse"></span>
    <?php endif; ?>
    <?php echo e($slot->isNotEmpty() ? $slot : $config['label']); ?>

</span>
<?php /**PATH C:\Users\ghada\Desktop\5ème\projet_Laravel\aquasecure\resources\views/components/ui/status-badge.blade.php ENDPATH**/ ?>