@props([
    'placeholder' => 'Rechercher...',
    'name' => 'search',
    'value' => '',
])

<div {{ $attributes->merge(['class' => 'relative']) }}>
    <div class="absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none">
        <i data-lucide="search" class="w-5 h-5 text-cyan-100/40"></i>
    </div>
    
    <input
        type="search"
        name="{{ $name }}"
        id="{{ $name }}"
        value="{{ $value }}"
        placeholder="{{ $placeholder }}"
        class="w-full bg-slate-950/35 border border-cyan-400/15 rounded-xl pl-11 pr-4 py-3 text-sm text-white placeholder:text-cyan-100/30 focus:outline-none focus:border-cyan-400/50 transition-colors"
    />
</div>
