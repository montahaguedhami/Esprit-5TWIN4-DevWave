<?php $__env->startSection('title', 'Signaler un problème'); ?>

<?php
    use App\Data\PlaceholderData;
    $zones = PlaceholderData::zones();
?>

<?php $__env->startSection('content'); ?>
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="flex items-center gap-4 mb-8">
        <a href="<?php echo e(route('citizen.dashboard')); ?>" class="w-10 h-10 rounded-xl glass flex items-center justify-center hover:border-cyan-400/40 transition-colors">
            <i data-lucide="arrow-left" class="w-5 h-5 text-cyan-300"></i>
        </a>
        <div>
            <h1 class="text-3xl font-display font-bold text-white">Signaler un problème</h1>
            <p class="text-cyan-100/60 text-sm mt-1">Aidez-nous à maintenir la qualité du service</p>
        </div>
    </div>

    <!-- Multi-step Form -->
    <div class="max-w-3xl mx-auto">
        <!-- Progress Steps -->
        <div class="glass-strong p-6 rounded-2xl mb-6">
            <div class="flex items-center justify-between">
                <?php $__currentLoopData = [
                    ['num' => 1, 'label' => 'Type'],
                    ['num' => 2, 'label' => 'Localisation'],
                    ['num' => 3, 'label' => 'Détails'],
                    ['num' => 4, 'label' => 'Confirmation'],
                ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $step): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="flex items-center <?php echo e($index < 3 ? 'flex-1' : ''); ?>">
                    <div class="flex flex-col items-center">
                        <div class="step-circle w-10 h-10 rounded-full flex items-center justify-center font-semibold transition-all <?php echo e($index === 0 ? 'bg-gradient-to-r from-cyan-500 to-blue-600 text-white' : 'bg-slate-800/50 text-cyan-100/40'); ?>" data-step="<?php echo e($step['num']); ?>">
                            <?php echo e($step['num']); ?>

                        </div>
                        <span class="text-xs text-cyan-100/60 mt-2 hidden sm:block"><?php echo e($step['label']); ?></span>
                    </div>
                    <?php if($index < 3): ?>
                    <div class="step-line flex-1 h-0.5 mx-3 <?php echo e($index === 0 ? 'bg-slate-800/50' : 'bg-slate-800/50'); ?>"></div>
                    <?php endif; ?>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>

        <!-- Form Steps -->
        <form id="reportForm" method="POST" action="<?php echo e(route('citizen.reports.store')); ?>" enctype="multipart/form-data">
            <?php echo csrf_field(); ?>

            <!-- Step 1: Problem Type -->
            <div class="form-step" data-step="1">
                <?php if (isset($component)) { $__componentOriginaldae4cd48acb67888a4631e1ba48f2f93 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.card','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
                    <h3 class="text-xl font-display font-bold text-white mb-4">Quel type de problème signalez-vous ?</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <?php $__currentLoopData = [
                            ['type' => 'leak', 'icon' => 'droplet', 'title' => 'Fuite d\'eau', 'desc' => 'Fuite visible sur la voie publique'],
                            ['type' => 'quality', 'icon' => 'flask', 'title' => 'Qualité de l\'eau', 'desc' => 'Eau trouble, odeur ou goût inhabituel'],
                            ['type' => 'pressure', 'icon' => 'gauge', 'title' => 'Pression insuffisante', 'desc' => 'Débit faible ou irrégulier'],
                            ['type' => 'outage', 'icon' => 'power-off', 'title' => 'Coupure d\'eau', 'desc' => 'Absence totale d\'eau'],
                            ['type' => 'noise', 'icon' => 'volume-2', 'title' => 'Bruit anormal', 'desc' => 'Sifflement ou vibration dans les tuyaux'],
                            ['type' => 'other', 'icon' => 'help-circle', 'title' => 'Autre', 'desc' => 'Un problème non listé ci-dessus'],
                        ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $problem): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <label class="problem-type-card glass p-5 rounded-xl cursor-pointer hover:border-cyan-400/40 transition-all relative">
                            <input type="radio" name="type" value="<?php echo e($problem['type']); ?>" class="peer hidden" required>
                            <div class="peer-checked:border-cyan-400 peer-checked:bg-cyan-500/5 absolute inset-0 border-2 border-transparent rounded-xl transition-all"></div>
                            <div class="relative">
                                <i data-lucide="<?php echo e($problem['icon']); ?>" class="w-8 h-8 text-cyan-400 mb-3"></i>
                                <h4 class="font-semibold text-white mb-1"><?php echo e($problem['title']); ?></h4>
                                <p class="text-xs text-cyan-100/60"><?php echo e($problem['desc']); ?></p>
                            </div>
                        </label>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                    <div class="flex justify-end mt-6">
                        <?php if (isset($component)) { $__componentOriginala8bb031a483a05f647cb99ed3a469847 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala8bb031a483a05f647cb99ed3a469847 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.button','data' => ['onclick' => 'nextStep()','type' => 'button','iconRight' => 'arrow-right']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['onclick' => 'nextStep()','type' => 'button','icon-right' => 'arrow-right']); ?>
                            Continuer
                         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala8bb031a483a05f647cb99ed3a469847)): ?>
<?php $attributes = $__attributesOriginala8bb031a483a05f647cb99ed3a469847; ?>
<?php unset($__attributesOriginala8bb031a483a05f647cb99ed3a469847); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala8bb031a483a05f647cb99ed3a469847)): ?>
<?php $component = $__componentOriginala8bb031a483a05f647cb99ed3a469847; ?>
<?php unset($__componentOriginala8bb031a483a05f647cb99ed3a469847); ?>
<?php endif; ?>
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
            </div>

            <!-- Step 2: Location -->
            <div class="form-step hidden" data-step="2">
                <?php if (isset($component)) { $__componentOriginaldae4cd48acb67888a4631e1ba48f2f93 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.card','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
                    <h3 class="text-xl font-display font-bold text-white mb-4">Où se situe le problème ?</h3>
                    
                    <div class="space-y-4">
                        <?php if (isset($component)) { $__componentOriginal7041cc63efd62f0450fe4bb37aadf484 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal7041cc63efd62f0450fe4bb37aadf484 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.forms.select','data' => ['name' => 'zone','label' => 'Zone','required' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('forms.select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'zone','label' => 'Zone','required' => true]); ?>
                            <?php $__currentLoopData = $zones; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $zone): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($zone['id']); ?>"><?php echo e($zone['emoji']); ?> <?php echo e($zone['name']); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal7041cc63efd62f0450fe4bb37aadf484)): ?>
<?php $attributes = $__attributesOriginal7041cc63efd62f0450fe4bb37aadf484; ?>
<?php unset($__attributesOriginal7041cc63efd62f0450fe4bb37aadf484); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal7041cc63efd62f0450fe4bb37aadf484)): ?>
<?php $component = $__componentOriginal7041cc63efd62f0450fe4bb37aadf484; ?>
<?php unset($__componentOriginal7041cc63efd62f0450fe4bb37aadf484); ?>
<?php endif; ?>

                        <?php if (isset($component)) { $__componentOriginal4fb6044c7ed6b655352043ff774efcd0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4fb6044c7ed6b655352043ff774efcd0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.forms.input','data' => ['name' => 'address','label' => 'Adresse complète','icon' => 'map-pin','placeholder' => 'Ex: 42 Rue Habib Bourguiba','required' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('forms.input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'address','label' => 'Adresse complète','icon' => 'map-pin','placeholder' => 'Ex: 42 Rue Habib Bourguiba','required' => true]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal4fb6044c7ed6b655352043ff774efcd0)): ?>
<?php $attributes = $__attributesOriginal4fb6044c7ed6b655352043ff774efcd0; ?>
<?php unset($__attributesOriginal4fb6044c7ed6b655352043ff774efcd0); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal4fb6044c7ed6b655352043ff774efcd0)): ?>
<?php $component = $__componentOriginal4fb6044c7ed6b655352043ff774efcd0; ?>
<?php unset($__componentOriginal4fb6044c7ed6b655352043ff774efcd0); ?>
<?php endif; ?>

                        <div>
                            <label class="text-xs font-semibold text-cyan-100/70 mb-2 block">Point de repère (optionnel)</label>
                            <input 
                                type="text" 
                                name="landmark" 
                                placeholder="Ex: À côté de la pharmacie centrale"
                                class="w-full bg-slate-950/35 border border-cyan-400/15 rounded-xl px-4 py-3 text-sm text-white placeholder:text-cyan-100/30 focus:outline-none focus:border-cyan-400/50 transition-colors"
                            />
                        </div>
                    </div>

                    <div class="flex justify-between mt-6">
                        <?php if (isset($component)) { $__componentOriginala8bb031a483a05f647cb99ed3a469847 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala8bb031a483a05f647cb99ed3a469847 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.button','data' => ['onclick' => 'previousStep()','type' => 'button','variant' => 'outline','icon' => 'arrow-left']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['onclick' => 'previousStep()','type' => 'button','variant' => 'outline','icon' => 'arrow-left']); ?>
                            Retour
                         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala8bb031a483a05f647cb99ed3a469847)): ?>
<?php $attributes = $__attributesOriginala8bb031a483a05f647cb99ed3a469847; ?>
<?php unset($__attributesOriginala8bb031a483a05f647cb99ed3a469847); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala8bb031a483a05f647cb99ed3a469847)): ?>
<?php $component = $__componentOriginala8bb031a483a05f647cb99ed3a469847; ?>
<?php unset($__componentOriginala8bb031a483a05f647cb99ed3a469847); ?>
<?php endif; ?>
                        <?php if (isset($component)) { $__componentOriginala8bb031a483a05f647cb99ed3a469847 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala8bb031a483a05f647cb99ed3a469847 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.button','data' => ['onclick' => 'nextStep()','type' => 'button','iconRight' => 'arrow-right']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['onclick' => 'nextStep()','type' => 'button','icon-right' => 'arrow-right']); ?>
                            Continuer
                         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala8bb031a483a05f647cb99ed3a469847)): ?>
<?php $attributes = $__attributesOriginala8bb031a483a05f647cb99ed3a469847; ?>
<?php unset($__attributesOriginala8bb031a483a05f647cb99ed3a469847); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala8bb031a483a05f647cb99ed3a469847)): ?>
<?php $component = $__componentOriginala8bb031a483a05f647cb99ed3a469847; ?>
<?php unset($__componentOriginala8bb031a483a05f647cb99ed3a469847); ?>
<?php endif; ?>
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
            </div>

            <!-- Step 3: Details -->
            <div class="form-step hidden" data-step="3">
                <?php if (isset($component)) { $__componentOriginaldae4cd48acb67888a4631e1ba48f2f93 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.card','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
                    <h3 class="text-xl font-display font-bold text-white mb-4">Décrivez le problème en détail</h3>
                    
                    <div class="space-y-4">
                        <?php if (isset($component)) { $__componentOriginalea7b7095850fe8bc9657025b89ccf5a5 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalea7b7095850fe8bc9657025b89ccf5a5 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.forms.textarea','data' => ['name' => 'description','label' => 'Description','rows' => '5','maxlength' => '500','placeholder' => 'Décrivez le problème observé, quand l\'avez-vous remarqué, quelle est sa gravité...','required' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('forms.textarea'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'description','label' => 'Description','rows' => '5','maxlength' => '500','placeholder' => 'Décrivez le problème observé, quand l\'avez-vous remarqué, quelle est sa gravité...','required' => true]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalea7b7095850fe8bc9657025b89ccf5a5)): ?>
<?php $attributes = $__attributesOriginalea7b7095850fe8bc9657025b89ccf5a5; ?>
<?php unset($__attributesOriginalea7b7095850fe8bc9657025b89ccf5a5); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalea7b7095850fe8bc9657025b89ccf5a5)): ?>
<?php $component = $__componentOriginalea7b7095850fe8bc9657025b89ccf5a5; ?>
<?php unset($__componentOriginalea7b7095850fe8bc9657025b89ccf5a5); ?>
<?php endif; ?>

                        <div>
                            <label class="text-xs font-semibold text-cyan-100/70 mb-2 block">
                                Niveau d'urgence
                            </label>
                            <div class="grid grid-cols-3 gap-3">
                                <?php $__currentLoopData = [
                                    ['value' => 'low', 'label' => 'Faible', 'color' => 'emerald'],
                                    ['value' => 'medium', 'label' => 'Moyen', 'color' => 'amber'],
                                    ['value' => 'high', 'label' => 'Urgent', 'color' => 'rose'],
                                ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $priority): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <label class="priority-card glass p-4 rounded-xl cursor-pointer hover:border-cyan-400/40 transition-all relative text-center">
                                    <input type="radio" name="priority" value="<?php echo e($priority['value']); ?>" class="peer hidden" required>
                                    <div class="peer-checked:border-<?php echo e($priority['color']); ?>-400 peer-checked:bg-<?php echo e($priority['color']); ?>-500/5 absolute inset-0 border-2 border-transparent rounded-xl transition-all"></div>
                                    <div class="relative">
                                        <span class="block font-semibold text-white"><?php echo e($priority['label']); ?></span>
                                    </div>
                                </label>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>

                        <div>
                            <label class="text-xs font-semibold text-cyan-100/70 mb-2 block">
                                Photos (optionnel)
                            </label>
                            <div class="glass p-6 rounded-xl border-2 border-dashed border-cyan-400/20 hover:border-cyan-400/40 transition-colors">
                                <input type="file" name="photos[]" id="photos" multiple accept="image/*" class="hidden" onchange="handleFileSelect(event)">
                                <label for="photos" class="cursor-pointer flex flex-col items-center">
                                    <i data-lucide="upload" class="w-10 h-10 text-cyan-400 mb-3"></i>
                                    <span class="text-sm text-white font-medium mb-1">Cliquez pour ajouter des photos</span>
                                    <span class="text-xs text-cyan-100/50">PNG, JPG jusqu'à 5MB chacune</span>
                                </label>
                            </div>
                            <div id="photo-preview" class="grid grid-cols-3 gap-3 mt-3"></div>
                        </div>
                    </div>

                    <div class="flex justify-between mt-6">
                        <?php if (isset($component)) { $__componentOriginala8bb031a483a05f647cb99ed3a469847 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala8bb031a483a05f647cb99ed3a469847 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.button','data' => ['onclick' => 'previousStep()','type' => 'button','variant' => 'outline','icon' => 'arrow-left']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['onclick' => 'previousStep()','type' => 'button','variant' => 'outline','icon' => 'arrow-left']); ?>
                            Retour
                         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala8bb031a483a05f647cb99ed3a469847)): ?>
<?php $attributes = $__attributesOriginala8bb031a483a05f647cb99ed3a469847; ?>
<?php unset($__attributesOriginala8bb031a483a05f647cb99ed3a469847); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala8bb031a483a05f647cb99ed3a469847)): ?>
<?php $component = $__componentOriginala8bb031a483a05f647cb99ed3a469847; ?>
<?php unset($__componentOriginala8bb031a483a05f647cb99ed3a469847); ?>
<?php endif; ?>
                        <?php if (isset($component)) { $__componentOriginala8bb031a483a05f647cb99ed3a469847 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala8bb031a483a05f647cb99ed3a469847 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.button','data' => ['onclick' => 'nextStep()','type' => 'button','iconRight' => 'arrow-right']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['onclick' => 'nextStep()','type' => 'button','icon-right' => 'arrow-right']); ?>
                            Continuer
                         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala8bb031a483a05f647cb99ed3a469847)): ?>
<?php $attributes = $__attributesOriginala8bb031a483a05f647cb99ed3a469847; ?>
<?php unset($__attributesOriginala8bb031a483a05f647cb99ed3a469847); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala8bb031a483a05f647cb99ed3a469847)): ?>
<?php $component = $__componentOriginala8bb031a483a05f647cb99ed3a469847; ?>
<?php unset($__componentOriginala8bb031a483a05f647cb99ed3a469847); ?>
<?php endif; ?>
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
            </div>

            <!-- Step 4: Confirmation -->
            <div class="form-step hidden" data-step="4">
                <?php if (isset($component)) { $__componentOriginaldae4cd48acb67888a4631e1ba48f2f93 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.card','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
                    <h3 class="text-xl font-display font-bold text-white mb-4">Vérifiez votre déclaration</h3>
                    
                    <div class="space-y-4" id="summary">
                        <!-- Will be filled by JavaScript -->
                    </div>

                    <div class="flex items-start gap-3 p-4 rounded-xl bg-cyan-500/5 border border-cyan-400/10 mt-6">
                        <input 
                            type="checkbox" 
                            name="confirm" 
                            id="confirm" 
                            required
                            class="mt-0.5 w-4 h-4 rounded border-cyan-400/30 bg-slate-950/50 text-cyan-500 focus:ring-2 focus:ring-cyan-400/50"
                        />
                        <label for="confirm" class="text-xs text-cyan-100/70 leading-relaxed">
                            Je certifie que les informations fournies sont exactes et que ce signalement concerne un problème réel.
                        </label>
                    </div>

                    <div class="flex justify-between mt-6">
                        <?php if (isset($component)) { $__componentOriginala8bb031a483a05f647cb99ed3a469847 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala8bb031a483a05f647cb99ed3a469847 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.button','data' => ['onclick' => 'previousStep()','type' => 'button','variant' => 'outline','icon' => 'arrow-left']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['onclick' => 'previousStep()','type' => 'button','variant' => 'outline','icon' => 'arrow-left']); ?>
                            Retour
                         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala8bb031a483a05f647cb99ed3a469847)): ?>
<?php $attributes = $__attributesOriginala8bb031a483a05f647cb99ed3a469847; ?>
<?php unset($__attributesOriginala8bb031a483a05f647cb99ed3a469847); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala8bb031a483a05f647cb99ed3a469847)): ?>
<?php $component = $__componentOriginala8bb031a483a05f647cb99ed3a469847; ?>
<?php unset($__componentOriginala8bb031a483a05f647cb99ed3a469847); ?>
<?php endif; ?>
                        <?php if (isset($component)) { $__componentOriginala8bb031a483a05f647cb99ed3a469847 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala8bb031a483a05f647cb99ed3a469847 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.button','data' => ['type' => 'submit','icon' => 'send']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'submit','icon' => 'send']); ?>
                            Envoyer la déclaration
                         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala8bb031a483a05f647cb99ed3a469847)): ?>
<?php $attributes = $__attributesOriginala8bb031a483a05f647cb99ed3a469847; ?>
<?php unset($__attributesOriginala8bb031a483a05f647cb99ed3a469847); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala8bb031a483a05f647cb99ed3a469847)): ?>
<?php $component = $__componentOriginala8bb031a483a05f647cb99ed3a469847; ?>
<?php unset($__componentOriginala8bb031a483a05f647cb99ed3a469847); ?>
<?php endif; ?>
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
            </div>
        </form>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
let currentStep = 1;
const formData = {};

function nextStep() {
    const currentStepEl = document.querySelector(`.form-step[data-step="${currentStep}"]`);
    const inputs = currentStepEl.querySelectorAll('input[required], select[required], textarea[required]');
    
    // Validate current step
    let isValid = true;
    inputs.forEach(input => {
        if (!input.value || (input.type === 'radio' && !document.querySelector(`input[name="${input.name}"]:checked`))) {
            isValid = false;
            input.reportValidity();
        }
    });
    
    if (!isValid) return;
    
    // Save current step data
    inputs.forEach(input => {
        if (input.type === 'radio' && input.checked) {
            formData[input.name] = input.value;
        } else if (input.type !== 'radio') {
            formData[input.name] = input.value;
        }
    });
    
    // Update step 4 summary
    if (currentStep === 3) {
        updateSummary();
    }
    
    // Move to next step
    if (currentStep < 4) {
        currentStepEl.classList.add('hidden');
        currentStep++;
        document.querySelector(`.form-step[data-step="${currentStep}"]`).classList.remove('hidden');
        updateProgressUI();
    }
}

function previousStep() {
    if (currentStep > 1) {
        document.querySelector(`.form-step[data-step="${currentStep}"]`).classList.add('hidden');
        currentStep--;
        document.querySelector(`.form-step[data-step="${currentStep}"]`).classList.remove('hidden');
        updateProgressUI();
    }
}

function updateProgressUI() {
    document.querySelectorAll('.step-circle').forEach((circle, index) => {
        if (index + 1 < currentStep) {
            circle.className = 'step-circle w-10 h-10 rounded-full flex items-center justify-center font-semibold transition-all bg-emerald-500 text-white';
            circle.innerHTML = '<i data-lucide="check" class="w-5 h-5"></i>';
        } else if (index + 1 === currentStep) {
            circle.className = 'step-circle w-10 h-10 rounded-full flex items-center justify-center font-semibold transition-all bg-gradient-to-r from-cyan-500 to-blue-600 text-white';
            circle.textContent = index + 1;
        } else {
            circle.className = 'step-circle w-10 h-10 rounded-full flex items-center justify-center font-semibold transition-all bg-slate-800/50 text-cyan-100/40';
            circle.textContent = index + 1;
        }
    });
    
    if (typeof lucide !== 'undefined') lucide.createIcons();
}

function updateSummary() {
    const typeLabels = {
        'leak': 'Fuite d\'eau',
        'quality': 'Qualité de l\'eau',
        'pressure': 'Pression insuffisante',
        'outage': 'Coupure d\'eau',
        'noise': 'Bruit anormal',
        'other': 'Autre'
    };
    
    const priorityLabels = {
        'low': 'Faible',
        'medium': 'Moyen',
        'high': 'Urgent'
    };
    
    const zoneName = document.querySelector('select[name="zone"] option:checked').textContent;
    
    const summary = `
        <div class="glass p-4 rounded-xl">
            <div class="flex items-center gap-3 mb-3">
                <i data-lucide="alert-circle" class="w-5 h-5 text-cyan-400"></i>
                <h4 class="font-semibold text-white">Type de problème</h4>
            </div>
            <p class="text-sm text-cyan-100/70">${typeLabels[formData.type] || formData.type}</p>
        </div>
        
        <div class="glass p-4 rounded-xl">
            <div class="flex items-center gap-3 mb-3">
                <i data-lucide="map-pin" class="w-5 h-5 text-cyan-400"></i>
                <h4 class="font-semibold text-white">Localisation</h4>
            </div>
            <p class="text-sm text-cyan-100/70">${formData.address}</p>
            <p class="text-xs text-cyan-100/50 mt-1">${zoneName}</p>
        </div>
        
        <div class="glass p-4 rounded-xl">
            <div class="flex items-center gap-3 mb-3">
                <i data-lucide="file-text" class="w-5 h-5 text-cyan-400"></i>
                <h4 class="font-semibold text-white">Description</h4>
            </div>
            <p class="text-sm text-cyan-100/70">${formData.description}</p>
        </div>
        
        <div class="glass p-4 rounded-xl">
            <div class="flex items-center gap-3 mb-3">
                <i data-lucide="alert-triangle" class="w-5 h-5 text-cyan-400"></i>
                <h4 class="font-semibold text-white">Urgence</h4>
            </div>
            <p class="text-sm text-cyan-100/70">${priorityLabels[formData.priority] || formData.priority}</p>
        </div>
    `;
    
    document.getElementById('summary').innerHTML = summary;
    if (typeof lucide !== 'undefined') lucide.createIcons();
}

function handleFileSelect(event) {
    const files = event.target.files;
    const preview = document.getElementById('photo-preview');
    preview.innerHTML = '';
    
    Array.from(files).forEach((file, index) => {
        const reader = new FileReader();
        reader.onload = function(e) {
            const div = document.createElement('div');
            div.className = 'relative glass rounded-lg overflow-hidden aspect-square';
            div.innerHTML = `
                <img src="${e.target.result}" class="w-full h-full object-cover">
                <button type="button" onclick="removePhoto(${index})" class="absolute top-2 right-2 w-6 h-6 rounded-full bg-rose-500 flex items-center justify-center hover:bg-rose-600 transition-colors">
                    <i data-lucide="x" class="w-4 h-4 text-white"></i>
                </button>
            `;
            preview.appendChild(div);
            if (typeof lucide !== 'undefined') lucide.createIcons();
        };
        reader.readAsDataURL(file);
    });
}

// Form submission
document.getElementById('reportForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Simulate success
    showToast('Déclaration envoyée avec succès ! Vous recevrez une confirmation par email.', 'success');
    
    setTimeout(() => {
        window.location.href = '<?php echo e(route('citizen.dashboard')); ?>';
    }, 2000);
});
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.frontoffice', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\ghada\Desktop\5ème\projet_Laravel\aquasecure\resources\views/citizen/reports/create.blade.php ENDPATH**/ ?>