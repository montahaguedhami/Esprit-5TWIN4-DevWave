@props([
    'color' => '#06b6d4',
])

@php
    $colorHex = $color;
    $bgColor = $colorHex . '1a';
    $borderColor = $colorHex . '66';
@endphp

<span 
    {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold border']) }}
    style="background: {{ $bgColor }}; border-color: {{ $borderColor }}; color: {{ $colorHex }};"
>
    {{ $slot }}
</span>
