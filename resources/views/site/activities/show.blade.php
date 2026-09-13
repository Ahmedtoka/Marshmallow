@extends('layouts.site')

@php $color = $activity->color ?: '#E8177F'; @endphp

@section('title', $activity->name.' | Activities at Marshmallow Nursery')
@section('description', \Illuminate\Support\Str::limit((string) ($activity->summary ?: $activity->description), 160))
@if ($activity->cover_image)
    @section('og_image', media_url($activity->cover_image))
@endif

@section('content')
    <x-site.page-header :title="$activity->name" :intro="$activity->summary" :color="$color" :back="route('activities.index')" back-label="All activities">
        <p class="mt-5 inline-flex items-center gap-2 rounded-full bg-white px-3.5 py-1.5 font-bold">
            <x-icon :name="$activity->icon" class="size-5" style="color: {{ $color }}" /> {{ $activity->categoryLabel() }}
        </p>
    </x-site.page-header>

    <section class="bg-white py-12 sm:py-16">
        <div class="mx-auto grid max-w-6xl gap-10 px-5 sm:px-8 lg:grid-cols-[1.1fr_1fr] lg:gap-16">
            <div>
                @if ($activity->description && $activity->description !== $activity->summary)
                    <div class="prose-mm text-lg text-ink-soft">{!! nl2br(e($activity->description)) !!}</div>
                @endif
                @if (media_url($activity->cover_image))
                    <x-site.photo :src="media_url($activity->cover_image)" :alt="$activity->name" ratio="16/10" :color="$color" class="mt-6" />
                @endif
            </div>

            @if ($activity->classrooms->isNotEmpty())
                <div data-track-section="activity_classes">
                    <h2 class="font-display text-2xl font-semibold sm:text-[1.9rem]">Which classes do this</h2>
                    <ul class="mt-5 space-y-3">
                        @foreach ($activity->classrooms as $class)
                            <li>
                                <a href="{{ route('classes.show', $class) }}" class="group flex gap-4 rounded-[1.4rem] border-2 p-4 transition-colors" style="border-color: color-mix(in srgb, {{ $class->color }} 30%, #fff); background: color-mix(in srgb, {{ $class->color }} 5%, #fff);">
                                    <x-site.candy :name="$class->icon" :color="$class->color" class="size-12" />
                                    <span class="min-w-0 flex-1">
                                        <span class="flex flex-wrap items-baseline justify-between gap-x-3">
                                            <span class="font-display text-xl font-semibold group-hover:underline" style="color: color-mix(in srgb, {{ $class->color }} 72%, #33307A);">{{ $class->name }}</span>
                                            <span class="text-sm font-bold text-ink-soft">{{ $class->ageRangeLabel() }}</span>
                                        </span>
                                        @if ($class->pivot->frequency)
                                            <span class="mt-1 inline-flex items-center gap-1.5 text-sm font-bold"><x-icon name="calendar" class="size-3.5" style="color: {{ $class->color }}" /> {{ $class->pivot->frequency }}</span>
                                        @endif
                                        @if ($class->pivot->details)
                                            <span class="mt-1 block leading-relaxed text-ink-soft">{{ $class->pivot->details }}</span>
                                        @endif
                                    </span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </section>

    <section class="bg-blush py-12 sm:py-16" data-track-section="activity_photos">
        <div class="mx-auto max-w-6xl px-5 sm:px-8">
            <x-site.section-head title="Photos" />
            <div class="mt-8">
                @include('site.partials.photo-grid', [
                    'photos' => $activity->photos,
                    'title' => $activity->name,
                    'color' => $color,
                    'icon' => $activity->icon,
                    'emptyTitle' => 'Photos of '.\Illuminate\Support\Str::lower($activity->name).' are coming soon',
                ])
            </div>
        </div>
    </section>

    @if ($related->isNotEmpty())
        <section class="bg-white py-12">
            <div class="mx-auto max-w-6xl px-5 sm:px-8">
                <h2 class="font-display text-2xl font-semibold">More in {{ \Illuminate\Support\Str::lower($activity->categoryLabel()) }}</h2>
                <ul class="mt-5 flex flex-wrap gap-2">
                    @foreach ($related as $other)
                        <li>
                            <a href="{{ route('activities.show', $other) }}" class="chip bg-white hover:bg-blush" style="--chip: color-mix(in srgb, {{ $other->color ?: '#E8177F' }} 35%, #fff);">
                                <x-icon :name="$other->icon" class="size-4" style="color: {{ $other->color ?: '#E8177F' }}" /> {{ $other->name }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </section>
    @endif

    <section class="px-4 py-12 sm:px-8 sm:py-16">
        @include('site.partials.enroll-band', [
            'title' => 'Come and see it for yourself',
            'subtitle' => 'Book a visit and our team will show you the classrooms, garden and daily activities.',
            'buttonText' => 'Book a visit',
            'buttonUrl' => route('enroll'),
            'place' => $activity->name.' page CTA',
        ])
    </section>
@endsection
