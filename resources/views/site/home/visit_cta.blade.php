<section id="book" class="bg-ink py-16 text-white sm:py-24">
    <div class="mx-auto grid max-w-6xl gap-10 px-5 sm:px-8 lg:grid-cols-[1fr_1.05fr] lg:gap-16">
        <div>
            <h2 class="font-display text-[2rem] font-semibold leading-[1.1] sm:text-[2.6rem]">
                {{ $section->title ?: 'Come and see it for yourself' }}
            </h2>
            @if ($section->subtitle)
                <p class="mt-4 max-w-md text-lg leading-relaxed text-white/80">{{ $section->subtitle }}</p>
            @endif

            @if (setting('tour_hours'))
                <p class="mt-5 inline-flex items-start gap-2.5 rounded-2xl bg-white/10 px-4 py-3 text-[0.95rem] leading-snug text-white/85">
                    <x-icon name="clock" class="mt-0.5 size-4 shrink-0 text-sun" /> {{ setting('tour_hours') }}
                </p>
            @endif

            <ul class="mt-8 space-y-4">
                @foreach ($branches as $branch)
                    <li class="rounded-2xl border-2 border-white/15 p-4">
                        <p class="font-display text-lg font-medium">{{ $branch->name }}</p>
                        <p class="mt-1 text-sm leading-snug text-white/70">{{ $branch->address }}</p>
                        <p class="mt-3 flex flex-wrap gap-2">
                            <a href="{{ $branch->telLink() }}" class="inline-flex items-center gap-2 rounded-full bg-white px-4 py-2 font-bold text-ink hover:bg-sun"
                               data-track-label="Call {{ $branch->name }}">
                                <x-icon name="phone" class="size-4" /> {{ $branch->phone }}
                            </a>
                            <a href="{{ $branch->whatsappLink('Hello Marshmallow, I would like to book a visit.') }}" target="_blank" rel="noopener"
                               class="inline-flex items-center gap-2 rounded-full border-2 border-white/30 px-4 py-2 font-bold hover:bg-white/10"
                               data-track-label="WhatsApp {{ $branch->name }}">
                                <x-icon name="whatsapp" class="size-4" /> WhatsApp
                            </a>
                            @if ($branch->map_url)
                                <a href="{{ $branch->map_url }}" target="_blank" rel="noopener"
                                   class="inline-flex items-center gap-2 rounded-full border-2 border-white/30 px-4 py-2 font-bold hover:bg-white/10"
                                   data-track-label="Map {{ $branch->name }}">
                                    <x-icon name="map-pin" class="size-4" /> Directions
                                </a>
                            @endif
                        </p>
                    </li>
                @endforeach
            </ul>
        </div>

        <div class="rounded-[1.75rem] bg-white p-6 text-ink sm:p-8">
            <p class="font-display text-xl font-medium">Book your visit</p>
            <p class="mt-1 text-ink-soft">Leave your number and we’ll call you back within one working day.</p>
            <div class="mt-6">
                @include('site.partials.compact-enroll', ['branches' => $branches, 'place' => 'Homepage – book a visit'])
            </div>
        </div>
    </div>
</section>
