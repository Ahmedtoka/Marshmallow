@props(['title' => null, 'subtitle' => null, 'align' => 'left', 'as' => 'h2'])
@if ($title || $subtitle)
    <div {{ $attributes->merge(['class' => $align === 'center' ? 'mx-auto max-w-2xl text-center' : 'max-w-2xl']) }}>
        @if ($title)
            <{{ $as }} class="font-display text-[1.85rem] font-semibold leading-[1.12] text-ink sm:text-[2.5rem]">{{ $title }}</{{ $as }}>
        @endif
        @if ($subtitle)
            <p class="mt-3 text-[1.05rem] leading-relaxed text-ink-soft sm:text-lg">{{ $subtitle }}</p>
        @endif
        {{ $slot }}
    </div>
@endif
