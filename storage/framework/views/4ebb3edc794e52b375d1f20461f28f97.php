<?php $__env->startSection('content'); ?>
<div class="min-h-screen relative">
    <!-- Sticky Navigation -->
    <nav class="sticky top-0 z-50 glass-strong px-4 sm:px-6 py-3 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-cyan-400 to-blue-600 flex items-center justify-center">
                    <i data-lucide="droplet" class="w-5 h-5 text-white"></i>
                </div>
                <span class="font-display font-bold text-white hidden sm:block">AquaSecure</span>
                <span class="hidden sm:inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold bg-cyan-500/15 text-cyan-300 border border-cyan-400/20">
                    Espace Citoyen
                </span>
            </div>
        </div>
        <div class="flex items-center gap-2">
            
            <a href="<?php echo e(route('citizen.reports.create')); ?>"
               class="hidden sm:flex items-center gap-1.5 glass px-3 py-1.5 rounded-lg text-xs text-cyan-300 hover:text-white font-semibold transition-colors">
                <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                Signaler
            </a>
            <a href="<?php echo e(route('citizen.reports.create')); ?>"
               class="sm:hidden glass p-2 rounded-lg text-cyan-300 hover:text-white transition-colors"
               aria-label="Signaler un problème">
                <i data-lucide="plus" class="w-5 h-5"></i>
            </a>
            
            <button onclick="toggleTheme()"
                    class="glass p-2 rounded-lg text-cyan-200 hover:text-white transition-colors"
                    aria-label="Changer de thème">
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
        </div>
    </nav>

    
    <div class="fixed bottom-0 left-0 right-0 z-50 glass-strong border-t border-white/5 px-2 py-2 flex justify-around sm:hidden">
        <a href="<?php echo e(route('citizen.dashboard')); ?>"
           class="flex flex-col items-center gap-0.5 px-3 py-1.5 rounded-xl text-cyan-100/60 hover:text-cyan-300 transition-colors">
            <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
            <span class="text-[9px] font-medium">Accueil</span>
        </a>
        <a href="<?php echo e(route('citizen.reports.create')); ?>"
           class="flex flex-col items-center gap-0.5 px-3 py-1.5 rounded-xl bg-gradient-to-r from-cyan-500/20 to-blue-600/20 border border-cyan-400/25 text-cyan-300">
            <i data-lucide="plus" class="w-5 h-5"></i>
            <span class="text-[9px] font-medium">Signaler</span>
        </a>
        <a href="<?php echo e(route('citizen.invoices.index')); ?>"
           class="flex flex-col items-center gap-0.5 px-3 py-1.5 rounded-xl text-cyan-100/60 hover:text-cyan-300 transition-colors">
            <i data-lucide="file-text" class="w-5 h-5"></i>
            <span class="text-[9px] font-medium">Factures</span>
        </a>
        <a href="<?php echo e(route('citizen.notifications')); ?>"
           class="flex flex-col items-center gap-0.5 px-3 py-1.5 rounded-xl text-cyan-100/60 hover:text-cyan-300 transition-colors">
            <i data-lucide="bell" class="w-5 h-5"></i>
            <span class="text-[9px] font-medium">Notifs</span>
        </a>
        <a href="<?php echo e(route('profile.show')); ?>"
           class="flex flex-col items-center gap-0.5 px-3 py-1.5 rounded-xl text-cyan-100/60 hover:text-cyan-300 transition-colors">
            <i data-lucide="user" class="w-5 h-5"></i>
            <span class="text-[9px] font-medium">Profil</span>
        </a>
    </div>

    <!-- Tab Navigation (if provided) -->
    <?php if (! empty(trim($__env->yieldContent('tabs')))): ?>
    <div class="sticky top-[57px] z-40 glass px-4 py-2 flex gap-2 overflow-x-auto">
        <?php echo $__env->yieldContent('tabs'); ?>
    </div>
    <?php endif; ?>

    <!-- Main Content -->
    <div class="px-4 sm:px-6 py-6 max-w-5xl mx-auto pb-24 sm:pb-8">
        <?php echo $__env->yieldContent('frontoffice-content'); ?>
    </div>

    <!-- Mobile Bottom Navigation (if provided) -->
    <?php if (! empty(trim($__env->yieldContent('mobile-nav')))): ?>
    <div class="fixed bottom-0 left-0 right-0 z-50 glass-strong px-2 py-2 flex justify-around sm:hidden">
        <?php echo $__env->yieldContent('mobile-nav'); ?>
    </div>
    <?php endif; ?>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
    // Update theme icons
    function updateThemeIcons() {
        const theme = document.documentElement.getAttribute('data-theme');
        document.querySelectorAll('.sun-icon').forEach(el => {
            theme === 'light' ? el.classList.remove('hidden') : el.classList.add('hidden');
        });
        document.querySelectorAll('.moon-icon').forEach(el => {
            theme === 'light' ? el.classList.add('hidden') : el.classList.remove('hidden');
        });
    }
    
    updateThemeIcons();
    
    const originalToggleTheme = window.toggleTheme;
    window.toggleTheme = function() {
        originalToggleTheme();
        updateThemeIcons();
    };

    // Profile modal toggle (placeholder)
    function toggleProfileModal() {
        showToast('Modal profil à implémenter', 'info');
    }
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\ghada\Desktop\5ème\projet_Laravel\aquasecure\resources\views/layouts/frontoffice.blade.php ENDPATH**/ ?>