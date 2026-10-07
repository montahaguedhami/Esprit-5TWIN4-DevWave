<?php $__env->startSection('title', 'AquaSecure — Funding & Budget Financial Transparency'); ?>

<?php
    use App\Data\PlaceholderData;
    $user     = session('user', ['name' => 'Moncef Triki', 'role' => 'manager']);
    $funding  = PlaceholderData::fundingData();
?>

<?php $__env->startPush('styles'); ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="min-h-screen bg-[#070e17] text-slate-100 flex flex-col font-sans">

    
    <header class="sticky top-0 z-50 bg-[#0c1624]/90 backdrop-blur-md border-b border-slate-800/80 px-4 sm:px-6 py-3">
        <div class="max-w-7xl mx-auto flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="<?php echo e(route('manager.dashboard')); ?>" class="p-2 rounded-xl bg-slate-900 border border-slate-700 text-slate-300 hover:text-white">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                </a>
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-xl bg-emerald-500/20 border border-emerald-500/30 flex items-center justify-center">
                        <i data-lucide="dollar-sign" class="w-4 h-4 text-emerald-400"></i>
                    </div>
                    <span class="font-display font-bold text-white text-base">Financial Transparency & Funding Portal</span>
                </div>
            </div>
            <div class="flex items-center gap-3">
                
                
                <div class="flex items-center gap-2 bg-slate-900 border border-slate-700 rounded-xl px-3 py-1 text-xs">
                    <span class="text-slate-400">View Mode:</span>
                    <button id="view-toggle-btn" onclick="togglePublicMode()" class="font-bold text-emerald-400 hover:text-emerald-300 flex items-center gap-1">
                        <i data-lucide="eye" class="w-3.5 h-3.5"></i> <span id="view-mode-label">Manager Audit View</span>
                    </button>
                </div>

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
                <h1 class="text-2xl font-display font-bold text-white tracking-tight">Municipal Renovation Funding & Expenditure</h1>
                <p class="text-slate-400 text-xs mt-1">Ensures financial accountability for public water infrastructure bonds, eco-grants, and decontamination funds.</p>
            </div>
            <div class="flex items-center gap-2">
                <button onclick="showToast('Exporting Financial Audit Report (PDF)...', 'success')" class="px-4 py-2 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-400 hover:to-teal-500 text-slate-950 font-bold text-xs flex items-center gap-2 shadow-lg shadow-emerald-500/20">
                    <i data-lucide="download" class="w-4 h-4"></i> Export Audit Report (PDF / CSV)
                </button>
            </div>
        </div>

        
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            
            <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-4">
                <div class="text-xs text-slate-400 font-bold uppercase mb-1">Total Approved Funding</div>
                <div class="text-2xl font-display font-bold text-white">$<?php echo e(number_format($funding['total_approved_funding'])); ?></div>
                <p class="text-[11px] text-cyan-400 mt-2">Municipal Bonds & Grants</p>
            </div>

            <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-4">
                <div class="text-xs text-slate-400 font-bold uppercase mb-1">Actual Expenditure</div>
                <div class="text-2xl font-display font-bold text-emerald-400">$<?php echo e(number_format($funding['actual_expenditure'])); ?></div>
                <p class="text-[11px] text-slate-400 mt-2"><?php echo e($funding['utilization_rate']); ?>% Utilized</p>
            </div>

            <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-4">
                <div class="text-xs text-slate-400 font-bold uppercase mb-1">Remaining Budget</div>
                <div class="text-2xl font-display font-bold text-teal-300">$<?php echo e(number_format($funding['remaining_budget'])); ?></div>
                <p class="text-[11px] text-teal-400 mt-2">Available for Q4 Renovation</p>
            </div>

            <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-4">
                <div class="text-xs text-slate-400 font-bold uppercase mb-1">Funding Sources</div>
                <div class="text-2xl font-display font-bold text-white">4 Active Grants</div>
                <p class="text-[11px] text-slate-400 mt-2">EU, Federal & Municipal</p>
            </div>

        </div>

        
        <div class="grid lg:grid-cols-2 gap-6">
            
            
            <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-5">
                <h3 class="text-sm font-bold text-white mb-3 flex items-center gap-2">
                    <i data-lucide="pie-chart" class="w-4 h-4 text-emerald-400"></i> Funding Contribution Breakdown
                </h3>
                <div style="height: 220px;">
                    <canvas id="fundingBreakdownChart"></canvas>
                </div>
            </div>

            
            <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-5">
                <h3 class="text-sm font-bold text-white mb-3 flex items-center gap-2">
                    <i data-lucide="bar-chart-3" class="w-4 h-4 text-emerald-400"></i> Cumulative Monthly Expenditure vs Planned Budget
                </h3>
                <div style="height: 220px;">
                    <canvas id="expenditureTimelineChart"></canvas>
                </div>
            </div>

        </div>

        
        <div class="bg-slate-900/90 border border-slate-800 rounded-2xl overflow-hidden shadow-xl">
            <div class="p-4 border-b border-slate-800 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-white flex items-center gap-2">
                        <i data-lucide="receipt" class="w-4 h-4 text-emerald-400"></i> Project Expenditure Audit Log
                    </h3>
                    <p class="text-xs text-slate-400">Verified transactions for pipe replacement, IoT sensor procurement, and labor.</p>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-300">
                    <thead class="bg-slate-950/80 text-slate-400 uppercase font-bold text-[10px] tracking-wider border-b border-slate-800">
                        <tr>
                            <th class="px-4 py-3">Expense ID</th>
                            <th class="px-4 py-3">Date</th>
                            <th class="px-4 py-3">Project</th>
                            <th class="px-4 py-3">Category</th>
                            <th class="px-4 py-3">Vendor / Contractor</th>
                            <th class="px-4 py-3">Amount</th>
                            <th class="px-4 py-3 text-right">Approval Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60">
                        <?php $__currentLoopData = $funding['expenditure_history']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $exp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr class="hover:bg-slate-800/40 transition-colors">
                                <td class="px-4 py-3 font-mono font-bold text-emerald-400"><?php echo e($exp['id']); ?></td>
                                <td class="px-4 py-3 text-slate-400"><?php echo e($exp['date']); ?></td>
                                <td class="px-4 py-3 font-semibold text-white"><?php echo e($exp['project']); ?></td>
                                <td class="px-4 py-3 text-slate-300"><?php echo e($exp['category']); ?></td>
                                <td class="px-4 py-3 text-slate-200"><?php echo e($exp['vendor']); ?></td>
                                <td class="px-4 py-3 font-mono font-bold text-white">$<?php echo e(number_format($exp['amount'])); ?></td>
                                <td class="px-4 py-3 text-right">
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/15 text-emerald-300 border border-emerald-500/30">
                                        <?php echo e($exp['status']); ?>

                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
let isPublicMode = false;

function togglePublicMode() {
    isPublicMode = !isPublicMode;
    const label = document.getElementById('view-mode-label');
    if (isPublicMode) {
        label.textContent = "Public Transparency View";
        showToast("Switched to Public Transparency View (Sensors & contractor internals hidden)", "info");
    } else {
        label.textContent = "Manager Audit View";
        showToast("Switched to Manager Audit View", "info");
    }
}

(function() {
    const ctx = document.getElementById('fundingBreakdownChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['EU Water Fund', 'Federal Grant', 'Municipal Bond', 'AfDB Eco-Fund'],
            datasets: [{
                label: 'Funding Contribution ($)',
                data: [2100000, 1450000, 800000, 500000],
                backgroundColor: ['#0284c7', '#0d9488', '#3b82f6', '#8b5cf6']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { ticks: { color: '#64748b' }, grid: { color: 'rgba(255,255,255,0.05)' } },
                y: { ticks: { color: '#64748b' }, grid: { color: 'rgba(255,255,255,0.05)' } }
            }
        }
    });
})();

(function() {
    const ctx = document.getElementById('expenditureTimelineChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Q1', 'Q2', 'Q3 (Current)', 'Q4 (Target)'],
            datasets: [
                { label: 'Planned Budget ($)', data: [1200000, 2400000, 3600000, 4850000], borderColor: '#64748b', borderDash: [5,5], fill: false },
                { label: 'Actual Spent ($)', data: [1150000, 2300000, 3580000, null], borderColor: '#10b981', backgroundColor: 'rgba(16, 185, 129, 0.1)', fill: true, tension: 0.3 }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { labels: { color: '#94a3b8', font: { size: 11 } } } },
            scales: {
                x: { ticks: { color: '#64748b' }, grid: { color: 'rgba(255,255,255,0.05)' } },
                y: { ticks: { color: '#64748b' }, grid: { color: 'rgba(255,255,255,0.05)' } }
            }
        }
    });
})();
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\monta\Downloads\Aqua_Secure-main\Aqua_Secure-main\resources\views/manager/budget.blade.php ENDPATH**/ ?>