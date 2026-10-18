@props([
    'icon' => 'activity',
    'title' => '',
    'value' => '0',
    'color' => 'cyan',
    'trend' => null,
    'trendValue' => null,
    'subtitle' => null,
    'loading' => false,
])

<x-ui.card :hover="true" class="group">
    @if($loading)
        <x-ui.loading-skeleton type="stat" />
    @else
    <div class="flex items-start justify-between mb-4">
        <div>
            <div class="flex items-center gap-2 mb-2">
                <div class="w-10 h-10 rounded-xl bg-{{ $color }}-500/10 flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                    <i data-lucide="{{ $icon }}" class="w-5 h-5 text-{{ $color }}-400"></i>
                </div>
                @if($trend)
                <div class="flex items-center gap-1 text-xs font-semibold {{ $trend === 'up' ? 'text-emerald-400' : 'text-rose-400' }}">
                    <i data-lucide="{{ $trend === 'up' ? 'trending-up' : 'trending-down' }}" class="w-3.5 h-3.5"></i>
                    @if($trendValue)
                    <span>{{ $trendValue }}</span>
                    @endif
                </div>
                @endif
            </div>
            <p class="text-xs font-medium text-cyan-100/60 mb-1">{{ $title }}</p>
            <p class="text-2xl font-display font-bold text-white">{{ $value }}</p>
            @if($subtitle)
            <p class="text-xs text-cyan-100/50 mt-1">{{ $subtitle }}</p>
            @endif
        </div>
    </div>
    
    @if($slot->isNotEmpty())
    <div class="pt-3 border-t border-white/5">
        {{ $slot }}
    </div>
    @endif
    @endif
</x-ui.card>
