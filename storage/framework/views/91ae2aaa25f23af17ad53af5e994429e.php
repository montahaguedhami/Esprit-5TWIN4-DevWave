<?php $__env->startSection('title', 'Détail de la facture'); ?>

<?php
    use App\Data\PlaceholderData;
    $invoices = PlaceholderData::citizenInvoices();
    // For demo, use the first invoice
    $invoice = $invoices[0];
    $user = session('user', ['name' => 'Yassine Hamdi', 'email' => 'citoyen@aquasecure.tn']);
?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between mb-8">
        <div class="flex items-center gap-4">
            <a href="<?php echo e(route('citizen.invoices.index')); ?>" class="w-10 h-10 rounded-xl glass flex items-center justify-center hover:border-cyan-400/40 transition-colors">
                <i data-lucide="arrow-left" class="w-5 h-5 text-cyan-300"></i>
            </a>
            <div>
                <h1 class="text-2xl sm:text-3xl font-display font-bold text-white">Facture <?php echo e($invoice['id']); ?></h1>
                <p class="text-cyan-100/60 text-sm mt-1"><?php echo e($invoice['month']); ?></p>
            </div>
        </div>
        
        <div class="flex items-center gap-2">
            <?php if (isset($component)) { $__componentOriginala8bb031a483a05f647cb99ed3a469847 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala8bb031a483a05f647cb99ed3a469847 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.button','data' => ['variant' => 'outline','size' => 'sm','icon' => 'printer']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'outline','size' => 'sm','icon' => 'printer']); ?>
                Imprimer
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.button','data' => ['variant' => 'outline','size' => 'sm','icon' => 'download']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'outline','size' => 'sm','icon' => 'download']); ?>
                Télécharger PDF
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
    </div>

    <!-- Invoice Content -->
    <div class="max-w-4xl mx-auto">
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
            <!-- Invoice Header -->
            <div class="flex items-start justify-between pb-8 border-b border-white/5 mb-8">
                <!-- Company Info -->
                <div>
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-cyan-400 to-blue-600 flex items-center justify-center shadow-lg shadow-cyan-500/30">
                            <i data-lucide="droplet" class="w-8 h-8 text-white"></i>
                        </div>
                        <div>
                            <div class="font-display text-2xl font-bold text-white">AquaSecure</div>
                            <div class="text-xs text-cyan-100/50">Gestion intelligente de l'eau</div>
                        </div>
                    </div>
                    <div class="text-sm text-cyan-100/60 space-y-1">
                        <p>Boulevard de l'Environnement</p>
                        <p>1053 Tunis, Tunisie</p>
                        <p>Tél: +216 71 234 567</p>
                    </div>
                </div>
                
                <!-- Invoice Meta -->
                <div class="text-right">
                    <div class="text-xs text-cyan-100/50 mb-1">Numéro de facture</div>
                    <div class="text-xl font-display font-bold text-white mb-4"><?php echo e($invoice['id']); ?></div>
                    <?php if (isset($component)) { $__componentOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.status-badge','data' => ['status' => $invoice['status'] === 'pending' ? 'warning' : 'success','size' => 'lg']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($invoice['status'] === 'pending' ? 'warning' : 'success'),'size' => 'lg']); ?>
                        <?php echo e($invoice['status'] === 'pending' ? 'À PAYER' : 'PAYÉE'); ?>

                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8)): ?>
<?php $attributes = $__attributesOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8; ?>
<?php unset($__attributesOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8)): ?>
<?php $component = $__componentOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8; ?>
<?php unset($__componentOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8); ?>
<?php endif; ?>
                </div>
            </div>

            <!-- Customer & Dates -->
            <div class="grid md:grid-cols-2 gap-8 pb-8 border-b border-white/5 mb-8">
                <!-- Customer Info -->
                <div>
                    <h3 class="text-xs font-semibold text-cyan-100/50 uppercase mb-3">Facturé à</h3>
                    <div class="text-sm text-white space-y-1">
                        <p class="font-semibold"><?php echo e($user['name']); ?></p>
                        <p><?php echo e($user['email']); ?></p>
                        <p>42 Rue Habib Bourguiba</p>
                        <p>Tunis Nord, Tunisie</p>
                    </div>
                </div>
                
                <!-- Invoice Dates -->
                <div>
                    <h3 class="text-xs font-semibold text-cyan-100/50 uppercase mb-3">Informations</h3>
                    <div class="space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-cyan-100/60">Date d'émission :</span>
                            <span class="text-white font-medium"><?php echo e(\Carbon\Carbon::parse($invoice['issue_date'])->format('d/m/Y')); ?></span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-cyan-100/60">Date limite :</span>
                            <span class="text-white font-medium"><?php echo e(\Carbon\Carbon::parse($invoice['due_date'])->format('d/m/Y')); ?></span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-cyan-100/60">Période :</span>
                            <span class="text-white font-medium"><?php echo e($invoice['month']); ?></span>
                        </div>
                        <?php if($invoice['status'] === 'paid'): ?>
                        <div class="flex justify-between text-sm">
                            <span class="text-cyan-100/60">Payée le :</span>
                            <span class="text-emerald-400 font-medium"><?php echo e(\Carbon\Carbon::parse($invoice['paid_date'])->format('d/m/Y')); ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Consumption Details -->
            <div class="mb-8">
                <h3 class="text-lg font-display font-bold text-white mb-4">Détails de consommation</h3>
                
                <!-- Consumption Chart -->
                <div class="glass p-6 rounded-xl mb-6">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <div class="text-4xl font-display font-bold text-white"><?php echo e($invoice['consumption']); ?> m³</div>
                            <div class="text-sm text-cyan-100/60 mt-1">Consommation totale</div>
                        </div>
                        <div class="w-20 h-20 rounded-full bg-gradient-to-br from-cyan-500 to-blue-600 flex items-center justify-center">
                            <i data-lucide="droplets" class="w-10 h-10 text-white"></i>
                        </div>
                    </div>
                    
                    <!-- Bar Chart Placeholder -->
                    <div class="flex items-end justify-between h-32 gap-2">
                        <?php $__currentLoopData = [15, 18, 16, 19, 17, 20, 18.5]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="flex-1 flex flex-col items-center gap-2">
                            <div class="w-full bg-gradient-to-t from-cyan-500 to-blue-600 rounded-t transition-all hover:from-cyan-400 hover:to-blue-500" style="height: <?php echo e(($value / 20) * 100); ?>%"></div>
                            <span class="text-xs text-cyan-100/50">S<?php echo e($index + 1); ?></span>
                        </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
                
                <!-- Consumption Table -->
                <div class="glass rounded-xl overflow-hidden">
                    <table class="w-full">
                        <thead class="bg-slate-950/50">
                            <tr>
                                <th class="text-left px-6 py-3 text-xs font-semibold text-cyan-100/70 uppercase">Description</th>
                                <th class="text-center px-6 py-3 text-xs font-semibold text-cyan-100/70 uppercase">Quantité</th>
                                <th class="text-right px-6 py-3 text-xs font-semibold text-cyan-100/70 uppercase">Prix unitaire</th>
                                <th class="text-right px-6 py-3 text-xs font-semibold text-cyan-100/70 uppercase">Montant</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            <tr>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-white font-medium">Eau potable</div>
                                    <div class="text-xs text-cyan-100/50">Tranche 1 (0-10 m³)</div>
                                </td>
                                <td class="px-6 py-4 text-center text-sm text-white">10 m³</td>
                                <td class="px-6 py-4 text-right text-sm text-white">0.500 TND</td>
                                <td class="px-6 py-4 text-right text-sm text-white font-medium">5.00 TND</td>
                            </tr>
                            <tr>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-white font-medium">Eau potable</div>
                                    <div class="text-xs text-cyan-100/50">Tranche 2 (10-20 m³)</div>
                                </td>
                                <td class="px-6 py-4 text-center text-sm text-white"><?php echo e($invoice['consumption'] - 10); ?> m³</td>
                                <td class="px-6 py-4 text-right text-sm text-white">0.750 TND</td>
                                <td class="px-6 py-4 text-right text-sm text-white font-medium"><?php echo e(number_format(($invoice['consumption'] - 10) * 0.75, 2)); ?> TND</td>
                            </tr>
                            <tr>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-white font-medium">Assainissement</div>
                                    <div class="text-xs text-cyan-100/50">Forfait mensuel</div>
                                </td>
                                <td class="px-6 py-4 text-center text-sm text-white">1</td>
                                <td class="px-6 py-4 text-right text-sm text-white">12.00 TND</td>
                                <td class="px-6 py-4 text-right text-sm text-white font-medium">12.00 TND</td>
                            </tr>
                            <tr>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-white font-medium">Redevance fixe</div>
                                    <div class="text-xs text-cyan-100/50">Abonnement</div>
                                </td>
                                <td class="px-6 py-4 text-center text-sm text-white">1</td>
                                <td class="px-6 py-4 text-right text-sm text-white">8.00 TND</td>
                                <td class="px-6 py-4 text-right text-sm text-white font-medium">8.00 TND</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Totals -->
            <div class="glass p-6 rounded-xl">
                <div class="space-y-3">
                    <div class="flex justify-between text-sm">
                        <span class="text-cyan-100/60">Sous-total HT</span>
                        <span class="text-white font-medium"><?php echo e(number_format($invoice['amount'] / 1.19, 2)); ?> TND</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-cyan-100/60">TVA (19%)</span>
                        <span class="text-white font-medium"><?php echo e(number_format($invoice['amount'] - ($invoice['amount'] / 1.19), 2)); ?> TND</span>
                    </div>
                    <div class="h-px bg-white/10"></div>
                    <div class="flex justify-between">
                        <span class="text-lg font-display font-bold text-white">Total TTC</span>
                        <span class="text-2xl font-display font-bold text-cyan-300"><?php echo e(number_format($invoice['amount'], 2)); ?> TND</span>
                    </div>
                </div>
            </div>

            <!-- Payment Button -->
            <?php if($invoice['status'] === 'pending'): ?>
            <div class="mt-8 p-6 rounded-xl bg-gradient-to-r from-cyan-500/10 to-blue-600/10 border border-cyan-400/20">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="font-semibold text-white mb-1">Paiement en attente</h4>
                        <p class="text-sm text-cyan-100/60">Date limite: <?php echo e(\Carbon\Carbon::parse($invoice['due_date'])->format('d/m/Y')); ?></p>
                    </div>
                    <?php if (isset($component)) { $__componentOriginala8bb031a483a05f647cb99ed3a469847 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala8bb031a483a05f647cb99ed3a469847 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.button','data' => ['icon' => 'credit-card','size' => 'lg']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'credit-card','size' => 'lg']); ?>
                        Payer maintenant
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
            </div>
            <?php else: ?>
            <div class="mt-8 p-6 rounded-xl bg-emerald-500/10 border border-emerald-400/20">
                <div class="flex items-center gap-3">
                    <i data-lucide="check-circle" class="w-6 h-6 text-emerald-400"></i>
                    <div>
                        <h4 class="font-semibold text-white">Facture payée</h4>
                        <p class="text-sm text-emerald-400">Paiement reçu le <?php echo e(\Carbon\Carbon::parse($invoice['paid_date'])->format('d/m/Y')); ?></p>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Footer Note -->
            <div class="mt-8 pt-6 border-t border-white/5">
                <p class="text-xs text-cyan-100/40 text-center">
                    Merci de votre confiance. Pour toute question, contactez-nous au +216 71 234 567 ou par email à contact@aquasecure.tn
                </p>
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
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.frontoffice', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\monta\Downloads\DEVWAVE\Esprit-5TWIN4-DevWave-main (1)\Esprit-5TWIN4-DevWave-main\resources\views\citizen\invoices\show.blade.php ENDPATH**/ ?>