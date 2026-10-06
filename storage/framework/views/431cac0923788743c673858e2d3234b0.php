<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'currentPage' => 1,
    'totalPages' => 1,
    'perPage' => 10,
    'total' => 0,
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
    'currentPage' => 1,
    'totalPages' => 1,
    'perPage' => 10,
    'total' => 0,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $showFirst = $currentPage > 2;
    $showLast = $currentPage < $totalPages - 1;
    $pages = [];
    
    // Generate page numbers to display
    for ($i = max(1, $currentPage - 1); $i <= min($totalPages, $currentPage + 1); $i++) {
        $pages[] = $i;
    }
    
    $from = ($currentPage - 1) * $perPage + 1;
    $to = min($currentPage * $perPage, $total);
?>

<?php if($totalPages > 1): ?>
<div <?php echo e($attributes->merge(['class' => 'flex items-center justify-between'])); ?>>
    <!-- Info -->
    <div class="text-sm text-cyan-100/60">
        Affichage de <span class="font-semibold text-white"><?php echo e($from); ?></span> à 
        <span class="font-semibold text-white"><?php echo e($to); ?></span> sur 
        <span class="font-semibold text-white"><?php echo e($total); ?></span> résultats
    </div>
    
    <!-- Pagination -->
    <nav class="flex items-center gap-1">
        <!-- Previous -->
        <button 
            <?php if($currentPage <= 1): ?> disabled <?php endif; ?>
            onclick="window.location.href='?page=<?php echo e($currentPage - 1); ?>'"
            class="w-9 h-9 rounded-lg flex items-center justify-center transition-colors <?php echo e($currentPage <= 1 ? 'opacity-30 cursor-not-allowed' : 'hover:bg-white/5'); ?>"
        >
            <i data-lucide="chevron-left" class="w-4 h-4 text-cyan-100/60"></i>
        </button>
        
        <!-- First page -->
        <?php if($showFirst): ?>
        <button 
            onclick="window.location.href='?page=1'"
            class="w-9 h-9 rounded-lg flex items-center justify-center text-sm font-semibold text-cyan-100/60 hover:bg-white/5 transition-colors"
        >
            1
        </button>
        <span class="text-cyan-100/30">...</span>
        <?php endif; ?>
        
        <!-- Page numbers -->
        <?php $__currentLoopData = $pages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <button 
            onclick="window.location.href='?page=<?php echo e($page); ?>'"
            class="w-9 h-9 rounded-lg flex items-center justify-center text-sm font-semibold transition-colors <?php echo e($page === $currentPage ? 'bg-gradient-to-r from-cyan-500 to-blue-600 text-white' : 'text-cyan-100/60 hover:bg-white/5'); ?>"
        >
            <?php echo e($page); ?>

        </button>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        
        <!-- Last page -->
        <?php if($showLast): ?>
        <span class="text-cyan-100/30">...</span>
        <button 
            onclick="window.location.href='?page=<?php echo e($totalPages); ?>'"
            class="w-9 h-9 rounded-lg flex items-center justify-center text-sm font-semibold text-cyan-100/60 hover:bg-white/5 transition-colors"
        >
            <?php echo e($totalPages); ?>

        </button>
        <?php endif; ?>
        
        <!-- Next -->
        <button 
            <?php if($currentPage >= $totalPages): ?> disabled <?php endif; ?>
            onclick="window.location.href='?page=<?php echo e($currentPage + 1); ?>'"
            class="w-9 h-9 rounded-lg flex items-center justify-center transition-colors <?php echo e($currentPage >= $totalPages ? 'opacity-30 cursor-not-allowed' : 'hover:bg-white/5'); ?>"
        >
            <i data-lucide="chevron-right" class="w-4 h-4 text-cyan-100/60"></i>
        </button>
    </nav>
</div>
<?php endif; ?>
<?php /**PATH C:\Users\ghada\Desktop\5ème\projet_Laravel\aquasecure\resources\views/components/ui/pagination.blade.php ENDPATH**/ ?>