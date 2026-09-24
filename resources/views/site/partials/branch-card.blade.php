<article class="relative overflow-hidden rounded-[2rem] border-2 border-line bg-white p-6 sm:p-8" id="{{ $branch->slug }}">
    <span aria-hidden="true" class="absolute -right-8 -top-8 size-28 rounded-full opacity-20" style="background: {{ $color }}"></span>
    <div class="relative">
        <h3 class="font-display text-2xl font-semibold leading-tight sm:text-[1.75rem]">{{ $branch->name }}</h3>
        <p class="mt-3 flex gap-2.5 leading-relaxed">
            <x-icon name="map-pin" class="mt-0.5 size-5" style="color: {{ $color }}" />
            <span>
                {{ $branch->address }}
                @if ($branch->address_note)
                    <span class="block text-sm font-bold text-ink-soft">{{ $branch->address_note }}</span>
                @endif
            </span>
        </p>
        @if ($branch->working_hours)
            <p class="mt-2 flex gap-2.5"><x-icon name="clock" class="mt-0.5 size-5" style="color: {{ $color }}" /> {{ $branch->working_hours }}</p>
        @endif

        <div class="mt-6 flex flex-wrap gap-2">
            <a href="{{ $branch->telLink() }}" class="btn btn-primary btn-sm" data-track-label="Call {{ $branch->name }}">
                <x-icon name="phone" class="size-4" /> {{ $branch->phone }}
            </a>
            <a href="{{ $branch->whatsappLink('Hello Marshmallow '.$branch->name.', I would like to book a visit.') }}" target="_blank" rel="noopener" class="btn btn-soft btn-sm" data-track-label="WhatsApp {{ $branch->name }}">
                <x-icon name="whatsapp" class="size-4 text-lime-700" /> WhatsApp
            </a>
            @if ($branch->map_url)
                <a href="{{ $branch->map_url }}" target="_blank" rel="noopener" class="btn btn-soft btn-sm" data-track-label="Directions {{ $branch->name }}">
                    <x-icon name="map-pin" class="size-4 text-teal-700" /> Directions
                </a>
            @endif
        </div>
        <a href="{{ route('enroll', ['branch' => $branch->slug, 'interest' => 'tour']) }}" class="link mt-5 inline-block" data-track="cta_click" data-track-label="{{ $place ?? 'Branches' }} – Book a visit at {{ $branch->name }}">Book a visit at {{ $branch->name }}</a>

        @if (($map ?? false) && $branch->map_embed_url)
            <iframe src="{{ $branch->map_embed_url }}" title="Map of {{ $branch->name }}" loading="lazy" referrerpolicy="no-referrer-when-downgrade"
                    class="mt-5 h-56 w-full rounded-[1.25rem] border-2 border-line sm:h-64"></iframe>
        @endif
    </div>
</article>
