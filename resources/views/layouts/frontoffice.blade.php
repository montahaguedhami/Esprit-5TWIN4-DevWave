@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-[#04121b] text-white flex flex-col font-sans relative w-full">
    <!-- NAVBAR (Full Width Header Shell) -->
    <header class="sticky top-0 z-50 glass-strong border-b border-white/20 w-full" style="height: var(--navbar-height); padding: 0 var(--container-px); display:flex; align-items:center;">
        <div class="w-full max-w-[1600px] mx-auto flex items-center justify-between gap-4">
            
            {{-- Brand Logo & Back Arrow --}}
            <div class="flex items-center gap-3">
                <x-back-button :fallback="route('citizen.dashboard')" />
                <a href="{{ route('citizen.dashboard') }}" class="flex items-center gap-2.5 group">
                    <div class="rounded-xl bg-gradient-to-br from-cyan-400 to-blue-600 flex items-center justify-center shadow-lg shadow-cyan-500/30 group-hover:scale-105 transition-transform" style="width:var(--navbar-logo-w);height:var(--navbar-logo-w);">
                        <i data-lucide="droplet" style="width:55%;height:55%;" class="text-white"></i>
                    </div>
                    <span class="font-display font-bold text-white tracking-tight hidden sm:block" style="font-size: clamp(1.1rem,1.4vw,1.5rem);">AquaSecure</span>
                    <div class="liquid-chip hidden sm:inline-flex">
                        <span class="w-1.5 h-1.5 rounded-full bg-cyan-400 animate-pulse"></span>
                        <span class="font-semibold text-white/90 uppercase tracking-wider" style="font-size:var(--font-size-badge);">Citoyen</span>
                    </div>
                </a>
            </div>

            {{-- Center: Desktop Navigation Bar --}}
            <nav class="hidden 2xl:flex items-center gap-1 glass p-1 rounded-xl font-semibold" style="font-size:var(--font-size-nav);">
                <a href="{{ route('citizen.dashboard') }}" class="px-4 py-2 rounded-xl text-white/80 hover:text-white hover:bg-white/10 transition-all flex items-center gap-2">
                    <i data-lucide="layout-dashboard" style="width:var(--icon-sm);height:var(--icon-sm);" class="text-cyan-300"></i>
                    <span>Tableau de bord</span>
                </a>
                <a href="{{ route('incidents.index') }}" class="px-4 py-2 rounded-xl text-white/80 hover:text-white hover:bg-white/10 transition-all flex items-center gap-2">
                    <i data-lucide="alert-circle" style="width:var(--icon-sm);height:var(--icon-sm);" class="text-amber-300"></i>
                    <span>Mes incidents</span>
                </a>
                <a href="{{ route('citizen.invoices.index') }}" class="px-4 py-2 rounded-xl text-white/80 hover:text-white hover:bg-white/10 transition-all flex items-center gap-2">
                    <i data-lucide="file-text" style="width:var(--icon-sm);height:var(--icon-sm);" class="text-teal-300"></i>
                    <span>Factures & consommation</span>
                </a>
                <a href="{{ route('citizen.travaux.index') }}" class="px-4 py-2 rounded-xl text-white/80 hover:text-white hover:bg-white/10 transition-all flex items-center gap-2 {{ request()->routeIs('citizen.travaux.*') ? 'bg-white/10 text-white' : '' }}">
                    <i data-lucide="construction" style="width:var(--icon-sm);height:var(--icon-sm);" class="text-orange-300"></i>
                    <span>Travaux</span>
                </a>
                <a href="{{ route('citizen.projets.index') }}" class="px-4 py-2 rounded-xl text-white/80 hover:text-white hover:bg-white/10 transition-all flex items-center gap-2 {{ request()->routeIs('citizen.projets.*') ? 'bg-white/10 text-white' : '' }}">
                    <i data-lucide="briefcase" style="width:var(--icon-sm);height:var(--icon-sm);" class="text-purple-300"></i>
                    <span>Projets d'infrastructure</span>
                </a>
                <a href="{{ route('citizen.notifications') }}" class="px-4 py-2 rounded-xl text-white/80 hover:text-white hover:bg-white/10 transition-all flex items-center gap-2">
                    <i data-lucide="bell" style="width:var(--icon-sm);height:var(--icon-sm);" class="text-sky-300"></i>
                    <span>Alertes & coupures</span>
                </a>
            </nav>

            {{-- Right Controls: Notifications & User menu --}}
            <div class="flex items-center gap-3">
                <details class="relative 2xl:hidden">
                    <summary class="flex h-10 w-10 cursor-pointer list-none items-center justify-center rounded-xl border border-white/10 bg-white/5 text-white/80 hover:bg-white/10" aria-label="Ouvrir la navigation citoyen">
                        <i data-lucide="menu" class="h-5 w-5" aria-hidden="true"></i>
                    </summary>
                    <nav class="absolute right-0 top-12 z-[70] w-64 rounded-2xl border border-white/15 bg-slate-950/95 p-2 shadow-2xl backdrop-blur-xl" aria-label="Navigation citoyen">
                        <a class="block rounded-lg px-3 py-2 text-sm text-white/85 hover:bg-white/10" href="{{ route('citizen.dashboard') }}">Tableau de bord</a>
                        <a class="block rounded-lg px-3 py-2 text-sm text-white/85 hover:bg-white/10" href="{{ route('citizen.reports.create') }}">Signaler un problème</a>
                        <a class="block rounded-lg px-3 py-2 text-sm text-white/85 hover:bg-white/10" href="{{ route('citizen.invoices.index') }}">Factures & consommation</a>
                        <a class="block rounded-lg px-3 py-2 text-sm text-white/85 hover:bg-white/10" href="{{ route('citizen.notifications') }}">Alertes & coupures</a>
                        <a class="block rounded-lg px-3 py-2 text-sm text-white/85 hover:bg-white/10" href="{{ route('profile.show') }}">Profil</a>
                    </nav>
                </details>
                <x-notification-center />
                <x-user-menu />
            </div>
        </div>
    </header>

    {{-- Bottom Nav Mobile Drawer --}}
    <div class="fixed bottom-0 left-0 right-0 z-50 glass-strong border-t border-white/20 px-2 py-2 flex justify-around sm:hidden">
        <a href="{{ route('citizen.dashboard') }}"
           class="flex flex-col items-center gap-0.5 px-3 py-1.5 rounded-xl text-white/70 hover:text-cyan-300 transition-colors">
            <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
            <span class="text-[9px] font-medium">Accueil</span>
        </a>
        <a href="{{ route('incidents.index') }}"
           class="flex flex-col items-center gap-0.5 px-3 py-1.5 rounded-xl liquid-chip text-cyan-300">
            <i data-lucide="alert-circle" class="w-5 h-5"></i>
            <span class="text-[9px] font-medium">Incidents</span>
        </a>
        <a href="{{ route('citizen.invoices.index') }}"
           class="flex flex-col items-center gap-0.5 px-3 py-1.5 rounded-xl text-white/70 hover:text-cyan-300 transition-colors">
            <i data-lucide="file-text" class="w-5 h-5"></i>
            <span class="text-[9px] font-medium">Factures</span>
        </a>
        <a href="{{ route('citizen.notifications') }}"
           class="flex flex-col items-center gap-0.5 px-3 py-1.5 rounded-xl text-white/70 hover:text-cyan-300 transition-colors">
            <i data-lucide="bell" class="w-5 h-5"></i>
            <span class="text-[9px] font-medium">Notifs</span>
        </a>
        <a href="{{ route('profile.show') }}"
           class="flex flex-col items-center gap-0.5 px-3 py-1.5 rounded-xl text-white/70 hover:text-cyan-300 transition-colors">
            <i data-lucide="user" class="w-5 h-5"></i>
            <span class="text-[9px] font-medium">Profil</span>
        </a>
    </div>

    <!-- MAIN CONTAINER (Full-Screen Layout Shell) -->
    <main class="fo-container flex-1" style="padding-top: var(--spacing-xl); padding-bottom: clamp(6rem, 10vw, 3rem);">
        @yield('frontoffice-content')
        @yield('content')
    </main>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    });
</script>
@endpush
@endsection

