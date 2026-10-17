@php
    $currentUser = auth()->user();
    $notifications = $currentUser
        ? $currentUser->notifications()->latest()->limit(8)->get()->map(function ($notification) {
            $data = $notification->data ?? [];
            $actionTitle = $data['action_title'] ?? null;

            return [
                'id' => (string) $notification->id,
                'title' => $actionTitle ? 'Nouvelle action corrective : ' . $actionTitle : ($data['incident_title'] ?? 'Nouvelle notification'),
                'message' => $data['message'] ?? 'Une mise à jour est disponible.',
                'read' => $notification->read_at !== null,
                'time' => $notification->created_at?->diffForHumans() ?? 'Récemment',
                'color' => 'cyan',
                'icon' => 'wrench',
                'action' => isset($data['incident_id'])
                    ? ['url' => route('incidents.show', $data['incident_id']), 'label' => 'Voir l’incident']
                    : null,
            ];
        })->all()
        : \App\Data\PlaceholderData::globalNotifications();
    $unreadCount = count(array_filter($notifications, fn($n) => !$n['read']));
    $pollUrl = $currentUser && request()->routeIs('citizen.*', 'incidents.*')
        ? route('citizen.alerts.poll')
        : null;
    $viewAllUrl = request()->routeIs('citizen.*', 'incidents.*')
        ? route('citizen.notifications')
        : route('notifications.index');
@endphp

<!-- Notification Bell Button -->
<div class="relative notification-center" @if($pollUrl) data-notification-poll-url="{{ $pollUrl }}" @endif>
    <button 
        onclick="toggleNotifications()" 
        class="relative p-2 rounded-lg glass hover:glass-strong transition-all group"
        aria-label="Notifications"
    >
        <i data-lucide="bell" class="w-5 h-5 text-cyan-700 group-hover:text-cyan-900 transition-colors"></i>
        @if($unreadCount > 0)
        <span id="notification-unread-badge" class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 rounded-full flex items-center justify-center text-[10px] font-bold text-white animate-pulse">
            {{ $unreadCount > 9 ? '9+' : $unreadCount }}
        </span>
        @endif
    </button>

    <!-- Notification Dropdown -->
    <div 
        id="notification-dropdown" 
        class="hidden absolute right-0 mt-2 w-96 bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden z-9999"
        style="max-height: 600px;"
    >
        <!-- Header -->
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="text-lg font-bold text-slate-900">Notifications</h3>
                <p id="notification-unread-label" class="text-xs text-slate-500">{{ $unreadCount }} non lues</p>
            </div>
            <button 
                onclick="markAllAsRead()" 
                class="text-xs text-cyan-700 hover:text-cyan-900 transition-colors font-medium"
            >
                Tout marquer comme lu
            </button>
        </div>

        <!-- Notifications List -->
        <div id="notification-items" class="overflow-y-auto" style="max-height: 450px;" aria-live="polite">
            @forelse($notifications as $notification)
            <div data-notification-id="{{ $notification['id'] }}" data-read="{{ $notification['read'] ? 'true' : 'false' }}" class="notification-item p-4 border-b border-slate-100 hover:bg-slate-50 transition-colors cursor-pointer {{ !$notification['read'] ? 'bg-cyan-500/5' : '' }}"
                 onclick="markAsRead('{{ $notification['id'] }}')">
                <div class="flex items-start gap-3">
                    <!-- Icon -->
                    <div class="shrink-0 w-10 h-10 rounded-lg bg-{{ $notification['color'] }}-500/20 flex items-center justify-center">
                        <i data-lucide="{{ $notification['icon'] }}" class="w-5 h-5 text-{{ $notification['color'] }}-400"></i>
                    </div>

                    <!-- Content -->
                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between gap-2 mb-1">
                            <h4 class="text-sm font-semibold text-slate-800 line-clamp-1">{{ $notification['title'] }}</h4>
                            @if(!$notification['read'])
                            <span class="shrink-0 w-2 h-2 rounded-full bg-cyan-400"></span>
                            @endif
                        </div>
                        <p class="text-xs text-slate-600 line-clamp-2 mb-2">{{ $notification['message'] }}</p>
                        <div class="flex items-center gap-3">
                            <span class="text-xs text-slate-400">{{ $notification['time'] }}</span>
                            @if(isset($notification['action']))
                            <a href="{{ $notification['action']['url'] }}" class="text-xs text-cyan-700 hover:text-cyan-900 font-medium">
                                {{ $notification['action']['label'] }} →
                            </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="p-8 text-center">
                <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-slate-100 flex items-center justify-center">
                    <i data-lucide="bell-off" class="w-8 h-8 text-slate-400"></i>
                </div>
                <p class="text-sm text-slate-500">Aucune notification</p>
            </div>
            @endforelse
        </div>

        <!-- Footer -->
        <div class="p-3 border-t border-slate-100 bg-slate-50">
            <a href="{{ $viewAllUrl }}" class="block text-center text-sm text-cyan-700 hover:text-cyan-900 transition-colors font-medium">
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
        const unreadItems = document.querySelectorAll('.notification-item[data-read="false"]').length;
        const badge = document.getElementById('notification-unread-badge');
        const unreadText = document.getElementById('notification-unread-label');
        
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

    function renderNotification(notification) {
        const item = document.createElement('div');
        item.dataset.notificationId = notification.id;
        item.dataset.read = notification.read ? 'true' : 'false';
        item.className = 'notification-item p-4 border-b border-slate-100 hover:bg-slate-50 transition-colors cursor-pointer' + (notification.read ? '' : ' bg-cyan-500/5');

        const content = document.createElement('div');
        content.className = 'flex items-start gap-3';
        const iconWrap = document.createElement('div');
        iconWrap.className = 'flex-shrink-0 w-10 h-10 rounded-lg bg-cyan-500/20 flex items-center justify-center';
        const icon = document.createElement('i');
        icon.dataset.lucide = 'wrench';
        icon.className = 'w-5 h-5 text-cyan-500';
        iconWrap.appendChild(icon);

        const text = document.createElement('div');
        text.className = 'flex-1 min-w-0';
        const title = document.createElement('h4');
        title.className = 'text-sm font-semibold text-slate-800 line-clamp-1';
        title.textContent = notification.title;
        const message = document.createElement('p');
        message.className = 'text-xs text-slate-600 line-clamp-2 mb-2';
        message.textContent = notification.message;
        const time = document.createElement('span');
        time.className = 'text-xs text-slate-400';
        time.textContent = notification.time;
        text.append(title, message, time);

        if (notification.url) {
            const link = document.createElement('a');
            link.href = notification.url;
            link.className = 'block mt-2 text-xs text-cyan-700 hover:text-cyan-900 font-medium';
            link.textContent = 'Voir l’incident';
            text.appendChild(link);
        }

        content.append(iconWrap, text);
        item.appendChild(content);
        return item;
    }

    async function refreshCitizenNotifications() {
        const root = document.querySelector('[data-notification-poll-url]');
        if (!root) return;

        try {
            const response = await fetch(root.dataset.notificationPollUrl, {
                headers: { 'Accept': 'application/json' },
                credentials: 'same-origin',
            });
            if (!response.ok) return;

            const payload = await response.json();
            const list = document.getElementById('notification-items');
            const existingIds = new Set(Array.from(list.querySelectorAll('[data-notification-id]')).map(item => item.dataset.notificationId));
            const newItems = payload.notifications.filter(notification => !existingIds.has(notification.id));

            list.replaceChildren(...payload.notifications.map(renderNotification));
            const badge = document.getElementById('notification-unread-badge');
            if (payload.unread_count > 0) {
                const count = payload.unread_count > 9 ? '9+' : payload.unread_count;
                if (badge) {
                    badge.textContent = count;
                } else {
                    const badgeElement = document.createElement('span');
                    badgeElement.id = 'notification-unread-badge';
                    badgeElement.className = 'absolute -top-1 -right-1 w-5 h-5 bg-red-500 rounded-full flex items-center justify-center text-[10px] font-bold text-white animate-pulse';
                    badgeElement.textContent = count;
                    root.querySelector('button[aria-label="Notifications"]').appendChild(badgeElement);
                }
            } else if (badge) {
                badge.remove();
            }

            const unreadLabel = document.getElementById('notification-unread-label');
            if (unreadLabel) unreadLabel.textContent = payload.unread_count + ' non lues';
            if (newItems.length && typeof showToast === 'function') {
                showToast('Nouvelle alerte : ' + newItems[0].title, 'info');
            }
            if (typeof lucide !== 'undefined') lucide.createIcons();
        } catch (error) {
            console.error('Impossible de rafraîchir les alertes.', error);
        }
    }

    if (document.querySelector('[data-notification-poll-url]')) {
        window.setInterval(refreshCitizenNotifications, 15000);
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
