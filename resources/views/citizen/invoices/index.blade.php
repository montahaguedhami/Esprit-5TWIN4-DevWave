@extends('layouts.frontoffice')

@section('title', 'Mes factures')

@php
    use App\Data\PlaceholderData;
    $invoices = PlaceholderData::citizenInvoices();
    $pendingCount = count(array_filter($invoices, fn($i) => $i['status'] === 'pending'));
    $totalPending = array_sum(array_map(fn($i) => $i['status'] === 'pending' ? $i['amount'] : 0, $invoices));
@endphp

@section('content')
<div class="space-y-6">
    <!-- Header with Back Navigation -->
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('citizen.dashboard') }}" class="w-10 h-10 rounded-2xl glass flex items-center justify-center text-cyan-300 hover:text-white hover:bg-white/10 transition-all shadow-sm shrink-0" title="Retour au tableau de bord">
            <i data-lucide="arrow-left" class="w-5 h-5"></i>
        </a>
        <div>
            <h1 class="text-2xl sm:text-3xl font-display font-bold text-white tracking-tight">Mes factures & Consommation</h1>
            <p class="text-cyan-100/60 text-xs sm:text-sm mt-0.5">Consultez votre historique de consommation et réglez vos factures d'eau</p>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <x-dashboard.kpi-card
            icon="receipt"
            title="Factures en attente"
            :value="$pendingCount"
            color="amber"
            subtitle="{{ $totalPending }} TND"
        />
        
        <x-dashboard.kpi-card
            icon="check-circle"
            title="Factures payées"
            :value="count($invoices) - $pendingCount"
            color="emerald"
        />
        
        <x-dashboard.kpi-card
            icon="droplet"
            title="Consommation moy."
            value="19.6 m³"
            color="cyan"
            subtitle="Par mois"
        />
        
        <x-dashboard.kpi-card
            icon="trending-down"
            title="Économie"
            value="-5%"
            color="teal"
            subtitle="vs mois dernier"
            trend="down"
            trend-value="-5%"
        />
    </div>

    <!-- Actions & Filters -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
        <div class="flex items-center gap-2 flex-wrap">
            <button class="status-filter px-5 py-2.5 rounded-lg font-semibold transition-colors bg-cyan-500/10 text-white border border-cyan-400/20" data-status="all" style="font-size:var(--font-size-sm);">
                Toutes
            </button>
            <button class="status-filter px-5 py-2.5 rounded-lg font-semibold transition-colors text-cyan-100/60 hover:bg-white/5" data-status="pending" style="font-size:var(--font-size-sm);">
                En attente
            </button>
            <button class="status-filter px-5 py-2.5 rounded-lg font-semibold transition-colors text-cyan-100/60 hover:bg-white/5" data-status="paid" style="font-size:var(--font-size-sm);">
                Payées
            </button>
        </div>
        
        <div class="flex items-center gap-2">
            <x-ui.button variant="outline" size="sm" icon="download">
                Télécharger tout
            </x-ui.button>
            <x-ui.button size="sm" icon="credit-card">
                Payer maintenant
            </x-ui.button>
        </div>
    </div>

    <!-- Invoices List -->
    <div class="space-y-4">
        @forelse($invoices as $invoice)
        <x-ui.card hover class="invoice-item" data-status="{{ $invoice['status'] }}">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                <!-- Invoice Info -->
                <div class="flex items-start gap-4 flex-1">
                    <!-- Icon -->
                    <div class="w-14 h-14 rounded-xl bg-gradient-to-br {{ $invoice['status'] === 'pending' ? 'from-amber-500/20 to-orange-600/20 border border-amber-400/25' : 'from-emerald-500/20 to-teal-600/20 border border-emerald-400/25' }} flex items-center justify-center flex-shrink-0">
                        <i data-lucide="receipt" class="w-7 h-7 {{ $invoice['status'] === 'pending' ? 'text-amber-400' : 'text-emerald-400' }}"></i>
                    </div>
                    
                    <!-- Details -->
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-2">
                            <h3 class="text-lg font-display font-bold text-white">{{ $invoice['id'] }}</h3>
                            <x-ui.status-badge :status="$invoice['status'] === 'pending' ? 'warning' : 'success'">
                                {{ $invoice['status'] === 'pending' ? 'À payer' : 'Payée' }}
                            </x-ui.status-badge>
                        </div>
                        
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-x-6 gap-y-2 text-sm">
                            <div>
                                <span class="text-cyan-100/50 block">Période</span>
                                <span class="text-white font-medium">{{ $invoice['month'] }}</span>
                            </div>
                            <div>
                                <span class="text-cyan-100/50 block">Consommation</span>
                                <span class="text-white font-medium">{{ $invoice['consumption'] }} m³</span>
                            </div>
                            <div>
                                <span class="text-cyan-100/50 block">Date d'émission</span>
                                <span class="text-white font-medium">{{ \Carbon\Carbon::parse($invoice['issue_date'])->format('d/m/Y') }}</span>
                            </div>
                            <div>
                                <span class="text-cyan-100/50 block">
                                    {{ $invoice['status'] === 'pending' ? 'Date limite' : 'Payée le' }}
                                </span>
                                <span class="text-white font-medium">
                                    @if($invoice['status'] === 'pending')
                                        {{ \Carbon\Carbon::parse($invoice['due_date'])->format('d/m/Y') }}
                                    @else
                                        {{ \Carbon\Carbon::parse($invoice['paid_date'])->format('d/m/Y') }}
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Amount & Actions -->
                <div class="flex flex-row lg:flex-col items-center lg:items-end justify-between lg:justify-start gap-4">
                    <div class="text-right">
                        <div class="text-sm text-cyan-100/50 mb-1">Montant</div>
                        <div class="text-2xl font-display font-bold text-white">{{ number_format($invoice['amount'], 2) }} <span class="text-lg text-cyan-100/50">TND</span></div>
                    </div>
                    
                    <div class="flex items-center gap-2">
                        <a href="{{ route('citizen.invoices.show', ['id' => $invoice['id']]) }}">
                            <x-ui.button variant="outline" size="sm" icon="eye">
                                Voir
                            </x-ui.button>
                        </a>
                        @if($invoice['status'] === 'pending')
                        <x-ui.button size="sm" icon="credit-card">
                            Payer
                        </x-ui.button>
                        @else
                        <x-ui.button variant="outline" size="sm" icon="download">
                            PDF
                        </x-ui.button>
                        @endif
                    </div>
                </div>
            </div>
        </x-ui.card>
        @empty
        <x-ui.empty-state 
            icon="receipt"
            title="Aucune facture"
            description="Vous n'avez pas encore de factures"
        />
        @endforelse
    </div>

    <!-- Help Section -->
    <x-ui.card class="mt-8">
        <div class="flex items-start gap-4">
            <div class="w-12 h-12 rounded-xl bg-cyan-500/10 flex items-center justify-center flex-shrink-0">
                <i data-lucide="help-circle" class="w-6 h-6 text-cyan-400"></i>
            </div>
            <div class="flex-1">
                <h3 class="text-lg font-display font-bold text-white mb-2">Besoin d'aide ?</h3>
                <p class="text-sm text-cyan-100/70 mb-4">
                    Vous avez des questions sur vos factures ou sur votre consommation ?
                </p>
                <div class="flex flex-wrap gap-3">
                    <x-ui.button variant="outline" size="sm" icon="phone">
                        Contacter le support
                    </x-ui.button>
                    <x-ui.button variant="outline" size="sm" icon="file-text">
                        Guide de facturation
                    </x-ui.button>
                    <x-ui.button variant="outline" size="sm" icon="calculator">
                        Simuler ma facture
                    </x-ui.button>
                </div>
            </div>
        </div>
    </x-ui.card>
</div>

@push('scripts')
<script>
// Filter invoices by status
document.querySelectorAll('.status-filter').forEach(btn => {
    btn.addEventListener('click', function() {
        const status = this.dataset.status;
        
        // Update active state
        document.querySelectorAll('.status-filter').forEach(b => {
            b.classList.remove('bg-cyan-500/10', 'text-white', 'border-cyan-400/20');
            b.classList.add('text-cyan-100/60');
        });
        this.classList.add('bg-cyan-500/10', 'text-white', 'border-cyan-400/20');
        this.classList.remove('text-cyan-100/60');
        
        // Filter invoices
        document.querySelectorAll('.invoice-item').forEach(item => {
            const itemStatus = item.dataset.status;
            
            if (status === 'all') {
                item.classList.remove('hidden');
            } else {
                item.classList.toggle('hidden', itemStatus !== status);
            }
        });
    });
});
</script>
@endpush
@endsection
