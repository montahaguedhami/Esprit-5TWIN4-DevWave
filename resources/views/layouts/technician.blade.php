@extends('layouts.app')

@section('content')
<div class="technician-layout flex flex-col font-sans relative">
    <header class="sticky top-0 z-50 glass-strong border-b border-white/20 px-4 sm:px-6 py-3">
        <div class="max-w-7xl mx-auto flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <x-back-button :fallback="route('technician.dashboard')" />
                <a href="{{ route('technician.dashboard') }}" class="flex items-center gap-2.5 group">
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-cyan-400 to-blue-600 flex items-center justify-center shadow-lg shadow-cyan-500/30 group-hover:scale-105 transition-transform">
                        <i data-lucide="droplet" class="w-5 h-5 text-white"></i>
                    </div>
                    <span class="technician-brand-name font-display font-semibold text-white text-lg tracking-tight hidden sm:inline-block">AquaSecure</span>
                    <div class="liquid-chip">
                        <span class="w-1.5 h-1.5 rounded-full bg-teal-400 animate-pulse"></span>
                        <span class="technician-role-label text-[11px] font-medium uppercase tracking-wider">Technicien</span>
                    </div>
                </a>
            </div>

            <nav class="technician-topnav hidden md:flex items-center gap-1.5 glass p-1.5 rounded-2xl text-xs font-medium" aria-label="Navigation technicien">
                <a href="{{ route('technician.dashboard') }}" class="{{ request()->routeIs('technician.dashboard') ? 'is-active' : '' }}">Tableau de bord</a>
                <a href="{{ route('technician.incidents.index') }}" class="{{ request()->routeIs('technician.incidents.*') ? 'is-active' : '' }}">Incidents & actions</a>
                <a href="{{ route('technician.interventions.index') }}" class="{{ request()->routeIs('technician.interventions.*') ? 'is-active' : '' }}">Interventions</a>
                <a href="{{ route('technician.equipment') }}" class="{{ request()->routeIs('technician.equipment') ? 'is-active' : '' }}">Équipement</a>
            </nav>

            <div class="flex items-center gap-3">
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
        </div>
    </header>

    <main class="technician-main" id="main-content">
        @yield('technician-content')
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
