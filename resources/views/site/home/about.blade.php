<section class="{{ $tint ? 'bg-blush' : 'bg-white' }} py-16 sm:py-24">
    <div class="mx-auto grid max-w-6xl gap-12 px-5 sm:px-8 lg:grid-cols-[1fr_1.05fr] lg:items-center lg:gap-16">
        <div>
            <x-site.section-head :title="$section->title" :subtitle="$section->subtitle" />

            @if ($section->body)
                <div class="prose-mm mt-5 text-ink-soft">{!! nl2br(e($section->body)) !!}</div>
            @endif

            <dl class="mt-8 grid grid-cols-2 gap-x-6 gap-y-7 sm:grid-cols-4 lg:grid-cols-2 xl:grid-cols-4">
                @foreach ([
                    [setting('years_experience'), 'years since we opened'],
                    [setting('reviews_count'), 'parent reviews'],
                    [$classrooms->count(), 'classes by age'],
                    [setting('followers'), 'families on Facebook'],
                ] as [$value, $label])
                    @if ($value)
                        <div>
                            <dd class="font-display text-4xl font-semibold leading-none text-pink-600">{{ $value }}</dd>
                            <dt class="mt-1.5 text-sm font-bold leading-snug text-ink-soft">{{ $label }}</dt>
                        </div>
                    @endif
                @endforeach
            </dl>

            @if ($section->button_text)
                <a href="{{ url($section->button_url ?: route('classes.index')) }}" class="btn btn-outline mt-8"
                   data-track="cta_click" data-track-label="About – {{ $section->button_text }}">{{ $section->button_text }}</a>
            @endif
        </div>

        @if ($graduations->isNotEmpty())
            <div>
                <p class="font-display text-lg font-medium text-ink">Every June, another class graduates</p>
                <div class="mt-5 space-y-5">
                    @foreach ($graduations as $album)
                        <a href="{{ route('gallery.show', $album) }}" class="group block rounded-[1.75rem] border-2 border-line-soft bg-white p-3 transition-colors hover:border-pink-300"
                           data-track="cta_click" data-track-label="About – {{ $album->title }}">
                            <div class="grid grid-cols-4 gap-2">
                                @foreach ($album->photos->take(4) as $photo)
                                    <img src="{{ thumb_url($photo->path, 520) }}" alt="{{ $photo->alt }}" loading="lazy"
                                         class="aspect-square w-full rounded-2xl object-cover">
                                @endforeach
                            </div>
                            <p class="mt-3 flex items-center justify-between gap-3 px-1.5 pb-1">
                                <span class="font-bold">{{ $album->title }}</span>
                                <span class="text-sm font-bold text-pink-600 group-hover:underline">See the day</span>
                            </p>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</section>
