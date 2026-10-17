@php
    use App\Models\Role;

    $user = session('user', ['name' => 'Utilisateur', 'email' => 'user@aquasecure.tn', 'role' => Role::CITOYEN]);
    $roleSlug  = Role::normalizeSlug($user['role'] ?? null);
    $roleLabel = Role::label($roleSlug);
    $roleColors = [
        Role::ADMINISTRATEUR => 'purple',
        Role::MANAGER        => 'blue',
        Role::TECHNICIEN     => 'cyan',
        Role::CITOYEN        => 'emerald',
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
            <div class="truncate text-sm font-semibold text-slate-800 transition-colors group-hover:text-cyan-700">
                {{ $user['name'] }}
            </div>
            <div class="text-xs text-cyan-100/60">{{ $roleLabel }}</div>
        </div>
        <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 group-hover:text-cyan-700 transition-all group-hover:rotate-180"></i>
    </button>

    <!-- User Dropdown -->
    <div 
        id="user-menu-dropdown" 
        class="hidden absolute right-0 mt-2 w-80 bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden z-[9999]"
    >
        <!-- User Info Header -->
        <div class="p-4 border-b border-slate-100 bg-gradient-to-br from-cyan-50 to-blue-50">
            <div class="flex items-center gap-3 mb-3">
                <x-ui.avatar :name="$user['name']" size="lg" />
                <div class="flex-1 min-w-0">
                    <h3 class="text-base font-bold text-slate-900 truncate">{{ $user['name'] }}</h3>
                    <p class="text-xs text-slate-500 truncate">{{ $user['email'] }}</p>
                </div>
            </div>
            <x-ui.status-badge
                :status="$roleColors[$roleSlug] ?? 'cyan'"
                :label="$roleLabel"
            />
        </div>

        <!-- Menu Items -->
        <div class="py-2">
            <!-- Profile -->
            <a href="{{ route('profile.show') }}" class="flex items-center gap-3 px-4 py-3 hover:bg-slate-50 transition-colors group">
                <div class="w-8 h-8 rounded-lg bg-cyan-500/20 flex items-center justify-center group-hover:bg-cyan-500/30 transition-colors">
                    <i data-lucide="user" class="w-4 h-4 text-cyan-400"></i>
                </div>
                <div class="flex-1">
                    <div class="text-sm font-medium text-slate-800">Mon Profil</div>
                    <div class="text-xs text-slate-500">Informations personnelles</div>
                </div>
            </a>

            <!-- Settings -->
            <a href="{{ route('settings.index') }}" class="flex items-center gap-3 px-4 py-3 hover:bg-slate-50 transition-colors group">
                <div class="w-8 h-8 rounded-lg bg-blue-500/20 flex items-center justify-center group-hover:bg-blue-500/30 transition-colors">
                    <i data-lucide="settings" class="w-4 h-4 text-blue-400"></i>
                </div>
                <div class="flex-1">
                    <div class="text-sm font-medium text-slate-800">Paramètres</div>
                    <div class="text-xs text-slate-500">Préférences et configuration</div>
                </div>
            </a>

            <!-- Notifications Settings -->
            <a href="{{ route('settings.notifications') }}" class="flex items-center gap-3 px-4 py-3 hover:bg-slate-50 transition-colors group">
                <div class="w-8 h-8 rounded-lg bg-purple-500/20 flex items-center justify-center group-hover:bg-purple-500/30 transition-colors">
                    <i data-lucide="bell" class="w-4 h-4 text-purple-400"></i>
                </div>
                <div class="flex-1">
                    <div class="text-sm font-medium text-slate-800">Notifications</div>
                    <div class="text-xs text-slate-500">Gérer les alertes</div>
                </div>
            </a>

            <!-- Divider -->
            <div class="my-2 border-t border-white/10"></div>


            <!-- Help -->
            <a href="#" class="flex items-center gap-3 px-4 py-3 hover:bg-slate-50 transition-colors group">
                <div class="w-8 h-8 rounded-lg bg-emerald-500/20 flex items-center justify-center group-hover:bg-emerald-500/30 transition-colors">
                    <i data-lucide="help-circle" class="w-4 h-4 text-emerald-400"></i>
                </div>
                <div class="flex-1">
                    <div class="text-sm font-medium text-slate-800">Centre d'aide</div>
                    <div class="text-xs text-slate-500">Documentation et support</div>
                </div>
            </a>

            <!-- Divider -->
            <div class="my-2 border-t border-slate-100"></div>

            <!-- Logout -->
            <form method="POST" action="{{ route('logout') }}" class="px-2">
                @csrf
                <button type="submit" class="w-full flex items-center gap-3 px-4 py-3 hover:bg-red-50 rounded-lg transition-colors group">
                    <div class="w-8 h-8 rounded-lg bg-red-500/20 flex items-center justify-center group-hover:bg-red-500/30 transition-colors">
                        <i data-lucide="log-out" class="w-4 h-4 text-red-400"></i>
                    </div>
                    <div class="flex-1 text-left">
                        <div class="text-sm font-medium text-red-400">Déconnexion</div>
                        <div class="text-xs text-slate-500">Quitter votre session</div>
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
