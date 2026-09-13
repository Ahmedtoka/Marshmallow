@extends('layouts.admin')
@section('title', 'Team & access')

@section('content')
    <x-admin.page-header title="Team & access" subtitle="Who can sign in to the dashboard and what they can see.">
        <x-slot:actions>
            <a href="{{ route('admin.content.users.create') }}" class="btn btn-primary"><x-icon name="plus" class="size-4" /> Add team member</a>
        </x-slot:actions>
    </x-admin.page-header>

    <div class="mb-4 grid gap-3 text-sm sm:grid-cols-3">
        <div class="card card-pad py-3"><span class="badge badge-pink mb-1">Admin</span><p class="text-muted">Everything, including website content and team accounts.</p></div>
        <div class="card card-pad py-3"><span class="badge mb-1 bg-grape/15 text-grape">Sales manager</span><p class="text-muted">All leads, assignment, reports and analytics.</p></div>
        <div class="card card-pad py-3"><span class="badge mb-1 bg-teal/15 text-[#146B73]">Sales agent</span><p class="text-muted">Only the leads assigned to them.</p></div>
    </div>

    <div class="card overflow-hidden">
        @if ($users->isEmpty())
            <x-admin.empty icon="users" title="No team members" />
        @else
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr><th>Name</th><th>Role</th><th>Branch</th><th>Open leads</th><th>Last sign in</th><th>Status</th><th></th></tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $user)
                            @php
                                $roleClass = match ($user->role) { 'admin' => 'badge-pink', 'sales_manager' => 'bg-grape/15 text-grape', default => 'bg-teal/15 text-[#146B73]' };
                                $isMe = $user->is(auth()->user());
                            @endphp
                            <tr @class(['opacity-60' => ! $user->is_active])>
                                <td class="min-w-60">
                                    <div class="flex items-center gap-3">
                                        <span class="grid size-9 shrink-0 place-items-center rounded-full bg-grape text-xs font-bold text-white">{{ $user->initials() }}</span>
                                        <div class="min-w-0">
                                            <a href="{{ route('admin.content.users.edit', $user) }}" class="font-bold hover:text-brand">{{ $user->name }}</a>
                                            @if ($isMe) <span class="badge badge-muted ml-1">You</span> @endif
                                            <p class="truncate text-xs text-muted">{{ $user->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="badge {{ $roleClass }}">{{ $user->roleLabel() }}</span></td>
                                <td class="whitespace-nowrap text-muted">{{ $user->branch?->name ?? '—' }}</td>
                                <td>{{ $user->open_leads_count }}</td>
                                <td class="whitespace-nowrap text-muted">{{ $user->last_login_at?->diffForHumans() ?? 'Never' }}</td>
                                <td>
                                    @if ($user->is_active)
                                        <span class="badge badge-green">Active</span>
                                    @else
                                        <span class="badge badge-muted">Deactivated</span>
                                    @endif
                                </td>
                                <td class="text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <a href="{{ route('admin.content.users.edit', $user) }}" class="btn btn-secondary btn-sm">Edit</a>
                                        @unless ($isMe)
                                            <x-admin.confirm-delete :action="route('admin.content.users.destroy', $user)" icon :message="'Delete '.$user->name.'’s account? They will no longer be able to sign in.'" />
                                        @endunless
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection
