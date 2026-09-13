{{--
    Photo grid with lightbox.
    Params: $photos (Photo collection), $title (string), $color, $icon, $emptyTitle, $emptyText
--}}
@php
    $color = $color ?? '#E8177F';
    $icon = $icon ?? 'camera';
@endphp
@if ($photos->isNotEmpty())
    <div x-data="lightbox(@js($title))">
        <ul class="columns-2 gap-3 sm:gap-4 lg:columns-3">
            @foreach ($photos as $i => $photo)
                <li class="mb-3 break-inside-avoid sm:mb-4">
                    <button type="button" @click="show({{ $i }})" data-lightbox-item data-src="{{ $photo->url() }}" data-alt="{{ $photo->alt ?: $photo->caption ?: $title }}" data-caption="{{ $photo->caption }}"
                        class="group block w-full overflow-hidden rounded-[1.25rem] text-left">
                        <img src="{{ $photo->url() }}" alt="{{ $photo->alt ?: $photo->caption ?: $title }}" loading="lazy" decoding="async"
                            @if ($photo->width && $photo->height) width="{{ $photo->width }}" height="{{ $photo->height }}" @endif
                            class="h-auto w-full rounded-[1.25rem] bg-blush object-cover transition-transform duration-300 group-hover:scale-[1.02]">
                        <span class="sr-only">Open photo {{ $i + 1 }}</span>
                    </button>
                    @if ($photo->caption)
                        <p class="mt-1.5 px-1 text-sm text-ink-soft">{{ $photo->caption }}</p>
                    @endif
                </li>
            @endforeach
        </ul>
        @include('site.partials.lightbox-dialog')
    </div>
@else
    <div class="grid items-center gap-6 rounded-[2rem] border-2 border-dashed border-line p-5 sm:grid-cols-[1.2fr_1fr] sm:p-8">
        <div class="grid grid-cols-3 gap-2.5">
            <x-site.photo :color="$color" :icon="$icon" ratio="3/4" rounded="rounded-[1.1rem]" alt="" />
            <x-site.photo :color="$color" icon="camera" ratio="3/4" rounded="rounded-[1.1rem]" class="translate-y-4" alt="" />
            <x-site.photo :color="$color" icon="heart" ratio="3/4" rounded="rounded-[1.1rem]" alt="" />
        </div>
        <div>
            <p class="font-display text-xl font-semibold">{{ $emptyTitle ?? 'Photos are on their way' }}</p>
            <p class="mt-2 leading-relaxed text-ink-soft">{{ $emptyText ?? 'We’re choosing our favourite moments to share here. Follow us on Facebook for daily photos in the meantime.' }}</p>
            @if (setting('facebook_url'))
                <a href="{{ setting('facebook_url') }}" target="_blank" rel="noopener" class="link mt-3 inline-flex items-center gap-1.5"><x-icon name="facebook" class="size-4" /> See us on Facebook</a>
            @endif
        </div>
    </div>
@endif
