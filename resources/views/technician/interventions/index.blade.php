@extends('layouts.manager')

@section('title', 'Mes interventions')

@php
    use App\Data\PlaceholderData;
    $interventions = PlaceholderData::technicianInterventions();
    $scheduled = array_filter($interventions, fn($i) => $i['status'] === 'scheduled');
    $inProgress = array_filter($interventions, fn($i) => $i['status'] === 'in_progress');
    $completed = array_filter($interventions, fn($i) => $i['status'] === 'completed');
@endphp

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-display font-bold text-white mb-2">Mes interventions</h1>
            <p class="text-cyan-100/60 text-sm">Gérez vos interventions terrain</p>
        </div>
        
        <div class="flex items-center gap-2">
            <x-ui.button variant="outline" size="sm" icon="filter">
                Filtrer
            </x-ui.button>
            <x-ui.button size="sm" icon="map">
                Vue carte
            </x-ui.button>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
        <x-ui.card hover>
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm text-cyan-100/60 mb-1">Programmées</div>
                    <div class="text-3xl font-display font-bold text-white">{{ count($scheduled) }}</div>
                </div>
                <div class="w-12 h-12 rounded-xl bg-amber-500/10 flex items-center justify-center">
                    <i data-lucide="calendar" class="w-6 h-6 text-amber-400"></i>
                </div>
            </div>
        </x-ui.card>
        
        <x-ui.card hover>
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm text-cyan-100/60 mb-1">En cours</div>
                    <div class="text-3xl font-display font-bold text-white">{{ count($inProgress) }}</div>
                </div>
                <div class="w-12 h-12 rounded-xl bg-cyan-500/10 flex items-center justify-center">
                    <i data-lucide="loader" class="w-6 h-6 text-cyan-400"></i>
                </div>
            </div>
        </x-ui.card>
        
        <x-ui.card hover>
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm text-cyan-100/60 mb-1">Complétées</div>
                    <div class="text-3xl font-display font-bold text-white">{{ count($completed) }}</div>
                </div>
                <div class="w-12 h-12 rounded-xl bg-emerald-500/10 flex items-center justify-center">
                    <i data-lucide="check-circle" class="w-6 h-6 text-emerald-400"></i>
                </div>
            </div>
        </x-ui.card>
    </div>

    <!-- Filters -->
    <div class="flex items-center gap-2 mb-6 overflow-x-auto pb-2">
        <button class="status-filter px-4 py-2 rounded-lg text-sm font-semibold whitespace-nowrap transition-colors bg-cyan-500/10 text-white border border-cyan-400/20" data-status="all">
            Toutes ({{ count($interventions) }})
        </button>
        <button class="status-filter px-4 py-2 rounded-lg text-sm font-semibold whitespace-nowrap transition-colors text-cyan-100/60 hover:bg-white/5" data-status="scheduled">
            Programmées ({{ count($scheduled) }})
        </button>
        <button class="status-filter px-4 py-2 rounded-lg text-sm font-semibold whitespace-nowrap transition-colors text-cyan-100/60 hover:bg-white/5" data-status="in_progress">
            En cours ({{ count($inProgress) }})
        </button>
        <button class="status-filter px-4 py-2 rounded-lg text-sm font-semibold whitespace-nowrap transition-colors text-cyan-100/60 hover:bg-white/5" data-status="completed">
            Complétées ({{ count($completed) }})
        </button>
    </div>

    <!-- Interventions List -->
    <div class="space-y-4">
        @forelse($interventions as $intervention)
        <x-ui.card hover class="intervention-item" data-status="{{ $intervention['status'] }}">
            <div class="flex flex-col lg:flex-row lg:items-center gap-6">
                <!-- Left: Status Indicator -->
                <div class="flex items-center gap-4 lg:gap-6">
                    <div class="flex flex-col items-center">
                        @if($intervention['status'] === 'in_progress')
                        <div class="w-16 h-16 rounded-full bg-gradient-to-br from-cyan-500 to-blue-600 flex items-center justify-center animate-pulse">
                            <i data-lucide="loader" class="w-8 h-8 text-white"></i>
                        </div>
                        @elseif($intervention['status'] === 'scheduled')
                        <div class="w-16 h-16 rounded-full bg-amber-500/10 border-2 border-amber-400/30 flex items-center justify-center">
                            <i data-lucide="calendar" class="w-8 h-8 text-amber-400"></i>
                        </div>
                        @else
                        <div class="w-16 h-16 rounded-full bg-emerald-500/10 border-2 border-emerald-400/30 flex items-center justify-center">
                            <i data-lucide="check-circle" class="w-8 h-8 text-emerald-400"></i>
                        </div>
                        @endif
                        <x-ui.status-badge 
                            :status="$intervention['status'] === 'in_progress' ? 'info' : ($intervention['status'] === 'scheduled' ? 'warning' : 'success')" 
                            size="sm"
                            class="mt-2"
                        >
                            {{ $intervention['status'] === 'in_progress' ? 'En cours' : ($intervention['status'] === 'scheduled' ? 'Programmée' : 'Terminée') }}
                        </x-ui.status-badge>
                    </div>

                    <!-- Priority Badge -->
                    <div class="hidden sm:flex flex-col items-center gap-2">
                        @if($intervention['priority'] === 'high')
                        <div class="w-3 h-12 rounded-full bg-gradient-to-b from-rose-500 to-rose-600"></div>
                        <span class="text-xs text-rose-400 font-semibold">URGENT</span>
                        @elseif($intervention['priority'] === 'medium')
                        <div class="w-3 h-12 rounded-full bg-gradient-to-b from-amber-500 to-amber-600"></div>
                        <span class="text-xs text-amber-400 font-semibold">MOYEN</span>
                        @else
                        <div class="w-3 h-12 rounded-full bg-gradient-to-b from-emerald-500 to-emerald-600"></div>
                        <span class="text-xs text-emerald-400 font-semibold">FAIBLE</span>
                        @endif
                    </div>
                </div>

                <!-- Center: Info -->
                <div class="flex-1 min-w-0">
                    <div class="flex items-start justify-between gap-3 mb-3">
                        <div>
                            <h3 class="text-xl font-display font-bold text-white mb-1">{{ $intervention['id'] }}</h3>
                            <p class="text-cyan-100/60 text-sm">{{ $intervention['type'] }}</p>
                        </div>
                    </div>

                    <div class="grid sm:grid-cols-2 gap-3 text-sm">
                        <div class="flex items-center gap-2">
                            <i data-lucide="map-pin" class="w-4 h-4 text-cyan-400 flex-shrink-0"></i>
                            <span class="text-white truncate">{{ $intervention['address'] }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <i data-lucide="clock" class="w-4 h-4 text-cyan-400 flex-shrink-0"></i>
                            <span class="text-white">{{ \Carbon\Carbon::parse($intervention['scheduled_time'])->format('d/m/Y H:i') }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <i data-lucide="map" class="w-4 h-4 text-cyan-400 flex-shrink-0"></i>
                            <span class="text-white">{{ $intervention['zone'] }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <i data-lucide="timer" class="w-4 h-4 text-cyan-400 flex-shrink-0"></i>
                            <span class="text-white">Durée estimée: {{ $intervention['estimated_duration'] }}</span>
                        </div>
                    </div>

                    @if(isset($intervention['started_at']) && $intervention['status'] === 'in_progress')
                    <div class="mt-3 flex items-center gap-2 text-sm">
                        <div class="flex-1 h-2 bg-slate-800 rounded-full overflow-hidden">
                            <div class="h-full bg-gradient-to-r from-cyan-500 to-blue-600 rounded-full animate-pulse" style="width: 60%"></div>
                        </div>
                        <span class="text-cyan-300 font-semibold">En cours...</span>
                    </div>
                    @endif
                </div>

                <!-- Right: Actions -->
                <div class="flex flex-col gap-2 sm:flex-row lg:flex-col lg:items-end">
                    <a href="{{ route('technician.interventions.show', ['id' => $intervention['id']]) }}">
                        <x-ui.button variant="outline" size="sm" icon="eye" class="w-full sm:w-auto">
                            Détails
                        </x-ui.button>
                    </a>
                    
                    @if($intervention['status'] === 'scheduled')
                    <x-ui.button size="sm" icon="play" class="w-full sm:w-auto">
                        Démarrer
                    </x-ui.button>
                    @elseif($intervention['status'] === 'in_progress')
                    <x-ui.button variant="outline" size="sm" icon="pause" class="w-full sm:w-auto">
                        Pause
                    </x-ui.button>
                    <a href="{{ route('technician.interventions.report', ['id' => $intervention['id']]) }}">
                        <x-ui.button size="sm" icon="check" class="w-full sm:w-auto">
                            Terminer
                        </x-ui.button>
                    </a>
                    @else
                    <x-ui.button variant="outline" size="sm" icon="file-text" class="w-full sm:w-auto">
                        Voir rapport
                    </x-ui.button>
                    @endif
                </div>
            </div>
        </x-ui.card>
        @empty
        <x-ui.empty-state 
            icon="clipboard-list"
            title="Aucune intervention"
            description="Vous n'avez pas d'interventions assignées pour le moment"
        />
        @endforelse
    </div>
</div>

@push('scripts')
<script>
// Filter interventions by status
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
        
        // Filter interventions
        document.querySelectorAll('.intervention-item').forEach(item => {
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
