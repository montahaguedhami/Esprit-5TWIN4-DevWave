<?php
    use App\Data\PlaceholderData;
    $zones = PlaceholderData::zones();
?>

<?php $__env->startSection('auth-content'); ?>
<div class="w-full max-w-5xl grid lg:grid-cols-[1.05fr_0.95fr] gap-8 items-stretch">
    <!-- Left Section - Info (Desktop only) -->
    <section class="hidden lg:flex flex-col justify-between glass p-9 animate-fade-in-up">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-cyan-400/10 border border-cyan-400/25 text-cyan-200 text-xs font-semibold mb-8">
                <span class="w-2 h-2 rounded-full bg-teal-400 pulse-glow"></span>
                Plateforme nationale de surveillance
            </div>
            <h1 class="text-4xl xl:text-5xl font-display font-bold text-white leading-tight mb-6">
                Chaque goutte compte.
                <span class="block text-gradient">Chaque intervention aussi.</span>
            </h1>
            <p class="text-cyan-100/65 leading-relaxed max-w-lg">
                AquaSecure relie les citoyens, techniciens et gestionnaires autour d'un réseau d'eau potable plus sûr, plus transparent et plus résilient.
            </p>
        </div>

        <div class="grid grid-cols-3 gap-3 mt-12">
            <?php $__currentLoopData = [
                ['value' => '14', 'label' => 'zones suivies', 'icon' => 'M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                ['value' => '24/7', 'label' => 'surveillance', 'icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'],
                ['value' => '100%', 'label' => 'transparent', 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
            ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="glass-strong p-4 rounded-2xl">
                <div class="text-cyan-300 mb-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?php echo e($stat['icon']); ?>"></path>
                    </svg>
                </div>
                <div class="text-xl font-display font-bold text-white"><?php echo e($stat['value']); ?></div>
                <div class="text-[11px] text-cyan-100/50 mt-1"><?php echo e($stat['label']); ?></div>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </section>

    <!-- Right Section - Login Form -->
    <section class="glass-strong p-6 sm:p-8 animate-fade-in-up" style="animation-delay: 0.12s">
        <div class="flex items-center justify-between mb-7">
            <div>
                <p class="text-xs font-semibold tracking-wider text-cyan-300 uppercase">Accès sécurisé</p>
                <h2 class="text-2xl font-display font-bold text-white mt-1">Heureux de vous revoir</h2>
            </div>
            <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-cyan-500/20 to-blue-600/20 border border-cyan-400/25 flex items-center justify-center">
                <svg class="w-5 h-5 text-cyan-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                </svg>
            </div>
        </div>

        <!-- Mode Toggle Tabs -->
        <div class="grid grid-cols-3 gap-1 p-1 rounded-xl bg-slate-950/30 border border-white/5 mb-7">
            <a href="<?php echo e(route('auth.login')); ?>" class="flex items-center justify-center gap-1.5 rounded-lg px-2 py-2 text-[11px] sm:text-xs font-semibold transition-all bg-gradient-to-r from-cyan-500 to-blue-600 text-white shadow-lg shadow-cyan-500/20">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                </svg>
                <span class="hidden sm:inline">Connexion</span>
            </a>
            <a href="<?php echo e(route('auth.register')); ?>" class="flex items-center justify-center gap-1.5 rounded-lg px-2 py-2 text-[11px] sm:text-xs font-semibold transition-all text-cyan-100/45 hover:text-cyan-100/80">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                </svg>
                <span class="hidden sm:inline">Inscription</span>
            </a>
            <a href="<?php echo e(route('auth.forgot-password')); ?>" class="flex items-center justify-center gap-1.5 rounded-lg px-2 py-2 text-[11px] sm:text-xs font-semibold transition-all text-cyan-100/45 hover:text-cyan-100/80">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                </svg>
                <span class="hidden sm:inline">Mot de passe</span>
            </a>
        </div>

        <!-- Login Form -->
        <form method="POST" action="<?php echo e(route('login.post')); ?>" class="space-y-4">
            <?php echo csrf_field(); ?>
            
            <div>
                <label class="text-xs font-semibold text-cyan-100/70 mb-2 block">Adresse email</label>
                <div class="relative">
                    <input type="email" name="email" required placeholder="vous@exemple.tn" class="w-full bg-slate-950/35 border border-cyan-400/15 rounded-xl pl-11 pr-4 py-3 text-sm text-white placeholder:text-cyan-100/30 focus:outline-none focus:border-cyan-400/50 transition-colors" />
                    <svg class="w-4 h-4 text-cyan-100/30 absolute left-4 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                </div>
            </div>

            <div>
                <label class="text-xs font-semibold text-cyan-100/70 mb-2 block">Mot de passe</label>
                <div class="relative">
                    <input type="password" name="password" id="password" required minlength="6" placeholder="••••••••" class="w-full bg-slate-950/35 border border-cyan-400/15 rounded-xl pl-11 pr-11 py-3 text-sm text-white placeholder:text-cyan-100/30 focus:outline-none focus:border-cyan-400/50 transition-colors" />
                    <svg class="w-4 h-4 text-cyan-100/30 absolute left-4 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                    <button type="button" onclick="togglePasswordVisibility()" class="text-cyan-100/35 hover:text-cyan-300 absolute right-4 top-1/2 -translate-y-1/2" aria-label="Afficher le mot de passe">
                        <svg class="w-4 h-4 eye-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                        <svg class="w-4 h-4 eye-off-icon hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <button type="button" onclick="window.location.href='<?php echo e(route('auth.forgot-password')); ?>'" class="text-right text-xs text-cyan-300/80 hover:text-cyan-200 transition-colors block w-full">
                Mot de passe oublié ?
            </button>

            <?php if (isset($component)) { $__componentOriginal31327652ba86dff3ae51860919901558 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal31327652ba86dff3ae51860919901558 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ripple-button','data' => ['type' => 'submit','size' => 'lg','class' => 'w-full flex items-center justify-center gap-2']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ripple-button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'submit','size' => 'lg','class' => 'w-full flex items-center justify-center gap-2']); ?>
                Se connecter
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal31327652ba86dff3ae51860919901558)): ?>
<?php $attributes = $__attributesOriginal31327652ba86dff3ae51860919901558; ?>
<?php unset($__attributesOriginal31327652ba86dff3ae51860919901558); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal31327652ba86dff3ae51860919901558)): ?>
<?php $component = $__componentOriginal31327652ba86dff3ae51860919901558; ?>
<?php unset($__componentOriginal31327652ba86dff3ae51860919901558); ?>
<?php endif; ?>
        </form>

        <!-- Demo Accounts -->
        <div class="mt-7">
            <div class="flex items-center gap-3 mb-4">
                <span class="h-px flex-1 bg-white/10"></span>
                <span class="text-[11px] uppercase tracking-wider text-cyan-100/35">Comptes de démonstration</span>
                <span class="h-px flex-1 bg-white/10"></span>
            </div>
            <div class="grid grid-cols-2 gap-2">
                <?php $__currentLoopData = [
                    ['label' => 'Citoyenne', 'email' => 'citoyen@aquasecure.tn', 'role' => 'Front', 'desc' => 'Espace citoyen'],
                    ['label' => 'Technicien', 'email' => 'amira@aquasecure.tn', 'role' => 'Back', 'desc' => 'Interventions terrain'],
                    ['label' => 'Gestionnaire', 'email' => 'gestionnaire@aquasecure.tn', 'role' => 'Back', 'desc' => 'Back office réseau'],
                    ['label' => 'Administrateur', 'email' => 'admin@aquasecure.tn', 'role' => 'Back', 'desc' => 'Administration complète'],
                ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $account): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <button onclick="fillDemo('<?php echo e($account['email']); ?>')" class="text-left glass p-3 rounded-xl hover:border-cyan-400/40 hover:bg-cyan-400/5 transition-all">
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-xs font-semibold text-white"><?php echo e($account['label']); ?></span>
                        <?php if (isset($component)) { $__componentOriginal2ddbc40e602c342e508ac696e52f8719 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2ddbc40e602c342e508ac696e52f8719 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.badge','data' => ['color' => '#2dd4bf']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['color' => '#2dd4bf']); ?><?php echo e($account['role']); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2ddbc40e602c342e508ac696e52f8719)): ?>
<?php $attributes = $__attributesOriginal2ddbc40e602c342e508ac696e52f8719; ?>
<?php unset($__attributesOriginal2ddbc40e602c342e508ac696e52f8719); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2ddbc40e602c342e508ac696e52f8719)): ?>
<?php $component = $__componentOriginal2ddbc40e602c342e508ac696e52f8719; ?>
<?php unset($__componentOriginal2ddbc40e602c342e508ac696e52f8719); ?>
<?php endif; ?>
                    </div>
                    <span class="block text-[10px] text-cyan-100/40 mt-1 truncate"><?php echo e($account['desc']); ?></span>
                </button>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
            <p class="text-[11px] text-cyan-100/35 mt-3">Mot de passe commun : <span class="text-cyan-300 font-semibold">demo123</span></p>
        </div>
    </section>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
    function togglePasswordVisibility() {
        const input = document.getElementById('password');
        const eyeIcon = document.querySelector('.eye-icon');
        const eyeOffIcon = document.querySelector('.eye-off-icon');
        
        if (input.type === 'password') {
            input.type = 'text';
            eyeIcon.classList.add('hidden');
            eyeOffIcon.classList.remove('hidden');
        } else {
            input.type = 'password';
            eyeIcon.classList.remove('hidden');
            eyeOffIcon.classList.add('hidden');
        }
    }
    
    function fillDemo(email) {
        document.querySelector('input[name="email"]').value = email;
        document.querySelector('input[name="password"]').value = 'demo123';
        showToast('Compte prérempli avec succès', 'info');
    }
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.auth', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\ghada\Desktop\5ème\projet_Laravel\aquasecure\resources\views/auth/login.blade.php ENDPATH**/ ?>