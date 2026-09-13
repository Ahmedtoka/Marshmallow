<?php

namespace App\Http\Controllers\Admin\Content;

use App\Http\Controllers\Admin\Content\Concerns\HandlesUploads;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Content\SectionRequest;
use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SectionController extends Controller
{
    use HandlesUploads;

    /** Where each homepage section appears, in the owner's words. */
    public const HINTS = [
        'hero' => 'The big banner at the very top of the homepage, with the main headline and button.',
        'class_finder' => 'The birthday box where parents find which class their child will join.',
        'why' => 'The “Why Marshmallow” cards. The cards themselves are edited under Highlights → Why Marshmallow.',
        'classes' => 'The row of class cards. Classes are edited under Classes.',
        'activities' => 'The activities preview. Activities are edited under Activities.',
        'safety' => 'The safety and care strip. Its items come from Highlights (safety, health and meals).',
        'camps' => 'The holiday camps teaser. Camps are edited under Camps; featured camps show first.',
        'testimonials' => 'Quotes from parents. Edited under Testimonials.',
        'partners' => 'The partner schools strip. Edited under Partners.',
        'gallery' => 'A strip of photos from your visible gallery albums.',
        'faq' => 'Frequently asked questions. Edited under FAQ.',
        'branches' => 'Branch cards with maps and phone numbers. Edited under Branches.',
        'enroll_cta' => 'The closing “book a visit” banner just above the footer.',
    ];

    public function index()
    {
        return view('admin.content.sections.index', [
            'sections' => Section::query()->orderBy('sort_order')->get(),
            'hints' => self::HINTS,
        ]);
    }

    public function reorder(Request $request)
    {
        $data = $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['integer']]);

        foreach (array_values($data['ids']) as $i => $id) {
            Section::query()->whereKey($id)->update(['sort_order' => $i]);
        }
        Cache::forget(Section::CACHE_KEY);

        return $request->expectsJson()
            ? response()->json(['ok' => true])
            : back()->with('success', 'Section order saved.');
    }

    public function edit(Section $section)
    {
        return view('admin.content.sections.edit', [
            'section' => $section,
            'hint' => self::HINTS[$section->key] ?? null,
        ]);
    }

    public function update(SectionRequest $request, Section $section)
    {
        $data = $request->saveData();
        $this->applyImage($request, $data, 'image', 'sections', $section);
        $section->update($data);

        return redirect()->route('admin.content.sections.index')->with('success', "“{$section->name}” section saved.");
    }

    public function toggle(Request $request, Section $section)
    {
        $section->update(['is_visible' => ! $section->is_visible]);

        if ($request->expectsJson()) {
            return response()->json(['is_visible' => $section->is_visible]);
        }

        return back()->with('success', "“{$section->name}” is now ".($section->is_visible ? 'visible' : 'hidden').'.');
    }
}
