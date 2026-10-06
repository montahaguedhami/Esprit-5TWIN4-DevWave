<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'type' => 'text',
    'label' => null,
    'name' => '',
    'value' => '',
    'placeholder' => '',
    'required' => false,
    'disabled' => false,
    'error' => null,
    'icon' => null,
    'hint' => null,
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
    'type' => 'text',
    'label' => null,
    'name' => '',
    'value' => '',
    'placeholder' => '',
    'required' => false,
    'disabled' => false,
    'error' => null,
    'icon' => null,
    'hint' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div <?php echo e($attributes->merge(['class' => 'w-full'])); ?>>
    <?php if($label): ?>
    <label for="<?php echo e($name); ?>" class="text-xs font-semibold text-cyan-100/70 mb-2 block">
        <?php echo e($label); ?>

        <?php if($required): ?>
        <span class="text-rose-400">*</span>
        <?php endif; ?>
    </label>
    <?php endif; ?>
    
    <div class="relative">
        <?php if($icon): ?>
        <div class="absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none">
            <i data-lucide="<?php echo e($icon); ?>" class="w-5 h-5 text-cyan-100/40"></i>
        </div>
        <?php endif; ?>
        
        <input
            type="<?php echo e($type); ?>"
            name="<?php echo e($name); ?>"
            id="<?php echo e($name); ?>"
            value="<?php echo e(old($name, $value)); ?>"
            placeholder="<?php echo e($placeholder); ?>"
            <?php if($required): ?> required aria-required="true" <?php endif; ?>
            <?php if($disabled): ?> disabled <?php endif; ?>
            <?php if($error): ?> aria-invalid="true" aria-describedby="<?php echo e($name); ?>-error" <?php endif; ?>
            <?php if($hint && !$error): ?> aria-describedby="<?php echo e($name); ?>-hint" <?php endif; ?>
            class="w-full bg-slate-950/35 border rounded-xl px-4 py-3 text-sm text-white placeholder:text-cyan-100/30 focus:outline-none transition-colors <?php echo e($icon ? 'pl-11' : ''); ?> <?php echo e($error ? 'border-rose-400/50 focus:border-rose-400' : 'border-cyan-400/15 focus:border-cyan-400/50'); ?> <?php echo e($disabled ? 'opacity-50 cursor-not-allowed' : ''); ?>"
        />
    </div>
    
    <?php if($error): ?>
    <p id="<?php echo e($name); ?>-error" class="text-xs text-rose-400 mt-1.5 flex items-center gap-1" role="alert">
        <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i>
        <?php echo e($error); ?>

    </p>
    <?php elseif($hint): ?>
    <p id="<?php echo e($name); ?>-hint" class="text-xs text-cyan-100/50 mt-1.5"><?php echo e($hint); ?></p>
    <?php endif; ?>
</div>
<?php /**PATH C:\Users\ghada\Desktop\5ème\projet_Laravel\aquasecure\resources\views/components/forms/input.blade.php ENDPATH**/ ?>