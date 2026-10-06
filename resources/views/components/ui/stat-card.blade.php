@props([
    'icon' => 'activity',
    'value' => '0',
    'label' => '',
    'color' => 'cyan',
    'trend' => null, // 'up', 'down', null
    'trendValue' => null,
])

<x-ui.card hover class="group">
    <div class="flex items-start justify-between mb-3">
        <div class="w-12 h-12 rounded-xl bg-{{ $color }}-500/10 flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
            <i data-lucide="{{ $icon }}" class="w-6 h-6 text-{{ $color }}-400"></i>
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
    <div class="text-2xl font-display font-bold text-white mb-1">{{ $value }}</div>
    <div class="text-xs text-cyan-100/60">{{ $label }}</div>
    @if($slot->isNotEmpty())
    <div class="mt-3 pt-3 border-t border-white/5">
        {{ $slot }}
    </div>
    @endif
</x-ui.card>
