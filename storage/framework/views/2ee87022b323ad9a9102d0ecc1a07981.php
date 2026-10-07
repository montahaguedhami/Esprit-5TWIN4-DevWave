<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['route', 'icon', 'label', 'badge' => null]));

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

foreach (array_filter((['route', 'icon', 'label', 'badge' => null]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    try {
        $isActive = request()->routeIs($route) || str_starts_with(request()->path(), str_replace('.', '/', $route));
    } catch (\Exception $e) {
        $isActive = false;
    }
?>

<a href="<?php echo e(route($route)); ?>"
   class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 group
          <?php echo e($isActive
             ? 'bg-cyan-500/10 text-cyan-300 border border-cyan-400/20'
             : 'text-cyan-100/55 hover:text-cyan-100/90 hover:bg-white/[.04]'); ?>"
   aria-current="<?php echo e($isActive ? 'page' : 'false'); ?>">

    <i data-lucide="<?php echo e($icon); ?>"
       class="w-4 h-4 shrink-0 transition-colors
              <?php echo e($isActive ? 'text-cyan-400' : 'text-cyan-100/40 group-hover:text-cyan-300'); ?>"></i>

    <span class="flex-1 truncate"><?php echo e($label); ?></span>

    <?php if($badge): ?>
    <span class="ml-auto px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-red-500/20 text-red-300">
        <?php echo e($badge); ?>

    </span>
    <?php endif; ?>
</a>
<?php /**PATH C:\Users\ghada\Desktop\5ème\projet_Laravel\aquasecure\resources\views/components/admin-nav-item.blade.php ENDPATH**/ ?>