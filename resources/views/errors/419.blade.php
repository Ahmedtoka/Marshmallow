@extends('layouts.site')

@section('title', 'Page expired | Marshmallow Nursery')

@section('content')
    <section class="bg-white py-14 sm:py-24">
        <div class="mx-auto max-w-2xl px-5 sm:px-8">
            <div class="mm-bubble px-6 pb-9 pt-8 sm:px-10">
                <h1 class="font-display text-3xl font-semibold leading-tight sm:text-4xl">This page timed out</h1>
                <p class="mt-3 text-lg leading-relaxed text-ink-soft">For your security, forms expire when left open for a long time. Please go back, refresh the page and send it again.</p>
                <div class="mt-6 flex flex-wrap gap-2">
                    <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('enroll') }}" class="btn btn-primary">Go back</a>
                    <a href="{{ route('home') }}" class="btn btn-soft">Homepage</a>
                </div>
                <x-site.bubble-tail side="right" />
            </div>
        </div>
    </section>
@endsection
