<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\ClassroomActivity;
use App\Models\Photo;
use App\Models\Section;
use Illuminate\Contracts\View\View;

class ActivityController extends Controller
{
    public function index(): View
    {
        $activities = Activity::active()
            ->with(['classrooms' => fn ($q) => $q->where('is_active', true)])
            ->get();

        $groups = collect(Activity::CATEGORIES)
            ->map(fn ($label, $key) => ['label' => $label, 'items' => $activities->where('category', $key)->values()])
            ->filter(fn ($g) => $g['items']->isNotEmpty());

        // Categories added in the dashboard that are not in the constant still show.
        $activities->whereNotIn('category', array_keys(Activity::CATEGORIES))->groupBy('category')
            ->each(fn ($items, $key) => $groups->put($key, ['label' => ucfirst($key), 'items' => $items->values()]));

        return view('site.activities.index', [
            'seoKey' => 'activities',
            'groups' => $groups,
            'section' => Section::for('activities'),
        ]);
    }

    public function show(Activity $activity): View
    {
        abort_unless($activity->is_active, 404);

        $activity->load(['photos', 'classrooms' => fn ($q) => $q->where('is_active', true)]);

        // Most photos are taken of this activity *inside a class* (gymnastics in Candy, and so on).
        // Fall back to those so an activity page is never empty just because it has no photos of its own.
        $photos = $activity->photos;

        if ($photos->isEmpty()) {
            $pivots = ClassroomActivity::with('classroom:id,name')->where('activity_id', $activity->id)->get()->keyBy('id');

            $photos = Photo::where('photoable_type', 'classroom_activity')
                ->whereIn('photoable_id', $pivots->keys())
                ->orderBy('photoable_id')
                ->orderBy('sort_order')
                ->get()
                ->each(fn (Photo $photo) => $photo->caption ??= 'In the '.$pivots[$photo->photoable_id]->classroom->name.' class');
        }

        $cover = media_url($activity->cover_image);

        if (! $cover && $photos->isNotEmpty()) {
            $cover = $photos->shift()->url();
        }

        return view('site.activities.show', [
            'seoKey' => 'activities',
            'activity' => $activity,
            'photos' => $photos,
            'cover' => $cover,
            'related' => Activity::active()
                ->where('category', $activity->category)
                ->whereKeyNot($activity->id)
                ->get(),
        ]);
    }
}
