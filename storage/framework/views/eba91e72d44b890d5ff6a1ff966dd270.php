<?php $__env->startSection('content'); ?>
<div class="min-h-screen bg-[#04121b] text-white flex flex-col font-sans relative w-full">
    <!-- NAVBAR (Full Width Header Shell) -->
    <header class="sticky top-0 z-50 glass-strong border-b border-white/20 w-full px-4 sm:px-8 py-3.5">
        <div class="w-full max-w-[1600px] mx-auto flex items-center justify-between gap-4">
            
            
            <div class="flex items-center gap-3">
                <a href="<?php echo e(route('landing')); ?>" class="liquid-tool text-white/80 hover:text-white" title="Retour à l'accueil" aria-label="Retour à l'accueil">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                </a>
                <a href="<?php echo e(route('citizen.dashboard')); ?>" class="flex items-center gap-2.5 group">
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-cyan-400 to-blue-600 flex items-center justify-center shadow-lg shadow-cyan-500/30 group-hover:scale-105 transition-transform">
                        <i data-lucide="droplet" class="w-5 h-5 text-white"></i>
                    </div>
                    <span class="font-display font-bold text-white text-xl tracking-tight hidden sm:block">AquaSecure</span>
                    <div class="liquid-chip hidden sm:inline-flex">
                        <span class="w-1.5 h-1.5 rounded-full bg-cyan-400 animate-pulse"></span>
                        <span class="text-[11px] font-semibold text-white/90 uppercase tracking-wider">Citoyen</span>
                    </div>
                </a>
            </div>

            
            <nav class="hidden md:flex items-center gap-1.5 glass p-1.5 rounded-2xl text-xs font-semibold">
                <a href="<?php echo e(route('citizen.dashboard')); ?>" class="px-4 py-2 rounded-xl text-white/80 hover:text-white hover:bg-white/10 transition-all flex items-center gap-2">
                    <i data-lucide="layout-dashboard" class="w-4 h-4 text-cyan-300"></i>
                    <span>Tableau de bord</span>
                </a>
                <a href="<?php echo e(route('citizen.reports.create')); ?>" class="px-4 py-2 rounded-xl text-white/80 hover:text-white hover:bg-white/10 transition-all flex items-center gap-2">
                    <i data-lucide="alert-circle" class="w-4 h-4 text-amber-300"></i>
                    <span>Signaler un problème</span>
                </a>
                <a href="<?php echo e(route('citizen.invoices.index')); ?>" class="px-4 py-2 rounded-xl text-white/80 hover:text-white hover:bg-white/10 transition-all flex items-center gap-2">
                    <i data-lucide="file-text" class="w-4 h-4 text-teal-300"></i>
                    <span>Factures & consommation</span>
                </a>
                <a href="<?php echo e(route('citizen.notifications')); ?>" class="px-4 py-2 rounded-xl text-white/80 hover:text-white hover:bg-white/10 transition-all flex items-center gap-2">
                    <i data-lucide="bell" class="w-4 h-4 text-sky-300"></i>
                    <span>Alertes & coupures</span>
                </a>
            </nav>

            
            <div class="flex items-center gap-3">
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
        </div>
    </header>

    
    <div class="fixed bottom-0 left-0 right-0 z-50 glass-strong border-t border-white/20 px-2 py-2 flex justify-around sm:hidden">
        <a href="<?php echo e(route('citizen.dashboard')); ?>"
           class="flex flex-col items-center gap-0.5 px-3 py-1.5 rounded-xl text-white/70 hover:text-cyan-300 transition-colors">
            <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
            <span class="text-[9px] font-medium">Accueil</span>
        </a>
        <a href="<?php echo e(route('citizen.reports.create')); ?>"
           class="flex flex-col items-center gap-0.5 px-3 py-1.5 rounded-xl liquid-chip text-cyan-300">
            <i data-lucide="plus" class="w-5 h-5"></i>
            <span class="text-[9px] font-medium">Signaler</span>
        </a>
        <a href="<?php echo e(route('citizen.invoices.index')); ?>"
           class="flex flex-col items-center gap-0.5 px-3 py-1.5 rounded-xl text-white/70 hover:text-cyan-300 transition-colors">
            <i data-lucide="file-text" class="w-5 h-5"></i>
            <span class="text-[9px] font-medium">Factures</span>
        </a>
        <a href="<?php echo e(route('citizen.notifications')); ?>"
           class="flex flex-col items-center gap-0.5 px-3 py-1.5 rounded-xl text-white/70 hover:text-cyan-300 transition-colors">
            <i data-lucide="bell" class="w-5 h-5"></i>
            <span class="text-[9px] font-medium">Notifs</span>
        </a>
        <a href="<?php echo e(route('profile.show')); ?>"
           class="flex flex-col items-center gap-0.5 px-3 py-1.5 rounded-xl text-white/70 hover:text-cyan-300 transition-colors">
            <i data-lucide="user" class="w-5 h-5"></i>
            <span class="text-[9px] font-medium">Profil</span>
        </a>
    </div>

    <!-- MAIN CONTAINER (Full-Screen Layout Shell) -->
    <main class="w-full max-w-[1600px] mx-auto px-4 sm:px-8 py-8 pb-24 sm:pb-12 flex-1">
        <?php echo $__env->yieldContent('frontoffice-content'); ?>
        <?php echo $__env->yieldContent('content'); ?>
    </main>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    });
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>



<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\monta\Downloads\Aqua_Secure-main\Aqua_Secure-main\resources\views/layouts/frontoffice.blade.php ENDPATH**/ ?>