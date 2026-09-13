@extends('layouts.site')

@section('content')
    <x-site.page-header title="Visit a branch near you" intro="Two branches in Giza, both open Sunday to Thursday. Call, message us on WhatsApp or book a visit." color="#2CBCC9">
        @if (setting('tour_hours'))
            <p class="mt-5 inline-flex items-center gap-2 rounded-full bg-white px-4 py-2 font-bold"><x-icon name="camera" class="size-5 text-grape" /> {{ setting('tour_hours') }}</p>
        @endif
    </x-site.page-header>

    @foreach ($branches as $i => $branch)
        @php $color = $i % 2 ? '#2CBCC9' : '#E8177F'; @endphp
        <section id="{{ $branch->slug }}" class="{{ $i % 2 ? 'bg-blush' : 'bg-white' }} py-12 sm:py-16" data-track-section="branch_{{ $branch->slug }}">
            <div class="mx-auto grid max-w-6xl items-start gap-8 px-5 sm:px-8 lg:grid-cols-2 lg:gap-12">
                <div class="relative overflow-hidden rounded-[2rem] border-2 border-line bg-white p-6 sm:p-9">
                    <span aria-hidden="true" class="absolute -right-10 -top-10 size-32 rounded-full opacity-20" style="background: {{ $color }}"></span>
                    <h2 class="relative font-display text-[2rem] font-semibold leading-tight sm:text-4xl">{{ $branch->name }}</h2>
                    <dl class="relative mt-5 space-y-4">
                        <div class="flex gap-3">
                            <dt><span class="sr-only">Address</span><x-icon name="map-pin" class="mt-0.5 size-5" style="color: {{ $color }}" /></dt>
                            <dd class="leading-relaxed">{{ $branch->address }}@if ($branch->address_note)<span class="block text-sm font-bold text-ink-soft">{{ $branch->address_note }}</span>@endif</dd>
                        </div>
                        @if ($branch->working_hours)
                            <div class="flex gap-3"><dt><span class="sr-only">Hours</span><x-icon name="clock" class="mt-0.5 size-5" style="color: {{ $color }}" /></dt><dd>{{ $branch->working_hours }}</dd></div>
                        @endif
                        <div class="flex gap-3"><dt><span class="sr-only">Phone</span><x-icon name="phone" class="mt-0.5 size-5" style="color: {{ $color }}" /></dt><dd><a href="{{ $branch->telLink() }}" class="font-bold hover:underline" data-track-label="Call {{ $branch->name }}">{{ $branch->phone }}</a></dd></div>
                        @if ($branch->email)
                            <div class="flex gap-3"><dt><span class="sr-only">Email</span><x-icon name="mail" class="mt-0.5 size-5" style="color: {{ $color }}" /></dt><dd><a href="mailto:{{ $branch->email }}" class="break-all hover:underline" data-track-label="Email {{ $branch->name }}">{{ $branch->email }}</a></dd></div>
                        @endif
                    </dl>
                    <div class="relative mt-7 grid gap-2 sm:grid-cols-3">
                        <a href="{{ $branch->telLink() }}" class="btn btn-primary" data-track-label="Call {{ $branch->name }}"><x-icon name="phone" class="size-4" /> Call</a>
                        <a href="{{ $branch->whatsappLink('Hello Marshmallow '.$branch->name.', I would like to book a visit.') }}" target="_blank" rel="noopener" class="btn btn-soft" data-track-label="WhatsApp {{ $branch->name }}"><x-icon name="whatsapp" class="size-4 text-lime-700" /> WhatsApp</a>
                        @if ($branch->map_url)
                            <a href="{{ $branch->map_url }}" target="_blank" rel="noopener" class="btn btn-soft" data-track-label="Directions {{ $branch->name }}"><x-icon name="map-pin" class="size-4 text-teal-700" /> Directions</a>
                        @endif
                    </div>
                    <a href="{{ route('enroll', ['branch' => $branch->slug, 'interest' => 'tour']) }}" class="btn btn-outline relative mt-3 w-full" data-track="cta_click" data-track-label="Branches page – Book a visit at {{ $branch->name }}">Book a visit at {{ $branch->name }}</a>
                </div>

                <div class="overflow-hidden rounded-[2rem] border-2 border-line bg-blush">
                    @if ($branch->map_embed_url)
                        <iframe src="{{ $branch->map_embed_url }}" title="Map of Marshmallow {{ $branch->name }}" loading="lazy" referrerpolicy="no-referrer-when-downgrade" class="block aspect-[4/3] w-full border-0 lg:aspect-[5/4]" allowfullscreen></iframe>
                    @else
                        <x-site.photo :color="$color" icon="map-pin" ratio="4/3" rounded="rounded-none" :alt="'Map of '.$branch->name" />
                    @endif
                </div>
            </div>
        </section>
    @endforeach
@endsection
