@extends('layouts.site')

@php
    $years = setting('years_experience', '14');
    $story = setting('about_story', "Marshmallow opened its doors in 2011 with a simple belief: little children learn best when they feel safe, loved and free to play. For {$years} years that belief has shaped every morning in our classrooms.\n\nWe keep classes small, cook fresh meals every day and put cameras in every room so parents can relax at work. Our teachers are trained in Positive Discipline, and they spend their days singing, building, experimenting and reading with the children in English, with French and Arabic every week.\n\nToday we welcome families in Hadayek Al Ahram and Sheikh Zayed, and our graduates move on with confidence to international schools across West Cairo.");
    $philosophy = setting('about_philosophy', 'The first years of life are the most important stage of a child’s development. What children experience before school shapes how they think, feel and connect with others. That is why we teach through play: children build, cook, sing, experiment and tell stories, and the learning follows naturally.');
    $image = media_url(setting('about_image'));
@endphp

@section('content')
    <x-site.page-header
        :title="setting('about_title', $years.' years of happy mornings')"
        :intro="setting('about_intro', 'Marshmallow is an English-language child development center in Giza for children from 9 months to school age, with branches in Hadayek Al Ahram and Sheikh Zayed.')" />

    <section class="bg-white py-14 sm:py-20">
        <div class="mx-auto grid max-w-6xl items-center gap-10 px-5 sm:px-8 lg:grid-cols-2 lg:gap-16">
            <div>
                <h2 class="font-display text-[1.85rem] font-semibold leading-tight sm:text-4xl">Our story</h2>
                <div class="prose-mm mt-4 text-lg text-ink-soft">
                    @foreach (preg_split('/\R{2,}/', trim($story)) as $paragraph)
                        <p>{{ $paragraph }}</p>
                    @endforeach
                </div>
            </div>
            <div class="relative">
                <x-site.photo :src="$image" alt="Children playing at Marshmallow" ratio="4/5" color="#2CBCC9" icon="heart" rounded="rounded-[2.5rem]" class="mx-auto max-w-md" />
                <span aria-hidden="true" class="absolute -bottom-4 -left-2 size-16 rounded-full bg-sun sm:left-6"></span>
                <span aria-hidden="true" class="absolute -right-1 top-8 size-6 rounded-full bg-pink sm:right-10"></span>
            </div>
        </div>
    </section>

    <section class="bg-blush py-14 sm:py-20">
        <div class="mx-auto max-w-4xl px-5 sm:px-8">
            <div class="mm-bubble px-6 pb-8 pt-7 sm:px-12 sm:pb-12 sm:pt-10">
                <h2 class="font-display text-2xl font-semibold text-pink-600 sm:text-3xl">What we believe</h2>
                <p class="mt-4 font-display text-xl font-medium leading-relaxed sm:text-[1.65rem] sm:leading-relaxed">{{ $philosophy }}</p>
                <x-site.bubble-tail side="right" />
            </div>
            <div class="mt-3 flex justify-end pr-2 sm:pr-6"><x-site.mascot class="w-24 sm:w-28" /></div>
        </div>
    </section>

    <section class="bg-white py-14 sm:py-16">
        <div class="mx-auto max-w-6xl px-5 sm:px-8">
            <dl class="grid grid-cols-2 gap-6 md:grid-cols-4">
                @foreach (array_filter([
                    [$years, 'years caring for children', 'text-pink-600'],
                    setting('recommend_percent') ? [setting('recommend_percent').'%', 'of parents recommend us', 'text-teal-700'] : null,
                    setting('reviews_count') ? [setting('reviews_count'), 'reviews on Facebook', 'text-grape'] : null,
                    setting('followers') ? [setting('followers'), 'followers on Facebook', 'text-lime-700'] : null,
                ]) as [$value, $label, $tone])
                    <div class="rounded-[1.5rem] border-2 border-line-soft p-5">
                        <dt class="sr-only">{{ $label }}</dt>
                        <dd><span class="block font-display text-4xl font-semibold {{ $tone }}">{{ $value }}</span><span class="mt-1 block font-bold">{{ $label }}</span></dd>
                    </div>
                @endforeach
            </dl>
        </div>
    </section>

    @if ($credentials->isNotEmpty())
        <section class="bg-white pb-14 sm:pb-20">
            <div class="mx-auto grid max-w-6xl gap-8 px-5 sm:px-8 lg:grid-cols-[0.8fr_1.2fr] lg:gap-16">
                <x-site.section-head title="Trained, trusted and recognised" subtitle="The training and recognition behind our team." />
                <ul class="divide-y-2 divide-line-soft rounded-[2rem] border-2 border-line">
                    @foreach ($credentials as $item)
                        @php $color = $item->color ?: '#E8177F'; @endphp
                        <li class="flex gap-4 p-5 sm:p-6">
                            <span class="grid size-12 shrink-0 place-items-center rounded-full" style="background: color-mix(in srgb, {{ $color }} 14%, #fff); color: {{ $color }};"><x-icon :name="$item->icon" class="size-6" /></span>
                            <div>
                                <h3 class="font-display text-lg font-semibold">{{ $item->title }}</h3>
                                @if ($item->description)
                                    <p class="mt-1 leading-relaxed text-ink-soft">{{ $item->description }}</p>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        </section>
    @endif

    @if ($partners->isNotEmpty())
        <section class="bg-blush py-14 sm:py-20">
            <div class="mx-auto max-w-6xl px-5 sm:px-8">
                <x-site.section-head title="Where our graduates go next" subtitle="We work with international schools across West Cairo to help children move on with confidence." align="center" />
                @include('site.partials.partners', ['partners' => $partners])
            </div>
        </section>
    @endif

    @if ($testimonials->isNotEmpty())
        <section class="bg-white py-14 sm:py-20">
            <div class="mx-auto max-w-6xl px-5 sm:px-8">
                <x-site.section-head title="In parents’ words" />
                <div class="mt-8 grid gap-8 md:grid-cols-3">
                    @foreach ($testimonials->take(3) as $t)
                        <figure class="border-l-4 border-pink-200 pl-5">
                            <blockquote class="leading-relaxed">{{ $t->quote }}</blockquote>
                            <figcaption class="mt-3 text-sm"><span class="font-bold">{{ $t->parent_name }}</span>@if ($t->relation)<span class="block text-ink-soft">{{ $t->relation }}</span>@endif</figcaption>
                        </figure>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <section class="px-4 pb-14 sm:px-8 sm:pb-20">
        @include('site.partials.enroll-band', [
            'title' => 'Come and meet the team',
            'subtitle' => 'Book a visit to either branch and see our classrooms, garden and teachers for yourself.',
            'buttonText' => 'Book a visit',
            'buttonUrl' => route('enroll'),
            'place' => 'About page CTA',
        ])
    </section>
@endsection
