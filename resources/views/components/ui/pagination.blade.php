@props([
    'currentPage' => 1,
    'totalPages' => 1,
    'perPage' => 10,
    'total' => 0,
])

@php
    $showFirst = $currentPage > 2;
    $showLast = $currentPage < $totalPages - 1;
    $pages = [];
    
    // Generate page numbers to display
    for ($i = max(1, $currentPage - 1); $i <= min($totalPages, $currentPage + 1); $i++) {
        $pages[] = $i;
    }
    
    $from = ($currentPage - 1) * $perPage + 1;
    $to = min($currentPage * $perPage, $total);
@endphp

@if($totalPages > 1)
<div {{ $attributes->merge(['class' => 'flex items-center justify-between']) }}>
    <!-- Info -->
    <div class="text-sm text-cyan-100/60">
        Affichage de <span class="font-semibold text-white">{{ $from }}</span> à 
        <span class="font-semibold text-white">{{ $to }}</span> sur 
        <span class="font-semibold text-white">{{ $total }}</span> résultats
    </div>
    
    <!-- Pagination -->
    <nav class="flex items-center gap-1">
        <!-- Previous -->
        <button 
            @if($currentPage <= 1) disabled @endif
            onclick="window.location.href='?page={{ $currentPage - 1 }}'"
            class="w-9 h-9 rounded-lg flex items-center justify-center transition-colors {{ $currentPage <= 1 ? 'opacity-30 cursor-not-allowed' : 'hover:bg-white/5' }}"
        >
            <i data-lucide="chevron-left" class="w-4 h-4 text-cyan-100/60"></i>
        </button>
        
        <!-- First page -->
        @if($showFirst)
        <button 
            onclick="window.location.href='?page=1'"
            class="w-9 h-9 rounded-lg flex items-center justify-center text-sm font-semibold text-cyan-100/60 hover:bg-white/5 transition-colors"
        >
            1
        </button>
        <span class="text-cyan-100/30">...</span>
        @endif
        
        <!-- Page numbers -->
        @foreach($pages as $page)
        <button 
            onclick="window.location.href='?page={{ $page }}'"
            class="w-9 h-9 rounded-lg flex items-center justify-center text-sm font-semibold transition-colors {{ $page === $currentPage ? 'bg-gradient-to-r from-cyan-500 to-blue-600 text-white' : 'text-cyan-100/60 hover:bg-white/5' }}"
        >
            {{ $page }}
        </button>
        @endforeach
        
        <!-- Last page -->
        @if($showLast)
        <span class="text-cyan-100/30">...</span>
        <button 
            onclick="window.location.href='?page={{ $totalPages }}'"
            class="w-9 h-9 rounded-lg flex items-center justify-center text-sm font-semibold text-cyan-100/60 hover:bg-white/5 transition-colors"
        >
            {{ $totalPages }}
        </button>
        @endif
        
        <!-- Next -->
        <button 
            @if($currentPage >= $totalPages) disabled @endif
            onclick="window.location.href='?page={{ $currentPage + 1 }}'"
            class="w-9 h-9 rounded-lg flex items-center justify-center transition-colors {{ $currentPage >= $totalPages ? 'opacity-30 cursor-not-allowed' : 'hover:bg-white/5' }}"
        >
            <i data-lucide="chevron-right" class="w-4 h-4 text-cyan-100/60"></i>
        </button>
    </nav>
</div>
@endif
