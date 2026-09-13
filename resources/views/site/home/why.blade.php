@if ($why->isNotEmpty())
    <section class="{{ $tint ? 'bg-blush' : 'bg-white' }} py-16 sm:py-24">
        <div class="mx-auto grid max-w-6xl gap-10 px-5 sm:px-8 lg:grid-cols-[0.75fr_1.25fr] lg:gap-16">
            <div class="self-start lg:sticky lg:top-32">
                <x-site.section-head :title="$section->title" :subtitle="$section->subtitle" />
                @if ($section->body)
                    <div class="prose-mm mt-4 text-ink-soft">{!! nl2br(e($section->body)) !!}</div>
                @endif
                @if ($section->button_text && $section->button_url)
                    <a href="{{ url($section->button_url) }}" class="btn btn-outline mt-6" data-track="cta_click" data-track-label="Why – {{ $section->button_text }}">{{ $section->button_text }}</a>
                @endif
            </div>
            <ul class="grid gap-x-10 gap-y-9 sm:grid-cols-2">
                @foreach ($why as $item)
                    @php $color = $item->color ?: '#E8177F'; @endphp
                    <li class="flex gap-4">
                        <span class="grid size-[3.25rem] shrink-0 place-items-center rounded-[1.1rem]" style="background: color-mix(in srgb, {{ $color }} 14%, #fff); color: {{ $color }};">
                            <x-icon :name="$item->icon" class="size-6" />
                        </span>
                        <div>
                            <h3 class="font-display text-xl font-semibold leading-snug">{{ $item->title }}</h3>
                            @if ($item->description)
                                <p class="mt-1.5 leading-relaxed text-ink-soft">{{ $item->description }}</p>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    </section>
@endif
