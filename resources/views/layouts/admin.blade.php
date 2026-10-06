@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-[#04121b] text-white flex font-sans relative" id="admin-shell">

    {{-- ═══════════════════════════════════════════
         SIDEBAR (Liquid Glass)
    ═══════════════════════════════════════════ --}}
    <aside id="admin-sidebar"
           class="fixed inset-y-0 left-0 z-40 flex flex-col w-64 glass-strong border-r border-white/20
                  transition-transform duration-300 lg:translate-x-0 -translate-x-full"
           aria-label="Sidebar administration">

        {{-- Brand --}}
        <div class="flex items-center gap-3 px-5 py-4 border-b border-white/10 shrink-0">
            <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-cyan-400 to-blue-600 flex items-center justify-center shadow-lg shadow-cyan-500/30 shrink-0">
                <i data-lucide="droplet" class="w-5 h-5 text-white"></i>
            </div>
            <div class="flex-1 min-w-0">
                <p class="font-display font-semibold text-white text-base leading-tight">AquaSecure</p>
                <div class="liquid-chip mt-0.5 py-0 px-2 h-5">
                    <span class="text-[9px] text-cyan-300 font-semibold uppercase tracking-wider">Control Center</span>
                </div>
            </div>
            {{-- Close (mobile) --}}
            <button onclick="closeSidebar()"
                    class="lg:hidden text-white/70 hover:text-white transition-colors"
                    aria-label="Fermer menu">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        {{-- Nav --}}
        <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1.5" aria-label="Navigation admin">

            <x-admin-nav-item route="admin.dashboard" icon="layout-dashboard" label="Tableau de bord" />

            <p class="px-3 pt-4 pb-1 text-[10px] font-bold uppercase tracking-widest text-white/40">Gestion System</p>
            <x-admin-nav-item route="admin.users.index" icon="users"     label="Utilisateurs" />
            <x-admin-nav-item route="admin.roles"       icon="shield"    label="Rôles & permissions" />
            <x-admin-nav-item route="manager.map"       icon="map-pin"   label="Carte réseau" />
            <x-admin-nav-item route="admin.logs"        icon="file-text" label="Historique Logs" />

            <p class="px-3 pt-4 pb-1 text-[10px] font-bold uppercase tracking-widest text-white/40">Compte & Config</p>
            <x-admin-nav-item route="profile.show"        icon="user-circle" label="Mon profil" />
            <x-admin-nav-item route="settings.index"      icon="settings"    label="Paramètres" />
            <x-admin-nav-item route="notifications.index" icon="bell"        label="Notifications" />
        </nav>

        {{-- Footer: user card --}}
        <div class="px-3 py-3 border-t border-white/10 shrink-0">
            <a href="{{ route('profile.show') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-2xl glass hover:bg-white/15 transition-colors group">
                <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-cyan-500/40 to-blue-600/40 border border-cyan-400/30
                             flex items-center justify-center text-xs font-bold text-cyan-300 shrink-0">
                    AK
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-white text-xs font-semibold truncate">Amina Kacem</p>
                    <p class="text-cyan-300 text-[10px] truncate">Admin Principal</p>
                </div>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-white/40 group-hover:text-cyan-300 transition-colors shrink-0"></i>
            </a>
        </div>
    </aside>

    {{-- Sidebar overlay (mobile) --}}
    <div id="sidebar-overlay"
         class="fixed inset-0 z-30 bg-black/60 backdrop-blur-md hidden lg:hidden"
         onclick="closeSidebar()"></div>

    {{-- ═══════════════════════════════════════════
         MAIN COLUMN
    ═══════════════════════════════════════════ --}}
    <div class="flex-1 flex flex-col min-w-0 lg:ml-64">

        {{-- ── TOPBAR (Liquid Glass) ── --}}
        <header class="sticky top-0 z-30 glass-strong border-b border-white/20 px-4 sm:px-6 py-3
                        flex items-center gap-3">

            {{-- Back Arrow & Mobile Hamburger --}}
            <a href="{{ route('landing') }}" class="liquid-tool text-white/80 hover:text-white" title="Retour à l'accueil" aria-label="Retour à l'accueil">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
            </a>

            <button onclick="openSidebar()"
                    class="lg:hidden liquid-tool text-cyan-300 hover:text-white"
                    aria-label="Ouvrir le menu">
                <i data-lucide="menu" class="w-5 h-5"></i>
            </button>

            {{-- Page title slot --}}
            <div class="flex-1 min-w-0">
                <h1 class="text-white font-display font-semibold text-base sm:text-lg leading-tight truncate">
                    @yield('page-title', 'Administration')
                </h1>
                <p class="text-white/60 text-xs truncate hidden sm:block">
                    @yield('page-subtitle', 'Vue globale de la plateforme AquaSecure')
                </p>
            </div>

            {{-- Notifications --}}
            <x-notification-center />

            {{-- Admin user menu --}}
            <x-user-menu />
        </header>

        {{-- ── PAGE CONTENT ── --}}
        <main class="flex-1 overflow-x-hidden p-4 sm:p-6 pb-12">
            @yield('admin-content')
        </main>
    </div>
</div>

@push('scripts')
<script>
/* ── Sidebar toggle ── */
function openSidebar() {
    document.getElementById('admin-sidebar').classList.remove('-translate-x-full');
    document.getElementById('sidebar-overlay').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}
function closeSidebar() {
    document.getElementById('admin-sidebar').classList.add('-translate-x-full');
    document.getElementById('sidebar-overlay').classList.add('hidden');
    document.body.style.overflow = '';
}

document.addEventListener('DOMContentLoaded', function() {
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
});
</script>
@endpush
@endsection


