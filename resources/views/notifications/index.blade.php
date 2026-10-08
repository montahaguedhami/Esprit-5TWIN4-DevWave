@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-900 via-cyan-950 to-slate-900">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-white flex items-center gap-3">
                    <i data-lucide="bell" class="w-8 h-8 text-purple-400"></i>
                    Toutes les Notifications
                </h1>
                <p class="text-slate-400">Centre de notifications AquaSecure</p>
            </div>
            <div class="flex items-center gap-3">
                <button class="btn btn-secondary" onclick="markAllAsRead()">
                    <i data-lucide="check-double" class="w-4 h-4"></i>
                    Tout marquer comme lu
                </button>
                <a href="{{ route('settings.notifications') }}" class="btn btn-secondary">
                    <i data-lucide="settings" class="w-4 h-4"></i>
                    Paramètres
                </a>
            </div>
        </div>

        @php
            $notifications = \App\Data\PlaceholderData::globalNotifications();
            $unreadCount = count(array_filter($notifications, fn($n) => !$n['read']));
        @endphp

        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <x-ui.card class="text-center">
                <div class="text-3xl font-bold text-white mb-1">{{ count($notifications) }}</div>
                <div class="text-sm text-slate-400">Total</div>
            </x-ui.card>
            <x-ui.card class="text-center">
                <div class="text-3xl font-bold text-cyan-400 mb-1">{{ $unreadCount }}</div>
                <div class="text-sm text-slate-400">Non lues</div>
            </x-ui.card>
            <x-ui.card class="text-center">
                <div class="text-3xl font-bold text-emerald-400 mb-1">{{ count($notifications) - $unreadCount }}</div>
                <div class="text-sm text-slate-400">Lues</div>
            </x-ui.card>
            <x-ui.card class="text-center">
                <div class="text-3xl font-bold text-purple-400 mb-1">3</div>
                <div class="text-sm text-slate-400">Aujourd'hui</div>
            </x-ui.card>
        </div>

        <!-- Filters -->
        <x-ui.card class="mb-6">
            <div class="flex flex-wrap items-center gap-4">
                <div class="flex-1 min-w-[200px]">
                    <x-ui.search-input placeholder="Rechercher dans les notifications..." />
                </div>
                
                <x-forms.select name="type" class="w-48">
                    <option value="">Tous les types</option>
                    <option value="system">Système</option>
                    <option value="alert">Alertes</option>
                    <option value="intervention">Interventions</option>
                    <option value="report">Rapports</option>
                    <option value="success">Succès</option>
                </x-forms.select>

                <x-forms.select name="status" class="w-40">
                    <option value="">Toutes</option>
                    <option value="unread">Non lues</option>
                    <option value="read">Lues</option>
                </x-forms.select>

                <x-forms.select name="period" class="w-40">
                    <option value="all">Toutes les périodes</option>
                    <option value="today">Aujourd'hui</option>
                    <option value="week">Cette semaine</option>
                    <option value="month">Ce mois</option>
                </x-forms.select>
            </div>
        </x-ui.card>

        <!-- Notifications List -->
        <x-ui.card>
            <div class="space-y-2">
                @forelse($notifications as $notification)
                <div class="notification-item p-5 rounded-lg hover:bg-slate-800/50 transition-colors cursor-pointer {{ !$notification['read'] ? 'bg-cyan-500/5 border-l-4 border-cyan-500' : 'border-l-4 border-transparent' }}"
                     onclick="markAsReadAndNavigate('{{ $notification['id'] }}', '{{ $notification['action']['url'] ?? '#' }}')">
                    <div class="flex items-start gap-4">
                        <!-- Icon -->
                        <div class="flex-shrink-0 w-12 h-12 rounded-xl bg-{{ $notification['color'] }}-500/20 flex items-center justify-center">
                            <i data-lucide="{{ $notification['icon'] }}" class="w-6 h-6 text-{{ $notification['color'] }}-400"></i>
                        </div>

                        <!-- Content -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-3 mb-2">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-1">
                                        <h3 class="text-base font-semibold text-white">{{ $notification['title'] }}</h3>
                                        @if(!$notification['read'])
                                        <span class="w-2 h-2 rounded-full bg-cyan-400 flex-shrink-0"></span>
                                        @endif
                                    </div>
                                    <p class="text-sm text-cyan-100/70 mb-2">{{ $notification['message'] }}</p>
                                    
                                    <div class="flex items-center gap-4">
                                        <span class="text-xs text-slate-400 flex items-center gap-1">
                                            <i data-lucide="clock" class="w-3 h-3"></i>
                                            {{ $notification['time'] }}
                                        </span>
                                        <span class="text-xs px-2 py-1 rounded bg-{{ $notification['color'] }}-500/20 text-{{ $notification['color'] }}-400">
                                            {{ ucfirst($notification['type']) }}
                                        </span>
                                        @if(isset($notification['action']))
                                        <span class="text-xs text-cyan-400 hover:text-cyan-300 font-medium flex items-center gap-1">
                                            {{ $notification['action']['label'] }}
                                            <i data-lucide="arrow-right" class="w-3 h-3"></i>
                                        </span>
                                        @endif
                                    </div>
                                </div>

                                <!-- Actions -->
                                <div class="flex items-center gap-2">
                                    @if(!$notification['read'])
                                    <button 
                                        onclick="event.stopPropagation(); markAsRead('{{ $notification['id'] }}')" 
                                        class="p-2 rounded-lg hover:bg-slate-700 transition-colors text-cyan-400 hover:text-cyan-300"
                                        title="Marquer comme lu"
                                    >
                                        <i data-lucide="check" class="w-4 h-4"></i>
                                    </button>
                                    @endif
                                    <button 
                                        onclick="event.stopPropagation(); deleteNotification('{{ $notification['id'] }}')" 
                                        class="p-2 rounded-lg hover:bg-slate-700 transition-colors text-red-400 hover:text-red-300"
                                        title="Supprimer"
                                    >
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <x-ui.empty-state 
                    icon="bell-off"
                    title="Aucune notification"
                    message="Vous êtes à jour ! Aucune notification pour le moment."
                />
                @endforelse
            </div>

            <!-- Pagination -->
            @if(count($notifications) > 0)
            <div class="mt-6 flex items-center justify-between border-t border-slate-700 pt-4">
                <div class="text-sm text-slate-400">
                    Affichage de <span class="font-medium text-white">1-{{ count($notifications) }}</span> sur <span class="font-medium text-white">{{ count($notifications) }}</span> notifications
                </div>
                <x-ui.pagination :current="1" :total="1" />
            </div>
            @endif
        </x-ui.card>
    </div>
</div>

@push('scripts')
<script>
    function markAsRead(id) {
        const item = event.currentTarget.closest('.notification-item');
        item.classList.remove('bg-cyan-500/5', 'border-cyan-500');
        item.classList.add('border-transparent');
        
        const badge = item.querySelector('.bg-cyan-400');
        if (badge) badge.remove();
        
        const checkBtn = item.querySelector('button[title="Marquer comme lu"]');
        if (checkBtn) checkBtn.remove();
        
        showToast('Notification marquée comme lue', 'success');
    }

    function markAsReadAndNavigate(id, url) {
        markAsRead(id);
        if (url && url !== '#') {
            setTimeout(() => {
                window.location.href = url;
            }, 500);
        }
    }

    function markAllAsRead() {
        document.querySelectorAll('.notification-item').forEach(item => {
            item.classList.remove('bg-cyan-500/5', 'border-cyan-500');
            item.classList.add('border-transparent');
            
            const badge = item.querySelector('.bg-cyan-400');
            if (badge) badge.remove();
            
            const checkBtn = item.querySelector('button[title="Marquer comme lu"]');
            if (checkBtn) checkBtn.remove();
        });
        
        showToast('Toutes les notifications sont marquées comme lues', 'success');
    }

    function deleteNotification(id) {
        if (confirm('Êtes-vous sûr de vouloir supprimer cette notification ?')) {
            const item = event.currentTarget.closest('.notification-item');
            item.style.opacity = '0';
            item.style.transform = 'translateX(100%)';
            setTimeout(() => item.remove(), 300);
            
            showToast('Notification supprimée', 'success');
        }
    }
</script>
@endpush
@endsection
