<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Branch;
use App\Models\Camp;
use App\Models\Classroom;
use App\Models\Faq;
use App\Models\GalleryAlbum;
use App\Models\Highlight;
use App\Models\Partner;
use App\Models\Section;
use App\Models\Testimonial;
use App\Support\ClassFinder;
use Illuminate\Contracts\View\View;

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
            'why' => $has('why') ? Highlight::group('why')->get() : collect(),
            'activityGroups' => $has('activities') ? Activity::active()->get()->groupBy('category') : collect(),
            'safety' => $has('safety') ? Highlight::group('safety')->get() : collect(),
            'meals' => $has('safety') ? Highlight::group('meals')->get() : collect(),
            'camps' => $has('camps') ? Camp::active()->with('photos')->get() : collect(),
            'testimonials' => $has('testimonials') ? Testimonial::visible()->get() : collect(),
            'partners' => $has('partners') ? Partner::visible()->get() : collect(),
            'albums' => $has('gallery') ? GalleryAlbum::where('is_visible', true)
                ->orderByRaw('event_date IS NULL')->orderByDesc('event_date')->orderBy('sort_order')
                ->with(['photos' => fn ($q) => $q->reorder()->orderByDesc('is_featured')->orderBy('sort_order')->limit(1)])
                ->withCount('photos')
                ->take(5)->get() : collect(),
            'faqs' => $has('faq') ? Faq::visible()->get() : collect(),
            'branches' => Branch::active()->get(),
        ]);
    }
}
