<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'variant' => 'primary', // primary, secondary, outline, ghost, danger
    'size' => 'md', // sm, md, lg
    'icon' => null,
    'iconRight' => null,
    'loading' => false,
    'disabled' => false,
    'type' => 'button',
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
    'variant' => 'primary', // primary, secondary, outline, ghost, danger
    'size' => 'md', // sm, md, lg
    'icon' => null,
    'iconRight' => null,
    'loading' => false,
    'disabled' => false,
    'type' => 'button',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $baseClasses = 'inline-flex items-center justify-center gap-2 font-semibold rounded-xl transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-cyan-400/50 focus:ring-offset-2 focus:ring-offset-slate-950';
    
    $variantClasses = match($variant) {
        'primary' => 'bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 text-white shadow-lg shadow-cyan-500/25',
        'secondary' => 'bg-slate-800/50 hover:bg-slate-700/50 text-cyan-100 border border-cyan-400/15 hover:border-cyan-400/30',
        'outline' => 'bg-transparent hover:bg-white/5 text-cyan-400 border border-cyan-400/30 hover:border-cyan-400/50',
        'ghost' => 'bg-transparent hover:bg-white/5 text-cyan-100',
        'danger' => 'bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 border border-rose-400/30 hover:border-rose-400/50',
        default => 'bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 text-white',
    };
    
    $sizeClasses = match($size) {
        'sm' => 'text-xs px-3 py-2',
        'md' => 'text-sm px-5 py-2.5',
        'lg' => 'text-base px-6 py-3',
        default => 'text-sm px-5 py-2.5',
    };
    
    $disabledClasses = ($disabled || $loading) ? 'opacity-50 cursor-not-allowed pointer-events-none' : '';
?>

<button 
    type="<?php echo e($type); ?>"
    <?php echo e($attributes->merge(['class' => trim("$baseClasses $variantClasses $sizeClasses $disabledClasses")])); ?>

    <?php if($disabled || $loading): ?> disabled aria-disabled="true" <?php endif; ?>
    <?php if($loading): ?> aria-busy="true" <?php endif; ?>
>
    <?php if($loading): ?>
        <svg class="animate-spin w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
    <?php elseif($icon): ?>
        <i data-lucide="<?php echo e($icon); ?>" class="w-4 h-4"></i>
    <?php endif; ?>
    
    <span><?php echo e($slot); ?></span>
    
    <?php if($iconRight && !$loading): ?>
        <i data-lucide="<?php echo e($iconRight); ?>" class="w-4 h-4"></i>
    <?php endif; ?>
</button>
<?php /**PATH C:\Users\ghada\Desktop\5ème\projet_Laravel\aquasecure\resources\views/components/ui/button.blade.php ENDPATH**/ ?>