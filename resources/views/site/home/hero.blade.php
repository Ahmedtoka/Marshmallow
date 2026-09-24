@php
    $slides = $heroPhotos->map(fn ($photo) => [
        'src' => $photo->url(),
        'alt' => $photo->alt ?: 'Children at Marshmallow Nursery',
        'caption' => $photo->photoable?->title,
    ])->values();
@endphp
<section class="relative overflow-hidden bg-ink">
    <div class="relative" x-data="{
            i: 0,
            count: {{ max(1, $slides->count()) }},
            timer: null,
            start() {
                if (window.matchMedia('(prefers-reduced-motion: reduce)').matches || this.count < 2) return;
                this.timer = setInterval(() => this.i = (this.i + 1) % this.count, 5000);
            },
            stop() { clearInterval(this.timer); },
        }" x-init="start()" @mouseenter="stop()" @mouseleave="start()">

        {{-- Slides --}}
        <div class="relative h-[62vh] min-h-[26rem] w-full sm:h-[70vh] lg:h-[78vh]">
            @forelse ($slides as $index => $slide)
                <img src="{{ $slide['src'] }}" alt="{{ $slide['alt'] }}"
                     @class(['absolute inset-0 size-full object-cover transition-opacity duration-700'])
                     x-show="i === {{ $index }}" x-transition:enter="transition-opacity duration-700"
                     x-transition:enter-start="opacity-0" x-transition:leave="transition-opacity duration-700"
                     x-transition:leave-end="opacity-0" x-cloak="{{ $index > 0 ? 'true' : 'false' }}"
                     @if ($index === 0) fetchpriority="high" @else loading="lazy" @endif>
            @empty
                <div class="absolute inset-0 bg-gradient-to-br from-pink-600 via-grape to-teal"></div>
            @endforelse
            <div aria-hidden="true" class="absolute inset-0 bg-gradient-to-t from-ink/90 via-ink/55 to-ink/20"></div>
        </div>

        {{-- Words on top --}}
        <div class="absolute inset-x-0 bottom-0">
            <div class="mx-auto max-w-6xl px-5 pb-8 sm:px-8 sm:pb-12 lg:pb-16">
                @if (setting('admissions_open') === '1' && setting('admissions_label'))
                    <p class="inline-flex items-center gap-2 rounded-full bg-sun px-3.5 py-1 text-sm font-bold text-ink">
                        <span class="size-2 rounded-full bg-pink-600" aria-hidden="true"></span>
                        {{ setting('admissions_label') }}
                    </p>
                @endif

                <h1 class="mt-4 max-w-3xl font-display text-[2.6rem] font-semibold leading-[1.02] text-white sm:text-6xl lg:text-[4.2rem]">
                    {{ $section->title ?: 'Where little ones learn by playing' }}
                </h1>

                @if ($section->subtitle)
                    <p class="mt-4 max-w-xl text-lg leading-relaxed text-white/85 sm:text-xl">{{ $section->subtitle }}</p>
                @endif

                <div class="mt-7 flex flex-wrap items-center gap-3">
                    <a href="{{ url($section->button_url ?: route('enroll')) }}" class="btn btn-primary px-7 text-lg"
                       data-track="cta_click" data-track-label="Hero – {{ $section->button_text ?: 'Book a visit' }}">
                        {{ $section->button_text ?: 'Book a visit' }}
                    </a>
                    @foreach ($branches as $branch)
                        <a href="{{ $branch->telLink() }}" class="inline-flex items-center gap-2 rounded-full border-2 border-white/35 px-4 py-2.5 font-bold text-white hover:bg-white/10"
                           data-track-label="Call {{ $branch->name }}">
                            <x-icon name="phone" class="size-4" />
                            <span class="text-white/70">{{ $branch->short_name ?: $branch->name }}</span> {{ $branch->phone }}
                        </a>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Dots --}}
        @if ($slides->count() > 1)
            <div class="absolute right-5 top-5 flex gap-2 sm:right-8 sm:top-8">
                @foreach ($slides as $index => $slide)
                    <button type="button" @click="i = {{ $index }}" :class="i === {{ $index }} ? 'w-7 bg-white' : 'w-2.5 bg-white/50'"
                            class="h-2.5 rounded-full transition-all hover:bg-white" aria-label="Photo {{ $index + 1 }}"></button>
                @endforeach
            </div>
        @endif
    </div>

    {{-- The three numbers a parent wants before reading anything --}}
    <div class="border-t-2 border-white/10">
        <dl class="mx-auto grid max-w-6xl grid-cols-3 divide-x-2 divide-white/10 px-5 sm:px-8">
            @foreach ([
                [setting('years_experience'), 'years in Giza'],
                [setting('recommend_percent') ? setting('recommend_percent').'%' : null, 'of parents recommend us'],
                [$branches->count() ?: null, \Illuminate\Support\Str::plural('branch', $branches->count()).' · '.$classrooms->count().' classes'],
            ] as [$value, $label])
                @if ($value)
                    <div class="px-2 py-5 text-center sm:py-7">
                        <dd class="font-display text-3xl font-semibold text-sun sm:text-5xl">{{ $value }}</dd>
                        <dt class="mt-1 text-xs font-bold text-white/70 sm:text-sm">{{ $label }}</dt>
                    </div>
                @endif
            @endforeach
        </dl>
    </div>
</section>
