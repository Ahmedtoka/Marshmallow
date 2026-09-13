@extends('layouts.site')

@section('title', 'Page not found | Marshmallow Nursery')

@push('meta')
    <meta name="robots" content="noindex">
@endpush

@section('content')
    <section class="bg-white py-14 sm:py-24">
        <div class="mx-auto grid max-w-5xl items-end gap-6 px-5 sm:px-8 md:grid-cols-[1fr_auto]">
            <div class="mm-bubble px-6 pb-9 pt-8 sm:px-12 sm:pb-12 sm:pt-10">
                <p class="font-display text-6xl font-semibold text-pink sm:text-7xl">Oops!</p>
                <h1 class="mt-3 font-display text-3xl font-semibold leading-tight sm:text-4xl">We couldn’t find that page</h1>
                <p class="mt-3 text-lg leading-relaxed text-ink-soft">It may have moved, or the link might have a typo. Here are some places parents usually look for:</p>
                <ul class="mt-6 flex flex-wrap gap-2">
                    <li><a href="{{ route('home') }}" class="btn btn-primary btn-sm">Homepage</a></li>
                    <li><a href="{{ route('classes.index') }}" class="btn btn-soft btn-sm">Classes</a></li>
                    <li><a href="{{ route('branches') }}" class="btn btn-soft btn-sm">Branches</a></li>
                    <li><a href="{{ route('enroll') }}" class="btn btn-soft btn-sm" data-track="cta_click" data-track-label="404 – Book a visit">Book a visit</a></li>
                </ul>
                <x-site.bubble-tail side="right" />
            </div>
            <x-site.mascot class="ml-auto mt-4 w-28 md:w-40" />
        </div>
    </section>
@endsection
