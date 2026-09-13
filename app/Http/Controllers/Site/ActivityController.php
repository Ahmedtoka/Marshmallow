<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Activity;
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

        return view('site.activities.show', [
            'seoKey' => 'activities',
            'activity' => $activity,
            'related' => Activity::active()
                ->where('category', $activity->category)
                ->whereKeyNot($activity->id)
                ->get(),
        ]);
    }
}
