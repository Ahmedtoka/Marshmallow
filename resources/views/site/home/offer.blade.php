@php $items = $offer->take(15); @endphp
<section class="{{ $tint ? 'bg-blush' : 'bg-white' }} py-16 sm:py-24">
    <div class="mx-auto max-w-6xl px-5 sm:px-8">
        <x-site.section-head :title="$section->title" :subtitle="$section->subtitle" align="center" />

        {{-- A swipeable row on phones, five across from large screens up. --}}
        <ul class="-mx-5 mt-10 flex snap-x snap-mandatory gap-3 overflow-x-auto px-5 pb-3 sm:mx-0 sm:grid sm:grid-cols-3 sm:gap-5 sm:overflow-visible sm:px-0 sm:pb-0 lg:grid-cols-5">
            @foreach ($items as $item)
                @php $color = $item->activity->color ?: '#E8177F'; @endphp
                <li class="w-[42vw] min-w-[9.5rem] shrink-0 snap-start sm:w-auto sm:min-w-0">
                    <a href="{{ route('activities.show', $item->activity) }}" class="group block"
                       data-track="cta_click" data-track-label="What we do – {{ $item->activity->name }}">
                        <x-site.photo :src="thumb_url($item->photo, 520)" :alt="$item->activity->name" ratio="1/1"
                                      :color="$color" :icon="$item->activity->icon" rounded="rounded-[1.35rem]"
                                      class="transition-transform duration-300 group-hover:-translate-y-1" />
                        <p class="mt-2.5 flex items-center gap-2 px-0.5 font-display text-[1.02rem] font-medium leading-tight">
                            <span class="size-2.5 shrink-0 rounded-full" style="background: {{ $color }}"></span>
                            {{ $item->activity->name }}
                        </p>
                    </a>
                </li>
            @endforeach
        </ul>

        @if ($camps->isNotEmpty())
            <div class="mt-10 flex flex-wrap items-center justify-between gap-5 rounded-[1.75rem] border-2 border-dashed border-teal/50 bg-teal/5 px-6 py-6 sm:px-8">
                <div>
                    <p class="font-display text-xl font-medium">And in every school holiday: our camps</p>
                    <p class="mt-1 text-ink-soft">
                        {{ $camps->pluck('title')->join(' · ') }} — ages {{ $camps->min('age_from') }}–{{ $camps->max('age_to') }}, with meals included.
                    </p>
                </div>
                <a href="{{ route('camps.index') }}" class="btn btn-outline" data-track="cta_click" data-track-label="What we do – Camps">See the camps</a>
            </div>
        @endif
    </div>
</section>
