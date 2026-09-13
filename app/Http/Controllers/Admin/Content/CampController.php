<?php

namespace App\Http\Controllers\Admin\Content;

use App\Http\Controllers\Admin\Content\Concerns\HandlesUploads;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Content\CampRequest;
use App\Models\Camp;

class CampController extends Controller
{
    use HandlesUploads;

    public function index()
    {
        return view('admin.content.camps.index', [
            'camps' => Camp::query()->withCount('photos')->orderByDesc('is_featured')->orderBy('sort_order')->get(),
        ]);
    }

    public function create()
    {
        return view('admin.content.camps.form', [
            'camp' => new Camp([
                'is_active' => true, 'season' => 'summer', 'year' => (int) now()->format('Y') + 1,
                'age_from' => 4, 'age_to' => 12, 'activities' => [], 'sort_order' => (int) Camp::max('sort_order') + 1,
            ]),
        ]);
    }

    public function store(CampRequest $request)
    {
        $camp = Camp::create($this->payload($request));

        return redirect()->route('admin.content.camps.edit', $camp)->with('success', "Camp “{$camp->title}” saved.");
    }

    public function edit(Camp $camp)
    {
        return view('admin.content.camps.form', ['camp' => $camp]);
    }

    public function update(CampRequest $request, Camp $camp)
    {
        $camp->update($this->payload($request, $camp));

        return redirect()->route('admin.content.camps.edit', $camp)->with('success', "Camp “{$camp->title}” saved.");
    }

    public function destroy(Camp $camp)
    {
        $this->deleteImages($camp, ['cover_image']);
        $title = $camp->title;
        $camp->delete();

        return redirect()->route('admin.content.camps.index')->with('success', "Camp “{$title}” deleted.");
    }

    private function payload(CampRequest $request, ?Camp $camp = null): array
    {
        $data = $request->saveData();
        $data['slug'] = $this->uniqueSlug(Camp::class, $data['slug'] ?? null, $data['title'], $camp?->id);
        $data['activities'] = collect($request->input('activities', []))->map(fn ($a) => trim((string) $a))->filter()->values()->all();
        $this->applyImage($request, $data, 'cover_image', 'camps', $camp);

        return $data;
    }
}
