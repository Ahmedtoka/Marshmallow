@php
    // The colour of the fallback avatar comes from the name, so a parent always keeps the same one.
    $palette = ['#E8177F', '#2CBCC9', '#8479BD', '#7FA82A', '#E8A317', '#C0479A'];
    $count = setting('reviews_count') ?: $reviewsTotal;
@endphp
<section class="{{ $tint ? 'bg-blush' : 'bg-white' }} py-16 sm:py-24">
    <div class="mx-auto max-w-6xl px-5 sm:px-8">
        <div class="grid gap-8 lg:grid-cols-[auto_1fr] lg:items-center lg:gap-14">
            <div class="flex items-center gap-5">
                <p class="font-display text-[4.5rem] font-semibold leading-none text-pink-600 sm:text-[5.5rem]">{{ setting('recommend_percent', 96) }}%</p>
                <div>
                    <p class="font-display text-xl font-medium leading-tight">of parents<br>recommend us</p>
                    @if ($count)
                        <p class="mt-1.5 text-sm font-bold text-ink-soft">{{ $count }} reviews on Facebook</p>
                    @endif
                </div>
            </div>
            <div>
                <x-site.section-head :title="$section->title" :subtitle="$section->subtitle" />
                <div class="mt-5 flex flex-wrap gap-2.5">
                    <a href="{{ route('reviews') }}" class="btn btn-primary" data-track="cta_click" data-track-label="Reviews – Read all reviews">
                        Read all reviews
                    </a>
                    @if (setting('facebook_url'))
                        <a href="{{ setting('facebook_url') }}/reviews" target="_blank" rel="noopener"
                           class="btn btn-outline" data-track="cta_click" data-track-label="Reviews – Read on Facebook">
                            <x-icon name="facebook" class="size-4" /> See them on Facebook
                        </a>
                    @endif
                </div>
            </div>
        </div>

        @if ($reviews->isNotEmpty())
            <ul class="-mx-5 mt-10 flex snap-x snap-mandatory gap-4 overflow-x-auto px-5 pb-3 sm:mx-0 sm:block sm:columns-2 sm:gap-4 sm:space-y-4 sm:overflow-visible sm:px-0 sm:pb-0 lg:columns-3">
                @foreach ($reviews as $review)
                    @php
                        $initial = mb_strtoupper(mb_substr(trim($review->parent_name), 0, 1));
                        $color = $palette[crc32($review->parent_name) % count($palette)];
                    @endphp
                    <li class="w-[85%] shrink-0 snap-start break-inside-avoid rounded-[1.4rem] border-2 border-line-soft bg-white p-5 sm:w-auto">
                        <div class="flex items-start gap-3">
                            @if ($review->photo)
                                <img src="{{ thumb_url($review->photo, 160) }}" alt="" loading="lazy" class="size-11 shrink-0 rounded-full object-cover">
                            @else
                                <span class="grid size-11 shrink-0 place-items-center rounded-full font-display text-lg font-semibold text-white" style="background: {{ $color }}">{{ $initial }}</span>
                            @endif
                            <div class="min-w-0 flex-1">
                                <p class="font-bold leading-tight">{{ $review->parent_name }}</p>
                                <p class="mt-0.5 text-[0.8rem] leading-snug text-ink-muted">
                                    recommends {{ setting('site_name', 'Marshmallow Child Development Center') }}
                                    @if ($review->reviewed_at)
                                        · {{ $review->reviewed_at->format('j M Y') }}
                                    @endif
                                </p>
                            </div>
                            @if ($review->source === 'facebook')
                                <x-icon name="facebook" class="size-4 shrink-0 text-[#1877F2]" />
                            @endif
                        </div>

                        <blockquote class="mt-3 whitespace-pre-line leading-relaxed text-ink-soft">{{ \Illuminate\Support\Str::limit($review->quote, 320) }}</blockquote>

                        @if ($review->source_url)
                            <a href="{{ $review->source_url }}" target="_blank" rel="noopener"
                               class="mt-3 inline-block text-[0.8rem] font-bold text-ink-muted hover:text-pink-600" data-track-label="Review on Facebook">See it on Facebook</a>
                        @endif
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</section>
