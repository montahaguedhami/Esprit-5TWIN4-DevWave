{{-- Lien stylé comme x-ui.button (le composant existant ne génère que des <button>) --}}
@props([
    'href',
    'variant' => 'primary', // primary, secondary, danger
    'icon' => null,
])

@php
    $variantClasses = match ($variant) {
        'secondary' => 'bg-slate-800/50 hover:bg-slate-700/50 text-cyan-100 border border-cyan-400/15 hover:border-cyan-400/30',
        'danger' => 'bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 border border-rose-400/30',
        default => 'bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 text-white shadow-lg shadow-cyan-500/25',
    };
@endphp

<a href="{{ $href }}" {{ $attributes->merge(['class' => "inline-flex items-center justify-center gap-2 text-sm font-semibold px-4 py-2.5 rounded-xl transition-all duration-300 $variantClasses"]) }}>
    @if($icon)
        <i data-lucide="{{ $icon }}" class="w-4 h-4"></i>
    @endif
    <span>{{ $slot }}</span>
</a>
