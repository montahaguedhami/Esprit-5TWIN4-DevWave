@props([
    'text' => '',
    'position' => 'top', // top, bottom, left, right
])

@php
    $positionClasses = match($position) {
        'top' => 'bottom-full left-1/2 -translate-x-1/2 mb-2',
        'bottom' => 'top-full left-1/2 -translate-x-1/2 mt-2',
        'left' => 'right-full top-1/2 -translate-y-1/2 mr-2',
        'right' => 'left-full top-1/2 -translate-y-1/2 ml-2',
        default => 'bottom-full left-1/2 -translate-x-1/2 mb-2',
    };
@endphp

<div class="relative inline-block group">
    {{ $slot }}
    
    <div class="absolute {{ $positionClasses }} z-50 px-3 py-2 text-xs font-medium text-white bg-slate-900 rounded-lg shadow-xl border border-cyan-400/20 whitespace-nowrap pointer-events-none opacity-0 group-hover:opacity-100 transition-opacity duration-200">
        {{ $text }}
    </div>
</div>
