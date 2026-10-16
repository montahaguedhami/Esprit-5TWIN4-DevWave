<!DOCTYPE html>
<html lang="fr" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'AquaSecure' }} — Gestion du réseau d'eau</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Inter+Tight:wght@600;700&display=swap" rel="stylesheet">

    <!-- App styles & scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Lucide icons (CDN — loaded before body so icons resolve on first paint) -->
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>

   @stack('styles')
    <div class="route-sweep" aria-hidden="true">
        <span></span>
        <span></span>
        <span></span>
    </div>

    <!-- Scripts -->
    @stack('scripts')

    {{-- Apply saved theme BEFORE paint to avoid flash --}}
    <script>
        (function () {
            var t = localStorage.getItem('aq-theme')
                || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
            document.documentElement.setAttribute('data-theme', t);
        })();
    </script>
</head>
<body>

<div id="app" class="min-h-screen flex flex-col">
@yield('content')
</div>

{{-- ── Demo-mode banner ── --}}
@unless (request()->routeIs('auth.login', 'auth.register', 'auth.forgot-password'))
<div class="demo-banner" role="status" aria-label="Mode démonstration">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 3H5a2 2 0 0 0-2 2v4m6-6h10a2 2 0 0 1 2 2v4M9 3v18m0 0h10a2 2 0 0 0 2-2V9M9 21H5a2 2 0 0 1-2-2V9m0 0h18"/></svg>
    <span><strong>Démo</strong><span class="hide-mobile"> · Données fictives</span></span>
</div>
@endunless

{{-- ── AI Assistant ── --}}
<x-ai-assistant />

{{-- ── Toast container ── --}}
<div id="toast-container" role="region" aria-label="Notifications" aria-live="polite"></div>

<div class="route-sweep" aria-hidden="true">
    <span></span>
    <span></span>
    <span></span>
</div>

@stack('scripts')

<script>
/* ── Theme toggle ── */
window.toggleTheme = function () {
    var next = document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
    document.documentElement.setAttribute('data-theme', next);
    localStorage.setItem('aq-theme', next);
    // Update chart / map colours if needed (dispatch custom event)
    window.dispatchEvent(new CustomEvent('aq:themechange', { detail: { theme: next } }));
};

/* ── Toast system ── */
window.showToast = function (message, type) {
    type = type || 'info';
    var container = document.getElementById('toast-container');
    var id = 'toast-' + Date.now();
    var icons = {
        success: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
        error:   '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
        info:    '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
        warning: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>',
    };
    var colors = { success: 'var(--success)', error: 'var(--danger)', info: 'var(--info)', warning: 'var(--warning)' };
    var el = document.createElement('div');
    el.id = id;
    el.className = 'toast';
    el.style.borderLeftColor = colors[type] || colors.info;
    el.style.borderLeftWidth = '3px';
    el.innerHTML =
        '<span style="color:' + (colors[type] || colors.info) + ';flex-shrink:0">' + (icons[type] || icons.info) + '</span>' +
        '<span style="flex:1;font-size:13px">' + message + '</span>' +
        '<button onclick="this.closest(\'.toast\').remove()" style="background:none;border:none;padding:0;cursor:pointer;color:var(--text-muted);line-height:1" aria-label="Fermer">' +
            '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>' +
        '</button>';
    container.appendChild(el);
    setTimeout(function () {
        if (el.parentNode) {
            el.style.opacity = '0';
            el.style.transform = 'translateX(100%)';
            el.style.transition = 'opacity .3s ease, transform .3s ease';
            setTimeout(function () { if (el.parentNode) el.remove(); }, 300);
        }
    }, 5000);
};

/* ── Init Lucide on every page ── */
document.addEventListener('DOMContentLoaded', function () {
    if (typeof lucide !== 'undefined') lucide.createIcons();
});
</script>
</body>
</html>
