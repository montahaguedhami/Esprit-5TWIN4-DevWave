<?php $__env->startSection('title', $technicien->nom.' — AquaSecure'); ?>

<?php $__env->startSection('manager-content'); ?>
<div class="max-w-6xl mx-auto">
    <?php if (isset($component)) { $__componentOriginalf2de2158a73822dbd9074b03366e73f1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf2de2158a73822dbd9074b03366e73f1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.maintenance.page-header','data' => ['title' => $technicien->nom,'subtitle' => $technicien->specialite,'icon' => 'hard-hat']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('maintenance.page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($technicien->nom),'subtitle' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($technicien->specialite),'icon' => 'hard-hat']); ?>
        <?php if (isset($component)) { $__componentOriginal315d03b0d0695345e2e870d0abb4e102 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal315d03b0d0695345e2e870d0abb4e102 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.maintenance.link-button','data' => ['href' => route('manager.techniciens.index'),'variant' => 'secondary','icon' => 'arrow-left']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('maintenance.link-button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('manager.techniciens.index')),'variant' => 'secondary','icon' => 'arrow-left']); ?>Retour <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal315d03b0d0695345e2e870d0abb4e102)): ?>
<?php $attributes = $__attributesOriginal315d03b0d0695345e2e870d0abb4e102; ?>
<?php unset($__attributesOriginal315d03b0d0695345e2e870d0abb4e102); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal315d03b0d0695345e2e870d0abb4e102)): ?>
<?php $component = $__componentOriginal315d03b0d0695345e2e870d0abb4e102; ?>
<?php unset($__componentOriginal315d03b0d0695345e2e870d0abb4e102); ?>
<?php endif; ?>
        <?php if (isset($component)) { $__componentOriginal315d03b0d0695345e2e870d0abb4e102 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal315d03b0d0695345e2e870d0abb4e102 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.maintenance.link-button','data' => ['href' => route('manager.techniciens.edit', $technicien),'variant' => 'secondary','icon' => 'pencil']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('maintenance.link-button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('manager.techniciens.edit', $technicien)),'variant' => 'secondary','icon' => 'pencil']); ?>Modifier <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal315d03b0d0695345e2e870d0abb4e102)): ?>
<?php $attributes = $__attributesOriginal315d03b0d0695345e2e870d0abb4e102; ?>
<?php unset($__attributesOriginal315d03b0d0695345e2e870d0abb4e102); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal315d03b0d0695345e2e870d0abb4e102)): ?>
<?php $component = $__componentOriginal315d03b0d0695345e2e870d0abb4e102; ?>
<?php unset($__componentOriginal315d03b0d0695345e2e870d0abb4e102); ?>
<?php endif; ?>
        <?php if (isset($component)) { $__componentOriginal00677f0e8dccc4dc790f25dd98652820 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal00677f0e8dccc4dc790f25dd98652820 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.maintenance.delete-button','data' => ['action' => route('manager.techniciens.destroy', $technicien),'confirm' => 'Supprimer ce technicien ? Ses interventions seront aussi supprimées.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('maintenance.delete-button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['action' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('manager.techniciens.destroy', $technicien)),'confirm' => 'Supprimer ce technicien ? Ses interventions seront aussi supprimées.']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal00677f0e8dccc4dc790f25dd98652820)): ?>
<?php $attributes = $__attributesOriginal00677f0e8dccc4dc790f25dd98652820; ?>
<?php unset($__attributesOriginal00677f0e8dccc4dc790f25dd98652820); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal00677f0e8dccc4dc790f25dd98652820)): ?>
<?php $component = $__componentOriginal00677f0e8dccc4dc790f25dd98652820; ?>
<?php unset($__componentOriginal00677f0e8dccc4dc790f25dd98652820); ?>
<?php endif; ?>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf2de2158a73822dbd9074b03366e73f1)): ?>
<?php $attributes = $__attributesOriginalf2de2158a73822dbd9074b03366e73f1; ?>
<?php unset($__attributesOriginalf2de2158a73822dbd9074b03366e73f1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf2de2158a73822dbd9074b03366e73f1)): ?>
<?php $component = $__componentOriginalf2de2158a73822dbd9074b03366e73f1; ?>
<?php unset($__componentOriginalf2de2158a73822dbd9074b03366e73f1); ?>
<?php endif; ?>

    <?php echo $__env->make('manager.maintenance._flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    
    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <?php if (isset($component)) { $__componentOriginaldae4cd48acb67888a4631e1ba48f2f93 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.card','data' => ['padding' => 'sm']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['padding' => 'sm']); ?>
            <p class="text-xs font-semibold text-cyan-100/50 mb-2">Disponibilité</p>
            <?php if (isset($component)) { $__componentOriginal613a257712d79f8faaf5703f21bda327 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal613a257712d79f8faaf5703f21bda327 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.maintenance.badge','data' => ['value' => $technicien->disponibilite]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('maintenance.badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($technicien->disponibilite)]); ?>
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
            <p class="text-sm text-cyan-100/80 mt-3 flex items-center gap-2"><i data-lucide="phone" class="w-4 h-4 text-cyan-300"></i><?php echo e($technicien->telephone); ?></p>
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
        <?php if (isset($component)) { $__componentOriginaldae4cd48acb67888a4631e1ba48f2f93 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.card','data' => ['padding' => 'sm']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['padding' => 'sm']); ?>
            <p class="text-xs font-semibold text-cyan-100/50 mb-1">Interventions</p>
            <p class="text-3xl font-display font-bold text-white"><?php echo e($interventions->count()); ?></p>
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
        <?php if (isset($component)) { $__componentOriginaldae4cd48acb67888a4631e1ba48f2f93 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.card','data' => ['padding' => 'sm']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['padding' => 'sm']); ?>
            <p class="text-xs font-semibold text-cyan-100/50 mb-1">Terminées</p>
            <p class="text-3xl font-display font-bold text-emerald-400"><?php echo e($interventions->where('statut', 'Terminée')->count()); ?></p>
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
        <?php if (isset($component)) { $__componentOriginaldae4cd48acb67888a4631e1ba48f2f93 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.card','data' => ['padding' => 'sm']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['padding' => 'sm']); ?>
            <p class="text-xs font-semibold text-cyan-100/50 mb-1">Coût total</p>
            <p class="text-3xl font-display font-bold text-white"><?php echo e(number_format($interventions->sum('cout'), 2, ',', ' ')); ?> <span class="text-base text-cyan-100/60">DT</span></p>
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
    </div>

    
    <?php if (isset($component)) { $__componentOriginaldae4cd48acb67888a4631e1ba48f2f93 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.card','data' => ['padding' => 'none','class' => 'overflow-hidden']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['padding' => 'none','class' => 'overflow-hidden']); ?>
        <div class="flex items-center justify-between gap-3 px-5 py-4 border-b border-white/10">
            <h2 class="font-display font-semibold text-white flex items-center gap-2">
                <i data-lucide="clipboard-list" class="w-5 h-5 text-cyan-300"></i> Interventions réalisées
            </h2>
            <?php if (isset($component)) { $__componentOriginal315d03b0d0695345e2e870d0abb4e102 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal315d03b0d0695345e2e870d0abb4e102 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.maintenance.link-button','data' => ['href' => route('manager.interventions.create', ['technicien_id' => $technicien->id]),'icon' => 'plus','class' => '!py-2 !text-xs']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('maintenance.link-button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('manager.interventions.create', ['technicien_id' => $technicien->id])),'icon' => 'plus','class' => '!py-2 !text-xs']); ?>
                Nouvelle intervention
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal315d03b0d0695345e2e870d0abb4e102)): ?>
<?php $attributes = $__attributesOriginal315d03b0d0695345e2e870d0abb4e102; ?>
<?php unset($__attributesOriginal315d03b0d0695345e2e870d0abb4e102); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal315d03b0d0695345e2e870d0abb4e102)): ?>
<?php $component = $__componentOriginal315d03b0d0695345e2e870d0abb4e102; ?>
<?php unset($__componentOriginal315d03b0d0695345e2e870d0abb4e102); ?>
<?php endif; ?>
        </div>

        <?php if($interventions->isEmpty()): ?>
            <?php if (isset($component)) { $__componentOriginal3607a477fdef7402bc742abad5df9c51 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3607a477fdef7402bc742abad5df9c51 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.empty-state','data' => ['icon' => 'clipboard-list','title' => 'Aucune intervention','description' => 'Ce technicien n\'a pas encore d\'intervention.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'clipboard-list','title' => 'Aucune intervention','description' => 'Ce technicien n\'a pas encore d\'intervention.']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3607a477fdef7402bc742abad5df9c51)): ?>
<?php $attributes = $__attributesOriginal3607a477fdef7402bc742abad5df9c51; ?>
<?php unset($__attributesOriginal3607a477fdef7402bc742abad5df9c51); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3607a477fdef7402bc742abad5df9c51)): ?>
<?php $component = $__componentOriginal3607a477fdef7402bc742abad5df9c51; ?>
<?php unset($__componentOriginal3607a477fdef7402bc742abad5df9c51); ?>
<?php endif; ?>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-white/10 text-left">
                            <th class="px-5 py-3 text-xs font-semibold text-cyan-100/50">Date</th>
                            <th class="px-5 py-3 text-xs font-semibold text-cyan-100/50">Description</th>
                            <th class="px-5 py-3 text-xs font-semibold text-cyan-100/50">Statut</th>
                            <th class="px-5 py-3 text-xs font-semibold text-cyan-100/50 text-right">Coût</th>
                            <th class="px-5 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        <?php $__currentLoopData = $interventions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $intervention): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr class="hover:bg-white/[.03] transition-colors">
                                <td class="px-5 py-3 text-cyan-100/80 whitespace-nowrap"><?php echo e($intervention->date->format('d/m/Y')); ?></td>
                                <td class="px-5 py-3 text-white"><?php echo e(Str::limit($intervention->description, 70)); ?></td>
                                <td class="px-5 py-3"><?php if (isset($component)) { $__componentOriginal613a257712d79f8faaf5703f21bda327 = $component; } ?>
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
<?php endif; ?></td>
                                <td class="px-5 py-3 text-right text-cyan-100/80 whitespace-nowrap"><?php echo e(number_format($intervention->cout, 2, ',', ' ')); ?> DT</td>
                                <td class="px-5 py-3 text-right">
                                    <a href="<?php echo e(route('manager.interventions.show', $intervention)); ?>" class="p-2 inline-flex rounded-lg text-cyan-100/60 hover:text-white hover:bg-white/5" title="Voir"><i data-lucide="eye" class="w-4 h-4"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
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
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.manager', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Ranim\Documents\GitHub\Esprit-5TWIN4-DevWave\resources\views/manager/techniciens/show.blade.php ENDPATH**/ ?>