<?php $__env->startSection('content'); ?>
<div class="min-h-screen bg-[#04121b] text-white flex flex-col font-sans relative w-full">
    <!-- NAVBAR (Full Width Header Shell) -->
    <header class="sticky top-0 z-50 glass-strong border-b border-white/20 w-full" style="height: var(--navbar-height); padding: 0 var(--container-px); display:flex; align-items:center;">
        <div class="w-full max-w-[1600px] mx-auto flex items-center justify-between gap-4">
            
            
            <div class="flex items-center gap-3">
                <?php if (isset($component)) { $__componentOriginal5c84f04e4e4c3f6b2afa5416a6776687 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5c84f04e4e4c3f6b2afa5416a6776687 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.back-button','data' => ['fallback' => route('citizen.dashboard')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('back-button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['fallback' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('citizen.dashboard'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5c84f04e4e4c3f6b2afa5416a6776687)): ?>
<?php $attributes = $__attributesOriginal5c84f04e4e4c3f6b2afa5416a6776687; ?>
<?php unset($__attributesOriginal5c84f04e4e4c3f6b2afa5416a6776687); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5c84f04e4e4c3f6b2afa5416a6776687)): ?>
<?php $component = $__componentOriginal5c84f04e4e4c3f6b2afa5416a6776687; ?>
<?php unset($__componentOriginal5c84f04e4e4c3f6b2afa5416a6776687); ?>
<?php endif; ?>
                <a href="<?php echo e(route('citizen.dashboard')); ?>" class="flex items-center gap-2.5 group">
                    <div class="rounded-xl bg-gradient-to-br from-cyan-400 to-blue-600 flex items-center justify-center shadow-lg shadow-cyan-500/30 group-hover:scale-105 transition-transform" style="width:var(--navbar-logo-w);height:var(--navbar-logo-w);">
                        <i data-lucide="droplet" style="width:55%;height:55%;" class="text-white"></i>
                    </div>
                    <span class="font-display font-bold text-white tracking-tight hidden sm:block" style="font-size: clamp(1.1rem,1.4vw,1.5rem);">AquaSecure</span>
                    <div class="liquid-chip hidden sm:inline-flex">
                        <span class="w-1.5 h-1.5 rounded-full bg-cyan-400 animate-pulse"></span>
                        <span class="font-semibold text-white/90 uppercase tracking-wider" style="font-size:var(--font-size-badge);">Citoyen</span>
                    </div>
                </a>
            </div>

            
            <nav class="hidden md:flex items-center gap-1.5 glass p-1.5 rounded-2xl font-semibold" style="font-size:var(--font-size-nav);">
                <a href="<?php echo e(route('citizen.dashboard')); ?>" class="px-4 py-2 rounded-xl text-white/80 hover:text-white hover:bg-white/10 transition-all flex items-center gap-2">
                    <i data-lucide="layout-dashboard" style="width:var(--icon-sm);height:var(--icon-sm);" class="text-cyan-300"></i>
                    <span>Tableau de bord</span>
                </a>
                <a href="<?php echo e(route('incidents.index')); ?>" class="px-4 py-2 rounded-xl text-white/80 hover:text-white hover:bg-white/10 transition-all flex items-center gap-2">
                    <i data-lucide="alert-circle" style="width:var(--icon-sm);height:var(--icon-sm);" class="text-amber-300"></i>
                    <span>Mes incidents</span>
                </a>
                <a href="<?php echo e(route('citizen.invoices.index')); ?>" class="px-4 py-2 rounded-xl text-white/80 hover:text-white hover:bg-white/10 transition-all flex items-center gap-2">
                    <i data-lucide="file-text" style="width:var(--icon-sm);height:var(--icon-sm);" class="text-teal-300"></i>
                    <span>Factures & consommation</span>
                </a>
                <a href="<?php echo e(route('citizen.travaux.index')); ?>" class="px-4 py-2 rounded-xl text-white/80 hover:text-white hover:bg-white/10 transition-all flex items-center gap-2 <?php echo e(request()->routeIs('citizen.travaux.*') ? 'bg-white/10 text-white' : ''); ?>">
                    <i data-lucide="construction" style="width:var(--icon-sm);height:var(--icon-sm);" class="text-orange-300"></i>
                    <span>Travaux</span>
                </a>
                <a href="<?php echo e(route('citizen.projets.index')); ?>" class="px-4 py-2 rounded-xl text-white/80 hover:text-white hover:bg-white/10 transition-all flex items-center gap-2 <?php echo e(request()->routeIs('citizen.projets.*') ? 'bg-white/10 text-white' : ''); ?>">
                    <i data-lucide="briefcase" style="width:var(--icon-sm);height:var(--icon-sm);" class="text-purple-300"></i>
                    <span>Projets d'infrastructure</span>
                </a>
                <a href="<?php echo e(route('citizen.notifications')); ?>" class="px-4 py-2 rounded-xl text-white/80 hover:text-white hover:bg-white/10 transition-all flex items-center gap-2">
                    <i data-lucide="bell" style="width:var(--icon-sm);height:var(--icon-sm);" class="text-sky-300"></i>
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
        <a href="<?php echo e(route('incidents.index')); ?>"
           class="flex flex-col items-center gap-0.5 px-3 py-1.5 rounded-xl liquid-chip text-cyan-300">
            <i data-lucide="alert-circle" class="w-5 h-5"></i>
            <span class="text-[9px] font-medium">Incidents</span>
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
    <main class="fo-container flex-1" style="padding-top: var(--spacing-xl); padding-bottom: clamp(6rem, 10vw, 3rem);">
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



<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\monta\Downloads\DEVWAVE\Esprit-5TWIN4-DevWave-main (1)\Esprit-5TWIN4-DevWave-main\resources\views/layouts/frontoffice.blade.php ENDPATH**/ ?>