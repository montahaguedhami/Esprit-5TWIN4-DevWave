<?php $__env->startSection('content'); ?>
<div class="min-h-screen bg-[#04121b] text-white flex font-sans relative" id="admin-shell">

    
    <aside id="admin-sidebar"
           class="fixed inset-y-0 left-0 z-40 flex flex-col w-64 glass-strong border-r border-white/20
                  transition-transform duration-300 lg:translate-x-0 -translate-x-full"
           aria-label="Sidebar administration">

        
        <div class="flex items-center gap-3 px-5 py-4 border-b border-white/10 shrink-0">
            <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-cyan-400 to-blue-600 flex items-center justify-center shadow-lg shadow-cyan-500/30 shrink-0">
                <i data-lucide="droplet" class="w-5 h-5 text-white"></i>
            </div>
            <div class="flex-1 min-w-0">
                <p class="font-display font-semibold text-white text-base leading-tight">AquaSecure</p>
                <div class="liquid-chip mt-0.5 py-0 px-2 h-5">
                    <span class="text-[9px] text-cyan-300 font-semibold uppercase tracking-wider">Control Center</span>
                </div>
            </div>
            
            <button onclick="closeSidebar()"
                    class="lg:hidden text-white/70 hover:text-white transition-colors"
                    aria-label="Fermer menu">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        
        <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1.5" aria-label="Navigation admin">

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

            <p class="px-3 pt-4 pb-1 text-[10px] font-bold uppercase tracking-widest text-white/40">Gestion System</p>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin-nav-item','data' => ['route' => 'admin.logs','icon' => 'file-text','label' => 'Historique Logs']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-nav-item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['route' => 'admin.logs','icon' => 'file-text','label' => 'Historique Logs']); ?>
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

            <p class="px-3 pt-4 pb-1 text-[10px] font-bold uppercase tracking-widest text-white/40">Compte & Config</p>
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

        
        <div class="px-3 py-3 border-t border-white/10 shrink-0">
            <a href="<?php echo e(route('profile.show')); ?>"
               class="flex items-center gap-3 px-3 py-2.5 rounded-2xl glass hover:bg-white/15 transition-colors group">
                <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-cyan-500/40 to-blue-600/40 border border-cyan-400/30
                             flex items-center justify-center text-xs font-bold text-cyan-300 shrink-0">
                    AK
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-white text-xs font-semibold truncate">Amina Kacem</p>
                    <p class="text-cyan-300 text-[10px] truncate">Admin Principal</p>
                </div>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-white/40 group-hover:text-cyan-300 transition-colors shrink-0"></i>
            </a>
        </div>
    </aside>

    
    <div id="sidebar-overlay"
         class="fixed inset-0 z-30 bg-black/60 backdrop-blur-md hidden lg:hidden"
         onclick="closeSidebar()"></div>

    
    <div class="flex-1 flex flex-col min-w-0 lg:ml-64">

        
        <header class="sticky top-0 z-30 glass-strong border-b border-white/20 px-4 sm:px-6 py-3
                        flex items-center gap-3">

            
            <button onclick="openSidebar()"
                    class="lg:hidden liquid-tool text-cyan-300 hover:text-white"
                    aria-label="Ouvrir le menu">
                <i data-lucide="menu" class="w-5 h-5"></i>
            </button>

            
            <div class="flex-1 min-w-0">
                <h1 class="text-white font-display font-semibold text-base sm:text-lg leading-tight truncate">
                    <?php echo $__env->yieldContent('page-title', 'Administration'); ?>
                </h1>
                <p class="text-white/60 text-xs truncate hidden sm:block">
                    <?php echo $__env->yieldContent('page-subtitle', 'Vue globale de la plateforme AquaSecure'); ?>
                </p>
            </div>

            
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

        
        <main class="flex-1 overflow-x-hidden p-4 sm:p-6 pb-12">
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

document.addEventListener('DOMContentLoaded', function() {
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
});
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>



<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\monta\Downloads\Aqua_Secure-main\Aqua_Secure-main\resources\views/layouts/admin.blade.php ENDPATH**/ ?>