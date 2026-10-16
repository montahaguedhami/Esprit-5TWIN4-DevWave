@extends('layouts.app')

@section('content')
<div id="fo-shell" data-role="citizen" style="display:flex;flex-direction:column;min-height:100vh;">

    {{-- ══ CITIZEN TOP NAV ══════════════════════════════════════════════════ --}}
    <header class="fo-nav" aria-label="Navigation citoyenne">
        <div class="fo-nav-inner">

            {{-- Left: back + brand --}}
            <div style="display:flex;align-items:center;gap:10px;">
                <x-back-button :fallback="route('citizen.dashboard')" class="icon-btn" />

                <a href="{{ route('citizen.dashboard') }}"
                   style="display:flex;align-items:center;gap:10px;text-decoration:none;">
                    <span style="
                        width:32px;height:32px;border-radius:9px;flex-shrink:0;
                        background:linear-gradient(135deg,#059669,#0891b2);
                        display:flex;align-items:center;justify-content:center;
                    ">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" style="color:#fff"><path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z"/></svg>
                    </span>
                    <span class="hide-mobile" style="font-family:var(--font-display);font-size:15px;font-weight:700;color:var(--text-primary);">
                        <span style="color:#059669">Aqua</span>Secure
                    </span>
                    <span class="hide-mobile badge badge-success" style="font-size:10px;padding:2px 8px;">Portail citoyen</span>
                </a>
            </div>

            {{-- Center: desktop nav links --}}
            <nav class="fo-nav-links" aria-label="Navigation citoyen">
                <a href="{{ route('citizen.dashboard') }}"
                   class="fo-nav-link {{ request()->routeIs('citizen.dashboard') ? 'is-active' : '' }}">
                    <i data-lucide="layout-dashboard"></i>
                    <span>Accueil</span>
                </a>
                <a href="{{ route('incidents.index') }}"
                   class="fo-nav-link {{ request()->routeIs('incidents.*') ? 'is-active' : '' }}">
                    <i data-lucide="alert-circle"></i>
                    <span>Mes incidents</span>
                </a>
                <a href="{{ route('citizen.invoices.index') }}"
                   class="fo-nav-link {{ request()->routeIs('citizen.invoices.*') ? 'is-active' : '' }}">
                    <i data-lucide="file-text"></i>
                    <span>Factures</span>
                </a>
                <a href="{{ route('citizen.travaux.index') }}"
                   class="fo-nav-link {{ request()->routeIs('citizen.travaux.*') ? 'is-active' : '' }}">
                    <i data-lucide="construction"></i>
                    <span>Travaux</span>
                </a>
                <a href="{{ route('citizen.zones.index') }}"
                   class="fo-nav-link {{ request()->routeIs('citizen.zones.*') ? 'is-active' : '' }}">
                    <i data-lucide="map"></i>
                    <span>Zones</span>
                </a>
                <a href="{{ route('citizen.infrastructures.index') }}"
                   class="fo-nav-link {{ request()->routeIs('citizen.infrastructures.*') ? 'is-active' : '' }}">
                    <i data-lucide="construction"></i>
                    <span>Infrastructure</span>
                </a>
                <a href="{{ route('citizen.projets.index') }}"
                   class="fo-nav-link {{ request()->routeIs('citizen.projets.*') ? 'is-active' : '' }}">
                    <i data-lucide="briefcase"></i>
                    <span>Projets</span>
                </a>
                <a href="{{ route('citizen.notifications') }}"
                   class="fo-nav-link {{ request()->routeIs('citizen.notifications') ? 'is-active' : '' }}">
                    <i data-lucide="bell"></i>
                    <span>Alertes</span>
                </a>
            </nav>

            {{-- Right: theme + notifications + user --}}
            <div style="display:flex;align-items:center;gap:8px;">
                {{-- Mobile hamburger --}}
                <div style="position:relative;" class="show-mobile-only">
                    <button class="icon-btn" id="fo-mobile-menu-btn"
                            onclick="toggleFoMenu()" aria-label="Menu citoyen" aria-expanded="false">
                        <i data-lucide="menu"></i>
                    </button>
                    {{-- Mobile dropdown --}}
                    <div id="fo-mobile-menu" class="dropdown-panel" style="display:none;width:220px;">
                        <div style="padding:8px;">
                            <a href="{{ route('citizen.dashboard') }}"       class="fo-nav-link" style="border-radius:8px;">Tableau de bord</a>
                            <a href="{{ route('incidents.index') }}"         class="fo-nav-link" style="border-radius:8px;">Mes incidents</a>
                            <a href="{{ route('citizen.invoices.index') }}"  class="fo-nav-link" style="border-radius:8px;">Factures</a>
                            <a href="{{ route('citizen.travaux.index') }}"   class="fo-nav-link" style="border-radius:8px;">Travaux</a>
                            <a href="{{ route('citizen.zones.index') }}"      class="fo-nav-link" style="border-radius:8px;">Zones</a>
                            <a href="{{ route('citizen.infrastructures.index') }}" class="fo-nav-link" style="border-radius:8px;">Infrastructure</a>
                            <a href="{{ route('citizen.projets.index') }}"   class="fo-nav-link" style="border-radius:8px;">Projets</a>
                            <a href="{{ route('citizen.notifications') }}"   class="fo-nav-link" style="border-radius:8px;">Alertes &amp; coupures</a>
                            <a href="{{ route('profile.show') }}"            class="fo-nav-link" style="border-radius:8px;">Profil</a>
                        </div>
                    </div>
                </div>

                <button class="theme-toggle" onclick="toggleTheme()" aria-label="Changer de thème">
                    <svg class="icon-moon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/></svg>
                    <svg class="icon-sun"  width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
                </button>
                <x-notification-center />
                <x-user-menu />
            </div>
        </div>
    </header>

    {{-- ══ MAIN CONTENT ═══════════════════════════════════════════════════════ --}}
    <main class="fo-main" id="main-content">
        @yield('frontoffice-content')
        @yield('content')
    </main>

    {{-- ══ MOBILE BOTTOM NAV ══════════════════════════════════════════════════ --}}
    <nav class="mobile-bottom-nav" aria-label="Navigation rapide mobile">
        <a href="{{ route('citizen.dashboard') }}"
           class="mobile-nav-item {{ request()->routeIs('citizen.dashboard') ? 'is-active' : '' }}">
            <i data-lucide="home"></i>
            <span>Accueil</span>
        </a>
        <a href="{{ route('incidents.index') }}"
           class="mobile-nav-item {{ request()->routeIs('incidents.*') ? 'is-active' : '' }}">
            <i data-lucide="alert-circle"></i>
            <span>Incidents</span>
        </a>
        <a href="{{ route('incidents.create') }}"
           class="mobile-nav-item"
           style="background:var(--role-accent);border-radius:50%;width:48px;height:48px;padding:0;margin-top:-12px;
                  box-shadow:0 4px 12px rgba(5,150,105,.35);color:#fff;">
            <i data-lucide="plus"></i>
        </a>
        <a href="{{ route('citizen.invoices.index') }}"
           class="mobile-nav-item {{ request()->routeIs('citizen.invoices.*') ? 'is-active' : '' }}">
            <i data-lucide="file-text"></i>
            <span>Factures</span>
        </a>
        <a href="{{ route('citizen.notifications') }}"
           class="mobile-nav-item {{ request()->routeIs('citizen.notifications') ? 'is-active' : '' }}">
            <i data-lucide="bell"></i>
            <span>Alertes</span>
        </a>
    </nav>

</div>

@push('scripts')
<script>
function toggleFoMenu() {
    var menu = document.getElementById('fo-mobile-menu');
    var btn  = document.getElementById('fo-mobile-menu-btn');
    var open = menu.style.display === 'none' || menu.style.display === '';
    menu.style.display = open ? 'block' : 'none';
    btn.setAttribute('aria-expanded', open ? 'true' : 'false');
}
document.addEventListener('click', function(e) {
    var btn  = document.getElementById('fo-mobile-menu-btn');
    var menu = document.getElementById('fo-mobile-menu');
    if (btn && menu && !btn.contains(e.target) && !menu.contains(e.target)) {
        menu.style.display = 'none';
        btn.setAttribute('aria-expanded', 'false');
    }
});
document.addEventListener('DOMContentLoaded', function () {
    if (typeof lucide !== 'undefined') lucide.createIcons();
});
</script>
@endpush
@endsection
