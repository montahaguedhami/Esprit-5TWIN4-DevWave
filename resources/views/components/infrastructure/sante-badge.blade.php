@props(['sante'])

@php
    $score = is_array($sante) ? ($sante['score'] ?? 0) : (int) $sante;
    $niveau = is_array($sante) ? ($sante['niveau'] ?? 'bon') : ($score >= 70 ? 'bon' : ($score >= 40 ? 'surveiller' : 'critique'));

    $config = match ($niveau) {
        'bon' => [
            'bg' => 'bg-emerald-500/15',
            'text' => 'text-emerald-300',
            'ring' => 'ring-emerald-400/20',
            'bar' => 'bg-emerald-400',
            'label' => 'Bon état',
        ],
        'surveiller' => [
            'bg' => 'bg-amber-500/15',
            'text' => 'text-amber-300',
            'ring' => 'ring-amber-400/20',
            'bar' => 'bg-amber-400',
            'label' => 'À surveiller',
        ],
        default => [
            'bg' => 'bg-rose-500/15',
            'text' => 'text-rose-300',
            'ring' => 'ring-rose-400/20',
            'bar' => 'bg-rose-400',
            'label' => 'Critique',
        ],
    };
@endphp

<div {{ $attributes->class(['inline-flex items-center gap-1.5']) }}>
    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold ring-1 ring-inset {{ $config['bg'] }} {{ $config['text'] }} {{ $config['ring'] }}">
        {{ $score }}/100
    </span>
    <span class="text-xs {{ $config['text'] }} font-medium hidden sm:inline">{{ $config['label'] }}</span>
</div>
