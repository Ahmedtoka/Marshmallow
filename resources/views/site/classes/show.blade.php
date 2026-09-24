@extends('layouts.site')

@php
    $c = $classroom->color ?: '#E8177F';
    $deep = "color-mix(in srgb, {$c} 72%, #33307A)";
@endphp

@section('title', $classroom->seo_title ?: $classroom->name.' class, '.$classroom->ageRangeLabel().' | Marshmallow Nursery')
@section('description', $classroom->seo_description ?: \Illuminate\Support\Str::limit((string) $classroom->summary, 160))
@if ($classroom->cover_image)
    @section('og_image', media_url($classroom->cover_image))
@endif

@section('content')
    {{-- Header in the class color --}}
    <header class="relative overflow-hidden" style="background: color-mix(in srgb, {{ $c }} 11%, #fff);">
        <span aria-hidden="true" class="absolute -right-16 -top-20 size-72 rounded-full" style="background: color-mix(in srgb, {{ $c }} 18%, #fff);"></span>
        <span aria-hidden="true" class="absolute bottom-8 left-1/2 size-4 rounded-full bg-sun"></span>
        <div class="relative mx-auto max-w-6xl px-5 pb-10 pt-6 sm:px-8 sm:pb-14 sm:pt-10">
            <a href="{{ route('classes.index') }}" class="inline-flex items-center gap-1.5 text-sm font-bold text-ink-soft hover:text-pink-600"><x-icon name="arrow-left" class="size-4" /> All classes</a>
            <div class="mt-5 grid items-center gap-6 md:grid-cols-[auto_1fr] md:gap-10">
                <span class="grid size-28 place-items-center rounded-[2.25rem] bg-white sm:size-36" style="box-shadow: 0 0 0 3px {{ $c }};">
                    <x-site.candy :name="$classroom->icon" :color="$c" class="size-20 sm:size-28" :title="$classroom->name" />
                </span>
                <div>
                    <p class="inline-block rounded-full bg-white px-3.5 py-1 font-bold">{{ $classroom->ageRangeLabel() }}</p>
                    <h1 class="mt-3 font-display text-[2.6rem] font-semibold leading-none sm:text-6xl" style="color: {{ $deep }};">{{ $classroom->name }}</h1>
                    @if ($classroom->tagline)
                        <p class="mt-3 font-display text-xl text-ink sm:text-2xl">{{ $classroom->tagline }}</p>
                    @endif
                    <div class="mt-6 flex flex-wrap items-center gap-3">
                        <a href="{{ route('enroll', ['class' => $classroom->slug]) }}" class="btn btn-primary" data-track="cta_click" data-track-label="{{ $classroom->name }} header – Book a visit">Book a visit</a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    {{-- Summary + goals --}}
    <section class="bg-white py-14 sm:py-20" data-track-section="class_overview">
        <div class="mx-auto grid max-w-6xl gap-10 px-5 sm:px-8 lg:grid-cols-[1.15fr_1fr] lg:gap-16">
            <div>
                <h2 class="font-display text-[1.85rem] font-semibold leading-tight sm:text-4xl">A day in {{ $classroom->name }}</h2>
                @if ($classroom->description && $classroom->description !== $classroom->summary)
                    <div class="prose-mm mt-4 text-lg text-ink-soft">{!! nl2br(e($classroom->description)) !!}</div>
                @elseif ($classroom->summary)
                    <p class="prose-mm mt-4 text-lg text-ink-soft">{{ $classroom->summary }}</p>
                @endif
            </div>
            @if (! empty($classroom->goals))
                <div class="rounded-[2rem] border-2 p-6 sm:p-8" style="border-color: color-mix(in srgb, {{ $c }} 35%, #fff);">
                    <h2 class="font-display text-2xl font-semibold">By the end of the year</h2>
                    <ul class="mt-4 space-y-3">
                        @foreach ($classroom->goals as $goal)
                            <li class="flex gap-3">
                                <span class="mt-0.5 grid size-6 shrink-0 place-items-center rounded-full text-white" style="background: {{ $c }};"><x-icon name="check" class="size-3.5" stroke="3" /></span>
                                <span class="leading-relaxed">{{ $goal }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </section>

    {{-- What the class learns: the name, its icon and how often it happens --}}
    @if ($items->isNotEmpty())
        <section class="bg-white py-14 sm:py-20" data-track-section="class_activities">
            <div class="mx-auto max-w-6xl px-5 sm:px-8">
                <x-site.section-head :title="'What '.$classroom->name.' learns'"
                    subtitle="Everything in this class’s week, and how often it happens." />

                <ul class="mt-9 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($items as $item)
                        @php $activity = $item->activity; $ac = $activity->color ?: $c; @endphp
                        <li>
                            <a href="{{ route('activities.show', $activity) }}"
                               class="flex h-full items-center gap-3.5 rounded-[1.35rem] border-2 border-line-soft bg-white p-4 transition-colors hover:border-current"
                               style="color: {{ $ac }}"
                               data-track="cta_click" data-track-label="{{ $classroom->name }} – {{ $activity->name }}">
                                <span class="grid size-12 shrink-0 place-items-center rounded-2xl" style="background: color-mix(in srgb, {{ $ac }} 14%, #fff);">
                                    <x-icon :name="$activity->icon" class="size-6" />
                                </span>
                                <span class="min-w-0 text-ink">
                                    <span class="block font-display text-[1.08rem] font-medium leading-snug">{{ $activity->name }}</span>
                                    @if ($item->frequency)
                                        <span class="mt-0.5 flex items-center gap-1.5 text-sm font-bold text-ink-soft">
                                            <x-icon name="clock" class="size-3.5" /> {{ $item->frequency }}
                                        </span>
                                    @endif
                                </span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </section>
    @endif

    {{-- One slider with every photo of this class, whatever the activity --}}
    @if ($gallery->isNotEmpty())
        <section class="bg-blush py-14 sm:py-20" data-track-section="class_photos"
                 x-data="lightbox(@js($classroom->name.' class'))" @keydown.window="keydown($event)">
            <div class="mx-auto max-w-6xl px-5 sm:px-8">
                <div class="flex flex-wrap items-end justify-between gap-4">
                    <x-site.section-head :title="$classroom->name.' in photos'"
                        :subtitle="'A real week in '.$classroom->name.' — tap any photo to see it big.'" />

                    <div class="hidden gap-2 sm:flex">
                        <button type="button" @click="$refs.track.scrollBy({ left: -$refs.track.clientWidth * 0.8, behavior: 'smooth' })"
                                class="grid size-11 place-items-center rounded-full border-2 border-ink/15 bg-white text-ink hover:border-pink-300" aria-label="Previous photos">
                            <x-icon name="arrow-left" class="size-5" />
                        </button>
                        <button type="button" @click="$refs.track.scrollBy({ left: $refs.track.clientWidth * 0.8, behavior: 'smooth' })"
                                class="grid size-11 place-items-center rounded-full border-2 border-ink/15 bg-white text-ink hover:border-pink-300" aria-label="More photos">
                            <x-icon name="arrow-right" class="size-5" />
                        </button>
                    </div>
                </div>

                <div x-ref="track" class="-mx-5 mt-8 flex snap-x snap-mandatory gap-4 overflow-x-auto px-5 pb-3 sm:mx-0 sm:px-0">
                    @foreach ($gallery as $i => $item)
                        <button type="button" @click="show({{ $i }})"
                                data-lightbox-item data-src="{{ $item->photo->url() }}"
                                data-alt="{{ $item->photo->alt ?: $item->label }}" data-caption="{{ $item->photo->caption ?: $item->label }}"
                                class="group w-[78%] shrink-0 snap-center text-left sm:w-[46%] lg:w-[31.5%]">
                            <img src="{{ thumb_url($item->photo->path, 520) }}" alt="{{ $item->photo->alt ?: $item->label }}" loading="lazy"
                                 class="aspect-[4/3] w-full rounded-[1.5rem] object-cover transition-transform duration-300 group-hover:-translate-y-1">
                            <span class="mt-2.5 flex items-center gap-2 px-0.5 font-bold">
                                <span class="size-2.5 shrink-0 rounded-full" style="background: {{ $c }}"></span>
                                {{ $item->label }}
                            </span>
                        </button>
                    @endforeach
                </div>

                <p class="mt-2 text-sm text-ink-soft sm:hidden">Swipe to see more →</p>
            </div>

            @include('site.partials.lightbox-dialog')
        </section>
    @endif

    {{-- Prev / next --}}
    @if ($prev || $next)
        <nav class="bg-white py-10" aria-label="Other classes">
            <div class="mx-auto grid max-w-6xl gap-3 px-5 sm:grid-cols-2 sm:px-8">
                @foreach ([['prev', $prev, 'Younger class'], ['next', $next, 'Older class']] as [$dir, $other, $label])
                    @if ($other)
                        <a href="{{ route('classes.show', $other) }}" @class(['group flex items-center gap-4 rounded-[1.5rem] border-2 border-line p-4 hover:border-pink-200', 'sm:col-start-2 sm:flex-row-reverse sm:text-right' => $dir === 'next'])>
                            <x-site.candy :name="$other->icon" :color="$other->color" class="size-12" />
                            <span>
                                <span class="block text-sm font-bold text-ink-soft">{{ $label }}</span>
                                <span class="block font-display text-xl font-semibold group-hover:underline" style="color: color-mix(in srgb, {{ $other->color }} 72%, #33307A);">{{ $other->name }}</span>
                                <span class="block text-sm text-ink-soft">{{ $other->ageRangeLabel() }}</span>
                            </span>
                        </a>
                    @endif
                @endforeach
            </div>
        </nav>
    @endif

    {{-- Book a visit --}}
    <section class="bg-white px-4 pb-14 sm:px-8 sm:pb-20" id="book" data-track-section="class_enroll">
        <div class="mx-auto grid max-w-6xl gap-8 rounded-[2.25rem] p-6 sm:p-10 lg:grid-cols-[0.8fr_1.2fr] lg:gap-14" style="background: color-mix(in srgb, {{ $c }} 9%, #fff);">
            <div>
                <x-site.candy :name="$classroom->icon" :color="$c" class="size-14" />
                <h2 class="mt-3 font-display text-[1.9rem] font-semibold leading-tight sm:text-4xl">Come and meet {{ $classroom->name }}</h2>
                <p class="mt-3 leading-relaxed text-ink-soft">Leave your number and our admissions team will call you within one working day to book your visit.</p>
                @if (setting('tour_hours'))
                    <p class="mt-4 flex gap-2 text-sm font-bold"><x-icon name="camera" class="size-4" style="color: {{ $c }}" /> {{ setting('tour_hours') }}</p>
                @endif
            </div>
            <div class="rounded-[1.75rem] bg-white p-5 sm:p-7">
                @include('site.partials.compact-enroll', ['branches' => $branches, 'place' => $classroom->name.' page', 'classroom' => $classroom])
            </div>
        </div>
    </section>
@endsection
