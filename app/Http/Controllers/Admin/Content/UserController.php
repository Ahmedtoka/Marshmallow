<?php

namespace App\Http\Controllers\Admin\Content;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Content\UserRequest;
use App\Models\Branch;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        return view('admin.content.users.index', [
            'users' => User::query()
                ->with('branch')
                ->withCount(['leads as open_leads_count' => fn ($q) => $q->open()])
                ->orderByDesc('is_active')
                ->orderByRaw("case role when 'admin' then 0 when 'sales_manager' then 1 else 2 end")
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function create()
    {
        return view('admin.content.users.form', ['user' => new User(['role' => 'sales', 'is_active' => true]), 'branches' => $this->branches()]);
    }

    public function store(UserRequest $request)
    {
        $user = User::create($request->saveData());

        return redirect()->route('admin.content.users.index')->with('success', "{$user->name} can now sign in to the dashboard.");
    }

    public function edit(User $user)
    {
        return view('admin.content.users.form', ['user' => $user, 'branches' => $this->branches()]);
    }

    public function update(UserRequest $request, User $user)
    {
        $data = $request->saveData();

        $losesAdmin = $user->isAdmin() && $user->is_active && ($data['role'] !== 'admin' || ! $data['is_active']);

        if ($losesAdmin && $user->is($request->user())) {
            return back()->withInput()->with('error', 'You can’t remove your own admin access or deactivate yourself. Ask another admin to do it.');
        }

        if ($losesAdmin && $this->otherActiveAdmins($user) === 0) {
            return back()->withInput()->with('error', "{$user->name} is the last active admin. Make someone else an admin first.");
        }

        $user->update($data);

        return redirect()->route('admin.content.users.index')->with('success', "{$user->name}’s account saved.");
    }

    public function destroy(Request $request, User $user)
    {
        if ($user->is($request->user())) {
            return back()->with('error', 'You can’t delete your own account.');
        }

        if ($user->isAdmin() && $user->is_active && $this->otherActiveAdmins($user) === 0) {
            return back()->with('error', "{$user->name} is the last active admin and can’t be deleted.");
        }

        if (Lead::withTrashed()->where('assigned_to', $user->id)->exists()) {
            return back()->with('error', "{$user->name} has leads assigned, so deleting would lose that history. Turn off “Active” instead — they won’t be able to sign in or receive new leads.");
        }

        $name = $user->name;
        $user->delete();

        return redirect()->route('admin.content.users.index')->with('success', "{$name}’s account deleted.");
    }

    private function otherActiveAdmins(User $user): int
    {
        return User::query()->where('role', 'admin')->where('is_active', true)->whereKeyNot($user->id)->count();
    }

    private function branches(): array
    {
        return Branch::query()->orderBy('sort_order')->pluck('name', 'id')->all();
    }
}
