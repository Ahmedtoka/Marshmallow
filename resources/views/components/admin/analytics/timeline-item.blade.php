@props(['item', 'compact' => false])
{{-- One row of a visit timeline, built by App\Support\Analytics\Journey::timeline(). --}}
@php
    $highlight = $item['highlight'] ?? false;
    $isPage = $item['type'] === 'page';
    $muted = $item['muted'] ?? false;
    $key = $item['key'] ?? false;
@endphp
<li class="relative flex gap-3 pb-3 last:pb-0">
    <span @class([
        'relative z-10 grid place-items-center rounded-full shrink-0 ring-4 ring-white',
        'size-7' => ! $compact, 'size-6' => $compact,
        'bg-brand text-white' => $highlight,
        'bg-teal/15 text-[#1B8C96]' => ! $highlight && $isPage,
        'bg-lime/20 text-[#557316]' => ! $highlight && ! $isPage && $key,
        'bg-canvas text-muted' => ! $highlight && ! $isPage && ! $key,
    ])>
        <x-icon :name="$item['icon']" @class(['size-3.5' => ! $compact, 'size-3' => $compact]) />
    </span>
    <div @class(['min-w-0 flex-1', 'rounded-xl bg-brand-soft/70 px-3 py-2 -my-1' => $highlight])>
        <div class="flex flex-wrap items-baseline justify-between gap-x-3">
            <p @class(['min-w-0 font-bold', 'text-muted font-semibold' => $muted, 'text-brand-dark' => $highlight, 'text-[13px]' => $compact])>
                {{ $item['title'] }}
            </p>
            <time class="text-xs text-muted tabular-nums shrink-0" datetime="{{ $item['at']->toIso8601String() }}">{{ $item['at']->format('g:i:s a') }}</time>
        </div>
        @if (! empty($item['subtitle']))
            <p @class(['text-xs text-muted break-words', 'line-clamp-2' => $compact])>{{ $item['subtitle'] }}</p>
        @endif
        @if ($isPage)
            <div class="mt-1 flex items-center gap-3 text-xs text-muted tabular-nums">
                <span title="Visible time on page"><x-icon name="clock" class="size-3 inline -mt-0.5" /> {{ \App\Support\Analytics\Format::humanDuration($item['duration']) }}</span>
                <span class="flex items-center gap-1.5" title="Scrolled {{ $item['scroll'] }}% of the page">
                    <span class="w-16 h-1.5 rounded-full bg-canvas overflow-hidden"><span class="block h-full rounded-full bg-teal" style="width: {{ $item['scroll'] }}%"></span></span>
                    {{ $item['scroll'] }}% scrolled
                </span>
            </div>
        @endif
    </div>
</li>
