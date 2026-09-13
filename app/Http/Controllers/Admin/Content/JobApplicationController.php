<?php

namespace App\Http\Controllers\Admin\Content;

use App\Http\Controllers\Controller;
use App\Models\JobApplication;
use App\Models\JobOpening;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class JobApplicationController extends Controller
{
    public function index(Request $request)
    {
        $status = array_key_exists((string) $request->query('status'), JobApplication::STATUSES) ? $request->query('status') : null;
        $opening = $request->integer('opening') ?: null;

        return view('admin.content.applications.index', [
            'applications' => JobApplication::query()
                ->with(['jobOpening', 'branch'])
                ->when($status, fn ($q) => $q->where('status', $status))
                ->when($opening, fn ($q) => $q->where('job_opening_id', $opening))
                ->latest('id')
                ->paginate(20)
                ->withQueryString(),
            'status' => $status,
            'opening' => $opening,
            'openings' => JobOpening::query()->orderBy('sort_order')->pluck('title', 'id')->all(),
            'counts' => JobApplication::query()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),
            'newApplications' => JobApplication::where('status', 'new')->count(),
        ]);
    }

    public function show(Request $request, JobApplication $application)
    {
        if ($request->query('download') === 'cv') {
            abort_unless($application->cv_path && Storage::disk('local')->exists($application->cv_path), 404, 'The CV file could not be found.');

            $extension = pathinfo($application->cv_path, PATHINFO_EXTENSION);

            return Storage::disk('local')->download($application->cv_path, Str::slug($application->name ?: 'applicant').'-cv'.($extension ? '.'.$extension : ''));
        }

        return view('admin.content.applications.show', [
            'application' => $application->load(['jobOpening', 'branch']),
            'hasCv' => $application->cv_path && Storage::disk('local')->exists($application->cv_path),
        ]);
    }

    public function update(Request $request, JobApplication $application)
    {
        $data = $request->validate(['status' => ['required', Rule::in(array_keys(JobApplication::STATUSES))]]);
        $application->update($data);

        return back()->with('success', "{$application->name} marked as ".mb_strtolower(JobApplication::STATUSES[$data['status']]).'.');
    }

    public function destroy(JobApplication $application)
    {
        if ($application->cv_path) {
            Storage::disk('local')->delete($application->cv_path);
        }
        $application->delete();

        return redirect()->route('admin.content.applications.index')->with('success', 'Application and CV deleted.');
    }
}
