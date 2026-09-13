@if ($paginator->hasPages())
    <nav class="flex items-center justify-between gap-3 text-sm" aria-label="Pagination">
        <p class="text-muted">
            Showing <b class="text-ink">{{ $paginator->firstItem() }}–{{ $paginator->lastItem() }}</b> of <b class="text-ink">{{ $paginator->total() }}</b>
        </p>
        <div class="flex items-center gap-1">
            @if ($paginator->onFirstPage())
                <span class="btn btn-secondary btn-sm opacity-50"><x-icon name="arrow-left" class="size-4" /></span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="btn btn-secondary btn-sm" aria-label="Previous page"><x-icon name="arrow-left" class="size-4" /></a>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="px-2 text-muted">…</span>
                @elseif (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="btn btn-primary btn-sm min-w-8" aria-current="page">{{ $page }}</span>
                        @elseif ($page === 1 || $page === $paginator->lastPage() || abs($page - $paginator->currentPage()) <= 1)
                            <a href="{{ $url }}" class="btn btn-secondary btn-sm min-w-8 hidden sm:inline-flex">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="btn btn-secondary btn-sm" aria-label="Next page"><x-icon name="arrow-right" class="size-4" /></a>
            @else
                <span class="btn btn-secondary btn-sm opacity-50"><x-icon name="arrow-right" class="size-4" /></span>
            @endif
        </div>
    </nav>
@endif
