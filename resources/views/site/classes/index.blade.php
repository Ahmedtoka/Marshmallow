@extends('layouts.site')

@section('content')
    <x-site.page-header
        :title="$classesSection?->title ?: 'Six classes, one for every stage'"
        :intro="$classesSection?->subtitle ?: 'Children are grouped by age so every activity fits where they are right now.'" />

    <section class="bg-white py-12 sm:py-16" data-track-section="classes_finder">
        <div class="mx-auto grid max-w-5xl items-end gap-4 px-5 sm:px-8 md:grid-cols-[1fr_auto] md:gap-8">
            <div class="mm-bubble p-5 sm:p-8">
                <x-site.class-finder :config="$finderConfig" id="classes-finder" place="Classes page finder"
                    :title="$finderSection?->title ?: 'Which class will your child join?'"
                    :subtitle="$finderSection?->subtitle" />
                <x-site.bubble-tail side="right" />
            </div>
            <x-site.mascot class="ml-auto mt-6 w-24 md:mt-0 md:w-40" />
        </div>
    </section>

    <section class="bg-blush py-14 sm:py-20" data-track-section="classes_path">
        <div class="mx-auto max-w-5xl px-5 sm:px-8">
            <x-site.section-head title="Growing up at Marshmallow" subtitle="Your child moves up a class as they grow. Class ages are measured on 1 October of each school year." />

            <ol class="relative mt-10 space-y-6 sm:mt-12">
                <span aria-hidden="true" class="absolute bottom-6 left-[1.9rem] top-6 w-1 rounded-full bg-line sm:left-[2.4rem]"></span>
                @foreach ($classrooms as $class)
                    <li class="relative grid grid-cols-[3.8rem_1fr] gap-3 sm:grid-cols-[4.8rem_1fr] sm:gap-5">
                        <div class="relative z-10 flex justify-center pt-5">
                            <span class="grid size-[3.8rem] place-items-center rounded-full bg-white sm:size-[4.8rem]" style="box-shadow: 0 0 0 3px {{ $class->color }};">
                                <x-site.candy :name="$class->icon" :color="$class->color" class="size-10 sm:size-14" />
                            </span>
                        </div>
                        <article class="rounded-[1.75rem] border-2 bg-white p-5 sm:p-7" style="border-color: color-mix(in srgb, {{ $class->color }} 30%, #fff);">
                            <div class="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-1">
                                <h2 class="font-display text-[1.7rem] font-semibold leading-tight" style="color: color-mix(in srgb, {{ $class->color }} 75%, #33307A);">
                                    <a href="{{ route('classes.show', $class) }}" class="hover:underline">{{ $class->name }}</a>
                                </h2>
                                <p class="rounded-full px-3 py-1 text-sm font-bold" style="background: color-mix(in srgb, {{ $class->color }} 12%, #fff);">{{ $class->ageRangeLabel() }}</p>
                            </div>
                            @if ($class->tagline)
                                <p class="mt-1 font-display text-lg text-ink-soft">{{ $class->tagline }}</p>
                            @endif
                            @if ($class->summary)
                                <p class="prose-mm mt-3 text-ink-soft">{{ $class->summary }}</p>
                            @endif
                            @if ($class->activities->isNotEmpty())
                                <ul class="mt-4 flex flex-wrap gap-1.5">
                                    @foreach ($class->activities->where('is_active', true)->take(5) as $activity)
                                        <li class="inline-flex items-center gap-1.5 rounded-full bg-blush px-2.5 py-1 text-sm font-bold">
                                            <x-icon :name="$activity->icon" class="size-3.5" style="color: {{ $activity->color ?: $class->color }}" /> {{ $activity->name }}
                                        </li>
                                    @endforeach
                                    @if ($class->activities->where('is_active', true)->count() > 5)
                                        <li class="px-1 py-1 text-sm font-bold text-ink-muted">and {{ $class->activities->where('is_active', true)->count() - 5 }} more</li>
                                    @endif
                                </ul>
                            @endif
                            <a href="{{ route('classes.show', $class) }}" class="btn btn-soft btn-sm mt-5" data-track="cta_click" data-track-label="Classes page – Inside {{ $class->name }}">
                                Inside {{ $class->name }} <x-icon name="chevron-right" class="size-4" />
                            </a>
                        </article>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    <section class="px-4 py-12 sm:px-8 sm:py-16">
        @include('site.partials.enroll-band', [
            'title' => 'See the classrooms for yourself',
            'subtitle' => 'Book a visit and our admissions team will show you around and answer every question.',
            'buttonText' => 'Book a visit',
            'buttonUrl' => route('enroll'),
            'place' => 'Classes page CTA',
        ])
    </section>
@endsection
