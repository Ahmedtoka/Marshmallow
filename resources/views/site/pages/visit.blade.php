@extends('layouts.site')

@section('title', 'Visit us | Marshmallow Nursery in Hadayek Al Ahram & Sheikh Zayed')
@section('description', 'Our two branches, opening hours, what keeps your child safe, the meals, the buses, and how to book your visit.')

@section('content')
    <x-site.page-header
        title="Come and visit us"
        intro="Two branches in Giza. Come while the children are here — that is when you see what a Marshmallow morning really looks like.">
        <p class="mt-5 flex flex-wrap gap-2.5">
            <a href="{{ route('enroll') }}" class="btn btn-primary" data-track="cta_click" data-track-label="Visit page – Book a visit">Book a visit</a>
            @foreach ($branches as $branch)
                <a href="{{ $branch->telLink() }}" class="btn btn-outline" data-track-label="Call {{ $branch->name }}">
                    <x-icon name="phone" class="size-4" /> {{ $branch->short_name ?: $branch->name }} {{ $branch->phone }}
                </a>
            @endforeach
        </p>
    </x-site.page-header>

    {{-- Branches with their maps --}}
    <section class="bg-white py-14 sm:py-20" data-track-section="visit_branches">
        <div class="mx-auto max-w-6xl px-5 sm:px-8">
            <x-site.section-head title="Where to find us" :subtitle="setting('tour_hours')" />
            <div class="mt-8 grid gap-6 lg:grid-cols-2">
                @foreach ($branches as $branch)
                    @include('site.partials.branch-card', ['branch' => $branch, 'color' => $loop->first ? '#E8177F' : '#2CBCC9'])
                @endforeach
            </div>
        </div>
    </section>

    {{-- Safety, hygiene, meals, transport, hours --}}
    <section id="safety" class="bg-blush py-14 sm:py-20" data-track-section="visit_safety">
        <div class="mx-auto max-w-6xl px-5 sm:px-8">
            <x-site.section-head
                title="What we look after while you are at work"
                subtitle="The questions every parent asks us on the first visit, answered before you ask them." />

            <div class="mt-10 space-y-10">
                @foreach ($groups as $group => $items)
                    <div>
                        <h3 class="font-display text-xl font-medium text-ink">{{ \App\Models\Highlight::GROUPS[$group] ?? ucfirst($group) }}</h3>
                        <ul class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach ($items as $item)
                                @php $color = $item->color ?: '#E8177F'; @endphp
                                <li class="flex gap-3.5 rounded-[1.4rem] border-2 border-line-soft bg-white p-4">
                                    <span class="grid size-10 shrink-0 place-items-center rounded-xl" style="background: color-mix(in srgb, {{ $color }} 14%, #fff); color: {{ $color }};">
                                        <x-icon :name="$item->icon" class="size-5" />
                                    </span>
                                    <span>
                                        <span class="block font-bold leading-snug">{{ $item->title }}</span>
                                        @if ($item->description)
                                            <span class="mt-1 block text-[0.95rem] leading-relaxed text-ink-soft">{{ $item->description }}</span>
                                        @endif
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Questions --}}
    @if ($faqs->isNotEmpty())
        <section class="bg-white py-14 sm:py-20" data-track-section="visit_faq">
            <div class="mx-auto max-w-3xl px-5 sm:px-8">
                <x-site.section-head title="Questions parents ask us" align="center" />
                <div class="mt-8">
                    @include('site.partials.faq-list', ['faqs' => $faqs])
                </div>
            </div>
        </section>
    @endif

    {{-- Book --}}
    <section id="book" class="bg-ink py-14 text-white sm:py-20" data-track-section="visit_book">
        <div class="mx-auto grid max-w-5xl gap-10 px-5 sm:px-8 lg:grid-cols-[1fr_1.1fr] lg:items-center">
            <div>
                <h2 class="font-display text-[2rem] font-semibold leading-tight sm:text-[2.4rem]">Book your visit</h2>
                <p class="mt-4 text-lg leading-relaxed text-white/80">
                    Leave your number and our admissions team will call you back within one working day to arrange a time that suits you.
                </p>
                @if (setting('working_days') && setting('working_hours'))
                    <p class="mt-5 font-bold text-white/70">{{ setting('working_days') }} · {{ setting('working_hours') }}</p>
                @endif
            </div>
            <div class="rounded-[1.75rem] bg-white p-6 text-ink sm:p-8">
                @include('site.partials.compact-enroll', ['branches' => $branches, 'place' => 'Visit page'])
            </div>
        </div>
    </section>
@endsection
