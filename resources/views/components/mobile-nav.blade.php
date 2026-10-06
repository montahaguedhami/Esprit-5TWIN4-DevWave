{{-- Mobile Navigation Component --}}
@props([
    'role' => 'citizen',
    'currentRoute' => '',
])

<div class="lg:hidden">
    {{-- Hamburger Button --}}
    <button 
        type="button"
        onclick="toggleMobileNav()"
        class="fixed top-4 left-4 z-50 flex h-12 w-12 items-center justify-center rounded-xl glass border-cyan-500/30 hover:border-cyan-400/50 transition-all duration-300 hover-lift"
        aria-label="Toggle navigation menu"
        aria-expanded="false"
        id="mobile-nav-btn"
    >
        <i data-lucide="menu" class="w-6 h-6 text-cyan-400" id="menu-icon"></i>
        <i data-lucide="x" class="w-6 h-6 text-cyan-400 hidden" id="close-icon"></i>
    </button>

    {{-- Mobile Navigation Overlay --}}
    <div 
        id="mobile-nav-overlay" 
        class="fixed inset-0 z-40 bg-black/60 backdrop-blur-sm opacity-0 pointer-events-none transition-opacity duration-300"
        onclick="toggleMobileNav()"
        aria-hidden="true"
    ></div>

    {{-- Mobile Navigation Menu --}}
    <nav 
        id="mobile-nav-menu"
        class="fixed top-0 left-0 bottom-0 z-40 w-72 glass-strong transform -translate-x-full transition-transform duration-300 overflow-y-auto"
        aria-label="Mobile navigation"
    >
        <div class="p-6">
            {{-- Header --}}
            <div class="flex items-center gap-3 mb-8 mt-12">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-cyan-500 to-blue-600">
                    <i data-lucide="droplet" class="w-6 h-6 text-white"></i>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-white font-display">AquaSecure</h2>
                    <p class="text-xs text-cyan-300">{{ ucfirst($role) }} Space</p>
                </div>
            </div>

            {{-- Navigation Links --}}
            <ul class="space-y-2" role="list">
                @if($role === 'citizen')
                    <li><a href="/citizen/dashboard" class="mobile-nav-link {{ $currentRoute === 'citizen.dashboard' ? 'active' : '' }}">
                        <i data-lucide="layout-dashboard"></i>
                        <span>Tableau de bord</span>
                    </a></li>
                    <li><a href="/citizen/reports" class="mobile-nav-link {{ $currentRoute === 'citizen.reports' ? 'active' : '' }}">
                        <i data-lucide="alert-circle"></i>
                        <span>Mes signalements</span>
                    </a></li>
                    <li><a href="/citizen/invoices" class="mobile-nav-link {{ $currentRoute === 'citizen.invoices' ? 'active' : '' }}">
                        <i data-lucide="file-text"></i>
                        <span>Factures</span>
                    </a></li>
                    <li><a href="/citizen/notifications" class="mobile-nav-link {{ $currentRoute === 'citizen.notifications' ? 'active' : '' }}">
                        <i data-lucide="bell"></i>
                        <span>Notifications</span>
                    </a></li>
                @elseif($role === 'technician')
                    <li><a href="/technician/dashboard" class="mobile-nav-link {{ $currentRoute === 'technician.dashboard' ? 'active' : '' }}">
                        <i data-lucide="layout-dashboard"></i>
                        <span>Tableau de bord</span>
                    </a></li>
                    <li><a href="/technician/interventions" class="mobile-nav-link {{ $currentRoute === 'technician.interventions' ? 'active' : '' }}">
                        <i data-lucide="wrench"></i>
                        <span>Interventions</span>
                    </a></li>
                    <li><a href="/technician/equipment" class="mobile-nav-link {{ $currentRoute === 'technician.equipment' ? 'active' : '' }}">
                        <i data-lucide="shield"></i>
                        <span>Équipements</span>
                    </a></li>
                @elseif($role === 'manager')
                    <li><a href="/manager/dashboard" class="mobile-nav-link {{ $currentRoute === 'manager.dashboard' ? 'active' : '' }}">
                        <i data-lucide="layout-dashboard"></i>
                        <span>Tableau de bord</span>
                    </a></li>
                    <li><a href="/manager/incidents" class="mobile-nav-link {{ $currentRoute === 'manager.incidents' ? 'active' : '' }}">
                        <i data-lucide="alert-triangle"></i>
                        <span>Incidents</span>
                    </a></li>
                    <li><a href="/manager/teams" class="mobile-nav-link {{ $currentRoute === 'manager.teams' ? 'active' : '' }}">
                        <i data-lucide="users"></i>
                        <span>Équipes</span>
                    </a></li>
                    <li><a href="/manager/map" class="mobile-nav-link {{ $currentRoute === 'manager.map' ? 'active' : '' }}">
                        <i data-lucide="map"></i>
                        <span>Carte réseau</span>
                    </a></li>
                    <li><a href="/manager/projects" class="mobile-nav-link {{ $currentRoute === 'manager.projects' ? 'active' : '' }}">
                        <i data-lucide="list"></i>
                        <span>Projets</span>
                    </a></li>
                    <li><a href="/manager/reports" class="mobile-nav-link {{ $currentRoute === 'manager.reports' ? 'active' : '' }}">
                        <i data-lucide="bar-chart-2"></i>
                        <span>Rapports</span>
                    </a></li>
                    <li><a href="/manager/analytics" class="mobile-nav-link {{ $currentRoute === 'manager.analytics' ? 'active' : '' }}">
                        <i data-lucide="bar-chart-2"></i>
                        <span>Analytiques</span>
                    </a></li>
                @elseif($role === 'admin')
                    <li><a href="/admin/dashboard" class="mobile-nav-link {{ $currentRoute === 'admin.dashboard' ? 'active' : '' }}">
                        <i data-lucide="layout-dashboard"></i>
                        <span>Tableau de bord</span>
                    </a></li>
                    <li><a href="/admin/users" class="mobile-nav-link {{ $currentRoute === 'admin.users' ? 'active' : '' }}">
                        <i data-lucide="users"></i>
                        <span>Utilisateurs</span>
                    </a></li>
                    <li><a href="/admin/roles" class="mobile-nav-link {{ $currentRoute === 'admin.roles' ? 'active' : '' }}">
                        <i data-lucide="shield"></i>
                        <span>Rôles & Permissions</span>
                    </a></li>
                    <li><a href="/admin/system" class="mobile-nav-link {{ $currentRoute === 'admin.system' ? 'active' : '' }}">
                        <i data-lucide="activity"></i>
                        <span>Système</span>
                    </a></li>
                    <li><a href="/admin/logs" class="mobile-nav-link {{ $currentRoute === 'admin.logs' ? 'active' : '' }}">
                        <i data-lucide="file-text"></i>
                        <span>Logs</span>
                    </a></li>
                    <li><a href="/admin/security" class="mobile-nav-link {{ $currentRoute === 'admin.security' ? 'active' : '' }}">
                        <i data-lucide="lock"></i>
                        <span>Sécurité</span>
                    </a></li>
                    <li><a href="/admin/backups" class="mobile-nav-link {{ $currentRoute === 'admin.backups' ? 'active' : '' }}">
                        <i data-lucide="database"></i>
                        <span>Sauvegardes</span>
                    </a></li>
                @endif

                {{-- Divider --}}
                <li class="my-4 border-t border-cyan-500/20"></li>

                {{-- Global Links --}}
                <li><a href="/profile" class="mobile-nav-link">
                    <i data-lucide="user"></i>
                    <span>Mon profil</span>
                </a></li>
                <li><a href="/settings" class="mobile-nav-link">
                    <i data-lucide="settings"></i>
                    <span>Paramètres</span>
                </a></li>
                <li><a href="/notifications" class="mobile-nav-link">
                    <i data-lucide="bell"></i>
                    <span>Notifications</span>
                </a></li>
                
                {{-- Divider --}}
                <li class="my-4 border-t border-cyan-500/20"></li>

                {{-- Logout --}}
                <li>
                    <form method="POST" action="/logout">
                        <button type="submit" class="mobile-nav-link text-red-400 hover:bg-red-500/10 w-full">
                            <i data-lucide="log-out"></i>
                            <span>Déconnexion</span>
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </nav>
</div>

<style>
.mobile-nav-link {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1rem;
    border-radius: 0.75rem;
    color: rgba(156, 200, 216, 0.9);
    font-size: 0.9375rem;
    font-weight: 500;
    transition: all 0.2s ease;
    text-decoration: none;
}

.mobile-nav-link:hover {
    background: rgba(5, 191, 219, 0.1);
    color: #5ee5f7;
}

.mobile-nav-link.active {
    background: linear-gradient(135deg, rgba(5, 191, 219, 0.15), rgba(45, 212, 191, 0.15));
    border-left: 3px solid #05bfdb;
    color: #5ee5f7;
    font-weight: 600;
}

.mobile-nav-link i {
    width: 1.25rem;
    height: 1.25rem;
}
</style>

<script>
function toggleMobileNav() {
    const menu = document.getElementById('mobile-nav-menu');
    const overlay = document.getElementById('mobile-nav-overlay');
    const btn = document.getElementById('mobile-nav-btn');
    const menuIcon = document.getElementById('menu-icon');
    const closeIcon = document.getElementById('close-icon');
    
    const isOpen = !menu.classList.contains('-translate-x-full');
    
    if (isOpen) {
        // Close
        menu.classList.add('-translate-x-full');
        overlay.classList.add('opacity-0', 'pointer-events-none');
        menuIcon.classList.remove('hidden');
        closeIcon.classList.add('hidden');
        btn.setAttribute('aria-expanded', 'false');
        document.body.style.overflow = '';
    } else {
        // Open
        menu.classList.remove('-translate-x-full');
        overlay.classList.remove('opacity-0', 'pointer-events-none');
        menuIcon.classList.add('hidden');
        closeIcon.classList.remove('hidden');
        btn.setAttribute('aria-expanded', 'true');
        document.body.style.overflow = 'hidden';
    }
}

// Close on escape key
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        const menu = document.getElementById('mobile-nav-menu');
        if (menu && !menu.classList.contains('-translate-x-full')) {
            toggleMobileNav();
        }
    }
});

// Close on link click
document.querySelectorAll('.mobile-nav-link').forEach(link => {
    link.addEventListener('click', () => {
        setTimeout(() => toggleMobileNav(), 150);
    });
});
</script>
