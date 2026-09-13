<?php

namespace App\Http\Controllers\Admin\Content;

use App\Http\Controllers\Admin\Content\Concerns\HandlesUploads;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Content\ClassroomRequest;
use App\Models\Activity;
use App\Models\Classroom;
use Illuminate\Support\Collection;

class ClassroomController extends Controller
{
    use HandlesUploads;

    /** The age map runs from birth to school age (6 years). */
    public const MAP_MONTHS = 72;

    public function index()
    {
        $classrooms = Classroom::query()
            ->withCount(['photos', 'classroomActivities'])
            ->orderBy('min_months')
            ->orderBy('sort_order')
            ->get();

        return view('admin.content.classrooms.index', [
            'classrooms' => $classrooms,
            'map' => $this->ageMap($classrooms),
            'scale' => self::MAP_MONTHS,
        ]);
    }

    public function create()
    {
        $last = Classroom::query()->where('is_active', true)->orderByDesc('min_months')->first();

        return view('admin.content.classrooms.form', [
            'classroom' => new Classroom([
                'is_active' => true,
                'color' => '#E8177F',
                'icon' => 'cupcake',
                'min_months' => $last?->max_months ?? 0,
                'sort_order' => (int) Classroom::max('sort_order') + 1,
            ]),
            'icons' => ClassroomRequest::ICONS,
        ]);
    }

    public function store(ClassroomRequest $request)
    {
        $classroom = Classroom::create($this->payload($request));

        return redirect()
            ->route('admin.content.classrooms.edit', $classroom)
            ->with('success', "Class “{$classroom->name}” saved. You can now add its activities and photos.");
    }

    public function edit(Classroom $classroom)
    {
        $rows = $classroom->classroomActivities()->with('activity')->withCount('photos')->get();

        return view('admin.content.classrooms.form', [
            'classroom' => $classroom,
            'icons' => ClassroomRequest::ICONS,
            'rows' => $rows,
            'available' => Activity::query()
                ->whereNotIn('id', $rows->pluck('activity_id'))
                ->orderBy('category')->orderBy('sort_order')->orderBy('name')
                ->get()
                ->groupBy(fn (Activity $a) => $a->categoryLabel()),
        ]);
    }

    public function update(ClassroomRequest $request, Classroom $classroom)
    {
        $classroom->update($this->payload($request, $classroom));

        return redirect()->route('admin.content.classrooms.edit', $classroom)->with('success', "Class “{$classroom->name}” saved.");
    }

    public function destroy(Classroom $classroom)
    {
        // Pivot rows are removed one by one so their photos are deleted too.
        $classroom->classroomActivities()->get()->each->delete();
        $this->deleteImages($classroom, ['cover_image']);
        $name = $classroom->name;
        $classroom->delete();

        return redirect()->route('admin.content.classrooms.index')->with('success', "Class “{$name}” deleted.");
    }

    private function payload(ClassroomRequest $request, ?Classroom $classroom = null): array
    {
        $data = $request->saveData();
        $data['slug'] = $this->uniqueSlug(Classroom::class, $data['slug'] ?? null, $data['name'], $classroom?->id);
        $data['max_months'] = $request->filled('max_months') ? (int) $request->input('max_months') : null;
        $data['color'] = strtoupper($data['color']);

        $data['goals'] = collect($request->input('goals', []))
            ->map(fn ($g) => trim((string) $g))->filter()->values()->all();

        $data['daily_routine'] = collect($request->input('daily_routine', []))
            ->map(fn ($r) => ['time' => trim((string) ($r['time'] ?? '')), 'label' => trim((string) ($r['label'] ?? ''))])
            ->filter(fn ($r) => $r['label'] !== '' || $r['time'] !== '')
            ->values()->all();

        if (blank($data['age_label'] ?? null)) {
            $to = $data['max_months'] === null ? 'school age' : Classroom::monthsLabel($data['max_months']);
            $data['age_label'] = Classroom::monthsLabel((int) $data['min_months']).' – '.$to;
        }

        $this->applyImage($request, $data, 'cover_image', 'classrooms', $classroom);

        return $data;
    }

    /**
     * Positions of active classes on a 0–72 month bar, plus any gaps or overlaps between them.
     *
     * @return array{segments: array, issues: array, first: ?int, last: ?int}
     */
    private function ageMap(Collection $classrooms): array
    {
        $scale = self::MAP_MONTHS;
        $active = $classrooms->where('is_active', true)->sortBy('min_months')->values();

        $segments = $active->map(function (Classroom $c) use ($scale) {
            $from = min($c->min_months, $scale);
            $to = min($c->max_months ?? $scale, $scale);

            return [
                'name' => $c->name,
                'color' => $c->color,
                'left' => round($from / $scale * 100, 3),
                'width' => max(round(($to - $from) / $scale * 100, 3), 0.8),
                'label' => $c->ageRangeLabel(),
                'open' => $c->max_months === null,
            ];
        })->all();

        $issues = [];
        for ($i = 1; $i < $active->count(); $i++) {
            $prev = $active[$i - 1];
            $cur = $active[$i];
            $prevEnd = $prev->max_months ?? PHP_INT_MAX;

            if ($cur->min_months > $prevEnd) {
                $issues[] = [
                    'type' => 'gap',
                    'left' => round(min($prevEnd, $scale) / $scale * 100, 3),
                    'width' => round((min($cur->min_months, $scale) - min($prevEnd, $scale)) / $scale * 100, 3),
                    'text' => "No class for children aged {$prevEnd}–{$cur->min_months} months (between {$prev->name} and {$cur->name}).",
                ];
            } elseif ($cur->min_months < $prevEnd) {
                $end = min($prevEnd, $cur->max_months ?? PHP_INT_MAX);
                $issues[] = [
                    'type' => 'overlap',
                    'left' => round(min($cur->min_months, $scale) / $scale * 100, 3),
                    'width' => round((min($end, $scale) - min($cur->min_months, $scale)) / $scale * 100, 3),
                    'text' => "{$prev->name} and {$cur->name} overlap from {$cur->min_months} months. The class finder may place children in the wrong class.",
                ];
            }
        }

        return [
            'segments' => $segments,
            'issues' => $issues,
            'first' => $active->first()?->min_months,
            'last' => $active->isEmpty() ? null : ($active->last()->max_months),
            'openEnded' => $active->isNotEmpty() && $active->last()->max_months === null,
        ];
    }
}
