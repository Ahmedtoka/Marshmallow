<?php

namespace App\Http\Controllers\Admin\Content;

use App\Http\Controllers\Admin\Content\Concerns\HandlesUploads;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Content\AlbumRequest;
use App\Models\Branch;
use App\Models\GalleryAlbum;
use Illuminate\Http\Request;

class AlbumController extends Controller
{
    use HandlesUploads;

    public function index(Request $request)
    {
        $category = $request->query('category');

        return view('admin.content.albums.index', [
            'albums' => GalleryAlbum::query()
                ->with(['branch', 'photos' => fn ($q) => $q->limit(4)])
                ->withCount('photos')
                ->when(array_key_exists((string) $category, GalleryAlbum::CATEGORIES), fn ($q) => $q->where('category', $category))
                ->orderBy('sort_order')->orderByDesc('event_date')
                ->get(),
            'category' => $category,
        ]);
    }

    public function create()
    {
        return view('admin.content.albums.form', [
            'album' => new GalleryAlbum(['is_visible' => true, 'category' => 'activities', 'sort_order' => (int) GalleryAlbum::max('sort_order') + 1]),
            'branches' => $this->branches(),
        ]);
    }

    public function store(AlbumRequest $request)
    {
        $album = GalleryAlbum::create($this->payload($request));

        return redirect()->route('admin.content.albums.edit', $album)->with('success', "Album “{$album->title}” saved. Now add its photos.");
    }

    public function edit(GalleryAlbum $album)
    {
        return view('admin.content.albums.form', ['album' => $album, 'branches' => $this->branches()]);
    }

    public function update(AlbumRequest $request, GalleryAlbum $album)
    {
        $album->update($this->payload($request, $album));

        return redirect()->route('admin.content.albums.edit', $album)->with('success', "Album “{$album->title}” saved.");
    }

    public function destroy(GalleryAlbum $album)
    {
        $this->deleteImages($album, ['cover_image']);
        $title = $album->title;
        $album->delete(); // photos are deleted with it

        return redirect()->route('admin.content.albums.index')->with('success', "Album “{$title}” and its photos deleted.");
    }

    private function payload(AlbumRequest $request, ?GalleryAlbum $album = null): array
    {
        $data = $request->saveData();
        $data['slug'] = $this->uniqueSlug(GalleryAlbum::class, $data['slug'] ?? null, $data['title'], $album?->id);
        $this->applyImage($request, $data, 'cover_image', 'albums', $album);

        return $data;
    }

    private function branches(): array
    {
        return Branch::query()->orderBy('sort_order')->pluck('name', 'id')->all();
    }
}
