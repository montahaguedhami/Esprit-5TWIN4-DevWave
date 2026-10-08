@php
    $user = session('user', ['name' => 'Utilisateur', 'email' => 'user@aquasecure.tn', 'role' => 'citizen']);
    $roleLabels = [
        'admin' => 'Administrateur',
        'manager' => 'Gestionnaire',
        'technician' => 'Technicien',
        'citizen' => 'Citoyen',
    ];
    $roleColors = [
        'admin' => 'purple',
        'manager' => 'blue',
        'technician' => 'cyan',
        'citizen' => 'emerald',
    ];
@endphp

<!-- User Menu Button -->
<div class="relative user-menu">
    <button 
        onclick="toggleUserMenu()" 
        class="flex max-w-[min(18rem,45vw)] items-center gap-2.5 rounded-xl p-2 glass transition-all group hover:glass-strong"
        aria-label="Menu utilisateur"
    >
        <x-ui.avatar :name="$user['name']" size="sm" />
        <div class="hidden min-w-0 max-w-48 flex-1 text-left md:block">
            <div class="truncate text-sm font-semibold text-white transition-colors group-hover:text-cyan-400">
                {{ $user['name'] }}
            </div>
            <div class="text-xs text-cyan-100/60">{{ $roleLabels[$user['role']] ?? 'Utilisateur' }}</div>
        </div>
        <i data-lucide="chevron-down" class="w-4 h-4 text-cyan-100/60 group-hover:text-cyan-400 transition-all group-hover:rotate-180"></i>
    </button>

    <!-- User Dropdown -->
    <div 
        id="user-menu-dropdown" 
        class="hidden absolute right-0 mt-2 w-80 bg-[#0d1726] rounded-2xl shadow-2xl border border-slate-700 overflow-hidden z-[9999]"
    >
        <!-- User Info Header -->
        <div class="p-4 border-b border-white/10 bg-gradient-to-br from-cyan-500/10 to-blue-600/10">
            <div class="flex items-center gap-3 mb-3">
                <x-ui.avatar :name="$user['name']" size="lg" />
                <div class="flex-1 min-w-0">
                    <h3 class="text-base font-bold text-white truncate">{{ $user['name'] }}</h3>
                    <p class="text-xs text-cyan-100/70 truncate">{{ $user['email'] }}</p>
                </div>
            </div>
            <x-ui.status-badge 
                :status="$roleColors[$user['role']] ?? 'cyan'" 
                :label="$roleLabels[$user['role']] ?? 'Utilisateur'" 
            />
        </div>

        <!-- Menu Items -->
        <div class="py-2">
            <!-- Profile -->
            <a href="{{ route('profile.show') }}" class="flex items-center gap-3 px-4 py-3 hover:bg-white/5 transition-colors group">
                <div class="w-8 h-8 rounded-lg bg-cyan-500/20 flex items-center justify-center group-hover:bg-cyan-500/30 transition-colors">
                    <i data-lucide="user" class="w-4 h-4 text-cyan-400"></i>
                </div>
                <div class="flex-1">
                    <div class="text-sm font-medium text-white">Mon Profil</div>
                    <div class="text-xs text-cyan-100/60">Informations personnelles</div>
                </div>
            </a>

            <!-- Settings -->
            <a href="{{ route('settings.index') }}" class="flex items-center gap-3 px-4 py-3 hover:bg-white/5 transition-colors group">
                <div class="w-8 h-8 rounded-lg bg-blue-500/20 flex items-center justify-center group-hover:bg-blue-500/30 transition-colors">
                    <i data-lucide="settings" class="w-4 h-4 text-blue-400"></i>
                </div>
                <div class="flex-1">
                    <div class="text-sm font-medium text-white">Paramètres</div>
                    <div class="text-xs text-cyan-100/60">Préférences et configuration</div>
                </div>
            </a>

            <!-- Notifications Settings -->
            <a href="{{ route('settings.notifications') }}" class="flex items-center gap-3 px-4 py-3 hover:bg-white/5 transition-colors group">
                <div class="w-8 h-8 rounded-lg bg-purple-500/20 flex items-center justify-center group-hover:bg-purple-500/30 transition-colors">
                    <i data-lucide="bell" class="w-4 h-4 text-purple-400"></i>
                </div>
                <div class="flex-1">
                    <div class="text-sm font-medium text-white">Notifications</div>
                    <div class="text-xs text-cyan-100/60">Gérer les alertes</div>
                </div>
            </a>

            <!-- Divider -->
            <div class="my-2 border-t border-white/10"></div>
            <div class="px-4 py-1 text-[11px] font-bold text-cyan-300 uppercase tracking-wider">Changer de rôle (Demo 120Water)</div>
            <a href="{{ route('switch.role', 'admin') }}" class="flex items-center gap-3 px-4 py-2 hover:bg-white/5 transition-colors">
                <i data-lucide="shield" class="w-4 h-4 text-purple-400"></i>
                <span class="text-xs text-white">System Administrator</span>
            </a>
            <a href="{{ route('switch.role', 'manager') }}" class="flex items-center gap-3 px-4 py-2 hover:bg-white/5 transition-colors">
                <i data-lucide="briefcase" class="w-4 h-4 text-blue-400"></i>
                <span class="text-xs text-white">Utility Manager</span>
            </a>
            <a href="{{ route('switch.role', 'quality') }}" class="flex items-center gap-3 px-4 py-2 hover:bg-white/5 transition-colors">
                <i data-lucide="flask-conical" class="w-4 h-4 text-teal-400"></i>
                <span class="text-xs text-white">Water Quality Specialist</span>
            </a>
            <a href="{{ route('switch.role', 'technician') }}" class="flex items-center gap-3 px-4 py-2 hover:bg-white/5 transition-colors">
                <i data-lucide="wrench" class="w-4 h-4 text-cyan-400"></i>
                <span class="text-xs text-white">Field Technician</span>
            </a>
            <a href="{{ route('switch.role', 'finance') }}" class="flex items-center gap-3 px-4 py-2 hover:bg-white/5 transition-colors">
                <i data-lucide="dollar-sign" class="w-4 h-4 text-emerald-400"></i>
                <span class="text-xs text-white">Finance Manager</span>
            </a>
            <a href="{{ route('switch.role', 'citizen') }}" class="flex items-center gap-3 px-4 py-2 hover:bg-white/5 transition-colors">
                <i data-lucide="user" class="w-4 h-4 text-amber-400"></i>
                <span class="text-xs text-white">Citizen Portal</span>
            </a>

            <!-- Divider -->
            <div class="my-2 border-t border-white/10"></div>

            <!-- Help -->
            <a href="#" class="flex items-center gap-3 px-4 py-3 hover:bg-white/5 transition-colors group">
                <div class="w-8 h-8 rounded-lg bg-emerald-500/20 flex items-center justify-center group-hover:bg-emerald-500/30 transition-colors">
                    <i data-lucide="help-circle" class="w-4 h-4 text-emerald-400"></i>
                </div>
                <div class="flex-1">
                    <div class="text-sm font-medium text-white">Centre d'aide</div>
                    <div class="text-xs text-cyan-100/60">Documentation et support</div>
                </div>
            </a>

            <!-- Divider -->
            <div class="my-2 border-t border-white/10"></div>

            <!-- Logout -->
            <form method="POST" action="{{ route('logout') }}" class="px-2">
                @csrf
                <button type="submit" class="w-full flex items-center gap-3 px-4 py-3 hover:bg-red-500/10 rounded-lg transition-colors group">
                    <div class="w-8 h-8 rounded-lg bg-red-500/20 flex items-center justify-center group-hover:bg-red-500/30 transition-colors">
                        <i data-lucide="log-out" class="w-4 h-4 text-red-400"></i>
                    </div>
                    <div class="flex-1 text-left">
                        <div class="text-sm font-medium text-red-400">Déconnexion</div>
                        <div class="text-xs text-cyan-100/60">Quitter votre session</div>
                    </div>
                </button>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function toggleUserMenu() {
        const dropdown = document.getElementById('user-menu-dropdown');
        const isHidden = dropdown.classList.contains('hidden');
        
        // Close notification dropdown if open
        const notifDropdown = document.getElementById('notification-dropdown');
        if (notifDropdown) {
            notifDropdown.classList.add('hidden');
        }
        
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

    // Close dropdown when clicking outside
    document.addEventListener('click', function(event) {
        const userMenu = document.querySelector('.user-menu');
        if (userMenu && !userMenu.contains(event.target)) {
            document.getElementById('user-menu-dropdown').classList.add('hidden');
        }
    });
</script>
@endpush
