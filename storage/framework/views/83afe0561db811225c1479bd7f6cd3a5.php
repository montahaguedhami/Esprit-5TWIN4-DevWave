<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'variant' => 'primary',
    'size' => 'md',
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
    'variant' => 'primary',
    'size' => 'md',
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
    $variants = [
        'primary' => 'bg-gradient-to-r from-cyan-500 to-blue-600 text-white hover:from-cyan-400 hover:to-blue-500 shadow-lg shadow-cyan-500/25',
        'secondary' => 'glass text-cyan-100 hover:text-white border border-cyan-400/30 hover:border-cyan-400/60',
        'ghost' => 'text-cyan-100 hover:bg-white/10',
    ];
    
    $sizes = [
        'sm' => 'px-4 py-2 text-sm',
        'md' => 'px-6 py-3 text-base',
        'lg' => 'px-8 py-4 text-lg',
    ];
    
    $classes = $variants[$variant] . ' ' . $sizes[$size];
?>

<button 
    type="<?php echo e($type); ?>"
    <?php echo e($attributes->merge(['class' => "ripple-btn rounded-xl font-semibold transition-all duration-300 cursor-pointer {$classes}"])); ?>

    onclick="handleRipple(event, this)"
>
    <?php echo e($slot); ?>

</button>

<?php if (! $__env->hasRenderedOnce('2611d8a5-9056-46ca-bf25-b50d5723a40b')): $__env->markAsRenderedOnce('2611d8a5-9056-46ca-bf25-b50d5723a40b'); ?>
<?php $__env->startPush('scripts'); ?>
<script>
    function handleRipple(event, button) {
        const rect = button.getBoundingClientRect();
        const x = event.clientX - rect.left;
        const y = event.clientY - rect.top;

        const circle = document.createElement('span');
        circle.className = 'ripple-circle';
        circle.style.left = `${x - 10}px`;
        circle.style.top = `${y - 10}px`;
        circle.style.width = '20px';
        circle.style.height = '20px';
        button.appendChild(circle);

        setTimeout(() => circle.remove(), 600);
    }
</script>
<?php $__env->stopPush(); ?>
<?php endif; ?>
<?php /**PATH C:\Users\ghada\Desktop\5ème\projet_Laravel\aquasecure\resources\views/components/ripple-button.blade.php ENDPATH**/ ?>