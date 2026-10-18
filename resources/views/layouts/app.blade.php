<!DOCTYPE html>
<html lang="fr" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'AquaSecure' }} — Gestion du réseau d'eau</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Inter+Tight:wght@600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://unpkg.com/lucide@latest"></script>
    @stack('styles')
    <script>
        (function () {
            var theme = localStorage.getItem('aq-theme')
                || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
            document.documentElement.setAttribute('data-theme', theme);

            try {
                var acc = localStorage.getItem('aq-accessibility-preferences');
                if (acc) {
                    var p = JSON.parse(acc);
                    if (p.font && p.font !== 'normal') document.documentElement.setAttribute('data-aq-font', p.font);
                    if (p.contrast) document.documentElement.classList.add('aq-contrast');
                    if (p.grayscale) document.documentElement.classList.add('aq-grayscale');
                    if (p.dyslexia) document.documentElement.classList.add('aq-dyslexia');
                    if (p.spacing) document.documentElement.classList.add('aq-spacing');
                    if (p.links) document.documentElement.classList.add('aq-links');
                    if (p.cursor) document.documentElement.classList.add('aq-cursor');
                    if (p.motion) document.documentElement.classList.add('aq-motion');
                }
            } catch (e) {}
        })();
    </script>
</head>
<body class="antialiased bg-[#04121b] text-white min-h-screen">
    <div id="app" class="min-h-screen flex flex-col">
        @yield('content')
    </div>



    <x-accessibility-menu />
    <x-ai-assistant />
    <div id="toast-container" role="region" aria-label="Notifications" aria-live="polite"></div>
    <div class="route-sweep" aria-hidden="true"><span></span><span></span><span></span></div>

    @stack('scripts')
    <script>
        window.toggleTheme = function () {
            var next = document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', next);
            localStorage.setItem('aq-theme', next);
            window.dispatchEvent(new CustomEvent('aq:themechange', { detail: { theme: next } }));
        };

        window.showToast = function (message, type) {
            type = type || 'info';
            var container = document.getElementById('toast-container');
            var icons = {
                success: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
                error: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
                info: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
                warning: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>'
            };
            var colors = { success: 'var(--success)', error: 'var(--danger)', info: 'var(--info)', warning: 'var(--warning)' };
            var toast = document.createElement('div');
            toast.className = 'toast';
            toast.style.borderLeftColor = colors[type] || colors.info;
            toast.style.borderLeftWidth = '3px';
            var icon = document.createElement('span');
            icon.style.color = colors[type] || colors.info;
            icon.innerHTML = icons[type] || icons.info;
            var text = document.createElement('span');
            text.style.flex = '1';
            text.style.fontSize = '13px';
            text.textContent = String(message);
            var close = document.createElement('button');
            close.type = 'button';
            close.textContent = '×';
            close.setAttribute('aria-label', 'Fermer');
            close.addEventListener('click', function () { toast.remove(); });
            toast.append(icon, text, close);
            container.appendChild(toast);
            setTimeout(function () {
                if (toast.parentNode) toast.remove();
            }, 5000);
        };

        document.addEventListener('DOMContentLoaded', function () {
            if (typeof lucide !== 'undefined') lucide.createIcons();
        });
    </script>
</body>
</html>
