<?php $__env->startSection('content'); ?>
<div class="min-h-screen bg-[#04121b] text-white relative overflow-hidden flex flex-col justify-center">
    <!-- Atmospheric Rain & Vignette Overlay -->
    <?php if (isset($component)) { $__componentOriginalcd745ee42ad18bfff762e4894e7b26db = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcd745ee42ad18bfff762e4894e7b26db = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.rain-effect','data' => ['count' => 48]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('rain-effect'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['count' => 48]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalcd745ee42ad18bfff762e4894e7b26db)): ?>
<?php $attributes = $__attributesOriginalcd745ee42ad18bfff762e4894e7b26db; ?>
<?php unset($__attributesOriginalcd745ee42ad18bfff762e4894e7b26db); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalcd745ee42ad18bfff762e4894e7b26db)): ?>
<?php $component = $__componentOriginalcd745ee42ad18bfff762e4894e7b26db; ?>
<?php unset($__componentOriginalcd745ee42ad18bfff762e4894e7b26db); ?>
<?php endif; ?>
    <div class="absolute inset-0 bg-gradient-to-b from-[#04121b]/80 via-transparent to-[#04121b]/90 pointer-events-none"></div>

    <!-- Navigation -->
    <nav class="fixed top-0 left-0 right-0 z-50 px-6 py-4 flex items-center justify-between">
        <a href="<?php echo e(route('landing')); ?>" class="flex items-center gap-3 group">
            <span class="w-10 h-10 rounded-xl bg-gradient-to-br from-cyan-400 to-blue-600 flex items-center justify-center shadow-lg shadow-cyan-500/30 group-hover:scale-105 transition-transform">
                <i data-lucide="droplet" class="w-6 h-6 text-white"></i>
            </span>
            <span class="font-display text-xl font-semibold text-white tracking-tight">AquaSecure</span>
        </a>
        <a href="<?php echo e(route('landing')); ?>" class="liquid-tool text-white/80 hover:text-white" aria-label="Retour à l'accueil">
            <i data-lucide="x" class="w-5 h-5"></i>
        </a>
    </nav>

    <!-- Main Content Shell -->
    <main class="relative z-10 min-h-screen flex items-center justify-center px-4 sm:px-8 py-12 w-full max-w-[1500px] mx-auto">
        <?php echo $__env->yieldContent('auth-content'); ?>
    </main>

    <!-- Toast Notifications -->
    <?php if (isset($component)) { $__componentOriginal339c7fedf680433726dbafc2f156956f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal339c7fedf680433726dbafc2f156956f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.toast','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.toast'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal339c7fedf680433726dbafc2f156956f)): ?>
<?php $attributes = $__attributesOriginal339c7fedf680433726dbafc2f156956f; ?>
<?php unset($__attributesOriginal339c7fedf680433726dbafc2f156956f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal339c7fedf680433726dbafc2f156956f)): ?>
<?php $component = $__componentOriginal339c7fedf680433726dbafc2f156956f; ?>
<?php unset($__componentOriginal339c7fedf680433726dbafc2f156956f); ?>
<?php endif; ?>
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


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Ranim\Documents\GitHub\Esprit-5TWIN4-DevWave\resources\views/layouts/auth.blade.php ENDPATH**/ ?>