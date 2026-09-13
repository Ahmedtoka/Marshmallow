@extends('layouts.admin')
@section('title', 'My profile')
@section('content')
    <x-admin.page-header title="My profile" :subtitle="$user->roleLabel().($user->branch ? ' · '.$user->branch->name : '')" />

    <form method="POST" action="{{ route('admin.profile.update') }}" class="grid gap-5 lg:grid-cols-2 lg:items-start max-w-4xl">
        @csrf
        @method('PUT')
        <section class="card card-pad space-y-4">
            <h2 class="card-title">Your details</h2>
            <x-admin.input name="name" label="Name" :value="$user->name" required autocomplete="name" />
            <x-admin.input name="email" label="Email" type="email" :value="$user->email" required autocomplete="email" />
            <x-admin.input name="phone" label="Mobile" type="tel" :value="$user->phone" inputmode="tel" placeholder="01012345678" />
        </section>

        <section class="card card-pad space-y-4">
            <div>
                <h2 class="card-title">Change password</h2>
                <p class="hint">Leave empty to keep your current password.</p>
            </div>
            <x-admin.input name="current_password" label="Current password" type="password" autocomplete="current-password" />
            <x-admin.input name="password" label="New password" type="password" autocomplete="new-password" hint="At least 8 characters." />
            <x-admin.input name="password_confirmation" label="Repeat new password" type="password" autocomplete="new-password" />
        </section>

        <div class="lg:col-span-2">
            <button class="btn btn-primary">Save profile</button>
        </div>
    </form>
@endsection
