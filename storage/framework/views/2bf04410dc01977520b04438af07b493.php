<?php $__env->startSection('title', 'AquaSecure — Water Quality Management & Telemetry'); ?>

<?php
    use App\Data\PlaceholderData;
    $user         = session('user', ['name' => 'Dr. Selim Dridi', 'role' => 'manager']);
    $records      = PlaceholderData::waterQualityRecords();
    $municipalities = PlaceholderData::municipalities();
?>

<?php $__env->startPush('styles'); ?>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
#quality-map { height: 320px; border-radius: 1rem; }
.leaflet-tile-pane { filter: brightness(.88) contrast(1.1) saturate(0.8); }
</style>
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
                    <div class="w-8 h-8 rounded-xl bg-teal-500/20 border border-teal-500/30 flex items-center justify-center">
                        <i data-lucide="flask-conical" class="w-4 h-4 text-teal-400"></i>
                    </div>
                    <span class="font-display font-bold text-white text-base">Water Quality & Compliance</span>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <button onclick="openThresholdModal()" class="px-3 py-1.5 rounded-xl bg-teal-500/10 border border-teal-500/30 text-teal-300 hover:bg-teal-500/20 text-xs font-semibold flex items-center gap-2">
                    <i data-lucide="sliders" class="w-3.5 h-3.5"></i> Configure Thresholds
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
                <h1 class="text-2xl font-display font-bold text-white tracking-tight">Water Quality Telemetry & Sampling Records</h1>
                <p class="text-slate-400 text-xs mt-1">Laboratory certified analyses, online sensor thresholds, and contamination warnings.</p>
            </div>
            <div class="flex items-center gap-2">
                <button onclick="showToast('Exporting Water Quality Compliance Log (CSV)...', 'success')" class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 text-xs font-semibold flex items-center gap-2">
                    <i data-lucide="file-spreadsheet" class="w-4 h-4 text-teal-400"></i> Export Compliance CSV
                </button>
            </div>
        </div>

        
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            
            
            <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-4 relative">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs text-slate-400 font-bold uppercase">pH Level</span>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/15 text-emerald-300 border border-emerald-500/30">Target 6.5 - 8.5</span>
                </div>
                <div class="text-2xl font-display font-bold text-white">7.45 <span class="text-xs text-slate-400 font-normal">pH</span></div>
                <div class="w-full bg-slate-800 h-2 rounded-full mt-3 overflow-hidden">
                    <div class="bg-emerald-400 h-full rounded-full" style="width: 74.5%"></div>
                </div>
                <p class="text-[11px] text-slate-400 mt-2 flex items-center justify-between">
                    <span>99.2% Compliant</span>
                    <span class="text-emerald-400 font-semibold">Optimal</span>
                </p>
            </div>

            
            <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-4 relative">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs text-slate-400 font-bold uppercase">Turbidity</span>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-cyan-500/15 text-cyan-300 border border-cyan-500/30">Limit &lt; 1.0 NTU</span>
                </div>
                <div class="text-2xl font-display font-bold text-white">0.42 <span class="text-xs text-slate-400 font-normal">NTU</span></div>
                <div class="w-full bg-slate-800 h-2 rounded-full mt-3 overflow-hidden">
                    <div class="bg-cyan-400 h-full rounded-full" style="width: 42%"></div>
                </div>
                <p class="text-[11px] text-slate-400 mt-2 flex items-center justify-between">
                    <span>Clear Clarity</span>
                    <span class="text-cyan-400 font-semibold">Normal</span>
                </p>
            </div>

            
            <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-4 relative">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs text-slate-400 font-bold uppercase">Residual Chlorine</span>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-500/15 text-blue-300 border border-blue-500/30">0.2 - 2.0 mg/L</span>
                </div>
                <div class="text-2xl font-display font-bold text-white">0.85 <span class="text-xs text-slate-400 font-normal">mg/L</span></div>
                <div class="w-full bg-slate-800 h-2 rounded-full mt-3 overflow-hidden">
                    <div class="bg-blue-400 h-full rounded-full" style="width: 55%"></div>
                </div>
                <p class="text-[11px] text-slate-400 mt-2 flex items-center justify-between">
                    <span>Disinfection Active</span>
                    <span class="text-blue-400 font-semibold">Safe</span>
                </p>
            </div>

            
            <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-4 relative">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs text-slate-400 font-bold uppercase">Lead (Pb) Concentration</span>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-500/15 text-amber-300 border border-amber-500/30">Limit &lt; 15 ppb</span>
                </div>
                <div class="text-2xl font-display font-bold text-white">2.1 <span class="text-xs text-slate-400 font-normal">ppb</span></div>
                <div class="w-full bg-slate-800 h-2 rounded-full mt-3 overflow-hidden">
                    <div class="bg-emerald-400 h-full rounded-full" style="width: 14%"></div>
                </div>
                <p class="text-[11px] text-slate-400 mt-2 flex items-center justify-between">
                    <span>Lead Rule 2026</span>
                    <span class="text-emerald-400 font-semibold">Passed</span>
                </p>
            </div>
        </div>

        
        <div class="grid lg:grid-cols-2 gap-6">
            
            
            <div class="bg-slate-900/90 border border-slate-800 rounded-2xl overflow-hidden p-4">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-bold text-white flex items-center gap-2">
                        <i data-lucide="map-pin" class="w-4 h-4 text-teal-400"></i> Sampling Stations Map
                    </h3>
                    <span class="text-xs text-slate-400">4 Active Sampling Locations</span>
                </div>
                <div id="quality-map"></div>
            </div>

            
            <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-5">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-bold text-white flex items-center gap-2">
                        <i data-lucide="line-chart" class="w-4 h-4 text-teal-400"></i> 30-Day Water Quality Compliance Trend
                    </h3>
                    <span class="text-xs text-emerald-400 font-semibold bg-emerald-500/10 px-2 py-0.5 rounded">98.5% Average</span>
                </div>
                <div style="height: 240px;">
                    <canvas id="qualityTrendChart"></canvas>
                </div>
            </div>
        </div>

        
        <div class="bg-slate-900/90 border border-slate-800 rounded-2xl overflow-hidden shadow-xl">
            <div class="p-4 border-b border-slate-800 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-white flex items-center gap-2">
                        <i data-lucide="clipboard-list" class="w-4 h-4 text-teal-400"></i> Laboratory & Telemetry Sampling Records
                    </h3>
                    <p class="text-xs text-slate-400">Distinguishes verified laboratory results from raw automated sensor telemetry.</p>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-300">
                    <thead class="bg-slate-950/80 text-slate-400 uppercase font-bold text-[10px] tracking-wider border-b border-slate-800">
                        <tr>
                            <th class="px-4 py-3">Sample ID & Point</th>
                            <th class="px-4 py-3">Zone</th>
                            <th class="px-4 py-3">Date</th>
                            <th class="px-4 py-3">pH</th>
                            <th class="px-4 py-3">Turbidity</th>
                            <th class="px-4 py-3">Chlorine</th>
                            <th class="px-4 py-3">Lead (Pb)</th>
                            <th class="px-4 py-3">Verification</th>
                            <th class="px-4 py-3 text-right">Compliance</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60">
                        <?php $__currentLoopData = $records; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rec): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr class="hover:bg-slate-800/40 transition-colors">
                                <td class="px-4 py-3 font-semibold text-white">
                                    <div class="font-mono text-cyan-400 text-[11px]"><?php echo e($rec['id']); ?></div>
                                    <div><?php echo e($rec['sampling_point']); ?></div>
                                </td>
                                <td class="px-4 py-3 text-slate-200"><?php echo e($rec['zone']); ?></td>
                                <td class="px-4 py-3 text-slate-400"><?php echo e($rec['date']); ?></td>
                                <td class="px-4 py-3 font-mono <?php echo e($rec['pH'] > 8.5 ? 'text-red-400 font-bold' : 'text-slate-200'); ?>"><?php echo e($rec['pH']); ?></td>
                                <td class="px-4 py-3 font-mono <?php echo e($rec['turbidity'] > 1.0 ? 'text-amber-400 font-bold' : 'text-slate-200'); ?>"><?php echo e($rec['turbidity']); ?> NTU</td>
                                <td class="px-4 py-3 font-mono <?php echo e($rec['residual_chlorine'] < 0.2 ? 'text-red-400 font-bold' : 'text-slate-200'); ?>"><?php echo e($rec['residual_chlorine']); ?> mg/L</td>
                                <td class="px-4 py-3 font-mono <?php echo e($rec['lead_pb'] > 15 ? 'text-red-400 font-bold' : 'text-slate-200'); ?>"><?php echo e($rec['lead_pb']); ?> ppb</td>
                                <td class="px-4 py-3">
                                    <?php if($rec['is_verified']): ?>
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-teal-500/15 text-teal-300 border border-teal-500/30 inline-flex items-center gap-1">
                                            <i data-lucide="check-circle-2" class="w-3 h-3 text-teal-400"></i> Lab Verified
                                        </span>
                                    <?php else: ?>
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/15 text-amber-300 border border-amber-500/30 inline-flex items-center gap-1">
                                            <i data-lucide="radio" class="w-3 h-3 text-amber-400"></i> Unverified Telemetry
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3 text-right font-bold <?php echo e($rec['status'] === 'Compliant' ? 'text-emerald-400' : ($rec['status'] === 'Alert' ? 'text-amber-400' : 'text-red-400')); ?>">
                                    <?php echo e($rec['status']); ?> (<?php echo e($rec['overall_compliance']); ?>%)
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>


<div id="threshold-modal" class="hidden fixed inset-0 z-50 bg-black/70 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-slate-900 border border-slate-700 rounded-2xl max-w-md w-full p-6 space-y-4 shadow-2xl">
        <div class="flex items-center justify-between">
            <h3 class="text-base font-bold text-white flex items-center gap-2">
                <i data-lucide="sliders" class="w-5 h-5 text-teal-400"></i> Water Quality Alert Thresholds
            </h3>
            <button onclick="closeThresholdModal()" class="text-slate-400 hover:text-white">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <p class="text-xs text-slate-400">Configure safety trigger limits for automatic SMS & email dispatch to technicians.</p>
        
        <div class="space-y-3 text-xs text-slate-200">
            <div>
                <label class="block mb-1 font-semibold">Maximum Allowed pH Level (Upper Bound)</label>
                <input type="number" step="0.1" value="8.5" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-white">
            </div>
            <div>
                <label class="block mb-1 font-semibold">Maximum Turbidity Threshold (NTU)</label>
                <input type="number" step="0.1" value="1.0" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-white">
            </div>
            <div>
                <label class="block mb-1 font-semibold">Minimum Residual Chlorine (mg/L)</label>
                <input type="number" step="0.05" value="0.20" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-white">
            </div>
            <div>
                <label class="block mb-1 font-semibold">Lead Action Level (ppb)</label>
                <input type="number" value="15" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-white">
            </div>
        </div>

        <div class="flex items-center justify-end gap-2 pt-2">
            <button onclick="closeThresholdModal()" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 text-xs font-semibold">Cancel</button>
            <button onclick="closeThresholdModal(); showToast('Threshold parameters updated successfully!', 'success');" class="px-4 py-2 rounded-xl bg-teal-500 hover:bg-teal-400 text-slate-950 font-bold text-xs">Save Thresholds</button>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
const RECORDS = <?php echo json_encode($records, 15, 512) ?>;

function openThresholdModal() { document.getElementById('threshold-modal').classList.remove('hidden'); }
function closeThresholdModal() { document.getElementById('threshold-modal').classList.add('hidden'); }

(function() {
    const map = L.map('quality-map', { center: [36.2, 10.3], zoom: 7, zoomControl: false, attributionControl: false });
    L.control.zoom({ position: 'topright' }).addTo(map);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 18 }).addTo(map);

    RECORDS.forEach(r => {
        const c = r.status === 'Compliant' ? '#2dd4bf' : (r.status === 'Alert' ? '#fbbf24' : '#ef4444');
        const svg = `<svg xmlns="http://www.w3.org/2000/svg" width="30" height="30">
            <circle cx="15" cy="15" r="12" fill="${c}" fill-opacity="0.8" stroke="#ffffff" stroke-width="2"/>
        </svg>`;
        L.marker([r.lat, r.lng], {
            icon: L.divIcon({ html: svg, iconSize:[30,30], iconAnchor:[15,15], className:'' })
        }).addTo(map).bindPopup(`<b>${r.sampling_point}</b><br>Compliance: ${r.overall_compliance}%<br>pH: ${r.pH} | Turbidity: ${r.turbidity} NTU`);
    });
})();

(function() {
    const ctx = document.getElementById('qualityTrendChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
            datasets: [
                { label: 'Compliance Score %', data: [97.2, 98.4, 96.8, 98.5], borderColor: '#2dd4bf', backgroundColor: 'rgba(45, 212, 191, 0.1)', fill: true, tension: 0.3 },
                { label: 'pH Average', data: [7.38, 7.42, 7.50, 7.35], borderColor: '#3b82f6', tension: 0.3 }
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

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\monta\Downloads\Aqua_Secure-main\Aqua_Secure-main\resources\views/manager/quality.blade.php ENDPATH**/ ?>