<?php
    use App\Data\PlaceholderData;
    $title = 'Incidents — AquaSecure';
    $reclamations = PlaceholderData::adminAllReclamations();
    $technicians  = PlaceholderData::adminTechnicians();
    $available    = array_values(array_filter($technicians, fn($t) => $t['status'] === 'available'));

    $pending    = count(array_filter($reclamations, fn($r) => $r['status'] === 'pending'));
    $inProgress = count(array_filter($reclamations, fn($r) => $r['status'] === 'in_progress'));
    $resolved   = count(array_filter($reclamations, fn($r) => $r['status'] === 'resolved'));
    $critical   = count(array_filter($reclamations, fn($r) => $r['priority'] === 'critical'));

    $statusStyle = [
        'pending'     => ['bg'=>'bg-red-500/15',   'text'=>'text-red-300',   'dot'=>'bg-red-400 animate-pulse','label'=>'Non traité'],
        'in_progress' => ['bg'=>'bg-amber-500/15', 'text'=>'text-amber-300', 'dot'=>'bg-amber-400',            'label'=>'En cours'],
        'resolved'    => ['bg'=>'bg-teal-500/15',  'text'=>'text-teal-300',  'dot'=>'bg-teal-400',             'label'=>'Résolu'],
    ];
    $priorityStyle = [
        'critical' => ['bg'=>'bg-red-500/15',  'text'=>'text-red-300',  'label'=>'Critique'],
        'medium'   => ['bg'=>'bg-amber-500/15','text'=>'text-amber-300','label'=>'Moyenne'],
        'low'      => ['bg'=>'bg-blue-500/15', 'text'=>'text-blue-300', 'label'=>'Faible'],
    ];
?>

<?php $__env->startSection('manager-content'); ?>
<div class="space-y-6 animate-fade-in-up">

    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-display font-bold text-white">Gestion des incidents</h1>
            <p class="text-cyan-100/55 text-sm mt-0.5">Affectation, suivi et clôture des signalements terrain</p>
        </div>
        <div class="flex gap-2">
            <a href="<?php echo e(route('manager.map')); ?>"
               class="glass px-4 py-2 rounded-xl text-sm text-cyan-300 hover:text-white font-semibold flex items-center gap-2 transition-all hover:border-cyan-400/40">
                <i data-lucide="map" class="w-4 h-4"></i>
                <span class="hidden sm:inline">Voir sur la carte</span>
            </a>
            <button type="button" onclick="showToast('Export CSV à implémenter', 'info')"
                    class="glass px-4 py-2 rounded-xl text-sm text-cyan-300 hover:text-white font-semibold flex items-center gap-2 transition-all hover:border-cyan-400/40">
                <i data-lucide="download" class="w-4 h-4"></i>
                Exporter
            </button>
        </div>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <?php $__currentLoopData = [
            ['val'=>count($reclamations),'label'=>'Total','icon'=>'layers','bg'=>'bg-cyan-500/10','ic'=>'text-cyan-400'],
            ['val'=>$pending,           'label'=>'Non traités','icon'=>'alert-triangle','bg'=>'bg-red-500/10','ic'=>'text-red-400'],
            ['val'=>$inProgress,        'label'=>'En cours','icon'=>'loader','bg'=>'bg-amber-500/10','ic'=>'text-amber-400'],
            ['val'=>$critical,          'label'=>'Critiques','icon'=>'flame','bg'=>'bg-rose-500/10','ic'=>'text-rose-400'],
        ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $kpi): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="glass rounded-2xl p-4 hover-lift">
            <div class="w-10 h-10 rounded-xl <?php echo e($kpi['bg']); ?> flex items-center justify-center mb-3">
                <i data-lucide="<?php echo e($kpi['icon']); ?>" class="w-5 h-5 <?php echo e($kpi['ic']); ?>"></i>
            </div>
            <p class="text-2xl font-display font-bold text-white"><?php echo e($kpi['val']); ?></p>
            <p class="text-xs font-semibold text-cyan-100/60 mt-0.5"><?php echo e($kpi['label']); ?></p>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    <div class="glass rounded-2xl p-4 flex flex-col lg:flex-row gap-3">
        <div class="relative flex-1">
            <i data-lucide="search" class="w-4 h-4 text-cyan-400/70 absolute left-3 top-1/2 -translate-y-1/2"></i>
            <input id="inc-search" type="search" placeholder="Rechercher un incident, une zone, un citoyen…"
                   class="w-full bg-white/5 border border-white/10 rounded-xl pl-10 pr-4 py-2.5 text-sm text-white placeholder:text-cyan-100/35 focus:outline-none focus:border-cyan-400/40">
        </div>
        <div class="flex flex-wrap gap-2">
            <select id="inc-status" class="glass px-3 py-2 rounded-xl text-sm text-cyan-200 bg-transparent border border-white/10 focus:outline-none">
                <option value="all">Tous les statuts</option>
                <option value="pending">Non traités</option>
                <option value="in_progress">En cours</option>
                <option value="resolved">Résolus</option>
            </select>
            <select id="inc-priority" class="glass px-3 py-2 rounded-xl text-sm text-cyan-200 bg-transparent border border-white/10 focus:outline-none">
                <option value="all">Toutes priorités</option>
                <option value="critical">Critique</option>
                <option value="medium">Moyenne</option>
                <option value="low">Faible</option>
            </select>
        </div>
    </div>

    <div class="glass rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="border-b border-white/5 text-[11px] uppercase tracking-wide text-cyan-100/40">
                        <th class="px-4 py-3 font-semibold">Réf.</th>
                        <th class="px-4 py-3 font-semibold">Incident</th>
                        <th class="px-4 py-3 font-semibold">Zone</th>
                        <th class="px-4 py-3 font-semibold">Priorité</th>
                        <th class="px-4 py-3 font-semibold">Statut</th>
                        <th class="px-4 py-3 font-semibold">Technicien</th>
                        <th class="px-4 py-3 font-semibold text-right">Action</th>
                    </tr>
                </thead>
                <tbody id="inc-body" class="divide-y divide-white/[.04]">
                    <?php $__currentLoopData = $reclamations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rec): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $ss = $statusStyle[$rec['status']];
                        $ps = $priorityStyle[$rec['priority']];
                    ?>
                    <tr class="inc-row hover:bg-white/[.025] transition-colors"
                        data-status="<?php echo e($rec['status']); ?>"
                        data-priority="<?php echo e($rec['priority']); ?>"
                        data-search="<?php echo e(strtolower($rec['id'].' '.$rec['type'].' '.$rec['zone'].' '.$rec['citizen'])); ?>">
                        <td class="px-4 py-3.5">
                            <span class="text-[11px] font-mono font-bold text-cyan-400"><?php echo e($rec['id']); ?></span>
                            <p class="text-[10px] text-cyan-100/30 mt-0.5"><?php echo e($rec['created_at']); ?></p>
                        </td>
                        <td class="px-4 py-3.5 min-w-[220px]">
                            <p class="text-white text-sm font-semibold"><?php echo e($rec['type']); ?></p>
                            <p class="text-cyan-100/45 text-xs mt-0.5 line-clamp-1"><?php echo e($rec['description']); ?></p>
                            <p class="text-cyan-100/35 text-[11px] mt-0.5"><?php echo e($rec['citizen']); ?></p>
                        </td>
                        <td class="px-4 py-3.5 text-sm text-cyan-100/70 whitespace-nowrap"><?php echo e($rec['zone']); ?></td>
                        <td class="px-4 py-3.5">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold <?php echo e($ps['bg']); ?> <?php echo e($ps['text']); ?>"><?php echo e($ps['label']); ?></span>
                        </td>
                        <td class="px-4 py-3.5">
                            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-bold <?php echo e($ss['bg']); ?> <?php echo e($ss['text']); ?>">
                                <span class="w-1.5 h-1.5 rounded-full <?php echo e($ss['dot']); ?>"></span>
                                <?php echo e($ss['label']); ?>

                            </span>
                        </td>
                        <td class="px-4 py-3.5 text-sm <?php echo e($rec['technician'] ? 'text-white' : 'text-cyan-100/35 italic'); ?>">
                            <?php echo e($rec['technician'] ?? 'Non affecté'); ?>

                        </td>
                        <td class="px-4 py-3.5 text-right whitespace-nowrap">
                            <?php if($rec['status'] === 'pending'): ?>
                            <button type="button"
                                    onclick="assignIncident('<?php echo e($rec['id']); ?>')"
                                    class="glass px-2.5 py-1.5 rounded-lg text-[11px] text-teal-300 hover:text-white font-semibold">
                                Affecter
                            </button>
                            <?php elseif($rec['status'] === 'in_progress'): ?>
                            <button type="button"
                                    onclick="showToast('Incident <?php echo e($rec['id']); ?> clôturé (démo)', 'success')"
                                    class="glass px-2.5 py-1.5 rounded-lg text-[11px] text-cyan-300 hover:text-white font-semibold">
                                Clôturer
                            </button>
                            <?php else: ?>
                            <a href="<?php echo e(route('manager.map')); ?>" class="text-[11px] text-cyan-400 hover:text-cyan-300 font-semibold">Carte →</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
        <p id="inc-empty" class="hidden px-5 py-10 text-center text-sm text-cyan-100/45">Aucun incident ne correspond aux filtres.</p>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
const AVAILABLE_TECHS = <?php echo json_encode(array_column($available, 'name'), 512) ?>;

function filterIncidents() {
    const q = (document.getElementById('inc-search').value || '').toLowerCase().trim();
    const st = document.getElementById('inc-status').value;
    const pr = document.getElementById('inc-priority').value;
    let visible = 0;
    document.querySelectorAll('.inc-row').forEach(row => {
        const okQ = !q || row.dataset.search.includes(q);
        const okS = st === 'all' || row.dataset.status === st;
        const okP = pr === 'all' || row.dataset.priority === pr;
        const show = okQ && okS && okP;
        row.classList.toggle('hidden', !show);
        if (show) visible++;
    });
    document.getElementById('inc-empty').classList.toggle('hidden', visible > 0);
}

function assignIncident(id) {
    const tech = AVAILABLE_TECHS[0] || 'un technicien disponible';
    showToast(id + ' affecté à ' + tech + ' (démo)', 'success');
}

['inc-search', 'inc-status', 'inc-priority'].forEach(id => {
    document.getElementById(id).addEventListener('input', filterIncidents);
    document.getElementById(id).addEventListener('change', filterIncidents);
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.manager', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\ghada\Desktop\5ème\projet_Laravel\aquasecure\resources\views/manager/incidents.blade.php ENDPATH**/ ?>