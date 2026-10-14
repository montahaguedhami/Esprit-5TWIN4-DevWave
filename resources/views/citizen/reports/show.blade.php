@extends('layouts.frontoffice')

@section('title', 'Détail de la déclaration')

@php
    use App\Data\PlaceholderData;
    $reports = PlaceholderData::citizenReports();
    // For demo, use the first report
    $report = $reports[0];
    
    $statusConfig = [
        'pending' => ['label' => 'En attente', 'color' => 'slate', 'icon' => 'clock'],
        'in_progress' => ['label' => 'En cours', 'color' => 'amber', 'icon' => 'loader'],
        'resolved' => ['label' => 'Résolu', 'color' => 'emerald', 'icon' => 'check-circle'],
        'closed' => ['label' => 'Fermé', 'color' => 'slate', 'icon' => 'x-circle'],
    ];
    
    $priorityConfig = [
        'low' => ['label' => 'Faible', 'color' => 'emerald'],
        'medium' => ['label' => 'Moyen', 'color' => 'amber'],
        'high' => ['label' => 'Urgent', 'color' => 'rose'],
    ];
@endphp

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
        <div class="flex items-center gap-4">
            <a href="{{ route('citizen.dashboard') }}" class="w-10 h-10 rounded-xl glass flex items-center justify-center hover:border-cyan-400/40 transition-colors">
                <i data-lucide="arrow-left" class="w-5 h-5 text-cyan-300"></i>
            </a>
            <div>
                <div class="flex items-center gap-3 mb-1">
                    <h1 class="text-2xl sm:text-3xl font-display font-bold text-white">{{ $report['id'] }}</h1>
                    <x-ui.status-badge 
                        :status="$report['status'] === 'in_progress' ? 'warning' : ($report['status'] === 'resolved' ? 'success' : 'pending')" 
                        dot
                    >
                        {{ $statusConfig[$report['status']]['label'] }}
                    </x-ui.status-badge>
                </div>
                <p class="text-cyan-100/60 text-sm">{{ $report['type'] }} • {{ $report['zone'] }}</p>
            </div>
        </div>
        
        <div class="flex items-center gap-2">
            <x-ui.button variant="outline" size="sm" icon="download">
                Télécharger
            </x-ui.button>
            <x-ui.button variant="outline" size="sm" icon="share-2">
                Partager
            </x-ui.button>
        </div>
    </div>

    <div class="grid lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Timeline -->
            <x-ui.card>
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 rounded-xl bg-cyan-500/10 flex items-center justify-center">
                        <i data-lucide="clock" class="w-5 h-5 text-cyan-400"></i>
                    </div>
                    <h3 class="text-lg font-display font-bold text-white">Suivi de l'intervention</h3>
                </div>

                <div class="relative">
                    @php
                        $timeline = [
                            ['status' => 'completed', 'title' => 'Déclaration créée', 'desc' => 'Votre signalement a été enregistré', 'time' => $report['created_at'], 'icon' => 'file-plus'],
                            ['status' => 'completed', 'title' => 'Déclaration reçue', 'desc' => 'Prise en compte par le service', 'time' => $report['created_at'], 'icon' => 'check'],
                            ['status' => 'current', 'title' => 'Technicien assigné', 'desc' => $report['assigned_to'], 'time' => $report['updated_at'], 'icon' => 'user-check'],
                            ['status' => 'pending', 'title' => 'Intervention en cours', 'desc' => 'Résolution du problème sur site', 'time' => null, 'icon' => 'wrench'],
                            ['status' => 'pending', 'title' => 'Problème résolu', 'desc' => 'Intervention terminée avec succès', 'time' => null, 'icon' => 'check-circle'],
                        ];
                    @endphp

                    <div class="space-y-6">
                        @foreach($timeline as $index => $event)
                        <div class="flex gap-4 relative">
                            <!-- Line -->
                            @if($index < count($timeline) - 1)
                            <div class="absolute left-5 top-12 bottom-0 w-0.5 {{ $event['status'] === 'completed' ? 'bg-cyan-400' : 'bg-slate-800' }}"></div>
                            @endif
                            
                            <!-- Icon -->
                            <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0 relative z-10 {{ 
                                $event['status'] === 'completed' ? 'bg-cyan-500 text-white' : 
                                ($event['status'] === 'current' ? 'bg-amber-500 text-white animate-pulse' : 'bg-slate-800 text-slate-400')
                            }}">
                                <i data-lucide="{{ $event['icon'] }}" class="w-5 h-5"></i>
                            </div>
                            
                            <!-- Content -->
                            <div class="flex-1">
                                <h4 class="font-semibold text-white mb-1">{{ $event['title'] }}</h4>
                                <p class="text-sm text-cyan-100/60 mb-1">{{ $event['desc'] }}</p>
                                @if($event['time'])
                                <p class="text-xs text-cyan-100/40">{{ $event['time'] }}</p>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </x-ui.card>

            <!-- Description -->
            <x-ui.card>
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-xl bg-cyan-500/10 flex items-center justify-center">
                        <i data-lucide="file-text" class="w-5 h-5 text-cyan-400"></i>
                    </div>
                    <h3 class="text-lg font-display font-bold text-white">Description</h3>
                </div>
                <p class="text-cyan-100/70 leading-relaxed">{{ $report['description'] }}</p>
            </x-ui.card>

            <!-- Location -->
            <x-ui.card>
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-xl bg-cyan-500/10 flex items-center justify-center">
                        <i data-lucide="map-pin" class="w-5 h-5 text-cyan-400"></i>
                    </div>
                    <h3 class="text-lg font-display font-bold text-white">Localisation</h3>
                </div>
                <p class="text-cyan-100/70 mb-4">{{ $report['address'] }}</p>
                
                <!-- Map Placeholder -->
                <div class="glass rounded-xl p-4 bg-gradient-to-br from-slate-900/80 to-blue-950/80 aspect-video flex items-center justify-center">
                    <div class="text-center">
                        <i data-lucide="map" class="w-12 h-12 text-cyan-400/40 mx-auto mb-3"></i>
                        <p class="text-sm text-cyan-100/50">Carte interactive disponible prochainement</p>
                    </div>
                </div>
            </x-ui.card>

            <!-- Comments / Updates -->
            <x-ui.card>
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 rounded-xl bg-cyan-500/10 flex items-center justify-center">
                        <i data-lucide="message-square" class="w-5 h-5 text-cyan-400"></i>
                    </div>
                    <h3 class="text-lg font-display font-bold text-white">Commentaires</h3>
                </div>

                @php
                    $comments = [
                        ['author' => 'Système AquaSecure', 'role' => 'system', 'message' => 'Votre déclaration a été reçue et est en cours de traitement.', 'time' => '2 jours'],
                        ['author' => 'Amira Ben Ali', 'role' => 'technician', 'message' => 'J\'ai été assignée à cette intervention. Je me déplace demain matin à 9h pour évaluer la situation.', 'time' => '1 jour'],
                    ];
                @endphp

                <div class="space-y-4 mb-6">
                    @foreach($comments as $comment)
                    <div class="flex gap-3">
                        <x-ui.avatar 
                            :name="$comment['author']"
                            size="md"
                        />
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="font-semibold text-white text-sm">{{ $comment['author'] }}</span>
                                <span class="text-xs text-cyan-100/40">{{ $comment['time'] }}</span>
                            </div>
                            <p class="text-sm text-cyan-100/70">{{ $comment['message'] }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>

                <!-- Add Comment -->
                <div class="flex gap-3">
                    <x-ui.avatar 
                        name="Yassine Hamdi"
                        size="md"
                    />
                    <div class="flex-1">
                        <textarea 
                            placeholder="Ajouter un commentaire..."
                            rows="3"
                            class="w-full bg-slate-950/35 border border-cyan-400/15 rounded-xl px-4 py-3 text-sm text-white placeholder:text-cyan-100/30 focus:outline-none focus:border-cyan-400/50 transition-colors resize-none"
                        ></textarea>
                        <div class="flex justify-end mt-2">
                            <x-ui.button size="sm" icon="send">
                                Publier
                            </x-ui.button>
                        </div>
                    </div>
                </div>
            </x-ui.card>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Info Card -->
            <x-ui.card>
                <h3 class="text-lg font-display font-bold text-white mb-4">Informations</h3>
                
                <div class="space-y-4">
                    <div>
                        <span class="text-xs text-cyan-100/50 block mb-1">Priorité</span>
                        <x-ui.status-badge :status="$report['priority'] === 'high' ? 'danger' : ($report['priority'] === 'medium' ? 'warning' : 'success')">
                            {{ $priorityConfig[$report['priority']]['label'] }}
                        </x-ui.status-badge>
                    </div>

                    <div>
                        <span class="text-xs text-cyan-100/50 block mb-1">Date de création</span>
                        <span class="text-sm text-white">{{ $report['created_at'] }}</span>
                    </div>

                    <div>
                        <span class="text-xs text-cyan-100/50 block mb-1">Dernière mise à jour</span>
                        <span class="text-sm text-white">{{ $report['updated_at'] }}</span>
                    </div>

                    @if(isset($report['estimated_resolution']))
                    <div>
                        <span class="text-xs text-cyan-100/50 block mb-1">Résolution estimée</span>
                        <span class="text-sm text-white">{{ $report['estimated_resolution'] }}</span>
                    </div>
                    @endif

                    <div>
                        <span class="text-xs text-cyan-100/50 block mb-1">Zone</span>
                        <span class="text-sm text-white">{{ $report['zone'] }}</span>
                    </div>

                    @if($report['assigned_to'])
                    <div>
                        <span class="text-xs text-cyan-100/50 block mb-1">Équipe assignée</span>
                        <div class="flex items-center gap-2 mt-2">
                            <x-ui.avatar name="{{ $report['assigned_to'] }}" size="sm" />
                            <span class="text-sm text-white">{{ $report['assigned_to'] }}</span>
                        </div>
                    </div>
                    @endif
                </div>
            </x-ui.card>

            <!-- Actions -->
            <x-ui.card>
                <h3 class="text-lg font-display font-bold text-white mb-4">Actions</h3>
                
                <div class="space-y-2">
                    <x-ui.button variant="outline" class="w-full" icon="bell">
                        Recevoir des notifications
                    </x-ui.button>
                    <x-ui.button variant="outline" class="w-full" icon="message-circle">
                        Contacter le support
                    </x-ui.button>
                    @if($report['status'] === 'resolved')
                    <x-ui.button variant="outline" class="w-full" icon="star">
                        Évaluer l'intervention
                    </x-ui.button>
                    @endif
                </div>
            </x-ui.card>

            <!-- Help -->
            <x-ui.alert type="info">
                <p class="text-sm">
                    Besoin d'aide ? Contactez notre support au 
                    <a href="tel:71234567" class="text-cyan-300 font-semibold">71 234 567</a>
                </p>
            </x-ui.alert>
        </div>
    </div>
</div>
@endsection
