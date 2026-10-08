<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'type' => 'card', // card, stat, table, list, text, avatar, chart
    'count' => 1,
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
    'type' => 'card', // card, stat, table, list, text, avatar, chart
    'count' => 1,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $renderSkeleton = function($type) {
        return match($type) {
            'card' => '<div class="glass rounded-2xl p-6 animate-pulse">
                <div class="flex items-start justify-between mb-4">
                    <div class="w-12 h-12 bg-cyan-500/10 rounded-xl"></div>
                    <div class="w-16 h-6 bg-cyan-500/10 rounded"></div>
                </div>
                <div class="space-y-3">
                    <div class="w-3/4 h-4 bg-cyan-500/10 rounded"></div>
                    <div class="w-1/2 h-3 bg-cyan-500/10 rounded"></div>
                </div>
            </div>',
            
            'stat' => '<div class="glass rounded-2xl p-5 animate-pulse">
                <div class="flex items-start justify-between mb-3">
                    <div class="w-12 h-12 bg-cyan-500/10 rounded-xl"></div>
                </div>
                <div class="w-20 h-8 bg-cyan-500/10 rounded mb-2"></div>
                <div class="w-32 h-3 bg-cyan-500/10 rounded"></div>
            </div>',
            
            'table' => '<div class="space-y-3 animate-pulse">
                <div class="flex gap-4">
                    <div class="w-12 h-12 bg-cyan-500/10 rounded-lg"></div>
                    <div class="flex-1 space-y-2">
                        <div class="w-1/3 h-4 bg-cyan-500/10 rounded"></div>
                        <div class="w-1/2 h-3 bg-cyan-500/10 rounded"></div>
                    </div>
                    <div class="w-24 h-8 bg-cyan-500/10 rounded-lg"></div>
                </div>
            </div>',
            
            'list' => '<div class="flex items-center gap-4 p-4 glass rounded-xl animate-pulse">
                <div class="w-10 h-10 bg-cyan-500/10 rounded-full"></div>
                <div class="flex-1 space-y-2">
                    <div class="w-3/4 h-4 bg-cyan-500/10 rounded"></div>
                    <div class="w-1/2 h-3 bg-cyan-500/10 rounded"></div>
                </div>
            </div>',
            
            'text' => '<div class="space-y-2 animate-pulse">
                <div class="w-full h-4 bg-cyan-500/10 rounded"></div>
                <div class="w-5/6 h-4 bg-cyan-500/10 rounded"></div>
                <div class="w-4/6 h-4 bg-cyan-500/10 rounded"></div>
            </div>',
            
            'avatar' => '<div class="w-10 h-10 bg-cyan-500/10 rounded-full animate-pulse"></div>',
            
            'chart' => '<div class="glass rounded-2xl p-6 animate-pulse">
                <div class="w-1/3 h-6 bg-cyan-500/10 rounded mb-6"></div>
                <div class="flex items-end justify-between h-48 gap-3">
                    <div class="w-full bg-cyan-500/10 rounded-t" style="height: 70%"></div>
                    <div class="w-full bg-cyan-500/10 rounded-t" style="height: 85%"></div>
                    <div class="w-full bg-cyan-500/10 rounded-t" style="height: 60%"></div>
                    <div class="w-full bg-cyan-500/10 rounded-t" style="height: 90%"></div>
                    <div class="w-full bg-cyan-500/10 rounded-t" style="height: 75%"></div>
                </div>
            </div>',
            
            default => '<div class="w-full h-20 bg-cyan-500/10 rounded-xl animate-pulse"></div>',
        };
    };
?>

<div <?php echo e($attributes->merge(['class' => ''])); ?>>
    <?php for($i = 0; $i < $count; $i++): ?>
        <?php echo $renderSkeleton($type); ?>

    <?php endfor; ?>
</div>

<?php if (! $__env->hasRenderedOnce('bbe161e5-f609-4926-b66f-8ae366d14c2a')): $__env->markAsRenderedOnce('bbe161e5-f609-4926-b66f-8ae366d14c2a'); ?>
<?php $__env->startPush('styles'); ?>
<style>
@keyframes shimmer {
    0% { opacity: 0.4; }
    50% { opacity: 0.6; }
    100% { opacity: 0.4; }
}
.animate-pulse {
    animation: shimmer 2s ease-in-out infinite;
}
</style>
<?php $__env->stopPush(); ?>
<?php endif; ?>
<?php /**PATH C:\Users\monta\Downloads\DEVWAVE\Esprit-5TWIN4-DevWave-main (1)\Esprit-5TWIN4-DevWave-main\resources\views\components\ui\loading-skeleton.blade.php ENDPATH**/ ?>