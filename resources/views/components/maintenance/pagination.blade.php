{{-- Pagination basée sur le paginator Laravel (conserve les filtres grâce à withQueryString) --}}
@props(['paginator'])

@if($paginator->hasPages())
<div class="flex flex-col sm:flex-row items-center justify-between gap-3 px-5 py-4 border-t border-white/5">
    <p class="text-sm text-cyan-100/60">
        Affichage de <span class="font-semibold text-white">{{ $paginator->firstItem() }}</span>
        à <span class="font-semibold text-white">{{ $paginator->lastItem() }}</span>
        sur <span class="font-semibold text-white">{{ $paginator->total() }}</span> résultats
    </p>

    <nav class="flex items-center gap-1" aria-label="Pagination">
        @if($paginator->onFirstPage())
            <span class="w-9 h-9 rounded-lg flex items-center justify-center opacity-30"><i data-lucide="chevron-left" class="w-4 h-4 text-cyan-100/60"></i></span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" class="w-9 h-9 rounded-lg flex items-center justify-center hover:bg-white/5" aria-label="Page précédente"><i data-lucide="chevron-left" class="w-4 h-4 text-cyan-100/60"></i></a>
        @endif

        @foreach($paginator->getUrlRange(1, $paginator->lastPage()) as $page => $url)
            <a href="{{ $url }}"
               class="w-9 h-9 rounded-lg flex items-center justify-center text-sm font-semibold transition-colors {{ $page === $paginator->currentPage() ? 'bg-gradient-to-r from-cyan-500 to-blue-600 text-white' : 'text-cyan-100/60 hover:bg-white/5' }}"
               @if($page === $paginator->currentPage()) aria-current="page" @endif>{{ $page }}</a>
        @endforeach

        @if($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" class="w-9 h-9 rounded-lg flex items-center justify-center hover:bg-white/5" aria-label="Page suivante"><i data-lucide="chevron-right" class="w-4 h-4 text-cyan-100/60"></i></a>
        @else
            <span class="w-9 h-9 rounded-lg flex items-center justify-center opacity-30"><i data-lucide="chevron-right" class="w-4 h-4 text-cyan-100/60"></i></span>
        @endif
    </nav>
</div>
@endif
