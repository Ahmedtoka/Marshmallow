<?php

namespace App\Http\Controllers\Admin\Content;

use App\Http\Controllers\Admin\Content\Concerns\HandlesUploads;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Content\ActivityRequest;
use App\Models\Activity;
use App\Models\ClassroomActivity;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    use HandlesUploads;

    public function index(Request $request)
    {
        $category = $request->query('category');

        return view('admin.content.activities.index', [
            'activities' => Activity::query()
                ->withCount(['photos', 'classrooms'])
                ->when(array_key_exists((string) $category, Activity::CATEGORIES), fn ($q) => $q->where('category', $category))
                ->orderBy('sort_order')->orderBy('name')
                ->get(),
            'category' => $category,
            'counts' => Activity::query()->selectRaw('category, count(*) as total')->groupBy('category')->pluck('total', 'category'),
        ]);
    }

    public function create()
    {
        return view('admin.content.activities.form', [
            'activity' => new Activity(['is_active' => true, 'icon' => 'star', 'color' => '#E8177F', 'category' => 'academic', 'sort_order' => (int) Activity::max('sort_order') + 1]),
        ]);
    }

    public function store(ActivityRequest $request)
    {
        $activity = Activity::create($this->payload($request));

        return redirect()->route('admin.content.activities.edit', $activity)->with('success', "Activity “{$activity->name}” saved.");
    }

    public function edit(Activity $activity)
    {
        return view('admin.content.activities.form', [
            'activity' => $activity,
            'classrooms' => $activity->classrooms()->get(),
        ]);
    }

    public function update(ActivityRequest $request, Activity $activity)
    {
        $activity->update($this->payload($request, $activity));

        return redirect()->route('admin.content.activities.edit', $activity)->with('success', "Activity “{$activity->name}” saved.");
    }

    public function destroy(Activity $activity)
    {
        ClassroomActivity::query()->where('activity_id', $activity->id)->get()->each->delete();
        $this->deleteImages($activity, ['cover_image']);
        $name = $activity->name;
        $activity->delete();

        return redirect()->route('admin.content.activities.index')->with('success', "Activity “{$name}” deleted.");
    }

    private function payload(ActivityRequest $request, ?Activity $activity = null): array
    {
        $data = $request->saveData();
        $data['slug'] = $this->uniqueSlug(Activity::class, $data['slug'] ?? null, $data['name'], $activity?->id);
        $data['color'] = isset($data['color']) ? strtoupper($data['color']) : null;
        $this->applyImage($request, $data, 'cover_image', 'activities', $activity);

        return $data;
    }
}
