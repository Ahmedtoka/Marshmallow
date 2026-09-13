@extends('layouts.admin')
@section('title', $partner->exists ? $partner->name : 'Add partner')

@section('content')
    <x-admin.page-header :title="$partner->exists ? $partner->name : 'Add partner'" subtitle="Shown in the partners strip on the homepage and on the About page." :back="route('admin.content.partners.index')">
        @if ($partner->exists)
            <x-slot:actions>
                <x-admin.confirm-delete :action="route('admin.content.partners.destroy', $partner)" size="" :message="'Delete '.$partner->name.'?'" />
            </x-slot:actions>
        @endif
    </x-admin.page-header>

    <form method="POST" action="{{ $partner->exists ? route('admin.content.partners.update', $partner) : route('admin.content.partners.store') }}" enctype="multipart/form-data">
        @csrf
        @if ($partner->exists) @method('PUT') @endif

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="card card-pad min-w-0 space-y-5 self-start lg:col-span-2">
                <x-admin.input name="name" label="Name" :value="$partner->name" required placeholder="Majesty International Schools" />
                <div class="grid gap-5 sm:grid-cols-2">
                    <x-admin.select name="type" label="Type" :options="\App\Models\Partner::TYPES" :value="$partner->type" required />
                    <x-admin.input name="website" label="Website" type="url" :value="$partner->website" placeholder="https://" />
                </div>
                <x-admin.textarea name="description" label="Description" :value="$partner->description" rows="3" hint="Optional. A short note, e.g. what the recognition was for." />
            </div>
            <div class="min-w-0 space-y-6">
                <div class="card card-pad space-y-4">
                    <h2 class="card-title">Publishing</h2>
                    <x-admin.toggle name="is_visible" label="Visible" :checked="$partner->is_visible" />
                    <x-admin.input name="sort_order" label="Order" type="number" min="0" :value="$partner->sort_order" hint="Lower numbers show first." />
                </div>
                <div class="card card-pad">
                    <x-admin.image name="logo" label="Logo" :path="$partner->logo" aspect="aspect-[3/2]" fit="object-contain" hint="PNG with a transparent background works best. Without a logo the name is shown." />
                </div>
            </div>
        </div>

        @include('admin.content.partials.save-bar', ['label' => $partner->exists ? 'Save partner' : 'Add partner', 'cancel' => route('admin.content.partners.index')])
    </form>
@endsection
