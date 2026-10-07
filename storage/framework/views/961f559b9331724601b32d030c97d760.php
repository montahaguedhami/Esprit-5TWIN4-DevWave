<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'icon' => 'inbox',
    'title' => 'Aucune donnée',
    'description' => '',
    'action' => null,
    'actionLabel' => null,
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
    'icon' => 'inbox',
    'title' => 'Aucune donnée',
    'description' => '',
    'action' => null,
    'actionLabel' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div <?php echo e($attributes->merge(['class' => 'flex flex-col items-center justify-center py-12 px-4 text-center'])); ?>>
    <div class="w-20 h-20 rounded-2xl bg-cyan-500/5 border border-cyan-400/10 flex items-center justify-center mb-5">
        <i data-lucide="<?php echo e($icon); ?>" class="w-10 h-10 text-cyan-400/40"></i>
    </div>
    
    <h3 class="text-lg font-display font-semibold text-white mb-2"><?php echo e($title); ?></h3>
    
    <?php if($description): ?>
    <p class="text-sm text-cyan-100/50 max-w-md mb-6"><?php echo e($description); ?></p>
    <?php endif; ?>
    
    <?php if($slot->isNotEmpty()): ?>
    <div class="mt-4">
        <?php echo e($slot); ?>

    </div>
    <?php elseif($action && $actionLabel): ?>
    <button 
        onclick="<?php echo e($action); ?>"
        class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 text-white text-sm font-semibold rounded-xl transition-all duration-300 shadow-lg shadow-cyan-500/25"
    >
        <i data-lucide="plus" class="w-4 h-4"></i>
        <span><?php echo e($actionLabel); ?></span>
    </button>
    <?php endif; ?>
</div>
<?php /**PATH C:\Users\Ranim\Documents\GitHub\Esprit-5TWIN4-DevWave\resources\views/components/ui/empty-state.blade.php ENDPATH**/ ?>