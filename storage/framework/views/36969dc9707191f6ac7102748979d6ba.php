<?php $__env->startSection('title', 'Interventions — AquaSecure'); ?>

<?php $__env->startSection('manager-content'); ?>
<div class="max-w-6xl mx-auto">
    <?php if (isset($component)) { $__componentOriginalf2de2158a73822dbd9074b03366e73f1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf2de2158a73822dbd9074b03366e73f1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.maintenance.page-header','data' => ['title' => 'Interventions','subtitle' => 'Suivi des interventions de maintenance sur le réseau','icon' => 'clipboard-list']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('maintenance.page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Interventions','subtitle' => 'Suivi des interventions de maintenance sur le réseau','icon' => 'clipboard-list']); ?>
        <?php if (isset($component)) { $__componentOriginal315d03b0d0695345e2e870d0abb4e102 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal315d03b0d0695345e2e870d0abb4e102 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.maintenance.link-button','data' => ['href' => route('manager.techniciens.index'),'variant' => 'secondary','icon' => 'hard-hat']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('maintenance.link-button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('manager.techniciens.index')),'variant' => 'secondary','icon' => 'hard-hat']); ?>Techniciens <?php echo $__env->renderComponent(); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.maintenance.link-button','data' => ['href' => route('manager.interventions.create', request()->only('technicien_id')),'icon' => 'plus']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('maintenance.link-button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('manager.interventions.create', request()->only('technicien_id'))),'icon' => 'plus']); ?>Nouvelle intervention <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal315d03b0d0695345e2e870d0abb4e102)): ?>
<?php $attributes = $__attributesOriginal315d03b0d0695345e2e870d0abb4e102; ?>
<?php unset($__attributesOriginal315d03b0d0695345e2e870d0abb4e102); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal315d03b0d0695345e2e870d0abb4e102)): ?>
<?php $component = $__componentOriginal315d03b0d0695345e2e870d0abb4e102; ?>
<?php unset($__componentOriginal315d03b0d0695345e2e870d0abb4e102); ?>
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

    
    <?php if (isset($component)) { $__componentOriginaldae4cd48acb67888a4631e1ba48f2f93 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.card','data' => ['padding' => 'sm','class' => 'mb-6']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['padding' => 'sm','class' => 'mb-6']); ?>
        <form method="GET" action="<?php echo e(route('manager.interventions.index')); ?>" class="grid sm:grid-cols-3 gap-3 items-end">
            <?php if (isset($component)) { $__componentOriginal7041cc63efd62f0450fe4bb37aadf484 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal7041cc63efd62f0450fe4bb37aadf484 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.forms.select','data' => ['name' => 'technicien_id','label' => 'Technicien','placeholder' => 'Tous les techniciens']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('forms.select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'technicien_id','label' => 'Technicien','placeholder' => 'Tous les techniciens']); ?>
                <?php $__currentLoopData = $techniciens; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $technicien): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($technicien->id); ?>" <?php if(request('technicien_id') == $technicien->id): echo 'selected'; endif; ?>><?php echo e($technicien->nom); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal7041cc63efd62f0450fe4bb37aadf484)): ?>
<?php $attributes = $__attributesOriginal7041cc63efd62f0450fe4bb37aadf484; ?>
<?php unset($__attributesOriginal7041cc63efd62f0450fe4bb37aadf484); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal7041cc63efd62f0450fe4bb37aadf484)): ?>
<?php $component = $__componentOriginal7041cc63efd62f0450fe4bb37aadf484; ?>
<?php unset($__componentOriginal7041cc63efd62f0450fe4bb37aadf484); ?>
<?php endif; ?>

            <?php if (isset($component)) { $__componentOriginal7041cc63efd62f0450fe4bb37aadf484 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal7041cc63efd62f0450fe4bb37aadf484 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.forms.select','data' => ['name' => 'statut','label' => 'Statut','placeholder' => 'Tous les statuts']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('forms.select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'statut','label' => 'Statut','placeholder' => 'Tous les statuts']); ?>
                <?php $__currentLoopData = \App\Models\Intervention::STATUTS; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $statut): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($statut); ?>" <?php if(request('statut') === $statut): echo 'selected'; endif; ?>><?php echo e($statut); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal7041cc63efd62f0450fe4bb37aadf484)): ?>
<?php $attributes = $__attributesOriginal7041cc63efd62f0450fe4bb37aadf484; ?>
<?php unset($__attributesOriginal7041cc63efd62f0450fe4bb37aadf484); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal7041cc63efd62f0450fe4bb37aadf484)): ?>
<?php $component = $__componentOriginal7041cc63efd62f0450fe4bb37aadf484; ?>
<?php unset($__componentOriginal7041cc63efd62f0450fe4bb37aadf484); ?>
<?php endif; ?>

            <div class="flex gap-2">
                <?php if (isset($component)) { $__componentOriginala8bb031a483a05f647cb99ed3a469847 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala8bb031a483a05f647cb99ed3a469847 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.button','data' => ['type' => 'submit','icon' => 'filter','class' => 'flex-1']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'submit','icon' => 'filter','class' => 'flex-1']); ?>Filtrer <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala8bb031a483a05f647cb99ed3a469847)): ?>
<?php $attributes = $__attributesOriginala8bb031a483a05f647cb99ed3a469847; ?>
<?php unset($__attributesOriginala8bb031a483a05f647cb99ed3a469847); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala8bb031a483a05f647cb99ed3a469847)): ?>
<?php $component = $__componentOriginala8bb031a483a05f647cb99ed3a469847; ?>
<?php unset($__componentOriginala8bb031a483a05f647cb99ed3a469847); ?>
<?php endif; ?>
                <?php if(request()->hasAny(['technicien_id', 'statut'])): ?>
                    <?php if (isset($component)) { $__componentOriginal315d03b0d0695345e2e870d0abb4e102 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal315d03b0d0695345e2e870d0abb4e102 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.maintenance.link-button','data' => ['href' => route('manager.interventions.index'),'variant' => 'secondary','title' => 'Réinitialiser']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('maintenance.link-button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('manager.interventions.index')),'variant' => 'secondary','title' => 'Réinitialiser']); ?>
                        <i data-lucide="x" class="w-4 h-4"></i>
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
                <?php endif; ?>
            </div>
        </form>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.card','data' => ['padding' => 'none','class' => 'overflow-hidden']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['padding' => 'none','class' => 'overflow-hidden']); ?>
        <?php if($interventions->isEmpty()): ?>
            <?php if (isset($component)) { $__componentOriginal3607a477fdef7402bc742abad5df9c51 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3607a477fdef7402bc742abad5df9c51 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.empty-state','data' => ['icon' => 'clipboard-list','title' => 'Aucune intervention','description' => 'Aucune intervention ne correspond à vos filtres.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'clipboard-list','title' => 'Aucune intervention','description' => 'Aucune intervention ne correspond à vos filtres.']); ?>
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
                            <th class="px-5 py-3 text-xs font-semibold text-cyan-100/50">Technicien</th>
                            <th class="px-5 py-3 text-xs font-semibold text-cyan-100/50">Description</th>
                            <th class="px-5 py-3 text-xs font-semibold text-cyan-100/50">Statut</th>
                            <th class="px-5 py-3 text-xs font-semibold text-cyan-100/50 text-right">Coût</th>
                            <th class="px-5 py-3 text-xs font-semibold text-cyan-100/50 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        <?php $__currentLoopData = $interventions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $intervention): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr class="hover:bg-white/[.03] transition-colors">
                                <td class="px-5 py-3 text-cyan-100/80 whitespace-nowrap"><?php echo e($intervention->date->format('d/m/Y')); ?></td>
                                <td class="px-5 py-3 whitespace-nowrap">
                                    <a href="<?php echo e(route('manager.techniciens.show', $intervention->technicien)); ?>" class="font-semibold text-white hover:text-cyan-300"><?php echo e($intervention->technicien->nom); ?></a>
                                </td>
                                <td class="px-5 py-3 text-cyan-100/80"><?php echo e(Str::limit($intervention->description, 60)); ?></td>
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
                                <td class="px-5 py-3">
                                    <div class="flex items-center justify-end gap-1">
                                        <a href="<?php echo e(route('manager.interventions.show', $intervention)); ?>" class="p-2 rounded-lg text-cyan-100/60 hover:text-white hover:bg-white/5" title="Voir"><i data-lucide="eye" class="w-4 h-4"></i></a>
                                        <a href="<?php echo e(route('manager.interventions.edit', $intervention)); ?>" class="p-2 rounded-lg text-cyan-100/60 hover:text-white hover:bg-white/5" title="Modifier"><i data-lucide="pencil" class="w-4 h-4"></i></a>
                                        <?php if (isset($component)) { $__componentOriginal00677f0e8dccc4dc790f25dd98652820 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal00677f0e8dccc4dc790f25dd98652820 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.maintenance.delete-button','data' => ['action' => route('manager.interventions.destroy', $intervention),'size' => 'sm','label' => '','confirm' => 'Supprimer cette intervention ?']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('maintenance.delete-button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['action' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('manager.interventions.destroy', $intervention)),'size' => 'sm','label' => '','confirm' => 'Supprimer cette intervention ?']); ?>
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
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
            <?php if (isset($component)) { $__componentOriginalbced78001204be04a1343e8b9e8a167f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalbced78001204be04a1343e8b9e8a167f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.maintenance.pagination','data' => ['paginator' => $interventions]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('maintenance.pagination'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['paginator' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($interventions)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalbced78001204be04a1343e8b9e8a167f)): ?>
<?php $attributes = $__attributesOriginalbced78001204be04a1343e8b9e8a167f; ?>
<?php unset($__attributesOriginalbced78001204be04a1343e8b9e8a167f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalbced78001204be04a1343e8b9e8a167f)): ?>
<?php $component = $__componentOriginalbced78001204be04a1343e8b9e8a167f; ?>
<?php unset($__componentOriginalbced78001204be04a1343e8b9e8a167f); ?>
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
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.manager', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\monta\Downloads\DEVWAVE\Esprit-5TWIN4-DevWave-main (1)\Esprit-5TWIN4-DevWave-main\resources\views\manager\interventions\index.blade.php ENDPATH**/ ?>