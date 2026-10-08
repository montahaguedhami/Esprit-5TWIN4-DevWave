<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'id' => 'tabs-' . uniqid(),
    'defaultTab' => null,
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
    'id' => 'tabs-' . uniqid(),
    'defaultTab' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div <?php echo e($attributes->merge(['class' => 'tabs-container'])); ?> data-tabs-id="<?php echo e($id); ?>">
    <?php echo e($slot); ?>

</div>

<?php if (! $__env->hasRenderedOnce('b1506e9a-8109-4b12-b1aa-dcfb060ca982')): $__env->markAsRenderedOnce('b1506e9a-8109-4b12-b1aa-dcfb060ca982'); ?>
<?php $__env->startPush('scripts'); ?>
<script>
function switchTab(tabId) {
    const container = document.querySelector(`[data-tabs-id]`);
    if (!container) return;
    
    // Update tab buttons
    const buttons = container.querySelectorAll('.tab-btn');
    buttons.forEach(btn => {
        const isActive = btn.dataset.tab === tabId;
        btn.classList.toggle('border-cyan-400', isActive);
        btn.classList.toggle('text-white', isActive);
        btn.classList.toggle('border-transparent', !isActive);
        btn.classList.toggle('text-cyan-100/50', !isActive);
    });
    
    // Update tab contents
    const contents = container.querySelectorAll('.tab-content');
    contents.forEach(content => {
        content.classList.toggle('hidden', content.id !== `tab-${tabId}`);
    });
}

// Initialize tabs on page load
document.addEventListener('DOMContentLoaded', function() {
    const containers = document.querySelectorAll('.tabs-container');
    containers.forEach(container => {
        const firstBtn = container.querySelector('.tab-btn');
        if (firstBtn && firstBtn.dataset.tab) {
            switchTab(firstBtn.dataset.tab);
        }
    });
});
</script>
<?php $__env->stopPush(); ?>
<?php endif; ?>
<?php /**PATH C:\Users\monta\Downloads\DEVWAVE\Esprit-5TWIN4-DevWave-main (1)\Esprit-5TWIN4-DevWave-main\resources\views\components\ui\tabs.blade.php ENDPATH**/ ?>