<?php

namespace App\Http\Controllers\Admin\Content;

use App\Http\Controllers\Admin\Content\Concerns\HandlesUploads;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Content\TestimonialRequest;
use App\Models\Branch;
use App\Models\Testimonial;

class TestimonialController extends Controller
{
    use HandlesUploads;

    public function index()
    {
        return view('admin.content.testimonials.index', [
            'testimonials' => Testimonial::query()->with('branch')->orderByDesc('is_featured')->orderBy('sort_order')->get(),
        ]);
    }

    public function create()
    {
        return view('admin.content.testimonials.form', [
            'testimonial' => new Testimonial(['rating' => 5, 'is_visible' => true, 'relation' => 'Marshmallow parent', 'sort_order' => (int) Testimonial::max('sort_order') + 1]),
            'branches' => $this->branches(),
        ]);
    }

    public function store(TestimonialRequest $request)
    {
        $data = $request->saveData();
        $this->applyImage($request, $data, 'photo', 'testimonials');
        $testimonial = Testimonial::create($data);

        return redirect()->route('admin.content.testimonials.index')->with('success', "Testimonial from {$testimonial->parent_name} saved.");
    }

    public function edit(Testimonial $testimonial)
    {
        return view('admin.content.testimonials.form', ['testimonial' => $testimonial, 'branches' => $this->branches()]);
    }

    public function update(TestimonialRequest $request, Testimonial $testimonial)
    {
        $data = $request->saveData();
        $this->applyImage($request, $data, 'photo', 'testimonials', $testimonial);
        $testimonial->update($data);

        return redirect()->route('admin.content.testimonials.index')->with('success', "Testimonial from {$testimonial->parent_name} saved.");
    }

    public function destroy(Testimonial $testimonial)
    {
        $this->deleteImages($testimonial, ['photo']);
        $testimonial->delete();

        return redirect()->route('admin.content.testimonials.index')->with('success', 'Testimonial deleted.');
    }

    private function branches(): array
    {
        return Branch::query()->orderBy('sort_order')->pluck('name', 'id')->all();
    }
}
