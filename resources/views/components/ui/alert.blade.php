@props([
    'type' => 'info', // success, error, warning, info
    'title' => null,
    'dismissible' => false,
    'icon' => null,
])

@php
    $config = [
        'success' => [
            'bg' => 'bg-emerald-500/10',
            'border' => 'border-emerald-400/30',
            'text' => 'text-emerald-400',
            'icon' => $icon ?? 'check-circle',
        ],
        'error' => [
            'bg' => 'bg-rose-500/10',
            'border' => 'border-rose-400/30',
            'text' => 'text-rose-400',
            'icon' => $icon ?? 'alert-circle',
        ],
        'warning' => [
            'bg' => 'bg-amber-500/10',
            'border' => 'border-amber-400/30',
            'text' => 'text-amber-400',
            'icon' => $icon ?? 'alert-triangle',
        ],
        'info' => [
            'bg' => 'bg-cyan-500/10',
            'border' => 'border-cyan-400/30',
            'text' => 'text-cyan-400',
            'icon' => $icon ?? 'info',
        ],
    ];
    
    $cfg = $config[$type] ?? $config['info'];
@endphp

<div {{ $attributes->merge(['class' => "flex items-start gap-3 p-4 rounded-xl border {$cfg['bg']} {$cfg['border']}"]) }}>
    <i data-lucide="{{ $cfg['icon'] }}" class="w-5 h-5 {{ $cfg['text'] }} flex-shrink-0 mt-0.5"></i>
    
    <div class="flex-1 min-w-0">
        @if($title)
        <h4 class="font-semibold {{ $cfg['text'] }} mb-1">{{ $title }}</h4>
        @endif
        <div class="text-sm text-cyan-100/80">
            {{ $slot }}
        </div>
    </div>
    
    @if($dismissible)
    <button 
        onclick="this.closest('[class*=bg-]').remove()"
        class="w-6 h-6 rounded-lg flex items-center justify-center hover:bg-white/5 transition-colors flex-shrink-0"
        aria-label="Fermer"
    >
        <i data-lucide="x" class="w-4 h-4 text-cyan-100/60"></i>
    </button>
    @endif
</div>
