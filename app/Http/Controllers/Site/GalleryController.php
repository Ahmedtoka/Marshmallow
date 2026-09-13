<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\GalleryAlbum;
use Illuminate\Contracts\View\View;

class GalleryController extends Controller
{
    public function index(): View
    {
        $albums = GalleryAlbum::visible()
            ->with(['photos' => fn ($q) => $q->reorder()->orderByDesc('is_featured')->orderBy('sort_order')->limit(1)])
            ->withCount('photos')
            ->get();

        return view('site.gallery.index', [
            'seoKey' => 'gallery',
            'albums' => $albums,
            'categories' => collect(GalleryAlbum::CATEGORIES)->only($albums->pluck('category')->unique()->all()),
        ]);
    }

    public function show(GalleryAlbum $album): View
    {
        abort_unless($album->is_visible, 404);

        $album->load(['photos', 'branch']);

        return view('site.gallery.show', [
            'seoKey' => 'gallery',
            'album' => $album,
            'others' => GalleryAlbum::visible()
                ->whereKeyNot($album->id)
                ->with(['photos' => fn ($q) => $q->reorder()->orderByDesc('is_featured')->orderBy('sort_order')->limit(1)])
                ->take(3)
                ->get(),
        ]);
    }
}
