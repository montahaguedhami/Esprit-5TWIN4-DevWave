<?php $__env->startSection('content'); ?>
<div class="min-h-screen relative overflow-hidden">
    <!-- Rain Effect -->
    <?php if (isset($component)) { $__componentOriginalcd745ee42ad18bfff762e4894e7b26db = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcd745ee42ad18bfff762e4894e7b26db = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.rain-effect','data' => ['count' => 72]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('rain-effect'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['count' => 72]); ?>
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
    
    <!-- Wave Background -->
    <?php if (isset($component)) { $__componentOriginal2c83e774ce36863e885a476fd5a9b327 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2c83e774ce36863e885a476fd5a9b327 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.wave-background','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('wave-background'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2c83e774ce36863e885a476fd5a9b327)): ?>
<?php $attributes = $__attributesOriginal2c83e774ce36863e885a476fd5a9b327; ?>
<?php unset($__attributesOriginal2c83e774ce36863e885a476fd5a9b327); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2c83e774ce36863e885a476fd5a9b327)): ?>
<?php $component = $__componentOriginal2c83e774ce36863e885a476fd5a9b327; ?>
<?php unset($__componentOriginal2c83e774ce36863e885a476fd5a9b327); ?>
<?php endif; ?>
    
    <!-- Gradient Overlay -->
    <div class="absolute inset-0 bg-gradient-to-b from-blue-950/40 via-transparent to-slate-950/70 pointer-events-none"></div>

    <!-- Navigation -->
    <nav class="fixed top-0 left-0 right-0 z-50 px-6 py-4 flex items-center justify-between">
        <a href="<?php echo e(route('landing')); ?>" class="flex items-center gap-3 group">
            <span class="w-10 h-10 rounded-xl bg-gradient-to-br from-cyan-400 to-blue-600 flex items-center justify-center shadow-lg shadow-cyan-500/30 group-hover:scale-105 transition-transform">
                <i data-lucide="droplet" class="w-6 h-6 text-white"></i>
            </span>
            <span class="font-display text-xl font-bold text-white">AquaSecure</span>
        </a>
        <button onclick="toggleTheme()" class="glass p-2.5 rounded-xl text-cyan-200 hover:text-white transition-colors" aria-label="Changer de thème">
            <svg class="w-5 h-5 sun-icon hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
            </svg>
            <svg class="w-5 h-5 moon-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
            </svg>
        </button>
    </nav>

    <!-- Main Content -->
    <main class="relative z-10 min-h-screen flex items-center justify-center px-4 py-28">
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
    // Update theme icons
    function updateThemeIcons() {
        const theme = document.documentElement.getAttribute('data-theme');
        const sunIcon = document.querySelector('.sun-icon');
        const moonIcon = document.querySelector('.moon-icon');
        
        if (theme === 'light') {
            sunIcon?.classList.remove('hidden');
            moonIcon?.classList.add('hidden');
        } else {
            sunIcon?.classList.add('hidden');
            moonIcon?.classList.remove('hidden');
        }
    }
    
    updateThemeIcons();
    
    // Override toggleTheme to update icons
    const originalToggleTheme = window.toggleTheme;
    window.toggleTheme = function() {
        originalToggleTheme();
        updateThemeIcons();
    };
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\ghada\Desktop\5ème\projet_Laravel\aquasecure\resources\views/layouts/auth.blade.php ENDPATH**/ ?>