<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'src' => null,
    'name' => 'User',
    'size' => 'md', // xs, sm, md, lg, xl
    'status' => null, // online, offline, busy, away
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
    'src' => null,
    'name' => 'User',
    'size' => 'md', // xs, sm, md, lg, xl
    'status' => null, // online, offline, busy, away
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
        'xs' => 'w-6 h-6 text-xs',
        'sm' => 'w-8 h-8 text-sm',
        'md' => 'w-10 h-10 text-base',
        'lg' => 'w-12 h-12 text-lg',
        'xl' => 'w-16 h-16 text-2xl',
        default => 'w-10 h-10 text-base',
    };
    
    $statusColors = [
        'online' => 'bg-emerald-400',
        'offline' => 'bg-slate-400',
        'busy' => 'bg-rose-400',
        'away' => 'bg-amber-400',
    ];
    
    $initials = collect(explode(' ', $name))
        ->take(2)
        ->map(fn($word) => strtoupper(substr($word, 0, 1)))
        ->join('');
?>

<div <?php echo e($attributes->merge(['class' => "relative inline-block flex-shrink-0"])); ?>>
    <?php if($src): ?>
        <img 
            src="<?php echo e($src); ?>" 
            alt="<?php echo e($name); ?>"
            class="<?php echo e($sizeClasses); ?> rounded-full object-cover border-2 border-cyan-400/20"
        />
    <?php else: ?>
        <div class="<?php echo e($sizeClasses); ?> rounded-full bg-gradient-to-br from-cyan-500 to-blue-600 flex items-center justify-center text-white font-semibold border-2 border-cyan-400/20">
            <?php echo e($initials); ?>

        </div>
    <?php endif; ?>
    
    <?php if($status): ?>
        <?php
            $statusSize = match($size) {
                'xs' => 'w-1.5 h-1.5',
                'sm' => 'w-2 h-2',
                'md' => 'w-2.5 h-2.5',
                'lg' => 'w-3 h-3',
                'xl' => 'w-4 h-4',
                default => 'w-2.5 h-2.5',
            };
        ?>
        <span class="absolute bottom-0 right-0 <?php echo e($statusSize); ?> <?php echo e($statusColors[$status] ?? 'bg-slate-400'); ?> rounded-full border-2 border-slate-950"></span>
    <?php endif; ?>
</div>
<?php /**PATH C:\Users\ghada\Desktop\5ème\projet_Laravel\aquasecure\resources\views/components/ui/avatar.blade.php ENDPATH**/ ?>