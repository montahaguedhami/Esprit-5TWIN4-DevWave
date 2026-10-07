@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-[#04121b] text-white flex flex-col font-sans relative">
    <!-- Top Navigation (Liquid Glass Theme) -->
    <header class="sticky top-0 z-50 glass-strong border-b border-white/20 px-4 sm:px-6 py-3">
        <div class="max-w-7xl mx-auto flex items-center justify-between gap-4">
            
            {{-- Brand Logo & Back Button --}}
            <div class="flex items-center gap-3">
                <a href="{{ route('landing') }}" class="liquid-tool text-white/80 hover:text-white" aria-label="Retour à l'accueil">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                </a>
                <a href="{{ route('manager.dashboard') }}" class="flex items-center gap-2.5 group">
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-cyan-400 to-blue-600 flex items-center justify-center shadow-lg shadow-cyan-500/30 group-hover:scale-105 transition-transform">
                        <i data-lucide="droplet" class="w-5 h-5 text-white"></i>
                    </div>
                    <span class="font-display font-semibold text-white text-lg tracking-tight hidden sm:inline-block">AquaSecure</span>
                    <div class="liquid-chip">
                        <span class="w-1.5 h-1.5 rounded-full bg-cyan-400 animate-pulse"></span>
                        <span class="text-[11px] font-medium text-white/90 uppercase tracking-wider">Gestionnaire</span>
                    </div>
                </a>
            </div>

            {{-- Navigation Quick Links --}}
            <div class="hidden md:flex items-center gap-1.5 glass p-1.5 rounded-2xl text-xs font-medium">
                <a href="{{ route('manager.dashboard') }}" class="px-3.5 py-1.5 rounded-xl text-white/80 hover:text-white hover:bg-white/10 transition-all">Tableau de bord</a>
                <a href="{{ route('manager.map') }}" class="px-3.5 py-1.5 rounded-xl text-white/80 hover:text-white hover:bg-white/10 transition-all">Carte du réseau</a>
                <a href="{{ route('manager.quality') }}" class="px-3.5 py-1.5 rounded-xl text-white/80 hover:text-white hover:bg-white/10 transition-all">Qualité de l’eau</a>
                <a href="{{ route('manager.incidents') }}" class="px-3.5 py-1.5 rounded-xl text-white/80 hover:text-white hover:bg-white/10 transition-all">Incidents</a>
                <a href="{{ route('manager.projects') }}" class="px-3.5 py-1.5 rounded-xl text-white/80 hover:text-white hover:bg-white/10 transition-all">Projets</a>
                <a href="{{ route('manager.budget') }}" class="px-3.5 py-1.5 rounded-xl text-white/80 hover:text-white hover:bg-white/10 transition-all">Budget</a>
                <a href="{{ route('manager.techniciens.index') }}" class="px-3.5 py-1.5 rounded-xl text-white/80 hover:text-white hover:bg-white/10 transition-all {{ request()->routeIs('manager.techniciens.*', 'manager.interventions.*') ? 'bg-white/10 text-white' : '' }}">Maintenance</a>
            </div>

            {{-- Right Controls: Role Switcher, Notifications, User Menu --}}
            <div class="flex items-center gap-3">
                <x-notification-center />
                <x-user-menu />
            </div>
        </div>
    </header>

    <!-- Tab Bar (if provided) -->
    @hasSection('tabs')
    <div class="sticky top-[61px] z-40 glass border-b border-white/15 px-4 py-2 flex gap-2 overflow-x-auto">
        @yield('tabs')
    </div>
    @endif

    <!-- Main Content -->
    <div class="flex-1 max-w-[1550px] w-full mx-auto px-4 sm:px-8 py-8 pb-12">
        @yield('manager-content')
    </div>
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
