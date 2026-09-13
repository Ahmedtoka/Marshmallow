@extends('layouts.site')

@section('content')
    <x-site.page-header title="A peek inside our days" intro="Graduations, trips, science days and celebrations at Marshmallow." color="#8479BD" />

    <section class="bg-white py-10 sm:py-14" x-data="{ cat: 'all' }">
        <div class="mx-auto max-w-6xl px-5 sm:px-8">
            @if ($categories->count() > 1)
                <div class="-mx-5 overflow-x-auto px-5 pb-2 scrollbar-none sm:mx-0 sm:px-0" role="group" aria-label="Filter albums">
                    <div class="flex gap-2">
                        <button type="button" @click="cat = 'all'" :aria-pressed="(cat === 'all').toString()"
                            class="shrink-0 rounded-full border-2 px-4 py-2 font-bold transition-colors"
                            :class="cat === 'all' ? 'border-pink bg-pink text-white' : 'border-line bg-white hover:border-pink-200'">All</button>
                        @foreach ($categories as $key => $label)
                            <button type="button" @click="cat = @js($key)" :aria-pressed="(cat === @js($key)).toString()"
                                class="shrink-0 rounded-full border-2 px-4 py-2 font-bold transition-colors"
                                :class="cat === @js($key) ? 'border-pink bg-pink text-white' : 'border-line bg-white hover:border-pink-200'">{{ $label }}</button>
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($albums->isEmpty())
                <div class="mt-6">
                    @include('site.partials.photo-grid', ['photos' => collect(), 'title' => 'Gallery', 'color' => '#8479BD', 'emptyTitle' => 'Our first albums are on their way'])
                </div>
            @else
                <ul class="mt-6 grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-3">
                    @foreach ($albums as $album)
                        <li x-show="cat === 'all' || cat === @js($album->category)" x-transition.opacity>
                            @include('site.partials.album-card', ['album' => $album, 'large' => false, 'place' => 'Gallery page'])
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </section>
@endsection
