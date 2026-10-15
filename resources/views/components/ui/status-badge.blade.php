@props([
    'status' => 'normal', // normal, alert, critical, success, warning, danger, info, pending
    'size' => 'md', // sm, md, lg
    'dot' => false,
])

@php
    $statusConfig = [
        'normal' => ['bg' => 'bg-emerald-500/10', 'border' => 'border-emerald-400/30', 'text' => 'text-emerald-800', 'label' => 'Normal'],
        'alert' => ['bg' => 'bg-amber-500/10', 'border' => 'border-amber-400/30', 'text' => 'text-amber-800', 'label' => 'Alerte'],
        'critical' => ['bg' => 'bg-rose-500/10', 'border' => 'border-rose-400/30', 'text' => 'text-rose-800', 'label' => 'Critique'],
        'success' => ['bg' => 'bg-teal-500/10', 'border' => 'border-teal-400/30', 'text' => 'text-teal-800', 'label' => 'Succès'],
        'warning' => ['bg' => 'bg-orange-500/10', 'border' => 'border-orange-400/30', 'text' => 'text-orange-800', 'label' => 'Attention'],
        'danger' => ['bg' => 'bg-red-500/10', 'border' => 'border-red-400/30', 'text' => 'text-red-800', 'label' => 'Danger'],
        'info' => ['bg' => 'bg-cyan-500/10', 'border' => 'border-cyan-400/30', 'text' => 'text-cyan-800', 'label' => 'Info'],
        'pending' => ['bg' => 'bg-slate-500/10', 'border' => 'border-slate-400/30', 'text' => 'text-slate-700', 'label' => 'En attente'],
    ];

    $config = $statusConfig[$status] ?? $statusConfig['info'];

    $sizeClasses = match ($size) {
        'sm' => 'text-xs px-2 py-0.5',
        'md' => 'text-xs px-3 py-1',
        'lg' => 'text-sm px-4 py-1.5',
        default => 'text-xs px-3 py-1',
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1.5 rounded-full font-semibold border {$config['bg']} {$config['border']} {$config['text']} {$sizeClasses}"]) }}>
    @if($dot)
        <span class="w-1.5 h-1.5 rounded-full {{ str_replace('/10', '', $config['bg']) }} animate-pulse"></span>
    @endif
    {{ $slot->isNotEmpty() ? $slot : $config['label'] }}
</span>