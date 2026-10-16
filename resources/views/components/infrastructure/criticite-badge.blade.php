@props(['criticite'])

@php
    $criticite = $criticite instanceof \App\Enums\NiveauCriticite
        ? $criticite
        : ($criticite ? \App\Enums\NiveauCriticite::tryFrom($criticite) : null);

    $classes = match ($criticite?->color()) {
        'green' => 'bg-emerald-500/15 text-emerald-300 ring-emerald-400/20',
        'amber' => 'bg-amber-500/15 text-amber-300 ring-amber-400/20',
        'rose' => 'bg-rose-500/15 text-rose-300 ring-rose-400/20',
        default => 'bg-slate-500/15 text-slate-300 ring-slate-400/20',
    };
@endphp

<span {{ $attributes->class(['inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium ring-1 ring-inset', $classes]) }}>
    {{ $criticite?->label() ?? 'Non défini' }}
</span>
