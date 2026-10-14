@props([
    'icon' => 'inbox',
    'title' => 'Aucune donnée',
    'description' => '',
    'action' => null,
    'actionLabel' => null,
])

<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center py-12 px-4 text-center']) }}>
    <div class="w-20 h-20 rounded-2xl bg-cyan-500/5 border border-cyan-400/10 flex items-center justify-center mb-5">
        <i data-lucide="{{ $icon }}" class="w-10 h-10 text-cyan-400/40"></i>
    </div>
    
    <h3 class="text-lg font-display font-semibold text-white mb-2">{{ $title }}</h3>
    
    @if($description)
    <p class="text-sm text-cyan-100/50 max-w-md mb-6">{{ $description }}</p>
    @endif
    
    @if($slot->isNotEmpty())
    <div class="mt-4">
        {{ $slot }}
    </div>
    @elseif($action && $actionLabel)
    <button 
        onclick="{{ $action }}"
        class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 text-white text-sm font-semibold rounded-xl transition-all duration-300 shadow-lg shadow-cyan-500/25"
    >
        <i data-lucide="plus" class="w-4 h-4"></i>
        <span>{{ $actionLabel }}</span>
    </button>
    @endif
</div>
