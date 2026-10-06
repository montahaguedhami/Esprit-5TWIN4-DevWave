@props(['route', 'icon', 'label', 'badge' => null])

@php
    try {
        $isActive = request()->routeIs($route) || str_starts_with(request()->path(), str_replace('.', '/', $route));
    } catch (\Exception $e) {
        $isActive = false;
    }
@endphp

<a href="{{ route($route) }}"
   class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 group
          {{ $isActive
             ? 'bg-cyan-500/10 text-cyan-300 border border-cyan-400/20'
             : 'text-cyan-100/55 hover:text-cyan-100/90 hover:bg-white/[.04]' }}"
   aria-current="{{ $isActive ? 'page' : 'false' }}">

    <i data-lucide="{{ $icon }}"
       class="w-4 h-4 shrink-0 transition-colors
              {{ $isActive ? 'text-cyan-400' : 'text-cyan-100/40 group-hover:text-cyan-300' }}"></i>

    <span class="flex-1 truncate">{{ $label }}</span>

    @if($badge)
    <span class="ml-auto px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-red-500/20 text-red-300">
        {{ $badge }}
    </span>
    @endif
</a>
