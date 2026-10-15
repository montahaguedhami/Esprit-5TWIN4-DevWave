@props(['statut'])

@php
    $statut = $statut instanceof \App\Enums\ZoneStatut
        ? $statut
        : ($statut ? \App\Enums\ZoneStatut::tryFrom($statut) : null);

    $classes = match ($statut?->color()) {
        'green' => 'bg-emerald-500/15 text-emerald-300 ring-emerald-400/20',
        default => 'bg-slate-500/15 text-slate-300 ring-slate-400/20',
    };
@endphp

<span {{ $attributes->class(['inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium ring-1 ring-inset', $classes]) }}>
    <span class="mr-1.5 h-1.5 w-1.5 rounded-full {{ $statut === \App\Enums\ZoneStatut::Active ? 'bg-emerald-400' : 'bg-slate-400' }}"></span>
    {{ $statut?->label() ?? 'Non défini' }}
</span>
