@extends('layouts.site')

@php
    $albumColors = ['activities' => '#E8177F', 'celebrations' => '#8479BD', 'graduation' => '#2CBCC9', 'trips' => '#7FA82A', 'camps' => '#E8A317', 'campus' => '#C0479A'];
    $albumIcons = ['activities' => 'blocks', 'celebrations' => 'balloon', 'graduation' => 'medal', 'trips' => 'bus', 'camps' => 'sun', 'campus' => 'leaf'];
    $color = $albumColors[$album->category] ?? '#8479BD';
@endphp

@section('title', $album->title.' | Marshmallow Nursery gallery')
@section('description', \Illuminate\Support\Str::limit((string) ($album->description ?: $album->title.' at Marshmallow Child Development Center.'), 160))
@if ($cover = media_url($album->cover_image) ?? $album->photos->first()?->url())
    @section('og_image', $cover)
@endif

@section('content')
    <x-site.page-header :title="$album->title" :intro="$album->description" :color="$color" :back="route('gallery.index')" back-label="All albums">
        <ul class="mt-5 flex flex-wrap gap-2 text-sm font-bold">
            <li class="inline-flex items-center gap-1.5 rounded-full bg-white px-3 py-1.5"><x-icon :name="$albumIcons[$album->category] ?? 'camera'" class="size-4" style="color: {{ $color }}" /> {{ $album->categoryLabel() }}</li>
            @if ($album->event_date)
                <li class="inline-flex items-center gap-1.5 rounded-full bg-white px-3 py-1.5"><x-icon name="calendar" class="size-4" style="color: {{ $color }}" /> {{ $album->event_date->format('j F Y') }}</li>
            @endif
            @if ($album->branch)
                <li class="inline-flex items-center gap-1.5 rounded-full bg-white px-3 py-1.5"><x-icon name="map-pin" class="size-4" style="color: {{ $color }}" /> {{ $album->branch->name }}</li>
            @endif
            @if ($album->photos->isNotEmpty())
                <li class="inline-flex items-center gap-1.5 rounded-full bg-white px-3 py-1.5"><x-icon name="image" class="size-4" style="color: {{ $color }}" /> {{ $album->photos->count() }} {{ \Illuminate\Support\Str::plural('photo', $album->photos->count()) }}</li>
            @endif
        </ul>
    </x-site.page-header>

    <section class="bg-white py-10 sm:py-14">
        <div class="mx-auto max-w-6xl px-5 sm:px-8">
            @include('site.partials.photo-grid', [
                'photos' => $album->photos,
                'title' => $album->title,
                'color' => $color,
                'icon' => $albumIcons[$album->category] ?? 'camera',
                'emptyTitle' => 'Photos from this album are on their way',
                'emptyText' => 'We’re choosing the best moments from '.$album->title.'. Follow us on Facebook to see them first.',
            ])
        </div>
    </section>

    @if ($others->isNotEmpty())
        <section class="bg-blush py-12 sm:py-16">
            <div class="mx-auto max-w-6xl px-5 sm:px-8">
                <h2 class="font-display text-2xl font-semibold sm:text-[1.9rem]">More albums</h2>
                <ul class="mt-6 grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-3">
                    @foreach ($others as $other)
                        <li @class(['hidden lg:block' => $loop->last])>@include('site.partials.album-card', ['album' => $other, 'place' => 'More albums'])</li>
                    @endforeach
                </ul>
            </div>
        </section>
    @endif
@endsection
