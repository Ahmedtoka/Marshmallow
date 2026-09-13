<?php

namespace App\Http\Controllers\Admin\Content;

use App\Http\Controllers\Admin\Content\Concerns\HandlesUploads;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Content\JobOpeningRequest;
use App\Models\Branch;
use App\Models\JobApplication;
use App\Models\JobOpening;

class JobOpeningController extends Controller
{
    use HandlesUploads;

    public function index()
    {
        return view('admin.content.jobs.index', [
            'jobs' => JobOpening::query()
                ->with('branch')
                ->withCount(['applications', 'applications as new_applications_count' => fn ($q) => $q->where('status', 'new')])
                ->orderBy('sort_order')->get(),
            'newApplications' => JobApplication::where('status', 'new')->count(),
        ]);
    }

    public function create()
    {
        return view('admin.content.jobs.form', [
            'job' => new JobOpening(['is_active' => true, 'type' => 'Full time', 'requirements' => [], 'sort_order' => (int) JobOpening::max('sort_order') + 1]),
            'branches' => $this->branches(),
        ]);
    }

    public function store(JobOpeningRequest $request)
    {
        $job = JobOpening::create($this->payload($request));

        return redirect()->route('admin.content.jobs.index')->with('success', "Job opening “{$job->title}” saved.");
    }

    public function edit(JobOpening $job)
    {
        return view('admin.content.jobs.form', ['job' => $job->loadCount('applications'), 'branches' => $this->branches()]);
    }

    public function update(JobOpeningRequest $request, JobOpening $job)
    {
        $job->update($this->payload($request, $job));

        return redirect()->route('admin.content.jobs.index')->with('success', "Job opening “{$job->title}” saved.");
    }

    public function destroy(JobOpening $job)
    {
        $title = $job->title;
        $job->delete(); // applications keep their position text; job_opening_id becomes empty

        return redirect()->route('admin.content.jobs.index')->with('success', "Job opening “{$title}” deleted. Its applications were kept.");
    }

    private function payload(JobOpeningRequest $request, ?JobOpening $job = null): array
    {
        $data = $request->saveData();
        $data['slug'] = $this->uniqueSlug(JobOpening::class, $data['slug'] ?? null, $data['title'], $job?->id);
        $data['requirements'] = collect($request->input('requirements', []))->map(fn ($r) => trim((string) $r))->filter()->values()->all();

        return $data;
    }

    private function branches(): array
    {
        return Branch::query()->orderBy('sort_order')->pluck('name', 'id')->all();
    }
}
