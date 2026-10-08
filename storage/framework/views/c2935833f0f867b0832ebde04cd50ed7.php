<?php $__env->startSection('content'); ?>
<div class="min-h-screen bg-gradient-to-br from-[#04121b] via-[#082236] to-[#04121b]">
    <div class="container mx-auto px-4 py-8">
        
        <div class="flex items-center justify-between mb-8">
            <a href="<?php echo e(route('landing')); ?>" class="liquid-tool text-cyan-300 hover:text-white flex items-center gap-2 text-xs font-semibold">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span>Retour à l'accueil</span>
            </a>
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-cyan-400 to-blue-600 flex items-center justify-center">
                    <i data-lucide="droplet" class="w-4 h-4 text-white"></i>
                </div>
                <span class="font-display font-bold text-white text-base">AquaSecure AI</span>
            </div>
        </div>

        <!-- Header -->
        <div class="text-center mb-12">
            <div class="inline-flex items-center justify-center w-20 h-20 rounded-3xl bg-gradient-to-br from-cyan-400 to-blue-600 shadow-xl shadow-cyan-500/20 mb-6">
                <i data-lucide="bot" class="w-10 h-10 text-white"></i>
            </div>
            <h1 class="text-4xl font-display font-extrabold text-white mb-4">AquaSecure AI Assistant</h1>
            <p class="text-lg text-cyan-100/70 max-w-2xl mx-auto font-medium">
                Votre assistant intelligent pour la gestion et le suivi de l'eau potable
            </p>
        </div>

        <!-- Features Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-12">
            <?php $__currentLoopData = [
                [
                    'icon' => 'droplet',
                    'title' => 'Qualité de l\'eau',
                    'desc' => 'Consultez les résultats de qualité de l\'eau disponibles pour votre zone',
                    'color' => 'cyan',
                    'example' => 'Quelle est la qualité de l\'eau dans ma zone ?'
                ],
                [
                    'icon' => 'wrench',
                    'title' => 'Signalement',
                    'desc' => 'Apprenez à signaler rapidement un problème',
                    'color' => 'blue',
                    'example' => 'Comment signaler un problème ?'
                ],
                [
                    'icon' => 'receipt',
                    'title' => 'Factures',
                    'desc' => 'Accédez à vos informations de facturation',
                    'color' => 'purple',
                    'example' => 'Consulter ma facture'
                ],
                [
                    'icon' => 'tool',
                    'title' => 'Maintenance',
                    'desc' => 'Informez-vous sur les interventions programmées',
                    'color' => 'pink',
                    'example' => 'Quand est la prochaine maintenance ?'
                ],
                [
                    'icon' => 'help-circle',
                    'title' => 'Aide générale',
                    'desc' => 'Obtenez de l\'aide sur l\'utilisation de la plateforme',
                    'color' => 'emerald',
                    'example' => 'Comment puis-je vous aider ?'
                ],
                [
                    'icon' => 'message-circle',
                    'title' => 'Questions libres',
                    'desc' => 'Posez n\'importe quelle question sur l\'eau',
                    'color' => 'indigo',
                    'example' => 'Parlez-moi de votre système'
                ],
            ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $feature): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="glass p-6 rounded-2xl hover:glass-strong transition-all hover:-translate-y-1">
                <div class="w-12 h-12 rounded-xl bg-<?php echo e($feature['color']); ?>-500/20 flex items-center justify-center mb-4">
                    <i data-lucide="<?php echo e($feature['icon']); ?>" class="w-6 h-6 text-<?php echo e($feature['color']); ?>-400"></i>
                </div>
                <h3 class="text-lg font-bold text-white mb-2"><?php echo e($feature['title']); ?></h3>
                <p class="text-sm text-slate-400 mb-4"><?php echo e($feature['desc']); ?></p>
                <button 
                    onclick="openAIAndAsk('<?php echo e($feature['example']); ?>')"
                    class="text-sm text-<?php echo e($feature['color']); ?>-400 hover:text-<?php echo e($feature['color']); ?>-300 font-medium flex items-center gap-1"
                >
                    Essayer
                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </button>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        <!-- Demo Section -->
        <?php if (isset($component)) { $__componentOriginaldae4cd48acb67888a4631e1ba48f2f93 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.card','data' => ['class' => 'max-w-4xl mx-auto']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'max-w-4xl mx-auto']); ?>
            <div class="text-center py-8">
                <h2 class="text-2xl font-bold text-white mb-4">Testez l'Assistant IA</h2>
                <p class="text-slate-400 mb-8">
                    Cliquez sur le bouton flottant en bas à droite ou essayez les exemples ci-dessus
                </p>
                
                <!-- Example Questions -->
                <div class="flex flex-wrap justify-center gap-3">
                    <?php $__currentLoopData = [
                        'Quelle est la qualité de l\'eau ?',
                        'Comment signaler une fuite ?',
                        'Ma facture du mois',
                        'Prochaine maintenance ?',
                        'J\'ai besoin d\'aide'
                    ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $question): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <button 
                        onclick="openAIAndAsk('<?php echo e($question); ?>')"
                        class="px-4 py-2 rounded-lg glass hover:glass-strong transition-all text-sm text-white hover:text-cyan-400"
                    >
                        <?php echo e($question); ?>

                    </button>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>

                <!-- CTA -->
                <div class="mt-12">
                    <button 
                        onclick="toggleAIAssistant()"
                        class="inline-flex items-center gap-3 px-8 py-4 rounded-xl bg-gradient-to-br from-purple-500 to-pink-600 hover:from-purple-600 hover:to-pink-700 transition-all text-white font-semibold shadow-lg hover:shadow-purple-500/50 hover:scale-105"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                        </svg>
                        Ouvrir l'Assistant IA
                    </button>
                </div>
            </div>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93)): ?>
<?php $attributes = $__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93; ?>
<?php unset($__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaldae4cd48acb67888a4631e1ba48f2f93)): ?>
<?php $component = $__componentOriginaldae4cd48acb67888a4631e1ba48f2f93; ?>
<?php unset($__componentOriginaldae4cd48acb67888a4631e1ba48f2f93); ?>
<?php endif; ?>

        <!-- Capabilities -->
        <div class="max-w-4xl mx-auto mt-12">
            <h2 class="text-2xl font-bold text-white text-center mb-8">Capacités de l'Assistant</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <?php $__currentLoopData = [
                    [
                        'title' => 'Réponses instantanées',
                        'desc' => 'Obtenez des réponses immédiates à vos questions sur la qualité de l\'eau, les factures et plus',
                        'icon' => 'zap'
                    ],
                    [
                        'title' => 'Disponible 24/7',
                        'desc' => 'L\'assistant IA est toujours disponible pour vous aider, jour et nuit',
                        'icon' => 'clock'
                    ],
                    [
                        'title' => 'Contexte personnalisé',
                        'desc' => 'Des réponses adaptées à votre zone et votre situation',
                        'icon' => 'user'
                    ],
                    [
                        'title' => 'Navigation intelligente',
                        'desc' => 'Guidage vers les bonnes sections de la plateforme',
                        'icon' => 'compass'
                    ],
                ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $capability): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="flex items-start gap-4 p-4 rounded-xl glass">
                    <div class="w-10 h-10 rounded-lg bg-purple-500/20 flex items-center justify-center flex-shrink-0">
                        <i data-lucide="<?php echo e($capability['icon']); ?>" class="w-5 h-5 text-purple-400"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-semibold text-white mb-1"><?php echo e($capability['title']); ?></h3>
                        <p class="text-sm text-slate-400"><?php echo e($capability['desc']); ?></p>
                    </div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>

        <!-- Back to Home -->
        <div class="text-center mt-12">
            <a href="<?php echo e(route('landing')); ?>" class="inline-flex items-center gap-2 text-cyan-400 hover:text-cyan-300 transition-colors">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                Retour à l'accueil
            </a>
        </div>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
    function openAIAndAsk(question) {
        // Open AI assistant if not open
        const panel = document.getElementById('ai-chat-panel');
        if (panel.classList.contains('hidden')) {
            toggleAIAssistant();
        }
        
        // Wait for panel to open, then send question
        setTimeout(() => {
            document.getElementById('ai-input').value = question;
            document.getElementById('ai-chat-form').dispatchEvent(new Event('submit'));
        }, 300);
    }
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\monta\Downloads\DEVWAVE\Esprit-5TWIN4-DevWave-main (1)\Esprit-5TWIN4-DevWave-main\resources\views\ai-demo.blade.php ENDPATH**/ ?>