<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'id' => 'modal',
    'title' => '',
    'size' => 'md', // sm, md, lg, xl, full
    'closeButton' => true,
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
    'id' => 'modal',
    'title' => '',
    'size' => 'md', // sm, md, lg, xl, full
    'closeButton' => true,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $sizeClasses = match($size) {
        'sm' => 'max-w-md',
        'md' => 'max-w-lg',
        'lg' => 'max-w-2xl',
        'xl' => 'max-w-4xl',
        'full' => 'max-w-7xl',
        default => 'max-w-lg',
    };
?>

<div 
    id="<?php echo e($id); ?>"
    class="modal-overlay fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm hidden"
    onclick="if(event.target === this) closeModal('<?php echo e($id); ?>')"
    role="dialog"
    aria-modal="true"
    aria-labelledby="<?php echo e($id); ?>-title"
    aria-hidden="true"
>
    <div 
        class="modal-content glass-strong rounded-2xl shadow-2xl w-full <?php echo e($sizeClasses); ?> max-h-[90vh] overflow-hidden flex flex-col animate-scale-in"
        onclick="event.stopPropagation()"
    >
        <!-- Header -->
        <?php if($title || $closeButton): ?>
        <div class="flex items-center justify-between p-6 border-b border-white/5">
            <h3 id="<?php echo e($id); ?>-title" class="text-xl font-display font-bold text-white"><?php echo e($title); ?></h3>
            <?php if($closeButton): ?>
            <button 
                onclick="closeModal('<?php echo e($id); ?>')"
                class="w-8 h-8 rounded-lg flex items-center justify-center hover:bg-white/5 transition-colors"
                aria-label="Fermer le modal"
                type="button"
            >
                <i data-lucide="x" class="w-5 h-5 text-cyan-100/60"></i>
            </button>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Body -->
        <div class="flex-1 overflow-y-auto p-6">
            <?php echo e($slot); ?>

        </div>

        <!-- Footer (optional) -->
        <?php if(isset($footer)): ?>
        <div class="p-6 border-t border-white/5 flex items-center justify-end gap-3">
            <?php echo e($footer); ?>

        </div>
        <?php endif; ?>
    </div>
</div>

<?php if (! $__env->hasRenderedOnce('23a4f5a9-1ea0-460d-a369-5588c9c970cb')): $__env->markAsRenderedOnce('23a4f5a9-1ea0-460d-a369-5588c9c970cb'); ?>
<?php $__env->startPush('scripts'); ?>
<script>
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('hidden');
        modal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
        // Focus trap
        const focusableElements = modal.querySelectorAll('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
        if (focusableElements.length > 0) {
            focusableElements[0].focus();
        }
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('hidden');
        modal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
    }
}

// Close on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const openModals = document.querySelectorAll('.modal-overlay:not(.hidden)');
        openModals.forEach(modal => {
            modal.classList.add('hidden');
            document.body.style.overflow = '';
        });
    }
});
</script>
<?php $__env->stopPush(); ?>
<?php endif; ?>
<?php /**PATH C:\Users\monta\Downloads\DEVWAVE\Esprit-5TWIN4-DevWave-main (1)\Esprit-5TWIN4-DevWave-main\resources\views\components\ui\modal.blade.php ENDPATH**/ ?>