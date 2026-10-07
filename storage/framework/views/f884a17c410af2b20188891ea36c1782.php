<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'icon' => 'activity',
    'title' => '',
    'value' => '0',
    'color' => 'cyan',
    'trend' => null,
    'trendValue' => null,
    'subtitle' => null,
    'loading' => false,
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
    'icon' => 'activity',
    'title' => '',
    'value' => '0',
    'color' => 'cyan',
    'trend' => null,
    'trendValue' => null,
    'subtitle' => null,
    'loading' => false,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php if (isset($component)) { $__componentOriginaldae4cd48acb67888a4631e1ba48f2f93 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.card','data' => ['hover' => true,'class' => 'group']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['hover' => true,'class' => 'group']); ?>
    <?php if($loading): ?>
        <?php if (isset($component)) { $__componentOriginal40dd5c8c22503bc06be1380a5fa9893b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal40dd5c8c22503bc06be1380a5fa9893b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.loading-skeleton','data' => ['type' => 'stat']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.loading-skeleton'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'stat']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal40dd5c8c22503bc06be1380a5fa9893b)): ?>
<?php $attributes = $__attributesOriginal40dd5c8c22503bc06be1380a5fa9893b; ?>
<?php unset($__attributesOriginal40dd5c8c22503bc06be1380a5fa9893b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal40dd5c8c22503bc06be1380a5fa9893b)): ?>
<?php $component = $__componentOriginal40dd5c8c22503bc06be1380a5fa9893b; ?>
<?php unset($__componentOriginal40dd5c8c22503bc06be1380a5fa9893b); ?>
<?php endif; ?>
    <?php else: ?>
    <div class="flex items-start justify-between mb-4">
        <div>
            <div class="flex items-center gap-2 mb-2">
                <div class="w-10 h-10 rounded-xl bg-<?php echo e($color); ?>-500/10 flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                    <i data-lucide="<?php echo e($icon); ?>" class="w-5 h-5 text-<?php echo e($color); ?>-400"></i>
                </div>
                <?php if($trend): ?>
                <div class="flex items-center gap-1 text-xs font-semibold <?php echo e($trend === 'up' ? 'text-emerald-400' : 'text-rose-400'); ?>">
                    <i data-lucide="<?php echo e($trend === 'up' ? 'trending-up' : 'trending-down'); ?>" class="w-3.5 h-3.5"></i>
                    <?php if($trendValue): ?>
                    <span><?php echo e($trendValue); ?></span>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
            <p class="text-xs font-medium text-cyan-100/60 mb-1"><?php echo e($title); ?></p>
            <p class="text-2xl font-display font-bold text-white"><?php echo e($value); ?></p>
            <?php if($subtitle): ?>
            <p class="text-xs text-cyan-100/50 mt-1"><?php echo e($subtitle); ?></p>
            <?php endif; ?>
        </div>
    </div>
    
    <?php if($slot->isNotEmpty()): ?>
    <div class="pt-3 border-t border-white/5">
        <?php echo e($slot); ?>

    </div>
    <?php endif; ?>
    <?php endif; ?>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93)): ?>
<?php $attributes = $__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93; ?>
<?php unset($__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaldae4cd48acb67888a4631e1ba48f2f93)): ?>
<?php $component = $__componentOriginaldae4cd48acb67888a4631e1ba48f2f93; ?>
<?php unset($__componentOriginaldae4cd48acb67888a4631e1ba48f2f93); ?>
<?php endif; ?>
<?php /**PATH C:\Users\ghada\Desktop\5ème\projet_Laravel\aquasecure\resources\views/components/dashboard/kpi-card.blade.php ENDPATH**/ ?>