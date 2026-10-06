<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'type' => 'info', // success, error, warning, info
    'title' => null,
    'dismissible' => false,
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
    'type' => 'info', // success, error, warning, info
    'title' => null,
    'dismissible' => false,
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
    $config = [
        'success' => [
            'bg' => 'bg-emerald-500/10',
            'border' => 'border-emerald-400/30',
            'text' => 'text-emerald-400',
            'icon' => $icon ?? 'check-circle',
        ],
        'error' => [
            'bg' => 'bg-rose-500/10',
            'border' => 'border-rose-400/30',
            'text' => 'text-rose-400',
            'icon' => $icon ?? 'alert-circle',
        ],
        'warning' => [
            'bg' => 'bg-amber-500/10',
            'border' => 'border-amber-400/30',
            'text' => 'text-amber-400',
            'icon' => $icon ?? 'alert-triangle',
        ],
        'info' => [
            'bg' => 'bg-cyan-500/10',
            'border' => 'border-cyan-400/30',
            'text' => 'text-cyan-400',
            'icon' => $icon ?? 'info',
        ],
    ];
    
    $cfg = $config[$type] ?? $config['info'];
?>

<div <?php echo e($attributes->merge(['class' => "flex items-start gap-3 p-4 rounded-xl border {$cfg['bg']} {$cfg['border']}"])); ?>>
    <i data-lucide="<?php echo e($cfg['icon']); ?>" class="w-5 h-5 <?php echo e($cfg['text']); ?> flex-shrink-0 mt-0.5"></i>
    
    <div class="flex-1 min-w-0">
        <?php if($title): ?>
        <h4 class="font-semibold <?php echo e($cfg['text']); ?> mb-1"><?php echo e($title); ?></h4>
        <?php endif; ?>
        <div class="text-sm text-cyan-100/80">
            <?php echo e($slot); ?>

        </div>
    </div>
    
    <?php if($dismissible): ?>
    <button 
        onclick="this.closest('[class*=bg-]').remove()"
        class="w-6 h-6 rounded-lg flex items-center justify-center hover:bg-white/5 transition-colors flex-shrink-0"
        aria-label="Fermer"
    >
        <i data-lucide="x" class="w-4 h-4 text-cyan-100/60"></i>
    </button>
    <?php endif; ?>
</div>
<?php /**PATH C:\Users\ghada\Desktop\5ème\projet_Laravel\aquasecure\resources\views/components/ui/alert.blade.php ENDPATH**/ ?>