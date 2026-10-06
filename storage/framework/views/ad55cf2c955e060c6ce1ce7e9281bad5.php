<?php
    $user = session('user', ['name' => 'Utilisateur', 'email' => 'user@aquasecure.tn', 'role' => 'citizen']);
    $roleLabels = [
        'admin' => 'Administrateur',
        'manager' => 'Gestionnaire',
        'technician' => 'Technicien',
        'citizen' => 'Citoyen',
    ];
    $roleColors = [
        'admin' => 'purple',
        'manager' => 'blue',
        'technician' => 'cyan',
        'citizen' => 'emerald',
    ];
?>

<!-- User Menu Button -->
<div class="relative user-menu">
    <button 
        onclick="toggleUserMenu()" 
        class="flex items-center gap-3 p-2 rounded-lg glass hover:glass-strong transition-all group"
        aria-label="Menu utilisateur"
    >
        <?php if (isset($component)) { $__componentOriginald04dd79f9e235eb8e58dee4526a2f3c2 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald04dd79f9e235eb8e58dee4526a2f3c2 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.avatar','data' => ['name' => $user['name'],'size' => 'sm']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.avatar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($user['name']),'size' => 'sm']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald04dd79f9e235eb8e58dee4526a2f3c2)): ?>
<?php $attributes = $__attributesOriginald04dd79f9e235eb8e58dee4526a2f3c2; ?>
<?php unset($__attributesOriginald04dd79f9e235eb8e58dee4526a2f3c2); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald04dd79f9e235eb8e58dee4526a2f3c2)): ?>
<?php $component = $__componentOriginald04dd79f9e235eb8e58dee4526a2f3c2; ?>
<?php unset($__componentOriginald04dd79f9e235eb8e58dee4526a2f3c2); ?>
<?php endif; ?>
        <div class="hidden md:block text-left">
            <div class="text-sm font-semibold text-white group-hover:text-cyan-400 transition-colors">
                <?php echo e($user['name']); ?>

            </div>
            <div class="text-xs text-cyan-100/60"><?php echo e($roleLabels[$user['role']] ?? 'Utilisateur'); ?></div>
        </div>
        <i data-lucide="chevron-down" class="w-4 h-4 text-cyan-100/60 group-hover:text-cyan-400 transition-all group-hover:rotate-180"></i>
    </button>

    <!-- User Dropdown -->
    <div 
        id="user-menu-dropdown" 
        class="hidden absolute right-0 mt-2 w-72 glass-strong rounded-2xl shadow-2xl border border-white/10 overflow-hidden z-50"
    >
        <!-- User Info Header -->
        <div class="p-4 border-b border-white/10 bg-gradient-to-br from-cyan-500/10 to-blue-600/10">
            <div class="flex items-center gap-3 mb-3">
                <?php if (isset($component)) { $__componentOriginald04dd79f9e235eb8e58dee4526a2f3c2 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald04dd79f9e235eb8e58dee4526a2f3c2 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.avatar','data' => ['name' => $user['name'],'size' => 'lg']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.avatar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($user['name']),'size' => 'lg']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald04dd79f9e235eb8e58dee4526a2f3c2)): ?>
<?php $attributes = $__attributesOriginald04dd79f9e235eb8e58dee4526a2f3c2; ?>
<?php unset($__attributesOriginald04dd79f9e235eb8e58dee4526a2f3c2); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald04dd79f9e235eb8e58dee4526a2f3c2)): ?>
<?php $component = $__componentOriginald04dd79f9e235eb8e58dee4526a2f3c2; ?>
<?php unset($__componentOriginald04dd79f9e235eb8e58dee4526a2f3c2); ?>
<?php endif; ?>
                <div class="flex-1 min-w-0">
                    <h3 class="text-base font-bold text-white truncate"><?php echo e($user['name']); ?></h3>
                    <p class="text-xs text-cyan-100/70 truncate"><?php echo e($user['email']); ?></p>
                </div>
            </div>
            <?php if (isset($component)) { $__componentOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.status-badge','data' => ['status' => $roleColors[$user['role']] ?? 'cyan','label' => $roleLabels[$user['role']] ?? 'Utilisateur']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($roleColors[$user['role']] ?? 'cyan'),'label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($roleLabels[$user['role']] ?? 'Utilisateur')]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8)): ?>
<?php $attributes = $__attributesOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8; ?>
<?php unset($__attributesOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8)): ?>
<?php $component = $__componentOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8; ?>
<?php unset($__componentOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8); ?>
<?php endif; ?>
        </div>

        <!-- Menu Items -->
        <div class="py-2">
            <!-- Profile -->
            <a href="<?php echo e(route('profile.show')); ?>" class="flex items-center gap-3 px-4 py-3 hover:bg-white/5 transition-colors group">
                <div class="w-8 h-8 rounded-lg bg-cyan-500/20 flex items-center justify-center group-hover:bg-cyan-500/30 transition-colors">
                    <i data-lucide="user" class="w-4 h-4 text-cyan-400"></i>
                </div>
                <div class="flex-1">
                    <div class="text-sm font-medium text-white">Mon Profil</div>
                    <div class="text-xs text-cyan-100/60">Informations personnelles</div>
                </div>
            </a>

            <!-- Settings -->
            <a href="<?php echo e(route('settings.index')); ?>" class="flex items-center gap-3 px-4 py-3 hover:bg-white/5 transition-colors group">
                <div class="w-8 h-8 rounded-lg bg-blue-500/20 flex items-center justify-center group-hover:bg-blue-500/30 transition-colors">
                    <i data-lucide="settings" class="w-4 h-4 text-blue-400"></i>
                </div>
                <div class="flex-1">
                    <div class="text-sm font-medium text-white">Paramètres</div>
                    <div class="text-xs text-cyan-100/60">Préférences et configuration</div>
                </div>
            </a>

            <!-- Notifications Settings -->
            <a href="<?php echo e(route('settings.notifications')); ?>" class="flex items-center gap-3 px-4 py-3 hover:bg-white/5 transition-colors group">
                <div class="w-8 h-8 rounded-lg bg-purple-500/20 flex items-center justify-center group-hover:bg-purple-500/30 transition-colors">
                    <i data-lucide="bell" class="w-4 h-4 text-purple-400"></i>
                </div>
                <div class="flex-1">
                    <div class="text-sm font-medium text-white">Notifications</div>
                    <div class="text-xs text-cyan-100/60">Gérer les alertes</div>
                </div>
            </a>

            <!-- Divider -->
            <div class="my-2 border-t border-white/10"></div>

            <!-- Help -->
            <a href="#" class="flex items-center gap-3 px-4 py-3 hover:bg-white/5 transition-colors group">
                <div class="w-8 h-8 rounded-lg bg-emerald-500/20 flex items-center justify-center group-hover:bg-emerald-500/30 transition-colors">
                    <i data-lucide="help-circle" class="w-4 h-4 text-emerald-400"></i>
                </div>
                <div class="flex-1">
                    <div class="text-sm font-medium text-white">Centre d'aide</div>
                    <div class="text-xs text-cyan-100/60">Documentation et support</div>
                </div>
            </a>

            <!-- Divider -->
            <div class="my-2 border-t border-white/10"></div>

            <!-- Logout -->
            <form method="POST" action="<?php echo e(route('logout')); ?>" class="px-2">
                <?php echo csrf_field(); ?>
                <button type="submit" class="w-full flex items-center gap-3 px-4 py-3 hover:bg-red-500/10 rounded-lg transition-colors group">
                    <div class="w-8 h-8 rounded-lg bg-red-500/20 flex items-center justify-center group-hover:bg-red-500/30 transition-colors">
                        <i data-lucide="log-out" class="w-4 h-4 text-red-400"></i>
                    </div>
                    <div class="flex-1 text-left">
                        <div class="text-sm font-medium text-red-400">Déconnexion</div>
                        <div class="text-xs text-cyan-100/60">Quitter votre session</div>
                    </div>
                </button>
            </form>
        </div>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
    function toggleUserMenu() {
        const dropdown = document.getElementById('user-menu-dropdown');
        const isHidden = dropdown.classList.contains('hidden');
        
        // Close notification dropdown if open
        const notifDropdown = document.getElementById('notification-dropdown');
        if (notifDropdown) {
            notifDropdown.classList.add('hidden');
        }
        
        if (isHidden) {
            dropdown.classList.remove('hidden');
            dropdown.style.animation = 'slideDown 0.2s ease-out';
        } else {
            dropdown.classList.add('hidden');
        }
        
        // Reinitialize icons
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    }

    // Close dropdown when clicking outside
    document.addEventListener('click', function(event) {
        const userMenu = document.querySelector('.user-menu');
        if (userMenu && !userMenu.contains(event.target)) {
            document.getElementById('user-menu-dropdown').classList.add('hidden');
        }
    });
</script>
<?php $__env->stopPush(); ?>
<?php /**PATH C:\Users\ghada\Desktop\5ème\projet_Laravel\aquasecure\resources\views/components/user-menu.blade.php ENDPATH**/ ?>