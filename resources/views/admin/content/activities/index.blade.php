@extends('layouts.admin')
@section('title', 'Activities')

@section('content')
    <x-admin.page-header title="Activities" subtitle="Everything children do at Marshmallow. Add activities to classes from each class’s page.">
        <x-slot:actions>
            <a href="{{ route('activities.index') }}" target="_blank" class="btn btn-secondary"><x-icon name="external" class="size-4" /> View on website</a>
            <a href="{{ route('admin.content.activities.create') }}" class="btn btn-primary"><x-icon name="plus" class="size-4" /> Add activity</a>
        </x-slot:actions>
    </x-admin.page-header>

    <div class="-mx-4 mb-4 flex gap-2 overflow-x-auto px-4 pb-1 lg:mx-0 lg:flex-wrap lg:px-0">
        <a href="{{ route('admin.content.activities.index') }}" @class(['btn btn-sm shrink-0', 'btn-primary' => ! $category, 'btn-secondary' => $category])>All <span class="opacity-70">{{ $counts->sum() }}</span></a>
        @foreach (\App\Models\Activity::CATEGORIES as $key => $label)
            <a href="{{ route('admin.content.activities.index', ['category' => $key]) }}" @class(['btn btn-sm shrink-0', 'btn-primary' => $category === $key, 'btn-secondary' => $category !== $key])>
                {{ $label }} <span class="opacity-70">{{ $counts[$key] ?? 0 }}</span>
            </a>
        @endforeach
    </div>

    <div class="card overflow-hidden">
        @if ($activities->isEmpty())
            <x-admin.empty icon="blocks" title="No activities here yet" text="Add an activity like gymnastics, storytelling or science experiments.">
                <a href="{{ route('admin.content.activities.create') }}" class="btn btn-primary"><x-icon name="plus" class="size-4" /> Add activity</a>
            </x-admin.empty>
        @else
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr><th>Activity</th><th>Category</th><th>Classes</th><th>Photos</th><th>Order</th><th>Status</th><th></th></tr>
                    </thead>
                    <tbody>
                        @foreach ($activities as $activity)
                            <tr>
                                <td class="min-w-64">
                                    <div class="flex items-center gap-3">
                                        <span class="grid size-9 shrink-0 place-items-center rounded-xl text-white" style="background: {{ $activity->color ?: '#8479BD' }}">
                                            <x-icon :name="$activity->icon" class="size-5" />
                                        </span>
                                        <div class="min-w-0">
                                            <a href="{{ route('admin.content.activities.edit', $activity) }}" class="font-bold hover:text-brand">{{ $activity->name }}</a>
                                            <p class="max-w-sm truncate text-xs text-muted">{{ $activity->summary }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap text-muted">{{ $activity->categoryLabel() }}</td>
                                <td>{{ $activity->classrooms_count }}</td>
                                <td>{{ $activity->photos_count }}</td>
                                <td class="text-muted">{{ $activity->sort_order }}</td>
                                <td>
                                    @if ($activity->is_active)
                                        <span class="badge badge-green">Active</span>
                                    @else
                                        <span class="badge badge-muted">Hidden</span>
                                    @endif
                                </td>
                                <td class="text-right">
                                    <a href="{{ route('admin.content.activities.edit', $activity) }}" class="btn btn-secondary btn-sm">Edit</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection
