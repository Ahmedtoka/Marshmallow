<?php

namespace App\Http\Controllers\Admin\Content;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\ClassroomActivity;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ClassroomActivityController extends Controller
{
    public function store(Request $request, Classroom $classroom)
    {
        $data = $request->validate([
            'activity_id' => [
                'required', 'integer', 'exists:activities,id',
                Rule::unique('classroom_activity', 'activity_id')->where('classroom_id', $classroom->id),
            ],
            'frequency' => ['nullable', 'string', 'max:255'],
            'details' => ['nullable', 'string', 'max:2000'],
        ], [
            'activity_id.required' => 'Choose an activity to add.',
            'activity_id.unique' => 'This activity is already in the class.',
        ]);

        $row = ClassroomActivity::create($data + [
            'classroom_id' => $classroom->id,
            'sort_order' => (int) ClassroomActivity::where('classroom_id', $classroom->id)->max('sort_order') + 1,
        ]);

        return redirect()
            ->to(route('admin.content.classrooms.edit', $classroom).'#activities')
            ->with('success', "“{$row->activity->name}” added to {$classroom->name}.");
    }

    public function edit(string $classroomActivity)
    {
        $row = $this->find($classroomActivity);

        return view('admin.content.classroom-activities.edit', ['row' => $row]);
    }

    public function update(Request $request, string $classroomActivity)
    {
        $row = $this->find($classroomActivity);

        $row->update($request->validate([
            'frequency' => ['nullable', 'string', 'max:255'],
            'details' => ['nullable', 'string', 'max:2000'],
        ]));

        return redirect()
            ->route('admin.content.classroom-activities.edit', $row->id)
            ->with('success', "“{$row->activity->name}” in {$row->classroom->name} saved.");
    }

    public function destroy(string $classroomActivity)
    {
        $row = $this->find($classroomActivity);
        $classroom = $row->classroom;
        $name = $row->activity->name;
        $row->delete(); // also deletes its photos (HasPhotos)

        return redirect()
            ->to(route('admin.content.classrooms.edit', $classroom).'#activities')
            ->with('success', "“{$name}” removed from {$classroom->name}.");
    }

    public function reorder(Request $request, Classroom $classroom)
    {
        $data = $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['integer']]);

        foreach (array_values($data['ids']) as $i => $id) {
            ClassroomActivity::query()->where('classroom_id', $classroom->id)->whereKey($id)->update(['sort_order' => $i]);
        }

        return $request->expectsJson()
            ? response()->json(['ok' => true])
            : back()->with('success', 'Activity order saved.');
    }

    private function find(string $id): ClassroomActivity
    {
        return ClassroomActivity::query()->with(['classroom', 'activity'])->findOrFail((int) $id);
    }
}
