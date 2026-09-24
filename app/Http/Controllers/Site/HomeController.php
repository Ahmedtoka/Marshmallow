<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Branch;
use App\Models\Camp;
use App\Models\Classroom;
use App\Models\GalleryAlbum;
use App\Models\Highlight;
use App\Models\Partner;
use App\Models\Photo;
use App\Models\Section;
use App\Models\Testimonial;
use App\Support\ClassFinder;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;

/**
 * The homepage is one journey for a parent who has just seen an ad:
 * happy children → who we are → what we do → the classes → is it safe →
 * what other parents say → the schools we hand children to → the photos → come and visit.
 */
class HomeController extends Controller
{
    public function index(): View
    {
        $sections = Section::map()->filter(fn (Section $s) => $s->is_visible)->sortBy('sort_order');
        $has = fn (string $key) => $sections->has($key);

        $classrooms = Classroom::active()->get();

        return view('site.home', [
            'seoKey' => 'home',
            'sections' => $sections,
            'classrooms' => $classrooms,
            'finderConfig' => ClassFinder::clientConfig($classrooms),
            'branches' => Branch::active()->get(),
            'heroPhotos' => $has('hero') ? $this->heroPhotos() : collect(),
            'graduations' => $has('about') ? $this->graduationAlbums() : collect(),
            'offer' => $has('offer') ? $this->activitiesWithPhotos() : collect(),
            'camps' => $has('offer') ? Camp::active()->take(2)->get() : collect(),
            'trust' => $has('safety') ? $this->trust() : collect(),
            'reviews' => $has('reviews') ? Testimonial::visible()->orderByDesc('reviewed_at')->whereNotNull('quote')->take(9)->get() : collect(),
            'reviewsTotal' => Testimonial::visible()->count(),
            'partners' => $has('partners') ? Partner::visible()->get() : collect(),
            'galleryPhotos' => $has('gallery') ? $this->galleryPhotos() : collect(),
            'albumCount' => GalleryAlbum::where('is_visible', true)->count(),
        ]);
    }

    /** Wide photos from different albums, so the slider never shows the same day twice. */
    private function heroPhotos(): Collection
    {
        return Photo::where('photoable_type', 'album')
            ->whereIn('photoable_id', GalleryAlbum::where('is_visible', true)->pluck('id'))
            ->whereColumn('width', '>=', 'height')
            ->with('photoable:id,title,slug')
            ->orderByDesc('width')
            ->get()
            ->unique('photoable_id')
            ->take(6)
            ->values();
    }

    /** @return Collection<int, GalleryAlbum> the graduation albums, newest first */
    private function graduationAlbums(): Collection
    {
        return GalleryAlbum::where('is_visible', true)
            ->where('category', 'graduation')
            ->with(['photos' => fn ($q) => $q->reorder()->orderBy('sort_order')->limit(4)])
            ->orderByDesc('event_date')
            ->orderByDesc('id')
            ->take(3)
            ->get();
    }

    /**
     * Every activity with one photo to show it. Most photos were taken of an activity inside a
     * class, so we fall back to those before giving up on a picture.
     *
     * @return Collection<int, object{activity: Activity, photo: ?string}>
     */
    private function activitiesWithPhotos(): Collection
    {
        $activities = Activity::active()->with(['photos' => fn ($q) => $q->reorder()->orderBy('sort_order')->limit(1)])->get();

        $fromClasses = Photo::where('photos.photoable_type', 'classroom_activity')
            ->join('classroom_activity', 'classroom_activity.id', '=', 'photos.photoable_id')
            ->whereIn('classroom_activity.activity_id', $activities->pluck('id'))
            ->orderBy('photos.sort_order')
            ->pluck('photos.path', 'classroom_activity.activity_id');

        return $activities->map(fn (Activity $activity) => (object) [
            'activity' => $activity,
            'photo' => $activity->cover_image ?: ($activity->photos->first()?->path ?: ($fromClasses[$activity->id] ?? null)),
        ])->sortByDesc(fn ($item) => $item->photo ? 1 : 0)->values();
    }

    /** The reassurance block: what keeps a child safe, fed and looked after. */
    private function trust(): Collection
    {
        $order = ['safety' => 1, 'health' => 2, 'logistics' => 3];

        return Highlight::whereIn('group', array_keys($order))
            ->where('is_visible', true)
            ->orderBy('sort_order')
            ->get()
            ->sortBy(fn (Highlight $h) => [$order[$h->group], $h->sort_order])
            ->take(6)
            ->concat(Highlight::group('meals')->get()->take(1))
            ->values();
    }

    /** A mixed wall of real photos, each one carrying the name of what it shows. */
    private function galleryPhotos(): Collection
    {
        $albums = GalleryAlbum::where('is_visible', true)
            ->withCount('photos')
            ->orderByRaw('event_date IS NULL')
            ->orderByDesc('event_date')
            ->orderBy('sort_order')
            ->take(12)
            ->get();

        // Landscape photos first: the designed posters the nursery publishes are portrait, and the
        // wall should be real moments, not adverts.
        return $albums->flatMap(fn (GalleryAlbum $album) => $album->photos()
            ->orderByRaw('(width >= height) desc')->orderBy('sort_order')->take(2)->get()
            ->map(fn (Photo $photo) => (object) ['photo' => $photo, 'album' => $album]))
            ->take(12);
    }
}
