@extends('layouts.site')

@php $seasonColors = ['summer' => '#E8A317', 'winter' => '#2CBCC9', 'spring' => '#7FA82A', 'autumn' => '#E8177F']; @endphp

@section('content')
    <x-site.page-header
        :title="$section?->title ?: 'Camps for every school holiday'"
        :intro="$section?->subtitle ?: 'Holiday camps for children aged 4 to 12, with meals included.'"
        color="#E8A317" />

    <section class="bg-white py-12 sm:py-16">
        <div class="mx-auto max-w-6xl space-y-6 px-5 sm:space-y-8 sm:px-8">
            @forelse ($camps as $i => $camp)
                @php $color = $seasonColors[$camp->season] ?? '#E8177F'; @endphp
                <article class="grid overflow-hidden rounded-[2rem] border-2 border-line md:grid-cols-2" data-track-section="camp_{{ $camp->slug }}">
                    <a href="{{ route('camps.show', $camp) }}" @class(['block', 'md:order-2' => $i % 2 === 1]) tabindex="-1" aria-hidden="true">
                        <x-site.photo :src="media_url($camp->cover_image) ?? $camp->photos->first()?->url()" :alt="$camp->title" ratio="4/3" :color="$color" icon="sun" rounded="rounded-none" class="h-full md:aspect-auto!" />
                    </a>
                    <div class="p-6 sm:p-9">
                        <div class="flex flex-wrap gap-2">
                            <span class="rounded-full px-3 py-1 text-sm font-bold" style="background: color-mix(in srgb, {{ $color }} 15%, #fff);">{{ $camp->seasonLabel() }} camp</span>
                            @if ($camp->badge)
                                <span class="rounded-full bg-sun px-3 py-1 text-sm font-bold">{{ $camp->badge }}</span>
                            @endif
                        </div>
                        <h2 class="mt-3 font-display text-[1.9rem] font-semibold leading-tight">
                            <a href="{{ route('camps.show', $camp) }}" class="hover:underline">{{ $camp->title }}</a>
                        </h2>
                        @if ($camp->summary)
                            <p class="mt-2 text-lg leading-relaxed text-ink-soft">{{ $camp->summary }}</p>
                        @endif
                        <dl class="mt-5 grid gap-3 sm:grid-cols-2">
                            <div class="flex gap-2.5"><x-icon name="users" class="mt-0.5 size-5" style="color: {{ $color }}" /><div><dt class="text-sm font-bold text-ink-soft">Ages</dt><dd class="font-bold">{{ $camp->age_from }} to {{ $camp->age_to }} years</dd></div></div>
                            @if ($camp->datesLabel())
                                <div class="flex gap-2.5"><x-icon name="calendar" class="mt-0.5 size-5" style="color: {{ $color }}" /><div><dt class="text-sm font-bold text-ink-soft">Dates</dt><dd class="font-bold">{{ $camp->datesLabel() }}</dd></div></div>
                            @endif
                            @if ($camp->schedule)
                                <div class="flex gap-2.5"><x-icon name="clock" class="mt-0.5 size-5" style="color: {{ $color }}" /><div><dt class="text-sm font-bold text-ink-soft">Schedule</dt><dd class="font-bold">{{ $camp->schedule }}</dd></div></div>
                            @endif
                            @if ($camp->meals)
                                <div class="flex gap-2.5"><x-icon name="apple" class="mt-0.5 size-5" style="color: {{ $color }}" /><div><dt class="text-sm font-bold text-ink-soft">Meals</dt><dd class="font-bold">{{ $camp->meals }}</dd></div></div>
                            @endif
                        </dl>
                        <div class="mt-6 flex flex-wrap gap-3">
                            <a href="{{ route('enroll', ['interest' => 'camp', 'camp' => $camp->slug]) }}" class="btn btn-primary" data-track="cta_click" data-track-label="Camps page – Register interest {{ $camp->title }}">Register your interest</a>
                            <a href="{{ route('camps.show', $camp) }}" class="btn btn-outline" data-track="cta_click" data-track-label="Camps page – Details {{ $camp->title }}">Camp details</a>
                        </div>
                    </div>
                </article>
            @empty
                <div class="mx-auto max-w-xl rounded-[2rem] border-2 border-dashed border-line p-8 text-center">
                    <x-icon name="sun" class="mx-auto size-10 text-honey" style="color:#E8A317" />
                    <h2 class="mt-3 font-display text-2xl font-semibold">No camps are open right now</h2>
                    <p class="mt-2 text-ink-soft">We run camps in every school holiday. Leave your number and we’ll tell you as soon as registration opens.</p>
                    <a href="{{ route('enroll', ['interest' => 'camp']) }}" class="btn btn-primary mt-5" data-track="cta_click" data-track-label="Camps empty – Tell me when">Tell me when camps open</a>
                </div>
            @endforelse
        </div>
    </section>
@endsection
