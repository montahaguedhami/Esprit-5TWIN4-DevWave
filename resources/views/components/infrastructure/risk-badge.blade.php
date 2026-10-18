@props(['level', 'probability' => null])
@php
    $classes = match ($level) {
        'Critique' => 'border-rose-300 bg-rose-50 text-rose-800 dark:border-rose-500/30 dark:bg-rose-500/15 dark:text-rose-200',
        'Élevé' => 'border-orange-300 bg-orange-50 text-orange-800 dark:border-orange-500/30 dark:bg-orange-500/15 dark:text-orange-200',
        'Moyen' => 'border-amber-300 bg-amber-50 text-amber-800 dark:border-amber-500/30 dark:bg-amber-500/15 dark:text-amber-200',
        default => 'border-emerald-300 bg-emerald-50 text-emerald-800 dark:border-emerald-500/30 dark:bg-emerald-500/15 dark:text-emerald-200',
    };
@endphp
<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1 rounded-full border px-2.5 py-1 text-xs font-semibold {$classes}"]) }}>
    {{ $level }}@if($probability !== null) <span class="opacity-75">{{ $probability }}%</span>@endif
</span>
