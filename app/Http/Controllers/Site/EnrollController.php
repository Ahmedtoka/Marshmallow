<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Http\Requests\Site\EnrollRequest;
use App\Models\Branch;
use App\Models\Camp;
use App\Models\Classroom;
use App\Services\LeadService;
use App\Support\ClassFinder;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EnrollController extends Controller
{
    public function create(Request $request): View
    {
        $years = ClassFinder::academicYears();
        $classrooms = Classroom::active()->get();
        $branches = Branch::active()->get();
        $camps = Camp::active()->get();

        $dob = $request->query('dob');
        $dob = is_string($dob) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dob) && strtotime($dob) && Carbon::parse($dob)->isPast() ? $dob : null;

        $camp = $request->filled('camp') ? $camps->firstWhere('slug', $request->query('camp')) : null;
        $branch = $request->filled('branch') ? $branches->firstWhere('slug', $request->query('branch')) : null;
        $classroom = $request->filled('class') ? $classrooms->firstWhere('slug', $request->query('class')) : null;

        $interest = $request->query('interest');
        if (! array_key_exists((string) $interest, EnrollRequest::INTERESTS) && $interest !== 'waitlist') {
            $interest = $camp ? 'camp' : 'enrollment';
        }

        return view('site.enroll.create', [
            'seoKey' => 'enroll',
            'years' => $years,
            'branches' => $branches,
            'camps' => $camps,
            'finderConfig' => ClassFinder::clientConfig($classrooms),
            'prefill' => [
                'child_dob' => $dob,
                'academic_year' => in_array($request->query('year'), $years, true) ? $request->query('year') : $years[0],
                'interest' => $interest,
                'camp_id' => $camp?->id,
                'branch_id' => $branch?->id ?? ($branches->count() === 1 ? $branches->first()->id : null),
            ],
            'prefillClass' => $classroom,
            'prefillCamp' => $camp,
        ]);
    }

    public function store(EnrollRequest $request, LeadService $leads): RedirectResponse
    {
        // Honeypot filled: reject silently.
        if (filled($request->input('website'))) {
            return redirect()->route('enroll.thanks')->with('lead', ['id' => null]);
        }

        $lead = $leads->createFromWebsite($request->leadData(), $request);

        return redirect()->route('enroll.thanks')->with('lead', [
            'id' => $lead->id,
            'reference' => $lead->reference,
            'classroom_id' => $lead->classroom_id,
            'branch_id' => $lead->branch_id,
            'child_name' => $lead->child_name,
            'interest' => $lead->interest,
        ]);
    }

    public function thanks(Request $request): View|RedirectResponse
    {
        $lead = $request->session()->get('lead');
        if (! is_array($lead)) {
            return redirect()->route('enroll');
        }

        return view('site.enroll.thanks', [
            'seoKey' => 'enroll',
            'lead' => $lead,
            'classroom' => ! empty($lead['classroom_id']) ? Classroom::find($lead['classroom_id']) : null,
            'branch' => ! empty($lead['branch_id']) ? Branch::find($lead['branch_id']) : null,
            'branches' => Branch::active()->get(),
        ]);
    }
}
