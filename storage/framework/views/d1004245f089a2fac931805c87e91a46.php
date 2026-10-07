<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'count' => 40,
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
    'count' => 40,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div class="rain-container">
    <?php for($i = 0; $i < $count; $i++): ?>
        <?php
            $left = rand(0, 100);
            $height = rand(20, 60);
            $duration = (rand(60, 140) / 100);
            $delay = (rand(0, 300) / 100);
            $opacity = (rand(30, 70) / 100);
        ?>
        <div 
            class="raindrop" 
            style="left: <?php echo e($left); ?>%; height: <?php echo e($height); ?>px; animation-duration: <?php echo e($duration); ?>s; animation-delay: <?php echo e($delay); ?>s; opacity: <?php echo e($opacity); ?>;"
        ></div>
    <?php endfor; ?>
</div>
<?php /**PATH C:\Users\ghada\Desktop\5ème\projet_Laravel\aquasecure\resources\views/components/rain-effect.blade.php ENDPATH**/ ?>