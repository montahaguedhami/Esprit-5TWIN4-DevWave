<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'label' => null,
    'name' => '',
    'value' => '',
    'placeholder' => '',
    'required' => false,
    'disabled' => false,
    'error' => null,
    'hint' => null,
    'rows' => 4,
    'maxlength' => null,
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
    'label' => null,
    'name' => '',
    'value' => '',
    'placeholder' => '',
    'required' => false,
    'disabled' => false,
    'error' => null,
    'hint' => null,
    'rows' => 4,
    'maxlength' => null,
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
    
    <textarea
        name="<?php echo e($name); ?>"
        id="<?php echo e($name); ?>"
        rows="<?php echo e($rows); ?>"
        placeholder="<?php echo e($placeholder); ?>"
        <?php if($required): ?> required <?php endif; ?>
        <?php if($disabled): ?> disabled <?php endif; ?>
        <?php if($maxlength): ?> maxlength="<?php echo e($maxlength); ?>" <?php endif; ?>
        class="w-full bg-slate-950/35 border rounded-xl px-4 py-3 text-sm text-white placeholder:text-cyan-100/30 focus:outline-none transition-colors resize-y <?php echo e($error ? 'border-rose-400/50 focus:border-rose-400' : 'border-cyan-400/15 focus:border-cyan-400/50'); ?> <?php echo e($disabled ? 'opacity-50 cursor-not-allowed' : ''); ?>"
    ><?php echo e(old($name, $value)); ?></textarea>
    
    <div class="flex items-center justify-between mt-1.5">
        <div>
            <?php if($error): ?>
            <p class="text-xs text-rose-400 flex items-center gap-1">
                <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i>
                <?php echo e($error); ?>

            </p>
            <?php elseif($hint): ?>
            <p class="text-xs text-cyan-100/50"><?php echo e($hint); ?></p>
            <?php endif; ?>
        </div>
        
        <?php if($maxlength): ?>
        <p class="text-xs text-cyan-100/40">
            <span id="<?php echo e($name); ?>_counter">0</span>/<?php echo e($maxlength); ?>

        </p>
        <?php endif; ?>
    </div>
</div>

<?php if($maxlength): ?>
<?php $__env->startPush('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const textarea = document.getElementById('<?php echo e($name); ?>');
    const counter = document.getElementById('<?php echo e($name); ?>_counter');
    
    if (textarea && counter) {
        const updateCounter = () => {
            counter.textContent = textarea.value.length;
        };
        
        textarea.addEventListener('input', updateCounter);
        updateCounter();
    }
});
</script>
<?php $__env->stopPush(); ?>
<?php endif; ?>
<?php /**PATH C:\Users\ghada\Desktop\5ème\projet_Laravel\aquasecure\resources\views/components/forms/textarea.blade.php ENDPATH**/ ?>