@extends('layouts.admin')
@section('title', $camp->exists ? $camp->title : 'Add camp')

@section('content')
    <x-admin.page-header :title="$camp->exists ? $camp->title : 'Add camp'"
        :subtitle="$camp->exists ? 'Shown on the Camps page and, when featured, on the homepage.' : 'A holiday camp parents can register for.'"
        :back="route('admin.content.camps.index')">
        @if ($camp->exists)
            <x-slot:actions>
                <a href="{{ route('camps.show', $camp) }}" target="_blank" class="btn btn-secondary"><x-icon name="external" class="size-4" /> View on website</a>
                <x-admin.confirm-delete :action="route('admin.content.camps.destroy', $camp)" size="" :message="'Delete '.$camp->title.' and its photos? This can’t be undone.'" />
            </x-slot:actions>
        @endif
    </x-admin.page-header>

    <form method="POST" action="{{ $camp->exists ? route('admin.content.camps.update', $camp) : route('admin.content.camps.store') }}" enctype="multipart/form-data">
        @csrf
        @if ($camp->exists) @method('PUT') @endif

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="min-w-0 space-y-6 lg:col-span-2">
                <div class="card card-pad space-y-5">
                    <h2 class="card-title">About the camp</h2>
                    <div class="grid gap-5 sm:grid-cols-2">
                        <x-admin.input name="title" label="Title" :value="$camp->title" required placeholder="Summer Camp 2027" class="sm:col-span-2" />
                        <x-admin.input name="slug" label="Web address" :value="$camp->slug" placeholder="made from the title" hint="Leave empty to make it from the title." />
                        <x-admin.input name="badge" label="Badge" :value="$camp->badge" placeholder="Limited spaces" hint="A small label on the camp card." />
                    </div>
                    <x-admin.textarea name="summary" label="Summary" :value="$camp->summary" rows="2" hint="One sentence shown on camp cards." />
                    <x-admin.textarea name="description" label="Full description" :value="$camp->description" rows="6" hint="Shown on the camp page. Leave an empty line between paragraphs." />
                </div>

                <div class="card card-pad space-y-5">
                    <h2 class="card-title">When and who</h2>
                    <div class="grid gap-5 sm:grid-cols-2">
                        <x-admin.select name="season" label="Season" :options="\App\Models\Camp::SEASONS" :value="$camp->season" required />
                        <x-admin.input name="year" label="Year" type="number" min="2000" max="2100" :value="$camp->year" />
                        <x-admin.input name="starts_on" label="Starts on" type="date" :value="$camp->starts_on?->format('Y-m-d')" />
                        <x-admin.input name="ends_on" label="Ends on" type="date" :value="$camp->ends_on?->format('Y-m-d')" />
                        <x-admin.input name="age_from" label="Youngest age (years)" type="number" min="0" max="18" :value="$camp->age_from" required />
                        <x-admin.input name="age_to" label="Oldest age (years)" type="number" min="0" max="18" :value="$camp->age_to" required />
                        <x-admin.input name="schedule" label="Schedule" :value="$camp->schedule" placeholder="Sunday – Thursday, June to August" class="sm:col-span-2" />
                        <x-admin.input name="meals" label="Meals" :value="$camp->meals" placeholder="3 meals included" class="sm:col-span-2" />
                    </div>
                </div>

                <div class="card card-pad">
                    <x-admin.list-input name="activities" label="Camp activities" :items="$camp->activities ?? []" add-label="Add an activity"
                        placeholder="Pottery making" hint="Shown as a list of tags on the camp page. Press Enter to add another." />
                </div>
            </div>

            <div class="min-w-0 space-y-6">
                <div class="card card-pad space-y-4">
                    <h2 class="card-title">Publishing</h2>
                    <x-admin.toggle name="is_active" label="Active" :checked="$camp->is_active" hint="Hidden camps don’t appear on the website." />
                    <x-admin.toggle name="is_featured" label="Featured" :checked="$camp->is_featured" hint="Featured camps show first and on the homepage." />
                    <x-admin.input name="sort_order" label="Order" type="number" min="0" :value="$camp->sort_order" hint="Lower numbers show first." />
                </div>
                <div class="card card-pad">
                    <x-admin.image name="cover_image" label="Cover photo" :path="$camp->cover_image" hint="Camp card and top of the camp page. Landscape, at least 1200 px wide." />
                </div>
            </div>
        </div>

        @include('admin.content.partials.save-bar', ['label' => $camp->exists ? 'Save camp' : 'Add camp', 'cancel' => route('admin.content.camps.index')])
    </form>

    @if ($camp->exists)
        <x-admin.photos :model="$camp" type="camp" class="mt-10" hint="Shown in the gallery on the camp page. Drag to reorder; the starred photo is used first." />
    @endif
@endsection
