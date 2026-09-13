@if ($activityGroups->isNotEmpty())
    <section class="{{ $tint ? 'bg-blush' : 'bg-white' }} py-16 sm:py-24">
        <div class="mx-auto grid max-w-6xl gap-10 px-5 sm:px-8 lg:grid-cols-[0.75fr_1.25fr] lg:gap-16">
            <div class="self-start lg:sticky lg:top-32">
                <x-site.section-head :title="$section->title" :subtitle="$section->subtitle" />
                @if ($section->button_text)
                    <a href="{{ url($section->button_url ?: route('activities.index')) }}" class="btn btn-outline mt-6" data-track="cta_click" data-track-label="Activities – {{ $section->button_text }}">{{ $section->button_text }}</a>
                @endif
            </div>
            <div class="divide-y-2 divide-line-soft">
                @foreach (\App\Models\Activity::CATEGORIES as $key => $label)
                    @continue(! $activityGroups->has($key))
                    <div class="flex flex-col gap-3 py-5 first:pt-0 sm:flex-row sm:gap-6">
                        <h3 class="shrink-0 font-display text-lg font-semibold sm:w-44 sm:pt-1.5">{{ $label }}</h3>
                        <ul class="flex flex-wrap gap-2">
                            @foreach ($activityGroups[$key] as $activity)
                                @php $color = $activity->color ?: '#E8177F'; @endphp
                                <li>
                                    <a href="{{ route('activities.show', $activity) }}" class="chip bg-white transition-colors hover:bg-blush" style="--chip: color-mix(in srgb, {{ $color }} 35%, #fff);">
                                        <x-icon :name="$activity->icon" class="size-4" style="color: {{ $color }}" />
                                        {{ $activity->name }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif
