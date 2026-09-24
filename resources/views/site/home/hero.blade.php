@php
    // 20 tiles on a large screen, 15 on a tablet, 10 on a phone — the spans below keep every grid
    // exactly full at each size. The rest of the photos are the pool the wall keeps swapping in.
    $pool = $heroPhotos->map(fn ($photo) => thumb_url($photo->path, 520))->filter()->values();
    $tiles = 20;
    $big = [0 => 'col-span-2 row-span-2', 5 => 'sm:col-span-2 sm:row-span-2', 10 => 'sm:col-span-2 sm:row-span-2', 15 => 'lg:col-span-2 lg:row-span-2'];
@endphp
<section class="relative isolate overflow-hidden bg-ink">
    @if ($pool->isNotEmpty())
        <div class="absolute inset-0" x-data="heroMosaic(@js($pool), {{ $tiles }})" aria-hidden="true">
            <div class="grid h-full grid-cols-3 grid-rows-4 gap-1.5 p-1.5 sm:grid-cols-6 lg:grid-cols-8">
                @for ($i = 0; $i < $tiles; $i++)
                    <div @class([
                            'relative overflow-hidden rounded-[0.85rem]',
                            $big[$i] ?? '',
                            'hidden sm:block' => $i >= 9 && $i < 15,
                            'hidden lg:block' => $i >= 15,
                        ])>
                        <img :src="current[{{ $i }}]" alt="" loading="{{ $i < 6 ? 'eager' : 'lazy' }}"
                             class="absolute inset-0 size-full object-cover transition-all duration-700 ease-out"
                             :class="swapping[{{ $i }}] ? '{{ $i % 2 ? '-translate-y-full' : 'opacity-0' }}' : ''">
                        <img :src="next[{{ $i }}]" alt="" loading="lazy"
                             class="absolute inset-0 size-full object-cover transition-all duration-700 ease-out"
                             :class="swapping[{{ $i }}] ? 'translate-y-0 opacity-100' : '{{ $i % 2 ? 'translate-y-full' : 'opacity-0' }}'">
                    </div>
                @endfor
            </div>
            {{-- A light veil over the whole wall, then a stronger wash only where the words sit. --}}
            <div class="absolute inset-0 bg-ink/25"></div>
            <div class="absolute inset-0 bg-gradient-to-t from-ink via-ink/70 to-transparent sm:bg-gradient-to-r sm:from-ink sm:via-ink/55 sm:to-transparent"></div>
        </div>
    @else
        <div aria-hidden="true" class="absolute inset-0 bg-gradient-to-br from-pink-600 via-grape to-teal"></div>
    @endif

    <div class="relative mx-auto flex min-h-[32rem] max-w-6xl flex-col justify-end px-5 pb-10 pt-24 sm:min-h-[36rem] sm:px-8 sm:pb-14 sm:pt-28 lg:min-h-[41rem]">
        <div class="max-w-2xl">
            @if (setting('admissions_open') === '1' && setting('admissions_label'))
                <p class="inline-flex items-center gap-2 rounded-full bg-sun px-3.5 py-1 text-sm font-bold text-ink">
                    <span class="size-2 rounded-full bg-pink-600" aria-hidden="true"></span>
                    {{ setting('admissions_label') }}
                </p>
            @endif

            <h1 class="mt-4 font-display text-[2.6rem] font-semibold leading-[1.02] text-white sm:text-6xl lg:text-[4.2rem]">
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
                    <a href="{{ $branch->telLink() }}" class="inline-flex items-center gap-2 rounded-full border-2 border-white/35 px-4 py-2.5 font-bold text-white backdrop-blur-sm hover:bg-white/10"
                       data-track-label="Call {{ $branch->name }}">
                        <x-icon name="phone" class="size-4" />
                        <span class="text-white/70">{{ $branch->short_name ?: $branch->name }}</span> {{ $branch->phone }}
                    </a>
                @endforeach
            </div>
        </div>
    </div>

    {{-- The three numbers a parent wants before reading anything --}}
    <div class="relative border-t-2 border-white/10 bg-ink/80 backdrop-blur-sm">
        <dl class="mx-auto grid max-w-6xl grid-cols-3 divide-x-2 divide-white/10 px-5 sm:px-8">
            @foreach ([
                [setting('years_experience'), 'years in Giza'],
                [setting('recommend_percent') ? setting('recommend_percent').'%' : null, 'of parents recommend us'],
                [$classrooms->count() ?: null, 'classes by age'],
            ] as [$value, $label])
                @if ($value)
                    <div class="px-2 pb-7 pt-5 text-center sm:py-7">
                        <dd class="font-display text-3xl font-semibold text-sun sm:text-5xl">{{ $value }}</dd>
                        <dt class="mt-1 text-xs font-bold text-white/70 sm:text-sm">{{ $label }}</dt>
                    </div>
                @endif
            @endforeach
        </dl>
    </div>
</section>
