<?php

namespace App\Http\Controllers\Admin\Content;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Content\FaqRequest;
use App\Models\Faq;

class FaqController extends Controller
{
    public function index()
    {
        return view('admin.content.faqs.index', [
            'faqs' => Faq::query()->orderBy('sort_order')->get(),
            'categories' => $this->categories(),
        ]);
    }

    public function create()
    {
        return view('admin.content.faqs.form', [
            'faq' => new Faq(['is_visible' => true, 'category' => 'admissions', 'sort_order' => (int) Faq::max('sort_order') + 1]),
            'categories' => $this->categories(),
        ]);
    }

    public function store(FaqRequest $request)
    {
        Faq::create($request->saveData());

        return redirect()->route('admin.content.faqs.index')->with('success', 'Question saved.');
    }

    public function edit(Faq $faq)
    {
        return view('admin.content.faqs.form', ['faq' => $faq, 'categories' => $this->categories()]);
    }

    public function update(FaqRequest $request, Faq $faq)
    {
        $faq->update($request->saveData());

        return redirect()->route('admin.content.faqs.index')->with('success', 'Question saved.');
    }

    public function destroy(Faq $faq)
    {
        $faq->delete();

        return redirect()->route('admin.content.faqs.index')->with('success', 'Question deleted.');
    }

    /** Known categories plus any custom ones already used. */
    private function categories(): array
    {
        $used = Faq::query()->whereNotNull('category')->distinct()->pluck('category')
            ->mapWithKeys(fn ($c) => [$c => ucfirst(str_replace(['-', '_'], ' ', $c))])->all();

        return FaqRequest::CATEGORIES + $used;
    }
}
