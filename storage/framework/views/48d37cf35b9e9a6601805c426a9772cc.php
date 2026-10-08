<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'icon' => 'activity',
    'value' => '0',
    'label' => '',
    'color' => 'cyan',
    'trend' => null, // 'up', 'down', null
    'trendValue' => null,
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
    'value' => '0',
    'label' => '',
    'color' => 'cyan',
    'trend' => null, // 'up', 'down', null
    'trendValue' => null,
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
    <div class="flex items-start justify-between mb-3">
        <div class="w-12 h-12 rounded-xl bg-<?php echo e($color); ?>-500/10 flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
            <i data-lucide="<?php echo e($icon); ?>" class="w-6 h-6 text-<?php echo e($color); ?>-400"></i>
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
    <div class="text-2xl font-display font-bold text-white mb-1"><?php echo e($value); ?></div>
    <div class="text-xs text-cyan-100/60"><?php echo e($label); ?></div>
    <?php if($slot->isNotEmpty()): ?>
    <div class="mt-3 pt-3 border-t border-white/5">
        <?php echo e($slot); ?>

    </div>
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
<?php /**PATH C:\Users\monta\Downloads\DEVWAVE\Esprit-5TWIN4-DevWave-main (1)\Esprit-5TWIN4-DevWave-main\resources\views\components\ui\stat-card.blade.php ENDPATH**/ ?>