<section class="{{ $tint ? 'bg-blush' : 'bg-white' }} py-16 sm:py-24">
    <div class="mx-auto max-w-6xl px-5 sm:px-8">
        <x-site.section-head :title="$section->title" :subtitle="$section->subtitle" align="center" />

        <ul class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($classrooms as $classroom)
                @php $color = $classroom->color ?: '#E8177F'; @endphp
                <li>
                    <a href="{{ route('classes.show', $classroom) }}"
                       class="group flex h-full items-start gap-4 rounded-[1.6rem] border-2 border-line-soft bg-white p-4 transition-colors hover:border-transparent sm:p-5"
                       style="--c: {{ $color }}" onmouseover="this.style.borderColor='{{ $color }}'" onmouseout="this.style.borderColor=''"
                       data-track="cta_click" data-track-label="Classes – {{ $classroom->name }}">
                        <span class="grid size-16 shrink-0 place-items-center rounded-[1.1rem]" style="background: color-mix(in srgb, {{ $color }} 12%, #fff);">
                            <x-site.candy :name="$classroom->icon" :color="$color" class="size-12" :title="$classroom->name" />
                        </span>
                        <span class="min-w-0">
                            <span class="block font-display text-[1.35rem] font-semibold leading-tight" style="color: {{ $color }}">{{ $classroom->name }}</span>
                            <span class="mt-0.5 block text-sm font-bold text-ink-soft">{{ $classroom->ageRangeLabel() }}</span>
                            @if ($classroom->tagline)
                                <span class="mt-2 block text-[0.95rem] leading-snug text-ink-soft">{{ $classroom->tagline }}</span>
                            @endif
                        </span>
                    </a>
                </li>
            @endforeach
        </ul>

        {{-- The finder belongs here: the parent has just met the classes and wants to know which one is theirs. --}}
        <div class="mt-12 grid gap-8 lg:grid-cols-[1fr_1.15fr] lg:items-center">
            <div class="hidden justify-center lg:flex">
                <x-site.mascot class="w-64 xl:w-72" />
            </div>
            <div class="mm-bubble px-6 py-7 sm:px-9 sm:py-9">
                <x-site.class-finder :config="$finderConfig" id="home-finder" place="Homepage class finder"
                    title="Which class will your child join?"
                    subtitle="Enter your child’s birthday and we’ll show you their class, what their day looks like, and how to book a visit." />
                <x-site.bubble-tail side="left" class="hidden lg:block" />
            </div>
        </div>

        @if ($section->button_text)
            <p class="mt-10 text-center">
                <a href="{{ url($section->button_url ?: route('classes.index')) }}" class="btn btn-outline"
                   data-track="cta_click" data-track-label="Classes – {{ $section->button_text }}">{{ $section->button_text }}</a>
            </p>
        @endif
    </div>
</section>
