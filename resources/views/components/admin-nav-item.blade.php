@props(['route', 'icon', 'label', 'badge' => null, 'activeRoute' => null])

@php
    try {
        $isActive = request()->routeIs($activeRoute ?? $route);
    } catch (\Exception $e) {
        $isActive = false;
    }
@endphp

<a href="{{ route($route) }}"
   class="sidebar-link {{ $isActive ? 'is-active' : '' }}"
   aria-current="{{ $isActive ? 'page' : 'false' }}">
    <i data-lucide="{{ $icon }}"></i>
    <span style="flex:1;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $label }}</span>
    @if($badge)
    <span style="
        min-width:18px;height:18px;border-radius:999px;
        background:var(--danger);color:#fff;
        font-size:10px;font-weight:700;
        display:inline-flex;align-items:center;justify-content:center;
        padding:0 4px;flex-shrink:0;
    ">{{ $badge }}</span>
    @endif
</a>
