@extends('layouts.manager')

@section('title', 'Détail intervention')

@php
    use App\Data\PlaceholderData;
    $interventions = PlaceholderData::technicianInterventions();
    $intervention = $interventions[0]; // For demo
@endphp

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="flex items-center justify-between mb-8">
        <div class="flex items-center gap-4">
            <a href="{{ route('technician.interventions.index') }}" class="w-10 h-10 rounded-xl glass flex items-center justify-center hover:border-cyan-400/40 transition-colors">
                <i data-lucide="arrow-left" class="w-5 h-5 text-cyan-300"></i>
            </a>
            <div>
                <div class="flex items-center gap-3 mb-1">
                    <h1 class="text-2xl sm:text-3xl font-display font-bold text-white">{{ $intervention['id'] }}</h1>
                    <x-ui.status-badge 
                        :status="$intervention['status'] === 'in_progress' ? 'info' : 'warning'" 
                        dot
                    >
                        {{ $intervention['status'] === 'in_progress' ? 'En cours' : 'Programmée' }}
                    </x-ui.status-badge>
                </div>
                <p class="text-cyan-100/60 text-sm">{{ $intervention['type'] }}</p>
            </div>
        </div>
        
        @if($intervention['status'] !== 'completed')
        <div class="flex items-center gap-2">
            @if($intervention['status'] === 'scheduled')
            <x-ui.button icon="play">
                Démarrer l'intervention
            </x-ui.button>
            @else
            <x-ui.button variant="outline" icon="pause">
                Pause
            </x-ui.button>
            <a href="{{ route('technician.interventions.report', ['id' => $intervention['id']]) }}">
                <x-ui.button icon="check">
                    Terminer
                </x-ui.button>
            </a>
            @endif
        </div>
        @endif
    </div>

    <div class="grid lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Priority Alert -->
            @if($intervention['priority'] === 'high')
            <x-ui.alert type="error" title="Intervention urgente">
                Cette intervention nécessite une action immédiate. Priorité maximale.
            </x-ui.alert>
            @endif

            <!-- Timer (if in progress) -->
            @if($intervention['status'] === 'in_progress')
            <x-ui.card>
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-display font-bold text-white mb-1">Durée de l'intervention</h3>
                        <p class="text-sm text-cyan-100/60">Démarrée à {{ \Carbon\Carbon::parse($intervention['started_at'])->format('H:i') }}</p>
                    </div>
                    <div class="text-right">
                        <div class="text-4xl font-display font-bold text-cyan-300" id="timer">00:00:00</div>
                        <p class="text-xs text-cyan-100/50 mt-1">Estimation: {{ $intervention['estimated_duration'] }}</p>
                    </div>
                </div>
            </x-ui.card>
            @endif

            <!-- Description -->
            <x-ui.card>
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-xl bg-cyan-500/10 flex items-center justify-center">
                        <i data-lucide="file-text" class="w-5 h-5 text-cyan-400"></i>
                    </div>
                    <h3 class="text-lg font-display font-bold text-white">Description du problème</h3>
                </div>
                <p class="text-cyan-100/70 leading-relaxed">{{ $intervention['description'] }}</p>
            </x-ui.card>

            <!-- Location -->
            <x-ui.card>
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-xl bg-cyan-500/10 flex items-center justify-center">
                        <i data-lucide="map-pin" class="w-5 h-5 text-cyan-400"></i>
                    </div>
                    <h3 class="text-lg font-display font-bold text-white">Localisation</h3>
                </div>
                
                <div class="space-y-3 mb-4">
                    <div class="flex items-start gap-3">
                        <i data-lucide="map" class="w-5 h-5 text-cyan-400 flex-shrink-0 mt-0.5"></i>
                        <div>
                            <div class="text-sm text-cyan-100/50 mb-1">Zone</div>
                            <div class="text-white font-medium">{{ $intervention['zone'] }}</div>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <i data-lucide="navigation" class="w-5 h-5 text-cyan-400 flex-shrink-0 mt-0.5"></i>
                        <div>
                            <div class="text-sm text-cyan-100/50 mb-1">Adresse</div>
                            <div class="text-white font-medium">{{ $intervention['address'] }}</div>
                        </div>
                    </div>
                </div>

                <x-ui.button variant="outline" size="sm" icon="navigation" class="w-full">
                    Ouvrir dans Google Maps
                </x-ui.button>
            </x-ui.card>

            <!-- Equipment Needed -->
            <x-ui.card>
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-xl bg-cyan-500/10 flex items-center justify-center">
                        <i data-lucide="wrench" class="w-5 h-5 text-cyan-400"></i>
                    </div>
                    <h3 class="text-lg font-display font-bold text-white">Équipement nécessaire</h3>
                </div>
                
                <div class="space-y-2">
                    @foreach($intervention['equipment_needed'] as $equipment)
                    <div class="flex items-center gap-3 p-3 glass rounded-lg">
                        <div class="w-8 h-8 rounded-lg bg-teal-500/10 flex items-center justify-center flex-shrink-0">
                            <i data-lucide="package" class="w-4 h-4 text-teal-400"></i>
                        </div>
                        <span class="text-white text-sm">{{ $equipment }}</span>
                    </div>
                    @endforeach
                </div>
            </x-ui.card>

            <!-- Photos (if any) -->
            <x-ui.card>
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-cyan-500/10 flex items-center justify-center">
                            <i data-lucide="camera" class="w-5 h-5 text-cyan-400"></i>
                        </div>
                        <h3 class="text-lg font-display font-bold text-white">Photos</h3>
                    </div>
                    <x-ui.button variant="outline" size="sm" icon="plus">
                        Ajouter
                    </x-ui.button>
                </div>
                
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                    <div class="aspect-square glass rounded-xl flex items-center justify-center">
                        <i data-lucide="image" class="w-8 h-8 text-cyan-400/40"></i>
                    </div>
                </div>
            </x-ui.card>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Quick Info -->
            <x-ui.card>
                <h3 class="text-lg font-display font-bold text-white mb-4">Informations</h3>
                
                <div class="space-y-4">
                    <div>
                        <span class="text-xs text-cyan-100/50 block mb-1">Programmée</span>
                        <span class="text-sm text-white">{{ \Carbon\Carbon::parse($intervention['scheduled_time'])->format('d/m/Y à H:i') }}</span>
                    </div>

                    @if(isset($intervention['started_at']))
                    <div>
                        <span class="text-xs text-cyan-100/50 block mb-1">Démarrée</span>
                        <span class="text-sm text-white">{{ \Carbon\Carbon::parse($intervention['started_at'])->format('d/m/Y à H:i') }}</span>
                    </div>
                    @endif

                    <div>
                        <span class="text-xs text-cyan-100/50 block mb-1">Durée estimée</span>
                        <span class="text-sm text-white">{{ $intervention['estimated_duration'] }}</span>
                    </div>

                    <div>
                        <span class="text-xs text-cyan-100/50 block mb-1">Équipe</span>
                        <span class="text-sm text-white">{{ $intervention['team'] }}</span>
                    </div>

                    <div>
                        <span class="text-xs text-cyan-100/50 block mb-1">Incident lié</span>
                        <a href="#" class="text-sm text-cyan-400 hover:text-cyan-300">{{ $intervention['incident_id'] }}</a>
                    </div>
                </div>
            </x-ui.card>

            <!-- Contact -->
            <x-ui.card>
                <h3 class="text-lg font-display font-bold text-white mb-4">Contact d'urgence</h3>
                
                <div class="space-y-3">
                    <a href="tel:{{ $intervention['contact'] }}">
                        <x-ui.button variant="outline" size="sm" icon="phone" class="w-full">
                            Appeler le centre
                        </x-ui.button>
                    </a>
                    <x-ui.button variant="outline" size="sm" icon="message-square" class="w-full">
                        Envoyer un message
                    </x-ui.button>
                </div>

                <div class="mt-4 p-3 rounded-lg bg-cyan-500/5 border border-cyan-400/10">
                    <div class="flex items-center gap-2 text-sm text-cyan-100/70">
                        <i data-lucide="phone" class="w-4 h-4"></i>
                        <span>{{ $intervention['contact'] }}</span>
                    </div>
                </div>
            </x-ui.card>

            <!-- Actions -->
            <x-ui.card>
                <h3 class="text-lg font-display font-bold text-white mb-4">Actions rapides</h3>
                
                <div class="space-y-2">
                    <x-ui.button variant="outline" size="sm" icon="share-2" class="w-full">
                        Partager
                    </x-ui.button>
                    <x-ui.button variant="outline" size="sm" icon="download" class="w-full">
                        Télécharger
                    </x-ui.button>
                    <x-ui.button variant="outline" size="sm" icon="printer" class="w-full">
                        Imprimer
                    </x-ui.button>
                </div>
            </x-ui.card>

            <!-- Safety Note -->
            <x-ui.alert type="warning" title="Sécurité">
                <p class="text-xs">Respectez toujours les consignes de sécurité. En cas de danger, contactez immédiatement le centre de contrôle.</p>
            </x-ui.alert>
        </div>
    </div>
</div>

@if($intervention['status'] === 'in_progress')
@push('scripts')
<script>
// Simple timer
let seconds = 0;
const timerEl = document.getElementById('timer');

if (timerEl) {
    setInterval(() => {
        seconds++;
        const hours = Math.floor(seconds / 3600);
        const minutes = Math.floor((seconds % 3600) / 60);
        const secs = seconds % 60;
        
        timerEl.textContent = 
            String(hours).padStart(2, '0') + ':' +
            String(minutes).padStart(2, '0') + ':' +
            String(secs).padStart(2, '0');
    }, 1000);
}
</script>
@endpush
@endif
@endsection
