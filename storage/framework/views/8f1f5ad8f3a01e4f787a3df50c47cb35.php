<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'value' => 0,
    'max' => 100,
    'height' => 120,
    'label' => null,
    'showValue' => true,
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
    'value' => 0,
    'max' => 100,
    'height' => 120,
    'label' => null,
    'showValue' => true,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $percentage = min(($value / $max) * 100, 100);
?>

<div class="flex flex-col gap-2">
    <?php if($label || $showValue): ?>
    <div class="flex justify-between items-center">
        <?php if($label): ?>
        <span class="text-sm text-cyan-100/70"><?php echo e($label); ?></span>
        <?php endif; ?>
        <?php if($showValue): ?>
        <span class="text-sm font-bold text-cyan-300"><?php echo e(round($percentage)); ?>%</span>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    
    <div 
        class="water-rise w-full bg-slate-900/50 border border-cyan-400/20"
        style="height: <?php echo e($height); ?>px;"
    >
        <div class="water-rise-fill" style="height: <?php echo e($percentage); ?>%;"></div>
    </div>
</div>
<?php /**PATH C:\Users\monta\Downloads\DEVWAVE\Esprit-5TWIN4-DevWave-main (1)\Esprit-5TWIN4-DevWave-main\resources\views\components\water-progress.blade.php ENDPATH**/ ?>