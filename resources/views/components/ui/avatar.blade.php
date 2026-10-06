@props([
    'src' => null,
    'name' => 'User',
    'size' => 'md', // xs, sm, md, lg, xl
    'status' => null, // online, offline, busy, away
])

@php
    $sizeClasses = match($size) {
        'xs' => 'w-6 h-6 text-xs',
        'sm' => 'w-8 h-8 text-sm',
        'md' => 'w-10 h-10 text-base',
        'lg' => 'w-12 h-12 text-lg',
        'xl' => 'w-16 h-16 text-2xl',
        default => 'w-10 h-10 text-base',
    };
    
    $statusColors = [
        'online' => 'bg-emerald-400',
        'offline' => 'bg-slate-400',
        'busy' => 'bg-rose-400',
        'away' => 'bg-amber-400',
    ];
    
    $initials = collect(explode(' ', $name))
        ->take(2)
        ->map(fn($word) => strtoupper(substr($word, 0, 1)))
        ->join('');
@endphp

<div {{ $attributes->merge(['class' => "relative inline-block flex-shrink-0"]) }}>
    @if($src)
        <img 
            src="{{ $src }}" 
            alt="{{ $name }}"
            class="{{ $sizeClasses }} rounded-full object-cover border-2 border-cyan-400/20"
        />
    @else
        <div class="{{ $sizeClasses }} rounded-full bg-gradient-to-br from-cyan-500 to-blue-600 flex items-center justify-center text-white font-semibold border-2 border-cyan-400/20">
            {{ $initials }}
        </div>
    @endif
    
    @if($status)
        @php
            $statusSize = match($size) {
                'xs' => 'w-1.5 h-1.5',
                'sm' => 'w-2 h-2',
                'md' => 'w-2.5 h-2.5',
                'lg' => 'w-3 h-3',
                'xl' => 'w-4 h-4',
                default => 'w-2.5 h-2.5',
            };
        @endphp
        <span class="absolute bottom-0 right-0 {{ $statusSize }} {{ $statusColors[$status] ?? 'bg-slate-400' }} rounded-full border-2 border-slate-950"></span>
    @endif
</div>
