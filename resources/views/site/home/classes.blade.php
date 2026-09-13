@if ($classrooms->isNotEmpty())
    <section class="{{ $tint ? 'bg-blush' : 'bg-white' }} overflow-hidden py-16 sm:py-24">
        <div class="mx-auto max-w-6xl px-5 sm:px-8">
            <div class="flex flex-wrap items-end justify-between gap-5">
                <x-site.section-head :title="$section->title" :subtitle="$section->subtitle" />
                @if ($section->button_text)
                    <a href="{{ url($section->button_url ?: route('classes.index')) }}" class="btn btn-outline" data-track="cta_click" data-track-label="Classes – {{ $section->button_text }}">{{ $section->button_text }}</a>
                @endif
            </div>

            {{-- A growing path: each class stands a little taller than the one before --}}
            <div class="relative mt-10">
                <ol class="-mx-5 flex snap-x snap-mandatory items-stretch gap-3 overflow-x-auto px-5 pb-2 scrollbar-none sm:-mx-8 sm:px-8 lg:mx-0 lg:grid lg:grid-cols-6 lg:gap-4 lg:overflow-visible lg:px-0">
                    @foreach ($classrooms as $i => $class)
                        <li class="flex w-[44%] min-w-[9.5rem] shrink-0 snap-start sm:w-[30%] lg:w-auto">
                            <a href="{{ route('classes.show', $class) }}" class="group flex w-full flex-col items-center text-center" data-track="cta_click" data-track-label="Classes path – {{ $class->name }}">
                                <span class="grid size-20 place-items-center rounded-full bg-white transition-transform duration-200 group-hover:-translate-y-1" style="box-shadow: 0 0 0 3px color-mix(in srgb, {{ $class->color }} 22%, #fff);">
                                    <x-site.candy :name="$class->icon" :color="$class->color" class="size-14" />
                                </span>
                                <h3 class="mt-3 font-display text-xl font-semibold leading-tight group-hover:underline" style="color: color-mix(in srgb, {{ $class->color }} 75%, #33307A);">{{ $class->name }}</h3>
                                <p class="mt-0.5 text-sm font-bold">{{ $class->ageRangeLabel() }}</p>
                                @if ($class->tagline)
                                    <p class="mt-1.5 line-clamp-3 px-1 text-sm leading-snug text-ink-soft">{{ $class->tagline }}</p>
                                @endif
                                <span class="mt-auto block w-full pt-4" aria-hidden="true">
                                    <span class="block w-full rounded-t-[1.25rem] border-2 border-b-0" style="height: {{ 2.5 + $i * 1.35 }}rem; background: color-mix(in srgb, {{ $class->color }} 16%, #fff); border-color: color-mix(in srgb, {{ $class->color }} 38%, #fff);"></span>
                                </span>
                            </a>
                        </li>
                    @endforeach
                </ol>
                <div class="h-1 rounded-full bg-line" aria-hidden="true"></div>
                <p class="mt-3 flex items-center justify-between text-sm font-bold text-ink-muted" aria-hidden="true">
                    <span>{{ $classrooms->first()->ageRangeLabel() }}</span>
                    <span class="hidden sm:inline">Growing up with Marshmallow</span>
                    <span class="lg:hidden">Swipe to see all six</span>
                    <span class="hidden lg:inline">School age</span>
                </p>
            </div>
        </div>
    </section>
@endif
