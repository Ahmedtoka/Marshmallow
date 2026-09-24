<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Classroom;
use App\Models\Section;
use App\Support\ClassFinder;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ClassController extends Controller
{
    public function index(): View
    {
        $classrooms = Classroom::active()->with('activities')->get();

        return view('site.classes.index', [
            'seoKey' => 'classes',
            'classrooms' => $classrooms,
            'finderConfig' => ClassFinder::clientConfig($classrooms),
            'finderSection' => Section::for('class_finder'),
            'classesSection' => Section::for('classes'),
        ]);
    }

    /** JSON version of the class finder; the browser computes the same answer locally. */
    public function find(Request $request): JsonResponse
    {
        $data = $request->validate([
            'dob' => ['required', 'date', 'before_or_equal:today'],
            'year' => ['required', Rule::in(ClassFinder::academicYears())],
        ]);

        $classrooms = Classroom::active()->get();
        $result = ClassFinder::find(Carbon::parse($data['dob']), $data['year'], $classrooms);
        $classroom = $result['classroom'];

        $query = ['dob' => Carbon::parse($data['dob'])->toDateString(), 'year' => $data['year']];
        if ($classroom) {
            $query['class'] = $classroom->slug;
        } elseif ($result['status'] === 'too_young') {
            $query['interest'] = 'waitlist';
        }

        return response()->json([
            'status' => $result['status'],
            'months' => $result['months'],
            'reference_date' => $result['reference_date'],
            'label' => $classroom?->name ?? ($result['status'] === 'too_young' ? 'Too young' : 'Too old'),
            'classroom' => $classroom ? [
                'name' => $classroom->name,
                'slug' => $classroom->slug,
                'age' => $classroom->ageRangeLabel(),
                'color' => $classroom->color,
                'icon' => $classroom->icon,
                'tagline' => $classroom->tagline,
                'url' => route('classes.show', $classroom),
            ] : null,
            'next' => $result['next']?->only(['name', 'slug', 'min_months']),
            'action_url' => $result['status'] === 'too_old' ? route('camps.index') : route('enroll', $query),
        ]);
    }

    public function show(Classroom $classroom): View
    {
        abort_unless($classroom->is_active, 404);

        $classroom->load('photos');

        $items = $classroom->classroomActivities()
            ->with(['activity.photos', 'photos'])
            ->get()
            ->filter(fn ($item) => $item->activity && $item->activity->is_active)
            ->values();

        // One gallery for the class: the photos of each activity plus any photo of the room itself.
        // Taken round-robin so the slider alternates between activities instead of showing six
        // gymnastics photos in a row.
        $byActivity = $items
            ->map(fn ($item) => $item->photos->map(fn ($photo) => (object) ['photo' => $photo, 'label' => $item->activity->name]))
            ->push($classroom->photos->map(fn ($photo) => (object) ['photo' => $photo, 'label' => $classroom->name]))
            ->filter->isNotEmpty()
            ->values();

        $gallery = collect();
        for ($round = 0; $round < $byActivity->max(fn ($group) => $group->count()); $round++) {
            foreach ($byActivity as $group) {
                if ($group->has($round)) {
                    $gallery->push($group[$round]);
                }
            }
        }

        $all = Classroom::active()->get();
        $index = $all->search(fn ($c) => $c->id === $classroom->id);

        return view('site.classes.show', [
            'seoKey' => 'classes',
            'classroom' => $classroom,
            'items' => $items,
            'gallery' => $gallery,
            'prev' => $index > 0 ? $all[$index - 1] : null,
            'next' => $index !== false && $index < $all->count() - 1 ? $all[$index + 1] : null,
            'allClasses' => $all,
            'branches' => Branch::active()->get(),
        ]);
    }
}
