@if ($camps->isNotEmpty())
    @php
        $seasonColors = ['summer' => '#E8A317', 'winter' => '#2CBCC9', 'spring' => '#7FA82A', 'autumn' => '#E8177F'];
        $featured = $camps->first();
        $others = $camps->slice(1)->take(3);
    @endphp
    <section class="{{ $tint ? 'bg-blush' : 'bg-white' }} py-16 sm:py-24">
        <div class="mx-auto max-w-6xl px-5 sm:px-8">
            <div class="flex flex-wrap items-end justify-between gap-5">
                <x-site.section-head :title="$section->title" :subtitle="$section->subtitle" />
                @if ($section->button_text)
                    <a href="{{ url($section->button_url ?: route('camps.index')) }}" class="btn btn-outline" data-track="cta_click" data-track-label="Camps – {{ $section->button_text }}">{{ $section->button_text }}</a>
                @endif
            </div>

            <div class="mt-10 grid gap-5 lg:grid-cols-[1.35fr_1fr]">
                @php $color = $seasonColors[$featured->season] ?? '#E8177F'; @endphp
                <a href="{{ route('camps.show', $featured) }}" class="group grid overflow-hidden rounded-[2rem] border-2 border-line bg-white transition-colors hover:border-pink-200 sm:grid-cols-[1fr_1.1fr]" data-track="cta_click" data-track-label="Camps – {{ $featured->title }}">
                    <x-site.photo :src="media_url($featured->cover_image) ?? $featured->photos->first()?->url()" :alt="$featured->title" ratio="4/3" :color="$color" icon="sun" rounded="rounded-none" class="h-full sm:aspect-auto!" />
                    <div class="p-6 sm:p-7">
                        @if ($featured->badge)
                            <span class="inline-block rounded-full bg-sun px-3 py-1 text-sm font-bold">{{ $featured->badge }}</span>
                        @endif
                        <h3 class="mt-3 font-display text-2xl font-semibold leading-tight group-hover:underline sm:text-[1.7rem]">{{ $featured->title }}</h3>
                        <ul class="mt-3 space-y-1 text-[0.95rem] font-bold text-ink-soft">
                            <li class="flex items-center gap-2"><x-icon name="users" class="size-4" style="color: {{ $color }}" /> Ages {{ $featured->age_from }} to {{ $featured->age_to }}</li>
                            @if ($featured->datesLabel())
                                <li class="flex items-center gap-2"><x-icon name="calendar" class="size-4" style="color: {{ $color }}" /> {{ $featured->datesLabel() }}</li>
                            @elseif ($featured->schedule)
                                <li class="flex items-center gap-2"><x-icon name="calendar" class="size-4" style="color: {{ $color }}" /> {{ $featured->schedule }}</li>
                            @endif
                            @if ($featured->meals)
                                <li class="flex items-center gap-2"><x-icon name="apple" class="size-4" style="color: {{ $color }}" /> {{ $featured->meals }}</li>
                            @endif
                        </ul>
                        @if ($featured->summary)
                            <p class="mt-3 leading-relaxed text-ink-soft">{{ $featured->summary }}</p>
                        @endif
                        @if (! empty($featured->activities))
                            <ul class="mt-4 flex flex-wrap gap-1.5">
                                @foreach (array_slice($featured->activities, 0, 4) as $name)
                                    <li class="rounded-full bg-blush px-2.5 py-1 text-sm font-bold">{{ $name }}</li>
                                @endforeach
                                @if (count($featured->activities) > 4)
                                    <li class="px-1 py-1 text-sm font-bold text-ink-muted">and {{ count($featured->activities) - 4 }} more</li>
                                @endif
                            </ul>
                        @endif
                    </div>
                </a>

                @if ($others->isNotEmpty())
                    <ul class="grid content-start gap-4">
                        @foreach ($others as $camp)
                            @php $c = $seasonColors[$camp->season] ?? '#E8177F'; @endphp
                            <li>
                                <a href="{{ route('camps.show', $camp) }}" class="group flex items-center gap-4 rounded-[1.5rem] border-2 border-line bg-white p-3 pr-5 transition-colors hover:border-pink-200" data-track="cta_click" data-track-label="Camps – {{ $camp->title }}">
                                    <x-site.photo :src="media_url($camp->cover_image) ?? $camp->photos->first()?->url()" :alt="$camp->title" ratio="1/1" :color="$c" icon="sun" rounded="rounded-[1.1rem]" class="w-24 shrink-0 sm:w-28" />
                                    <div class="min-w-0">
                                        @if ($camp->badge)
                                            <span class="inline-block rounded-full bg-sun-100 px-2.5 py-0.5 text-xs font-bold">{{ $camp->badge }}</span>
                                        @endif
                                        <h3 class="mt-1 font-display text-xl font-semibold leading-tight group-hover:underline">{{ $camp->title }}</h3>
                                        <p class="mt-1 text-sm font-bold text-ink-soft">Ages {{ $camp->age_from }} to {{ $camp->age_to }}</p>
                                        @if ($camp->summary)
                                            <p class="mt-1 line-clamp-2 text-sm text-ink-soft">{{ $camp->summary }}</p>
                                        @endif
                                    </div>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </section>
@endif
