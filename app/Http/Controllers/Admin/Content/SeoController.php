<?php

namespace App\Http\Controllers\Admin\Content;

use App\Http\Controllers\Admin\Content\Concerns\HandlesUploads;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Content\SeoPageRequest;
use App\Models\SeoPage;

class SeoController extends Controller
{
    use HandlesUploads;

    /** Public URL of each SEO page key, when there is a matching named route. */
    public const ROUTES = [
        'home' => 'home', 'classes' => 'classes.index', 'activities' => 'activities.index', 'camps' => 'camps.index',
        'safety' => 'safety', 'gallery' => 'gallery.index', 'about' => 'about', 'branches' => 'branches',
        'careers' => 'careers', 'enroll' => 'enroll',
    ];

    public function index()
    {
        return view('admin.content.seo.index', ['pages' => SeoPage::query()->orderBy('id')->get()]);
    }

    public function edit(SeoPage $seoPage)
    {
        return view('admin.content.seo.edit', ['page' => $seoPage]);
    }

    public function update(SeoPageRequest $request, SeoPage $seoPage)
    {
        $data = $request->saveData();
        $this->applyImage($request, $data, 'og_image', 'seo', $seoPage);
        $seoPage->update($data);

        return redirect()->route('admin.content.seo.index')->with('success', "Search settings for “{$seoPage->label}” saved.");
    }
}
