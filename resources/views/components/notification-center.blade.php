@php
    $notifications = \App\Data\PlaceholderData::globalNotifications();
    $unreadCount = count(array_filter($notifications, fn($n) => !$n['read']));
@endphp

<!-- Notification Bell Button -->
<div class="relative notification-center">
    <button 
        onclick="toggleNotifications()" 
        class="relative p-2 rounded-lg glass hover:glass-strong transition-all group"
        aria-label="Notifications"
    >
        <i data-lucide="bell" class="w-5 h-5 text-cyan-100/70 group-hover:text-cyan-400 transition-colors"></i>
        @if($unreadCount > 0)
        <span class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 rounded-full flex items-center justify-center text-[10px] font-bold text-white animate-pulse">
            {{ $unreadCount > 9 ? '9+' : $unreadCount }}
        </span>
        @endif
    </button>

    <!-- Notification Dropdown -->
    <div 
        id="notification-dropdown" 
        class="hidden absolute right-0 mt-2 w-96 bg-[#0d1726] rounded-2xl shadow-2xl border border-slate-700 overflow-hidden z-[9999]"
        style="max-height: 600px;"
    >
        <!-- Header -->
        <div class="p-4 border-b border-white/10 flex items-center justify-between">
            <div>
                <h3 class="text-lg font-bold text-white">Notifications</h3>
                <p class="text-xs text-cyan-100/60">{{ $unreadCount }} non lues</p>
            </div>
            <button 
                onclick="markAllAsRead()" 
                class="text-xs text-cyan-400 hover:text-cyan-300 transition-colors font-medium"
            >
                Tout marquer comme lu
            </button>
        </div>

        <!-- Notifications List -->
        <div class="overflow-y-auto" style="max-height: 450px;">
            @forelse($notifications as $notification)
            <div class="notification-item p-4 border-b border-white/5 hover:bg-white/5 transition-colors cursor-pointer {{ !$notification['read'] ? 'bg-cyan-500/5' : '' }}"
                 onclick="markAsRead('{{ $notification['id'] }}')">
                <div class="flex items-start gap-3">
                    <!-- Icon -->
                    <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-{{ $notification['color'] }}-500/20 flex items-center justify-center">
                        <i data-lucide="{{ $notification['icon'] }}" class="w-5 h-5 text-{{ $notification['color'] }}-400"></i>
                    </div>

                    <!-- Content -->
                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between gap-2 mb-1">
                            <h4 class="text-sm font-semibold text-white line-clamp-1">{{ $notification['title'] }}</h4>
                            @if(!$notification['read'])
                            <span class="flex-shrink-0 w-2 h-2 rounded-full bg-cyan-400"></span>
                            @endif
                        </div>
                        <p class="text-xs text-cyan-100/70 line-clamp-2 mb-2">{{ $notification['message'] }}</p>
                        <div class="flex items-center gap-3">
                            <span class="text-xs text-cyan-100/50">{{ $notification['time'] }}</span>
                            @if(isset($notification['action']))
                            <a href="{{ $notification['action']['url'] }}" class="text-xs text-cyan-400 hover:text-cyan-300 font-medium">
                                {{ $notification['action']['label'] }} →
                            </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="p-8 text-center">
                <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-slate-800/50 flex items-center justify-center">
                    <i data-lucide="bell-off" class="w-8 h-8 text-slate-600"></i>
                </div>
                <p class="text-sm text-slate-400">Aucune notification</p>
            </div>
            @endforelse
        </div>

        <!-- Footer -->
        <div class="p-3 border-t border-white/10 bg-slate-900/50">
            <a href="{{ route('notifications.index') }}" class="block text-center text-sm text-cyan-400 hover:text-cyan-300 transition-colors font-medium">
                Voir toutes les notifications
            </a>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function toggleNotifications() {
        const dropdown = document.getElementById('notification-dropdown');
        const isHidden = dropdown.classList.contains('hidden');
        
        // Close all other dropdowns first
        document.querySelectorAll('.notification-center [id$="-dropdown"]').forEach(d => {
            if (d.id !== 'notification-dropdown') {
                d.classList.add('hidden');
            }
        });
        
        if (isHidden) {
            dropdown.classList.remove('hidden');
            dropdown.style.animation = 'slideDown 0.2s ease-out';
        } else {
            dropdown.classList.add('hidden');
        }
        
        // Reinitialize icons
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    }

    function markAsRead(id) {
        // Simulate marking as read
        const item = event.currentTarget;
        item.classList.remove('bg-cyan-500/5');
        const badge = item.querySelector('.bg-cyan-400');
        if (badge) badge.remove();
        
        // Update counter
        updateUnreadCount();
        
        showToast('Notification marquée comme lue', 'success');
    }

    function markAllAsRead() {
        document.querySelectorAll('.notification-item').forEach(item => {
            item.classList.remove('bg-cyan-500/5');
            const badge = item.querySelector('.bg-cyan-400');
            if (badge) badge.remove();
        });
        
        updateUnreadCount();
        showToast('Toutes les notifications sont marquées comme lues', 'success');
    }

    function updateUnreadCount() {
        const unreadItems = document.querySelectorAll('.notification-item.bg-cyan-500\\/5').length;
        const badge = document.querySelector('.notification-center .bg-red-500');
        const unreadText = document.querySelector('.notification-center h3 + p');
        
        if (badge) {
            if (unreadItems === 0) {
                badge.remove();
            } else {
                badge.textContent = unreadItems > 9 ? '9+' : unreadItems;
            }
        }
        
        if (unreadText) {
            unreadText.textContent = unreadItems + ' non lues';
        }
    }

    // Close dropdown when clicking outside
    document.addEventListener('click', function(event) {
        const notifCenter = document.querySelector('.notification-center');
        if (notifCenter && !notifCenter.contains(event.target)) {
            document.getElementById('notification-dropdown').classList.add('hidden');
        }
    });

    // Animation keyframes
    if (!document.querySelector('#notification-animations')) {
        const style = document.createElement('style');
        style.id = 'notification-animations';
        style.textContent = `
            @keyframes slideDown {
                from {
                    opacity: 0;
                    transform: translateY(-10px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
        `;
        document.head.appendChild(style);
    }
</script>
@endpush
