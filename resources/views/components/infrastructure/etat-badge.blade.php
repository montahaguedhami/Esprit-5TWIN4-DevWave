@props(['etat'])

@php
    $etat = $etat instanceof \App\Enums\InfrastructureEtat
        ? $etat
        : \App\Enums\InfrastructureEtat::from($etat);

    $classes = match ($etat->color()) {
        'green' => 'bg-green-500/15 text-green-300 ring-green-400/20',
        'amber' => 'bg-amber-500/15 text-amber-300 ring-amber-400/20',
        'rose' => 'bg-rose-500/15 text-rose-300 ring-rose-400/20',
    };
@endphp

<span {{ $attributes->class(['inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium ring-1 ring-inset', $classes]) }}>
    {{ $etat->label() }}
</span>
