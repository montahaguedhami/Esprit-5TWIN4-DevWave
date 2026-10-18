@props([
    'variant' => 'glass', // glass, glass-strong, solid, bordered
    'padding' => 'default', // none, sm, default, lg
    'hover' => false,
])

@php
    $baseClasses = 'rounded-2xl';
    
    $variantClasses = match($variant) {
        'glass' => 'glass',
        'glass-strong' => 'glass-strong',
        'solid' => 'bg-white border border-slate-200 shadow-sm',
        'bordered' => 'bg-white border border-cyan-100',
        default => 'glass',
    };
    
    $paddingClasses = match($padding) {
        'none' => '',
        'sm' => 'p-4',
        'default' => 'p-6',
        'lg' => 'p-8',
        default => 'p-6',
    };
    
    $hoverClasses = $hover ? 'hover-lift cursor-pointer' : '';
@endphp

<div {{ $attributes->merge(['class' => trim("$baseClasses $variantClasses $paddingClasses $hoverClasses")]) }}>
    {{ $slot }}
</div>
