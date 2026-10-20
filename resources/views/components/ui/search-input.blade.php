@props([
    'placeholder' => 'Rechercher...',
    'name' => 'search',
    'value' => '',
    'dark' => false,
])

<div {{ $attributes->merge(['class' => 'relative']) }}>
    <div class="absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none">
        <i data-lucide="search" class="w-5 h-5 {{ $dark ? 'text-cyan-400/50' : 'text-slate-400' }}"></i>
    </div>
    
    <label for="{{ $name }}" class="sr-only">{{ $placeholder }}</label>
    <input
        type="search"
        name="{{ $name }}"
        id="{{ $name }}"
        value="{{ $value }}"
        placeholder="{{ $placeholder }}"
        class="w-full rounded-xl pl-11 pr-4 py-2.5 text-sm outline-none transition {{ $dark ? 'bg-slate-950/40 border border-cyan-400/20 text-white placeholder:text-cyan-100/40 focus:border-cyan-400/50 focus:ring-2 focus:ring-cyan-400/20' : 'bg-white border border-slate-200 text-slate-800 placeholder:text-slate-400 focus:outline-none focus:border-cyan-600 focus:ring-2 focus:ring-cyan-600/15' }}"
    />
</div>
