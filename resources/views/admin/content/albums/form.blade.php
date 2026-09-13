@extends('layouts.admin')
@section('title', $album->exists ? $album->title : 'New album')

@section('content')
    <x-admin.page-header :title="$album->exists ? $album->title : 'New album'"
        :subtitle="$album->exists ? $album->categoryLabel().' album on the Gallery page' : 'Give the album a name, then upload its photos.'"
        :back="route('admin.content.albums.index')">
        @if ($album->exists)
            <x-slot:actions>
                <a href="{{ route('gallery.show', $album) }}" target="_blank" class="btn btn-secondary"><x-icon name="external" class="size-4" /> View on website</a>
                <x-admin.confirm-delete :action="route('admin.content.albums.destroy', $album)" size="" label="Delete album"
                    :message="'Delete the album “'.$album->title.'” and all of its photos? This can’t be undone.'" />
            </x-slot:actions>
        @endif
    </x-admin.page-header>

    <div @class(['grid gap-6', 'lg:grid-cols-3' => $album->exists, 'max-w-3xl' => ! $album->exists])>
        @if ($album->exists)
            <x-admin.photos :model="$album" type="album" title="Album photos" class="min-w-0 lg:col-span-2"
                hint="Drag photos from your computer onto the box below. Drag the handle to reorder; the starred photo is the album cover if no cover is set." />
        @endif

        <form method="POST" action="{{ $album->exists ? route('admin.content.albums.update', $album) : route('admin.content.albums.store') }}" enctype="multipart/form-data" class="min-w-0 space-y-6 self-start">
            @csrf
            @if ($album->exists) @method('PUT') @endif

            <div class="card card-pad space-y-5">
                <h2 class="card-title">Album details</h2>
                <x-admin.input name="title" label="Title" :value="$album->title" required placeholder="Science Day 2026" />
                <x-admin.input name="slug" label="Web address" :value="$album->slug" placeholder="made from the title" hint="Leave empty to make it from the title." />
                <div @class(['grid gap-5', 'sm:grid-cols-2' => ! $album->exists])>
                    <x-admin.select name="category" label="Category" :options="\App\Models\GalleryAlbum::CATEGORIES" :value="$album->category" required hint="The filter tabs on the Gallery page." />
                    <x-admin.input name="event_date" label="Event date" type="date" :value="$album->event_date?->format('Y-m-d')" hint="Newer albums show first." />
                </div>
                <x-admin.select name="branch_id" label="Branch" :options="$branches" :value="$album->branch_id" placeholder="Both branches" />
                <x-admin.textarea name="description" label="Description" :value="$album->description" rows="3" hint="A short line shown under the album title." />
            </div>

            <div class="card card-pad space-y-4">
                <h2 class="card-title">Publishing</h2>
                <x-admin.toggle name="is_visible" label="Visible in the gallery" :checked="$album->is_visible" />
                <x-admin.input name="sort_order" label="Order" type="number" min="0" :value="$album->sort_order" hint="Lower numbers show first." />
            </div>

            <div class="card card-pad">
                <x-admin.image name="cover_image" label="Cover photo" :path="$album->cover_image" aspect="aspect-[4/3]" hint="Optional. If empty, the first or starred album photo is used." />
            </div>

            <div class="sticky bottom-3 z-10 rounded-2xl border border-line bg-white/95 p-3 shadow-lg shadow-ink/5 backdrop-blur">
                <button type="submit" class="btn btn-primary w-full"><x-icon name="check" class="size-4" /> {{ $album->exists ? 'Save album details' : 'Create album and add photos' }}</button>
            </div>
        </form>
    </div>
@endsection
