@php
    // Two blocks, each led by one large photo. On a phone the small photos beside it become a
    // swipeable row; from tablet up everything falls back into one mosaic grid.
    $groups = $galleryPhotos->chunk(6)->take(2);
@endphp
<section class="{{ $tint ? 'bg-blush' : 'bg-white' }} py-16 sm:py-24">
    <div class="mx-auto max-w-6xl px-5 sm:px-8">
        <x-site.section-head :title="$section->title" :subtitle="$section->subtitle" align="center" />

        @if ($galleryPhotos->isNotEmpty())
            {{-- Phones: a big photo, then the rest sliding under it --}}
            <div class="mt-10 space-y-8 sm:hidden">
                @foreach ($groups as $group)
                    @php $lead = $group->first(); $rest = $group->slice(1); @endphp
                    <div>
                        <a href="{{ route('gallery.show', $lead->album) }}" class="block"
                           data-track="cta_click" data-track-label="Gallery – {{ $lead->album->title }}">
                            <img src="{{ thumb_url($lead->photo->path, 520) }}" alt="{{ $lead->photo->alt }}" loading="lazy"
                                 class="aspect-[4/3] w-full rounded-[1.5rem] object-cover">
                            <p class="mt-2 px-0.5 font-bold">{{ $lead->photo->caption ?: $lead->album->title }}</p>
                        </a>

                        @if ($rest->isNotEmpty())
                            <ul class="-mx-5 mt-3 flex snap-x snap-mandatory gap-3 overflow-x-auto px-5 pb-2">
                                @foreach ($rest as $item)
                                    <li class="w-[38%] shrink-0 snap-start">
                                        <a href="{{ route('gallery.show', $item->album) }}" class="block"
                                           data-track="cta_click" data-track-label="Gallery – {{ $item->album->title }}">
                                            <img src="{{ thumb_url($item->photo->path, 520) }}" alt="{{ $item->photo->alt }}" loading="lazy"
                                                 class="aspect-square w-full rounded-[1.1rem] object-cover">
                                            <p class="mt-1.5 truncate text-[0.85rem] font-bold">{{ $item->album->title }}</p>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                @endforeach
            </div>

            {{-- Tablet and desktop: the mosaic --}}
            <ul class="mt-10 hidden grid-cols-3 gap-4 sm:grid lg:grid-cols-4">
                @foreach ($galleryPhotos as $item)
                    <li @class(['group', 'col-span-2 row-span-2' => $loop->first || $loop->index === 6])>
                        <a href="{{ route('gallery.show', $item->album) }}" class="block"
                           data-track="cta_click" data-track-label="Gallery – {{ $item->album->title }}">
                            <img src="{{ thumb_url($item->photo->path, 520) }}" alt="{{ $item->photo->alt }}" loading="lazy"
                                 class="aspect-square w-full rounded-[1.35rem] object-cover transition-transform duration-300 group-hover:-translate-y-1">
                            <p class="mt-2 px-0.5 text-[0.95rem] font-bold leading-snug">
                                {{ $item->photo->caption ?: $item->album->title }}
                            </p>
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif

        <p class="mt-10 text-center">
            <a href="{{ url($section->button_url ?: route('gallery.index')) }}" class="btn btn-primary px-7"
               data-track="cta_click" data-track-label="Gallery – {{ $section->button_text ?: 'Open the gallery' }}">
                {{ $section->button_text ?: 'Open the gallery' }}
                @if ($albumCount)
                    <span class="font-normal text-white/75">({{ $albumCount }} albums)</span>
                @endif
            </a>
        </p>
    </div>
</section>
