@php
    $albumColors = ['activities' => '#E8177F', 'celebrations' => '#8479BD', 'graduation' => '#2CBCC9', 'trips' => '#7FA82A', 'camps' => '#E8A317', 'campus' => '#C0479A'];
    $albumIcons = ['activities' => 'blocks', 'celebrations' => 'balloon', 'graduation' => 'medal', 'trips' => 'bus', 'camps' => 'sun', 'campus' => 'leaf'];
    $color = $albumColors[$album->category] ?? '#E8177F';
    $cover = media_url($album->cover_image) ?? $album->photos->first()?->url();
    $large = $large ?? false;
@endphp
<a href="{{ route('gallery.show', $album) }}" class="group relative block h-full" data-track="cta_click" data-track-label="{{ $place ?? 'Gallery' }} – {{ $album->title }}">
    <x-site.photo :src="$cover" :alt="$album->title" ratio="1/1" :color="$color" :icon="$albumIcons[$album->category] ?? 'camera'" :rounded="$large ? 'rounded-[2rem]' : 'rounded-[1.4rem]'" class="h-full transition-transform duration-300 group-hover:scale-[0.985]" />
    <span class="absolute inset-x-2.5 bottom-2.5 rounded-[1rem] bg-white/95 px-3 py-2 sm:inset-x-3 sm:bottom-3 {{ $large ? 'sm:px-4 sm:py-3' : '' }}">
        <span class="block font-display font-semibold leading-tight group-hover:underline {{ $large ? 'text-lg sm:text-2xl' : 'text-[0.95rem] sm:text-base' }}">{{ $album->title }}</span>
        <span class="mt-0.5 block text-xs font-bold text-ink-soft sm:text-sm">
            {{ $album->categoryLabel() }}@if ($album->photos_count ?? null), {{ $album->photos_count }} {{ \Illuminate\Support\Str::plural('photo', $album->photos_count) }}@endif
        </span>
    </span>
</a>
