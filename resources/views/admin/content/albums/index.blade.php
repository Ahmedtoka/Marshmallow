@extends('layouts.admin')
@section('title', 'Gallery')

@section('content')
    <x-admin.page-header title="Gallery" subtitle="Photo albums shown on the Gallery page. Open an album to upload photos.">
        <x-slot:actions>
            <a href="{{ route('gallery.index') }}" target="_blank" class="btn btn-secondary"><x-icon name="external" class="size-4" /> View on website</a>
            <a href="{{ route('admin.content.albums.create') }}" class="btn btn-primary"><x-icon name="plus" class="size-4" /> New album</a>
        </x-slot:actions>
    </x-admin.page-header>

    <div class="-mx-4 mb-4 flex gap-2 overflow-x-auto px-4 pb-1 lg:mx-0 lg:flex-wrap lg:px-0">
        <a href="{{ route('admin.content.albums.index') }}" @class(['btn btn-sm shrink-0', 'btn-primary' => ! $category, 'btn-secondary' => $category])>All</a>
        @foreach (\App\Models\GalleryAlbum::CATEGORIES as $key => $label)
            <a href="{{ route('admin.content.albums.index', ['category' => $key]) }}" @class(['btn btn-sm shrink-0', 'btn-primary' => $category === $key, 'btn-secondary' => $category !== $key])>{{ $label }}</a>
        @endforeach
    </div>

    @if ($albums->isEmpty())
        <div class="card">
            <x-admin.empty icon="image" title="No albums here yet" text="Create an album for an event, a trip or your classrooms, then upload its photos.">
                <a href="{{ route('admin.content.albums.create') }}" class="btn btn-primary"><x-icon name="plus" class="size-4" /> New album</a>
            </x-admin.empty>
        </div>
    @else
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4">
            @foreach ($albums as $album)
                @php $cover = $album->cover_image ? media_url($album->cover_image) : $album->photos->first()?->url(); @endphp
                <a href="{{ route('admin.content.albums.edit', $album) }}" class="card group overflow-hidden transition hover:border-grape/40 hover:shadow-lg hover:shadow-ink/5">
                    <div class="relative aspect-[4/3] bg-canvas">
                        @if ($cover)
                            <img src="{{ $cover }}" alt="" loading="lazy" class="absolute inset-0 size-full object-cover">
                        @else
                            <div class="absolute inset-0 grid place-items-center text-center text-sm text-muted">
                                <div><x-icon name="image" class="mx-auto mb-1 size-8 text-muted/50" /> No photos yet</div>
                            </div>
                        @endif
                        <span class="badge absolute left-3 top-3 bg-white/95 text-ink shadow-sm"><x-icon name="camera" class="size-3.5" /> {{ $album->photos_count }}</span>
                        @unless ($album->is_visible)
                            <span class="badge absolute right-3 top-3 bg-ink/80 text-white">Hidden</span>
                        @endunless
                    </div>
                    @if ($album->photos->count() > 1)
                        <div class="grid grid-cols-4 gap-0.5 bg-white">
                            @foreach ($album->photos->take(4) as $photo)
                                <img src="{{ $photo->url() }}" alt="" loading="lazy" class="aspect-square w-full object-cover">
                            @endforeach
                        </div>
                    @endif
                    <div class="card-pad py-4">
                        <p class="truncate font-bold text-ink group-hover:text-brand">{{ $album->title }}</p>
                        <p class="mt-0.5 truncate text-xs text-muted">
                            {{ $album->categoryLabel() }}
                            @if ($album->event_date) · {{ $album->event_date->format('j M Y') }} @endif
                            @if ($album->branch) · {{ $album->branch->short_name ?: $album->branch->name }} @endif
                        </p>
                    </div>
                </a>
            @endforeach
        </div>
    @endif
@endsection
