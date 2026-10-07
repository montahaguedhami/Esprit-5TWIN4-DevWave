<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'id' => 'dropdown-' . uniqid(),
    'align' => 'right', // left, right, center
    'width' => 'md', // sm, md, lg
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
    'id' => 'dropdown-' . uniqid(),
    'align' => 'right', // left, right, center
    'width' => 'md', // sm, md, lg
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $alignClasses = match($align) {
        'left' => 'left-0',
        'right' => 'right-0',
        'center' => 'left-1/2 -translate-x-1/2',
        default => 'right-0',
    };
    
    $widthClasses = match($width) {
        'sm' => 'w-48',
        'md' => 'w-56',
        'lg' => 'w-72',
        default => 'w-56',
    };
?>

<div class="relative" x-data="{ open: false }" @click.away="open = false">
    <!-- Trigger -->
    <div @click="open = !open" class="cursor-pointer">
        <?php echo e($trigger); ?>

    </div>
    
    <!-- Dropdown Menu -->
    <div 
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="absolute <?php echo e($alignClasses); ?> <?php echo e($widthClasses); ?> mt-2 z-50 glass-strong rounded-xl shadow-2xl border border-cyan-400/10 py-2 hidden"
        :class="{ 'hidden': !open, 'block': open }"
        style="display: none;"
    >
        <?php echo e($slot); ?>

    </div>
</div>

<?php if (! $__env->hasRenderedOnce('36a5e31e-9350-4151-a3fc-a96b3c186194')): $__env->markAsRenderedOnce('36a5e31e-9350-4151-a3fc-a96b3c186194'); ?>
<?php $__env->startPush('scripts'); ?>
<script>
// Simple Alpine.js-like behavior without Alpine
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('[x-data]').forEach(el => {
        const trigger = el.querySelector('[\\@click]');
        const dropdown = el.querySelector('[x-show]');
        
        if (trigger && dropdown) {
            trigger.addEventListener('click', function(e) {
                e.stopPropagation();
                const isHidden = dropdown.style.display === 'none' || dropdown.style.display === '';
                
                // Close all other dropdowns
                document.querySelectorAll('[x-show]').forEach(d => {
                    if (d !== dropdown) d.style.display = 'none';
                });
                
                dropdown.style.display = isHidden ? 'block' : 'none';
            });
            
            // Click outside to close
            document.addEventListener('click', function(e) {
                if (!el.contains(e.target)) {
                    dropdown.style.display = 'none';
                }
            });
        }
    });
});
</script>
<?php $__env->stopPush(); ?>
<?php endif; ?>
<?php /**PATH C:\Users\ghada\Desktop\5ème\projet_Laravel\aquasecure\resources\views/components/ui/dropdown.blade.php ENDPATH**/ ?>