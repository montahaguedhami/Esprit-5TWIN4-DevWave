<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>" data-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

    <title><?php echo e($title ?? 'AquaSecure'); ?> - Surveillance intelligente des infrastructures d'eau potable</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <!-- Styles -->
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- Additional Styles -->
    <?php echo $__env->yieldPushContent('styles'); ?>
</head>
<body class="antialiased">
    <div id="app">
        <?php echo $__env->yieldContent('content'); ?>
    </div>

    <!-- AI Assistant (Global) -->
    <?php if (isset($component)) { $__componentOriginal251385c7632efe222fb0f2f6aa10f34b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal251385c7632efe222fb0f2f6aa10f34b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ai-assistant','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ai-assistant'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal251385c7632efe222fb0f2f6aa10f34b)): ?>
<?php $attributes = $__attributesOriginal251385c7632efe222fb0f2f6aa10f34b; ?>
<?php unset($__attributesOriginal251385c7632efe222fb0f2f6aa10f34b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal251385c7632efe222fb0f2f6aa10f34b)): ?>
<?php $component = $__componentOriginal251385c7632efe222fb0f2f6aa10f34b; ?>
<?php unset($__componentOriginal251385c7632efe222fb0f2f6aa10f34b); ?>
<?php endif; ?>

    <!-- Toast Container -->
    <div id="toast-container" class="fixed top-6 right-6 z-[100] flex flex-col gap-3 max-w-sm"></div>

    <!-- Route Sweep Effect -->
    <div class="route-sweep" aria-hidden="true">
        <span></span>
        <span></span>
        <span></span>
    </div>

    <!-- Scripts -->
    <?php echo $__env->yieldPushContent('scripts'); ?>

    <script>
        // Theme management
        const theme = localStorage.getItem('aquasecure-theme') || 'dark';
        document.documentElement.setAttribute('data-theme', theme);

        function toggleTheme() {
            const currentTheme = document.documentElement.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', newTheme);
            localStorage.setItem('aquasecure-theme', newTheme);
        }

        // Toast notification system
        window.showToast = function(message, type = 'info') {
            const container = document.getElementById('toast-container');
            const id = 'toast-' + Date.now();
            
            const icons = {
                success: `<svg class="w-5 h-5 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>`,
                error: `<svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>`,
                info: `<svg class="w-5 h-5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>`
            };
            
            const borders = {
                success: 'border-teal-400/40',
                error: 'border-red-400/40',
                info: 'border-cyan-400/40'
            };
            
            const toast = document.createElement('div');
            toast.id = id;
            toast.className = `toast-drop glass-strong px-5 py-4 flex items-center gap-3 border ${borders[type]} rounded-2xl`;
            toast.innerHTML = `
                ${icons[type]}
                <p class="text-sm text-white/90 flex-1">${message}</p>
                <button onclick="document.getElementById('${id}').remove()" class="text-white/40 hover:text-white/80 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            `;
            
            container.appendChild(toast);
            
            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateX(100%)';
                setTimeout(() => toast.remove(), 300);
            }, 5000);
        };

        // Initialize Lucide icons
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });
    </script>
</body>
</html>
<?php /**PATH C:\Users\ghada\Desktop\5ème\projet_Laravel\aquasecure\resources\views/layouts/app.blade.php ENDPATH**/ ?>