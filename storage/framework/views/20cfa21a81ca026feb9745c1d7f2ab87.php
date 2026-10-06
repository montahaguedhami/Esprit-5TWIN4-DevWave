<?php $__env->startSection('admin-content'); ?>
<div class="min-h-screen bg-gradient-to-br from-slate-900 via-cyan-950 to-slate-900">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-white mb-2 flex items-center gap-3">
                    <i data-lucide="shield" class="w-8 h-8 text-purple-400"></i>
                    Rôles & Permissions
                </h1>
                <p class="text-slate-400">Gérer les rôles et contrôler l'accès aux fonctionnalités</p>
            </div>
            <button class="btn btn-primary">
                <i data-lucide="plus" class="w-4 h-4"></i>
                Nouveau Rôle
            </button>
        </div>

        <?php
            $roles = \App\Data\PlaceholderData::adminRoles();
        ?>

        <!-- Roles Cards -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php if (isset($component)) { $__componentOriginaldae4cd48acb67888a4631e1ba48f2f93 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.card','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
                <div class="flex items-start justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-xl bg-<?php echo e($role['color']); ?>-500/20 flex items-center justify-center">
                            <i data-lucide="shield" class="w-6 h-6 text-<?php echo e($role['color']); ?>-400"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-white"><?php echo e($role['name']); ?></h3>
                            <p class="text-sm text-slate-400"><?php echo e($role['description']); ?></p>
                        </div>
                    </div>
                    <?php if (isset($component)) { $__componentOriginaleea726fa4f84deb9f7684b50bdd6328c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaleea726fa4f84deb9f7684b50bdd6328c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.dropdown','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.dropdown'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
                         <?php $__env->slot('trigger', null, []); ?> 
                            <button class="text-slate-400 hover:text-white transition-colors">
                                <i data-lucide="more-vertical" class="w-5 h-5"></i>
                            </button>
                         <?php $__env->endSlot(); ?>
                        <a href="#" class="block px-4 py-2 text-sm text-slate-300 hover:bg-slate-700">
                            <i data-lucide="edit" class="w-4 h-4 inline mr-2"></i>
                            Modifier
                        </a>
                        <a href="#" class="block px-4 py-2 text-sm text-slate-300 hover:bg-slate-700">
                            <i data-lucide="copy" class="w-4 h-4 inline mr-2"></i>
                            Dupliquer
                        </a>
                        <?php if($role['id'] !== 'admin'): ?>
                        <a href="#" class="block px-4 py-2 text-sm text-red-400 hover:bg-slate-700">
                            <i data-lucide="trash-2" class="w-4 h-4 inline mr-2"></i>
                            Supprimer
                        </a>
                        <?php endif; ?>
                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaleea726fa4f84deb9f7684b50bdd6328c)): ?>
<?php $attributes = $__attributesOriginaleea726fa4f84deb9f7684b50bdd6328c; ?>
<?php unset($__attributesOriginaleea726fa4f84deb9f7684b50bdd6328c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaleea726fa4f84deb9f7684b50bdd6328c)): ?>
<?php $component = $__componentOriginaleea726fa4f84deb9f7684b50bdd6328c; ?>
<?php unset($__componentOriginaleea726fa4f84deb9f7684b50bdd6328c); ?>
<?php endif; ?>
                </div>

                <div class="flex items-center gap-4 mb-4 pb-4 border-b border-slate-700">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-white"><?php echo e($role['users_count']); ?></div>
                        <div class="text-xs text-slate-400">Utilisateurs</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-white"><?php echo e(count($role['permissions'])); ?></div>
                        <div class="text-xs text-slate-400">Modules</div>
                    </div>
                </div>

                <div class="space-y-3">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-slate-400">Permissions</span>
                        <span class="text-cyan-400 cursor-pointer hover:text-cyan-300">Voir tout</span>
                    </div>
                    
                    <div class="space-y-2">
                        <?php $__currentLoopData = array_slice($role['permissions'], 0, 3); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $module => $perms): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="flex items-center justify-between p-2 rounded-lg bg-slate-800/50">
                            <div class="flex items-center gap-2">
                                <i data-lucide="check-circle" class="w-4 h-4 text-emerald-400"></i>
                                <span class="text-sm text-white capitalize"><?php echo e($module); ?></span>
                            </div>
                            <div class="flex items-center gap-1">
                                <?php $__currentLoopData = $perms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $perm): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <span class="text-xs px-2 py-0.5 rounded bg-slate-700 text-slate-300">
                                    <?php echo e(substr($perm, 0, 1)); ?>

                                </span>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        
                        <?php if(count($role['permissions']) > 3): ?>
                        <div class="text-center">
                            <span class="text-xs text-slate-400">+<?php echo e(count($role['permissions']) - 3); ?> modules supplémentaires</span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="mt-4 pt-4 border-t border-slate-700">
                    <button class="w-full btn btn-secondary text-sm">
                        <i data-lucide="settings" class="w-4 h-4"></i>
                        Configurer les permissions
                    </button>
                </div>
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
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        <!-- Permissions Matrix -->
        <?php if (isset($component)) { $__componentOriginaldae4cd48acb67888a4631e1ba48f2f93 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.card','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-bold text-white flex items-center gap-2">
                    <i data-lucide="grid" class="w-6 h-6 text-cyan-400"></i>
                    Matrice des Permissions
                </h2>
                <div class="flex items-center gap-2">
                    <span class="text-xs text-slate-400">C: Create | R: Read | U: Update | D: Delete</span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-slate-700">
                            <th class="text-left py-3 px-4 text-sm font-semibold text-slate-300">Module</th>
                            <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <th class="text-center py-3 px-4 text-sm font-semibold text-<?php echo e($role['color']); ?>-400">
                                <?php echo e($role['name']); ?>

                            </th>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            $allModules = ['users', 'roles', 'system', 'incidents', 'teams', 'reports', 'analytics', 'interventions', 'equipment', 'invoices', 'notifications'];
                        ?>
                        
                        <?php $__currentLoopData = $allModules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $module): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr class="border-b border-slate-800 hover:bg-slate-800/30 transition-colors">
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-2">
                                    <i data-lucide="box" class="w-4 h-4 text-slate-400"></i>
                                    <span class="text-sm font-medium text-white capitalize"><?php echo e($module); ?></span>
                                </div>
                            </td>
                            <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <td class="py-3 px-4 text-center">
                                <?php if(isset($role['permissions'][$module])): ?>
                                <div class="flex items-center justify-center gap-1">
                                    <?php $__currentLoopData = $role['permissions'][$module]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $perm): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <span class="text-xs px-1.5 py-0.5 rounded bg-<?php echo e($role['color']); ?>-500/20 text-<?php echo e($role['color']); ?>-400 font-medium">
                                        <?php echo e(strtoupper(substr($perm, 0, 1))); ?>

                                    </span>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                                <?php else: ?>
                                <span class="text-slate-600">—</span>
                                <?php endif; ?>
                            </td>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
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
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\monta\Downloads\Aqua_Secure-main\Aqua_Secure-main\resources\views/admin/roles.blade.php ENDPATH**/ ?>