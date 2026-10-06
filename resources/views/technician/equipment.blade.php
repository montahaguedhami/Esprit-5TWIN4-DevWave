@extends('layouts.manager')

@section('title', 'Mon équipement')

@php
    use App\Data\PlaceholderData;
    $equipment = PlaceholderData::technicianEquipment();
    $statusConfig = [
        'available' => ['label' => 'Disponible', 'color' => 'emerald', 'icon' => 'check-circle'],
        'in_use' => ['label' => 'En utilisation', 'color' => 'cyan', 'icon' => 'loader'],
        'low_stock' => ['label' => 'Stock faible', 'color' => 'amber', 'icon' => 'alert-triangle'],
        'maintenance' => ['label' => 'Maintenance', 'color' => 'rose', 'icon' => 'wrench'],
    ];
@endphp

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-display font-bold text-white mb-2">Mon équipement</h1>
            <p class="text-cyan-100/60 text-sm">Gérez votre matériel et équipement terrain</p>
        </div>
        
        <div class="flex items-center gap-2">
            <x-ui.button variant="outline" size="sm" icon="scan">
                Scanner QR
            </x-ui.button>
            <x-ui.button size="sm" icon="plus">
                Demander équipement
            </x-ui.button>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-8">
        @foreach($statusConfig as $status => $config)
        @php
            $count = count(array_filter($equipment, fn($e) => $e['status'] === $status));
        @endphp
        <x-ui.card hover>
            <div class="flex items-center gap-3 mb-2">
                <div class="w-10 h-10 rounded-xl bg-{{ $config['color'] }}-500/10 flex items-center justify-center">
                    <i data-lucide="{{ $config['icon'] }}" class="w-5 h-5 text-{{ $config['color'] }}-400"></i>
                </div>
            </div>
            <div class="text-2xl font-display font-bold text-white">{{ $count }}</div>
            <div class="text-xs text-cyan-100/60">{{ $config['label'] }}</div>
        </x-ui.card>
        @endforeach
    </div>

    <!-- Filters -->
    <div class="flex items-center gap-2 mb-6 overflow-x-auto pb-2">
        <button class="category-filter px-4 py-2 rounded-lg text-sm font-semibold whitespace-nowrap transition-colors bg-cyan-500/10 text-white border border-cyan-400/20" data-category="all">
            Tout
        </button>
        @foreach(['Outillage', 'Électronique', 'Consommables', 'Équipement lourd'] as $category)
        <button class="category-filter px-4 py-2 rounded-lg text-sm font-semibold whitespace-nowrap transition-colors text-cyan-100/60 hover:bg-white/5" data-category="{{ $category }}">
            {{ $category }}
        </button>
        @endforeach
    </div>

    <!-- Equipment Grid -->
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($equipment as $item)
        <x-ui.card hover class="equipment-item" data-category="{{ $item['category'] }}">
            <div class="flex items-start justify-between mb-4">
                <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-teal-500/20 to-cyan-600/20 border border-teal-400/25 flex items-center justify-center">
                    @php
                        $categoryIcons = [
                            'Outillage' => 'wrench',
                            'Électronique' => 'radio',
                            'Consommables' => 'package',
                            'Équipement lourd' => 'truck',
                        ];
                    @endphp
                    <i data-lucide="{{ $categoryIcons[$item['category']] ?? 'box' }}" class="w-7 h-7 text-teal-400"></i>
                </div>
                
                <x-ui.status-badge 
                    :status="$item['status'] === 'available' ? 'success' : ($item['status'] === 'in_use' ? 'info' : ($item['status'] === 'low_stock' ? 'warning' : 'danger'))"
                    size="sm"
                >
                    {{ $statusConfig[$item['status']]['label'] }}
                </x-ui.status-badge>
            </div>

            <div class="mb-4">
                <h3 class="text-lg font-display font-bold text-white mb-1">{{ $item['name'] }}</h3>
                <p class="text-xs text-cyan-100/50">{{ $item['id'] }} • {{ $item['category'] }}</p>
            </div>

            <div class="space-y-2 mb-4 text-sm">
                <div class="flex items-center justify-between">
                    <span class="text-cyan-100/60">Quantité</span>
                    <span class="text-white font-semibold">{{ $item['quantity'] }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-cyan-100/60">Localisation</span>
                    <span class="text-white font-medium text-xs">{{ $item['location'] }}</span>
                </div>
                @if($item['next_maintenance'])
                <div class="flex items-center justify-between">
                    <span class="text-cyan-100/60">Prochaine maintenance</span>
                    <span class="text-white font-medium text-xs">{{ \Carbon\Carbon::parse($item['next_maintenance'])->format('d/m/Y') }}</span>
                </div>
                @endif
            </div>

            <div class="flex gap-2">
                <x-ui.button variant="outline" size="sm" icon="info" class="flex-1">
                    Détails
                </x-ui.button>
                @if($item['status'] === 'available')
                <x-ui.button size="sm" icon="check" class="flex-1">
                    Prendre
                </x-ui.button>
                @elseif($item['status'] === 'in_use')
                <x-ui.button variant="outline" size="sm" icon="x" class="flex-1">
                    Retourner
                </x-ui.button>
                @endif
            </div>
        </x-ui.card>
        @endforeach
    </div>

    <!-- Help Section -->
    <x-ui.alert type="info" class="mt-8">
        <div class="flex items-start gap-3">
            <i data-lucide="help-circle" class="w-5 h-5 flex-shrink-0 mt-0.5"></i>
            <div>
                <p class="text-sm font-semibold mb-1">Besoin d'un équipement ?</p>
                <p class="text-xs">Contactez le dépôt central au <a href="tel:71234567" class="text-cyan-300 font-semibold">71 234 567</a> ou faites une demande en ligne.</p>
            </div>
        </div>
    </x-ui.alert>
</div>

@push('scripts')
<script>
// Filter equipment by category
document.querySelectorAll('.category-filter').forEach(btn => {
    btn.addEventListener('click', function() {
        const category = this.dataset.category;
        
        // Update active state
        document.querySelectorAll('.category-filter').forEach(b => {
            b.classList.remove('bg-cyan-500/10', 'text-white', 'border-cyan-400/20');
            b.classList.add('text-cyan-100/60');
        });
        this.classList.add('bg-cyan-500/10', 'text-white', 'border-cyan-400/20');
        this.classList.remove('text-cyan-100/60');
        
        // Filter equipment
        document.querySelectorAll('.equipment-item').forEach(item => {
            const itemCategory = item.dataset.category;
            
            if (category === 'all') {
                item.classList.remove('hidden');
            } else {
                item.classList.toggle('hidden', itemCategory !== category);
            }
        });
    });
});
</script>
@endpush
@endsection
