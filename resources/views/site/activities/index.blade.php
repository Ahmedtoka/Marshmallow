@extends('layouts.site')

@section('content')
    <x-site.page-header
        :title="$section?->title ?: 'A week full of discovery'"
        :intro="$section?->subtitle ?: 'Academics in English, three languages, gymnastics, science, art, cooking and a trip every month.'"
        color="#2CBCC9">
        @if ($groups->count() > 1)
            <nav class="mt-6" aria-label="Activity groups">
                <ul class="flex flex-wrap gap-2">
                    @foreach ($groups as $key => $group)
                        <li><a href="#{{ $key }}" class="inline-block rounded-full border-2 border-line bg-white px-3.5 py-1.5 text-sm font-bold hover:border-pink-200">{{ $group['label'] }}</a></li>
                    @endforeach
                </ul>
            </nav>
        @endif
    </x-site.page-header>

    <div class="bg-white py-12 sm:py-16">
        <div class="mx-auto max-w-6xl space-y-14 px-5 sm:px-8 sm:space-y-20">
            @foreach ($groups as $key => $group)
                <section id="{{ $key }}" class="grid gap-6 lg:grid-cols-[14rem_1fr] lg:gap-12" data-track-section="activities_{{ $key }}">
                    <h2 class="font-display text-[1.7rem] font-semibold leading-tight lg:sticky lg:top-32 lg:self-start">{{ $group['label'] }}</h2>
                    <ul class="grid gap-4 md:grid-cols-2">
                        @foreach ($group['items'] as $activity)
                            @php $color = $activity->color ?: '#E8177F'; @endphp
                            <li>
                                <a href="{{ route('activities.show', $activity) }}" class="group flex h-full gap-4 rounded-[1.5rem] border-2 border-line-soft p-5 transition-colors hover:border-pink-200">
                                    <span class="grid size-12 shrink-0 place-items-center rounded-2xl" style="background: color-mix(in srgb, {{ $color }} 14%, #fff); color: {{ $color }};">
                                        <x-icon :name="$activity->icon" class="size-6" />
                                    </span>
                                    <span class="min-w-0">
                                        <span class="block font-display text-xl font-semibold leading-tight group-hover:underline">{{ $activity->name }}</span>
                                        @if ($activity->summary)
                                            <span class="mt-1.5 block leading-relaxed text-ink-soft">{{ $activity->summary }}</span>
                                        @endif
                                        @if ($activity->classrooms->isNotEmpty())
                                            <span class="mt-3 flex items-center gap-2">
                                                <span class="flex -space-x-1.5">
                                                    @foreach ($activity->classrooms as $class)
                                                        <span class="grid size-7 place-items-center rounded-full bg-white ring-2 ring-white" title="{{ $class->name }}">
                                                            <x-site.candy :name="$class->icon" :color="$class->color" class="size-6" />
                                                        </span>
                                                    @endforeach
                                                </span>
                                                <span class="text-sm font-bold text-ink-muted">
                                                    {{ $activity->classrooms->count() === 1 ? $activity->classrooms->first()->name.' class' : 'In '.$activity->classrooms->count().' classes' }}
                                                </span>
                                            </span>
                                        @endif
                                    </span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endforeach
        </div>
    </div>

    <section class="px-4 py-12 sm:px-8 sm:py-16">
        @include('site.partials.enroll-band', [
            'title' => 'Want to see a class in action?',
            'subtitle' => 'Book a visit and watch the classes live on the reception screens.',
            'buttonText' => 'Book a visit',
            'buttonUrl' => route('enroll'),
            'place' => 'Activities page CTA',
        ])
    </section>
@endsection
