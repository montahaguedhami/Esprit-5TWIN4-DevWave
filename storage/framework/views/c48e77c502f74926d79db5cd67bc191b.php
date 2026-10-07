<?php $__env->startSection('title', 'Rapport d\'intervention'); ?>

<?php
    use App\Data\PlaceholderData;
    $interventions = PlaceholderData::technicianInterventions();
    $intervention = $interventions[0];
?>

<?php $__env->startSection('content'); ?>
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="flex items-center gap-4 mb-8">
        <a href="<?php echo e(route('technician.interventions.show', ['id' => $intervention['id']])); ?>" class="w-10 h-10 rounded-xl glass flex items-center justify-center hover:border-cyan-400/40 transition-colors">
            <i data-lucide="arrow-left" class="w-5 h-5 text-cyan-300"></i>
        </a>
        <div>
            <h1 class="text-3xl font-display font-bold text-white">Rapport d'intervention</h1>
            <p class="text-cyan-100/60 text-sm mt-1"><?php echo e($intervention['id']); ?> - <?php echo e($intervention['type']); ?></p>
        </div>
    </div>

    <div class="max-w-4xl mx-auto">
        <form method="POST" action="<?php echo e(route('technician.interventions.report.store', ['id' => $intervention['id']])); ?>" enctype="multipart/form-data">
            <?php echo csrf_field(); ?>

            <!-- Intervention Summary -->
            <?php if (isset($component)) { $__componentOriginaldae4cd48acb67888a4631e1ba48f2f93 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.card','data' => ['class' => 'mb-6']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mb-6']); ?>
                <h3 class="text-lg font-display font-bold text-white mb-4">Récapitulatif</h3>
                <div class="grid sm:grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="text-cyan-100/50 block mb-1">Zone</span>
                        <span class="text-white"><?php echo e($intervention['zone']); ?></span>
                    </div>
                    <div>
                        <span class="text-cyan-100/50 block mb-1">Adresse</span>
                        <span class="text-white"><?php echo e($intervention['address']); ?></span>
                    </div>
                    <div>
                        <span class="text-cyan-100/50 block mb-1">Démarré</span>
                        <span class="text-white"><?php echo e(\Carbon\Carbon::parse($intervention['started_at'])->format('d/m/Y H:i')); ?></span>
                    </div>
                    <div>
                        <span class="text-cyan-100/50 block mb-1">Terminé</span>
                        <span class="text-white"><?php echo e(now()->format('d/m/Y H:i')); ?></span>
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

            <!-- Status -->
            <?php if (isset($component)) { $__componentOriginaldae4cd48acb67888a4631e1ba48f2f93 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.card','data' => ['class' => 'mb-6']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mb-6']); ?>
                <h3 class="text-lg font-display font-bold text-white mb-4">Résultat de l'intervention</h3>
                
                <div class="grid sm:grid-cols-2 gap-4 mb-6">
                    <?php $__currentLoopData = [
                        ['value' => 'resolved', 'icon' => 'check-circle', 'title' => 'Problème résolu', 'color' => 'emerald'],
                        ['value' => 'partial', 'icon' => 'alert-circle', 'title' => 'Résolution partielle', 'color' => 'amber'],
                        ['value' => 'failed', 'icon' => 'x-circle', 'title' => 'Non résolu', 'color' => 'rose'],
                        ['value' => 'followup', 'icon' => 'clock', 'title' => 'Nécessite suivi', 'color' => 'cyan'],
                    ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <label class="status-card glass p-5 rounded-xl cursor-pointer hover:border-cyan-400/40 transition-all relative">
                        <input type="radio" name="status" value="<?php echo e($status['value']); ?>" class="peer hidden" required>
                        <div class="peer-checked:border-<?php echo e($status['color']); ?>-400 peer-checked:bg-<?php echo e($status['color']); ?>-500/5 absolute inset-0 border-2 border-transparent rounded-xl transition-all"></div>
                        <div class="relative flex items-center gap-3">
                            <i data-lucide="<?php echo e($status['icon']); ?>" class="w-6 h-6 text-<?php echo e($status['color']); ?>-400"></i>
                            <span class="font-semibold text-white"><?php echo e($status['title']); ?></span>
                        </div>
                    </label>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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

            <!-- Work Details -->
            <?php if (isset($component)) { $__componentOriginaldae4cd48acb67888a4631e1ba48f2f93 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.card','data' => ['class' => 'mb-6']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mb-6']); ?>
                <h3 class="text-lg font-display font-bold text-white mb-4">Détails de l'intervention</h3>
                
                <div class="space-y-4">
                    <?php if (isset($component)) { $__componentOriginalea7b7095850fe8bc9657025b89ccf5a5 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalea7b7095850fe8bc9657025b89ccf5a5 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.forms.textarea','data' => ['name' => 'cause','label' => 'Cause du problème','rows' => '3','placeholder' => 'Décrivez la cause identifiée...','required' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('forms.textarea'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'cause','label' => 'Cause du problème','rows' => '3','placeholder' => 'Décrivez la cause identifiée...','required' => true]); ?>
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

                    <?php if (isset($component)) { $__componentOriginalea7b7095850fe8bc9657025b89ccf5a5 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalea7b7095850fe8bc9657025b89ccf5a5 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.forms.textarea','data' => ['name' => 'work_performed','label' => 'Travaux effectués','rows' => '4','placeholder' => 'Décrivez les actions réalisées...','required' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('forms.textarea'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'work_performed','label' => 'Travaux effectués','rows' => '4','placeholder' => 'Décrivez les actions réalisées...','required' => true]); ?>
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

                    <?php if (isset($component)) { $__componentOriginalea7b7095850fe8bc9657025b89ccf5a5 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalea7b7095850fe8bc9657025b89ccf5a5 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.forms.textarea','data' => ['name' => 'observations','label' => 'Observations / Recommandations','rows' => '3','placeholder' => 'Ajoutez vos observations et recommandations...']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('forms.textarea'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'observations','label' => 'Observations / Recommandations','rows' => '3','placeholder' => 'Ajoutez vos observations et recommandations...']); ?>
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

            <!-- Parts Used -->
            <?php if (isset($component)) { $__componentOriginaldae4cd48acb67888a4631e1ba48f2f93 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.card','data' => ['class' => 'mb-6']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mb-6']); ?>
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-display font-bold text-white">Pièces utilisées</h3>
                    <?php if (isset($component)) { $__componentOriginala8bb031a483a05f647cb99ed3a469847 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala8bb031a483a05f647cb99ed3a469847 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.button','data' => ['type' => 'button','variant' => 'outline','size' => 'sm','icon' => 'plus','onclick' => 'addPart()']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'button','variant' => 'outline','size' => 'sm','icon' => 'plus','onclick' => 'addPart()']); ?>
                        Ajouter
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
                
                <div id="parts-list" class="space-y-3">
                    <div class="flex gap-3 part-row">
                        <?php if (isset($component)) { $__componentOriginal4fb6044c7ed6b655352043ff774efcd0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4fb6044c7ed6b655352043ff774efcd0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.forms.input','data' => ['name' => 'parts[0][name]','placeholder' => 'Nom de la pièce','class' => 'flex-1']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('forms.input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'parts[0][name]','placeholder' => 'Nom de la pièce','class' => 'flex-1']); ?>
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
                        <?php if (isset($component)) { $__componentOriginal4fb6044c7ed6b655352043ff774efcd0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4fb6044c7ed6b655352043ff774efcd0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.forms.input','data' => ['name' => 'parts[0][quantity]','type' => 'number','placeholder' => 'Qté','class' => 'w-24']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('forms.input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'parts[0][quantity]','type' => 'number','placeholder' => 'Qté','class' => 'w-24']); ?>
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
                        <button type="button" onclick="removePart(this)" class="w-10 h-10 rounded-lg flex items-center justify-center hover:bg-rose-500/10 text-rose-400 transition-colors">
                            <i data-lucide="trash-2" class="w-4 h-4"></i>
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

            <!-- Duration -->
            <?php if (isset($component)) { $__componentOriginaldae4cd48acb67888a4631e1ba48f2f93 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.card','data' => ['class' => 'mb-6']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mb-6']); ?>
                <h3 class="text-lg font-display font-bold text-white mb-4">Durée</h3>
                
                <div class="grid sm:grid-cols-3 gap-4">
                    <?php if (isset($component)) { $__componentOriginal4fb6044c7ed6b655352043ff774efcd0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4fb6044c7ed6b655352043ff774efcd0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.forms.input','data' => ['name' => 'duration_hours','type' => 'number','label' => 'Heures','placeholder' => '0','min' => '0']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('forms.input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'duration_hours','type' => 'number','label' => 'Heures','placeholder' => '0','min' => '0']); ?>
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
                    <?php if (isset($component)) { $__componentOriginal4fb6044c7ed6b655352043ff774efcd0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4fb6044c7ed6b655352043ff774efcd0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.forms.input','data' => ['name' => 'duration_minutes','type' => 'number','label' => 'Minutes','placeholder' => '0','min' => '0','max' => '59']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('forms.input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'duration_minutes','type' => 'number','label' => 'Minutes','placeholder' => '0','min' => '0','max' => '59']); ?>
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
                    <div class="flex items-end">
                        <div class="glass p-4 rounded-xl w-full text-center">
                            <div class="text-xs text-cyan-100/50 mb-1">Durée estimée</div>
                            <div class="text-lg font-display font-bold text-white"><?php echo e($intervention['estimated_duration']); ?></div>
                        </div>
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

            <!-- Photos -->
            <?php if (isset($component)) { $__componentOriginaldae4cd48acb67888a4631e1ba48f2f93 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.card','data' => ['class' => 'mb-6']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mb-6']); ?>
                <h3 class="text-lg font-display font-bold text-white mb-4">Photos</h3>
                
                <div class="grid sm:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="text-xs font-semibold text-cyan-100/70 mb-2 block">Photo avant</label>
                        <div class="glass p-6 rounded-xl border-2 border-dashed border-cyan-400/20 hover:border-cyan-400/40 transition-colors">
                            <input type="file" name="photo_before" accept="image/*" class="hidden" id="photo_before">
                            <label for="photo_before" class="cursor-pointer flex flex-col items-center">
                                <i data-lucide="camera" class="w-8 h-8 text-cyan-400 mb-2"></i>
                                <span class="text-sm text-white font-medium">Avant intervention</span>
                            </label>
                        </div>
                    </div>
                    
                    <div>
                        <label class="text-xs font-semibold text-cyan-100/70 mb-2 block">Photo après</label>
                        <div class="glass p-6 rounded-xl border-2 border-dashed border-cyan-400/20 hover:border-cyan-400/40 transition-colors">
                            <input type="file" name="photo_after" accept="image/*" class="hidden" id="photo_after">
                            <label for="photo_after" class="cursor-pointer flex flex-col items-center">
                                <i data-lucide="camera" class="w-8 h-8 text-cyan-400 mb-2"></i>
                                <span class="text-sm text-white font-medium">Après intervention</span>
                            </label>
                        </div>
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

            <!-- Actions -->
            <div class="flex items-center justify-between gap-4">
                <a href="<?php echo e(route('technician.interventions.show', ['id' => $intervention['id']])); ?>">
                    <?php if (isset($component)) { $__componentOriginala8bb031a483a05f647cb99ed3a469847 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala8bb031a483a05f647cb99ed3a469847 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.button','data' => ['type' => 'button','variant' => 'outline']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'button','variant' => 'outline']); ?>
                        Annuler
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
                </a>
                <?php if (isset($component)) { $__componentOriginala8bb031a483a05f647cb99ed3a469847 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala8bb031a483a05f647cb99ed3a469847 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.button','data' => ['type' => 'submit','icon' => 'check']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'submit','icon' => 'check']); ?>
                    Valider le rapport
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
        </form>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
let partIndex = 1;

function addPart() {
    const partsList = document.getElementById('parts-list');
    const newPart = document.createElement('div');
    newPart.className = 'flex gap-3 part-row';
    newPart.innerHTML = `
        <input 
            name="parts[${partIndex}][name]" 
            placeholder="Nom de la pièce"
            class="flex-1 bg-slate-950/35 border border-cyan-400/15 rounded-xl px-4 py-3 text-sm text-white placeholder:text-cyan-100/30 focus:outline-none focus:border-cyan-400/50 transition-colors"
        />
        <input 
            name="parts[${partIndex}][quantity]" 
            type="number"
            placeholder="Qté"
            class="w-24 bg-slate-950/35 border border-cyan-400/15 rounded-xl px-4 py-3 text-sm text-white placeholder:text-cyan-100/30 focus:outline-none focus:border-cyan-400/50 transition-colors"
        />
        <button type="button" onclick="removePart(this)" class="w-10 h-10 rounded-lg flex items-center justify-center hover:bg-rose-500/10 text-rose-400 transition-colors">
            <i data-lucide="trash-2" class="w-4 h-4"></i>
        </button>
    `;
    partsList.appendChild(newPart);
    partIndex++;
    
    if (typeof lucide !== 'undefined') lucide.createIcons();
}

function removePart(button) {
    const partRow = button.closest('.part-row');
    if (document.querySelectorAll('.part-row').length > 1) {
        partRow.remove();
    }
}
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.manager', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\monta\Downloads\Aqua_Secure-main\Aqua_Secure-main\resources\views/technician/interventions/report.blade.php ENDPATH**/ ?>