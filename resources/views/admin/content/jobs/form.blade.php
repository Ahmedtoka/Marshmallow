@extends('layouts.admin')
@section('title', $job->exists ? $job->title : 'Add opening')

@section('content')
    <x-admin.page-header :title="$job->exists ? $job->title : 'Add job opening'" subtitle="Shown on the Careers page. Applicants can pick it in the application form." :back="route('admin.content.jobs.index')">
        @if ($job->exists)
            <x-slot:actions>
                <a href="{{ route('admin.content.applications.index', ['opening' => $job->id]) }}" class="btn btn-secondary"><x-icon name="file" class="size-4" /> {{ $job->applications_count }} applications</a>
                <x-admin.confirm-delete :action="route('admin.content.jobs.destroy', $job)" size="" :message="'Delete the '.$job->title.' opening? Applications already received are kept.'" />
            </x-slot:actions>
        @endif
    </x-admin.page-header>

    <form method="POST" action="{{ $job->exists ? route('admin.content.jobs.update', $job) : route('admin.content.jobs.store') }}">
        @csrf
        @if ($job->exists) @method('PUT') @endif

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="min-w-0 space-y-6 lg:col-span-2">
                <div class="card card-pad space-y-5">
                    <x-admin.input name="title" label="Job title" :value="$job->title" required placeholder="Class Teacher" />
                    <div class="grid gap-5 sm:grid-cols-2">
                        <x-admin.input name="type" label="Type" :value="$job->type" list="job-types" placeholder="Full time" />
                        <datalist id="job-types">
                            @foreach (\App\Http\Requests\Admin\Content\JobOpeningRequest::TYPES as $type)
                                <option value="{{ $type }}"></option>
                            @endforeach
                        </datalist>
                        <x-admin.select name="branch_id" label="Branch" :options="$branches" :value="$job->branch_id" placeholder="All branches" />
                    </div>
                    <x-admin.input name="slug" label="Web address" :value="$job->slug" placeholder="made from the title" hint="Leave empty to make it from the title." />
                    <x-admin.textarea name="description" label="Description" :value="$job->description" rows="5" hint="What the role involves, in two or three sentences." />
                </div>
                <div class="card card-pad">
                    <x-admin.list-input name="requirements" label="Requirements" :items="$job->requirements ?? []" add-label="Add a requirement"
                        placeholder="Fluent English" hint="Shown as a bullet list. Press Enter to add another." />
                </div>
            </div>
            <div class="card card-pad min-w-0 space-y-4 self-start">
                <h2 class="card-title">Publishing</h2>
                <x-admin.toggle name="is_active" label="Accepting applications" :checked="$job->is_active" hint="Turn off to hide the opening from the Careers page." />
                <x-admin.input name="sort_order" label="Order" type="number" min="0" :value="$job->sort_order" hint="Lower numbers show first." />
            </div>
        </div>

        @include('admin.content.partials.save-bar', ['label' => $job->exists ? 'Save opening' : 'Add opening', 'cancel' => route('admin.content.jobs.index')])
    </form>
@endsection
