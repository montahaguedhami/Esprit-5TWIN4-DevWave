@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-[#04121b] text-white flex flex-col font-sans relative">
    <header class="sticky top-0 z-50 glass-strong border-b border-white/20 px-4 sm:px-6 py-3">
        <div class="max-w-7xl mx-auto flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <x-back-button :fallback="route('technician.dashboard')" />
                <a href="{{ route('technician.dashboard') }}" class="flex items-center gap-2.5 group">
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-cyan-400 to-blue-600 flex items-center justify-center shadow-lg shadow-cyan-500/30 group-hover:scale-105 transition-transform">
                        <i data-lucide="droplet" class="w-5 h-5 text-white"></i>
                    </div>
                    <span class="font-display font-semibold text-white text-lg tracking-tight hidden sm:inline-block">AquaSecure</span>
                    <div class="liquid-chip">
                        <span class="w-1.5 h-1.5 rounded-full bg-teal-400 animate-pulse"></span>
                        <span class="text-[11px] font-medium text-white/90 uppercase tracking-wider">Technicien</span>
                    </div>
                </a>
            </div>

            <nav class="hidden md:flex items-center gap-1.5 glass p-1.5 rounded-2xl text-xs font-medium" aria-label="Navigation technicien">
                <a href="{{ route('technician.dashboard') }}" class="px-3.5 py-1.5 rounded-xl {{ request()->routeIs('technician.dashboard') ? 'text-white bg-white/15' : 'text-white/80 hover:text-white hover:bg-white/10' }} transition-all">Tableau de bord</a>
                <a href="{{ route('technician.interventions.index') }}" class="px-3.5 py-1.5 rounded-xl {{ request()->routeIs('technician.interventions.*') ? 'text-white bg-white/15' : 'text-white/80 hover:text-white hover:bg-white/10' }} transition-all">Interventions</a>
                <a href="{{ route('technician.equipment') }}" class="px-3.5 py-1.5 rounded-xl {{ request()->routeIs('technician.equipment') ? 'text-white bg-white/15' : 'text-white/80 hover:text-white hover:bg-white/10' }} transition-all">Équipement</a>
            </nav>

            <div class="flex items-center gap-3">
                <x-notification-center />
                <x-user-menu />
            </div>
        </div>
    </header>

    <div class="flex-1 max-w-[1550px] w-full mx-auto px-4 sm:px-8 py-8 pb-12">
        @yield('technician-content')
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
