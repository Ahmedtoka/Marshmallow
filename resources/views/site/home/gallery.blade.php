<section class="{{ $tint ? 'bg-blush' : 'bg-white' }} py-16 sm:py-24">
    <div class="mx-auto max-w-6xl px-5 sm:px-8">
        <x-site.section-head :title="$section->title" :subtitle="$section->subtitle" align="center" />

        @if ($galleryPhotos->isNotEmpty())
            <ul class="mt-10 grid grid-cols-2 gap-3 sm:grid-cols-3 sm:gap-4 lg:grid-cols-4">
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
