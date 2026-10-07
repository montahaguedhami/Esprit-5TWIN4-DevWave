@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-[#04121b] text-white relative overflow-hidden flex flex-col justify-center">
    <!-- Atmospheric Rain & Vignette Overlay -->
    <x-rain-effect :count="48" />
    <div class="absolute inset-0 bg-gradient-to-b from-[#04121b]/80 via-transparent to-[#04121b]/90 pointer-events-none"></div>

    <!-- Navigation -->
    <nav class="fixed top-0 left-0 right-0 z-50 px-6 py-4 flex items-center justify-between">
        <a href="{{ route('landing') }}" class="flex items-center gap-3 group">
            <span class="w-10 h-10 rounded-xl bg-gradient-to-br from-cyan-400 to-blue-600 flex items-center justify-center shadow-lg shadow-cyan-500/30 group-hover:scale-105 transition-transform">
                <i data-lucide="droplet" class="w-6 h-6 text-white"></i>
            </span>
            <span class="font-display text-xl font-semibold text-white tracking-tight">AquaSecure</span>
        </a>
        <a href="{{ route('landing') }}" class="liquid-tool text-white/80 hover:text-white" aria-label="Retour à l'accueil">
            <i data-lucide="x" class="w-5 h-5"></i>
        </a>
    </nav>

    <!-- Main Content Shell -->
    <main class="relative z-10 min-h-screen flex items-center justify-center px-4 sm:px-8 py-12 w-full max-w-[1500px] mx-auto">
        @yield('auth-content')
    </main>

    <!-- Toast Notifications -->
    <x-ui.toast />
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

