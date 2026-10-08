@props([
    'variant' => 'primary',
    'size' => 'md',
    'type' => 'button',
])

@php
    $variants = [
        'primary' => 'bg-gradient-to-r from-cyan-500 to-blue-600 text-white hover:from-cyan-400 hover:to-blue-500 shadow-lg shadow-cyan-500/25',
        'secondary' => 'glass text-cyan-100 hover:text-white border border-cyan-400/30 hover:border-cyan-400/60',
        'ghost' => 'text-cyan-100 hover:bg-white/10',
    ];
    
    $sizes = [
        'sm' => 'px-4 py-2 text-sm',
        'md' => 'px-6 py-3 text-base',
        'lg' => 'px-8 py-4 text-lg',
    ];
    
    $classes = $variants[$variant] . ' ' . $sizes[$size];
@endphp

<button 
    type="{{ $type }}"
    {{ $attributes->merge(['class' => "ripple-btn rounded-xl font-semibold transition-all duration-300 cursor-pointer {$classes}"]) }}
    onclick="handleRipple(event, this)"
>
    {{ $slot }}
</button>

@once
@push('scripts')
<script>
    function handleRipple(event, button) {
        const rect = button.getBoundingClientRect();
        const x = event.clientX - rect.left;
        const y = event.clientY - rect.top;

        const circle = document.createElement('span');
        circle.className = 'ripple-circle';
        circle.style.left = `${x - 10}px`;
        circle.style.top = `${y - 10}px`;
        circle.style.width = '20px';
        circle.style.height = '20px';
        button.appendChild(circle);

        setTimeout(() => circle.remove(), 600);
    }
</script>
@endpush
@endonce
