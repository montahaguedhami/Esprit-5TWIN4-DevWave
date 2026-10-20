@extends('layouts.app')

@php
    $isTech = in_array(session('user.role'), ['technician', 'technicien'], true);
    $role   = $isTech ? 'technician' : 'manager';
    $dashRoute = $isTech ? route('technician.dashboard') : route('manager.dashboard');
    $roleLabel = $isTech ? 'Espace Technicien' : 'Espace Gestionnaire';
    $u = session('user', ['name' => 'Utilisateur', 'role' => $role]);
    $initials = collect(explode(' ', $u['name']))->map(fn($w)=>strtoupper(substr($w,0,1)))->take(2)->join('');
@endphp

@section('content')
<div class="app-shell has-sidebar" data-role="{{ $role }}" id="mgr-shell">

    {{-- ══ SIDEBAR ══════════════════════════════════════════════════════════ --}}
    <aside class="app-sidebar" id="mgr-sidebar" aria-label="Navigation principale">

        {{-- Brand --}}
        <div class="sidebar-header">
            <a href="{{ $dashRoute }}" class="sidebar-brand">
                <div class="sidebar-logo">
                    <i data-lucide="{{ $isTech ? 'wrench' : 'droplets' }}"></i>
                </div>
                <div class="sidebar-title">
                    <span>AquaSecure</span>
                    <span>{{ $roleLabel }}</span>
                </div>
            </a>
            <button onclick="closeSidebar('mgr')"
                    class="icon-btn" style="border:none;background:none;color:#94a3b8"
                    aria-label="Fermer" id="mgr-sidebar-close">
                <i data-lucide="x"></i>
            </button>
        </div>

        {{-- Navigation --}}
        <nav class="sidebar-nav" aria-label="Menu principal">

            @if($isTech)
            {{-- ── TECHNICIAN NAV ── --}}
            <div class="sidebar-section">
                <p class="sidebar-section-label">Opérations terrain</p>
                <a href="{{ route('technician.dashboard') }}"
                   class="sidebar-link {{ request()->routeIs('technician.dashboard') ? 'is-active' : '' }}">
                    <i data-lucide="layout-dashboard"></i><span>Tableau de bord</span>
                </a>
                <a href="{{ route('technician.tournee') }}"
                   class="sidebar-link {{ request()->routeIs('technician.tournee') ? 'is-active' : '' }}">
                    <i data-lucide="map"></i><span>Tournée du jour</span>
                </a>
                <a href="{{ route('technician.scan') }}"
                   class="sidebar-link {{ request()->routeIs('technician.scan*') ? 'is-active' : '' }}">
                    <i data-lucide="qr-code"></i><span>Scanner QR Code</span>
                </a>
            </div>

            <div class="sidebar-section">
                <p class="sidebar-section-label">Mon secteur</p>
                <a href="{{ route('technician.zones.index') }}"
                   class="sidebar-link {{ request()->routeIs('technician.zones.*') ? 'is-active' : '' }}">
                    <i data-lucide="map-pin"></i><span>Mes zones</span>
                </a>
                <a href="{{ route('technician.infrastructures.index') }}"
                   class="sidebar-link {{ request()->routeIs('technician.infrastructures.*') ? 'is-active' : '' }}">
                    <i data-lucide="layers"></i><span>Mes infrastructures</span>
                </a>
            </div>

            <div class="sidebar-section">
                <p class="sidebar-section-label">Interventions</p>
                <a href="{{ route('technician.incidents.index') }}"
                   class="sidebar-link {{ request()->routeIs('technician.incidents.*') ? 'is-active' : '' }}">
                    <i data-lucide="alert-circle"></i><span>Incidents & actions correctives</span>
                </a>
                <a href="{{ route('technician.interventions.index') }}"
                   class="sidebar-link {{ request()->routeIs('technician.interventions.*') ? 'is-active' : '' }}">
                    <i data-lucide="wrench"></i><span>Mes interventions</span>
                </a>
                <a href="{{ route('technician.equipment') }}"
                   class="sidebar-link {{ request()->routeIs('technician.equipment') ? 'is-active' : '' }}">
                    <i data-lucide="package"></i><span>Équipement</span>
                </a>
            </div>

            @else
            {{-- ── MANAGER NAV ── --}}
            <div class="sidebar-section">
                <p class="sidebar-section-label">Vue d'ensemble</p>
                <a href="{{ route('manager.dashboard') }}"
                   class="sidebar-link {{ request()->routeIs('manager.dashboard') ? 'is-active' : '' }}">
                    <i data-lucide="layout-dashboard"></i><span>Tableau de bord</span>
                </a>
                <a href="{{ route('manager.map') }}"
                   class="sidebar-link {{ request()->routeIs('manager.map') ? 'is-active' : '' }}">
                    <i data-lucide="map"></i><span>Carte du réseau</span>
                </a>
            </div>

            <div class="sidebar-section">
                <p class="sidebar-section-label">Patrimoine</p>
                <a href="{{ route('manager.zones.index') }}"
                   class="sidebar-link {{ request()->routeIs('manager.zones.*') ? 'is-active' : '' }}">
                    <i data-lucide="map-pin"></i><span>Zones</span>
                </a>
                <a href="{{ route('manager.infrastructures.index') }}"
                   class="sidebar-link {{ request()->routeIs('manager.infrastructures.*') ? 'is-active' : '' }}">
                    <i data-lucide="layers"></i><span>Infrastructures</span>
                </a>
            </div>

            <div class="sidebar-section">
                <p class="sidebar-section-label">Exploitation</p>
                <a href="{{ route('manager.quality') }}"
                   class="sidebar-link {{ request()->routeIs('manager.quality*', 'manager.quality-measures.*') ? 'is-active' : '' }}">
                    <i data-lucide="droplets"></i><span>Qualité de l'eau</span>
                </a>
                <a href="{{ route(request()->routeIs('admin.*') ? 'admin.incidents' : 'manager.incidents') }}"
                   class="sidebar-link {{ request()->routeIs('manager.incidents*', 'manager.actions.*') ? 'is-active' : '' }}">
                    <i data-lucide="alert-circle"></i><span>Incidents & Actions</span>
                </a>
                <a href="{{ route('manager.techniciens.index') }}"
                   class="sidebar-link {{ request()->routeIs('manager.techniciens.*', 'manager.interventions.*', 'manager.maintenance.*') ? 'is-active' : '' }}">
                    <i data-lucide="wrench"></i><span>Maintenance</span>
                </a>
                <a href="{{ route('manager.planning.index') }}"
                   class="sidebar-link {{ request()->routeIs('manager.planning.*') ? 'is-active' : '' }}">
                    <i data-lucide="calendar-days"></i><span>Planning</span>
                </a>
            </div>

            <div class="sidebar-section">
                <p class="sidebar-section-label">Gestion</p>
                <a href="{{ route('manager.projets.index') }}"
                   class="sidebar-link {{ request()->routeIs('manager.projets.*') ? 'is-active' : '' }}">
                    <i data-lucide="briefcase"></i><span>Projets</span>
                </a>
                <a href="{{ route('manager.budget') }}"
                   class="sidebar-link {{ request()->routeIs('manager.budget*', 'manager.financements.*') ? 'is-active' : '' }}">
                    <i data-lucide="credit-card"></i><span>Budget</span>
                </a>
                <a href="{{ route('manager.teams') }}"
                   class="sidebar-link {{ request()->routeIs('manager.teams') ? 'is-active' : '' }}">
                    <i data-lucide="users"></i><span>Équipes</span>
                </a>
                <a href="{{ route('manager.analytics') }}"
                   class="sidebar-link {{ request()->routeIs('manager.analytics') ? 'is-active' : '' }}">
                    <i data-lucide="line-chart"></i><span>Analyses et rapports</span>
                </a>
            </div>
            @endif

            {{-- Common: account section --}}
            <div class="sidebar-section">
                <p class="sidebar-section-label">Mon compte</p>
                <a href="{{ route('profile.show') }}"
                   class="sidebar-link {{ request()->routeIs('profile.*') ? 'is-active' : '' }}">
                    <i data-lucide="user-circle"></i><span>Mon profil</span>
                </a>
            </div>
        </nav>

        {{-- Footer user --}}
        <div class="sidebar-footer">
            <div class="sidebar-user" style="cursor:default">
                <div class="sidebar-avatar">{{ $initials ?: 'AQ' }}</div>
                <div class="sidebar-user-info">
                    <p>{{ $u['name'] }}</p>
                    <p>{{ $isTech ? 'Technicien terrain' : 'Gestionnaire' }}</p>
                </div>
            </div>
        </div>
    </aside>

    {{-- Mobile overlay --}}
    <button type="button" class="sidebar-overlay" id="mgr-overlay" onclick="closeSidebar('mgr')" aria-label="Fermer le menu"></button>

    {{-- ══ MAIN COLUMN ════════════════════════════════════════════════════ --}}
    <div class="main-col">

        {{-- Topbar --}}
        <header class="app-topbar">
            <div class="topbar-left">
                <x-back-button :fallback="$dashRoute" class="icon-btn" />
                <button class="icon-btn sidebar-toggle" id="mgr-menu-btn" onclick="toggleSidebar('mgr')"
                        aria-label="Ouvrir la navigation" aria-expanded="false">
                    <i data-lucide="menu" class="sidebar-toggle-icon sidebar-toggle-icon--closed"></i>
                    <i data-lucide="arrow-left" class="sidebar-toggle-icon sidebar-toggle-icon--open"></i>
                </button>

                {{-- Role chip --}}
                <span class="hide-mobile" style="
                    display:inline-flex;align-items:center;gap:6px;
                    padding:3px 10px;border-radius:999px;
                    background:var(--role-accent-light);
                    color:var(--role-accent);
                    font-size:11px;font-weight:700;letter-spacing:.03em;
                ">
                    <span style="width:6px;height:6px;border-radius:50%;background:var(--role-accent)" class="animate-pulse"></span>
                    AquaSecure v2.4 · {{ $isTech ? 'Technicien' : 'Gestionnaire' }}
                </span>
            </div>

            <div class="topbar-right">
                <button class="theme-toggle" onclick="toggleTheme()" aria-label="Changer de thème">
                    <svg class="icon-moon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/></svg>
                    <svg class="icon-sun"  width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
                </button>
                <button type="button" class="theme-toggle aq-topbar-acc-btn" onclick="openAccessibilityModal()" aria-label="Paramètres d'accessibilité" title="Accessibilité (Alt+A)">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="12" cy="4.5" r="2.2"/>
                        <path d="M12 7v6.5"/>
                        <path d="m5 10 7 1.5 7-1.5"/>
                        <path d="m7.5 21 4.5-7.5 4.5 7.5"/>
                    </svg>
                </button>
                <x-notification-center />
                <x-user-menu />
            </div>
        </header>

        {{-- Optional tab bar --}}
        @hasSection('tabs')
        <div style="
            position:sticky;top:var(--topbar-h);z-index:20;
            background:var(--bg-topbar);border-bottom:1px solid var(--border);
            padding:var(--sp-2) var(--sp-6);
            display:flex;gap:var(--sp-2);overflow-x:auto;
        ">
            @yield('tabs')
        </div>
        @endif

        {{-- Page content --}}
        @php($isMap = request()->routeIs('manager.map'))
        <main class="{{ $isMap ? '' : 'page-content' }}" id="main-content"
              @if($isMap) style="flex:1;width:100%;max-width:none;padding:0" @endif>
            @yield('manager-content')
        </main>

        <x-ui.confirm-modal />
    </div>
</div>

@push('scripts')
<script>
function openSidebar(role) {
    var sidebar = document.getElementById(role + '-sidebar');
    var shell = document.getElementById(role + '-shell');
    var overlay = document.getElementById(role + '-overlay');
    if (window.innerWidth >= 1024) {
        shell.classList.remove('sidebar-collapsed');
    } else {
        sidebar.classList.add('is-open');
        overlay.classList.add('is-open');
        document.body.style.overflow = 'hidden';
    }
    updateSidebarToggle(role);
}
function closeSidebar(role) {
    var sidebar = document.getElementById(role + '-sidebar');
    var shell = document.getElementById(role + '-shell');
    var overlay = document.getElementById(role + '-overlay');
    if (window.innerWidth >= 1024) {
        shell.classList.add('sidebar-collapsed');
    } else {
        sidebar.classList.remove('is-open');
        overlay.classList.remove('is-open');
        document.body.style.overflow = '';
    }
    updateSidebarToggle(role);
}
function toggleSidebar(role) {
    var sidebar = document.getElementById(role + '-sidebar');
    var shell = document.getElementById(role + '-shell');
    var isOpen = window.innerWidth >= 1024
        ? !shell.classList.contains('sidebar-collapsed')
        : sidebar.classList.contains('is-open');
    if (isOpen) closeSidebar(role);
    else openSidebar(role);
}
function updateSidebarToggle(role) {
    var sidebar = document.getElementById(role + '-sidebar');
    var shell = document.getElementById(role + '-shell');
    var btn = document.getElementById(role + '-menu-btn');
    if (!sidebar || !shell || !btn) return;
    var isOpen = window.innerWidth >= 1024
        ? !shell.classList.contains('sidebar-collapsed')
        : sidebar.classList.contains('is-open');
    btn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    btn.setAttribute('aria-label', isOpen ? 'Fermer la navigation' : 'Ouvrir la navigation');
}
function syncSidebarAfterResize() {
    if (window.innerWidth >= 1024) {
        document.getElementById('mgr-sidebar').classList.remove('is-open');
        document.getElementById('mgr-overlay').classList.remove('is-open');
        document.body.style.overflow = '';
    }
    updateSidebarToggle('mgr');
}
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeSidebar('mgr');
});
(function() {
    var closeBtn = document.getElementById('mgr-sidebar-close');
    function checkDesktop() {
        if (closeBtn) closeBtn.style.display = window.innerWidth >= 1024 ? 'none' : '';
        syncSidebarAfterResize();
    }
    checkDesktop();
    window.addEventListener('resize', checkDesktop);
})();
document.addEventListener('DOMContentLoaded', function () {
    if (typeof lucide !== 'undefined') lucide.createIcons();
});
</script>
@endpush
@endsection
