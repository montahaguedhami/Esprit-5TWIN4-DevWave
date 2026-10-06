@extends('layouts.app')

@section('content')
<div class="min-h-screen relative">
    <!-- Top Navigation -->
    <nav class="sticky top-0 z-50 glass-strong px-4 sm:px-6 py-3 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('landing') }}" class="glass p-2 rounded-lg text-cyan-200 hover:text-white transition-colors" aria-label="Retour à l'accueil">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </a>
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-cyan-400 to-blue-600 flex items-center justify-center">
                    <i data-lucide="droplet" class="w-5 h-5 text-white"></i>
                </div>
                <a href="{{ route('manager.dashboard') }}" class="font-display font-bold text-white hidden sm:block">AquaSecure</a>
                <x-badge color="#3b82f6" class="hidden sm:inline-flex">Espace Gestionnaire</x-badge>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <!-- Notifications -->
            <x-notification-center />
            
            <!-- User Menu -->
            <x-user-menu />
        </div>
    </nav>

    <!-- Tab Bar -->
    <div class="sticky top-[57px] z-40 glass px-4 py-2 flex gap-2 overflow-x-auto">
        @hasSection('tabs')
            @yield('tabs')
        @else
            @php
                $mgrTabs = [
                    ['route' => 'manager.dashboard', 'label' => 'Dashboard', 'icon' => 'layout-dashboard'],
                    ['route' => 'manager.incidents', 'label' => 'Incidents', 'icon' => 'alert-triangle'],
                    ['route' => 'manager.teams',     'label' => 'Équipes',   'icon' => 'users'],
                    ['route' => 'manager.projets.index',  'label' => 'Projets',   'icon' => 'briefcase'],
                    ['route' => 'manager.map',       'label' => 'Carte',     'icon' => 'map'],
                    ['route' => 'manager.analytics', 'label' => 'Analytics', 'icon' => 'bar-chart-2'],
                ];
            @endphp
            @foreach($mgrTabs as $tab)
            <a href="{{ route($tab['route']) }}"
               class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-sm font-semibold whitespace-nowrap transition-colors
                      {{ request()->routeIs($tab['route']) ? 'bg-cyan-500/15 text-white border border-cyan-400/25' : 'text-cyan-100/60 hover:text-white hover:bg-white/5' }}">
                <i data-lucide="{{ $tab['icon'] }}" class="w-4 h-4"></i>
                {{ $tab['label'] }}
            </a>
            @endforeach
        @endif
    </div>

    <!-- Content -->
    <div class="px-4 sm:px-6 py-6 max-w-7xl mx-auto pb-8">
        @yield('manager-content')
    </div>
</div>

@push('scripts')
<script>
    // Update theme icons
    function updateThemeIcons() {
        const theme = document.documentElement.getAttribute('data-theme');
        document.querySelectorAll('.sun-icon').forEach(el => {
            theme === 'light' ? el.classList.remove('hidden') : el.classList.add('hidden');
        });
        document.querySelectorAll('.moon-icon').forEach(el => {
            theme === 'light' ? el.classList.add('hidden') : el.classList.remove('hidden');
        });
    }
    
    updateThemeIcons();
    
    const originalToggleTheme = window.toggleTheme;
    window.toggleTheme = function() {
        originalToggleTheme();
        updateThemeIcons();
    };

    // Profile modal toggle (placeholder)
    function toggleProfileModal() {
        showToast('Modal profil à implémenter', 'info');
    }
</script>
@endpush
@endsection
