<?php $__env->startSection('title', 'AquaSecure — Qualité de l’eau et mesures'); ?>

<?php
    $user         = session('user', ['name' => 'Dr. Selim Dridi', 'role' => 'manager']);
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
                    <span class="font-display font-bold text-white text-base">Qualité de l’eau et conformité</span>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <button onclick="openThresholdModal()" class="px-3 py-1.5 rounded-xl bg-teal-500/10 border border-teal-500/30 text-teal-300 hover:bg-teal-500/20 text-xs font-semibold flex items-center gap-2">
                    <i data-lucide="sliders" class="w-3.5 h-3.5"></i> Régler les seuils
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
                <h1 class="text-2xl font-display font-bold text-white tracking-tight">Qualité de l’eau : mesures et prélèvements</h1>
                <p class="text-slate-400 text-xs mt-1">Analyses de laboratoire, seuils de contrôle et alertes de contamination.</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="<?php echo e(route('manager.points-mesure.index')); ?>" class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 text-xs font-semibold">Points de prélèvement</a>
                <?php if($activePointsCount > 0): ?>
                    <a href="<?php echo e(route('manager.quality.mesures.create')); ?>" class="px-3.5 py-2 rounded-xl bg-teal-500 hover:bg-teal-400 text-slate-950 text-xs font-bold">Nouvelle mesure</a>
                <?php else: ?>
                    <a href="<?php echo e(route('manager.points-mesure.create')); ?>" class="px-3.5 py-2 rounded-xl bg-teal-500 hover:bg-teal-400 text-slate-950 text-xs font-bold">Créer un point</a>
                <?php endif; ?>
                <button onclick="showToast('Exporting Water Quality Compliance Log (CSV)...', 'success')" class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 text-xs font-semibold flex items-center gap-2">
                    <i data-lucide="file-spreadsheet" class="w-4 h-4 text-teal-400"></i> Exporter le rapport CSV
                </button>
            </div>
        </div>

        
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            
            
            <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-4 relative">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs text-slate-400 font-bold uppercase">pH Level</span>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/15 text-emerald-300 border border-emerald-500/30">Target 6.5 - 8.5</span>
                </div>
                <div class="text-2xl font-display font-bold text-white"><?php echo e($latestMeasurement?->ph ?? '—'); ?> <span class="text-xs text-slate-400 font-normal">pH</span></div>
                <div class="w-full bg-slate-800 h-2 rounded-full mt-3 overflow-hidden">
                    <div class="bg-emerald-400 h-full rounded-full" style="width: <?php echo e($latestMeasurement ? min(((float) $latestMeasurement->ph / 14) * 100, 100) : 0); ?>%"></div>
                </div>
                <p class="text-[11px] text-slate-400 mt-2 flex items-center justify-between">
                    <span>Dernière mesure</span>
                    <span class="text-emerald-400 font-semibold"><?php echo e($latestAssessment['parameter_statuses']['ph'] ?? 'Aucune donnée'); ?></span>
                </p>
            </div>

            
            <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-4 relative">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs text-slate-400 font-bold uppercase">Turbidity</span>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-cyan-500/15 text-cyan-300 border border-cyan-500/30">Limit &lt; 1.0 NTU</span>
                </div>
                <div class="text-2xl font-display font-bold text-white"><?php echo e($latestMeasurement?->turbidite ?? '—'); ?> <span class="text-xs text-slate-400 font-normal">NTU</span></div>
                <div class="w-full bg-slate-800 h-2 rounded-full mt-3 overflow-hidden">
                    <div class="bg-cyan-400 h-full rounded-full" style="width: <?php echo e($latestMeasurement ? min(((float) $latestMeasurement->turbidite / 2) * 100, 100) : 0); ?>%"></div>
                </div>
                <p class="text-[11px] text-slate-400 mt-2 flex items-center justify-between">
                    <span>Dernière mesure</span>
                    <span class="text-cyan-400 font-semibold"><?php echo e($latestAssessment['parameter_statuses']['turbidite'] ?? 'Aucune donnée'); ?></span>
                </p>
            </div>

            
            <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-4 relative">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs text-slate-400 font-bold uppercase">Chlore résiduel</span>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-500/15 text-blue-300 border border-blue-500/30">0.2 - 2.0 mg/L</span>
                </div>
                <div class="text-2xl font-display font-bold text-white"><?php echo e($latestMeasurement?->chlore_residuel ?? '—'); ?> <span class="text-xs text-slate-400 font-normal">mg/L</span></div>
                <div class="w-full bg-slate-800 h-2 rounded-full mt-3 overflow-hidden">
                    <div class="bg-blue-400 h-full rounded-full" style="width: <?php echo e($latestMeasurement ? min(((float) $latestMeasurement->chlore_residuel / 2) * 100, 100) : 0); ?>%"></div>
                </div>
                <p class="text-[11px] text-slate-400 mt-2 flex items-center justify-between">
                    <span>Dernière mesure</span>
                    <span class="text-blue-400 font-semibold"><?php echo e($latestAssessment['parameter_statuses']['chlore_residuel'] ?? 'Aucune donnée'); ?></span>
                </p>
            </div>

            
            <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-4 relative">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs text-slate-400 font-bold uppercase">Lead (Pb) Concentration</span>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-500/15 text-amber-300 border border-amber-500/30">Limit &lt; 15 ppb</span>
                </div>
                <div class="text-2xl font-display font-bold text-white"><?php echo e($latestMeasurement?->plomb ?? '—'); ?> <span class="text-xs text-slate-400 font-normal">ppb</span></div>
                <div class="w-full bg-slate-800 h-2 rounded-full mt-3 overflow-hidden">
                    <div class="bg-emerald-400 h-full rounded-full" style="width: <?php echo e($latestMeasurement ? min(((float) $latestMeasurement->plomb / 15) * 100, 100) : 0); ?>%"></div>
                </div>
                <p class="text-[11px] text-slate-400 mt-2 flex items-center justify-between">
                    <span>Dernière mesure</span>
                    <span class="text-emerald-400 font-semibold"><?php echo e($latestAssessment['parameter_statuses']['plomb'] ?? 'Aucune donnée'); ?></span>
                </p>
            </div>

            <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-4 relative">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs text-slate-400 font-bold uppercase">Nitrates</span>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-700/60 text-slate-300 border border-slate-600">Seuil configurable</span>
                </div>
                <div class="text-2xl font-display font-bold text-white"><?php echo e($latestMeasurement?->nitrates ?? '—'); ?> <span class="text-xs text-slate-400 font-normal">mg/L</span></div>
                <div class="w-full bg-slate-800 h-2 rounded-full mt-3 overflow-hidden">
                    <div class="bg-violet-400 h-full rounded-full" style="width: <?php echo e($latestMeasurement ? min(((float) $latestMeasurement->nitrates / 100) * 100, 100) : 0); ?>%"></div>
                </div>
                <p class="text-[11px] text-slate-400 mt-2 flex items-center justify-between">
                    <span><?php echo e(config('water_quality.nitrates_max') !== null ? 'Maximum '.config('water_quality.nitrates_max').' mg/L' : 'Aucune limite configurée'); ?></span>
                    <span class="text-violet-300 font-semibold"><?php echo e($latestAssessment['parameter_statuses']['nitrates'] ?? ($latestMeasurement ? 'Non évalué' : 'Aucune donnée')); ?></span>
                </p>
            </div>
        </div>

        
        <div class="grid lg:grid-cols-2 gap-6">
            
            
            <div class="bg-slate-900/90 border border-slate-800 rounded-2xl overflow-hidden p-4">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-bold text-white flex items-center gap-2">
                        <i data-lucide="map-pin" class="w-4 h-4 text-teal-400"></i> Carte des stations de prélèvement
                    </h3>
                    <span class="text-xs text-slate-400"><?php echo e($activePointsCount); ?> points de prélèvement actifs</span>
                </div>
                <div id="quality-map"></div>
            </div>

            
            <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-5">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-bold text-white flex items-center gap-2">
                        <i data-lucide="line-chart" class="w-4 h-4 text-teal-400"></i> Conformité de la qualité de l’eau sur 30 jours
                    </h3>
                    <span class="text-xs text-emerald-400 font-semibold bg-emerald-500/10 px-2 py-0.5 rounded"><?php echo e($averageCompliance !== null ? $averageCompliance.'% moyenne' : 'Aucune mesure'); ?></span>
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
                        <i data-lucide="clipboard-list" class="w-4 h-4 text-teal-400"></i> Résultats d'analyses et suivi de la qualité
                    </h3>
                    <p class="text-xs text-slate-400">Consultez les résultats vérifiés par laboratoire et les relevés en attente de validation.</p>
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
                            <th class="px-4 py-3">Nitrates</th>
                            <th class="px-4 py-3">Verification</th>
                            <th class="px-4 py-3 text-right">Compliance</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60">
                        <?php $__empty_1 = true; $__currentLoopData = $records; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rec): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="hover:bg-slate-800/40 transition-colors">
                                <td class="px-4 py-3 font-semibold text-white">
                                    <a href="<?php echo e(route('manager.quality.mesures.show', $rec['measurement_id'])); ?>" class="font-mono text-cyan-400 text-[11px] hover:underline"><?php echo e($rec['id']); ?></a>
                                    <div><a href="<?php echo e(route('manager.points-mesure.index')); ?>"><?php echo e($rec['sampling_point']); ?></a></div>
                                </td>
                                <td class="px-4 py-3 text-slate-200"><?php echo e($rec['zone']); ?></td>
                                <td class="px-4 py-3 text-slate-400"><?php echo e($rec['date']); ?></td>
                                <td class="px-4 py-3 font-mono <?php echo e($rec['pH'] > 8.5 ? 'text-red-400 font-bold' : 'text-slate-200'); ?>"><?php echo e($rec['pH']); ?></td>
                                <td class="px-4 py-3 font-mono <?php echo e($rec['turbidity'] > 1.0 ? 'text-amber-400 font-bold' : 'text-slate-200'); ?>"><?php echo e($rec['turbidity']); ?> NTU</td>
                                <td class="px-4 py-3 font-mono <?php echo e($rec['residual_chlorine'] < 0.2 ? 'text-red-400 font-bold' : 'text-slate-200'); ?>"><?php echo e($rec['residual_chlorine']); ?> mg/L</td>
                                <td class="px-4 py-3 font-mono <?php echo e($rec['lead_pb'] > 15 ? 'text-red-400 font-bold' : 'text-slate-200'); ?>"><?php echo e($rec['lead_pb']); ?> ppb</td>
                                <td class="px-4 py-3 font-mono text-slate-200"><?php echo e($rec['nitrates']); ?> mg/L</td>
                                <td class="px-4 py-3">
                                    <?php if($rec['is_verified']): ?>
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-teal-500/15 text-teal-300 border border-teal-500/30 inline-flex items-center gap-1">
                                            <i data-lucide="check-circle-2" class="w-3 h-3 text-teal-400"></i> Vérifié en laboratoire
                                        </span>
                                    <?php else: ?>
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/15 text-amber-300 border border-amber-500/30 inline-flex items-center gap-1">
                                            <i data-lucide="clipboard-check" class="w-3 h-3 text-amber-400"></i> Relevé à vérifier
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3 text-right font-bold <?php echo e($rec['status'] === 'Compliant' ? 'text-emerald-400' : ($rec['status'] === 'Alert' ? 'text-amber-400' : 'text-red-400')); ?>">
                                    <?php echo e($rec['status']); ?> (<?php echo e($rec['overall_compliance']); ?>%)
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr><td colspan="10" class="px-4 py-10 text-center text-slate-400">Aucune mesure enregistrée. Créez un point de prélèvement puis ajoutez une mesure.</td></tr>
                        <?php endif; ?>
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
                <i data-lucide="sliders" class="w-5 h-5 text-teal-400"></i> Seuils d’alerte pour la qualité de l’eau
            </h3>
            <button onclick="closeThresholdModal()" class="text-slate-400 hover:text-white">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <p class="text-xs text-slate-400">Configure safety trigger limits for automatic SMS & email dispatch to technicians.</p>
        
        <div class="space-y-3 text-xs text-slate-200">
            <div>
                <label class="block mb-1 font-semibold">pH maximal autorisé</label>
                <input type="number" step="0.1" value="8.5" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-white">
            </div>
            <div>
                <label class="block mb-1 font-semibold">Seuil maximal de turbidité (NTU)</label>
                <input type="number" step="0.1" value="1.0" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-white">
            </div>
            <div>
                <label class="block mb-1 font-semibold">Minimum Chlore résiduel (mg/L)</label>
                <input type="number" step="0.05" value="0.20" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-white">
            </div>
            <div>
                <label class="block mb-1 font-semibold">Seuil d’action pour le plomb (ppb)</label>
                <input type="number" value="15" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-white">
            </div>
        </div>

        <div class="flex items-center justify-end gap-2 pt-2">
            <button onclick="closeThresholdModal()" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 text-xs font-semibold">Cancel</button>
            <button onclick="closeThresholdModal(); showToast('Threshold parameters updated successfully!', 'success');" class="px-4 py-2 rounded-xl bg-teal-500 hover:bg-teal-400 text-slate-950 font-bold text-xs">Enregistrer les seuils</button>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
const POINTS = <?php echo json_encode($points, 15, 512) ?>;

function openThresholdModal() { document.getElementById('threshold-modal').classList.remove('hidden'); }
function closeThresholdModal() { document.getElementById('threshold-modal').classList.add('hidden'); }

(function() {
    const map = L.map('quality-map', { center: [36.2, 10.3], zoom: 7, zoomControl: false, attributionControl: false });
    L.control.zoom({ position: 'topright' }).addTo(map);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 18 }).addTo(map);

    POINTS.forEach(point => {
        const r = point.measurement;
        const c = point.status === 'normal' ? '#2dd4bf' : (point.status === 'alert' ? '#fbbf24' : '#ef4444');
        const svg = `<svg xmlns="http://www.w3.org/2000/svg" width="30" height="30">
            <circle cx="15" cy="15" r="12" fill="${c}" fill-opacity="0.8" stroke="#ffffff" stroke-width="2"/>
        </svg>`;
        const details = r
            ? `Réf. ${escapeHtml(r.reference)}<br>Conformité: ${point.overall_compliance}%<br>pH: ${r.ph} | Turbidité: ${r.turbidite} NTU<br>Chlore: ${r.chlore_residuel} mg/L | Plomb: ${r.plomb} ppb | Nitrates: ${r.nitrates} mg/L`
            : 'Aucune mesure';
        L.marker([point.lat, point.lng], {
            icon: L.divIcon({ html: svg, iconSize:[30,30], iconAnchor:[15,15], className:'' })
        }).addTo(map).bindPopup(`<b>${escapeHtml(point.name)}</b><br>${details}`);
    });
})();

function escapeHtml(value) {
    return String(value ?? '').replace(/[&<>"']/g, char => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
    })[char]);
}

(function() {
    const ctx = document.getElementById('qualityTrendChart').getContext('2d');
    const chartRecords = <?php echo json_encode($chartRecords, 15, 512) ?>;
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: chartRecords.map(record => record.date),
            datasets: [
                { label: 'Taux de conformité (%)', data: chartRecords.map(record => record.overall_compliance), borderColor: '#2dd4bf', backgroundColor: 'rgba(45, 212, 191, 0.1)', fill: true, tension: 0.3 },
                { label: 'pH', data: chartRecords.map(record => record.pH), borderColor: '#3b82f6', tension: 0.3 }
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

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\monta\Downloads\DEVWAVE\Esprit-5TWIN4-DevWave-main (1)\Esprit-5TWIN4-DevWave-main\resources\views\manager\quality.blade.php ENDPATH**/ ?>