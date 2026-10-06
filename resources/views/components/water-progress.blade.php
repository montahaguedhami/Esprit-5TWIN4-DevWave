@props([
    'value' => 0,
    'max' => 100,
    'height' => 120,
    'label' => null,
    'showValue' => true,
])

@php
    $percentage = min(($value / $max) * 100, 100);
@endphp

<div class="flex flex-col gap-2">
    @if($label || $showValue)
    <div class="flex justify-between items-center">
        @if($label)
        <span class="text-sm text-cyan-100/70">{{ $label }}</span>
        @endif
        @if($showValue)
        <span class="text-sm font-bold text-cyan-300">{{ round($percentage) }}%</span>
        @endif
    </div>
    @endif
    
    <div 
        class="water-rise w-full bg-slate-900/50 border border-cyan-400/20"
        style="height: {{ $height }}px;"
    >
        <div class="water-rise-fill" style="height: {{ $percentage }}%;"></div>
    </div>
</div>
