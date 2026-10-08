<?php $__env->startSection('title', 'AquaSecure — Équipes de terrain et techniciens'); ?>

<?php
    use App\Data\PlaceholderData;
    $technicians = PlaceholderData::adminTechnicians();
    $performance = PlaceholderData::analyticsTeamPerformance();
?>

<?php $__env->startSection('content'); ?>
<div class="min-h-screen bg-[#070e17] text-slate-100 flex flex-col font-sans">
    <header class="sticky top-0 z-50 bg-[#0c1624]/90 backdrop-blur-md border-b border-slate-800/80 px-4 sm:px-6 py-3">
        <div class="max-w-7xl mx-auto flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="<?php echo e(route('manager.dashboard')); ?>" class="p-2 rounded-xl bg-slate-900 border border-slate-700 text-slate-300 hover:text-white">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                </a>
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-xl bg-cyan-500/20 border border-cyan-500/30 flex items-center justify-center">
                        <i data-lucide="users" class="w-4 h-4 text-cyan-400"></i>
                    </div>
                    <span class="font-display font-bold text-white text-base">Équipes de réparation et techniciens</span>
                </div>
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

    <div class="flex-1 max-w-7xl w-full mx-auto p-4 sm:p-6 space-y-6">
        <div>
            <h1 class="text-2xl font-display font-bold text-white tracking-tight">Équipes de terrain et suivi des performances</h1>
            <p class="text-slate-400 text-xs mt-1">Manage active technicians, assigned repair zones, and mean time to resolution (MTTR).</p>
        </div>

        <div class="grid lg:grid-cols-2 gap-6">
            <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-5 space-y-4">
                <h3 class="text-sm font-bold text-white flex items-center gap-2">
                    <i data-lucide="user-check" class="w-4 h-4 text-cyan-400"></i> Équipes de terrain
                </h3>
                <div class="space-y-3 text-xs">
                    <?php $__currentLoopData = $technicians; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="p-3 bg-slate-950/60 border border-slate-800 rounded-xl flex items-center justify-between">
                            <div>
                                <div class="font-bold text-white text-sm"><?php echo e($t['name']); ?></div>
                                <div class="text-slate-400 text-xs">Zone: <?php echo e($t['zone']); ?></div>
                            </div>
                            <div class="text-right">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold <?php echo e($t['status'] === 'on_mission' ? 'bg-amber-500/15 text-amber-300' : 'bg-teal-500/15 text-teal-300'); ?>">
                                    <?php echo e(ucfirst(str_replace('_', ' ', $t['status']))); ?>

                                </span>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>

            <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-5 space-y-4">
                <h3 class="text-sm font-bold text-white flex items-center gap-2">
                    <i data-lucide="award" class="w-4 h-4 text-emerald-400"></i> Performance des équipes
                </h3>
                <div class="space-y-3 text-xs">
                    <?php $__currentLoopData = $performance; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="p-3 bg-slate-950/60 border border-slate-800 rounded-xl space-y-2">
                            <div class="flex items-center justify-between font-bold">
                                <span class="text-white"><?php echo e($p['team']); ?></span>
                                <span class="text-emerald-400"><?php echo e($p['score']); ?>% Score</span>
                            </div>
                            <div class="flex justify-between text-slate-400 text-[11px]">
                                <span>Interventions: <?php echo e($p['interventions']); ?></span>
                                <span>Resolved: <?php echo e($p['resolved']); ?></span>
                                <span>Avg MTTR: <?php echo e($p['avg_time']); ?></span>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\monta\Downloads\DEVWAVE\Esprit-5TWIN4-DevWave-main (1)\Esprit-5TWIN4-DevWave-main\resources\views\manager\teams.blade.php ENDPATH**/ ?>