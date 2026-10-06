<?php $__env->startSection('title', 'AquaSecure — Maintenance & Infrastructure Projects'); ?>

<?php
    use App\Data\PlaceholderData;
    $user     = session('user', ['name' => 'Ines Mansouri', 'role' => 'manager']);
    $projects = PlaceholderData::projects();
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
                    <div class="w-8 h-8 rounded-xl bg-amber-500/20 border border-amber-500/30 flex items-center justify-center">
                        <i data-lucide="briefcase" class="w-4 h-4 text-amber-400"></i>
                    </div>
                    <span class="font-display font-bold text-white text-base">Capital Projects & Infrastructure Renovation</span>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <button onclick="showToast('Create new project modal opening...', 'info')" class="px-3.5 py-1.5 rounded-xl bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 text-white font-bold text-xs flex items-center gap-2 shadow-lg shadow-cyan-500/20">
                    <i data-lucide="plus" class="w-4 h-4"></i> New Capital Project
                </button>
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
        
        
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-display font-bold text-white tracking-tight">Infrastructure Renovation & Decontamination Projects</h1>
                <p class="text-slate-400 text-xs mt-1">Track capital investment, contractor progress, milestone deadlines, and budget expenditure.</p>
            </div>
            <div class="flex items-center gap-2">
                <input type="text" placeholder="Filter projects by keyword or zone..." class="bg-slate-900 border border-slate-700 rounded-xl px-3 py-1.5 text-xs text-white placeholder-slate-400 outline-none">
            </div>
        </div>

        
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-4 flex items-center gap-4">
                <div class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-400 flex items-center justify-center font-bold">
                    <i data-lucide="wrench" class="w-5 h-5"></i>
                </div>
                <div>
                    <div class="text-2xl font-display font-bold text-white">3 Capital Projects</div>
                    <div class="text-xs text-slate-400">2 In Progress, 1 Completed</div>
                </div>
            </div>
            <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-4 flex items-center gap-4">
                <div class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center font-bold">
                    <i data-lucide="dollar-sign" class="w-5 h-5"></i>
                </div>
                <div>
                    <div class="text-2xl font-display font-bold text-white">$4,850,000</div>
                    <div class="text-xs text-slate-400">Total Planned Allocation</div>
                </div>
            </div>
            <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-4 flex items-center gap-4">
                <div class="w-10 h-10 rounded-xl bg-cyan-500/10 text-cyan-400 flex items-center justify-center font-bold">
                    <i data-lucide="check-circle-2" class="w-5 h-5"></i>
                </div>
                <div>
                    <div class="text-2xl font-display font-bold text-white">77.6%</div>
                    <div class="text-xs text-slate-400">Average Milestone Completion Rate</div>
                </div>
            </div>
        </div>

        
        <div class="space-y-6">
            <?php $__currentLoopData = $projects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $prj): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-6 space-y-4 shadow-xl hover:border-slate-700 transition-all">
                    
                    
                    <div class="flex flex-wrap items-start justify-between gap-4 border-b border-slate-800/80 pb-4">
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <span class="font-mono text-xs font-bold text-cyan-400 bg-cyan-500/10 px-2.5 py-0.5 rounded border border-cyan-500/20"><?php echo e($prj['code']); ?></span>
                                <span class="text-xs px-2.5 py-0.5 rounded-full font-bold <?php echo e($prj['status'] === 'Completed' ? 'bg-emerald-500/15 text-emerald-300 border border-emerald-500/30' : 'bg-amber-500/15 text-amber-300 border border-amber-500/30'); ?>">
                                    <?php echo e($prj['status']); ?>

                                </span>
                                <span class="text-xs px-2.5 py-0.5 rounded-full font-semibold bg-slate-800 text-slate-300">
                                    <?php echo e($prj['category']); ?>

                                </span>
                            </div>
                            <h2 class="text-xl font-display font-bold text-white"><?php echo e($prj['title']); ?></h2>
                            <p class="text-xs text-slate-400 mt-1 max-w-3xl"><?php echo e($prj['description']); ?></p>
                        </div>
                        <div class="text-right">
                            <div class="text-xs text-slate-400">Planned vs Actual Spent</div>
                            <div class="text-lg font-display font-bold text-white">$<?php echo e(number_format($prj['actual_spent'])); ?> / <span class="text-slate-400 text-sm">$<?php echo e(number_format($prj['planned_budget'])); ?></span></div>
                            <div class="text-[11px] text-cyan-300 mt-0.5">Manager: <?php echo e($prj['project_manager']); ?></div>
                        </div>
                    </div>

                    
                    <div class="grid lg:grid-cols-3 gap-6 items-center">
                        <div class="lg:col-span-2 space-y-2">
                            <div class="flex items-center justify-between text-xs font-bold">
                                <span class="text-slate-300">Overall Progress</span>
                                <span class="text-cyan-400"><?php echo e($prj['completion_pct']); ?>% Complete</span>
                            </div>
                            <div class="w-full bg-slate-950 h-3 rounded-full overflow-hidden p-0.5 border border-slate-800">
                                <div class="bg-gradient-to-r from-cyan-500 to-blue-500 h-full rounded-full transition-all duration-1000" style="width: <?php echo e($prj['completion_pct']); ?>%"></div>
                            </div>
                            <div class="flex justify-between text-[11px] text-slate-400 pt-1">
                                <span>Start: <?php echo e($prj['start_date']); ?></span>
                                <span>Contractor: <strong class="text-slate-200"><?php echo e($prj['contractor']); ?></strong></span>
                                <span>Target Completion: <?php echo e($prj['end_date']); ?></span>
                            </div>
                        </div>

                        
                        <div class="bg-slate-950/60 border border-slate-800 rounded-xl p-3 text-xs space-y-1.5">
                            <div class="font-bold text-slate-400 text-[10px] uppercase tracking-wider">Funding Contributions</div>
                            <?php $__currentLoopData = $prj['funding_sources']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $source): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="flex items-center gap-1.5 text-slate-300">
                                    <i data-lucide="circle-dot" class="w-3 h-3 text-cyan-400"></i>
                                    <span><?php echo e($source); ?></span>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>

                    
                    <div class="grid lg:grid-cols-2 gap-4 pt-2 border-t border-slate-800/60 text-xs">
                        
                        
                        <div>
                            <h4 class="font-bold text-white mb-2 flex items-center gap-2">
                                <i data-lucide="check-square" class="w-4 h-4 text-cyan-400"></i> Project Milestones & Timeline
                            </h4>
                            <div class="space-y-1.5">
                                <?php $__currentLoopData = $prj['milestones']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="flex items-center justify-between bg-slate-950/40 px-3 py-1.5 rounded-lg border border-slate-800/80">
                                        <div class="flex items-center gap-2">
                                            <i data-lucide="<?php echo e($m['done'] ? 'check-circle-2' : 'clock'); ?>" class="w-4 h-4 <?php echo e($m['done'] ? 'text-emerald-400' : 'text-slate-500'); ?>"></i>
                                            <span class="<?php echo e($m['done'] ? 'text-slate-200 font-medium' : 'text-slate-400'); ?>"><?php echo e($m['name']); ?></span>
                                        </div>
                                        <span class="text-[10px] font-mono text-slate-400"><?php echo e($m['date']); ?></span>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>

                        
                        <div>
                            <h4 class="font-bold text-white mb-2 flex items-center gap-2">
                                <i data-lucide="paperclip" class="w-4 h-4 text-blue-400"></i> Documents & Progress Reports
                            </h4>
                            <div class="space-y-1.5">
                                <?php $__currentLoopData = $prj['documents']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $doc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="flex items-center justify-between bg-slate-950/40 px-3 py-1.5 rounded-lg border border-slate-800/80">
                                        <div class="flex items-center gap-2">
                                            <i data-lucide="file-text" class="w-4 h-4 text-cyan-400"></i>
                                            <span class="text-slate-200 font-medium"><?php echo e($doc['name']); ?></span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-[10px] text-slate-400"><?php echo e($doc['size']); ?></span>
                                            <button onclick="showToast('Downloading <?php echo e($doc['name']); ?>...', 'info')" class="text-cyan-400 hover:text-cyan-300 font-bold text-[11px]">Download</button>
                                        </div>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>

                    </div>

                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\monta\Downloads\Aqua_Secure-main\Aqua_Secure-main\resources\views/manager/projects.blade.php ENDPATH**/ ?>