{{-- Logo wall of the schools our graduates move on to. Params: $partners --}}
@php
    $tile = fn ($partner) => implode(' ', [
        'grid h-24 place-items-center rounded-[1.25rem] border-2 px-4 transition-colors',
        $partner->on_dark ? 'border-ink bg-ink hover:border-pink' : 'border-line-soft bg-white hover:border-pink-200',
    ]);
@endphp
<ul class="-mx-5 mt-8 flex snap-x snap-mandatory gap-3 overflow-x-auto px-5 pb-3 sm:mx-0 sm:grid sm:grid-cols-3 sm:gap-4 sm:overflow-visible sm:px-0 sm:pb-0 lg:grid-cols-5">
    @foreach ($partners as $partner)
        @php $logo = media_url($partner->logo); @endphp
        <li class="w-[45%] shrink-0 snap-start sm:w-auto">
            @if ($partner->website)
                <a href="{{ $partner->website }}" target="_blank" rel="noopener" class="{{ $tile($partner) }}" data-track-label="Partner school – {{ $partner->name }}">
            @else
                <div class="{{ $tile($partner) }}">
            @endif

            @if ($logo)
                <img src="{{ $logo }}" alt="{{ $partner->name }}" loading="lazy" class="max-h-14 w-auto max-w-full object-contain">
            @else
                <span class="text-center font-display text-[0.95rem] font-medium leading-snug {{ $partner->on_dark ? 'text-white' : 'text-ink-soft' }}">{{ $partner->name }}</span>
            @endif

            @if ($partner->website)
                </a>
            @else
                </div>
            @endif
        </li>
    @endforeach
</ul>
