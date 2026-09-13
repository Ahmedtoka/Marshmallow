@extends('layouts.admin')
@section('title', 'Careers')

@section('content')
    <x-admin.page-header title="Careers" subtitle="Job openings shown on the Careers page, and the applications people send.">
        <x-slot:actions>
            <a href="{{ route('careers') }}" target="_blank" class="btn btn-secondary"><x-icon name="external" class="size-4" /> View on website</a>
            <a href="{{ route('admin.content.jobs.create') }}" class="btn btn-primary"><x-icon name="plus" class="size-4" /> Add opening</a>
        </x-slot:actions>
    </x-admin.page-header>

    @include('admin.content.partials.careers-tabs', ['active' => 'jobs'])

    <div class="card overflow-hidden">
        @if ($jobs->isEmpty())
            <x-admin.empty icon="briefcase" title="No job openings" text="When you’re hiring, add the role here and it appears on the Careers page.">
                <a href="{{ route('admin.content.jobs.create') }}" class="btn btn-primary"><x-icon name="plus" class="size-4" /> Add opening</a>
            </x-admin.empty>
        @else
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr><th>Opening</th><th>Type</th><th>Branch</th><th>Applications</th><th>Status</th><th></th></tr>
                    </thead>
                    <tbody>
                        @foreach ($jobs as $job)
                            <tr>
                                <td class="min-w-60">
                                    <a href="{{ route('admin.content.jobs.edit', $job) }}" class="font-bold hover:text-brand">{{ $job->title }}</a>
                                    <p class="max-w-md truncate text-xs text-muted">{{ $job->description }}</p>
                                </td>
                                <td class="whitespace-nowrap text-muted">{{ $job->type ?: '—' }}</td>
                                <td class="whitespace-nowrap text-muted">{{ $job->branch?->name ?? 'All branches' }}</td>
                                <td class="whitespace-nowrap">
                                    <a href="{{ route('admin.content.applications.index', ['opening' => $job->id]) }}" class="font-bold text-grape hover:underline">{{ $job->applications_count }}</a>
                                    @if ($job->new_applications_count)
                                        <span class="badge badge-pink ml-1">{{ $job->new_applications_count }} new</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($job->is_active)
                                        <span class="badge badge-green">Open</span>
                                    @else
                                        <span class="badge badge-muted">Closed</span>
                                    @endif
                                </td>
                                <td class="text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <a href="{{ route('admin.content.jobs.edit', $job) }}" class="btn btn-secondary btn-sm">Edit</a>
                                        <x-admin.confirm-delete :action="route('admin.content.jobs.destroy', $job)" icon :message="'Delete the '.$job->title.' opening? Applications already received are kept.'" />
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
