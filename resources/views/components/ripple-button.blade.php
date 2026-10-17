@props([
    'variant' => 'primary',
    'size' => 'md',
    'type' => 'button',
])

@php
    $variants = [
        'primary' => 'bg-gradient-to-r from-cyan-500 to-blue-600 text-white hover:from-cyan-400 hover:to-blue-500 shadow-lg shadow-cyan-500/25',
        'secondary' => 'glass text-cyan-800 hover:text-cyan-950 border border-cyan-200 hover:border-cyan-400',
        'ghost' => 'text-slate-700 hover:bg-slate-100',
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
    onmousedown="handleRipple(event, this)"
>
    {{ $slot }}
</button>

@once
@push('scripts')
<script>
    function handleRipple(event, button) {
        // Effet visuel uniquement — ne bloque PAS la soumission du formulaire
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
        // Pas de preventDefault() — le form se soumet normalement
    }
</script>
@endpush
@endonce
