@extends('layouts.site')

@php
    $seasonColors = ['summer' => '#E8A317', 'winter' => '#2CBCC9', 'spring' => '#7FA82A', 'autumn' => '#E8177F'];
    $color = $seasonColors[$camp->season] ?? '#E8177F';
    $enrollUrl = route('enroll', ['interest' => 'camp', 'camp' => $camp->slug]);
@endphp

@section('title', $camp->title.' for ages '.$camp->age_from.' to '.$camp->age_to.' | Marshmallow Camps')
@section('description', \Illuminate\Support\Str::limit((string) ($camp->summary ?: $camp->description), 160))
@if ($camp->cover_image)
    @section('og_image', media_url($camp->cover_image))
@endif

@section('content')
    <x-site.page-header :title="$camp->title" :intro="$camp->summary" :color="$color" :back="route('camps.index')" back-label="All camps">
        <div class="mt-5 flex flex-wrap gap-2">
            <span class="rounded-full bg-white px-3.5 py-1.5 font-bold">{{ $camp->seasonLabel() }} camp</span>
            @if ($camp->badge)
                <span class="rounded-full bg-sun px-3.5 py-1.5 font-bold">{{ $camp->badge }}</span>
            @endif
        </div>
    </x-site.page-header>

    <section class="bg-white py-12 sm:py-16">
        <div class="mx-auto grid max-w-6xl items-start gap-10 px-5 sm:px-8 lg:grid-cols-[1.35fr_1fr] lg:gap-14">
            <div>
                <x-site.photo :src="media_url($camp->cover_image) ?? $camp->photos->first()?->url()" :alt="$camp->title" ratio="16/10" :color="$color" icon="sun" rounded="rounded-[2rem]" eager />

                @if ($camp->description)
                    <div class="prose-mm mt-8 text-lg text-ink-soft">{!! nl2br(e($camp->description)) !!}</div>
                @endif

                @if (! empty($camp->activities))
                    <h2 class="mt-10 font-display text-2xl font-semibold sm:text-[1.9rem]">What children do at camp</h2>
                    <ul class="mt-4 flex flex-wrap gap-2">
                        @foreach ($camp->activities as $name)
                            <li class="chip bg-white" style="--chip: color-mix(in srgb, {{ $color }} 40%, #fff);">
                                <span class="size-2 rounded-full" style="background: {{ $color }}"></span> {{ $name }}
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <aside class="rounded-[2rem] p-6 sm:p-8 lg:sticky lg:top-28" style="background: color-mix(in srgb, {{ $color }} 10%, #fff);">
                <h2 class="font-display text-2xl font-semibold">Camp at a glance</h2>
                <dl class="mt-5 space-y-4">
                    <div class="flex gap-3"><span class="grid size-10 shrink-0 place-items-center rounded-xl bg-white" style="color: {{ $color }}"><x-icon name="users" class="size-5" /></span><div><dt class="text-sm font-bold text-ink-soft">Ages</dt><dd class="font-bold">{{ $camp->age_from }} to {{ $camp->age_to }} years</dd></div></div>
                    @if ($camp->datesLabel())
                        <div class="flex gap-3"><span class="grid size-10 shrink-0 place-items-center rounded-xl bg-white" style="color: {{ $color }}"><x-icon name="calendar" class="size-5" /></span><div><dt class="text-sm font-bold text-ink-soft">Dates</dt><dd class="font-bold">{{ $camp->datesLabel() }}</dd></div></div>
                    @endif
                    @if ($camp->schedule)
                        <div class="flex gap-3"><span class="grid size-10 shrink-0 place-items-center rounded-xl bg-white" style="color: {{ $color }}"><x-icon name="clock" class="size-5" /></span><div><dt class="text-sm font-bold text-ink-soft">Schedule</dt><dd class="font-bold">{{ $camp->schedule }}</dd></div></div>
                    @endif
                    @if ($camp->meals)
                        <div class="flex gap-3"><span class="grid size-10 shrink-0 place-items-center rounded-xl bg-white" style="color: {{ $color }}"><x-icon name="apple" class="size-5" /></span><div><dt class="text-sm font-bold text-ink-soft">Meals</dt><dd class="font-bold">{{ $camp->meals }}</dd></div></div>
                    @endif
                </dl>
                <a href="{{ $enrollUrl }}" class="btn btn-primary mt-7 w-full" data-track="cta_click" data-track-label="{{ $camp->title }} – Register interest">Register your interest</a>
                <p class="mt-3 text-center text-sm text-ink-soft">We’ll call you with prices and available days.</p>
            </aside>
        </div>
    </section>

    <section class="bg-blush py-12 sm:py-16" data-track-section="camp_photos">
        <div class="mx-auto max-w-6xl px-5 sm:px-8">
            <x-site.section-head title="Camp photos" />
            <div class="mt-8">
                @include('site.partials.photo-grid', [
                    'photos' => $camp->photos,
                    'title' => $camp->title,
                    'color' => $color,
                    'icon' => 'sun',
                    'emptyTitle' => 'Camp photos are coming soon',
                ])
            </div>
        </div>
    </section>

    @if ($others->isNotEmpty())
        <section class="bg-white py-12">
            <div class="mx-auto max-w-6xl px-5 sm:px-8">
                <h2 class="font-display text-2xl font-semibold">Other camps</h2>
                <ul class="mt-5 grid gap-4 sm:grid-cols-2">
                    @foreach ($others as $other)
                        @php $oc = $seasonColors[$other->season] ?? '#E8177F'; @endphp
                        <li>
                            <a href="{{ route('camps.show', $other) }}" class="group flex items-center gap-4 rounded-[1.5rem] border-2 border-line p-3 pr-5 hover:border-pink-200">
                                <x-site.photo :src="media_url($other->cover_image) ?? $other->photos->first()?->url()" :alt="$other->title" ratio="1/1" :color="$oc" icon="sun" rounded="rounded-[1.1rem]" class="w-20 shrink-0" />
                                <span>
                                    <span class="block font-display text-xl font-semibold group-hover:underline">{{ $other->title }}</span>
                                    <span class="block text-sm font-bold text-ink-soft">Ages {{ $other->age_from }} to {{ $other->age_to }}</span>
                                </span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </section>
    @endif

    <section class="px-4 py-12 sm:px-8 sm:py-16">
        @include('site.partials.enroll-band', [
            'title' => 'Spaces at camp are limited',
            'subtitle' => 'Register your interest and we’ll call you with prices, dates and available days.',
            'buttonText' => 'Register your interest',
            'buttonUrl' => $enrollUrl,
            'place' => $camp->title.' CTA',
        ])
    </section>
@endsection
