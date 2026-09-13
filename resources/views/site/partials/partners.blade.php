<ul class="mt-8 flex flex-wrap items-center justify-center gap-2.5 sm:gap-3">
    @foreach ($partners as $partner)
        <li>
            @php $logo = media_url($partner->logo); @endphp
            @if ($partner->website)
                <a href="{{ $partner->website }}" target="_blank" rel="noopener" class="block">
            @endif
            @if ($logo)
                <span class="grid h-16 place-items-center rounded-2xl border-2 border-line-soft bg-white px-4">
                    <img src="{{ $logo }}" alt="{{ $partner->name }}" loading="lazy" class="max-h-11 w-auto max-w-[9rem] object-contain">
                </span>
            @else
                <span class="inline-flex items-center gap-2 rounded-full border-2 border-line bg-white px-4 py-2 font-display font-medium text-ink-soft">
                    <x-icon :name="$partner->type === 'award' ? 'medal' : ($partner->type === 'certification' ? 'shield' : 'book')" class="size-4 text-teal" />
                    {{ $partner->name }}
                </span>
            @endif
            @if ($partner->website)
                </a>
            @endif
        </li>
    @endforeach
</ul>
