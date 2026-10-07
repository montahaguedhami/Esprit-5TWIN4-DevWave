<?php $__env->startSection('title', 'AquaSecure — Incident Dispatch & Management Desk'); ?>

<?php
    use App\Data\PlaceholderData;
    $user         = session('user', ['name' => 'Ines Mansouri', 'role' => 'manager']);
    $reclamations = PlaceholderData::adminAllReclamations();
    $technicians  = PlaceholderData::adminTechnicians();
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
                    <div class="w-8 h-8 rounded-xl bg-red-500/20 border border-red-500/30 flex items-center justify-center">
                        <i data-lucide="alert-triangle" class="w-4 h-4 text-red-400"></i>
                    </div>
                    <span class="font-display font-bold text-white text-base">Administrative Incident Dispatch Desk</span>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <a href="<?php echo e(route('citizen.reports.create')); ?>" class="px-3.5 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 text-xs font-semibold flex items-center gap-2">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i> Test Citizen Report Submit Form
                </a>
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
                <h1 class="text-2xl font-display font-bold text-white tracking-tight">Incident Dispatch & Technician Assignment Desk</h1>
                <p class="text-slate-400 text-xs mt-1">Review incoming citizen reports, dispatch field repair crews, update resolution status, and notify residents.</p>
            </div>
            <div class="flex items-center gap-2">
                <button onclick="showToast('Bulk notifying affected residents via SMS & Email...', 'info')" class="px-3.5 py-2 rounded-xl bg-red-500/10 border border-red-500/30 text-red-300 hover:bg-red-500/20 text-xs font-semibold flex items-center gap-2">
                    <i data-lucide="bell" class="w-4 h-4"></i> Broadcast Resident Outage Alert
                </button>
            </div>
        </div>

        
        <div class="grid lg:grid-cols-3 gap-6">
            
            
            <div class="lg:col-span-2 bg-slate-900/90 border border-slate-800 rounded-2xl overflow-hidden shadow-xl">
                <div class="p-4 border-b border-slate-800 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-white flex items-center gap-2">
                        <i data-lucide="inbox" class="w-4 h-4 text-cyan-400"></i> Incoming Citizen & Telemetry Reports
                    </h3>
                    <span class="text-xs text-slate-400"><?php echo e(count($reclamations)); ?> Total Reports Logged</span>
                </div>
                <div class="divide-y divide-slate-800">
                    <?php $__currentLoopData = $reclamations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rec): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="p-4 hover:bg-slate-800/40 transition-colors space-y-3">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="font-mono text-xs font-bold text-cyan-400 bg-cyan-500/10 px-2 py-0.5 rounded border border-cyan-500/20"><?php echo e($rec['id']); ?></span>
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold <?php echo e($rec['priority'] === 'critical' ? 'bg-red-500/20 text-red-300 border border-red-500/30' : 'bg-amber-500/20 text-amber-300'); ?>">
                                            <?php echo e(strtoupper($rec['priority'])); ?>

                                        </span>
                                        <span class="text-xs text-slate-400">• <?php echo e($rec['created_at']); ?></span>
                                    </div>
                                    <h4 class="text-base font-bold text-white"><?php echo e($rec['type']); ?></h4>
                                    <p class="text-xs text-slate-300 mt-0.5"><?php echo e($rec['description']); ?></p>
                                </div>
                                <div class="text-right shrink-0">
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold <?php echo e($rec['status'] === 'resolved' ? 'bg-teal-500/20 text-teal-300 border border-teal-500/30' : ($rec['status'] === 'in_progress' ? 'bg-amber-500/20 text-amber-300' : 'bg-red-500/20 text-red-300')); ?>">
                                        <?php echo e(ucfirst(str_replace('_', ' ', $rec['status']))); ?>

                                    </span>
                                </div>
                            </div>

                            <div class="flex flex-wrap items-center justify-between gap-2 text-xs pt-2 border-t border-slate-800/60">
                                <div class="text-slate-400">
                                    <i data-lucide="map-pin" class="w-3.5 h-3.5 inline text-slate-500"></i> <?php echo e($rec['address']); ?> (<?php echo e($rec['zone']); ?>)
                                </div>
                                <div class="flex items-center gap-2">
                                    <button onclick="dispatchTechnician('<?php echo e($rec['id']); ?>')" class="px-3 py-1 rounded-lg bg-cyan-500/15 hover:bg-cyan-500/25 text-cyan-300 border border-cyan-500/30 font-semibold text-xs">
                                        Assign Technician
                                    </button>
                                    <button onclick="updateStatus('<?php echo e($rec['id']); ?>')" class="px-3 py-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 font-semibold text-xs">
                                        Update Status
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>

            
            <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-5 space-y-4 shadow-xl">
                <h3 class="text-sm font-bold text-white flex items-center gap-2 border-b border-slate-800 pb-3">
                    <i data-lucide="wrench" class="w-4 h-4 text-cyan-400"></i> Active Field Technicians
                </h3>
                
                <div class="space-y-3 text-xs">
                    <?php $__currentLoopData = $technicians; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tech): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="flex items-center justify-between p-3 rounded-xl bg-slate-950/60 border border-slate-800">
                            <div>
                                <div class="font-bold text-white"><?php echo e($tech['name']); ?></div>
                                <div class="text-[11px] text-slate-400"><?php echo e($tech['zone']); ?></div>
                            </div>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold <?php echo e($tech['status'] === 'on_mission' ? 'bg-amber-500/15 text-amber-300' : 'bg-teal-500/15 text-teal-300'); ?>">
                                <?php echo e($tech['status'] === 'on_mission' ? 'On Mission' : 'Available'); ?>

                            </span>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>

                <div class="bg-slate-950/80 border border-slate-800 rounded-xl p-4 text-xs space-y-2">
                    <div class="font-bold text-cyan-300">Automated Resident Notification</div>
                    <p class="text-slate-400 text-[11px]">When an incident status changes to "Dispatched" or "Resolved", residents receive automated SMS and email notifications.</p>
                </div>
            </div>

        </div>

    </div>
</div>

<script>
function dispatchTechnician(id) {
    showToast('Dispatched Field Technician Amira Ben Ali to ' + id, 'success');
}
function updateStatus(id) {
    showToast('Updated status for ' + id + ' to IN PROGRESS', 'info');
}
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\monta\Downloads\Aqua_Secure-main\Aqua_Secure-main\resources\views/manager/incidents.blade.php ENDPATH**/ ?>