@extends('layouts.frontoffice')

@section('title', 'Notifications')

@php
    use App\Data\PlaceholderData;
    $notifications = PlaceholderData::citizenNotifications();
    $incidentNotifications = $incidentNotifications ?? collect();
    $incidentUnreadCount = $incidentNotifications->whereNull('read_at')->count();
    $unreadCount = count(array_filter($notifications, fn($n) => !$n['read'])) + $incidentUnreadCount;
@endphp

@section('content')
<div class="space-y-6 citizen-notifications">
    <!-- Header with Back Navigation -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div class="flex items-center gap-3">
            <a href="{{ route('citizen.dashboard') }}" class="w-10 h-10 rounded-2xl glass flex items-center justify-center text-cyan-300 hover:text-white hover:bg-white/10 transition-all shadow-sm shrink-0" title="Retour au tableau de bord">
                <i data-lucide="arrow-left" class="w-5 h-5"></i>
            </a>
            <div>
                <h1 class="text-2xl sm:text-3xl font-display font-bold text-white tracking-tight">Notifications & Coupures</h1>
                <p class="text-cyan-100/60 text-xs sm:text-sm mt-0.5">
                    @if($unreadCount > 0)
                    Vous avez <span class="text-cyan-300 font-semibold">{{ $unreadCount }} notification(s) non lue(s)</span>
                    @else
                    Toutes vos notifications sont à jour
                    @endif
                </p>
            </div>
        </div>
        
        <div class="flex items-center gap-2">
            @if($unreadCount > 0)
            <x-ui.button variant="outline" size="sm" icon="check-check">
                Tout marquer comme lu
            </x-ui.button>
            @endif
            <x-ui.button variant="outline" size="sm" icon="settings">
                Paramètres
            </x-ui.button>
        </div>
    </div>

    <div class="grid lg:grid-cols-4 gap-6">
        <!-- Filters Sidebar -->
        <div class="lg:col-span-1">
            <x-ui.card padding="sm">
                <h3 class="text-sm font-display font-bold text-white mb-4 px-2">Filtrer par type</h3>
                
                <div class="space-y-1">
                    @foreach([
                        ['value' => 'all', 'label' => 'Toutes', 'icon' => 'inbox', 'count' => count($notifications) + $incidentNotifications->count()],
                        ['value' => 'unread', 'label' => 'Non lues', 'icon' => 'mail', 'count' => $unreadCount],
                        ['value' => 'action_corrective', 'label' => 'Incidents', 'icon' => 'wrench', 'count' => $incidentNotifications->count()],
                        ['value' => 'report_update', 'label' => 'Déclarations', 'icon' => 'alert-circle', 'count' => 2],
                        ['value' => 'water_quality', 'label' => 'Qualité eau', 'icon' => 'flask', 'count' => 1],
                        ['value' => 'maintenance', 'label' => 'Maintenance', 'icon' => 'wrench', 'count' => 1],
                        ['value' => 'invoice', 'label' => 'Factures', 'icon' => 'receipt', 'count' => 1],
                    ] as $filter)
                    <button class="filter-btn w-full flex items-center justify-between px-3 py-2.5 rounded-lg text-left transition-colors {{ $filter['value'] === 'all' ? 'bg-cyan-500/10 text-white' : 'text-cyan-100/60 hover:bg-white/5' }}" data-filter="{{ $filter['value'] }}">
                        <div class="flex items-center gap-3">
                            <i data-lucide="{{ $filter['icon'] }}" class="w-4 h-4"></i>
                            <span class="text-sm font-medium">{{ $filter['label'] }}</span>
                        </div>
                        <span class="text-xs {{ $filter['value'] === 'all' ? 'text-cyan-300' : 'text-cyan-100/40' }}">{{ $filter['count'] }}</span>
                    </button>
                    @endforeach
                </div>
            </x-ui.card>
        </div>

        <!-- Notifications List -->
        <div class="lg:col-span-3">
            <!-- Search -->
            <div class="mb-6">
                <x-ui.search-input name="notification-search" placeholder="Rechercher dans les notifications..." />
            </div>

            <!-- Notifications -->
            <div class="space-y-3">
                @forelse($notifications as $notification)
                <x-ui.card hover padding="none" class="notification-item {{ $notification['read'] ? 'is-read' : 'is-unread' }}" data-type="{{ $notification['type'] }}" data-read="{{ $notification['read'] ? 'true' : 'false' }}">
                    <div class="flex gap-4 p-5">
                        <!-- Icon -->
                        <div class="w-12 h-12 rounded-xl bg-{{ $notification['color'] }}-500/10 flex items-center justify-center flex-shrink-0">
                            <i data-lucide="{{ $notification['icon'] }}" class="w-6 h-6 text-{{ $notification['color'] }}-400"></i>
                        </div>
                        
                        <!-- Content -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-3 mb-2">
                                <h4 class="notification-title font-semibold text-white">{{ $notification['title'] }}</h4>
                                @if(!$notification['read'])
                                <span class="w-2 h-2 rounded-full bg-cyan-400 flex-shrink-0 mt-1.5"></span>
                                @endif
                            </div>
                            <p class="notification-message text-sm text-cyan-100/70 mb-3">{{ $notification['message'] }}</p>
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-cyan-100/40">
                                    <i data-lucide="clock" class="w-3.5 h-3.5 inline mr-1"></i>
                                    Il y a {{ $notification['time'] }}
                                </span>
                                <div class="flex items-center gap-2">
                                    @if(!$notification['read'])
                                    <button class="text-xs text-cyan-400 hover:text-cyan-300 transition-colors font-medium">
                                        Marquer comme lu
                                    </button>
                                    @endif
                                    <button class="text-xs text-cyan-100/50 hover:text-cyan-100/80 transition-colors">
                                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </x-ui.card>
                @empty
                <x-ui.empty-state 
                    icon="inbox"
                    title="Aucune notification"
                    description="Vous n'avez pas encore de notifications"
                />
                @endforelse
            </div>

            <section class="mt-8 space-y-3" aria-labelledby="incident-notifications-title">
                <div class="flex items-center justify-between gap-3">
                    <h2 id="incident-notifications-title" class="text-lg font-display font-bold text-white">Incidents</h2>
                    <span class="text-xs text-cyan-100/50">{{ $incidentNotifications->count() }} action(s) corrective(s)</span>
                </div>

                @forelse($incidentNotifications as $incidentNotification)
                    @php($incidentData = $incidentNotification->data ?? [])
                    <x-ui.card hover padding="none" class="notification-item {{ $incidentNotification->read_at ? 'opacity-75' : '' }}" data-type="action_corrective" data-read="{{ $incidentNotification->read_at ? 'true' : 'false' }}">
                        <div class="flex gap-4 p-5">
                            <div class="shrink-0 flex h-12 w-12 items-center justify-center rounded-xl bg-cyan-500/10">
                                <i data-lucide="wrench" class="h-6 w-6 text-cyan-400"></i>
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-start justify-between gap-3">
                                    <h3 class="font-semibold text-white">
                                        Action corrective : {{ $incidentData['action_title'] ?? 'Action enregistrée' }}
                                    </h3>
                                    @if(! $incidentNotification->read_at)
                                        <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-cyan-400" aria-label="Non lue"></span>
                                    @endif
                                </div>
                                <p class="mt-2 text-sm text-cyan-100/70">{{ $incidentData['message'] ?? 'Le gestionnaire a ajouté une action corrective à votre incident.' }}</p>
                                <p class="mt-2 text-xs text-cyan-100/50">
                                    Incident : {{ $incidentData['incident_title'] ?? 'Incident' }}
                                </p>
                                @if(! empty($incidentData['action_description']))
                                    <p class="mt-2 whitespace-pre-line text-sm text-cyan-100/80">{{ $incidentData['action_description'] }}</p>
                                @endif
                                <div class="mt-3 flex flex-wrap items-center justify-between gap-3">
                                    <span class="text-xs text-cyan-100/40">{{ $incidentNotification->created_at?->diffForHumans() ?? 'Récemment' }}</span>
                                    @if(! empty($incidentData['incident_id']))
                                        <a href="{{ route('incidents.show', $incidentData['incident_id']) }}" class="text-xs font-medium text-cyan-400 hover:text-cyan-300">
                                            Voir l’incident
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </x-ui.card>
                @empty
                    <x-ui.empty-state
                        icon="clipboard-list"
                        title="Aucune action corrective"
                        description="Les actions ajoutées par le gestionnaire à vos incidents apparaîtront ici."
                    />
                @endforelse
            </section>

            <!-- Load More -->
            @if(count($notifications) + $incidentNotifications->count() > 0)
            <div class="mt-6 text-center">
                <x-ui.button variant="outline" icon="refresh-cw">
                    Charger plus
                </x-ui.button>
            </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
// Filter functionality
document.querySelectorAll('.filter-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const filter = this.dataset.filter;
        
        // Update active state
        document.querySelectorAll('.filter-btn').forEach(b => {
            b.classList.remove('bg-cyan-500/10', 'text-white');
            b.classList.add('text-cyan-100/60');
            b.classList.remove('is-active');
        });
        this.classList.add('bg-cyan-500/10', 'text-white');
        this.classList.remove('text-cyan-100/60');
        this.classList.add('is-active');
        
        // Filter notifications
        document.querySelectorAll('.notification-item').forEach(item => {
            const type = item.dataset.type;
            const read = item.dataset.read === 'true';
            const query = document.getElementById('notification-search')?.value.trim().toLocaleLowerCase() || '';
            const searchableText = `${item.querySelector('.notification-title')?.textContent || ''} ${item.querySelector('.notification-message')?.textContent || ''}`.toLocaleLowerCase();
            const matchesSearch = searchableText.includes(query);
            let matchesFilter = true;
            
            if (filter === 'all') {
                matchesFilter = true;
            } else if (filter === 'unread') {
                matchesFilter = !read;
            } else {
                matchesFilter = type === filter;
            }
            item.classList.toggle('hidden', !matchesFilter || !matchesSearch);
        });
    });
});

const notificationSearch = document.getElementById('notification-search');
notificationSearch?.addEventListener('input', function () {
    const activeFilter = document.querySelector('.filter-btn.is-active')?.dataset.filter || 'all';
    document.querySelectorAll('.notification-item').forEach(item => {
        const read = item.dataset.read === 'true';
        const type = item.dataset.type;
        const query = this.value.trim().toLocaleLowerCase();
        const text = `${item.querySelector('.notification-title')?.textContent || ''} ${item.querySelector('.notification-message')?.textContent || ''}`.toLocaleLowerCase();
        const matchesFilter = activeFilter === 'all' || (activeFilter === 'unread' ? !read : type === activeFilter);
        item.classList.toggle('hidden', !matchesFilter || !text.includes(query));
    });
});
</script>
@endpush
@endsection
