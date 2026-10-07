<?php $__env->startSection('content'); ?>
<div class="min-h-screen bg-[#04121b] text-white flex flex-col font-sans relative">
    <!-- Top Navigation (Liquid Glass Theme) -->
    <header class="sticky top-0 z-50 glass-strong border-b border-white/20 px-4 sm:px-6 py-3">
        <div class="max-w-7xl mx-auto flex items-center justify-between gap-4">
            
            
            <div class="flex items-center gap-3">
                
                <?php if (isset($component)) { $__componentOriginal5c84f04e4e4c3f6b2afa5416a6776687 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5c84f04e4e4c3f6b2afa5416a6776687 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.back-button','data' => ['fallback' => session('user.role') === 'technician' ? route('technician.dashboard') : route('manager.dashboard')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('back-button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['fallback' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(session('user.role') === 'technician' ? route('technician.dashboard') : route('manager.dashboard'))]); ?>
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
                <a href="<?php echo e(route('manager.dashboard')); ?>" class="flex items-center gap-2.5 group">
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-cyan-400 to-blue-600 flex items-center justify-center shadow-lg shadow-cyan-500/30 group-hover:scale-105 transition-transform">
                        <i data-lucide="droplet" class="w-5 h-5 text-white"></i>
                    </div>
                    <span class="font-display font-semibold text-white text-lg tracking-tight hidden sm:inline-block">AquaSecure</span>
                    <div class="liquid-chip">
                        <span class="w-1.5 h-1.5 rounded-full bg-cyan-400 animate-pulse"></span>
                        <span class="text-[11px] font-medium text-white/90 uppercase tracking-wider">Gestionnaire</span>
                    </div>
                </a>
            </div>

            
            <div class="hidden md:flex items-center gap-1.5 glass p-1.5 rounded-2xl text-xs font-medium">
                <a href="<?php echo e(route('manager.dashboard')); ?>" class="px-3.5 py-1.5 rounded-xl text-white/80 hover:text-white hover:bg-white/10 transition-all">Tableau de bord</a>
                <a href="<?php echo e(route('manager.map')); ?>" class="px-3.5 py-1.5 rounded-xl text-white/80 hover:text-white hover:bg-white/10 transition-all">Carte du réseau</a>
                <a href="<?php echo e(route('manager.quality')); ?>" class="px-3.5 py-1.5 rounded-xl text-white/80 hover:text-white hover:bg-white/10 transition-all">Qualité de l’eau</a>
                <a href="<?php echo e(route('manager.incidents')); ?>" class="px-3.5 py-1.5 rounded-xl text-white/80 hover:text-white hover:bg-white/10 transition-all">Incidents</a>
                <a href="<?php echo e(route('manager.projects')); ?>" class="px-3.5 py-1.5 rounded-xl text-white/80 hover:text-white hover:bg-white/10 transition-all">Projets</a>
                <a href="<?php echo e(route('manager.budget')); ?>" class="px-3.5 py-1.5 rounded-xl text-white/80 hover:text-white hover:bg-white/10 transition-all">Budget</a>
                <a href="<?php echo e(route('manager.techniciens.index')); ?>" class="px-3.5 py-1.5 rounded-xl text-white/80 hover:text-white hover:bg-white/10 transition-all <?php echo e(request()->routeIs('manager.techniciens.*', 'manager.interventions.*') ? 'bg-white/10 text-white' : ''); ?>">Maintenance</a>
            </div>

            
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

    <!-- Tab Bar (if provided) -->
    <?php if (! empty(trim($__env->yieldContent('tabs')))): ?>
    <div class="sticky top-[61px] z-40 glass border-b border-white/15 px-4 py-2 flex gap-2 overflow-x-auto">
        <?php echo $__env->yieldContent('tabs'); ?>
    </div>
    <?php endif; ?>

    <!-- Main Content -->
    <div class="flex-1 max-w-[1550px] w-full mx-auto px-4 sm:px-8 py-8 pb-12">
        <?php echo $__env->yieldContent('manager-content'); ?>
    </div>
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

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Ranim\Documents\GitHub\Esprit-5TWIN4-DevWave\resources\views/layouts/manager.blade.php ENDPATH**/ ?>