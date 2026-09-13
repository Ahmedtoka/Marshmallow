@extends('layouts.admin')
@section('title', $user->exists ? $user->name : 'Add team member')

@php $isMe = $user->exists && $user->is(auth()->user()); @endphp

@section('content')
    <x-admin.page-header :title="$user->exists ? $user->name : 'Add team member'"
        :subtitle="$user->exists ? $user->email : 'They’ll sign in at '.route('admin.login').' with the email and password you set here.'"
        :back="route('admin.content.users.index')">
        @if ($user->exists && ! $isMe)
            <x-slot:actions>
                <x-admin.confirm-delete :action="route('admin.content.users.destroy', $user)" size="" :message="'Delete '.$user->name.'’s account? They will no longer be able to sign in.'" />
            </x-slot:actions>
        @endif
    </x-admin.page-header>

    <form method="POST" action="{{ $user->exists ? route('admin.content.users.update', $user) : route('admin.content.users.store') }}" autocomplete="off">
        @csrf
        @if ($user->exists) @method('PUT') @endif

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="min-w-0 space-y-6 lg:col-span-2">
                <div class="card card-pad space-y-5">
                    <h2 class="card-title">Details</h2>
                    <div class="grid gap-5 sm:grid-cols-2">
                        <x-admin.input name="name" label="Full name" :value="$user->name" required class="sm:col-span-2" />
                        <x-admin.input name="email" label="Email" type="email" :value="$user->email" required autocomplete="off" />
                        <x-admin.input name="phone" label="Phone" type="tel" :value="$user->phone" />
                    </div>
                </div>

                <div class="card card-pad space-y-5">
                    <div>
                        <h2 class="card-title">Password</h2>
                        <p class="hint">{{ $user->exists ? 'Leave empty to keep the current password.' : 'At least 8 characters. Share it privately and ask them to change it from My profile.' }}</p>
                    </div>
                    <div class="grid gap-5 sm:grid-cols-2">
                        <x-admin.input name="password" label="New password" type="password" :required="! $user->exists" autocomplete="new-password" />
                        <x-admin.input name="password_confirmation" label="Repeat password" type="password" :required="! $user->exists" autocomplete="new-password" />
                    </div>
                </div>
            </div>

            <div class="min-w-0 space-y-6">
                <div class="card card-pad space-y-5">
                    <h2 class="card-title">Access</h2>
                    <x-admin.select name="role" label="Role" :options="\App\Models\User::ROLES" :value="$user->role" required
                        :hint="$isMe ? 'You can’t change your own role.' : 'Admins manage everything. Sales agents only see their own leads.'" />
                    <x-admin.select name="branch_id" label="Branch" :options="$branches" :value="$user->branch_id" placeholder="No branch"
                        hint="Sales agents receive new website leads for their branch." />
                    <x-admin.toggle name="is_active" label="Active" :checked="$user->is_active"
                        :hint="$isMe ? 'You can’t deactivate yourself.' : 'Deactivated members can’t sign in or receive leads. Their history is kept.'" />
                </div>
                @if ($user->exists)
                    <div class="card card-pad text-sm">
                        <dl class="grid grid-cols-[auto_1fr] gap-x-4 gap-y-1.5">
                            <dt class="text-muted">Last sign in</dt><dd class="font-semibold">{{ $user->last_login_at?->format('j M Y, g:i a') ?? 'Never' }}</dd>
                            <dt class="text-muted">Added</dt><dd class="font-semibold">{{ $user->created_at?->format('j M Y') }}</dd>
                        </dl>
                    </div>
                @endif
            </div>
        </div>

        @include('admin.content.partials.save-bar', ['label' => $user->exists ? 'Save account' : 'Add team member', 'cancel' => route('admin.content.users.index')])
    </form>
@endsection
