<section class="{{ $tint ? 'bg-blush' : 'bg-white' }} py-16 sm:py-24">
    <div class="mx-auto max-w-6xl px-5 sm:px-8">
        <x-site.section-head :title="$section->title" :subtitle="$section->subtitle" align="center" />

        <ul class="-mx-5 mt-10 flex snap-x snap-mandatory gap-4 overflow-x-auto px-5 pb-3 sm:mx-0 sm:grid sm:grid-cols-2 sm:overflow-visible sm:px-0 sm:pb-0 lg:grid-cols-3">
            @foreach ($trust as $item)
                @php $color = $item->color ?: '#E8177F'; @endphp
                <li class="flex h-full w-[82%] shrink-0 snap-start gap-4 sm:w-auto rounded-[1.5rem] bg-white p-5 shadow-[0_0_0_2px_var(--tw-shadow-color)] shadow-line-soft">
                    <span class="grid size-11 shrink-0 place-items-center rounded-xl" style="background: color-mix(in srgb, {{ $color }} 14%, #fff); color: {{ $color }};">
                        <x-icon :name="$item->icon" class="size-5" />
                    </span>
                    <span>
                        <span class="block font-display text-[1.1rem] font-medium leading-snug">{{ $item->title }}</span>
                        @if ($item->description)
                            <span class="mt-1 block text-[0.95rem] leading-relaxed text-ink-soft">{{ $item->description }}</span>
                        @endif
                    </span>
                </li>
            @endforeach
        </ul>

        <div class="mt-8 flex flex-wrap items-center justify-center gap-x-8 gap-y-3 text-center">
            @if (setting('working_days') && setting('working_hours'))
                <p class="font-bold"><span class="text-ink-soft">Open</span> {{ setting('working_days') }} · {{ setting('working_hours') }}</p>
            @endif
            @if (setting('after_school'))
                <p class="font-bold text-ink-soft">{{ setting('after_school') }}</p>
            @endif
            @if ($section->button_text)
                <a href="{{ url($section->button_url ?: route('safety')) }}" class="link font-bold"
                   data-track="cta_click" data-track-label="Safety – {{ $section->button_text }}">{{ $section->button_text }}</a>
            @endif
        </div>
    </div>
</section>
