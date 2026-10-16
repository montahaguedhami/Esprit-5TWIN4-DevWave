@extends('layouts.app')

@section('content')
<div class="app-shell has-sidebar admin-dashboard" data-role="admin" id="admin-shell">

    {{-- ══ SIDEBAR ══════════════════════════════════════════════════════════ --}}
    <aside class="app-sidebar" id="admin-sidebar" aria-label="Navigation administration">

        {{-- Brand --}}
        <div class="sidebar-header">
            <a href="{{ route('admin.dashboard') }}" class="sidebar-brand">
                <div class="sidebar-logo">
                    <i data-lucide="shield"></i>
                </div>
                <div class="sidebar-title">
                    <span>AquaSecure</span>
                    <span>Control Center</span>
                </div>
            </a>
            <button onclick="closeSidebar('admin')"
                    class="icon-btn" style="border:none;background:none;color:#94a3b8"
                    aria-label="Fermer le menu" id="admin-sidebar-close">
                <i data-lucide="x"></i>
            </button>
        </div>

        {{-- Navigation --}}
        <nav class="sidebar-nav" aria-label="Menu admin">

            <div class="sidebar-section">
                <p class="sidebar-section-label">Tableau de bord</p>
                <x-admin-nav-item route="admin.dashboard"   icon="layout-dashboard" label="Vue d'ensemble" />
            </div>

            <div class="sidebar-section">
                <p class="sidebar-section-label">Gestion système</p>
                <x-admin-nav-item route="admin.users.index" icon="users"            label="Utilisateurs" />
                <x-admin-nav-item route="admin.zones.index" active-route="admin.zones.*" icon="map-pinned" label="Zones" />
                <x-admin-nav-item route="admin.infrastructures.index" active-route="admin.infrastructures.*" icon="construction" label="Infrastructures" />
                <x-admin-nav-item route="admin.projets.index" active-route="admin.projets.*" icon="folder-kanban" label="Projets" />
                <x-admin-nav-item route="admin.budget" icon="wallet" label="Budgets" />
                <x-admin-nav-item route="admin.qualite" icon="flask-conical" label="Qualité de l’eau" />
                <x-admin-nav-item route="admin.incidents" icon="alert-triangle" label="Incidents" />
                <x-admin-nav-item route="admin.interventions" icon="wrench" label="Interventions" />
                <x-admin-nav-item route="admin.logs"        icon="file-text"        label="Historique Logs" />
            </div>

            <div class="sidebar-section">
                <p class="sidebar-section-label">Mon compte</p>
                <x-admin-nav-item route="profile.show"         icon="user-circle"  label="Mon profil" />
            </div>
        </nav>

        {{-- Footer user --}}
        @php
            $u = session('user', ['name' => 'Admin', 'role' => 'admin']);
            $initials = collect(explode(' ', $u['name']))->map(fn($w)=>strtoupper(substr($w,0,1)))->take(2)->join('');
        @endphp
        <div class="sidebar-footer">
            <a href="{{ route('profile.show') }}" class="sidebar-user">
                <div class="sidebar-avatar">{{ $initials ?: 'AK' }}</div>
                <div class="sidebar-user-info">
                    <p>{{ $u['name'] }}</p>
                    <p>Administrateur</p>
                </div>
                <i data-lucide="chevron-right" style="width:14px;height:14px;color:#64748b;margin-left:auto;flex-shrink:0"></i>
            </a>
        </div>
    </aside>

    {{-- Mobile overlay --}}
    <div class="sidebar-overlay" id="admin-overlay" onclick="closeSidebar('admin')"></div>

    {{-- ══ MAIN COLUMN ════════════════════════════════════════════════════ --}}
    <div class="main-col">

        {{-- Topbar --}}
        <header class="app-topbar">
            <div class="topbar-left">
                <button class="icon-btn" id="admin-menu-btn" onclick="openSidebar('admin')"
                        aria-label="Ouvrir la navigation" aria-expanded="false">
                    <i data-lucide="menu"></i>
                </button>

                <x-back-button :fallback="route('admin.dashboard')" class="icon-btn" />

                <h1 class="topbar-page-title hide-mobile">
                    @yield('page-title', request()->routeIs('admin.zones.*') ? 'Zones' : (request()->routeIs('admin.infrastructures.*') ? 'Infrastructures' : 'Administration'))
                </h1>
            </div>

            <div class="topbar-right">
                {{-- Theme toggle --}}
                <button class="theme-toggle" onclick="toggleTheme()" aria-label="Changer de thème">
                    <svg class="icon-moon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/></svg>
                    <svg class="icon-sun"  width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
                </button>
                <x-notification-center />
                <x-user-menu />
            </div>
        </header>

        {{-- Page content --}}
        <main class="page-content" id="main-content">
            @yield('admin-content')
        </main>

        <x-ui.confirm-modal />
    </div>
</div>

@push('scripts')
<script>
function openSidebar(role) {
    document.getElementById(role + '-sidebar').classList.add('is-open');
    document.getElementById(role + '-overlay').classList.add('is-open');
    document.getElementById(role + '-menu-btn').setAttribute('aria-expanded', 'true');
    document.body.style.overflow = 'hidden';
}
function closeSidebar(role) {
    document.getElementById(role + '-sidebar').classList.remove('is-open');
    document.getElementById(role + '-overlay').classList.remove('is-open');
    var btn = document.getElementById(role + '-menu-btn');
    if (btn) btn.setAttribute('aria-expanded', 'false');
    document.body.style.overflow = '';
}
// Close on Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeSidebar('admin');
});
// Hide close button on desktop (sidebar always visible)
(function() {
    var closeBtn = document.getElementById('admin-sidebar-close');
    function checkDesktop() {
        if (closeBtn) closeBtn.style.display = window.innerWidth >= 1024 ? 'none' : '';
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
