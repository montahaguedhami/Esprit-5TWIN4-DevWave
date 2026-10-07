{{-- En-tête commun aux pages du module Maintenance --}}
@props([
    'title',
    'subtitle' => null,
    'icon' => 'wrench',
])

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div class="flex items-center gap-4">
        <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-cyan-400/20 to-blue-600/20 border border-cyan-400/20 flex items-center justify-center shrink-0">
            <i data-lucide="{{ $icon }}" class="w-6 h-6 text-cyan-300"></i>
        </div>
        <div>
            <p class="text-[11px] font-semibold uppercase tracking-wider text-cyan-300/70">Maintenance</p>
            <h1 class="text-2xl sm:text-3xl font-display font-bold text-white tracking-tight">{{ $title }}</h1>
            @if($subtitle)
                <p class="text-cyan-100/60 text-sm mt-1">{{ $subtitle }}</p>
            @endif
        </div>
    </div>

    @if($slot->isNotEmpty())
        <div class="flex flex-wrap items-center gap-2">
            {{ $slot }}
        </div>
    @endif
</div>
