@props(['title', 'intro' => null, 'color' => '#E8177F', 'back' => null, 'backLabel' => null])
<header {{ $attributes->merge(['class' => 'relative overflow-hidden bg-blush']) }}>
    <span aria-hidden="true" class="absolute -right-10 -top-12 size-44 rounded-full opacity-25 sm:size-64" style="background: {{ $color }};"></span>
    <span aria-hidden="true" class="absolute right-24 top-24 hidden size-5 rounded-full bg-teal sm:block"></span>
    <span aria-hidden="true" class="absolute bottom-6 right-1/3 size-3 rounded-full bg-grape"></span>
    <div class="relative mx-auto max-w-6xl px-5 pb-10 pt-8 sm:px-8 sm:pb-14 sm:pt-12">
        @if ($back)
            <a href="{{ $back }}" class="mb-4 inline-flex items-center gap-1.5 text-sm font-bold text-ink-soft hover:text-pink-600">
                <x-icon name="arrow-left" class="size-4" /> {{ $backLabel }}
            </a>
        @endif
        <div class="grid items-end gap-6 lg:grid-cols-[1fr_auto]">
            <div class="max-w-2xl">
                <h1 class="font-display text-[2.1rem] font-semibold leading-[1.08] sm:text-5xl">{{ $title }}</h1>
                @if ($intro)
                    <p class="mt-4 text-[1.05rem] leading-relaxed text-ink-soft sm:text-lg">{{ $intro }}</p>
                @endif
                {{ $slot }}
            </div>
            @isset($aside)
                <div>{{ $aside }}</div>
            @endisset
        </div>
    </div>
</header>
