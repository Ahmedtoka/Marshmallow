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
                        @if ($classroom->teacher_ratio)
                            <span class="inline-flex items-center gap-2 rounded-full border-2 bg-white px-3.5 py-2 text-sm font-bold" style="border-color: color-mix(in srgb, {{ $c }} 35%, #fff);">
                                <x-icon name="teacher" class="size-4" style="color: {{ $c }}" /> {{ $classroom->teacher_ratio }}
                            </span>
                        @endif
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

    {{-- Daily routine timeline --}}
    @if (! empty($classroom->daily_routine))
        <section class="bg-blush py-14 sm:py-20" data-track-section="class_routine">
            <div class="mx-auto max-w-6xl px-5 sm:px-8">
                <x-site.section-head title="Daily routine" subtitle="Every day has a gentle rhythm, so children always know what comes next." />
                <ol class="mt-8 gap-x-10 md:columns-2">
                    @foreach ($classroom->daily_routine as $i => $step)
                        <li class="relative flex break-inside-avoid gap-4 pb-5">
                            @if (! $loop->last)
                                <span aria-hidden="true" class="absolute bottom-0 left-[1.45rem] top-12 w-0.5" style="background: color-mix(in srgb, {{ $c }} 30%, #fff);"></span>
                            @endif
                            <span class="grid size-12 shrink-0 place-items-center rounded-full bg-white font-display text-sm font-semibold" style="box-shadow: inset 0 0 0 2.5px {{ $c }}; color: {{ $deep }};">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span>
                            <div class="flex-1 rounded-2xl bg-white px-4 py-3">
                                <p class="text-sm font-bold" style="color: {{ $deep }};">{{ $step['time'] ?? '' }}</p>
                                <p class="font-display text-lg font-medium leading-snug">{{ $step['label'] ?? '' }}</p>
                            </div>
                        </li>
                    @endforeach
                </ol>
            </div>
        </section>
    @endif

    {{-- What the class learns --}}
    @if ($items->isNotEmpty())
        <section class="bg-white py-14 sm:py-20" data-track-section="class_activities">
            <div class="mx-auto max-w-6xl px-5 sm:px-8">
                <x-site.section-head :title="'What '.$classroom->name.' learns'" subtitle="The activities in this class, how often they happen and what they look like at this age." />
                <div class="mt-10 grid gap-5 md:grid-cols-2">
                    @foreach ($items as $item)
                        @php
                            $activity = $item->activity;
                            $photos = $item->photos->isNotEmpty() ? $item->photos : $activity->photos;
                            $ac = $activity->color ?: $c;
                            $count = $photos->count();
                        @endphp
                        <article class="flex flex-col overflow-hidden rounded-[1.75rem] border-2 border-line bg-white" x-data="lightbox(@js($classroom->name.' – '.$activity->name))">
                            @if ($count === 0)
                                <x-site.photo :color="$ac" :icon="$activity->icon" ratio="3/1" rounded="rounded-none" :alt="$activity->name" />
                            @elseif ($count === 1)
                                @php $p = $photos->first(); @endphp
                                <button type="button" @click="show(0)" data-lightbox-item data-src="{{ $p->url() }}" data-alt="{{ $p->alt ?: $activity->name }}" data-caption="{{ $p->caption }}" class="block">
                                    <x-site.photo :src="$p->url()" :alt="$p->alt ?: $activity->name" ratio="16/10" rounded="rounded-none" />
                                    <span class="sr-only">Open photo</span>
                                </button>
                            @elseif ($count === 2)
                                <div class="grid grid-cols-2 gap-1">
                                    @foreach ($photos as $i => $p)
                                        <button type="button" @click="show({{ $i }})" data-lightbox-item data-src="{{ $p->url() }}" data-alt="{{ $p->alt ?: $activity->name }}" data-caption="{{ $p->caption }}" class="block">
                                            <x-site.photo :src="$p->url()" :alt="$p->alt ?: $activity->name" ratio="4/5" rounded="rounded-none" />
                                            <span class="sr-only">Open photo {{ $i + 1 }}</span>
                                        </button>
                                    @endforeach
                                </div>
                            @else
                                <div class="grid grid-cols-3 grid-rows-2 gap-1" style="aspect-ratio: 16/10;">
                                    @foreach ($photos as $i => $p)
                                        <button type="button" @click="show({{ $i }})" data-lightbox-item data-src="{{ $p->url() }}" data-alt="{{ $p->alt ?: $activity->name }}" data-caption="{{ $p->caption }}"
                                            @class(['relative block overflow-hidden', 'col-span-2 row-span-2' => $i === 0, 'hidden' => $i > 2])>
                                            <img src="{{ $p->url() }}" alt="{{ $p->alt ?: $activity->name }}" loading="lazy" class="absolute inset-0 size-full object-cover">
                                            @if ($i === 2 && $count > 3)
                                                <span class="absolute inset-0 grid place-items-center bg-ink/55 font-display text-xl font-semibold text-white">+{{ $count - 3 }}</span>
                                            @endif
                                            <span class="sr-only">Open photo {{ $i + 1 }}</span>
                                        </button>
                                    @endforeach
                                </div>
                            @endif

                            <div class="flex flex-1 flex-col p-5 sm:p-6">
                                <div class="flex items-start gap-3">
                                    <span class="grid size-11 shrink-0 place-items-center rounded-2xl" style="background: color-mix(in srgb, {{ $ac }} 14%, #fff); color: {{ $ac }};">
                                        <x-icon :name="$activity->icon" class="size-5" />
                                    </span>
                                    <div class="min-w-0 flex-1">
                                        <h3 class="font-display text-xl font-semibold leading-tight">
                                            <a href="{{ route('activities.show', $activity) }}" class="hover:underline">{{ $activity->name }}</a>
                                        </h3>
                                        @if ($item->frequency)
                                            <p class="mt-1 inline-flex items-center gap-1.5 text-sm font-bold" style="color: {{ $deep }};"><x-icon name="calendar" class="size-3.5" /> {{ $item->frequency }}</p>
                                        @endif
                                    </div>
                                </div>
                                <p class="mt-3 leading-relaxed text-ink-soft">{{ $item->details ?: $activity->summary }}</p>
                            </div>
                            @if ($count > 0)
                                @include('site.partials.lightbox-dialog')
                            @endif
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- Class photos --}}
    <section class="bg-blush py-14 sm:py-20" data-track-section="class_photos">
        <div class="mx-auto max-w-6xl px-5 sm:px-8">
            <x-site.section-head :title="'Inside '.$classroom->name" />
            <div class="mt-8">
                @include('site.partials.photo-grid', [
                    'photos' => $classroom->photos,
                    'title' => $classroom->name.' class photos',
                    'color' => $c,
                    'icon' => 'blocks',
                    'emptyTitle' => 'Class photos are coming soon',
                    'emptyText' => 'We’re picking our favourite moments from '.$classroom->name.'. Book a visit to see the classroom in person.',
                ])
            </div>
        </div>
    </section>

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
