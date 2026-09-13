@if ($albums->isNotEmpty())
    <section class="{{ $tint ? 'bg-blush' : 'bg-white' }} py-16 sm:py-24">
        <div class="mx-auto max-w-6xl px-5 sm:px-8">
            <div class="flex flex-wrap items-end justify-between gap-5">
                <x-site.section-head :title="$section->title" :subtitle="$section->subtitle" />
                @if ($section->button_text)
                    <a href="{{ url($section->button_url ?: route('gallery.index')) }}" class="btn btn-outline" data-track="cta_click" data-track-label="Gallery – {{ $section->button_text }}">{{ $section->button_text }}</a>
                @endif
            </div>
            <ul class="mt-10 grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
                @foreach ($albums as $i => $album)
                    <li @class(['col-span-2 row-span-2' => $i === 0])>
                        @include('site.partials.album-card', ['album' => $album, 'large' => $i === 0, 'place' => 'Home gallery'])
                    </li>
                @endforeach
            </ul>
        </div>
    </section>
@endif
