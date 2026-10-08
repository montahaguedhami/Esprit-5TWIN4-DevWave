
<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['intervention']));

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

foreach (array_filter((['intervention']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<a href="<?php echo e(route('citizen.travaux.show', $intervention)); ?>" <?php echo e($attributes->merge(['class' => 'block glass rounded-2xl p-5 hover-lift transition-all group'])); ?>>
    <div class="flex items-start justify-between gap-3 mb-3">
        <div class="flex items-center gap-3">
            <div class="w-11 h-11 rounded-xl bg-cyan-500/10 border border-cyan-400/20 flex flex-col items-center justify-center shrink-0">
                <span class="text-sm font-bold text-white leading-none"><?php echo e($intervention->date->format('d')); ?></span>
                <span class="text-[10px] uppercase text-cyan-300 leading-none mt-0.5"><?php echo e($intervention->date->locale('fr')->translatedFormat('M')); ?></span>
            </div>
            <div>
                <p class="text-xs text-cyan-100/50">Équipe</p>
                <p class="text-sm font-semibold text-white"><?php echo e($intervention->technicien->specialite); ?></p>
            </div>
        </div>
        <?php if (isset($component)) { $__componentOriginal613a257712d79f8faaf5703f21bda327 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal613a257712d79f8faaf5703f21bda327 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.maintenance.badge','data' => ['value' => $intervention->statut]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('maintenance.badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($intervention->statut)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal613a257712d79f8faaf5703f21bda327)): ?>
<?php $attributes = $__attributesOriginal613a257712d79f8faaf5703f21bda327; ?>
<?php unset($__attributesOriginal613a257712d79f8faaf5703f21bda327); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal613a257712d79f8faaf5703f21bda327)): ?>
<?php $component = $__componentOriginal613a257712d79f8faaf5703f21bda327; ?>
<?php unset($__componentOriginal613a257712d79f8faaf5703f21bda327); ?>
<?php endif; ?>
    </div>

    <p class="text-sm text-cyan-100/80 leading-relaxed"><?php echo e(Str::limit($intervention->description, 110)); ?></p>

    <p class="text-xs text-cyan-300 mt-4 flex items-center gap-1 opacity-70 group-hover:opacity-100 transition-opacity">
        Voir le détail <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
    </p>
</a>
<?php /**PATH C:\Users\monta\Downloads\DEVWAVE\Esprit-5TWIN4-DevWave-main (1)\Esprit-5TWIN4-DevWave-main\resources\views\components\maintenance\travail-card.blade.php ENDPATH**/ ?>