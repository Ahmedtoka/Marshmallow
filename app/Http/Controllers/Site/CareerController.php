<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Http\Requests\Site\CareerApplicationRequest;
use App\Models\Branch;
use App\Models\JobApplication;
use App\Models\JobOpening;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CareerController extends Controller
{
    public function index(): View
    {
        return view('site.careers', [
            'seoKey' => 'careers',
            'openings' => JobOpening::active()->with('branch')->get(),
            'branches' => Branch::active()->get(),
        ]);
    }

    public function apply(CareerApplicationRequest $request): RedirectResponse
    {
        $success = 'Thank you for applying! We read every application and will contact you if your profile fits an opening.';

        // Honeypot: bots fill every field. Pretend it worked.
        if (filled($request->input('website'))) {
            return redirect()->route('careers')->with('status', $success);
        }

        $data = $request->validated();
        $opening = is_numeric($data['position']) ? JobOpening::active()->find($data['position']) : null;

        JobApplication::create([
            'job_opening_id' => $opening?->id,
            'branch_id' => $data['branch_id'] ?? $opening?->branch_id,
            'name' => $data['name'],
            'phone' => $data['phone'],
            'email' => $data['email'] ?? null,
            'position' => $opening?->title ?? ($data['position_other'] ?? null ?: 'Other'),
            'message' => $data['message'] ?? null,
            'cv_path' => $request->hasFile('cv') ? $request->file('cv')->store('cvs', 'local') : null,
            'status' => 'new',
            'visitor_uuid' => self::visitorUuid($request),
        ]);

        return redirect()->to(route('careers').'#apply')->with('status', $success);
    }

    /**
     * The tracker sets mm_vid from JavaScript, so it is not an encrypted Laravel cookie.
     * Read the raw header when the cookie middleware could not decrypt it.
     */
    public static function visitorUuid(Request $request): ?string
    {
        $value = $request->cookie('mm_vid');
        if (! Str::isUuid((string) $value) && preg_match('/(?:^|;\s*)mm_vid=([0-9a-fA-F-]{36})/', (string) $request->headers->get('cookie'), $m)) {
            $value = $m[1];
        }

        return Str::isUuid((string) $value) ? (string) $value : null;
    }
}
