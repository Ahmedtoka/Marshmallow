@if ($safety->isNotEmpty() || $meals->isNotEmpty())
    <section class="{{ $tint ? 'bg-blush' : 'bg-white' }} py-16 sm:py-24">
        <div class="mx-auto grid max-w-6xl items-start gap-10 px-5 sm:px-8 lg:grid-cols-2 lg:gap-16">
            <div>
                <x-site.section-head :title="$section->title" :subtitle="$section->subtitle" />
                @if ($section->button_text)
                    <a href="{{ url($section->button_url ?: route('safety')) }}" class="btn btn-outline mt-6" data-track="cta_click" data-track-label="Safety – {{ $section->button_text }}">{{ $section->button_text }}</a>
                @endif

                @if ($meals->isNotEmpty())
                    <div class="mt-10 rounded-[1.75rem] bg-sun-100 p-5 sm:p-6">
                        <h3 class="font-display text-xl font-semibold">{{ $meals->count() === 4 ? 'Four fresh meals a day' : 'Fresh meals every day' }}</h3>
                        <ol class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-4">
                            @foreach ($meals as $meal)
                                <li class="rounded-2xl bg-white p-3">
                                    <x-icon :name="$meal->icon" class="size-6" style="color: {{ $meal->color ?: '#E8177F' }}" />
                                    <p class="mt-2 font-bold leading-tight">{{ $meal->title }}</p>
                                    @if ($meal->description)
                                        <p class="mt-0.5 text-sm leading-snug text-ink-soft">{{ $meal->description }}</p>
                                    @endif
                                </li>
                            @endforeach
                        </ol>
                    </div>
                @endif
            </div>

            @if ($safety->isNotEmpty())
                <ul class="divide-y-2 divide-line-soft rounded-[2rem] border-2 border-line bg-white">
                    @foreach ($safety as $item)
                        @php $color = $item->color ?: '#8479BD'; @endphp
                        <li class="flex gap-4 p-5 sm:p-6">
                            <span class="grid size-12 shrink-0 place-items-center rounded-full" style="background: color-mix(in srgb, {{ $color }} 14%, #fff); color: {{ $color }};">
                                <x-icon :name="$item->icon" class="size-6" />
                            </span>
                            <div>
                                <h3 class="font-display text-lg font-semibold leading-snug">{{ $item->title }}</h3>
                                @if ($item->description)
                                    <p class="mt-1 leading-relaxed text-ink-soft">{{ $item->description }}</p>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </section>
@endif
