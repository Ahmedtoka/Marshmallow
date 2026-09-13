<?php

namespace App\Http\Controllers\Admin\Content;

use App\Http\Controllers\Admin\Content\Concerns\HandlesUploads;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Content\PartnerRequest;
use App\Models\Partner;

class PartnerController extends Controller
{
    use HandlesUploads;

    public function index()
    {
        return view('admin.content.partners.index', [
            'partners' => Partner::query()->orderBy('type')->orderBy('sort_order')->get(),
        ]);
    }

    public function create()
    {
        return view('admin.content.partners.form', [
            'partner' => new Partner(['type' => 'school', 'is_visible' => true, 'sort_order' => (int) Partner::max('sort_order') + 1]),
        ]);
    }

    public function store(PartnerRequest $request)
    {
        $data = $request->saveData();
        $this->applyImage($request, $data, 'logo', 'partners');
        $partner = Partner::create($data);

        return redirect()->route('admin.content.partners.index')->with('success', "Partner “{$partner->name}” saved.");
    }

    public function edit(Partner $partner)
    {
        return view('admin.content.partners.form', ['partner' => $partner]);
    }

    public function update(PartnerRequest $request, Partner $partner)
    {
        $data = $request->saveData();
        $this->applyImage($request, $data, 'logo', 'partners', $partner);
        $partner->update($data);

        return redirect()->route('admin.content.partners.index')->with('success', "Partner “{$partner->name}” saved.");
    }

    public function destroy(Partner $partner)
    {
        $this->deleteImages($partner, ['logo']);
        $partner->delete();

        return redirect()->route('admin.content.partners.index')->with('success', 'Partner deleted.');
    }
}
