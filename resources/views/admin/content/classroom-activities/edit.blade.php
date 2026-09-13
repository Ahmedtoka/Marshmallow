@extends('layouts.admin')
@section('title', $row->activity->name.' in '.$row->classroom->name)

@section('content')
    <x-admin.page-header
        :title="$row->activity->name.' in '.$row->classroom->name"
        subtitle="How this activity works in this class, with photos taken here. They appear on the class page."
        :back="route('admin.content.classrooms.edit', $row->classroom).'#activities'">
        <x-slot:actions>
            <a href="{{ route('classes.show', $row->classroom) }}" target="_blank" class="btn btn-secondary"><x-icon name="external" class="size-4" /> View class page</a>
            <x-admin.confirm-delete :action="route('admin.content.classroom-activities.destroy', $row)" size="" label="Remove from class"
                :message="'Remove '.$row->activity->name.' from '.$row->classroom->name.'? Photos added for it in this class will be deleted.'" />
        </x-slot:actions>
    </x-admin.page-header>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="min-w-0 lg:col-span-2">
            <x-admin.photos :model="$row" type="classroom_activity" :title="'Photos of '.$row->activity->name.' in '.$row->classroom->name"
                hint="For example, photos of this class during this activity. Drag to reorder; the starred photo is used first." />
        </div>

        <div class="min-w-0 space-y-6">
            <form method="POST" action="{{ route('admin.content.classroom-activities.update', $row) }}" class="card card-pad space-y-5">
                @csrf
                @method('PUT')
                <h2 class="card-title">In this class</h2>
                <x-admin.input name="frequency" label="How often" :value="$row->frequency" placeholder="Twice a week" list="frequency-options" hint="e.g. Daily, Twice a week, Monthly." />
                <datalist id="frequency-options">
                    <option value="Daily"></option><option value="Twice a week"></option><option value="3 times a week"></option><option value="Weekly"></option><option value="Monthly"></option>
                </datalist>
                <x-admin.textarea name="details" label="What they do" :value="$row->details" rows="4" hint="A sentence about this activity for this age group." />
                <button type="submit" class="btn btn-primary w-full"><x-icon name="check" class="size-4" /> Save</button>
            </form>

            <div class="card card-pad">
                <div class="flex items-center gap-3">
                    <span class="grid size-11 shrink-0 place-items-center rounded-xl text-white" style="background: {{ $row->activity->color ?: '#8479BD' }}">
                        <x-icon :name="$row->activity->icon" />
                    </span>
                    <div class="min-w-0">
                        <p class="font-bold">{{ $row->activity->name }}</p>
                        <p class="text-xs text-muted">{{ $row->activity->categoryLabel() }}</p>
                    </div>
                </div>
                @if ($row->activity->summary)
                    <p class="mt-3 text-sm text-muted">{{ $row->activity->summary }}</p>
                @endif
                <a href="{{ route('admin.content.activities.edit', $row->activity) }}" class="btn btn-secondary btn-sm mt-4">
                    <x-icon name="pencil" class="size-4" /> Edit the activity for all classes
                </a>
            </div>
        </div>
    </div>
@endsection
