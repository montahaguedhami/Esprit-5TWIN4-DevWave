<?php $__env->startSection('content'); ?>
<div class="min-h-screen flex" id="admin-shell">

    
    <aside id="admin-sidebar"
           class="fixed inset-y-0 left-0 z-40 flex flex-col w-64 glass-strong border-r border-cyan-500/10
                  transition-transform duration-300 lg:translate-x-0 -translate-x-full"
           aria-label="Sidebar administration">

        
        <div class="flex items-center gap-3 px-5 py-4 border-b border-white/5 shrink-0">
            <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-cyan-400 to-blue-600 flex items-center justify-center shrink-0">
                <i data-lucide="droplet" class="w-5 h-5 text-white"></i>
            </div>
            <div class="flex-1 min-w-0">
                <p class="font-display font-bold text-white text-sm leading-tight">AquaSecure</p>
                <p class="text-[10px] text-cyan-300/70 font-medium uppercase tracking-wider">Control Center</p>
            </div>
            
            <button onclick="closeSidebar()"
                    class="lg:hidden text-cyan-400 hover:text-white transition-colors"
                    aria-label="Fermer menu">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        
        <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1" aria-label="Navigation admin">

            <?php if (isset($component)) { $__componentOriginalc17a041c7cfa47ad2e8570c22c580c7f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc17a041c7cfa47ad2e8570c22c580c7f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin-nav-item','data' => ['route' => 'admin.dashboard','icon' => 'layout-dashboard','label' => 'Tableau de bord']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-nav-item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['route' => 'admin.dashboard','icon' => 'layout-dashboard','label' => 'Tableau de bord']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc17a041c7cfa47ad2e8570c22c580c7f)): ?>
<?php $attributes = $__attributesOriginalc17a041c7cfa47ad2e8570c22c580c7f; ?>
<?php unset($__attributesOriginalc17a041c7cfa47ad2e8570c22c580c7f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc17a041c7cfa47ad2e8570c22c580c7f)): ?>
<?php $component = $__componentOriginalc17a041c7cfa47ad2e8570c22c580c7f; ?>
<?php unset($__componentOriginalc17a041c7cfa47ad2e8570c22c580c7f); ?>
<?php endif; ?>

            <p class="px-3 pt-4 pb-1 text-[10px] font-semibold uppercase tracking-widest text-cyan-100/30">Gestion</p>
            <?php if (isset($component)) { $__componentOriginalc17a041c7cfa47ad2e8570c22c580c7f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc17a041c7cfa47ad2e8570c22c580c7f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin-nav-item','data' => ['route' => 'admin.users.index','icon' => 'users','label' => 'Utilisateurs']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-nav-item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['route' => 'admin.users.index','icon' => 'users','label' => 'Utilisateurs']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc17a041c7cfa47ad2e8570c22c580c7f)): ?>
<?php $attributes = $__attributesOriginalc17a041c7cfa47ad2e8570c22c580c7f; ?>
<?php unset($__attributesOriginalc17a041c7cfa47ad2e8570c22c580c7f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc17a041c7cfa47ad2e8570c22c580c7f)): ?>
<?php $component = $__componentOriginalc17a041c7cfa47ad2e8570c22c580c7f; ?>
<?php unset($__componentOriginalc17a041c7cfa47ad2e8570c22c580c7f); ?>
<?php endif; ?>
            <?php if (isset($component)) { $__componentOriginalc17a041c7cfa47ad2e8570c22c580c7f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc17a041c7cfa47ad2e8570c22c580c7f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin-nav-item','data' => ['route' => 'admin.roles','icon' => 'shield','label' => 'Rôles & permissions']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-nav-item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['route' => 'admin.roles','icon' => 'shield','label' => 'Rôles & permissions']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc17a041c7cfa47ad2e8570c22c580c7f)): ?>
<?php $attributes = $__attributesOriginalc17a041c7cfa47ad2e8570c22c580c7f; ?>
<?php unset($__attributesOriginalc17a041c7cfa47ad2e8570c22c580c7f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc17a041c7cfa47ad2e8570c22c580c7f)): ?>
<?php $component = $__componentOriginalc17a041c7cfa47ad2e8570c22c580c7f; ?>
<?php unset($__componentOriginalc17a041c7cfa47ad2e8570c22c580c7f); ?>
<?php endif; ?>
            <?php if (isset($component)) { $__componentOriginalc17a041c7cfa47ad2e8570c22c580c7f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc17a041c7cfa47ad2e8570c22c580c7f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin-nav-item','data' => ['route' => 'manager.map','icon' => 'map-pin','label' => 'Carte réseau']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-nav-item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['route' => 'manager.map','icon' => 'map-pin','label' => 'Carte réseau']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc17a041c7cfa47ad2e8570c22c580c7f)): ?>
<?php $attributes = $__attributesOriginalc17a041c7cfa47ad2e8570c22c580c7f; ?>
<?php unset($__attributesOriginalc17a041c7cfa47ad2e8570c22c580c7f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc17a041c7cfa47ad2e8570c22c580c7f)): ?>
<?php $component = $__componentOriginalc17a041c7cfa47ad2e8570c22c580c7f; ?>
<?php unset($__componentOriginalc17a041c7cfa47ad2e8570c22c580c7f); ?>
<?php endif; ?>
            <?php if (isset($component)) { $__componentOriginalc17a041c7cfa47ad2e8570c22c580c7f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc17a041c7cfa47ad2e8570c22c580c7f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin-nav-item','data' => ['route' => 'admin.logs','icon' => 'file-text','label' => 'Historique']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-nav-item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['route' => 'admin.logs','icon' => 'file-text','label' => 'Historique']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc17a041c7cfa47ad2e8570c22c580c7f)): ?>
<?php $attributes = $__attributesOriginalc17a041c7cfa47ad2e8570c22c580c7f; ?>
<?php unset($__attributesOriginalc17a041c7cfa47ad2e8570c22c580c7f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc17a041c7cfa47ad2e8570c22c580c7f)): ?>
<?php $component = $__componentOriginalc17a041c7cfa47ad2e8570c22c580c7f; ?>
<?php unset($__componentOriginalc17a041c7cfa47ad2e8570c22c580c7f); ?>
<?php endif; ?>

            <p class="px-3 pt-4 pb-1 text-[10px] font-semibold uppercase tracking-widest text-cyan-100/30">Compte</p>
            <?php if (isset($component)) { $__componentOriginalc17a041c7cfa47ad2e8570c22c580c7f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc17a041c7cfa47ad2e8570c22c580c7f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin-nav-item','data' => ['route' => 'profile.show','icon' => 'user-circle','label' => 'Mon profil']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-nav-item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['route' => 'profile.show','icon' => 'user-circle','label' => 'Mon profil']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc17a041c7cfa47ad2e8570c22c580c7f)): ?>
<?php $attributes = $__attributesOriginalc17a041c7cfa47ad2e8570c22c580c7f; ?>
<?php unset($__attributesOriginalc17a041c7cfa47ad2e8570c22c580c7f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc17a041c7cfa47ad2e8570c22c580c7f)): ?>
<?php $component = $__componentOriginalc17a041c7cfa47ad2e8570c22c580c7f; ?>
<?php unset($__componentOriginalc17a041c7cfa47ad2e8570c22c580c7f); ?>
<?php endif; ?>
            <?php if (isset($component)) { $__componentOriginalc17a041c7cfa47ad2e8570c22c580c7f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc17a041c7cfa47ad2e8570c22c580c7f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin-nav-item','data' => ['route' => 'settings.index','icon' => 'settings','label' => 'Paramètres']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-nav-item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['route' => 'settings.index','icon' => 'settings','label' => 'Paramètres']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc17a041c7cfa47ad2e8570c22c580c7f)): ?>
<?php $attributes = $__attributesOriginalc17a041c7cfa47ad2e8570c22c580c7f; ?>
<?php unset($__attributesOriginalc17a041c7cfa47ad2e8570c22c580c7f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc17a041c7cfa47ad2e8570c22c580c7f)): ?>
<?php $component = $__componentOriginalc17a041c7cfa47ad2e8570c22c580c7f; ?>
<?php unset($__componentOriginalc17a041c7cfa47ad2e8570c22c580c7f); ?>
<?php endif; ?>
            <?php if (isset($component)) { $__componentOriginalc17a041c7cfa47ad2e8570c22c580c7f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc17a041c7cfa47ad2e8570c22c580c7f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin-nav-item','data' => ['route' => 'notifications.index','icon' => 'bell','label' => 'Notifications']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-nav-item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['route' => 'notifications.index','icon' => 'bell','label' => 'Notifications']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc17a041c7cfa47ad2e8570c22c580c7f)): ?>
<?php $attributes = $__attributesOriginalc17a041c7cfa47ad2e8570c22c580c7f; ?>
<?php unset($__attributesOriginalc17a041c7cfa47ad2e8570c22c580c7f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc17a041c7cfa47ad2e8570c22c580c7f)): ?>
<?php $component = $__componentOriginalc17a041c7cfa47ad2e8570c22c580c7f; ?>
<?php unset($__componentOriginalc17a041c7cfa47ad2e8570c22c580c7f); ?>
<?php endif; ?>
        </nav>

        
        <div class="px-3 py-3 border-t border-white/5 shrink-0">
            <a href="<?php echo e(route('profile.show')); ?>"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-white/5 transition-colors group">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-cyan-500/30 to-blue-600/30 border border-cyan-400/20
                             flex items-center justify-center text-xs font-bold text-cyan-300 shrink-0">
                    AK
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-white text-xs font-semibold truncate">Amina Kacem</p>
                    <p class="text-cyan-100/40 text-[10px] truncate">Administrateur</p>
                </div>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-cyan-100/30 group-hover:text-cyan-400 transition-colors shrink-0"></i>
            </a>
        </div>
    </aside>

    
    <div id="sidebar-overlay"
         class="fixed inset-0 z-30 bg-black/50 backdrop-blur-sm hidden lg:hidden"
         onclick="closeSidebar()"></div>

    
    <div class="flex-1 flex flex-col min-w-0 lg:ml-64">

        
        <header class="sticky top-0 z-30 glass-strong border-b border-white/5 px-4 sm:px-6 py-3
                        flex items-center gap-3">

            
            <button onclick="openSidebar()"
                    class="lg:hidden glass p-2 rounded-lg text-cyan-300 hover:text-white transition-colors"
                    aria-label="Ouvrir le menu">
                <i data-lucide="menu" class="w-5 h-5"></i>
            </button>

            
            <div class="flex-1 min-w-0">
                <h1 class="text-white font-display font-bold text-base sm:text-lg leading-tight truncate">
                    <?php echo $__env->yieldContent('page-title', 'Administration'); ?>
                </h1>
                <p class="text-cyan-100/50 text-xs truncate hidden sm:block">
                    <?php echo $__env->yieldContent('page-subtitle', 'Vue globale de la plateforme AquaSecure'); ?>
                </p>
            </div>

            
            <button onclick="toggleTheme()"
                    class="glass p-2 rounded-lg text-cyan-300 hover:text-white transition-colors"
                    aria-label="Changer le thème">
                <i data-lucide="sun"  class="w-4 h-4 sun-icon  hidden"></i>
                <i data-lucide="moon" class="w-4 h-4 moon-icon"></i>
            </button>

            
            <?php if (isset($component)) { $__componentOriginal7169a5b356633be5dafc74bf7a8eb300 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal7169a5b356633be5dafc74bf7a8eb300 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.notification-center','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('notification-center'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal7169a5b356633be5dafc74bf7a8eb300)): ?>
<?php $attributes = $__attributesOriginal7169a5b356633be5dafc74bf7a8eb300; ?>
<?php unset($__attributesOriginal7169a5b356633be5dafc74bf7a8eb300); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal7169a5b356633be5dafc74bf7a8eb300)): ?>
<?php $component = $__componentOriginal7169a5b356633be5dafc74bf7a8eb300; ?>
<?php unset($__componentOriginal7169a5b356633be5dafc74bf7a8eb300); ?>
<?php endif; ?>

            
            <?php if (isset($component)) { $__componentOriginal42edc48abdcb6c65aa0760095ea712dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal42edc48abdcb6c65aa0760095ea712dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.user-menu','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('user-menu'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal42edc48abdcb6c65aa0760095ea712dd)): ?>
<?php $attributes = $__attributesOriginal42edc48abdcb6c65aa0760095ea712dd; ?>
<?php unset($__attributesOriginal42edc48abdcb6c65aa0760095ea712dd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal42edc48abdcb6c65aa0760095ea712dd)): ?>
<?php $component = $__componentOriginal42edc48abdcb6c65aa0760095ea712dd; ?>
<?php unset($__componentOriginal42edc48abdcb6c65aa0760095ea712dd); ?>
<?php endif; ?>
        </header>

        
        <main class="flex-1 overflow-x-hidden p-4 sm:p-6 pb-10">
            <?php echo $__env->yieldContent('admin-content'); ?>
        </main>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
/* ── Sidebar toggle ── */
function openSidebar() {
    document.getElementById('admin-sidebar').classList.remove('-translate-x-full');
    document.getElementById('sidebar-overlay').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}
function closeSidebar() {
    document.getElementById('admin-sidebar').classList.add('-translate-x-full');
    document.getElementById('sidebar-overlay').classList.add('hidden');
    document.body.style.overflow = '';
}

/* ── Theme icons ── */
function updateThemeIcons() {
    const isLight = document.documentElement.getAttribute('data-theme') === 'light';
    document.querySelectorAll('.sun-icon').forEach(el =>
        isLight ? el.classList.remove('hidden') : el.classList.add('hidden'));
    document.querySelectorAll('.moon-icon').forEach(el =>
        isLight ? el.classList.add('hidden') : el.classList.remove('hidden'));
}
updateThemeIcons();
const _origToggle = window.toggleTheme;
window.toggleTheme = function() { _origToggle?.(); updateThemeIcons(); };
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\ghada\Desktop\5ème\projet_Laravel\aquasecure\resources\views/layouts/admin.blade.php ENDPATH**/ ?>