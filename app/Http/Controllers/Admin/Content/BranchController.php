<?php

namespace App\Http\Controllers\Admin\Content;

use App\Http\Controllers\Admin\Content\Concerns\HandlesUploads;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Content\BranchRequest;
use App\Models\Branch;
use App\Models\Lead;

class BranchController extends Controller
{
    use HandlesUploads;

    public function index()
    {
        return view('admin.content.branches.index', [
            'branches' => Branch::query()->withCount(['leads', 'users'])->orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function create()
    {
        return view('admin.content.branches.form', ['branch' => new Branch(['is_active' => true, 'sort_order' => Branch::max('sort_order') + 1])]);
    }

    public function store(BranchRequest $request)
    {
        $branch = Branch::create($this->payload($request));

        return redirect()->route('admin.content.branches.index')->with('success', "Branch “{$branch->name}” saved.");
    }

    public function edit(Branch $branch)
    {
        return view('admin.content.branches.form', ['branch' => $branch]);
    }

    public function update(BranchRequest $request, Branch $branch)
    {
        $branch->update($this->payload($request, $branch));

        return redirect()->route('admin.content.branches.index')->with('success', "Branch “{$branch->name}” saved.");
    }

    public function destroy(Branch $branch)
    {
        if (Lead::withTrashed()->where('branch_id', $branch->id)->exists()) {
            return back()->with('error', "“{$branch->name}” has leads, so it can’t be deleted without losing their history. Turn off “Active” instead to hide it from the website.");
        }

        $this->deleteImages($branch, ['image']);
        $branch->delete();

        return redirect()->route('admin.content.branches.index')->with('success', 'Branch deleted.');
    }

    private function payload(BranchRequest $request, ?Branch $branch = null): array
    {
        $data = $request->saveData();
        $data['slug'] = $this->uniqueSlug(Branch::class, $data['slug'] ?? null, $data['name'], $branch?->id);

        if (empty($data['map_embed_url'])) {
            $data['map_embed_url'] = 'https://www.google.com/maps?q='.rawurlencode($data['address']).'&output=embed';
        }

        $this->applyImage($request, $data, 'image', 'branches', $branch);

        return $data;
    }
}
