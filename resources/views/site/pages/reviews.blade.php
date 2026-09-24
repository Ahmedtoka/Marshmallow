@extends('layouts.site')

@php
    $palette = ['#E8177F', '#2CBCC9', '#8479BD', '#7FA82A', '#E8A317', '#C0479A'];
    $count = setting('reviews_count') ?: $reviews->count();
    $facebook = setting('facebook_url');
@endphp

@section('content')
    <section class="bg-blush pb-12 pt-10 sm:pb-16 sm:pt-14">
        <div class="mx-auto max-w-6xl px-5 sm:px-8">
            <div class="grid gap-8 lg:grid-cols-[auto_1fr] lg:items-center lg:gap-14">
                <div class="flex items-center gap-5">
                    <p class="font-display text-[4.5rem] font-semibold leading-none text-pink-600 sm:text-[6rem]">{{ setting('recommend_percent', 96) }}%</p>
                    <div>
                        <p class="font-display text-xl font-medium leading-tight">of parents<br>recommend us</p>
                        @if ($count)
                            <p class="mt-1.5 text-sm font-bold text-ink-soft">{{ $count }} reviews on Facebook</p>
                        @endif
                    </div>
                </div>
                <div>
                    <h1 class="font-display text-[2rem] font-semibold leading-[1.1] sm:text-[2.7rem]">What parents say about us</h1>
                    <p class="mt-3 max-w-xl text-lg leading-relaxed text-ink-soft">
                        Every review below was written by a Marshmallow family on our Facebook page — we have not edited a word.
                    </p>
                    <div class="mt-5 flex flex-wrap gap-2.5">
                        <a href="{{ route('enroll') }}" class="btn btn-primary" data-track="cta_click" data-track-label="Reviews page – Book a visit">Book a visit</a>
                        @if ($facebook)
                            <a href="{{ $facebook }}/reviews" target="_blank" rel="noopener" class="btn btn-outline" data-track="cta_click" data-track-label="Reviews page – Facebook">
                                <x-icon name="facebook" class="size-4" /> See them on Facebook
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="bg-white py-12 sm:py-16" data-track-section="all_reviews">
        <div class="mx-auto max-w-6xl px-5 sm:px-8">
            @if ($reviews->isEmpty())
                <p class="py-12 text-center text-lg text-ink-soft">The first reviews are on their way.</p>
            @else
                <ul class="gap-5 space-y-5 sm:columns-2 lg:columns-3">
                    @foreach ($reviews as $review)
                        @php
                            $initial = mb_strtoupper(mb_substr(trim($review->parent_name), 0, 1));
                            $color = $palette[crc32($review->parent_name) % count($palette)];
                        @endphp
                        <li class="break-inside-avoid rounded-[1.4rem] border-2 border-line-soft bg-white p-5">
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

                            <blockquote class="mt-3 whitespace-pre-line leading-relaxed text-ink-soft">{{ $review->quote }}</blockquote>

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

    <section class="bg-blush py-12 sm:py-16">
        @include('site.partials.enroll-band', [
            'title' => 'The next review could be yours',
            'subtitle' => 'Come and see a Marshmallow morning for yourself, then tell us what you think.',
            'buttonText' => 'Book a visit',
            'buttonUrl' => route('enroll'),
            'place' => 'Reviews page',
        ])
    </section>
@endsection
