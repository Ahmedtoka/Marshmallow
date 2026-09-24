@extends('layouts.site')

@section('title', 'One moment | Marshmallow Nursery')

@push('meta')
    <meta name="robots" content="noindex">
@endpush

@section('content')
    <section class="bg-white py-14 sm:py-24">
        <div class="mx-auto max-w-2xl px-5 sm:px-8">
            <div class="mm-bubble px-6 pb-9 pt-8 sm:px-10">
                <h1 class="font-display text-3xl font-semibold leading-tight sm:text-4xl">Give us one moment</h1>
                <p class="mt-3 text-lg leading-relaxed text-ink-soft">
                    That was a lot of requests in a short time, so we paused for a minute. Please wait a moment and send
                    the form again — or simply call us, we would love to talk to you.
                </p>

                <div class="mt-6 flex flex-wrap gap-2">
                    <a href="{{ route('enroll') }}" class="btn btn-primary">Back to the form</a>
                    @foreach (\App\Models\Branch::active()->get() as $branch)
                        <a href="{{ $branch->telLink() }}" class="btn btn-soft" data-track-label="Call {{ $branch->name }}">
                            <x-icon name="phone" class="size-4" /> {{ $branch->short_name ?: $branch->name }} {{ $branch->phone }}
                        </a>
                    @endforeach
                </div>

                <x-site.bubble-tail side="right" />
            </div>
        </div>
    </section>
@endsection
