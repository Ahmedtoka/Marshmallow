@props([
    'src' => null,
    'alt' => '',
    'ratio' => '4/3',
    'color' => '#E8177F',
    'icon' => 'camera',
    'label' => null,
    'rounded' => 'rounded-[1.5rem]',
    'eager' => false,
])
<div {{ $attributes->merge(['class' => "relative overflow-hidden {$rounded}"]) }} style="aspect-ratio: {{ $ratio }}; --ph: {{ $color ?: '#E8177F' }};">
    @if ($src)
        <img src="{{ $src }}" alt="{{ $alt }}" @unless ($eager) loading="lazy" @endunless decoding="async" class="absolute inset-0 size-full object-cover">
    @else
        <div class="mm-ph absolute inset-0 grid place-items-center" role="img" aria-label="{{ $alt ?: 'Photo coming soon' }}">
            <div class="relative z-10 flex flex-col items-center gap-2 px-3 text-center">
                <span class="grid size-12 place-items-center rounded-2xl bg-white sm:size-14" style="color: {{ $color ?: '#E8177F' }}; box-shadow: 0 0 0 2px color-mix(in srgb, {{ $color ?: '#E8177F' }} 30%, #fff);">
                    <x-icon :name="$icon" class="size-6 sm:size-7" />
                </span>
                @if ($label)
                    <span class="text-xs font-bold sm:text-sm" style="color: color-mix(in srgb, {{ $color ?: '#E8177F' }} 55%, #33307A);">{{ $label }}</span>
                @endif
            </div>
        </div>
    @endif
    {{ $slot }}
</div>
