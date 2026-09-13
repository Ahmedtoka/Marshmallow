@props(['title', 'subtitle' => null, 'back' => null])
<div class="flex flex-wrap items-end justify-between gap-4 mb-6">
    <div class="min-w-0">
        @if ($back)
            <a href="{{ $back }}" class="inline-flex items-center gap-1 text-sm font-bold text-muted hover:text-ink mb-2"><x-icon name="arrow-left" class="size-4" /> Back</a>
        @endif
        <h1 class="page-title">{{ $title }}</h1>
        @if ($subtitle)
            <p class="page-subtitle">{{ $subtitle }}</p>
        @endif
    </div>
    @if (isset($actions) && $actions->isNotEmpty())
        <div class="flex flex-wrap items-center gap-2">{{ $actions }}</div>
    @endif
</div>
