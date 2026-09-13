@extends('layouts.site')

@php
    $intros = [
        'safety' => 'Children never reach the street, and every room is watched over.',
        'health' => 'Clean rooms, clean hands and small classes.',
        'meals' => 'Freshly made every day, and served on a routine children can count on.',
        'logistics' => 'Getting here, and staying in touch while your child is with us.',
        'credentials' => 'The training behind the people who care for your child.',
        'services' => 'When we’re open and what we offer around the school day.',
    ];
    $section = \App\Models\Section::for('safety');
@endphp

@section('content')
    <x-site.page-header
        :title="$section?->title ?: 'Safe, clean and well fed'"
        :intro="$section?->subtitle ?: 'How we look after your child every day.'"
        color="#8479BD">
        @if ($groups->count() > 1)
            <nav class="mt-6" aria-label="Sections">
                <ul class="flex flex-wrap gap-2">
                    @foreach (\App\Models\Highlight::GROUPS as $key => $label)
                        @continue(! $groups->has($key))
                        <li><a href="#{{ $key }}" class="inline-block rounded-full border-2 border-line bg-white px-3.5 py-1.5 text-sm font-bold hover:border-pink-200">{{ $label }}</a></li>
                    @endforeach
                </ul>
            </nav>
        @endif
    </x-site.page-header>

    @php $n = 0; @endphp
    @foreach (\App\Models\Highlight::GROUPS as $key => $label)
        @continue(! $groups->has($key))
        @php $items = $groups[$key]; $tint = $n++ % 2 === 1; @endphp
        <section id="{{ $key }}" class="{{ $tint ? 'bg-blush' : 'bg-white' }} py-14 sm:py-20" data-track-section="safety_{{ $key }}">
            <div class="mx-auto grid max-w-6xl gap-8 px-5 sm:px-8 lg:grid-cols-[0.75fr_1.25fr] lg:gap-16">
                <div class="self-start lg:sticky lg:top-32">
                    <h2 class="font-display text-[1.85rem] font-semibold leading-tight sm:text-4xl">{{ $label }}</h2>
                    @if (isset($intros[$key]))
                        <p class="mt-3 text-lg leading-relaxed text-ink-soft">{{ $intros[$key] }}</p>
                    @endif
                </div>

                @if ($key === 'meals')
                    <ol class="grid gap-3 sm:grid-cols-2">
                        @foreach ($items as $item)
                            @php $color = $item->color ?: '#E8177F'; @endphp
                            <li class="flex gap-4 rounded-[1.5rem] bg-sun-100 p-5">
                                <span class="grid size-12 shrink-0 place-items-center rounded-2xl bg-white" style="color: {{ $color }}"><x-icon :name="$item->icon" class="size-6" /></span>
                                <div>
                                    <h3 class="font-display text-xl font-semibold">{{ $item->title }}</h3>
                                    @if ($item->description)<p class="mt-1 text-ink-soft">{{ $item->description }}</p>@endif
                                </div>
                            </li>
                        @endforeach
                    </ol>
                @elseif ($key === 'services')
                    <dl class="grid gap-3 sm:grid-cols-3">
                        @foreach ($items as $item)
                            @php $color = $item->color ?: '#2CBCC9'; @endphp
                            <div class="rounded-[1.5rem] border-2 p-5" style="border-color: color-mix(in srgb, {{ $color }} 35%, #fff);">
                                <x-icon :name="$item->icon" class="size-6" style="color: {{ $color }}" />
                                <dt class="mt-3 font-display text-lg font-semibold">{{ $item->title }}</dt>
                                @if ($item->description)<dd class="mt-1 leading-relaxed text-ink-soft">{{ $item->description }}</dd>@endif
                            </div>
                        @endforeach
                    </dl>
                @else
                    <ul class="divide-y-2 divide-line-soft rounded-[2rem] border-2 border-line bg-white">
                        @foreach ($items as $item)
                            @php $color = $item->color ?: '#8479BD'; @endphp
                            <li class="flex gap-4 p-5 sm:p-6">
                                <span class="grid size-12 shrink-0 place-items-center rounded-full" style="background: color-mix(in srgb, {{ $color }} 14%, #fff); color: {{ $color }};"><x-icon :name="$item->icon" class="size-6" /></span>
                                <div>
                                    <h3 class="font-display text-lg font-semibold leading-snug">{{ $item->title }}</h3>
                                    @if ($item->description)<p class="mt-1 leading-relaxed text-ink-soft">{{ $item->description }}</p>@endif
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </section>
    @endforeach

    <section class="px-4 py-12 sm:px-8 sm:py-16">
        @include('site.partials.enroll-band', [
            'title' => 'See it for yourself',
            'subtitle' => setting('tour_hours') ?: 'Book a visit and watch the classes live on the reception screens.',
            'buttonText' => 'Book a visit',
            'buttonUrl' => route('enroll', ['interest' => 'tour']),
            'place' => 'Safety page CTA',
        ])
    </section>
@endsection
