<div class="relative mx-auto max-w-6xl overflow-hidden rounded-[2.25rem] bg-pink px-6 py-10 text-white sm:px-12 sm:py-14">
    <span aria-hidden="true" class="absolute -right-10 -top-14 size-48 rounded-full bg-sun"></span>
    <span aria-hidden="true" class="absolute right-40 top-8 hidden size-6 rounded-full bg-teal sm:block"></span>
    <span aria-hidden="true" class="absolute -bottom-6 right-1/4 size-16 rounded-full bg-white/15"></span>
    <div class="relative grid items-center gap-7 md:grid-cols-[1.4fr_auto]">
        <div class="max-w-2xl">
            <h2 class="font-display text-[1.9rem] font-semibold leading-[1.1] sm:text-[2.6rem]">{{ $title }}</h2>
            @if (! empty($subtitle))
                <p class="mt-3 text-lg leading-relaxed text-white/90">{{ $subtitle }}</p>
            @endif
        </div>
        <div class="flex flex-wrap gap-3 md:flex-col md:items-stretch">
            <a href="{{ $buttonUrl }}" class="btn btn-white px-7 text-lg" data-track="cta_click" data-track-label="{{ $place }} – {{ $buttonText }}">{{ $buttonText }}</a>
            @if (setting('main_phone'))
                <a href="{{ tel_link(setting('main_phone')) }}" class="btn border-2 border-white/60 text-white hover:bg-white/10" data-track-label="Call main number – {{ $place }}">
                    <x-icon name="phone" class="size-4" /> {{ setting('main_phone') }}
                </a>
            @endif
        </div>
    </div>
</div>
